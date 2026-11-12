<?php

namespace App\Http\Controllers;

use App\Models\CapstoneSubmission;
use App\Models\Lab;
use App\Models\LabSubmission;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function index()
    {
        $students = User::where('role', 'student')->with('state')->get();
        $totalLessons = Lesson::where('is_published', true)->count();

        $completedTotal = 0;
        $activeStudents = 0;

        foreach ($students as $student) {
            $completedTotal += $this->completedCount($student);

            if ($student->last_seen_at && $student->last_seen_at->greaterThan(now()->subDays(7))) {
                $activeStudents++;
            }
        }

        $average = $students->count() > 0
            ? round($completedTotal / max(1, $students->count() * $totalLessons) * 100)
            : 0;

        return view('admin.dashboard', [
            'studentsCount' => $students->count(),
            'lessonsCount' => $totalLessons,
            'modulesCount' => Module::count(),
            'average' => $average,
            'activeStudents' => $activeStudents,
            'recentStudents' => $students->sortByDesc('last_seen_at')->take(6),
            'totalLessons' => $totalLessons,
            'modules' => Module::withCount(['lessons', 'publishedLessons'])->orderBy('position')->get(),
            'labsCount' => Lab::count(),
            'labPassed' => LabSubmission::where('status', 'passed')->count(),
            'labStudents' => LabSubmission::where('status', 'passed')->distinct()->count('user_id'),
        ]);
    }

    public function students()
    {
        $students = User::where('role', 'student')
            ->with('state')
            ->orderByDesc('last_seen_at')
            ->get();

        $totalLessons = Lesson::where('is_published', true)->count();

        return view('admin.students', compact('students', 'totalLessons'));
    }

    public function student(User $user)
    {
        abort_unless($user->role === 'student', 404);

        $state = $user->state?->data ?? [];
        $done = array_keys(array_filter($state['done'] ?? []));
        $lessons = Lesson::whereIn('slug', $done)->get()->keyBy('slug');
        $totalLessons = Lesson::where('is_published', true)->count();

        return view('admin.student', [
            'student' => $user,
            'state' => $state,
            'done' => $done,
            'lessons' => $lessons,
            'totalLessons' => $totalLessons,
            'quizCount' => count($state['quiz'] ?? []),
            'notesCount' => count(array_filter($state['notes'] ?? [])),
            'project' => $user->project,
            'labSubmissions' => LabSubmission::with('lab')
                ->where('user_id', $user->id)
                ->orderByDesc('updated_at')
                ->get(),
        ]);
    }

    public function lessons()
    {
        $lessons = Lesson::with('module')
            ->orderBy('module_id')
            ->orderBy('position')
            ->get();

        return view('admin.lessons', compact('lessons'));
    }

    public function labs()
    {
        $labs = Lab::with('module')->orderBy('position')->get();

        return view('admin.labs', compact('labs'));
    }

    public function editLab(Lab $lab)
    {
        return view('admin.lab-form', compact('lab'));
    }

    public function updateLab(Request $request, Lab $lab)
    {        $data = $request->validate([
            'module_id' => ['nullable', 'exists:modules,id'],
            'lesson_slug' => ['nullable', 'string', 'max:80'],
            'title' => ['required', 'string', 'max:255'],
            'minutes' => ['required', 'integer', 'between:1,180'],
            'level' => ['required', 'in:база,средний,практика'],
            'goal' => ['required', 'string', 'max:2000'],
            'brief' => ['required', 'string'],
            'steps' => ['nullable', 'string'],
            'starter_code' => ['nullable', 'string'],
            'language' => ['required', 'in:html,css,php,sql,blade,javascript,text'],
            'hints' => ['nullable', 'string'],
            'solution' => ['nullable', 'string'],
            'checks' => ['required', 'string'],
            'is_published' => ['nullable', 'boolean'],
        ], [
            'checks.required' => 'Правила проверки обязательны (JSON).',
        ]);

        $checks = json_decode($data['checks'], true);

        if (! is_array($checks)) {
            throw ValidationException::withMessages(['checks' => 'Правила проверки: некорректный JSON.']);
        }

        $lab->update([
            'module_id' => $data['module_id'] ?? null,
            'lesson_slug' => $data['lesson_slug'] ?? null,
            'title' => $data['title'],
            'minutes' => $data['minutes'],
            'level' => $data['level'],
            'goal' => $data['goal'],
            'brief' => $data['brief'],
            'steps' => $this->lines($data['steps'] ?? ''),
            'starter_code' => $data['starter_code'] ?? '',
            'language' => $data['language'],
            'hints' => $this->lines($data['hints'] ?? ''),
            'solution' => $data['solution'] ?? '',
            'checks' => $checks,
            'is_published' => $request->boolean('is_published'),
        ]);

        return redirect()->route('admin.labs')->with('success', 'Лабораторная обновлена.');
    }

    public function createLesson()
    {
        return view('admin.lesson-form', [
            'lesson' => new Lesson([
                'minutes' => 15,
                'level' => 'база',
                'is_published' => true,
                'tags' => [],
                'blocks' => [],
                'practice' => [],
                'quiz' => [],
            ]),
            'modules' => Module::orderBy('position')->get(),
            'action' => route('admin.lessons.store'),
            'method' => 'POST',
        ]);
    }

    public function storeLesson(Request $request)
    {
        $data = $this->validateLesson($request);
        $moduleId = $request->integer('module_id');

        Lesson::create([
            ...$data,
            'module_id' => $moduleId,
            'position' => (int) Lesson::where('module_id', $moduleId)->max('position') + 1,
        ]);

        return redirect()->route('admin.lessons')->with('success', 'Урок создан.');
    }

    public function editLesson(Lesson $lesson)
    {
        return view('admin.lesson-form', [
            'lesson' => $lesson,
            'modules' => Module::orderBy('position')->get(),
            'action' => route('admin.lessons.update', $lesson),
            'method' => 'PUT',
        ]);
    }

    public function updateLesson(Request $request, Lesson $lesson)
    {
        $lesson->update($this->validateLesson($request, $lesson));

        return redirect()->route('admin.lessons')->with('success', 'Урок обновлён.');
    }

    public function destroyLesson(Lesson $lesson)
    {
        $lesson->delete();

        return redirect()->route('admin.lessons')->with('success', 'Урок удалён.');
    }

    public function capstone()
    {
        $submissions = CapstoneSubmission::with('user')->latest()->get();

        return view('admin.capstone', compact('submissions'));
    }

    public function capstoneShow(CapstoneSubmission $submission)
    {
        $submission->load('user');

        return view('admin.capstone-show', compact('submission'));
    }

    private function validateLesson(Request $request, ?Lesson $lesson = null): array
    {
        $data = $request->validate([
            'module_id' => ['required', 'exists:modules,id'],
            'slug' => [
                'required',
                'regex:/^[a-z0-9-]+$/',
                'max:80',
                Rule::unique('lessons', 'slug')->ignore($lesson?->id),
            ],
            'title' => ['required', 'string', 'max:255'],
            'minutes' => ['required', 'integer', 'between:1,180'],
            'level' => ['required', 'in:база,средний,практика'],
            'tags' => ['nullable', 'string', 'max:255'],
            'goal' => ['required', 'string', 'max:2000'],
            'practice' => ['nullable', 'string'],
            'blocks' => ['required', 'string'],
            'quiz' => ['required', 'string'],
            'is_published' => ['nullable', 'boolean'],
        ], [
            'slug.regex' => 'Slug: только латиница, цифры и дефис.',
            'slug.unique' => 'Такой slug уже занят.',
            'module_id.exists' => 'Выберите существующий модуль.',
            'blocks.required' => 'Блоки урока обязательны (JSON-массив).',
            'quiz.required' => 'Вопросы обязательны (JSON-массив).',
        ]);

        $blocks = json_decode($data['blocks'], true);
        $quiz = json_decode($data['quiz'], true);

        if (! is_array($blocks)) {
            throw ValidationException::withMessages(['blocks' => 'Блоки: некорректный JSON.']);
        }

        if (! is_array($quiz)) {
            throw ValidationException::withMessages(['quiz' => 'Вопросы: некорректный JSON.']);
        }

        $practice = collect(preg_split('/\r\n|\r|\n/', $data['practice'] ?? ''))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();

        $tags = collect(explode(',', $data['tags'] ?? ''))
            ->map(fn ($tag) => trim($tag))
            ->filter()
            ->values()
            ->all();

        return [
            'slug' => $data['slug'],
            'title' => $data['title'],
            'minutes' => $data['minutes'],
            'level' => $data['level'],
            'tags' => $tags,
            'goal' => $data['goal'],
            'blocks' => $blocks,
            'practice' => $practice,
            'quiz' => $quiz,
            'is_published' => $request->boolean('is_published'),
        ];
    }

    private function completedCount(User $user): int
    {
        $data = $user->state?->data ?? [];
        $done = $data['done'] ?? [];

        return count(array_filter($done));
    }

    private function lines(string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $value))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }
}
