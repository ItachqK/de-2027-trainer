<?php

namespace Database\Seeders;

use App\Models\CaseStudy;
use App\Models\ProjectVariant;
use Illuminate\Database\Seeder;

class PizzaCaseSeeder extends Seeder
{
    public function run(): void
    {
        $files = require database_path('seeders/data/case-files/pizza.php');

        $file = fn (string $path) => [
            'path' => $path,
            'language' => $files[$path]['language'],
            'code' => $files[$path]['code'],
        ];

        $chapters = [
            [
                'title' => 'Задача и модель данных',
                'goal' => 'Понять сценарий доставки до первой команды.',
                'paragraphs' => [
                    'Пользователь регистрируется, выбирает пиццу из списка или описывает свою, указывает адрес, телефон, дату и время, количество и оплату.',
                    'Администратор ведёт заказ по статусам: `new`, `on_the_way`, `delivered`, `cancelled`. При отмене причина обязательна.',
                    'Основные таблицы: `users`, `pizzas`, `orders`. Заказ ссылается на пользователя и, если выбрана готовая пицца, на справочник `pizzas`.',
                ],
                'commands' => [],
                'files' => [],
                'expert' => [
                    'Список пицц и «Иная пицца» работают по заданию.',
                    'Отмена требует причину.',
                ],
                'mistakes' => [
                    'Сделать только свободный ввод вместо списка.',
                    'Забыть причину отмены.',
                ],
            ],
            [
                'title' => 'Каркас и база',
                'goal' => 'Поднять Laravel, подключить SQLite и сделать первый коммит.',
                'paragraphs' => [
                    'Проект создаётся Composer-ом. Сразу генерируется ключ. Затем создаётся SQLite и применяются стартовые миграции.',
                    'Первый коммит — точка возврата. На экзамене коммиты нужны минимум в начале и в конце.',
                ],
                'commands' => [
                    ['label' => 'Создать проект', 'code' => "composer create-project laravel/laravel pizza\ncd pizza\nphp artisan key:generate"],
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
                'goal' => 'Описать пользователей, пиццы и заказы.',
                'paragraphs' => [
                    'В `users` — ФИО, логин, телефон, email, пароль и роль. Логин и email уникальны.',
                    'В `pizzas` — название, цена и картинка. В `orders` — адрес, телефон, дата доставки, ссылка на пиццу, своя пицца, количество, оплата, статус и причина отмены.',
                    '`pizza_id` nullable: заказ может быть со своей пиццей. `cancel_reason` — отдельное поле, иначе причину негде сохранить.',
                ],
                'commands' => [
                    ['label' => 'Применить', 'code' => 'php artisan migrate'],
                ],
                'files' => [
                    $file('database/migrations/0001_01_01_000000_create_users_table.php'),
                    $file('database/migrations/2026_01_01_000001_create_pizzas_table.php'),
                    $file('database/migrations/2026_01_01_000002_create_orders_table.php'),
                ],
                'expert' => [
                    '`pizza_id` nullable, есть причина отмены.',
                    'Количество — целое число.',
                ],
                'mistakes' => [
                    'Сделать `pizza_id` обязательным и сломать «Иную пиццу».',
                    'Забыть количество.',
                ],
            ],
            [
                'title' => 'Модели',
                'goal' => 'Связать заказ с пользователем и пиццей.',
                'paragraphs' => [
                    'У пользователя много заказов, у пиццы — тоже. Заказ принадлежит пользователю и, опционально, пицце.',
                    'В `$fillable` — все поля формы и причина отмены.',
                ],
                'commands' => [],
                'files' => [
                    $file('app/Models/User.php'),
                    $file('app/Models/Pizza.php'),
                    $file('app/Models/Order.php'),
                ],
                'expert' => [
                    'Связи с пользователем и пиццей есть.',
                    'Нет запросов к базе во view.',
                ],
                'mistakes' => [
                    'Забыть `custom_pizza` в `$fillable`.',
                    'Не связать заказ с пользователем.',
                ],
            ],
            [
                'title' => 'Сидеры',
                'goal' => 'Получить одинаковую базу одной командой.',
                'paragraphs' => [
                    'Сидер администратора создаёт `aDminka25` / `passw0rd`. Сидер студента — демо-аккаунт. Сидер пицц заполняет шесть видов, сидер заказов даёт пример для кабинета и админки.',
                ],
                'commands' => [
                    ['label' => 'Заполнить', 'code' => 'php artisan db:seed'],
                ],
                'files' => [
                    $file('database/seeders/DatabaseSeeder.php'),
                    $file('database/seeders/UserSeeder.php'),
                    $file('database/seeders/PizzaSeeder.php'),
                    $file('database/seeders/OrderSeeder.php'),
                ],
                'expert' => [
                    'Шесть пицц и админ появляются на чистой базе.',
                    'Пароли хешируются.',
                ],
                'mistakes' => [
                    'Сидер падает при повторном запуске.',
                    'Нет администратора — статусы не проверить.',
                ],
            ],
            [
                'title' => 'Регистрация и вход',
                'goal' => 'Соблюсти пароль с заглавной буквой и формат телефона.',
                'paragraphs' => [
                    'Логин — кириллица или цифры, не менее 6 символов. Пароль — от 6 символов с одной заглавной буквой. Телефон — `+7(XXX)-XXX-XX-XX`.',
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
                    'Правило заглавной буквы проверяется на сервере.',
                    'Студент не может открыть `/admin`.',
                ],
                'mistakes' => [
                    'Проверять пароль только регуляркой в JS.',
                    'Пропустить уникальность логина.',
                ],
            ],
            [
                'title' => 'Заказ и «Иная пицца»',
                'goal' => 'Связать список, чекбокс и текстовое поле.',
                'paragraphs' => [
                    'Если чекбокс «Иная пицца» включён, описание обязательно. Если выбрана готовая пицца, `pizza_id` берётся из списка.',
                    'Условная валидация `required_if` решает это на сервере, а не только скрытием поля. Количество — от 1 до 20, оплата — наличные или карта.',
                ],
                'commands' => [],
                'files' => [
                    $file('resources/views/layouts/app.blade.php'),
                    $file('resources/views/home.blade.php'),
                    $file('app/Http/Controllers/OrderController.php'),
                    $file('resources/views/orders/index.blade.php'),
                    $file('resources/views/order/create.blade.php'),
                    $file('public/css/app.css'),
                ],
                'expert' => [
                    'Условная валидация работает.',
                    'Количество и оплата проверены.',
                ],
                'mistakes' => [
                    'Оставить `custom_pizza` без `required_if`.',
                    'Разрешить заказ в прошлом.',
                ],
            ],
            [
                'title' => 'Админка и доставка',
                'goal' => 'Провести заказ по статусам.',
                'paragraphs' => [
                    'Статусы: `new`, `on_the_way`, `delivered`, `cancelled`. При отмене причина обязательна. В админке видны ФИО, контакты и состав заказа.',
                ],
                'commands' => [],
                'files' => [
                    $file('app/Http/Controllers/AdminOrderController.php'),
                    $file('resources/views/admin/orders/index.blade.php'),
                ],
                'expert' => [
                    'Причина отмены обязательна.',
                    'Статусы соответствуют заданию.',
                ],
                'mistakes' => [
                    'Разрешить отмену без причины.',
                    'Не показывать состав заказа.',
                ],
            ],
            [
                'title' => 'Маршруты, тесты и сдача',
                'goal' => 'Собрать всё вместе и проверить на чистой базе.',
                'paragraphs' => [
                    'Полный `routes/web.php` связывает регистрацию, заказы и админку. Дальше — `migrate:fresh --seed`, тесты и ручной сценарий.',
                    'Перед сдачей проверяют 390, 768 и 1440 px, консоль, причину отмены, вход администратора и коммиты.',
                ],
                'commands' => [
                    ['label' => 'Чистая база', 'code' => 'php artisan migrate:fresh --seed'],
                    ['label' => 'Тесты', 'code' => 'php artisan test'],
                    ['label' => 'Запуск', 'code' => 'php artisan serve'],
                    ['label' => 'Git', 'code' => "git add .\ngit commit -m \"Готовый проект: заказы, пиццы, доставка\"\ngit push -u origin main"],
                ],
                'files' => [
                    $file('routes/web.php'),
                    $file('tests/Feature/OrderTest.php'),
                    $file('README.md'),
                ],
                'expert' => [
                    'Проект запускается с нуля и проходит сценарий.',
                    'Есть тесты, README и коммиты.',
                ],
                'mistakes' => [
                    'Проверять только на заполненной базе.',
                    'Забыть мобильный макет и причину отмены.',
                ],
            ],
        ];

        $variant = ProjectVariant::where('slug', 'pizza')->first();

        CaseStudy::updateOrCreate(
            ['slug' => 'pizza-build'],
            [
                'project_variant_id' => $variant?->id,
                'title' => 'Как собрали «Волшебную пиццу»',
                'subtitle' => 'Полный разбор: 9 глав, 28 файлов, от консоли до тестов',
                'summary' => 'Доставка пиццы: выбор из списка, «Иная пицца», количество, оплата, статусы и причина отмены. Полный код, а не фрагменты.',
                'stack' => 'Laravel · SQLite · Blade',
                'duration' => 210,
                'level' => 'средний',
                'chapters' => $chapters,
                'position' => 3,
                'is_published' => true,
            ]
        );

        $this->command?->info('Разбор «Волшебная пицца»: 9 глав, 28 файлов.');
    }
}
