<?php

namespace Database\Seeders;

use App\Models\CaseStudy;
use Illuminate\Database\Seeder;

class ChronicleSeeder extends Seeder
{
    public function run(): void
    {
        $testLines = [
            'conference-build' => 'Tests: 6 passed (14 assertions)',
            'bookexchange-build' => 'Tests: 5 passed (14 assertions)',
            'restaurant-build' => 'Tests: 6 passed (13 assertions)',
            'pizza-build' => 'Tests: 6 passed (15 assertions)',
        ];

        foreach (CaseStudy::all() as $case) {
            $entries = [];
            $minutes = 0;
            $testLine = $testLines[$case->slug] ?? 'Tests: passed';
            $perChapter = max(10, (int) round($case->duration / max(1, count($case->chapters))));

            foreach ($case->chapters as $index => $chapter) {
                $number = $index + 1;
                $time = sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
                $minutes += $perChapter;

                $commands = [];

                foreach (($chapter['commands'] ?? []) as $command) {
                    $commands[] = [
                        'label' => $command['label'],
                        'code' => $command['code'],
                        'output' => $this->outputFor($command['code'], $case->slug, $number, $testLine),
                    ];
                }

                $hasCommit = collect($commands)->contains(fn (array $command) => str_contains($command['code'], 'git commit'));
                $hash = substr(md5($case->slug.$number), 0, 7);

                $entries[] = [
                    'time' => $time,
                    'title' => $chapter['title'],
                    'goal' => $chapter['goal'],
                    'paragraphs' => array_slice($chapter['paragraphs'] ?? [], 0, 2),
                    'commands' => $commands,
                    'files' => collect($chapter['files'] ?? [])->take(12)->values()->all(),
                    'commit' => $hasCommit ? null : [
                        'message' => 'Шаг '.$number.': '.$chapter['title'],
                        'hash' => $hash,
                        'output' => '[main '.$hash.'] Шаг '.$number.': '.$chapter['title'],
                    ],
                    'result' => $chapter['expert'][0] ?? 'Шаг выполнен, идём дальше.',
                ];
            }

            $releaseHash = substr(md5($case->slug.'release'), 0, 7);

            $entries[] = [
                'time' => sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60),
                'title' => 'Релиз',
                'goal' => 'Собрать проект, прогнать тесты, поставить тег и подготовить архив.',
                'paragraphs' => [
                    'Релиз — это не «папка на рабочем столе». Это чистая база, зелёные тесты, тег в Git и архив, который можно передать или развернуть.',
                    'Проверьте README, `.env.example`, команды запуска и то, что в репозитории нет `vendor`, `.env` и локальной базы.',
                ],
                'commands' => [
                    ['label' => 'Чистая база', 'code' => 'php artisan migrate:fresh --seed', 'output' => "INFO Running migrations.\nINFO Seeding database.\nDONE"],
                    ['label' => 'Тесты', 'code' => 'php artisan test', 'output' => $testLine],
                    ['label' => 'Коммит релиза', 'code' => "git add .\ngit commit -m \"Релиз 1.0\"", 'output' => '[main '.$releaseHash.'] Релиз 1.0'],
                    ['label' => 'Тег', 'code' => 'git tag v1.0.0', 'output' => ''],
                    ['label' => 'Пуш с тегом', 'code' => 'git push origin main --tags', 'output' => "To github.com:student/project.git\n * [new tag] v1.0.0 -> v1.0.0"],
                    ['label' => 'Архив релиза', 'code' => 'git archive --format=zip HEAD -o project.zip', 'output' => 'Архив создан: project.zip'],
                ],
                'files' => ['README.md', 'routes/web.php', 'tests/Feature'],
                'commit' => null,
                'result' => 'Проект собран, тесты зелёные, тег v1.0.0 поставлен, архив готов.',
            ];

            $case->update(['chronicle' => $entries]);
        }

        $this->command?->info('Хроники сборки: '.CaseStudy::count().'.');
    }

    private function outputFor(string $code, string $slug, int $step, string $testLine): string
    {
        if (str_contains($code, 'php -v')) {
            return "PHP 8.5.0 (cli) (built: ...)\nCopyright (c) The PHP Group";
        }

        if (str_contains($code, 'composer -V')) {
            return 'Composer version 2.8.0';
        }

        if (str_contains($code, 'composer create-project')) {
            return "Creating a \"laravel/laravel\" project at ./project\nInstalling dependencies...\nApplication ready! Build something amazing.";
        }

        if (str_contains($code, 'key:generate')) {
            return 'Application key set successfully.';
        }

        if (str_contains($code, 'artisan migrate')) {
            return "INFO Running migrations.\n\n0001_01_01_000000_create_users_table ... DONE\n2026_01_01_000001_create_tables ... DONE";
        }

        if (str_contains($code, 'db:seed')) {
            return "INFO Seeding database.\n\nDatabase\\Seeders\\UserSeeder ... DONE\nDatabase\\Seeders\\DemoSeeder ... DONE";
        }

        if (str_contains($code, 'artisan serve')) {
            return 'INFO Server running on [http://127.0.0.1:8000].';
        }

        if (str_contains($code, 'artisan test')) {
            return $testLine;
        }

        if (str_contains($code, 'git init')) {
            return 'Initialized empty Git repository.';
        }

        if (str_contains($code, 'git commit')) {
            $hash = substr(md5($slug.$step), 0, 7);

            return '[main '.$hash.'] Шаг '.$step;
        }

        if (str_contains($code, 'git push')) {
            return "To github.com:student/project.git\n * [new branch] main -> main";
        }

        if (str_contains($code, 'git tag')) {
            return '';
        }

        if (str_contains($code, 'type nul')) {
            return 'Файл database.sqlite создан.';
        }

        if (str_contains($code, 'artisan make')) {
            return 'Файл создан.';
        }

        return 'OK';
    }
}
