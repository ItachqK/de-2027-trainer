<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectVariant;
use App\Models\User;
use Database\Seeders\ProjectVariantSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VariantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ProjectVariantSeeder::class);
    }

    public function test_guest_cannot_open_variants(): void
    {
        $this->get('/variants')->assertRedirect(route('login'));
    }

    public function test_student_sees_all_project_variants(): void
    {
        $student = User::factory()->create();

        $this->actingAs($student)->get('/variants')
            ->assertOk()
            ->assertSee('Варианты ТЗ')
            ->assertSee('Конференция «Цифра-2027»')
            ->assertSee('Медцентр «Здоровье»')
            ->assertSee('Курсы «Практика»')
            ->assertSee('«Буквоежка» — обмен книгами')
            ->assertSee('«Я буду кушац» — бронирование столиков')
            ->assertSee('«Волшебная пицца» — доставка');

        $this->assertSame(9, ProjectVariant::count());
    }

    public function test_real_2025_variants_keep_their_admin_credentials(): void
    {
        $this->assertDatabaseHas('project_variants', [
            'slug' => 'bookexchange',
            'admin_login' => 'admin',
            'admin_password' => 'bookworm',
        ]);

        $this->assertDatabaseHas('project_variants', [
            'slug' => 'restaurant',
            'admin_login' => 'admin',
            'admin_password' => 'restaurant',
        ]);

        $this->assertDatabaseHas('project_variants', [
            'slug' => 'pizza',
            'admin_login' => 'aDminka25',
            'admin_password' => 'passw0rd',
        ]);
    }

    public function test_variant_page_shows_entities_statuses_and_routes(): void
    {
        $student = User::factory()->create();
        $variant = ProjectVariant::where('slug', 'conference')->firstOrFail();

        $this->actingAs($student)->get(route('variants.show', $variant))
            ->assertOk()
            ->assertSee($variant->title)
            ->assertSee('applications')
            ->assertSee('start_date')
            ->assertSee('assigned')
            ->assertSee('/admin/applications')
            ->assertSee($variant->admin_login);
    }

    public function test_student_can_select_variant_for_project(): void
    {
        $student = User::factory()->create();
        $variant = ProjectVariant::where('slug', 'medcenter')->firstOrFail();

        $this->actingAs($student)->post(route('variants.select', $variant))
            ->assertRedirect(route('variants.show', $variant));

        $this->assertDatabaseHas('projects', [
            'user_id' => $student->id,
            'variant_slug' => $variant->slug,
        ]);

        $this->actingAs($student)->get('/project')
            ->assertOk()
            ->assertSee($variant->title)
            ->assertSee('Ваш вариант');
    }

    public function test_variant_can_be_downloaded_as_markdown(): void
    {
        $student = User::factory()->create();
        $variant = ProjectVariant::where('slug', 'conference')->firstOrFail();

        $response = $this->actingAs($student)->get(route('variants.download', $variant));

        $response->assertOk();
        $this->assertStringContainsString('conference.md', (string) $response->headers->get('content-disposition'));
        $this->assertStringContainsString('# '.$variant->title, $response->streamedContent());
        $this->assertStringContainsString('## Сущности и поля', $response->streamedContent());
    }

    public function test_random_variant_redirects_to_a_variant(): void
    {
        $student = User::factory()->create();

        $response = $this->actingAs($student)->get('/variants/random');

        $response->assertRedirect();
        $this->assertStringContainsString('/variants/', (string) $response->headers->get('Location'));
    }

    public function test_project_save_does_not_reset_selected_variant(): void
    {
        $student = User::factory()->create();
        $variant = ProjectVariant::where('slug', 'rent')->firstOrFail();

        $this->actingAs($student)->post(route('variants.select', $variant));
        $this->actingAs($student)->post(route('project.save'), [
            'repo_url' => 'https://github.com/student/rent',
            'notes' => 'Готово на 70%',
        ]);

        $project = Project::where('user_id', $student->id)->firstOrFail();

        $this->assertSame($variant->slug, $project->variant_slug);
        $this->assertSame('https://github.com/student/rent', $project->repo_url);
    }
}
