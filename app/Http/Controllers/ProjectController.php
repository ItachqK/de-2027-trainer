<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectVariant;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function show(Request $request)
    {
        $project = Project::firstOrNew(['user_id' => $request->user()->id]);

        $variant = $project->variant_slug
            ? ProjectVariant::where('slug', $project->variant_slug)->first()
            : null;

        return view('project.show', [
            'project' => $project,
            'variant' => $variant,
            'stages' => Project::STAGES,
        ]);
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'repo_url' => ['nullable', 'url', 'max:255'],
            'deploy_url' => ['nullable', 'url', 'max:255'],
            'commit_hash' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'stages' => ['nullable', 'array'],
            'stages.*' => ['string', 'in:'.implode(',', array_keys(Project::STAGES))],
        ], [
            'repo_url.url' => 'Ссылка на репозиторий должна начинаться с http:// или https://.',
            'deploy_url.url' => 'Ссылка на деплой должна начинаться с http:// или https://.',
        ]);

        $stages = collect(array_keys(Project::STAGES))
            ->mapWithKeys(fn ($key) => [$key => in_array($key, $data['stages'] ?? [], true)])
            ->all();

        Project::updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'repo_url' => $data['repo_url'] ?? null,
                'deploy_url' => $data['deploy_url'] ?? null,
                'commit_hash' => $data['commit_hash'] ?? null,
                'notes' => $data['notes'] ?? null,
                'stages' => $stages,
            ]
        );

        return back()->with('success', 'Проект сохранён.');
    }
}
