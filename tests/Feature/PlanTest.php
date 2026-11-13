<?php

namespace Tests\Feature;

use App\Models\CapstoneSubmission;
use App\Models\Drill;
use App\Models\DrillProgress;
use App\Models\ExamSession;
use App\Models\Lab;
use App\Models\LabSubmission;
use App\Models\Lesson;
use App\Models\ProjectVariant;
use App\Models\User;
use App\Models\UserState;
use App\Services\PlanBuilder;
use Database\Seeders\CourseSeeder;
use Database\Seeders\DrillSeeder;
use Database\Seeders\LabSeeder;
use Database\Seeders\ProjectVariantSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PlanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // План привязан к календарю, поэтому время в тестах заморожено.
        Carbon::setTestNow('2026-11-13 12:00:00');
        $this->seed(CourseSeeder::class);
        $this->seed(DrillSeeder::class);
        $this->seed(LabSeeder::class);
        $this->seed(ProjectVariantSeeder::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_guest_cannot_open_plan(): void
    {
        $this->get('/plan')->assertRedirect(route('login'));
    }

    public function test_fresh_student_sees_zero_readiness_and_next_tasks(): void
    {
        $student = User::factory()->create();

        $this->actingAs($student)->get('/plan')
            ->assertOk()
            ->assertSee('План на сегодня')
            ->assertSee('0%')
            ->assertSee('Теория')
            ->assertSee('Тренажёр')
            ->assertSee('Лабораторные')
            ->assertSee('Экзамен и аудит')
            ->assertSee('На сегодня')
            ->assertSee('План на 15 недель');
    }

    public function test_weights_add_up_to_one_hundred(): void
    {
        $this->assertSame(100, array_sum(PlanBuilder::WEIGHTS));
    }

    public function test_readiness_grows_with_real_progress(): void
    {
        $student = User::factory()->create();
        $plan = app(PlanBuilder::class)->build($student);

        $this->assertSame(0, $plan['readiness']);
        $this->assertCount(3, $plan['tasks']);
        $this->assertTrue($plan['tasks'][0]['primary']);

        // Теория: один урок из курса.
        $lesson = Lesson::where('is_published', true)->firstOrFail();
        UserState::create([
            'user_id' => $student->id,
            'data' => ['done' => [$lesson->slug => true]],
        ]);

        // Тренажёр: один решённый вариант.
        $drill = Drill::where('is_published', true)->firstOrFail();
        DrillProgress::create([
            'user_id' => $student->id,
            'drill_id' => $drill->id,
            'attempts' => 1,
            'passes' => 1,
            'streak' => 1,
            'last_passed_at' => now(),
            'next_review_at' => now()->addDay(),
        ]);

        // Лабораторная: одна сданная.
        $lab = Lab::where('is_published', true)->firstOrFail();
        LabSubmission::create([
            'user_id' => $student->id,
            'lab_id' => $lab->id,
            'code' => 'ok',
            'status' => 'passed',
            'results' => [],
            'attempts' => 1,
        ]);

        // Экзамен: один завершённый прогон.
        ExamSession::create([
            'user_id' => $student->id,
            'project_variant_id' => ProjectVariant::firstOrFail()->id,
            'started_at' => now()->subHour(),
            'finished_at' => now(),
            'checklist' => [],
            'score' => 40,
        ]);

        $plan = app(PlanBuilder::class)->build($student->fresh());

        // 1/46 + 1/33 + 1/14 по 25% плюс половина блока за прогон = 15.6 → 16.
        $this->assertSame(16, $plan['readiness']);
        $this->assertSame(1, $plan['blocks'][0]['done']);
        $this->assertSame(1, $plan['blocks'][1]['done']);
        $this->assertSame(1, $plan['blocks'][2]['done']);
        $this->assertSame(1, $plan['blocks'][3]['done']);

        // Аудит на 75 из 75 закрывает вторую половину блока — 28 из 100.
        CapstoneSubmission::create([
            'user_id' => $student->id,
            'project_path' => 'C:/Temp/project',
            'score' => 75,
            'max_score' => 75,
            'results' => [],
        ]);

        $plan = app(PlanBuilder::class)->build($student->fresh());
        $this->assertSame(28, $plan['readiness']);
    }

    public function test_audit_is_a_separate_task_after_exam_run(): void
    {
        $student = User::factory()->create();

        ExamSession::create([
            'user_id' => $student->id,
            'project_variant_id' => ProjectVariant::firstOrFail()->id,
            'started_at' => now()->subHour(),
            'finished_at' => now(),
            'checklist' => [],
            'score' => 40,
        ]);

        $plan = app(PlanBuilder::class)->build($student->fresh());
        $kinds = collect($plan['tasks'])->pluck('kind')->all();

        // Прогон есть, аудита нет: блок «Экзамен и аудит» закрывается только аудитом.
        $this->assertContains('аудит', $kinds);
        $audit = collect($plan['tasks'])->firstWhere('kind', 'аудит');
        $this->assertSame(route('capstone.index'), $audit['url']);
    }

    public function test_audit_adds_half_of_exam_block(): void
    {
        $student = User::factory()->create();

        CapstoneSubmission::create([
            'user_id' => $student->id,
            'project_path' => 'C:/Temp/project',
            'score' => 60,
            'max_score' => 75,
            'results' => [],
        ]);

        $plan = app(PlanBuilder::class)->build($student);
        $exam = collect($plan['blocks'])->firstWhere('key', 'exam');

        // 60 из 75 — это 80%, половина блока — 40%.
        $this->assertSame(40, $exam['percent']);
    }

    public function test_plan_dates_end_at_exam_week(): void
    {
        $student = User::factory()->create();
        $plan = app(PlanBuilder::class)->build($student);

        $last = $plan['weeks']->last();

        $this->assertSame(15, $last['num']);
        $this->assertStringContainsString('22.02–28.02', $last['dates']);
        $this->assertSame('current', $plan['weeks']->first()['state']);
    }

    public function test_current_week_stays_current_on_its_last_day(): void
    {
        $student = User::factory()->create();

        // Воскресенье 22.11.2026 — последний день первой недели.
        Carbon::setTestNow('2026-11-22 12:00:00');
        $plan = app(PlanBuilder::class)->build($student);

        $first = $plan['weeks']->first();
        $second = $plan['weeks'][1];

        $this->assertSame('current', $first['state']);
        $this->assertSame('future', $second['state']);
    }

    public function test_days_left_changes_at_moscow_midnight(): void
    {
        $student = User::factory()->create();

        // 21:30 UTC — это уже 00:30 следующего дня по Москве.
        Carbon::setTestNow('2026-11-13 21:30:00');
        $plan = app(PlanBuilder::class)->build($student);

        $this->assertSame(106, $plan['daysLeft']);
    }

    public function test_plan_page_renders_for_student(): void
    {
        $student = User::factory()->create();

        $this->actingAs($student)->get('/plan')
            ->assertOk()
            ->assertSee('2,5 ч в неделю')
            ->assertSee('Начать');
    }
}
