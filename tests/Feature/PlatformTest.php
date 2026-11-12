<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\User;
use Database\Seeders\CourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CourseSeeder::class);
    }

    public function test_course_api_returns_seeded_content(): void
    {
        $response = $this->getJson('/api/course');

        $response->assertOk()
            ->assertJsonPath('total', 46)
            ->assertJsonCount(9, 'modules')
            ->assertJsonCount(18, 'recipes')
            ->assertJsonPath('modules.0.lessons.0.id', 'exam-format');
    }

    public function test_zero_module_covers_terminal_install_and_debugging(): void
    {
        $response = $this->getJson('/api/course');

        $response->assertOk()
            ->assertJsonPath('modules.0.title', 'Старт с нуля')
            ->assertJsonCount(9, 'modules.0.lessons');

        $slugs = collect($response->json('modules.0.lessons'))->pluck('id')->all();

        $this->assertContains('terminal-basics', $slugs);
        $this->assertContains('files-and-paths', $slugs);
        $this->assertContains('install-php-composer', $slugs);
        $this->assertContains('first-php-server', $slugs);
        $this->assertContains('php-errors', $slugs);
        $this->assertContains('debugging', $slugs);
    }

    public function test_guest_cannot_read_or_save_state(): void
    {
        $this->getJson('/api/state')->assertUnauthorized();
        $this->postJson('/api/state', ['state' => ['done' => []]])->assertUnauthorized();
    }

    public function test_authenticated_user_can_save_and_read_state(): void
    {
        $user = User::factory()->create();

        $state = [
            'done' => ['exam-format' => true],
            'checks' => ['exam-format' => ['0' => true]],
            'quiz' => [],
            'notes' => ['exam-format' => 'Заметка'],
            'last' => 'exam-format',
        ];

        $this->actingAs($user)
            ->postJson('/api/state', ['state' => $state])
            ->assertOk();

        $this->assertDatabaseHas('user_states', ['user_id' => $user->id]);

        $this->actingAs($user)
            ->getJson('/api/state')
            ->assertOk()
            ->assertJsonPath('state.done.exam-format', true)
            ->assertJsonPath('state.notes.exam-format', 'Заметка');
    }

    public function test_student_cannot_open_admin(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)->get('/admin')->assertForbidden();
    }

    public function test_admin_can_open_dashboard_and_lessons(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/admin/lessons')->assertOk()->assertSee('exam-format');
    }

    public function test_admin_can_update_lesson_blocks(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lesson = Lesson::where('slug', 'exam-format')->firstOrFail();

        $response = $this->actingAs($admin)->put(route('admin.lessons.update', $lesson), [
            'module_id' => $lesson->module_id,
            'slug' => $lesson->slug,
            'title' => 'Новое название',
            'minutes' => 20,
            'level' => 'средний',
            'tags' => 'экзамен, тест',
            'goal' => 'Новая цель урока.',
            'practice' => "Первый пункт\nВторой пункт",
            'blocks' => json_encode([['t' => 'p', 'v' => 'Текст']]),
            'quiz' => json_encode([]),
            'is_published' => '1',
        ]);

        $response->assertRedirect(route('admin.lessons'));

        $lesson->refresh();
        $this->assertSame('Новое название', $lesson->title);
        $this->assertSame(['экзамен', 'тест'], $lesson->tags);
        $this->assertCount(2, $lesson->practice);
        $this->assertTrue($lesson->is_published);
    }

    public function test_admin_sees_error_for_invalid_json(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lesson = Lesson::where('slug', 'exam-format')->firstOrFail();

        $response = $this->actingAs($admin)->from(route('admin.lessons.edit', $lesson))
            ->put(route('admin.lessons.update', $lesson), [
                'module_id' => $lesson->module_id,
                'slug' => $lesson->slug,
                'title' => $lesson->title,
                'minutes' => 10,
                'level' => 'база',
                'tags' => '',
                'goal' => $lesson->goal,
                'practice' => '',
                'blocks' => '{not json',
                'quiz' => '[]',
            ]);

        $response->assertRedirect(route('admin.lessons.edit', $lesson));
        $response->assertSessionHasErrors('blocks');
    }
}
