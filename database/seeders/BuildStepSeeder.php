<?php

namespace Database\Seeders;

use App\Models\BuildStep;
use Illuminate\Database\Seeder;

class BuildStepSeeder extends Seeder
{
    public function run(): void
    {
        $source = require database_path('seeders/data/build-files.php');

        $routesHome = <<<'PHP'
Route::get('/', fn () => view('home'))->name('home');
PHP;

        $routesRegister = <<<'PHP'
use App\Http\Controllers\AuthController;

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});
PHP;

        $routesAuth = <<<'PHP'
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.store');

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');
PHP;

        $routesApplications = <<<'PHP'
use App\Http\Controllers\ApplicationController;

Route::middleware('auth')->group(function () {
    Route::get('/application', [ApplicationController::class, 'create'])->name('application.create');
    Route::post('/application', [ApplicationController::class, 'store'])->name('application.store');
    Route::get('/profile', [ApplicationController::class, 'profile'])->name('profile');
    Route::post('/applications/{application}/review', [ApplicationController::class, 'review'])->name('applications.review');
});
PHP;

        $routesAdmin = <<<'PHP'
use App\Http\Controllers\AdminController;

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::post('/applications/{application}/status', [AdminController::class, 'status'])->name('status');
});
PHP;

        $steps = [
            [
                'slug' => 'prepare',
                'title' => 'Каркас и база',
                'goal' => 'Поднять Laravel-проект, подключить SQLite, запустить сервер и сделать первый коммит. Здесь создаются только стандартные таблицы Laravel — `rooms` и `applications` появятся на следующем шаге.',
                'minutes' => 25,
                'commands' => [
                    ['label' => 'Проверить окружение', 'code' => "php -v\ncomposer -V"],
                    ['label' => 'Создать проект', 'code' => "composer create-project laravel/laravel conference\ncd conference\nphp artisan key:generate"],
                    ['label' => 'Подключить SQLite', 'code' => "type nul > database\\database.sqlite\nphp artisan migrate"],
                    ['label' => 'Запустить сервер', 'code' => 'php artisan serve'],
                    ['label' => 'Первый коммит', 'code' => "git init\ngit add .\ngit commit -m \"Старт проекта: Laravel + SQLite\""],
                ],
                'files' => [
                    'app/Http/Controllers/Controller.php',
                    'app/Providers/AppServiceProvider.php',
                ],
                'snippets' => [],
                'checks' => [
                    '`php -v` и `composer -V` отвечают.',
                    '`http://127.0.0.1:8000` открывается.',
                    'В `.env` стоит `DB_CONNECTION=sqlite`.',
                    'Есть первый коммит.',
                    'Таблицы `rooms` и `applications` появятся на шаге 02 «Миграции».',
                    '`db:seed` пока не запускаем — стандартный сидер Laravel упадёт с ошибкой про колонку `name`.',
                    'Стандартные файлы Laravel (`config`, `artisan`, `composer.json`, `public/index.php`, `bootstrap/providers.php`, `tests/TestCase.php`, `phpunit.xml`) не удаляем — в шагах их нет, но без них проект не работает.',
                ],
            ],
            [
                'slug' => 'database',
                'title' => 'Миграции',
                'goal' => 'Создать файлы миграций и таблицы пользователей, помещений и заявок. `migrate` применяет только уже созданные файлы — сначала создаём файлы, потом запускаем команду.',
                'minutes' => 25,
                'commands' => [
                    ['label' => 'Создать файл миграции rooms', 'code' => 'php artisan make:migration create_rooms_table'],
                    ['label' => 'Создать файл миграции applications', 'code' => 'php artisan make:migration create_applications_table'],
                    ['label' => 'Применить миграции', 'code' => 'php artisan migrate'],
                ],
                'files' => [
                    'database/migrations/0001_01_01_000000_create_users_table.php',
                    'database/migrations/2026_01_01_000001_create_rooms_table.php',
                    'database/migrations/2026_01_01_000002_create_applications_table.php',
                ],
                'snippets' => [],
                'checks' => [
                    'Файлы миграций созданы командой `make:migration`.',
                    'В созданные файлы вставлен код из блока «Файлы», а не пустой шаблон.',
                    'В базе есть `users`, `rooms`, `applications`.',
                    'В `users` есть поля `fio`, `login`, `phone`, `email`, `password`, `role`.',
                    '`applications` ссылается на `users` и `rooms`.',
                    'Статус по умолчанию — `new`.',
                ],
            ],
            [
                'slug' => 'models',
                'title' => 'Модели и связи',
                'goal' => 'Описать Eloquent-модели: пользователь, помещение, заявка. Связи позволяют получать данные без ручных JOIN. Модели `Room` и `Application` создаются командой, а стандартный `app/Models/User.php` из Laravel нужно заменить целиком: в нём поля `name`/`email`, а нам нужны `fio`, `login`, `phone`, `role`.',
                'minutes' => 20,
                'commands' => [
                    ['label' => 'Создать модель Room', 'code' => 'php artisan make:model Room'],
                    ['label' => 'Создать модель Application', 'code' => 'php artisan make:model Application'],
                ],
                'files' => [
                    'app/Models/User.php',
                    'app/Models/Room.php',
                    'app/Models/Application.php',
                    'database/factories/UserFactory.php',
                ],
                'snippets' => [],
                'checks' => [
                    'Стандартный `User.php` заменён целиком — в нём больше нет `name`.',
                    '`User` имеет `applications()`.',
                    '`Room` имеет `applications()`.',
                    '`Application` имеет `user()` и `room()`.',
                    'Пароль кастуется как `hashed`.',
                    '`UserFactory` соответствует полям `fio`, `login`, `phone`, `email`, `password`, `role`.',
                ],
            ],
            [
                'slug' => 'seeders',
                'title' => 'Сидеры',
                'goal' => 'Заполнить базу администратором, студентом, помещениями и демо-заявками. Сначала создаём сидеры, потом запускаем `db:seed` — команда сама файлы не создаёт.',
                'minutes' => 25,
                'commands' => [
                    ['label' => 'Создать сидер пользователей', 'code' => 'php artisan make:seeder UserSeeder'],
                    ['label' => 'Создать сидер помещений', 'code' => 'php artisan make:seeder RoomSeeder'],
                    ['label' => 'Создать сидер заявок', 'code' => 'php artisan make:seeder ApplicationSeeder'],
                    ['label' => 'Заполнить базу', 'code' => 'php artisan db:seed'],
                ],
                'files' => [
                    'database/seeders/DatabaseSeeder.php',
                    'database/seeders/UserSeeder.php',
                    'database/seeders/RoomSeeder.php',
                    'database/seeders/ApplicationSeeder.php',
                ],
                'snippets' => [],
                'checks' => [
                    'Есть `Conf2027` / `Demo77` — администратор, и `maria2027` / `Student2027!` — студент.',
                    'В `rooms` три помещения: Аудитория, Коворкинг, Кинозал.',
                    'У студента есть демо-заявки.',
                    '`DatabaseSeeder` заменён — иначе `db:seed` вызовет стандартный сидер.',
                ],
            ],
            [
                'slug' => 'pages',
                'title' => 'Каркас страниц',
                'goal' => 'Сделать общий layout, главную и локальный CSS. Это база интерфейса, которую дальше наполняем формами.',
                'minutes' => 25,
                'commands' => [],
                'files' => [
                    'resources/views/layouts/app.blade.php',
                    'resources/views/home.blade.php',
                    'public/css/app.css',
                ],
                'snippets' => [
                    ['path' => 'routes/web.php', 'language' => 'php', 'code' => $routesHome, 'note' => 'Добавьте маршрут главной.'],
                ],
                'checks' => [
                    'Главная открывается.',
                    'Навигация меняется после входа.',
                    'CSS подключён локально, без CDN.',
                    'Шрифт PT Sans и цвета КИМ: `#007bff`, `#0d47a1`, `#6c757d`, `#f8f9fa`.',
                    'Файлы `PTSans-*.ttf` из приложения КИМ лежат в `public/fonts`.',
                    'Ссылки на заявку и регистрацию пока ведут на страницы, которые появятся на шагах 06–08 — главная при этом не падает.',
                ],
            ],
            [
                'slug' => 'registration',
                'title' => 'Регистрация',
                'goal' => 'Сделать регистрацию по требованиям КИМ: ФИО, логин, email, пароль и подтверждение. Ошибки — у полей.',
                'minutes' => 30,
                'commands' => [
                    ['label' => 'Создать контроллер', 'code' => 'php artisan make:controller AuthController'],
                ],
                'files' => [
                    'app/Http/Controllers/AuthController.php',
                    'resources/views/auth/register.blade.php',
                ],
                'snippets' => [
                    ['path' => 'routes/web.php', 'language' => 'php', 'code' => $routesRegister, 'note' => 'Добавьте маршруты регистрации.'],
                ],
                'checks' => [
                    'Регистрация создаёт пользователя с ролью `student`.',
                    'Логин — латиница и цифры, не менее 6 символов.',
                    'Пароль — не менее 8 символов, с подтверждением.',
                    'Телефон — в формате `8(XXX)XXX-XX-XX`.',
                    'ФИО — только кириллица и пробелы.',
                    'Кнопка называется «Создать пользователя».',
                    'После регистрации редирект ведёт на `/profile` — сама страница появится на шаге 08.',
                ],
            ],
            [
                'slug' => 'auth',
                'title' => 'Вход и роли',
                'goal' => 'Добавить вход, выход и защиту админки. Студент не должен попадать в `/admin`.',
                'minutes' => 25,
                'commands' => [
                    ['label' => 'Создать middleware', 'code' => 'php artisan make:middleware EnsureUserIsAdmin'],
                ],
                'files' => [
                    'resources/views/auth/login.blade.php',
                    'app/Http/Middleware/EnsureUserIsAdmin.php',
                    'bootstrap/app.php',
                ],
                'snippets' => [
                    ['path' => 'routes/web.php', 'language' => 'php', 'code' => $routesAuth, 'note' => 'Добавьте вход и выход.'],
                ],
                'checks' => [
                    'Вход по логину и паролю работает.',
                    'Выход очищает сессию.',
                    'Студент получает 403 на `/admin`.',
                    'Администратор входит как `Conf2027` / `Demo77`.',
                    'После входа редирект ведёт на `/profile` — страница появится на шаге 08.',
                ],
            ],
            [
                'slug' => 'applications',
                'title' => 'Заявки и кабинет',
                'goal' => 'Сделать создание заявки, список своих заявок и отзыв после завершения.',
                'minutes' => 35,
                'commands' => [
                    ['label' => 'Создать контроллер', 'code' => 'php artisan make:controller ApplicationController'],
                ],
                'files' => [
                    'app/Http/Controllers/ApplicationController.php',
                    'resources/views/application.blade.php',
                    'resources/views/profile.blade.php',
                ],
                'snippets' => [
                    ['path' => 'routes/web.php', 'language' => 'php', 'code' => $routesApplications, 'note' => 'Добавьте маршруты заявок и кабинета.'],
                ],
                'checks' => [
                    'Заявка создаётся с статусом `new`.',
                    'Кабинет показывает только заявки текущего пользователя.',
                    'Оплата: «При очном посещении» или «Переводом по СБП».',
                    'Чужая заявка при отзыве даёт 403.',
                    'Отзыв доступен после `completed`.',
                ],
            ],
            [
                'slug' => 'admin',
                'title' => 'Админка',
                'goal' => 'Сделать панель администратора: все заявки, фильтр по статусу и смена статуса.',
                'minutes' => 30,
                'commands' => [
                    ['label' => 'Создать контроллер', 'code' => 'php artisan make:controller AdminController'],
                ],
                'files' => [
                    'app/Http/Controllers/AdminController.php',
                    'resources/views/admin/index.blade.php',
                ],
                'snippets' => [
                    ['path' => 'routes/web.php', 'language' => 'php', 'code' => $routesAdmin, 'note' => 'Добавьте админские маршруты.'],
                ],
                'checks' => [
                    'Админ видит все заявки.',
                    'Фильтр по статусу и поиск по ФИО работают.',
                    'Статусы: «Новая», «Мероприятие назначено», «Завершено».',
                ],
            ],
            [
                'slug' => 'finish',
                'title' => 'Маршруты, тесты и сдача',
                'goal' => 'Собрать полный `routes/web.php`, прогнать тесты и сценарий, проверить адаптив и сделать коммит.',
                'minutes' => 30,
                'commands' => [
                    ['label' => 'Чистая база', 'code' => 'php artisan migrate:fresh --seed'],
                    ['label' => 'Создать тест', 'code' => 'php artisan make:test ApplicationTest'],
                    ['label' => 'Тесты', 'code' => 'php artisan test'],
                    ['label' => 'Запуск', 'code' => 'php artisan serve'],
                    ['label' => 'Коммит и пуш', 'code' => "git add .\ngit commit -m \"Готовый проект: заявки, кабинет, админка\"\ngit remote add origin <url>\ngit push -u origin main"],
                ],
                'files' => [
                    'routes/web.php',
                    'tests/Feature/ApplicationTest.php',
                    'README.md',
                ],
                'snippets' => [],
                'checks' => [
                    '`migrate:fresh --seed` проходит на чистой базе.',
                    '`php artisan test` зелёный.',
                    'Сценарий: регистрация → вход → заявка → кабинет → админка → статус.',
                    '390 / 768 / 1440 px без горизонтальной прокрутки.',
                    'Есть коммиты и README.',
                ],
            ],
        ];

        foreach ($steps as $position => $step) {
            $files = [];

            foreach ($step['files'] as $path) {
                if (! isset($source[$path])) {
                    continue;
                }

                $files[] = [
                    'path' => $path,
                    'language' => $source[$path]['language'],
                    'code' => $source[$path]['code'],
                ];
            }

            BuildStep::updateOrCreate(
                ['slug' => $step['slug']],
                [
                    'title' => $step['title'],
                    'goal' => $step['goal'],
                    'minutes' => $step['minutes'],
                    'commands' => $step['commands'],
                    'files' => $files,
                    'snippets' => $step['snippets'],
                    'checks' => $step['checks'],
                    'position' => $position,
                    'is_published' => true,
                ]
            );
        }

        $this->command?->info('Сборка с нуля: шагов — '.BuildStep::count().'.');
    }
}
