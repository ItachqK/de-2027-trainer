<?php

namespace App\Http\Controllers;

use App\Models\CaseStudy;
use Illuminate\Http\Request;

class CaseStudyController extends Controller
{
    public function index()
    {
        $cases = CaseStudy::with('variant')
            ->where('is_published', true)
            ->orderBy('position')
            ->get();

        return view('cases.index', ['cases' => $cases]);
    }

    public function show(Request $request, CaseStudy $case)
    {
        abort_unless($case->is_published, 404);

        $chapters = $case->chapters;
        $total = count($chapters);
        $chapterIndex = (int) $request->query('chapter', 1);
        $chapterIndex = max(1, min($chapterIndex, $total));

        return view('cases.show', [
            'case' => $case,
            'variant' => $case->variant,
            'chapters' => $chapters,
            'chapter' => $chapters[$chapterIndex - 1],
            'chapterIndex' => $chapterIndex,
            'total' => $total,
        ]);
    }

    public function chronicle(CaseStudy $case)
    {
        abort_unless($case->is_published, 404);

        return view('cases.chronicle', [
            'case' => $case,
            'variant' => $case->variant,
            'chronicle' => $case->chronicle ?? [],
        ]);
    }
}
