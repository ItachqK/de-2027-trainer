<?php

namespace App\Http\Controllers;

use App\Models\Drill;
use App\Models\DrillAttempt;
use App\Models\DrillProgress;
use App\Services\LabChecker;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DrillController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $drills = Drill::where('is_published', true)->orderBy('position')->get();
        $progress = DrillProgress::where('user_id', $userId)->get()->keyBy('drill_id');
        $queue = $this->queue($userId);
        $now = now();

        $states = $drills->mapWithKeys(fn (Drill $drill) => [
            $drill->id => $this->state($drill, $progress, $now),
        ]);

        $attemptsQuery = DrillAttempt::where('user_id', $userId);
        $totalAttempts = (clone $attemptsQuery)->count();
        $passedAttempts = (clone $attemptsQuery)->where('passed', true)->count();

        $trackOrder = [
            'html' => 'HTML',
            'css' => 'CSS',
            'bootstrap' => 'Bootstrap',
            'php' => 'PHP',
            'sql' => 'SQL',
            'laravel' => 'Laravel',
        ];

        $tracks = collect($trackOrder)->map(function (string $label, string $key) use ($drills, $progress) {
            $all = $drills->where('track', $key);
            $solved = $all->filter(fn (Drill $drill) => ($progress->get($drill->id)?->passes ?? 0) > 0)->count();

            return [
                'key' => $key,
                'label' => $label,
                'total' => $all->count(),
                'solved' => $solved,
            ];
        })->values();

        return view('drills.index', [
            'drills' => $drills,
            'progress' => $progress,
            'queue' => $queue,
            'states' => $states,
            'tracks' => $tracks,
            'stats' => [
                'solved' => $progress->filter(fn (DrillProgress $item) => $item->passes > 0)->count(),
                'total' => $drills->count(),
                'attempts' => $totalAttempts,
                'accuracy' => $totalAttempts > 0 ? (int) round($passedAttempts / $totalAttempts * 100) : 0,
                'streak' => $this->dayStreak($userId),
            ],
            'queueMinutes' => collect($queue)->sum(fn (array $item) => $item['drill']->minutes),
        ]);
    }

    public function show(Drill $drill)
    {
        abort_unless($drill->is_published, 404);

        $userId = auth()->id();
        $progress = DrillProgress::firstOrNew([
            'user_id' => $userId,
            'drill_id' => $drill->id,
        ]);

        $attempts = DrillAttempt::where('user_id', $userId)
            ->where('drill_id', $drill->id)
            ->latest()
            ->take(5)
            ->get();

        $next = Drill::where('is_published', true)
            ->where('position', '>', $drill->position)
            ->orderBy('position')
            ->first()
            ?? Drill::where('is_published', true)->orderBy('position')->first();

        return view('drills.show', [
            'drill' => $drill,
            'progress' => $progress,
            'attempts' => $attempts,
            'lastAttempt' => $attempts->first(),
            'next' => $next,
        ]);
    }

    public function check(Request $request, Drill $drill, LabChecker $checker)
    {
        abort_unless($drill->is_published, 404);

        $data = $request->validate([
            'code' => ['required', 'string', 'max:100000'],
        ], [
            'code.required' => 'Напишите решение перед проверкой.',
        ]);

        $result = $checker->checkRules($drill->checks ?? [], $data['code']);

        DrillAttempt::create([
            'user_id' => $request->user()->id,
            'drill_id' => $drill->id,
            'code' => $data['code'],
            'passed' => $result['passed'],
            'results' => $result['results'],
        ]);

        $progress = DrillProgress::firstOrNew([
            'user_id' => $request->user()->id,
            'drill_id' => $drill->id,
        ]);

        $progress->attempts = ($progress->attempts ?? 0) + 1;
        $progress->last_code = $data['code'];

        if ($result['passed']) {
            $progress->passes = ($progress->passes ?? 0) + 1;
            $progress->streak = ($progress->streak ?? 0) + 1;
            $progress->last_passed_at = now();
            $progress->next_review_at = now()->addDays($this->interval($progress->streak));
        } else {
            $progress->streak = 0;
            $progress->next_review_at = now()->addDay();
        }

        $progress->save();

        return redirect()
            ->to(route('drills.show', $drill).'#result')
            ->with('drill_checked', true);
    }

    public function next(Request $request)
    {
        $queue = $this->queue($request->user()->id);

        if ($queue === []) {
            return redirect()
                ->route('drills.index')
                ->with('success', 'На сегодня всё. Возвращайтесь завтра — повторы уже запланированы.');
        }

        return redirect()->route('drills.show', $queue[0]['drill']);
    }

    /**
     * @return array<int, array{drill: Drill, mode: string}>
     */
    private function queue(int $userId): array
    {
        $drills = Drill::where('is_published', true)->orderBy('position')->get();
        $progress = DrillProgress::where('user_id', $userId)->get()->keyBy('drill_id');
        $now = now();

        $due = $drills->filter(function (Drill $drill) use ($progress, $now) {
            $item = $progress->get($drill->id);

            return $item && $item->next_review_at && $item->next_review_at->lte($now);
        })->sortBy(fn (Drill $drill) => $progress->get($drill->id)->next_review_at->timestamp)->values();

        $fresh = $drills->filter(fn (Drill $drill) => ! $progress->has($drill->id))->values();

        // Новые варианты идут по кругу: HTML, CSS, Bootstrap, PHP, SQL, Laravel.
        $groups = $fresh->groupBy('track')->values();
        $mixed = collect();
        $index = 0;

        while ($mixed->count() < $fresh->count()) {
            $added = false;

            foreach ($groups as $group) {
                if (isset($group[$index])) {
                    $mixed->push($group[$index]);
                    $added = true;
                }
            }

            if (! $added) {
                break;
            }

            $index++;
        }

        $fresh = $mixed;

        $items = [];

        foreach ($due->take(3) as $drill) {
            $items[] = ['drill' => $drill, 'mode' => 'repeat'];
        }

        foreach ($fresh as $drill) {
            if (count($items) >= 5) {
                break;
            }

            $items[] = ['drill' => $drill, 'mode' => 'new'];
        }

        foreach ($due->slice(3) as $drill) {
            if (count($items) >= 5) {
                break;
            }

            $items[] = ['drill' => $drill, 'mode' => 'repeat'];
        }

        if ($items === []) {
            foreach ($drills->sortBy(fn (Drill $drill) => $progress->get($drill->id)?->streak ?? 0)->take(3) as $drill) {
                $items[] = ['drill' => $drill, 'mode' => 'weak'];
            }
        }

        return $items;
    }

    private function state(Drill $drill, Collection $progress, Carbon $now): string
    {
        $item = $progress->get($drill->id);

        if (! $item || $item->passes === 0) {
            return 'new';
        }

        if ($item->next_review_at && $item->next_review_at->lte($now)) {
            return 'due';
        }

        return 'learned';
    }

    private function interval(int $streak): int
    {
        return [1 => 1, 2 => 3, 3 => 7, 4 => 16, 5 => 35][$streak] ?? 70;
    }

    private function dayStreak(int $userId): int
    {
        $days = DrillAttempt::where('user_id', $userId)
            ->where('passed', true)
            ->selectRaw('date(created_at) as day')
            ->distinct()
            ->pluck('day')
            ->all();

        $set = array_flip($days);
        $cursor = now()->startOfDay();

        if (! isset($set[$cursor->toDateString()])) {
            $cursor = $cursor->copy()->subDay();
        }

        $streak = 0;

        while (isset($set[$cursor->toDateString()])) {
            $streak++;
            $cursor = $cursor->copy()->subDay();
        }

        return $streak;
    }
}
