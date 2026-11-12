<?php

namespace App\Http\Controllers;

use App\Models\CapstoneSubmission;
use App\Services\CapstoneAuditor;
use App\Services\ProjectRunner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Process;

class CapstoneController extends Controller
{
    public function index(Request $request)
    {
        $submissions = CapstoneSubmission::where('user_id', $request->user()->id)
            ->latest()
            ->take(10)
            ->get();

        return view('capstone.index', [
            'submissions' => $submissions,
            'latest' => $submissions->first(),
            'roots' => config('capstone.allowed_roots', []),
        ]);
    }

    public function audit(Request $request, CapstoneAuditor $auditor, ProjectRunner $runner)
    {
        @set_time_limit(300);

        $data = $request->validate([
            'project_path' => ['nullable', 'string', 'max:500'],
            'repo_url' => ['nullable', 'url', 'max:255'],
            'deploy_url' => ['nullable', 'url', 'max:255'],
            'commit_hash' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'run_project' => ['nullable', 'boolean'],
        ], [
            'repo_url.url' => 'Ссылка на репозиторий должна начинаться с http:// или https://.',
            'deploy_url.url' => 'Ссылка на проект должна начинаться с http:// или https://.',
        ]);

        $path = trim((string) ($data['project_path'] ?? ''));
        $repo = trim((string) ($data['repo_url'] ?? ''));

        if ($path === '' && $repo === '') {
            throw ValidationException::withMessages([
                'project_path' => 'Укажите путь к папке проекта или ссылку на репозиторий.',
            ]);
        }

        $cleanup = null;
        $runtime = null;

        try {
            if ($path !== '') {
                $directory = $this->resolvePath($path);
            } else {
                [$directory, $cleanup] = $this->cloneRepository($repo);
            }

            $result = $auditor->audit($directory);

            if ($request->boolean('run_project')) {
                $runtime = $runner->run($directory);
            }
        } finally {
            if ($cleanup) {
                File::deleteDirectory($cleanup);
            }
        }

        CapstoneSubmission::create([
            'user_id' => $request->user()->id,
            'project_path' => $path !== '' ? $path : null,
            'repo_url' => $repo !== '' ? $repo : null,
            'deploy_url' => $data['deploy_url'] ?? null,
            'commit_hash' => $data['commit_hash'] ?? null,
            'score' => $result['score'],
            'max_score' => $result['max'],
            'results' => $result,
            'runtime' => $runtime,
            'runtime_passed' => $runtime['passed'] ?? false,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('capstone.index')
            ->with('success', 'Проверка выполнена. Это предварительная оценка по файлам проекта.');
    }

    private function resolvePath(string $path): string
    {
        $real = realpath($path);

        if ($real === false || ! is_dir($real)) {
            throw ValidationException::withMessages([
                'project_path' => 'Папка не найдена. Проверьте путь.',
            ]);
        }

        foreach (config('capstone.allowed_roots', []) as $root) {
            $rootReal = realpath($root);

            if ($rootReal === false) {
                continue;
            }

            $prefix = rtrim(mb_strtolower($rootReal), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
            $candidate = mb_strtolower($real);

            if ($candidate === mb_strtolower($rootReal) || str_starts_with($candidate, $prefix)) {
                return $real;
            }
        }

        throw ValidationException::withMessages([
            'project_path' => 'Путь должен находиться в разрешённой папке: '.implode(', ', config('capstone.allowed_roots', [])),
        ]);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function cloneRepository(string $url): array
    {
        if (! preg_match('#^(https://|git@)#i', $url)) {
            throw ValidationException::withMessages([
                'repo_url' => 'Поддерживаются только https:// и git@ ссылки.',
            ]);
        }

        $target = storage_path('app/capstone/'.Str::uuid());
        File::ensureDirectoryExists(dirname($target));

        $process = new Process(['git', 'clone', '--depth=1', $url, $target]);
        $process->setTimeout(90);
        $process->run();

        if (! $process->isSuccessful()) {
            File::deleteDirectory($target);

            throw ValidationException::withMessages([
                'repo_url' => 'Не удалось склонировать репозиторий: '.mb_substr(trim($process->getErrorOutput()), 0, 240),
            ]);
        }

        return [$target, $target];
    }
}
