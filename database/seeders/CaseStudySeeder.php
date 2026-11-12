<?php

namespace Database\Seeders;

use App\Models\CaseStudy;
use App\Models\ProjectVariant;
use Illuminate\Database\Seeder;

class CaseStudySeeder extends Seeder
{
    public function run(): void
    {
        $source = require database_path('seeders/data/build-files.php');

        $file = fn (string $path) => [
            'path' => $path,
            'language' => $source[$path]['language'],
            'code' => $source[$path]['code'],
        ];

        $bookMigration = <<<'PHP'
Schema::create('cards', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('author');
    $table->string('title');
    $table->string('type'); // share / want
    $table->string('publisher')->nullable();
    $table->unsignedSmallInteger('year')->nullable();
    $table->string('binding')->nullable();
    $table->string('condition')->nullable();
    $table->string('status')->default('pending');
    $table->text('rejection_reason')->nullable();
    $table->timestamps();
});
PHP;

        $bookModel = <<<'PHP'
class Card extends Model
{
    protected $fillable = [
        'user_id', 'author', 'title', 'type', 'publisher',
        'year', 'binding', 'condition', 'status', 'rejection_reason',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
PHP;

        $bookValidation = <<<'PHP'
$data = $request->validate([
    'fio' => ['required', 'regex:/^[А-Яа-яЁё\s-]+$/u'],
    'login' => ['required', 'regex:/^[А-Яа-яЁё0-9]{6,}$/u', 'unique:users,login'],
    'phone' => ['required', 'regex:/^\+7\(\d{3}\)-\d{3}-\d{2}-\d{2}$/'],
    'email' => ['required', 'email', 'unique:users,email'],
    'password' => ['required', 'min:6', 'confirmed'],
]);
PHP;

        $bookStore = <<<'PHP'
public function store(Request $request)
{
    $data = $request->validate([
        'author' => ['required', 'string', 'max:255'],
        'title' => ['required', 'string', 'max:255'],
        'type' => ['required', 'in:share,want'],
        'publisher' => ['nullable', 'string', 'max:255'],
        'year' => ['nullable', 'integer', 'between:1800,2100'],
        'binding' => ['nullable', 'in:hard,soft'],
        'condition' => ['nullable', 'in:ideal,normal,attention,leg'],
    ]);

    $request->user()->cards()->create($data + ['status' => 'pending']);

    return redirect()->route('cards.index')->with('success', 'Карточка отправлена на проверку.');
}
PHP;

        $bookAdmin = <<<'PHP'
public function reject(Request $request, Card $card)
{
    $data = $request->validate([
        'rejection_reason' => ['required', 'string', 'min:5', 'max:500'],
    ]);

    $card->update([
        'status' => 'rejected',
        'rejection_reason' => $data['rejection_reason'],
    ]);

    return back()->with('success', 'Карточка отклонена.');
}
PHP;

        $restMigration = <<<'PHP'
Schema::create('bookings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->date('visit_date');
    $table->string('visit_time', 5);
    $table->unsignedTinyInteger('guests');
    $table->string('phone');
    $table->string('status')->default('new');
    $table->text('review')->nullable();
    $table->unsignedTinyInteger('rating')->nullable();
    $table->timestamps();
});
PHP;

        $restModel = <<<'PHP'
class Booking extends Model
{
    protected $fillable = [
        'user_id', 'visit_date', 'visit_time',
        'guests', 'phone', 'status', 'review', 'rating',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
PHP;

        $restValidation = <<<'PHP'
$data = $request->validate([
    'visit_date' => ['required', 'date', 'after_or_equal:today'],
    'visit_time' => ['required', 'regex:/^\d{2}:\d{2}$/'],
    'guests' => ['required', 'integer', 'between:1,10'],
    'phone' => ['required', 'regex:/^\+7\(\d{3}\)-\d{3}-\d{2}-\d{2}$/'],
]);
PHP;

        $restStore = <<<'PHP'
public function store(Request $request)
{
    $data = $request->validate([
        'visit_date' => ['required', 'date', 'after_or_equal:today'],
        'visit_time' => ['required', 'regex:/^\d{2}:\d{2}$/'],
        'guests' => ['required', 'integer', 'between:1,10'],
        'phone' => ['required', 'regex:/^\+7\(\d{3}\)-\d{3}-\d{2}-\d{2}$/'],
    ]);

    $request->user()->bookings()->create($data + ['status' => 'new']);

    return redirect()->route('bookings.index')->with('success', 'Столик забронирован.');
}
PHP;

        $restAdmin = <<<'PHP'
public function status(Request $request, Booking $booking)
{
    $data = $request->validate([
        'status' => ['required', 'in:new,visited,cancelled'],
    ]);

    $booking->update(['status' => $data['status']]);

    return back()->with('success', 'Статус брони обновлён.');
}
PHP;

        $pizzaMigration = <<<'PHP'
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('address');
    $table->string('phone');
    $table->dateTime('delivery_at');
    $table->foreignId('pizza_id')->nullable()->constrained()->nullOnDelete();
    $table->string('custom_pizza')->nullable();
    $table->unsignedTinyInteger('quantity');
    $table->string('payment_type');
    $table->string('status')->default('new');
    $table->text('cancel_reason')->nullable();
    $table->timestamps();
});
PHP;

        $pizzaModel = <<<'PHP'
class Order extends Model
{
    protected $fillable = [
        'user_id', 'address', 'phone', 'delivery_at', 'pizza_id',
        'custom_pizza', 'quantity', 'payment_type', 'status', 'cancel_reason',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pizza(): BelongsTo
    {
        return $this->belongsTo(Pizza::class);
    }
}
PHP;

        $pizzaValidation = <<<'PHP'
$data = $request->validate([
    'address' => ['required', 'string', 'max:255'],
    'phone' => ['required', 'regex:/^\+7\(\d{3}\)-\d{3}-\d{2}-\d{2}$/'],
    'delivery_at' => ['required', 'date', 'after:now'],
    'pizza_id' => ['nullable', 'exists:pizzas,id'],
    'custom' => ['nullable', 'boolean'],
    'custom_pizza' => ['nullable', 'required_if:custom,1', 'string', 'max:255'],
    'quantity' => ['required', 'integer', 'between:1,20'],
    'payment_type' => ['required', 'in:cash,card'],
]);
PHP;

        $pizzaStore = <<<'PHP'
public function store(Request $request)
{
    $data = $request->validate([
        'address' => ['required', 'string', 'max:255'],
        'phone' => ['required', 'regex:/^\+7\(\d{3}\)-\d{3}-\d{2}-\d{2}$/'],
        'delivery_at' => ['required', 'date', 'after:now'],
        'pizza_id' => ['nullable', 'exists:pizzas,id'],
        'custom_pizza' => ['nullable', 'required_if:custom,1', 'string', 'max:255'],
        'quantity' => ['required', 'integer', 'between:1,20'],
        'payment_type' => ['required', 'in:cash,card'],
    ]);

    $request->user()->orders()->create($data + ['status' => 'new']);

    return redirect()->route('orders.index')->with('success', 'Заказ принят.');
}
PHP;

        $pizzaAdmin = <<<'PHP'
public function status(Request $request, Order $order)
{
    $data = $request->validate([
        'status' => ['required', 'in:new,on_the_way,delivered,cancelled'],
        'cancel_reason' => ['nullable', 'required_if:status,cancelled', 'string', 'max:500'],
    ]);

    $order->update($data);

    return back()->with('success', 'Статус заказа обновлён.');
}
PHP;

        $cases = [
            [
                'slug' => 'conference-build',
                'variant' => 'conference',
                'title' => 'Как собрали «Конференцию»',
                'subtitle' => 'Полный разбор: от пустой папки до тестов и Git',
                'summary' => 'Регистрация, помещения, заявки, кабинет, отзыв и админка со статусами. Ниже — порядок работы, команды и полный код.',
                'duration' => 210,
                'level' => 'базовый',
                'chapters' => [
                    [
                        'title' => 'Задача и план',
                        'goal' => 'Понять, что именно строим, до первой команды.',
                        'paragraphs' => [
                            'На демоэкзамене сначала читают задание целиком. Выписывают сущности, поля, статусы, роли и точные подписи. Пока этого нет, код писать рано: половина переделок — из-за пропущенного поля.',
                            'Для «Конференции» получается три таблицы: пользователи, помещения и заявки. Заявка принадлежит пользователю и помещению, имеет дату, оплату и статус. Администратор меняет статус, пользователь видит только свои заявки.',
                            'Стек — Laravel, SQLite, Blade и локальный CSS. Этого достаточно: база в одном файле, сервер поднимается одной командой, шаблоны не требуют сборки фронтенда.',
                        ],
                        'commands' => [],
                        'files' => [],
                        'expert' => [
                            'Сценарий связный: регистрация → заявка → кабинет → админка → статус.',
                            'Поля и статусы совпадают с заданием, а не придуманы по ходу.',
                        ],
                        'mistakes' => [
                            'Начать с дизайна, не описав сущности.',
                            'Сделать десять страниц, но не связать их сценарием.',
                        ],
                    ],
                    [
                        'title' => 'Каркас и база',
                        'goal' => 'Поднять Laravel, подключить SQLite и сделать первый коммит.',
                        'paragraphs' => [
                            'Проект создаётся Composer-ом. Сразу генерируется ключ приложения: без него Laravel не запустится. Затем создаётся файл SQLite и применяются стартовые миграции.',
                            'Первый коммит делается до кода. Это страховка: если что-то сломается, можно вернуться к рабочему каркасу. На экзамене коммиты нужны минимум в начале и в конце.',
                        ],
                        'commands' => [
                            ['label' => 'Создать проект', 'code' => "composer create-project laravel/laravel conference\ncd conference\nphp artisan key:generate"],
                            ['label' => 'Подключить SQLite', 'code' => "type nul > database\\database.sqlite\nphp artisan migrate"],
                            ['label' => 'Запустить', 'code' => 'php artisan serve'],
                            ['label' => 'Первый коммит', 'code' => "git init\ngit add .\ngit commit -m \"Старт проекта: Laravel + SQLite\""],
                        ],
                        'files' => [
                            $file('app/Providers/AppServiceProvider.php'),
                        ],
                        'expert' => [
                            'Проект запускается с нуля, а не только на вашей машине.',
                            'В репозитории нет `.env` и `vendor`.',
                        ],
                        'mistakes' => [
                            'Закоммитить `.env` с ключом и паролями.',
                            'Забыть `php artisan key:generate`.',
                        ],
                    ],
                    [
                        'title' => 'Миграции',
                        'goal' => 'Описать базу кодом, чтобы её можно было пересоздать одной командой.',
                        'paragraphs' => [
                            'Сначала пользователи: логин, ФИО, email, пароль и роль. Логин и email уникальны. Пароль хранится только хешем.',
                            'Потом помещения: название и вместимость. Затем заявки: внешние ключи на пользователя и помещение, дата, оплата, статус, оценка и отзыв. Статус по умолчанию — `new`.',
                            'Внешние ключи с `cascadeOnDelete` не дают появиться заявке без пользователя или помещения.',
                        ],
                        'commands' => [
                            ['label' => 'Применить', 'code' => 'php artisan migrate'],
                        ],
                        'files' => [
                            $file('database/migrations/0001_01_01_000000_create_users_table.php'),
                            $file('database/migrations/2026_01_01_000001_create_rooms_table.php'),
                            $file('database/migrations/2026_01_01_000002_create_applications_table.php'),
                        ],
                        'expert' => [
                            'Связи настоящие: `user_id` и `room_id` — внешние ключи.',
                            'Статус имеет осмысленное значение по умолчанию.',
                        ],
                        'mistakes' => [
                            'Хранить пароль открытым текстом или в `md5`.',
                            'Сделать `room_id` просто числом без внешнего ключа.',
                        ],
                    ],
                    [
                        'title' => 'Модели',
                        'goal' => 'Связать таблицы в Eloquent, чтобы не писать JOIN в каждом месте.',
                        'paragraphs' => [
                            'У пользователя много заявок, у помещения — тоже. Заявка принадлежит пользователю и помещению. Эти три связи закрывают весь сценарий.',
                            'В `$fillable` перечисляются поля, которые можно заполнять из формы. Пароль кастуется как `hashed`: Laravel сам хеширует значение.',
                        ],
                        'commands' => [],
                        'files' => [
                            $file('app/Models/User.php'),
                            $file('app/Models/Room.php'),
                            $file('app/Models/Application.php'),
                        ],
                        'expert' => [
                            'Связи названы по смыслу: `applications`, `user`, `room`.',
                            'Нет запросов к базе прямо во view.',
                        ],
                        'mistakes' => [
                            'Забыть `$fillable` и получить ошибку массового заполнения.',
                            'Сделать связь в одну сторону и писать JOIN вручную.',
                        ],
                    ],
                    [
                        'title' => 'Сидеры',
                        'goal' => 'Получить одинаковую базу на любом компьютере одной командой.',
                        'paragraphs' => [
                            'Сидер администратора создаёт логин `admin` и пароль `Admin2027!`. Сидер студента — демо-аккаунт. Сидер помещений заполняет залы, сидер заявок даёт пару примеров для кабинета и админки.',
                            'На защите эксперт может выполнить `migrate:fresh --seed` и сразу войти. Если данные заводятся только руками, сценарий не воспроизводится.',
                        ],
                        'commands' => [
                            ['label' => 'Заполнить', 'code' => 'php artisan db:seed'],
                        ],
                        'files' => [
                            $file('database/seeders/DatabaseSeeder.php'),
                            $file('database/seeders/UserSeeder.php'),
                            $file('database/seeders/RoomSeeder.php'),
                            $file('database/seeders/ApplicationSeeder.php'),
                        ],
                        'expert' => [
                            'Админ и демо-данные появляются на чистой базе.',
                            'Пароли в сидере хешируются, а не хранятся текстом.',
                        ],
                        'mistakes' => [
                            'Сидер падает при повторном запуске из-за дублей.',
                            'Нет администратора — проверить админку невозможно.',
                        ],
                    ],
                    [
                        'title' => 'Регистрация и вход',
                        'goal' => 'Сделать сценарий входа по требованиям КИМ.',
                        'paragraphs' => [
                            'Регистрация валидирует ФИО, логин, email и пароль. Логин уникален, пароль — от 6 символов и с подтверждением. Роль всегда `student`: её нельзя передать формой.',
                            'После регистрации пользователь сразу авторизуется. Вход ищет пользователя по логину и проверяет хеш пароля. Сессия регенерируется, чтобы нельзя было подменить идентификатор.',
                            'Middleware `admin` закрывает админку. Студент получает 403, а не пустую страницу.',
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
                            'Пароль не хранится открытым текстом.',
                            'Ошибки валидации видны у полей, ввод не теряется.',
                            'Студент не может открыть `/admin`.',
                        ],
                        'mistakes' => [
                            'Пускать в админку по ссылке в меню, без middleware.',
                            'Разрешить выбрать роль `admin` при регистрации.',
                        ],
                    ],
                    [
                        'title' => 'Заявки и кабинет',
                        'goal' => 'Дать пользователю создать заявку и увидеть только свои.',
                        'paragraphs' => [
                            'Форма заявки получает помещения из базы и валидирует дату и оплату. Заявка создаётся от текущего пользователя со статусом `new`.',
                            'Кабинет делает запрос `$request->user()->applications()`, поэтому чужие заявки туда не попадают. Отзыв доступен только после статуса `completed`; перед обновлением проверяется владелец.',
                            'Это место, где чаще всего теряют баллы: показывают все заявки или разрешают отзыв о чужой.',
                        ],
                        'commands' => [],
                        'files' => [
                            $file('resources/views/layouts/app.blade.php'),
                            $file('resources/views/home.blade.php'),
                            $file('app/Http/Controllers/ApplicationController.php'),
                            $file('resources/views/application.blade.php'),
                            $file('resources/views/profile.blade.php'),
                            $file('public/css/app.css'),
                        ],
                        'expert' => [
                            'Пользователь видит только свои заявки.',
                            'Чужая заявка даёт 403.',
                            'Отзыв появляется после завершения.',
                        ],
                        'mistakes' => [
                            '`Application::all()` в кабинете.',
                            'Отзыв без проверки владельца и статуса.',
                        ],
                    ],
                    [
                        'title' => 'Админка',
                        'goal' => 'Дать администратору управление заявками.',
                        'paragraphs' => [
                            'Админка показывает все заявки с пользователем и помещением. Фильтр по статусу помогает найти нужные. Смена статуса валидируется по списку допустимых значений.',
                            'Список статусов — часть задания. Если в задании `new/assigned/completed/rejected`, нельзя добавлять произвольные строки.',
                        ],
                        'commands' => [],
                        'files' => [
                            $file('app/Http/Controllers/AdminController.php'),
                            $file('resources/views/admin/index.blade.php'),
                        ],
                        'expert' => [
                            'Админ видит все заявки и меняет статус.',
                            'Фильтр работает, статусы совпадают с заданием.',
                        ],
                        'mistakes' => [
                            'Разрешить любой статус из формы.',
                            'Забыть пагинацию и получить простыню на сотни строк.',
                        ],
                    ],
                    [
                        'title' => 'Маршруты, тесты и сдача',
                        'goal' => 'Собрать всё вместе и проверить на чистой базе.',
                        'paragraphs' => [
                            'Полный `routes/web.php` связывает страницы, кабинет и админку. Дальше — `migrate:fresh --seed`, тесты и ручной сценарий.',
                            'Перед сдачей проверяют 390, 768 и 1440 px, консоль, 403 на чужое, вход администратора и коммиты. README с запуском экономит время эксперту.',
                        ],
                        'commands' => [
                            ['label' => 'Чистая база', 'code' => 'php artisan migrate:fresh --seed'],
                            ['label' => 'Тесты', 'code' => 'php artisan test'],
                            ['label' => 'Запуск', 'code' => 'php artisan serve'],
                            ['label' => 'Git', 'code' => "git add .\ngit commit -m \"Готовый проект: заявки, кабинет, админка\"\ngit push -u origin main"],
                        ],
                        'files' => [
                            $file('routes/web.php'),
                            $file('tests/Feature/ApplicationTest.php'),
                            $file('README.md'),
                        ],
                        'expert' => [
                            'Проект запускается с нуля и проходит сценарий.',
                            'Есть тесты, README и коммиты.',
                        ],
                        'mistakes' => [
                            'Проверять только на базе, которая уже заполнена.',
                            'Забыть про мобильный макет и 403.',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'bookexchange-build',
                'variant' => 'bookexchange',
                'title' => 'Как собрать «Буквоежку»',
                'subtitle' => 'Обмен книгами: карточки, модерация и архив',
                'summary' => 'Разбор реального КИМ 2025: регистрация с кириллическим логином, карточки «поделиться / хочу», публикация и отклонение с причиной.',
                'duration' => 210,
                'level' => 'средний',
                'chapters' => [
                    [
                        'title' => 'Что меняется по сравнению с «Конференцией»',
                        'goal' => 'Понять отличие предметной области, не переписывая архитектуру.',
                        'paragraphs' => [
                            'Каркас тот же: Laravel, SQLite, Blade, регистрация, роли, админка. Меняются сущности и правила: вместо помещений — карточки книг, вместо заявки — карточка с типом «готов поделиться» или «хочу в библиотеку».',
                            'Появляется модерация: карточка создаётся со статусом `pending`, администратор публикует её или отклоняет с причиной. Отклонённые и удалённые карточки уходят в архив.',
                        ],
                        'commands' => [],
                        'files' => [],
                        'expert' => [
                            'Статусы и причины отклонения соответствуют заданию.',
                            'Архив отделён от активных карточек.',
                        ],
                        'mistakes' => [
                            'Сразу публиковать карточку без модерации.',
                            'Терять причину отклонения.',
                        ],
                    ],
                    [
                        'title' => 'Таблица карточек',
                        'goal' => 'Спроектировать `cards` под книгу и модерацию.',
                        'paragraphs' => [
                            'Обязательные поля — автор и название. Тип карточки хранится строкой `share` или `want`. Дополнительные поля — издательство, год, переплёт и состояние — nullable.',
                            'Статус по умолчанию `pending`. Причина отклонения — отдельное текстовое поле, иначе администратору негде её сохранить.',
                        ],
                        'commands' => [
                            ['label' => 'Создать миграцию', 'code' => 'php artisan make:migration create_cards_table'],
                        ],
                        'files' => [
                            ['path' => 'database/migrations/..._create_cards_table.php', 'language' => 'php', 'code' => $bookMigration],
                        ],
                        'expert' => [
                            'Обязательные и необязательные поля разделены.',
                            'Есть статус и причина отклонения.',
                        ],
                        'mistakes' => [
                            'Сделать все поля обязательными.',
                            'Хранить тип карточки числом без расшифровки.',
                        ],
                    ],
                    [
                        'title' => 'Модель и связи',
                        'goal' => 'Связать карточку с пользователем.',
                        'paragraphs' => [
                            'Карточка принадлежит пользователю. В модели перечисляются поля для массового заполнения. Связь `user()` пригодится в админке, чтобы показать владельца.',
                        ],
                        'commands' => [],
                        'files' => [
                            ['path' => 'app/Models/Card.php', 'language' => 'php', 'code' => $bookModel],
                        ],
                        'expert' => [
                            'В админке видно, чья карточка.',
                            'Нет прямой работы с базой во view.',
                        ],
                        'mistakes' => [
                            'Забыть `$fillable`.',
                            'Не связать карточку с пользователем.',
                        ],
                    ],
                    [
                        'title' => 'Регистрация по правилам КИМ',
                        'goal' => 'Соблюсти кириллический логин, телефон и уникальность.',
                        'paragraphs' => [
                            'В этом варианте логин — кириллица не менее 6 символов. Телефон — строго `+7(XXX)-XXX-XX-XX`. Email и логин уникальны.',
                            'Регулярки лучше держать в правилах валидации, а не в JS: эксперт проверяет сервер.',
                        ],
                        'commands' => [],
                        'files' => [
                            ['path' => 'app/Http/Controllers/AuthController.php (фрагмент)', 'language' => 'php', 'code' => $bookValidation],
                        ],
                        'expert' => [
                            'Серверная валидация, а не только HTML5.',
                            'Сообщения об ошибках понятные.',
                        ],
                        'mistakes' => [
                            'Пропустить уникальность логина.',
                            'Разрешить телефон в свободном формате.',
                        ],
                    ],
                    [
                        'title' => 'Создание карточки',
                        'goal' => 'Сохранить карточку и отправить её на модерацию.',
                        'paragraphs' => [
                            'Форма содержит автора, название, radio-кнопки типа и дополнительные поля. После сохранения статус — `pending`, пользователь возвращается в список своих карточек.',
                            'Радио-кнопки важны: это не выпадающий список. В задании прямо сказано Radio Button.',
                        ],
                        'commands' => [],
                        'files' => [
                            ['path' => 'app/Http/Controllers/CardController.php (фрагмент)', 'language' => 'php', 'code' => $bookStore],
                        ],
                        'expert' => [
                            'Карточка уходит на проверку, а не публикуется сразу.',
                            'Тип карточки обязателен.',
                        ],
                        'mistakes' => [
                            'Сделать select вместо radio.',
                            'Разрешить пустого автора или название.',
                        ],
                    ],
                    [
                        'title' => 'Админка и архив',
                        'goal' => 'Опубликовать или отклонить карточку с причиной.',
                        'paragraphs' => [
                            'Админ видит все карточки. Публикация меняет статус на `published`, отклонение требует причину и меняет статус на `rejected`.',
                            'Пользователь видит свои активные карточки и архив. Удаление — мягкое или через отдельный статус, но чужая карточка должна давать 403.',
                        ],
                        'commands' => [],
                        'files' => [
                            ['path' => 'app/Http/Controllers/Admin/CardController.php (фрагмент)', 'language' => 'php', 'code' => $bookAdmin],
                        ],
                        'expert' => [
                            'Причина отклонения обязательна.',
                            'Пользователь не может удалить чужую карточку.',
                        ],
                        'mistakes' => [
                            'Отклонять без причины.',
                            'Показывать в архиве только удалённые, забыв отклонённые.',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'restaurant-build',
                'variant' => 'restaurant',
                'title' => 'Как собрать «Я буду кушац»',
                'subtitle' => 'Бронирование столиков: дата, время, гости, отзыв',
                'summary' => 'Разбор реального КИМ 2025: бронирование, статусы «Новое / Посещение состоялось / Отменено», отзыв после визита.',
                'duration' => 210,
                'level' => 'средний',
                'chapters' => [
                    [
                        'title' => 'Что меняется',
                        'goal' => 'Понять сущности и сценарий ресторана.',
                        'paragraphs' => [
                            'Пользователь регистрируется, бронирует столик и видит свои брони. Администратор подтверждает визит или отменяет бронь. Отзыв доступен только после посещения.',
                            'Вместо помещений — дата, время и количество гостей. Вместо заявки — бронирование.',
                        ],
                        'commands' => [],
                        'files' => [],
                        'expert' => [
                            'Статусы соответствуют заданию.',
                            'Отзыв недоступен до визита.',
                        ],
                        'mistakes' => [
                            'Разрешить отзыв сразу после создания брони.',
                            'Забыть ограничение гостей 1–10.',
                        ],
                    ],
                    [
                        'title' => 'Таблица броней',
                        'goal' => 'Спроектировать `bookings`.',
                        'paragraphs' => [
                            'Дата, время в формате `ЧЧ:ММ`, гости, телефон и статус. Оценка и отзыв nullable: они появляются позже.',
                        ],
                        'commands' => [],
                        'files' => [
                            ['path' => 'database/migrations/..._create_bookings_table.php', 'language' => 'php', 'code' => $restMigration],
                        ],
                        'expert' => [
                            'Гости — целое число с ограничением.',
                            'Статус по умолчанию `new`.',
                        ],
                        'mistakes' => [
                            'Хранить дату и время одной строкой без формата.',
                            'Разрешить 0 гостей.',
                        ],
                    ],
                    [
                        'title' => 'Модель',
                        'goal' => 'Связать бронь с пользователем.',
                        'paragraphs' => [
                            'Бронь принадлежит пользователю. В админке понадобится имя и контакты, поэтому связь обязательна.',
                        ],
                        'commands' => [],
                        'files' => [
                            ['path' => 'app/Models/Booking.php', 'language' => 'php', 'code' => $restModel],
                        ],
                        'expert' => [
                            'Связь с пользователем есть.',
                            'Поля перечислены в `$fillable`.',
                        ],
                        'mistakes' => [
                            'Хранить имя гостя строкой в брони, а не ссылкой.',
                        ],
                    ],
                    [
                        'title' => 'Регистрация',
                        'goal' => 'Соблюсти правила КИМ.',
                        'paragraphs' => [
                            'Логин — кириллица от 6 символов, пароль от 6, имя и фамилия, телефон в формате `+7(XXX)-XXX-XX-XX`, email.',
                        ],
                        'commands' => [],
                        'files' => [
                            ['path' => 'app/Http/Controllers/AuthController.php (фрагмент)', 'language' => 'php', 'code' => $restValidation],
                        ],
                        'expert' => [
                            'Все поля обязательны и проверяются на сервере.',
                        ],
                        'mistakes' => [
                            'Пропустить уникальность логина.',
                        ],
                    ],
                    [
                        'title' => 'Бронирование и отзыв',
                        'goal' => 'Создать бронь и разрешить отзыв после визита.',
                        'paragraphs' => [
                            'Форма проверяет дату, время, гостей и телефон. После сохранения статус `new`. Отзыв принимается только для своей брони со статусом `visited`.',
                        ],
                        'commands' => [],
                        'files' => [
                            ['path' => 'app/Http/Controllers/BookingController.php (фрагмент)', 'language' => 'php', 'code' => $restStore],
                        ],
                        'expert' => [
                            'Дата не в прошлом.',
                            'Чужая бронь недоступна.',
                        ],
                        'mistakes' => [
                            'Не проверять владельца при отзыве.',
                            'Разрешить отзыв без визита.',
                        ],
                    ],
                    [
                        'title' => 'Админка и статусы',
                        'goal' => 'Провести бронь по статусам.',
                        'paragraphs' => [
                            'Админ видит все брони, фильтрует и меняет статус: `new`, `visited`, `cancelled`. В задании также просят пагинацию и всплывающие сообщения.',
                        ],
                        'commands' => [],
                        'files' => [
                            ['path' => 'app/Http/Controllers/Admin/BookingController.php (фрагмент)', 'language' => 'php', 'code' => $restAdmin],
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
                ],
            ],
            [
                'slug' => 'pizza-build',
                'variant' => 'pizza',
                'title' => 'Как собрать «Волшебную пиццу»',
                'subtitle' => 'Доставка: выбор пиццы, «Иная пицца», статусы',
                'summary' => 'Разбор КИМ 2025: заказ из списка, своя пицца через чекбокс, количество, оплата и статусы доставки.',
                'duration' => 210,
                'level' => 'средний',
                'chapters' => [
                    [
                        'title' => 'Что меняется',
                        'goal' => 'Понять сценарий доставки.',
                        'paragraphs' => [
                            'Пользователь выбирает пиццу из списка или отмечает «Иная пицца» и описывает её. Указывает адрес, телефон, дату и время, количество и оплату.',
                            'Администратор ведёт заказ: новый, в пути, доставлено или отменено с причиной.',
                        ],
                        'commands' => [],
                        'files' => [],
                        'expert' => [
                            'Список пицц и чекбокс «Иная» работают по заданию.',
                            'Отмена требует причину.',
                        ],
                        'mistakes' => [
                            'Сделать только свободный ввод вместо списка.',
                            'Забыть причину отмены.',
                        ],
                    ],
                    [
                        'title' => 'Таблицы пицц и заказов',
                        'goal' => 'Спроектировать `pizzas` и `orders`.',
                        'paragraphs' => [
                            'Пиццы — справочник: название, цена, картинка. Заказ ссылается на пользователя и, если выбрана готовая пицца, на `pizzas`. Своя пицца хранится текстом.',
                        ],
                        'commands' => [],
                        'files' => [
                            ['path' => 'database/migrations/..._create_orders_table.php', 'language' => 'php', 'code' => $pizzaMigration],
                        ],
                        'expert' => [
                            '`pizza_id` nullable: заказ может быть со своей пиццей.',
                            'Есть причина отмены.',
                        ],
                        'mistakes' => [
                            'Сделать `pizza_id` обязательным и сломать «Иную пиццу».',
                            'Забыть количество.',
                        ],
                    ],
                    [
                        'title' => 'Модель заказа',
                        'goal' => 'Связать заказ с пользователем и пиццей.',
                        'paragraphs' => [
                            'Заказ принадлежит пользователю и, опционально, пицце. В `$fillable` — все поля формы и причина отмены.',
                        ],
                        'commands' => [],
                        'files' => [
                            ['path' => 'app/Models/Order.php', 'language' => 'php', 'code' => $pizzaModel],
                        ],
                        'expert' => [
                            'Связи с пользователем и пиццей есть.',
                        ],
                        'mistakes' => [
                            'Забыть `custom_pizza` в `$fillable`.',
                        ],
                    ],
                    [
                        'title' => 'Регистрация',
                        'goal' => 'Соблюсти пароль с заглавной буквой и формат телефона.',
                        'paragraphs' => [
                            'В этом варианте пароль — от 6 символов с одной заглавной буквой. Логин уникален, телефон строго в формате `+7(XXX)-XXX-XX-XX`.',
                        ],
                        'commands' => [],
                        'files' => [
                            ['path' => 'app/Http/Controllers/AuthController.php (фрагмент)', 'language' => 'php', 'code' => $pizzaValidation],
                        ],
                        'expert' => [
                            'Правило заглавной буквы проверяется на сервере.',
                        ],
                        'mistakes' => [
                            'Проверять пароль только регуляркой в JS.',
                        ],
                    ],
                    [
                        'title' => 'Заказ и «Иная пицца»',
                        'goal' => 'Связать список, чекбокс и текстовое поле.',
                        'paragraphs' => [
                            'Если чекбокс «Иная пицца» включён, поле описания обязательно. Если выбрана готовая пицца, `pizza_id` берётся из списка.',
                            'Условная валидация `required_if` решает это на сервере, а не только скрытием поля.',
                        ],
                        'commands' => [],
                        'files' => [
                            ['path' => 'app/Http/Controllers/OrderController.php (фрагмент)', 'language' => 'php', 'code' => $pizzaStore],
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
                            'Статусы: `new`, `on_the_way`, `delivered`, `cancelled`. При отмене причина обязательна. В админке видны ФИО, контакты и свойства заказа.',
                        ],
                        'commands' => [],
                        'files' => [
                            ['path' => 'app/Http/Controllers/Admin/OrderController.php (фрагмент)', 'language' => 'php', 'code' => $pizzaAdmin],
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
                ],
            ],
        ];

        foreach ($cases as $position => $case) {
            $variant = ProjectVariant::where('slug', $case['variant'])->first();

            CaseStudy::updateOrCreate(
                ['slug' => $case['slug']],
                [
                    'project_variant_id' => $variant?->id,
                    'title' => $case['title'],
                    'subtitle' => $case['subtitle'],
                    'summary' => $case['summary'],
                    'stack' => 'Laravel · SQLite · Blade',
                    'duration' => $case['duration'],
                    'level' => $case['level'],
                    'chapters' => $case['chapters'],
                    'position' => $position,
                    'is_published' => true,
                ]
            );
        }

        $this->command?->info('Разборы проектов: '.CaseStudy::count().'.');
    }
}
