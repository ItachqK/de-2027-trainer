<?php

namespace App\Http\Controllers;

use App\Models\Lab;
use App\Models\LabSubmission;
use App\Models\Lesson;
use App\Services\LabChecker;
use Illuminate\Http\Request;

class LabController extends Controller
{
    public function index()
    {
        $labs = Lab::with('module')
            ->where('is_published', true)
            ->orderBy('position')
            ->get();

        $submissions = LabSubmission::where('user_id', auth()->id())
            ->get()
            ->keyBy('lab_id');

        return view('labs.index', [
            'labs' => $labs,
            'submissions' => $submissions,
            'passed' => $submissions->where('status', 'passed')->count(),
            'total' => $labs->count(),
        ]);
    }

    public function show(Lab $lab)
    {
        abort_unless($lab->is_published, 404);

        $submission = LabSubmission::where('user_id', auth()->id())
            ->where('lab_id', $lab->id)
            ->first();

        $lesson = $lab->lesson_slug
            ? Lesson::where('slug', $lab->lesson_slug)->first()
            : null;

        $next = Lab::where('is_published', true)
            ->where('position', '>', $lab->position)
            ->orderBy('position')
            ->first();

        return view('labs.show', compact('lab', 'submission', 'lesson', 'next'));
    }

    public function check(Request $request, Lab $lab, LabChecker $checker)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:100000'],
        ], [
            'code.required' => 'Введите код или ответ перед проверкой.',
        ]);

        $result = $checker->check($lab, $data['code']);

        $submission = LabSubmission::firstOrNew([
            'user_id' => $request->user()->id,
            'lab_id' => $lab->id,
        ]);

        $submission->code = $data['code'];
        $submission->status = $result['passed'] ? 'passed' : 'failed';
        $submission->results = $result['results'];
        $submission->attempts = ($submission->attempts ?? 0) + 1;
        $submission->save();

        return redirect()
            ->to(route('labs.show', $lab).'#result')
            ->with('lab_checked', true);
    }

    public function draft(Request $request, Lab $lab)
    {
        $data = $request->validate([
            'code' => ['nullable', 'string', 'max:100000'],
        ]);

        $submission = LabSubmission::firstOrNew([
            'user_id' => $request->user()->id,
            'lab_id' => $lab->id,
        ]);

        $submission->code = $data['code'] ?? '';
        $submission->status = $submission->status === 'passed' ? 'passed' : 'draft';
        $submission->save();

        return back()->with('success', 'Черновик сохранён.');
    }
}
