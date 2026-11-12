<?php

namespace App\Http\Controllers;

use App\Models\ExamSession;
use App\Models\ProjectVariant;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public const CHECKLIST = [
        'boot' => 'Проект запускается с нуля: `php artisan migrate:fresh --seed`',
        'register' => 'Регистрация: поля, валидация, уникальный email',
        'auth' => 'Вход и выход, пароль хранится хешем',
        'roles' => 'Роль student по умолчанию, admin из сидера',
        'ownership' => 'Чужая заявка недоступна: 403',
        'create' => 'Создание заявки с валидацией и old()',
        'cabinet' => 'Кабинет: список своих заявок',
        'admin' => 'Админка: все заявки и фильтр',
        'status' => 'Смена статуса по ТЗ',
        'review' => 'Отзыв и оценка после completed',
        'mobile' => 'Макет 390×844 без горизонтальной прокрутки',
        'responsive' => '768 и 1440 px, локальный Bootstrap',
        'git' => 'Коммиты и README в репозитории',
        'admin_login' => 'Вход администратора из ТЗ работает',
        'clean' => 'Нет ошибок в консоли и laravel.log',
    ];

    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $sessions = ExamSession::with('variant')
            ->where('user_id', $userId)
            ->latest()
            ->take(10)
            ->get();

        $active = ExamSession::with('variant')
            ->where('user_id', $userId)
            ->whereNull('finished_at')
            ->latest()
            ->first();

        return view('exam.index', [
            'variants' => ProjectVariant::where('is_published', true)->orderBy('position')->get(),
            'sessions' => $sessions,
            'active' => $active,
        ]);
    }

    public function start(Request $request)
    {
        $data = $request->validate([
            'variant_id' => ['nullable', 'integer', 'exists:project_variants,id'],
        ]);

        $variant = isset($data['variant_id'])
            ? ProjectVariant::findOrFail($data['variant_id'])
            : ProjectVariant::where('is_published', true)->inRandomOrder()->firstOrFail();

        abort_unless($variant->is_published, 404);

        $session = ExamSession::create([
            'user_id' => $request->user()->id,
            'project_variant_id' => $variant->id,
            'started_at' => now(),
        ]);

        return redirect()->route('exam.show', $session);
    }

    public function show(Request $request, ExamSession $session)
    {
        abort_unless($session->user_id === $request->user()->id, 403);

        if ($session->isFinished()) {
            return redirect()->route('exam.result', $session);
        }

        return view('exam.show', [
            'session' => $session,
            'variant' => $session->variant,
            'checklist' => self::CHECKLIST,
            'remaining' => $session->remainingSeconds(),
        ]);
    }

    public function finish(Request $request, ExamSession $session)
    {
        abort_unless($session->user_id === $request->user()->id, 403);

        if ($session->isFinished()) {
            return redirect()->route('exam.result', $session);
        }

        $data = $request->validate([
            'checklist' => ['nullable', 'array'],
            'checklist.*' => ['string', 'in:'.implode(',', array_keys(self::CHECKLIST))],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $checked = array_values(array_intersect(
            array_keys(self::CHECKLIST),
            $data['checklist'] ?? []
        ));

        $session->update([
            'finished_at' => now(),
            'checklist' => $checked,
            'notes' => $data['notes'] ?? null,
            'score' => count($checked) * 5,
        ]);

        return redirect()->route('exam.result', $session);
    }

    public function result(Request $request, ExamSession $session)
    {
        abort_unless($session->user_id === $request->user()->id, 403);
        abort_unless($session->isFinished(), 404);

        $checked = $session->checklist ?? [];
        $missing = array_diff(array_keys(self::CHECKLIST), $checked);

        return view('exam.result', [
            'session' => $session,
            'variant' => $session->variant,
            'checklist' => self::CHECKLIST,
            'checked' => $checked,
            'missing' => $missing,
        ]);
    }
}
