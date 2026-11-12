<?php

namespace Tests\Feature;

use App\Models\CaseStudy;
use App\Models\User;
use Database\Seeders\BookexchangeCaseSeeder;
use Database\Seeders\CaseStudySeeder;
use Database\Seeders\ChronicleSeeder;
use Database\Seeders\PizzaCaseSeeder;
use Database\Seeders\RestaurantCaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaseStudyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CaseStudySeeder::class);
        $this->seed(BookexchangeCaseSeeder::class);
        $this->seed(RestaurantCaseSeeder::class);
        $this->seed(PizzaCaseSeeder::class);
        $this->seed(ChronicleSeeder::class);
    }

    public function test_guest_cannot_open_case_studies(): void
    {
        $this->get('/cases')->assertRedirect(route('login'));
    }

    public function test_student_sees_all_case_studies(): void
    {
        $student = User::factory()->create();

        $this->actingAs($student)->get('/cases')
            ->assertOk()
            ->assertSee('Разборы проектов')
            ->assertSee('Как собрали «Конференцию»')
            ->assertSee('Как собрали «Буквоежку»')
            ->assertSee('Как собрали «Я буду кушац»')
            ->assertSee('Как собрали «Волшебную пиццу»');

        $this->assertSame(4, CaseStudy::count());
    }

    public function test_all_cases_have_nine_chapters_and_full_files(): void
    {
        $expectedFiles = [
            'conference-build' => 28,
            'bookexchange-build' => 25,
            'restaurant-build' => 25,
            'pizza-build' => 28,
        ];

        foreach ($expectedFiles as $slug => $filesCount) {
            $case = CaseStudy::where('slug', $slug)->firstOrFail();

            $this->assertCount(9, $case->chapters, $slug);

            $files = 0;
            foreach ($case->chapters as $chapter) {
                $files += count($chapter['files'] ?? []);
            }

            $this->assertSame($filesCount, $files, $slug);
        }
    }

    public function test_conference_case_shows_chapters_commands_and_full_code(): void
    {
        $student = User::factory()->create();
        $case = CaseStudy::where('slug', 'conference-build')->firstOrFail();

        $this->actingAs($student)->get(route('cases.show', $case))
            ->assertOk()
            ->assertSee('Глава 1 из 9')
            ->assertSee('Что делать')
            ->assertSee('Дальше');

        $this->actingAs($student)->get(route('cases.show', ['case' => $case, 'chapter' => 3]))
            ->assertOk()
            ->assertSee('Миграции')
            ->assertSee('php artisan migrate')
            ->assertSee('create_applications_table')
            ->assertSee('Назад');

        $this->actingAs($student)->get(route('cases.show', ['case' => $case, 'chapter' => 9]))
            ->assertOk()
            ->assertSee('Глава 9 из 9')
            ->assertSee('Что проверяет эксперт')
            ->assertSee('Типичные ошибки');
    }

    public function test_book_case_keeps_real_requirements(): void
    {
        $student = User::factory()->create();
        $case = CaseStudy::where('slug', 'bookexchange-build')->firstOrFail();

        $this->actingAs($student)->get(route('cases.show', $case))
            ->assertOk()
            ->assertSee('Буквоежку')
            ->assertSee('Что делать');

        $this->actingAs($student)->get(route('cases.show', ['case' => $case, 'chapter' => 3]))
            ->assertOk()
            ->assertSee('rejection_reason');

        $this->actingAs($student)->get(route('cases.show', ['case' => $case, 'chapter' => 7]))
            ->assertOk()
            ->assertSee('Карточка уходит на проверку');

        $this->actingAs($student)->get(route('cases.show', ['case' => $case, 'chapter' => 9]))
            ->assertOk()
            ->assertSee('CardTest.php');
    }

    public function test_chronicle_shows_full_code_and_release(): void
    {
        $student = User::factory()->create();
        $case = CaseStudy::where('slug', 'conference-build')->firstOrFail();

        $this->actingAs($student)->get(route('cases.chronicle', $case))
            ->assertOk()
            ->assertSee('Хроника сборки')
            ->assertSee('Релиз')
            ->assertSee('git tag v1.0.0')
            ->assertSee('Schema::create')
            ->assertSee('class AuthController');
    }
}
