<?php

namespace Tests\Feature;

use App\Models\ExamSession;
use App\Models\ProjectVariant;
use App\Models\User;
use Database\Seeders\ProjectVariantSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ProjectVariantSeeder::class);
    }

    public function test_guest_cannot_open_exam(): void
    {
        $this->get('/exam')->assertRedirect(route('login'));
    }

    public function test_student_sees_exam_start_and_history(): void
    {
        $student = User::factory()->create();

        $this->actingAs($student)->get('/exam')
            ->assertOk()
            ->assertSee('Экзамен 3:30')
            ->assertSee('Конференция «Цифра-2027»')
            ->assertSee('Случайная тема');
    }

    public function test_student_can_start_exam_with_random_variant(): void
    {
        $student = User::factory()->create();

        $this->actingAs($student)->post(route('exam.start'))
            ->assertRedirect();

        $session = ExamSession::firstOrFail();

        $this->assertSame($student->id, $session->user_id);
        $this->assertNull($session->finished_at);
        $this->assertNotNull($session->variant);
    }

    public function test_student_can_start_exam_with_chosen_variant(): void
    {
        $student = User::factory()->create();
        $variant = ProjectVariant::where('slug', 'medcenter')->firstOrFail();

        $this->actingAs($student)->post(route('exam.start'), [
            'variant_id' => $variant->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('exam_sessions', [
            'user_id' => $student->id,
            'project_variant_id' => $variant->id,
        ]);
    }

    public function test_other_student_cannot_open_someone_elses_exam(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $variant = ProjectVariant::firstOrFail();

        $session = ExamSession::create([
            'user_id' => $owner->id,
            'project_variant_id' => $variant->id,
            'started_at' => now(),
        ]);

        $this->actingAs($other)->get(route('exam.show', $session))->assertForbidden();
    }

    public function test_finish_saves_checklist_and_score(): void
    {
        $student = User::factory()->create();
        $variant = ProjectVariant::firstOrFail();

        $session = ExamSession::create([
            'user_id' => $student->id,
            'project_variant_id' => $variant->id,
            'started_at' => now(),
        ]);

        $this->actingAs($student)->post(route('exam.finish', $session), [
            'checklist' => ['boot', 'register', 'auth'],
            'notes' => 'Не успел отзыв',
        ])->assertRedirect(route('exam.result', $session));

        $session->refresh();

        $this->assertNotNull($session->finished_at);
        $this->assertSame(15, $session->score);
        $this->assertSame(['boot', 'register', 'auth'], $session->checklist);
        $this->assertSame('Не успел отзыв', $session->notes);
    }

    public function test_result_shows_score_and_missing_items(): void
    {
        $student = User::factory()->create();
        $variant = ProjectVariant::firstOrFail();

        $session = ExamSession::create([
            'user_id' => $student->id,
            'project_variant_id' => $variant->id,
            'started_at' => now()->subMinutes(40),
            'finished_at' => now(),
            'checklist' => ['boot', 'register'],
            'score' => 10,
        ]);

        $this->actingAs($student)->get(route('exam.result', $session))
            ->assertOk()
            ->assertSee('10')
            ->assertSee('из 75')
            ->assertSee('Рано, вернитесь к практике');
    }

    public function test_finished_exam_redirects_from_run_page(): void
    {
        $student = User::factory()->create();
        $variant = ProjectVariant::firstOrFail();

        $session = ExamSession::create([
            'user_id' => $student->id,
            'project_variant_id' => $variant->id,
            'started_at' => now()->subHour(),
            'finished_at' => now(),
            'checklist' => [],
            'score' => 0,
        ]);

        $this->actingAs($student)->get(route('exam.show', $session))
            ->assertRedirect(route('exam.result', $session));
    }
}
