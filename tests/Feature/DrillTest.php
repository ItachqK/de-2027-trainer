<?php

namespace Tests\Feature;

use App\Models\Drill;
use App\Models\DrillProgress;
use App\Models\User;
use Database\Seeders\DrillSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DrillTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DrillSeeder::class);
    }

    public function test_guest_cannot_open_drills(): void
    {
        $this->get('/drills')->assertRedirect(route('login'));
    }

    public function test_student_sees_daily_queue_and_tracks(): void
    {
        $student = User::factory()->create();

        $this->actingAs($student)->get('/drills')
            ->assertOk()
            ->assertSee('Тренажёр')
            ->assertSee('На сегодня')
            ->assertSee('HTML')
            ->assertSee('Laravel');
    }

    public function test_student_can_pass_drill_and_progress_is_saved(): void
    {
        $student = User::factory()->create();
        $drill = Drill::where('slug', 'conference-card')->firstOrFail();

        $this->actingAs($student)->post(route('drills.check', $drill), [
            'code' => $drill->solution,
        ])->assertRedirect(route('drills.show', $drill).'#result');

        $this->assertDatabaseHas('drill_attempts', [
            'user_id' => $student->id,
            'drill_id' => $drill->id,
            'passed' => true,
        ]);

        $progress = DrillProgress::firstOrFail();

        $this->assertSame(1, $progress->attempts);
        $this->assertSame(1, $progress->passes);
        $this->assertSame(1, $progress->streak);
        $this->assertNotNull($progress->next_review_at);
        $this->assertTrue($progress->next_review_at->isFuture());
    }

    public function test_failed_attempt_resets_streak_and_schedules_retry(): void
    {
        $student = User::factory()->create();
        $drill = Drill::where('slug', 'conference-card')->firstOrFail();

        $this->actingAs($student)->post(route('drills.check', $drill), [
            'code' => '<article></article>',
        ]);

        $progress = DrillProgress::firstOrFail();

        $this->assertSame(1, $progress->attempts);
        $this->assertSame(0, $progress->passes);
        $this->assertSame(0, $progress->streak);
        $this->assertNotNull($progress->next_review_at);
    }

    public function test_due_review_returns_to_daily_queue(): void
    {
        $student = User::factory()->create();
        $drill = Drill::where('slug', 'grid-2-3')->firstOrFail();

        DrillProgress::create([
            'user_id' => $student->id,
            'drill_id' => $drill->id,
            'attempts' => 1,
            'passes' => 1,
            'streak' => 1,
            'last_passed_at' => now()->subDays(2),
            'next_review_at' => now()->subDay(),
        ]);

        $this->actingAs($student)->get('/drills')
            ->assertOk()
            ->assertSee('пора повторить')
            ->assertSee($drill->title);
    }

    public function test_repeat_pass_increases_review_interval(): void
    {
        $student = User::factory()->create();
        $drill = Drill::where('slug', 'grid-2-3')->firstOrFail();

        $this->actingAs($student)->post(route('drills.check', $drill), [
            'code' => $drill->solution,
        ]);

        $this->actingAs($student)->post(route('drills.check', $drill), [
            'code' => $drill->solution,
        ]);

        $progress = DrillProgress::firstOrFail();

        $this->assertSame(2, $progress->passes);
        $this->assertSame(2, $progress->streak);
        $this->assertTrue($progress->next_review_at->greaterThan(now()->addDays(2)));
    }

    public function test_solution_is_hidden_before_pass_and_visible_after(): void
    {
        $student = User::factory()->create();
        $drill = Drill::where('slug', 'conference-card')->firstOrFail();

        $this->actingAs($student)->get(route('drills.show', $drill))
            ->assertOk()
            ->assertDontSee('Показать решение');

        $this->actingAs($student)->post(route('drills.check', $drill), [
            'code' => $drill->solution,
        ]);

        $this->actingAs($student)->get(route('drills.show', $drill))
            ->assertOk()
            ->assertSee('Показать решение');
    }

    public function test_next_redirects_to_a_drill(): void
    {
        $student = User::factory()->create();

        $this->actingAs($student)->get('/drills/next')
            ->assertRedirect();

        $this->assertStringContainsString('/drills/', (string) $this->get('/drills/next')->headers->get('Location'));
    }

    public function test_drill_page_renders_editor_and_requirements(): void
    {
        $student = User::factory()->create();
        $drill = Drill::where('slug', 'sql-status-counts')->firstOrFail();

        $this->actingAs($student)->get(route('drills.show', $drill))
            ->assertOk()
            ->assertSee('Требования')
            ->assertSee('id="code"', false)
            ->assertSee('Проверить');
    }
}
