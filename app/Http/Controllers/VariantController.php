<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectVariant;
use Illuminate\Http\Request;

class VariantController extends Controller
{
    public function index(Request $request)
    {
        $variants = ProjectVariant::where('is_published', true)
            ->orderBy('position')
            ->get();

        $project = Project::where('user_id', $request->user()->id)->first();

        return view('variants.index', [
            'variants' => $variants,
            'selected' => $project?->variant_slug,
        ]);
    }

    public function show(Request $request, ProjectVariant $variant)
    {
        abort_unless($variant->is_published, 404);

        $project = Project::where('user_id', $request->user()->id)->first();

        return view('variants.show', [
            'variant' => $variant,
            'selected' => $project?->variant_slug === $variant->slug,
        ]);
    }

    public function random()
    {
        $variant = ProjectVariant::where('is_published', true)
            ->inRandomOrder()
            ->firstOrFail();

        return redirect()->route('variants.show', $variant);
    }

    public function select(Request $request, ProjectVariant $variant)
    {
        abort_unless($variant->is_published, 404);

        Project::updateOrCreate(
            ['user_id' => $request->user()->id],
            ['variant_slug' => $variant->slug]
        );

        return redirect()
            ->route('variants.show', $variant)
            ->with('success', 'Вариант выбран. Он появился в «Мой проект».');
    }

    public function download(ProjectVariant $variant)
    {
        abort_unless($variant->is_published, 404);

        return response()->streamDownload(
            function () use ($variant) {
                echo $variant->toMarkdown();
            },
            $variant->slug.'.md',
            ['Content-Type' => 'text/markdown; charset=UTF-8']
        );
    }
}
