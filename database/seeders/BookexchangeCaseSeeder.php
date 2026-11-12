<?php

namespace Database\Seeders;

use App\Models\CaseStudy;
use App\Models\ProjectVariant;
use Illuminate\Database\Seeder;

class BookexchangeCaseSeeder extends Seeder
{
    public function run(): void
    {
        $files = require database_path('seeders/data/case-files/bookexchange.php');

        $file = fn (string $path) => [
            'path' => $path,
            'language' => $files[$path]['language'],
            'code' => $files[$path]['code'],
        ];

        $chapters = [
            [
                'title' => 'Задача и модель данных',
                'goal' => 'Понять, что именно строим, до первой команды.',
                'paragraphs' => [
                    '«Буквоежка» — портал обмена книгами. Пользователь регистрируется, создаёт карточку книги и указывает тип: «готов поделиться» или «хочу в свою библиотеку». Карточка уходит на модерацию.',
                    'Администратор публикует карточку или отклоняет её с причиной. Пользователь видит свои активные карточки и архив: отклонённые и удалённые.',
                    'Получается две основные таблицы: `users` и `cards`. В `users` появляется телефон, в `cards` — тип, дополнительные сведения о книге, статус и причина отклонения.',
                ],
                'commands' => [],
                'files' => [],
                'expert' => [
                    'Сценарий: регистрация → карточка → модерация → архив.',
                    'Статусы и причина отклонения соответствуют заданию.',
                ],
                'mistakes' => [
                    'Публиковать карточку сразу, без модерации.',
                    'Забыть архив отклонённых карточек.',
                ],
            ],
            [
                'title' => 'Каркас и база',
                'goal' => 'Поднять Laravel, подключить SQLite и сделать первый коммит.',
                'paragraphs' => [
                    'Проект создаётся Composer-ом. Сразу генерируется ключ приложения. Затем создаётся файл SQLite и применяются стартовые миграции.',
                    'Первый коммит делается до кода: это точка возврата, если что-то сломается. На экзамене коммиты нужны минимум в начале и в конце.',
                ],
                'commands' => [
                    ['label' => 'Создать проект', 'code' => "composer create-project laravel/laravel bookexchange\ncd bookexchange\nphp artisan key:generate"],
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
                    'Закоммитить `.env` с ключом.',
                    'Забыть `key:generate`.',
                ],
            ],
            [
                'title' => 'Миграции',
                'goal' => 'Описать базу кодом: пользователи и карточки.',
                'paragraphs' => [
                    'В `users` добавляется телефон: по заданию он обязателен и хранится строкой. Логин и email уникальны.',
                    'В `cards` — автор и название, тип `share` или `want`, необязательные издательство, год, переплёт и состояние. Статус по умолчанию `pending`, причина отклонения — отдельное поле.',
                    'Внешний ключ `user_id` связывает карточку с владельцем. `cascadeOnDelete` не даёт карточке остаться без пользователя.',
                ],
                'commands' => [
                    ['label' => 'Применить', 'code' => 'php artisan migrate'],
                ],
                'files' => [
                    $file('database/migrations/0001_01_01_000000_create_users_table.php'),
                    $file('database/migrations/2026_01_01_000001_create_cards_table.php'),
                ],
                'expert' => [
                    'Обязательные и необязательные поля разделены.',
                    'Есть статус и причина отклонения.',
                ],
                'mistakes' => [
                    'Сделать все поля книги обязательными.',
                    'Хранить пароль открытым текстом.',
                ],
            ],
            [
                'title' => 'Модели',
                'goal' => 'Связать карточки с пользователем.',
                'paragraphs' => [
                    'У пользователя много карточек, карточка принадлежит пользователю. Этого достаточно для кабинета и админки.',
                    'В `$fillable` перечисляются поля формы и служебные поля статуса. Пароль кастуется как `hashed`.',
                ],
                'commands' => [],
                'files' => [
                    $file('app/Models/User.php'),
                    $file('app/Models/Card.php'),
                ],
                'expert' => [
                    'Связь `cards()` и `user()` есть.',
                    'Нет запросов к базе прямо во view.',
                ],
                'mistakes' => [
                    'Забыть `$fillable`.',
                    'Не связать карточку с пользователем.',
                ],
            ],
            [
                'title' => 'Сидеры',
                'goal' => 'Получить одинаковую базу одной командой.',
                'paragraphs' => [
                    'Сидер администратора создаёт `admin` / `bookworm`. Сидер студента — демо-аккаунт. Сидер карточек даёт опубликованную, ожидающую и отклонённую карточки, чтобы сразу проверить кабинет и админку.',
                    'На защите эксперт может выполнить `migrate:fresh --seed` и войти. Если данные заводятся только руками, сценарий не воспроизводится.',
                ],
                'commands' => [
                    ['label' => 'Заполнить', 'code' => 'php artisan db:seed'],
                ],
                'files' => [
                    $file('database/seeders/DatabaseSeeder.php'),
                    $file('database/seeders/UserSeeder.php'),
                    $file('database/seeders/CardSeeder.php'),
                ],
                'expert' => [
                    'Админ и демо-карточки появляются на чистой базе.',
                    'Пароли хешируются.',
                ],
                'mistakes' => [
                    'Сидер падает при повторном запуске.',
                    'Нет администратора — модерацию не проверить.',
                ],
            ],
            [
                'title' => 'Регистрация и вход',
                'goal' => 'Соблюсти правила КИМ: логин, телефон, пароль.',
                'paragraphs' => [
                    'Логин — кириллица или цифры, не менее 6 символов. Телефон — строго `+7(XXX)-XXX-XX-XX`. Email и логин уникальны, пароль от 6 символов с подтверждением.',
                    'После регистрации пользователь сразу авторизуется и попадает в свои карточки. Вход ищет по логину и проверяет хеш пароля. Сессия регенерируется.',
                    'Middleware `admin` закрывает админку: студент получает 403, а не пустую страницу.',
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
                'title' => 'Карточки и архив',
                'goal' => 'Создать карточку, показать свои и архив.',
                'paragraphs' => [
                    'Форма карточки содержит автора, название, radio-кнопки типа и необязательные сведения. После сохранения статус `pending`: карточка уходит на модерацию.',
                    'Кабинет делает запрос `$request->user()->cards()`, поэтому чужие карточки туда не попадают. Активные — `pending` и `published`, архив — `rejected` и `deleted`.',
                    'Удаление не стирает запись, а переводит в `deleted`: пользователь должен видеть архив. Перед удалением проверяется владелец, иначе 403.',
                ],
                'commands' => [],
                'files' => [
                    $file('app/Http/Controllers/CardController.php'),
                    $file('resources/views/layouts/app.blade.php'),
                    $file('resources/views/home.blade.php'),
                    $file('resources/views/cards/index.blade.php'),
                    $file('resources/views/cards/create.blade.php'),
                    $file('public/css/app.css'),
                ],
                'expert' => [
                    'Карточка уходит на проверку, а не публикуется сразу.',
                    'Пользователь видит только свои карточки.',
                    'Чужая карточка при удалении даёт 403.',
                ],
                'mistakes' => [
                    'Сделать select вместо radio.',
                    'Удалять запись физически и терять архив.',
                ],
            ],
            [
                'title' => 'Админка и модерация',
                'goal' => 'Опубликовать карточку или отклонить её с причиной.',
                'paragraphs' => [
                    'Админ видит все карточки и фильтрует по статусу. Публикация меняет статус на `published` и очищает причину. Отклонение требует причину и ставит `rejected`.',
                    'Причина обязательна: без неё пользователь не поймёт, что исправить. В задании это прямо требуется.',
                ],
                'commands' => [],
                'files' => [
                    $file('app/Http/Controllers/AdminCardController.php'),
                    $file('resources/views/admin/cards/index.blade.php'),
                ],
                'expert' => [
                    'Админ видит все карточки и владельца.',
                    'Отклонение с причиной работает.',
                    'Фильтр по статусу есть.',
                ],
                'mistakes' => [
                    'Отклонять без причины.',
                    'Разрешить произвольный статус.',
                ],
            ],
            [
                'title' => 'Маршруты, тесты и сдача',
                'goal' => 'Собрать всё вместе и проверить на чистой базе.',
                'paragraphs' => [
                    'Полный `routes/web.php` связывает регистрацию, карточки и админку. Дальше — `migrate:fresh --seed`, тесты и ручной сценарий.',
                    'Перед сдачей проверяют 390, 768 и 1440 px, консоль, 403 на чужую карточку, вход администратора и коммиты. README с запуском экономит время эксперту.',
                ],
                'commands' => [
                    ['label' => 'Чистая база', 'code' => 'php artisan migrate:fresh --seed'],
                    ['label' => 'Тесты', 'code' => 'php artisan test'],
                    ['label' => 'Запуск', 'code' => 'php artisan serve'],
                    ['label' => 'Git', 'code' => "git add .\ngit commit -m \"Готовый проект: карточки, модерация, архив\"\ngit push -u origin main"],
                ],
                'files' => [
                    $file('routes/web.php'),
                    $file('tests/Feature/CardTest.php'),
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

        $variant = ProjectVariant::where('slug', 'bookexchange')->first();

        CaseStudy::updateOrCreate(
            ['slug' => 'bookexchange-build'],
            [
                'project_variant_id' => $variant?->id,
                'title' => 'Как собрали «Буквоежку»',
                'subtitle' => 'Полный разбор: 9 глав, 25 файлов, от консоли до тестов',
                'summary' => 'Регистрация с кириллическим логином и телефоном, карточки «поделиться / хочу», модерация с причиной отклонения и архив. Полный код, а не фрагменты.',
                'stack' => 'Laravel · SQLite · Blade',
                'duration' => 210,
                'level' => 'средний',
                'chapters' => $chapters,
                'position' => 1,
                'is_published' => true,
            ]
        );

        $this->command?->info('Разбор «Буквоежка»: 9 глав, 25 файлов.');
    }
}
