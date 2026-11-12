<?php

namespace App\Http\Controllers;

use App\Models\BuildProgress;
use App\Models\BuildStep;
use Illuminate\Http\Request;
use ZipArchive;

class BuildController extends Controller
{
    public function index(Request $request)
    {
        $steps = BuildStep::where('is_published', true)
            ->orderBy('position')
            ->get();

        $done = BuildProgress::where('user_id', $request->user()->id)
            ->pluck('build_step_id')
            ->all();

        $next = $steps->first(fn (BuildStep $step) => ! in_array($step->id, $done, true));

        return view('build.index', [
            'steps' => $steps,
            'done' => $done,
            'next' => $next,
        ]);
    }

    public function show(Request $request, BuildStep $step)
    {
        abort_unless($step->is_published, 404);

        $mode = $request->query('mode') === 'learn' ? 'learn' : 'full';

        $completed = BuildProgress::where('user_id', $request->user()->id)
            ->where('build_step_id', $step->id)
            ->exists();

        $prev = BuildStep::where('is_published', true)
            ->where('position', '<', $step->position)
            ->orderByDesc('position')
            ->first();

        $next = BuildStep::where('is_published', true)
            ->where('position', '>', $step->position)
            ->orderBy('position')
            ->first();

        return view('build.show', [
            'step' => $step,
            'completed' => $completed,
            'prev' => $prev,
            'next' => $next,
            'total' => BuildStep::where('is_published', true)->count(),
            'mode' => $mode,
            'questions' => $this->learningQuestions($step),
        ]);
    }

    private function learningQuestions(BuildStep $step): array
    {
        return match ($step->slug) {
            'prepare' => [
                'Какие команды создают проект, а какие только запускают его?',
                'Зачем нужен файл database/database.sqlite и где включается SQLite?',
                'Какие стандартные файлы Laravel нельзя удалять?',
            ],
            'database' => [
                'Какие таблицы нужны по КИМ: users, rooms, applications?',
                'Какие поля связывают заявку с пользователем и помещением?',
                'Почему сначала нужен make:migration, а потом migrate?',
            ],
            'models' => [
                'Какие связи будут hasMany и какие belongsTo?',
                'Почему стандартный User.php нужно заменить целиком?',
                'Зачем нужны fillable, casts и HasFactory?',
            ],
            'seeders' => [
                'Какие тестовые пользователи нужны по КИМ?',
                'Какие три помещения должны появиться в базе?',
                'Почему DatabaseSeeder должен вызывать только наши сидеры?',
            ],
            'pages' => [
                'Какие страницы и ссылки нужны до регистрации и входа?',
                'Почему на этом шаге нельзя ссылаться на ещё не созданные route names?',
                'Как проверить, что локальный CSS действительно подключился?',
            ],
            'registration' => [
                'Какие поля обязательны по КИМ и какие правила у каждого?',
                'Почему логин должен быть unique, а пароль — confirmed?',
                'Что должно произойти после успешной регистрации?',
            ],
            'auth' => [
                'Как Laravel проверяет логин и пароль?',
                'Как запретить студенту вход в /admin?',
                'Почему после входа нужно regenerate() для сессии?',
            ],
            'applications' => [
                'Какие поля пользователь отправляет в заявке?',
                'Какие два способа оплаты указаны в КИМ?',
                'Когда пользователь может оставить отзыв?',
            ],
            'admin' => [
                'Какие заявки видит администратор?',
                'Какие три статуса требует КИМ?',
                'Как добавить фильтр по статусу и поиск по ФИО?',
            ],
            'finish' => [
                'Какие сценарии нужно проверить перед сдачей?',
                'Зачем выполнить migrate:fresh --seed на чистой базе?',
                'Что должно попасть в финальный коммит?',
            ],
            default => ['Что создаётся на этом шаге?', 'Какой файл отвечает за логику?', 'Как проверить результат?'],
        };
    }

    public function complete(Request $request, BuildStep $step)
    {
        BuildProgress::firstOrCreate(
            [
                'user_id' => $request->user()->id,
                'build_step_id' => $step->id,
            ],
            ['completed_at' => now()]
        );

        $next = BuildStep::where('is_published', true)
            ->where('position', '>', $step->position)
            ->orderBy('position')
            ->first();

        if ($next) {
            return redirect()->route('build.show', $next);
        }

        return redirect()
            ->route('build.index')
            ->with('success', 'Сборка пройдена. Скачайте исходники и сравните со своим проектом.');
    }

    public function reset(Request $request)
    {
        BuildProgress::where('user_id', $request->user()->id)->delete();

        return redirect()
            ->route('build.index')
            ->with('success', 'Прогресс сборки сброшен.');
    }

    public function download()
    {
        $source = require database_path('seeders/data/build-files.php');

        $path = tempnam(sys_get_temp_dir(), 'build_').'.zip';

        $zip = new ZipArchive();

        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Не удалось создать архив.');
        }

        foreach ($source as $filePath => $file) {
            $zip->addFromString('conference/'.$filePath, $file['code']);
        }

        $zip->close();

        return response()
            ->download($path, 'conference-reference.zip')
            ->deleteFileAfterSend(true);
    }
}
