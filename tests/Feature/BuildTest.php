<?php

namespace Tests\Feature;

use App\Models\BuildStep;
use App\Models\User;
use App\Services\LabChecker;
use Database\Seeders\BuildStepSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

class BuildTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(BuildStepSeeder::class);
    }

    public function test_guest_cannot_open_build_mode(): void
    {
        $this->get('/build')->assertRedirect(route('login'));
    }

    public function test_student_sees_all_build_steps(): void
    {
        $student = User::factory()->create();

        $this->actingAs($student)->get('/build')
            ->assertOk()
            ->assertSee('Сборка с нуля')
            ->assertSee('Каркас и база')
            ->assertSee('Маршруты, тесты и сдача');

        $this->assertSame(10, BuildStep::count());
    }

    public function test_step_page_shows_commands_and_full_files(): void
    {
        $student = User::factory()->create();
        $step = BuildStep::where('slug', 'database')->firstOrFail();

        $this->actingAs($student)->get(route('build.show', $step))
            ->assertOk()
            ->assertSee('Команды')
            ->assertSee('php artisan migrate')
            ->assertSee('create_applications_table')
            ->assertSee('Файлы');
    }

    public function test_student_can_complete_step_and_progress_is_saved(): void
    {
        $student = User::factory()->create();
        $step = BuildStep::where('slug', 'prepare')->firstOrFail();
        $next = BuildStep::where('position', 1)->firstOrFail();

        $this->actingAs($student)->post(route('build.complete', $step))
            ->assertRedirect(route('build.show', $next));

        $this->assertDatabaseHas('build_progress', [
            'user_id' => $student->id,
            'build_step_id' => $step->id,
        ]);
    }

    public function test_progress_can_be_reset(): void
    {
        $student = User::factory()->create();
        $step = BuildStep::firstOrFail();

        $this->actingAs($student)->post(route('build.complete', $step));
        $this->actingAs($student)->post(route('build.reset'));

        $this->assertDatabaseCount('build_progress', 0);
    }

    public function test_reference_zip_can_be_downloaded(): void
    {
        $student = User::factory()->create();

        $response = $this->actingAs($student)->get(route('build.download'));

        $response->assertOk();
        $this->assertStringContainsString('conference-reference.zip', (string) $response->headers->get('content-disposition'));

        $file = $response->baseResponse->getFile();
        $this->assertNotNull($file);

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($file->getPathname()) === true);
        $this->assertNotFalse($zip->locateName('conference/routes/web.php'));
        $this->assertNotFalse($zip->locateName('conference/app/Http/Controllers/AdminController.php'));
        $zip->close();
    }

    public function test_all_php_files_in_reference_pass_syntax_check(): void
    {
        $source = require database_path('seeders/data/build-files.php');
        $checker = app(LabChecker::class);

        foreach ($source as $path => $file) {
            if ($file['language'] !== 'php') {
                continue;
            }

            $result = $checker->checkRules(['php_lint' => true], $file['code']);

            $this->assertTrue($result['passed'], $path.' содержит синтаксическую ошибку.');
        }
    }
}
