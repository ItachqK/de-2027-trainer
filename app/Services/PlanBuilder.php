<?php

namespace App\Services;

use App\Models\CapstoneSubmission;
use App\Models\Drill;
use App\Models\DrillProgress;
use App\Models\ExamSession;
use App\Models\Lab;
use App\Models\LabSubmission;
use App\Models\Lesson;
use App\Models\User;
use App\Models\UserState;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Считает готовность к экзамену и собирает план на день и на недели вперёд.
 *
 * Готовность — не оценка эксперта, а честный срез четырёх блоков подготовки:
 * теория, тренажёр, лабораторные и прогон экзамена с аудитом проекта.
 */
class PlanBuilder
{
    /** Вес каждого блока в процентах. В сумме — 100. */
    public const WEIGHTS = [
        'lessons' => 25,
        'drills' => 25,
        'labs' => 25,
        'exam' => 25,
    ];

    public function build(User $user): array
    {
        $doneLessons = $this->doneLessons($user);

        $blocks = [
            $this->lessonsBlock($user, $doneLessons),
            $this->drillsBlock($user),
            $this->labsBlock($user),
            $this->examBlock($user),
        ];

        $readiness = (int) round(collect($blocks)->sum(
            fn (array $block) => $block['percent'] * self::WEIGHTS[$block['key']] / 100
        ));

        $weakest = collect($blocks)->sortBy('percent')->first();
        $examDate = $this->examDate();
        $today = $this->today();
        $daysLeft = max(0, (int) round($today->diffInDays($examDate)));

        $tasks = $this->tasks($user, $doneLessons, $weakest['key']);

        return [
            'readiness' => $readiness,
            'blocks' => $blocks,
            'weakest' => $readiness >= 100 ? null : $weakest,
            'tasks' => $tasks,
            'primaryMinutes' => collect($tasks)->where('primary', true)->sum('minutes'),
            'weeks' => $this->weeks($today, $examDate),
            'daysLeft' => $daysLeft,
            'examDate' => $examDate,
            'hoursPerWeek' => (float) config('exam.hours_per_week'),
        ];
    }

    /** Дата экзамена по Москве: иначе в UTC она сдвигается на предыдущий день. */
    private function examDate(): Carbon
    {
        return Carbon::parse(config('exam.date'), 'Europe/Moscow')->startOfDay()->locale('ru');
    }

    /** Сегодняшний день тоже по Москве — чтобы «дней до экзамена» менялось в полночь, а не в 03:00. */
    private function today(): Carbon
    {
        return Carbon::now('Europe/Moscow')->startOfDay();
    }

    private function doneLessons(User $user): array
    {
        $state = UserState::where('user_id', $user->id)->value('data');
        $done = is_array($state) ? ($state['done'] ?? []) : [];

        return array_keys(array_filter($done));
    }

    private function lessonsBlock(User $user, array $done): array
    {
        $total = Lesson::where('is_published', true)->count();
        $slugs = Lesson::where('is_published', true)->pluck('slug')->all();
        $doneCount = count(array_intersect($done, $slugs));

        return $this->block('lessons', 'Теория', $doneCount, $total, route('app'), 'Уроки курса дают базу, без которой сборка превращается в переписывание.');
    }

    private function drillsBlock(User $user): array
    {
        $published = Drill::where('is_published', true)->pluck('id')->all();
        $total = count($published);
        $solved = DrillProgress::where('user_id', $user->id)
            ->where('passes', '>', 0)
            ->whereIn('drill_id', $published)
            ->count();

        return $this->block('drills', 'Тренажёр', $solved, $total, route('drills.index'), 'Варианты тренируют руки: карточки, формы, SQL и Laravel-фрагменты.');
    }

    private function labsBlock(User $user): array
    {
        $published = Lab::where('is_published', true)->pluck('id')->all();
        $total = count($published);
        $passed = LabSubmission::where('user_id', $user->id)
            ->where('status', 'passed')
            ->whereIn('lab_id', $published)
            ->distinct()
            ->count('lab_id');

        return $this->block('labs', 'Лабораторные', $passed, $total, route('labs.index'), 'Лабораторные проверяют код по критериям — как это делает эксперт.');
    }

    private function examBlock(User $user): array
    {
        $runs = ExamSession::where('user_id', $user->id)->whereNotNull('finished_at')->count();

        // Лучший аудит — по доле выполненных критериев, а не по сырому баллу.
        $best = CapstoneSubmission::where('user_id', $user->id)
            ->get()
            ->sortByDesc(fn (CapstoneSubmission $item) => $item->percent())
            ->first();

        $auditPercent = $best ? min(100, $best->percent()) : 0;

        $percent = ($runs > 0 ? 50 : 0) + (int) round($auditPercent / 2);
        $done = ($runs > 0 ? 1 : 0) + ($auditPercent > 0 ? 1 : 0);

        return [
            'key' => 'exam',
            'label' => 'Экзамен и аудит',
            'done' => $done,
            'total' => 2,
            'percent' => min(100, $percent),
            'url' => $runs > 0 && $auditPercent < 100 ? route('capstone.index') : route('exam.index'),
            'hint' => 'Прогон по таймеру и проверка проекта на 75 баллов — единственный способ узнать, где вы на самом деле.',
            'detail' => $runs > 0
                ? 'Прогонов: '.$runs.' · аудит: '.$auditPercent.'%'
                : 'Прогонов пока нет · аудит не запускался',
        ];
    }

    private function block(string $key, string $label, int $done, int $total, string $url, string $hint): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'done' => $done,
            'total' => $total,
            'percent' => $total > 0 ? (int) round(min(100, $done / $total * 100)) : 0,
            'url' => $url,
            'hint' => $hint,
            'detail' => $done.' из '.$total,
        ];
    }

    /**
     * Три дела: главное — из самого слабого блока, остальные — по порядку.
     * Аудит проекта идёт отдельной задачей: без него блок «Экзамен и аудит»
     * не закрыть, даже если прогон по таймеру уже сделан.
     */
    private function tasks(User $user, array $done, string $weakest): array
    {
        $tasks = array_values(array_filter([
            $this->lessonTask($done),
            $this->drillTask($user),
            $this->labTask($user),
            $this->examTask($user),
            $this->auditTask($user),
        ]));

        usort($tasks, function (array $a, array $b) use ($weakest) {
            $aRank = $a['block'] === $weakest ? -1 : $a['order'];
            $bRank = $b['block'] === $weakest ? -1 : $b['order'];

            return $aRank <=> $bRank;
        });

        $top = array_slice($tasks, 0, 3);

        // Если прогон по таймеру уже был, аудит обязателен: без него блок
        // «Экзамен и аудит» не закрыть. Меняем последнее дело на аудит.
        $hasRun = ExamSession::where('user_id', $user->id)->whereNotNull('finished_at')->exists();
        $hasAudit = collect($top)->contains(fn (array $task) => $task['kind'] === 'аудит');
        if ($hasRun && ! $hasAudit) {
            $audit = collect($tasks)->firstWhere('kind', 'аудит');
            if ($audit) {
                $top[2] = $audit;
            }
        }

        foreach ($top as $index => $task) {
            $top[$index]['primary'] = $index === 0;
        }

        return array_values($top);
    }

    private function lessonTask(array $done): ?array
    {
        $lesson = Lesson::with('module')
            ->where('is_published', true)
            ->get()
            ->sortBy(fn (Lesson $item) => [optional($item->module)->position ?? 0, $item->position])
            ->first(fn (Lesson $item) => ! in_array($item->slug, $done, true));

        if (! $lesson) {
            return null;
        }

        return [
            'block' => 'lessons',
            'order' => 10,
            'kind' => 'урок',
            'title' => $lesson->title,
            'meta' => 'Модуль '.(optional($lesson->module)->num ?? '—').' · '.$lesson->minutes.' мин',
            'minutes' => (int) $lesson->minutes,
            'url' => route('app').'#/lesson/'.$lesson->slug,
        ];
    }

    private function drillTask(User $user): ?array
    {
        $solved = DrillProgress::where('user_id', $user->id)
            ->where('passes', '>', 0)
            ->pluck('drill_id')
            ->all();

        $drill = Drill::where('is_published', true)
            ->whereNotIn('id', $solved)
            ->orderBy('position')
            ->first();

        $mode = 'новый';

        if (! $drill) {
            $due = DrillProgress::where('user_id', $user->id)
                ->whereNotNull('next_review_at')
                ->where('next_review_at', '<=', now())
                ->orderBy('next_review_at')
                ->first();

            $drill = $due
                ? Drill::where('is_published', true)->find($due->drill_id)
                : Drill::where('is_published', true)->orderBy('position')->first();
            $mode = $due ? 'повтор' : 'повторить';
        }

        if (! $drill) {
            return null;
        }

        return [
            'block' => 'drills',
            'order' => 20,
            'kind' => 'вариант',
            'title' => $drill->title,
            'meta' => $drill->trackLabel().' · '.$drill->minutes.' мин · '.$mode,
            'minutes' => (int) $drill->minutes,
            'url' => route('drills.show', $drill),
        ];
    }

    private function labTask(User $user): ?array
    {
        $passed = LabSubmission::where('user_id', $user->id)
            ->where('status', 'passed')
            ->pluck('lab_id')
            ->all();

        $lab = Lab::where('is_published', true)
            ->whereNotIn('id', $passed)
            ->orderBy('position')
            ->first();

        if (! $lab) {
            return null;
        }

        return [
            'block' => 'labs',
            'order' => 30,
            'kind' => 'лабораторная',
            'title' => $lab->title,
            'meta' => $lab->level.' · '.$lab->minutes.' мин',
            'minutes' => (int) $lab->minutes,
            'url' => route('labs.show', $lab),
        ];
    }

    private function examTask(User $user): ?array
    {
        $runs = ExamSession::where('user_id', $user->id)->whereNotNull('finished_at')->count();

        if ($runs > 0) {
            return null;
        }

        return [
            'block' => 'exam',
            'order' => 40,
            'kind' => 'экзамен',
            'title' => 'Прогон по таймеру 3:30',
            'meta' => 'Когда теория и сборка закрыты · 210 мин',
            'minutes' => 210,
            'url' => route('exam.index'),
        ];
    }

    /** Аудит проекта — вторая половина блока «Экзамен и аудит». */
    private function auditTask(User $user): ?array
    {
        $best = CapstoneSubmission::where('user_id', $user->id)
            ->get()
            ->sortByDesc(fn (CapstoneSubmission $item) => $item->percent())
            ->first();

        if ($best && $best->percent() >= 100) {
            return null;
        }

        return [
            'block' => 'exam',
            'order' => 45,
            'kind' => 'аудит',
            'title' => 'Проверить проект по 75-балльной шкале',
            'meta' => $best
                ? 'Лучший результат: '.$best->percent().'% · добрать до 100%'
                : 'Папка или репозиторий · 20 мин',
            'minutes' => 20,
            'url' => route('capstone.index'),
        ];
    }

    /**
     * План на 15 недель: 2–3 часа в неделю — это около 30 часов практики
     * плюс два прогона экзамена. Теория сжимается, практика — нет.
     */
    private function weeks(Carbon $today, Carbon $examDate): Collection
    {
        $plan = [
            ['Терминал и файлы', 'Модуль 00: команды, пути, установка PHP и Composer.'],
            ['HTML и CSS', 'Модуль 01 и лабораторная с карточкой заявки.'],
            ['PHP: синтаксис и формы', 'Модуль 02, лабораторные по валидации и PDO.'],
            ['SQL и связи', 'Модуль 03: три таблицы, два запроса, статусы.'],
            ['Laravel: маршруты и Blade', 'Модуль 04, первые две лабораторные Laravel.'],
            ['Laravel: Eloquent и валидация', 'Модуль 04 до конца, лабораторная по входу.'],
            ['Сборка: каркас и база', 'Шаги 1–3 из «Сборки с нуля».'],
            ['Сборка: страницы и вход', 'Шаги 4–6: Blade, регистрация, роли.'],
            ['Сборка: заявки и админка', 'Шаги 7–9: форма, статусы, фильтры.'],
            ['Сборка: тесты и сдача', 'Шаг 10 и две лабораторные по качеству.'],
            ['Дизайн 390×844', 'Модуль 06: макет, изображения, стиль.'],
            ['Адаптив и качество', 'Модуль 07 и оставшиеся лабораторные.'],
            ['Тренажёр: десять вариантов', 'По два варианта за занятие, с разбором ошибок.'],
            ['Экзамен №1 и аудит', 'Полный прогон по таймеру, затем проверка проекта.'],
            ['Слабые места и защита', 'Повтор провалов, речь на 3 минуты, отдых.'],
        ];

        $total = count($plan);
        $examWeekStart = $examDate->copy()->startOfWeek(Carbon::MONDAY);

        return collect($plan)->map(function (array $row, int $index) use ($total, $today, $examWeekStart) {
            $num = $index + 1;
            $start = $examWeekStart->copy()->subWeeks($total - $num);
            $end = $start->copy()->addDays(6);

            // Первая неделя начинается сегодня, а не со следующего понедельника.
            if ($num === 1 && $start->gt($today)) {
                $start = $today->copy();
            }

            // Состояние — по окну недели: в последний день недели она ещё «сейчас»,
            // а не «впереди».
            $state = $start->lte($today) && $end->gte($today)
                ? 'current'
                : ($end->lt($today) ? 'past' : 'future');

            return [
                'num' => $num,
                'focus' => $row[0],
                'detail' => $row[1],
                'dates' => $start->format('d.m').'–'.$end->format('d.m'),
                'state' => $state,
            ];
        });
    }
}
