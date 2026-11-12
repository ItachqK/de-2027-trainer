<?php

namespace Database\Seeders;

use App\Models\CaseStudy;
use App\Models\ProjectVariant;
use Illuminate\Database\Seeder;

class RestaurantCaseSeeder extends Seeder
{
    public function run(): void
    {
        $files = require database_path('seeders/data/case-files/restaurant.php');

        $file = fn (string $path) => [
            'path' => $path,
            'language' => $files[$path]['language'],
            'code' => $files[$path]['code'],
        ];

        $chapters = [
            [
                'title' => 'Задача и модель данных',
                'goal' => 'Понять сценарий ресторана до первой команды.',
                'paragraphs' => [
                    'Пользователь регистрируется, бронирует столик и видит свои брони. Администратор подтверждает визит или отменяет бронь. Отзыв доступен только после посещения.',
                    'Основные таблицы: `users` и `bookings`. В пользователе — имя, фамилия, логин, телефон и email. В брони — дата, время, гости, телефон и статус.',
                    'Статусы: `new`, `visited`, `cancelled`. Оценка и отзыв появляются только после `visited`.',
                ],
                'commands' => [],
                'files' => [],
                'expert' => [
                    'Сценарий: регистрация → бронь → админка → визит → отзыв.',
                    'Статусы соответствуют заданию.',
                ],
                'mistakes' => [
                    'Разрешить отзыв сразу после создания брони.',
                    'Забыть ограничение гостей 1–10.',
                ],
            ],
            [
                'title' => 'Каркас и база',
                'goal' => 'Поднять Laravel, подключить SQLite и сделать первый коммит.',
                'paragraphs' => [
                    'Проект создаётся Composer-ом. Сразу генерируется ключ приложения. Затем создаётся файл SQLite и применяются стартовые миграции.',
                    'Первый коммит — точка возврата. На экзамене коммиты нужны минимум в начале и в конце.',
                ],
                'commands' => [
                    ['label' => 'Создать проект', 'code' => "composer create-project laravel/laravel restaurant\ncd restaurant\nphp artisan key:generate"],
                    ['label' => 'Подключить SQLite', 'code' => "type nul > database\\database.sqlite\nphp artisan migrate"],
                    ['label' => 'Запустить', 'code' => 'php artisan serve'],
                    ['label' => 'Первый коммит', 'code' => "git init\ngit add .\ngit commit -m \"Старт проекта: Laravel + SQLite\""],
                ],
                'files' => [
                    $file('app/Providers/AppServiceProvider.php'),
                ],
                'expert' => [
                    'Проект запускается с нуля.',
                    'В репозитории нет `.env` и `vendor`.',
                ],
                'mistakes' => [
                    'Закоммитить `.env`.',
                    'Забыть `key:generate`.',
                ],
            ],
            [
                'title' => 'Миграции',
                'goal' => 'Описать пользователей и брони кодом.',
                'paragraphs' => [
                    'В `users` — имя, фамилия, логин, телефон, email, пароль и роль. Логин и email уникальны.',
                    'В `bookings` — дата, время `ЧЧ:ММ`, гости, телефон, статус, оценка и отзыв. Статус по умолчанию `new`.',
                    'Внешний ключ `user_id` связывает бронь с владельцем.',
                ],
                'commands' => [
                    ['label' => 'Применить', 'code' => 'php artisan migrate'],
                ],
                'files' => [
                    $file('database/migrations/0001_01_01_000000_create_users_table.php'),
                    $file('database/migrations/2026_01_01_000001_create_bookings_table.php'),
                ],
                'expert' => [
                    'Гости — целое число, статус по умолчанию `new`.',
                    'Оценка и отзыв nullable.',
                ],
                'mistakes' => [
                    'Хранить дату и время одной строкой без формата.',
                    'Разрешить 0 гостей.',
                ],
            ],
            [
                'title' => 'Модели',
                'goal' => 'Связать бронь с пользователем.',
                'paragraphs' => [
                    'У пользователя много броней, бронь принадлежит пользователю. Этого достаточно для кабинета и админки.',
                    'В `$fillable` — поля формы и служебные поля статуса. Пароль кастуется как `hashed`.',
                ],
                'commands' => [],
                'files' => [
                    $file('app/Models/User.php'),
                    $file('app/Models/Booking.php'),
                ],
                'expert' => [
                    'Связь `bookings()` и `user()` есть.',
                    'Нет запросов к базе во view.',
                ],
                'mistakes' => [
                    'Забыть `$fillable`.',
                    'Не связать бронь с пользователем.',
                ],
            ],
            [
                'title' => 'Сидеры',
                'goal' => 'Получить одинаковую базу одной командой.',
                'paragraphs' => [
                    'Сидер администратора создаёт `admin` / `restaurant`. Сидер студента — демо-аккаунт. Сидер броней даёт новую и состоявшуюся бронь, чтобы сразу проверить отзыв и админку.',
                ],
                'commands' => [
                    ['label' => 'Заполнить', 'code' => 'php artisan db:seed'],
                ],
                'files' => [
                    $file('database/seeders/DatabaseSeeder.php'),
                    $file('database/seeders/UserSeeder.php'),
                    $file('database/seeders/BookingSeeder.php'),
                ],
                'expert' => [
                    'Админ и демо-брони появляются на чистой базе.',
                    'Пароли хешируются.',
                ],
                'mistakes' => [
                    'Сидер падает при повторном запуске.',
                    'Нет администратора — статусы не проверить.',
                ],
            ],
            [
                'title' => 'Регистрация и вход',
                'goal' => 'Соблюсти правила КИМ: логин, телефон, пароль.',
                'paragraphs' => [
                    'Логин — кириллица или цифры, не менее 6 символов. Телефон — строго `+7(XXX)-XXX-XX-XX`. Email и логин уникальны, пароль от 6 символов с подтверждением.',
                    'После регистрации пользователь сразу авторизуется. Вход ищет по логину и проверяет хеш. Сессия регенерируется. Middleware `admin` закрывает админку.',
                ],
                'commands' => [],
                'files' => [
                    $file('app/Http/Controllers/Controller.php'),
                    $file('app/Http/Controllers/AuthController.php'),
                    $file('resources/views/auth/register.blade.php'),
                    $file('resources/views/auth/login.blade.php'),
                    $file('app/Http/Middleware/EnsureUserIsAdmin.php'),
                    $file('bootstrap/app.php'),
                ],
                'expert' => [
                    'Серверная валидация, ошибки у полей.',
                    'Пароль не хранится открытым текстом.',
                    'Студент не может открыть `/admin`.',
                ],
                'mistakes' => [
                    'Пропустить уникальность логина.',
                    'Разрешить роль `admin` при регистрации.',
                ],
            ],
            [
                'title' => 'Бронирование и отзыв',
                'goal' => 'Создать бронь и разрешить отзыв после визита.',
                'paragraphs' => [
                    'Форма проверяет дату, время, гостей и телефон. Дата не в прошлом, гостей от 1 до 10. После сохранения статус `new`.',
                    'Кабинет делает запрос `$request->user()->bookings()`, поэтому чужие брони туда не попадают. Отзыв принимается только для своей брони со статусом `visited`.',
                ],
                'commands' => [],
                'files' => [
                    $file('resources/views/layouts/app.blade.php'),
                    $file('resources/views/home.blade.php'),
                    $file('app/Http/Controllers/BookingController.php'),
                    $file('resources/views/bookings/index.blade.php'),
                    $file('resources/views/booking/create.blade.php'),
                    $file('public/css/app.css'),
                ],
                'expert' => [
                    'Дата не в прошлом, гости 1–10.',
                    'Чужая бронь при отзыве даёт 403.',
                    'Отзыв только после визита.',
                ],
                'mistakes' => [
                    'Не проверять владельца при отзыве.',
                    'Разрешить отзыв без визита.',
                ],
            ],
            [
                'title' => 'Админка',
                'goal' => 'Провести бронь по статусам.',
                'paragraphs' => [
                    'Админ видит все брони, фильтрует по статусу и меняет его: `new`, `visited`, `cancelled`. В задании также просят пагинацию и всплывающие сообщения.',
                ],
                'commands' => [],
                'files' => [
                    $file('app/Http/Controllers/AdminBookingController.php'),
                    $file('resources/views/admin/bookings/index.blade.php'),
                ],
                'expert' => [
                    'Статусы только из списка.',
                    'Фильтр и пагинация работают.',
                ],
                'mistakes' => [
                    'Разрешить произвольный статус.',
                    'Забыть пагинацию.',
                ],
            ],
            [
                'title' => 'Маршруты, тесты и сдача',
                'goal' => 'Собрать всё вместе и проверить на чистой базе.',
                'paragraphs' => [
                    'Полный `routes/web.php` связывает регистрацию, брони и админку. Дальше — `migrate:fresh --seed`, тесты и ручной сценарий.',
                    'Перед сдачей проверяют 390, 768 и 1440 px, консоль, 403 на чужую бронь, вход администратора и коммиты.',
                ],
                'commands' => [
                    ['label' => 'Чистая база', 'code' => 'php artisan migrate:fresh --seed'],
                    ['label' => 'Тесты', 'code' => 'php artisan test'],
                    ['label' => 'Запуск', 'code' => 'php artisan serve'],
                    ['label' => 'Git', 'code' => "git add .\ngit commit -m \"Готовый проект: брони, отзывы, админка\"\ngit push -u origin main"],
                ],
                'files' => [
                    $file('routes/web.php'),
                    $file('tests/Feature/BookingTest.php'),
                    $file('README.md'),
                ],
                'expert' => [
                    'Проект запускается с нуля и проходит сценарий.',
                    'Есть тесты, README и коммиты.',
                ],
                'mistakes' => [
                    'Проверять только на заполненной базе.',
                    'Забыть мобильный макет и 403.',
                ],
            ],
        ];

        $variant = ProjectVariant::where('slug', 'restaurant')->first();

        CaseStudy::updateOrCreate(
            ['slug' => 'restaurant-build'],
            [
                'project_variant_id' => $variant?->id,
                'title' => 'Как собрали «Я буду кушац»',
                'subtitle' => 'Полный разбор: 9 глав, 25 файлов, от консоли до тестов',
                'summary' => 'Бронирование столиков: дата, время, гости, отзыв после визита, статусы и админка. Полный код, а не фрагменты.',
                'stack' => 'Laravel · SQLite · Blade',
                'duration' => 210,
                'level' => 'средний',
                'chapters' => $chapters,
                'position' => 2,
                'is_published' => true,
            ]
        );

        $this->command?->info('Разбор «Я буду кушац»: 9 глав, 25 файлов.');
    }
}
