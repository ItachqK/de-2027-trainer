<?php

namespace App\Services;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class CapstoneAuditor
{
    private const MAX_FILES = 400;

    /**
     * Предварительный статический аудит Laravel-проекта.
     * Код проекта не запускается: читаются только файлы.
     */
    public function audit(string $root): array
    {
        $root = rtrim($root, DIRECTORY_SEPARATOR);

        $routes = $this->read($root.'/routes/web.php');
        $controllers = $this->readMany($this->files($root.'/app/Http/Controllers', ['php']));
        $views = $this->readMany($this->files($root.'/resources/views', ['php']));
        $migrations = $this->readMany($this->files($root.'/database/migrations', ['php']));
        $css = $this->readMany(array_merge(
            $this->files($root.'/resources/css', ['css']),
            $this->files($root.'/public/css', ['css'])
        ));
        $allPhp = $routes."\n".$controllers."\n".$migrations;
        $readme = $this->readFirst($root, ['README.md', 'readme.md', 'README.txt']);
        $mobileFiles = glob($root.'/mobile-design/*.html') ?: [];
        $mobileHtml = $this->readMany($mobileFiles);

        $checks = [];
        $add = function (string $group, string $label, int $points, bool $passed, string $message) use (&$checks) {
            $checks[] = [
                'group' => $group,
                'label' => $label,
                'points' => $points,
                'earned' => $passed ? $points : 0,
                'passed' => $passed,
                'message' => $message,
            ];
        };

        // 1. Разработка модулей — 6
        $skeleton = is_file($root.'/artisan')
            && is_file($root.'/composer.json')
            && is_file($root.'/public/index.php')
            && is_dir($root.'/app')
            && is_dir($root.'/resources/views');
        $add('Разработка модулей', 'Каркас Laravel', 1, $skeleton,
            $skeleton ? 'artisan, composer.json, public/index.php и папки на месте.' : 'Не найден полноценный каркас Laravel (artisan/composer/public/app/views).');

        $authRoutes = $this->any($routes, ['/register', '/login', '/profile', '/dashboard']);
        $add('Разработка модулей', 'Маршруты входа и кабинета', 2, $authRoutes,
            $authRoutes ? 'В routes/web.php есть вход, регистрация и кабинет.' : 'В routes/web.php не найдены /register, /login и /profile (или /dashboard).');

        $adminStatus = $this->any($routes.$controllers, ['admin', 'status']);
        $add('Разработка модулей', 'Админка и смена статуса', 2, $adminStatus,
            $adminStatus ? 'Найдены админские маршруты и работа со статусом.' : 'Не найдены админские маршруты или смена статуса.');

        $crud = count($this->files($root.'/app/Http/Controllers', ['php'])) >= 2 && $this->any($views, ['<form', '@csrf']);
        $add('Разработка модулей', 'CRUD и формы', 1, $crud,
            $crud ? 'Есть несколько контроллеров и формы.' : 'Недостаточно контроллеров или форм для CRUD.');

        // 2. Безопасность — 16
        $hash = $this->any($allPhp, ['Hash::make', 'password_hash']);
        $attempt = $this->any($allPhp, ['Auth::attempt', 'Auth::login']);
        $add('Безопасность', 'Хеширование и вход', 4, $hash && $attempt,
            $hash && $attempt ? 'Пароль хешируется, вход идёт через Auth.' : 'Не найдены Hash::make/password_hash и Auth::attempt/Auth::login.');

        $session = $this->has($allPhp, 'session()->regenerate') && $this->any($allPhp, ['Auth::logout', 'invalidate()']);
        $add('Безопасность', 'Сессия и выход', 2, $session,
            $session ? 'Сессия обновляется, выход завершает её.' : 'Нет session()->regenerate() или корректного выхода.');

        $role = $this->any($allPhp, ['isAdmin', "role === 'admin'", 'role == "admin"', "role === \"admin\""]);
        $middleware = $this->any($routes, ["middleware('admin')", 'middleware(["auth", "admin"])', "middleware(['auth', 'admin'])", 'isAdmin'])
            || $this->any($allPhp, ['abort(403', 'Auth::check']);
        $add('Безопасность', 'Роли и защита админки', 4, $role && $middleware,
            $role && $middleware ? 'Роль проверяется, админка закрыта.' : 'Нет проверки роли или middleware для /admin.');

        $owner = $this->any($allPhp, ['auth()->id()', 'Auth::id()']) && $this->has($allPhp, 'user_id');
        $add('Безопасность', 'Доступ только к своим заявкам', 2, $owner,
            $owner ? 'Есть привязка заявки к текущему пользователю и проверка user_id.' : 'Не найдена проверка владельца заявки (auth()->id()/Auth::id() и user_id).');

        $csrf = $this->has($views, '@csrf');
        $add('Безопасность', 'CSRF в формах', 2, $csrf,
            $csrf ? 'В Blade-формах есть @csrf.' : 'В формах не найден @csrf.');

        $passwordRules = $this->any($allPhp, ['min:8', 'min:6']) && $this->any($allPhp, ['unique:users', 'password_hash']);
        $add('Безопасность', 'Правила пароля и логина', 2, $passwordRules,
            $passwordRules ? 'Есть ограничение длины и уникальность.' : 'Не найдены min:8 и unique:users.');

        // 3. Информация — 3
        $er = $this->findEr($root);
        $add('Информация', 'ER-диаграмма', 2, $er !== null,
            $er !== null ? 'Найдена ER-диаграмма: '.$this->relative($root, $er) : 'В docs или корне проекта нет ER-диаграммы (svg/png/pdf/md).');

        $readmeOk = $readme !== '' && $this->any($readme, ['artisan serve', 'artisan migrate', 'migrate --seed']);
        $add('Информация', 'README с запуском', 1, $readmeOk,
            $readmeOk ? 'README объясняет запуск проекта.' : 'README не найден или в нём нет команд запуска.');

        // 4. Дизайн-концепция — 11
        $tokens = $this->any($css, [':root', '--']) && $this->any($css, ['#007bff', '#0d47a1', 'font-family']);
        $add('Дизайн-концепция', 'Палитра и переменные стиля', 3, $tokens,
            $tokens ? 'В CSS есть переменные и заданная палитра.' : 'Не найдены CSS-переменные/палитра.');

        $fonts = $this->any($views.$css, ['bootstrap', '@font-face', 'font-family']);
        $add('Дизайн-концепция', 'Шрифт и библиотека', 3, $fonts,
            $fonts ? 'Подключены Bootstrap или собственный шрифт.' : 'Не найдены Bootstrap или подключение шрифта.');

        $mobileOk = count($mobileFiles) >= 5 && $this->any($mobileHtml, ['390', 'width=390']);
        $add('Дизайн-концепция', 'Мобильный макет 390×844', 5, $mobileOk,
            $mobileOk ? 'Найдено '.count($mobileFiles).' HTML-экранов под 390 px.' : 'Нужно минимум 5 HTML-файлов в mobile-design с шириной 390.');

        // 5. Современные тенденции — 10
        $media = $this->has($css, '@media') || $this->any($views, ['col-md', 'col-lg', 'col-sm', 'container', 'row']);
        $add('Современные тенденции', 'Адаптивные медиазапросы', 3, $media,
            $media ? 'Есть @media или адаптивная сетка Bootstrap.' : 'В CSS нет медиазапросов и в шаблонах нет адаптивной сетки.');

        $motion = $this->any($css, ['transition', '@keyframes', 'animation']);
        $add('Современные тенденции', 'Микроанимации', 3, $motion,
            $motion ? 'Есть transition или @keyframes.' : 'Не найдены transition/@keyframes.');

        $images = $this->has($views, 'alt=') && ((glob($root.'/public/img/*') ?: []) !== [] || (glob($root.'/public/images/*') ?: []) !== []);
        $add('Современные тенденции', 'Изображения и alt', 4, $images,
            $images ? 'Есть изображения и alt-атрибуты.' : 'Не найдены изображения в public/img или alt в шаблонах.');

        // 6. Требования к дизайну — 4
        $empty = $this->any($views, ['@forelse', '@empty', 'пока нет', 'нет заявок']);
        $statuses = $this->any($views, ['status', 'badge']);
        $add('Требования к дизайну', 'Статусы и пустые состояния', 4, $empty && $statuses,
            $empty && $statuses ? 'Есть статусы и пустые состояния.' : 'Не найдены пустые состояния (@forelse/@empty) или статусы.');

        // 7. Интерфейс — 15
        $errors = $this->has($views, '@error') && $this->any($views, ['is-invalid', 'invalid-feedback', 'error']);
        $add('Интерфейс', 'Ошибки валидации у полей', 6, $errors,
            $errors ? 'Ошибки выводятся у полей.' : 'Не найдены @error и вывод ошибок у полей.');

        $select = $this->has($views, '<select') && $this->any($views, ['room_id', 'object_id', 'car_id']);
        $add('Интерфейс', 'Выпадающий список объекта', 3, $select,
            $select ? 'Есть select для выбора помещения/объекта.' : 'Не найден select с room_id/object_id.');

        $filters = $this->any($controllers, ["filled('status')", 'request(\'status\')', 'request("status")', '->when']) && $this->any($controllers, ['search', 'whereHas', "filled('search')"]);
        $add('Интерфейс', 'Фильтр и поиск в админке', 3, $filters,
            $filters ? 'Есть фильтрация и поиск.' : 'Не найдены фильтр по статусу и поиск.');

        $review = $this->has($allPhp.$views, 'completed') && $this->has($allPhp.$views, 'review');
        $add('Интерфейс', 'Отзыв после завершения', 3, $review,
            $review ? 'Есть отзыв и проверка статуса completed.' : 'Не найден отзыв или условие completed.');

        // 8. Веб-приложение — 10
        $migrationFiles = $this->files($root.'/database/migrations', ['php']);
        $migrationsOk = count($migrationFiles) >= 3 && $this->any($migrations, ['foreignId', 'constrained']) && $this->has($migrations, 'timestamps');
        $add('Веб-приложение', 'Миграции и связи', 3, $migrationsOk,
            $migrationsOk ? 'Есть миграции со связями и timestamps.' : 'Нужно минимум 3 миграции с foreignId/constrained и timestamps.');

        $env = is_file($root.'/.env.example') && is_file($root.'/.gitignore') && $this->has($this->read($root.'/.gitignore'), '.env');
        $storage = is_dir($root.'/public/storage') || $this->has($readme, 'storage:link');
        $add('Веб-приложение', 'Окружение и storage', 2, $env && $storage,
            $env && $storage ? 'Есть .env.example, .gitignore и storage:link.' : 'Не хватает .env.example/.gitignore или storage:link.');

        $tests = count($this->files($root.'/tests/Feature', ['php'])) > 0;
        $add('Веб-приложение', 'Тесты', 2, $tests,
            $tests ? 'Есть Feature-тесты.' : 'В tests/Feature нет тестов.');

        $git = is_dir($root.'/.git');
        $add('Веб-приложение', 'Git-репозиторий', 3, $git,
            $git ? 'Проект под Git.' : 'В папке проекта нет .git.');

        $score = array_sum(array_column($checks, 'earned'));
        $max = array_sum(array_column($checks, 'points'));

        $groups = [];
        foreach ($checks as $check) {
            $groups[$check['group']] ??= ['earned' => 0, 'max' => 0];
            $groups[$check['group']]['earned'] += $check['earned'];
            $groups[$check['group']]['max'] += $check['points'];
        }

        return [
            'score' => $score,
            'max' => $max,
            'checks' => $checks,
            'groups' => $groups,
            'summary' => [
                'php_files' => count($this->files($root.'/app', ['php'])),
                'views' => count($this->files($root.'/resources/views', ['php'])),
                'migrations' => count($migrationFiles),
                'mobile_pages' => count($mobileFiles),
            ],
        ];
    }

    private function files(string $dir, array $suffixes): array
    {
        if (! is_dir($dir)) {
            return [];
        }

        $result = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getSize() > 2_000_000) {
                continue;
            }

            foreach ($suffixes as $suffix) {
                if (str_ends_with($file->getFilename(), $suffix)) {
                    $result[] = $file->getPathname();
                    break;
                }
            }

            if (count($result) >= self::MAX_FILES) {
                break;
            }
        }

        return $result;
    }

    private function readMany(array $files): string
    {
        $content = '';

        foreach ($files as $file) {
            $content .= "\n".$this->read($file);
        }

        return $content;
    }

    private function read(string $file): string
    {
        return is_file($file) ? (string) file_get_contents($file) : '';
    }

    private function readFirst(string $root, array $names): string
    {
        foreach ($names as $name) {
            if (is_file($root.'/'.$name)) {
                return $this->read($root.'/'.$name);
            }
        }

        return '';
    }

    private function has(string $haystack, string $needle): bool
    {
        return $needle !== '' && mb_stripos($haystack, $needle) !== false;
    }

    private function any(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($this->has($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function findEr(string $root): ?string
    {
        $dirs = [$root.'/docs', $root];
        $extensions = ['svg', 'png', 'pdf', 'md', 'html'];

        foreach ($dirs as $dir) {
            if (! is_dir($dir)) {
                continue;
            }

            foreach (glob($dir.'/*') ?: [] as $file) {
                if (! is_file($file)) {
                    continue;
                }

                $name = mb_strtolower(basename($file));
                $ext = mb_strtolower(pathinfo($file, PATHINFO_EXTENSION));

                if (str_contains($name, 'er') && in_array($ext, $extensions, true)) {
                    return $file;
                }
            }
        }

        return null;
    }

    private function relative(string $root, string $file): string
    {
        return ltrim(str_replace($root, '', $file), DIRECTORY_SEPARATOR);
    }
}
