<?php

namespace Tests\Feature;

use App\Models\Lab;
use App\Models\User;
use Database\Seeders\CourseSeeder;
use Database\Seeders\LabSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LabTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CourseSeeder::class);
        $this->seed(LabSeeder::class);
    }

    public function test_guest_cannot_open_practice(): void
    {
        $this->get('/labs')->assertRedirect(route('login'));
    }

    public function test_student_can_open_practice_and_lab(): void
    {
        $student = User::factory()->create();
        $lab = Lab::where('slug', 'html-application-card')->firstOrFail();

        $this->actingAs($student)->get('/labs')->assertOk()->assertSee('Практикум');
        $this->actingAs($student)->get(route('labs.show', $lab))->assertOk()->assertSee($lab->title);
    }

    public function test_student_can_pass_lab_and_submission_is_saved(): void
    {
        $student = User::factory()->create();
        $lab = Lab::where('slug', 'html-application-card')->firstOrFail();

        $response = $this->actingAs($student)->post(route('labs.check', $lab), [
            'code' => $lab->solution,
        ]);

        $response->assertRedirect(route('labs.show', $lab).'#result');

        $this->assertDatabaseHas('lab_submissions', [
            'user_id' => $student->id,
            'lab_id' => $lab->id,
            'status' => 'passed',
            'attempts' => 1,
        ]);
    }

    public function test_wrong_solution_is_saved_as_failed(): void
    {
        $student = User::factory()->create();
        $lab = Lab::where('slug', 'html-application-card')->firstOrFail();

        $this->actingAs($student)->post(route('labs.check', $lab), [
            'code' => '<article></article>',
        ]);

        $this->assertDatabaseHas('lab_submissions', [
            'user_id' => $student->id,
            'lab_id' => $lab->id,
            'status' => 'failed',
        ]);
    }

    public function test_sql_lab_runs_query_against_fixture(): void
    {
        $student = User::factory()->create();
        $lab = Lab::where('slug', 'sql-user-applications')->firstOrFail();

        $this->actingAs($student)->post(route('labs.check', $lab), [
            'code' => $lab->solution,
        ]);

        $this->assertDatabaseHas('lab_submissions', [
            'user_id' => $student->id,
            'lab_id' => $lab->id,
            'status' => 'passed',
        ]);
    }

    public function test_admin_can_edit_lab(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lab = Lab::where('slug', 'html-application-card')->firstOrFail();

        $this->actingAs($admin)->get('/admin/labs')->assertOk()->assertSee($lab->title);
        $this->actingAs($admin)->get(route('admin.labs.edit', $lab))->assertOk();

        $response = $this->actingAs($admin)->put(route('admin.labs.update', $lab), [
            'module_id' => $lab->module_id,
            'lesson_slug' => $lab->lesson_slug,
            'title' => 'Карточка заявки',
            'minutes' => 15,
            'level' => 'база',
            'goal' => $lab->goal,
            'brief' => $lab->brief,
            'steps' => "Шаг 1\nШаг 2",
            'starter_code' => $lab->starter_code,
            'language' => $lab->language,
            'hints' => "Подсказка 1",
            'solution' => $lab->solution,
            'checks' => json_encode($lab->checks),
            'is_published' => '1',
        ]);

        $response->assertRedirect(route('admin.labs'));

        $lab->refresh();
        $this->assertSame('Карточка заявки', $lab->title);
        $this->assertCount(2, $lab->steps);
    }
}
