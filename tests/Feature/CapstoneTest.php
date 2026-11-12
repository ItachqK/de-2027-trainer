<?php

namespace Tests\Feature;

use App\Models\CapstoneSubmission;
use App\Models\User;
use App\Services\CapstoneAuditor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class CapstoneTest extends TestCase
{
    use RefreshDatabase;

    private string $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->project = storage_path('app/projects/capstone-test');

        File::deleteDirectory($this->project);
        File::ensureDirectoryExists($this->project);

        $this->write('artisan', "<?php\n");
        $this->write('composer.json', '{}');
        $this->write('public/index.php', "<?php\n");
        $this->write('app/Http/Controllers/AuthController.php', <<<'PHP'
<?php
class AuthController {
    public function login() {
        if (Auth::attempt(['login' => $login, 'password' => $password])) {
            session()->regenerate();
        }
        Hash::make($password);
        if (auth()->user()->isAdmin()) { abort(403); }
        Auth::logout();
    }
}
PHP);
        $this->write('app/Http/Controllers/AdminController.php', <<<'PHP'
<?php
class AdminController {
    public function index(Request $request) {
        $query = Application::where('user_id', auth()->id());
        $query->when($request->filled('status'), fn ($q) => $q->where('status', $request->status));
        $query->when($request->filled('search'), fn ($q) => $q->where('fio', 'like', '%' . $request->search . '%'));
        $application->update(['status' => 'completed']);
        $application->review;
    }
}
PHP);
        $this->write('routes/web.php', <<<'PHP'
<?php
Route::get('/register', [AuthController::class, 'register']);
Route::get('/login', [AuthController::class, 'login']);
Route::get('/profile', [ApplicationController::class, 'index']);
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index']);
    Route::post('/status', [AdminController::class, 'status']);
});
PHP);
        $this->write('database/migrations/2026_01_01_000001_create_users_table.php', "<?php\nSchema::create('users', function (Blueprint \$table) { \$table->id(); \$table->timestamps(); });");
        $this->write('database/migrations/2026_01_01_000002_create_rooms_table.php', "<?php\nSchema::create('rooms', function (Blueprint \$table) { \$table->id(); \$table->timestamps(); });");
        $this->write('database/migrations/2026_01_01_000003_create_applications_table.php', "<?php\nSchema::create('applications', function (Blueprint \$table) { \$table->foreignId('user_id')->constrained(); \$table->timestamps(); });");
        $this->write('resources/views/profile.blade.php', <<<'BLADE'
<form method="POST">@csrf
<select name="room_id"><option>1</option></select>
@error('room_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
<input value="{{ old('room_id') }}">
<img src="/img/room.jpg" alt="Комната">
@forelse ($items as $item) <span class="badge">{{ $item->status }}</span> @empty нет заявок @endforelse
@if ($application->status === 'completed') {{ $application->review }} @endif
BLADE);
        $this->write('resources/css/app.css', ':root { --brand: #007bff; } @media (max-width: 768px) { .x { display: none; } } .btn { transition: all .2s; }');
        $this->write('docs/er.svg', '<svg></svg>');
        $this->write('README.md', "php artisan serve\nphp artisan migrate --seed\nphp artisan storage:link\n");
        $this->write('.env.example', 'APP_NAME=Demo');
        $this->write('.gitignore', ".env\n/vendor\n");
        $this->write('tests/Feature/ExampleTest.php', "<?php\n");
        $this->write('public/img/room.jpg', 'x');

        foreach (range(1, 5) as $i) {
            $this->write("mobile-design/page{$i}.html", '<meta name="viewport" content="width=390">');
        }

        File::ensureDirectoryExists($this->project.'/.git');
        File::put($this->project.'/.git/HEAD', 'ref: refs/heads/main');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->project);

        parent::tearDown();
    }

    public function test_auditor_reads_real_project_and_returns_criteria(): void
    {
        $result = app(CapstoneAuditor::class)->audit($this->project);

        $this->assertSame(75, $result['max']);
        $this->assertGreaterThanOrEqual(60, $result['score']);
        $this->assertNotEmpty($result['checks']);
        $this->assertArrayHasKey('Безопасность', $result['groups']);
        $this->assertArrayHasKey('Дизайн-концепция', $result['groups']);
    }

    public function test_student_can_run_audit_and_submission_is_saved(): void
    {
        $student = User::factory()->create();

        $response = $this->actingAs($student)->post('/capstone', [
            'project_path' => $this->project,
            'deploy_url' => 'http://127.0.0.1:8000',
            'commit_hash' => 'a1b2c3d',
        ]);

        $response->assertRedirect(route('capstone.index'));

        $submission = CapstoneSubmission::where('user_id', $student->id)->firstOrFail();
        $this->assertGreaterThan(0, $submission->score);
        $this->assertSame(75, $submission->max_score);
    }

    public function test_path_outside_allowed_roots_is_rejected(): void
    {
        $student = User::factory()->create();
        $outside = sys_get_temp_dir();

        $response = $this->actingAs($student)->from('/capstone')->post('/capstone', [
            'project_path' => $outside,
        ]);

        $response->assertRedirect('/capstone');
        $response->assertSessionHasErrors('project_path');
        $this->assertDatabaseCount('capstone_submissions', 0);
    }

    public function test_run_project_result_is_saved(): void
    {
        $student = User::factory()->create();

        $this->mock(\App\Services\ProjectRunner::class, function ($mock) {
            $mock->shouldReceive('run')->once()->andReturn([
                'ran' => true,
                'passed' => true,
                'steps' => [['label' => 'Сервер', 'passed' => true, 'message' => 'ok', 'skipped' => false]],
                'outputs' => [],
                'duration' => 12.3,
            ]);
        });

        $this->actingAs($student)->post('/capstone', [
            'project_path' => $this->project,
            'run_project' => '1',
        ])->assertRedirect(route('capstone.index'));

        $submission = CapstoneSubmission::where('user_id', $student->id)->firstOrFail();
        $this->assertTrue($submission->runtime_passed);
        $this->assertSame('ok', $submission->runtime['steps'][0]['message']);
    }

    public function test_admin_can_view_capstone_submissions(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create();

        $submission = CapstoneSubmission::create([
            'user_id' => $student->id,
            'project_path' => $this->project,
            'score' => 50,
            'max_score' => 75,
            'results' => ['score' => 50, 'max' => 75, 'groups' => [], 'checks' => []],
        ]);

        $this->actingAs($admin)->get('/admin/capstone')->assertOk()->assertSee($student->fio);
        $this->actingAs($admin)->get(route('admin.capstone.show', $submission))->assertOk();
    }

    private function write(string $path, string $content): void
    {
        $full = $this->project.'/'.$path;
        File::ensureDirectoryExists(dirname($full));
        File::put($full, $content);
    }
}
