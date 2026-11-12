window.COURSE_MODULES = window.COURSE_MODULES || [];
window.COURSE_MODULES.push({
  id: 'm5',
  num: '05',
  title: 'Сборка проекта',
  lead: 'Девять шагов от пустой папки до сдачи. Повторяйте на своей теме, сверяясь с КИМ.',
  lessons: [
    {
      id: 'build-start',
      title: 'Шаг 1. Проект, база и первый коммит',
      minutes: 15,
      level: 'практика',
      tags: ['практика', 'laravel'],
      goal: 'Поднять Laravel, подключить SQLite и зафиксировать стартовое состояние в Git.',
      blocks: [
        { t: 'p', v: 'На экзамене первое задание длится 1 час 30 минут. Первые 20–25 минут уходят на каркас. Если вы делали это десять раз дома, здесь не будет сюрпризов.' },
        { t: 'h', v: 'Создание' },
        { t: 'code', lang: 'bash', file: 'терминал', v: 'cd C:\\Users\\you\\demo\ncomposer create-project laravel/laravel conference\ncd conference\nphp artisan key:generate' },
        { t: 'p', v: 'Папку называйте по теме, но латиницей: `conference`, `autodrive`, `medcenter`. Русские имена и пробелы иногда ломают Composer и Git.' },
        { t: 'h', v: 'База' },
        { t: 'code', lang: 'bash', file: 'терминал', v: 'type nul > database\\database.sqlite\nphp artisan migrate' },
        { t: 'p', v: 'В `.env` проверьте `DB_CONNECTION=sqlite`. Если используете MySQL, создайте базу заранее и заполните `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`. На экзамене уточните, какой сервер БД предоставляют.' },
        { t: 'h', v: 'Проверка' },
        { t: 'code', lang: 'bash', file: 'терминал', v: 'php artisan serve\n# открыть http://127.0.0.1:8000' },
        { t: 'p', v: 'Видите стартовую страницу Laravel — каркас готов. Сразу проверьте, что `storage` и `bootstrap/cache` доступны для записи. На Windows это обычно не проблема, на Linux — частая.' },
        { t: 'h', v: 'Git' },
        { t: 'code', lang: 'bash', file: 'терминал', v: 'git init\ngit add .\ngit commit -m "Старт проекта: Laravel + SQLite"\n# создайте удалённый репозиторий и добавьте origin\ngit remote add origin <url>\ngit push -u origin main' },
        { t: 'p', v: 'По КИМ результаты сдаются через индивидуальный репозиторий, коммиты нужны минимум в начале и в конце. Не откладывайте: в конце времени вы можете забыть про push.' },
        { t: 'note', v: 'Проверьте `.gitignore`: `.env`, `vendor`, `node_modules`, `database/*.sqlite` не должны попадать в репозиторий. Но `.env.example` — обязательно.' },
        { t: 'h', v: 'Что сделать за первые 25 минут' },
        { t: 'ol', v: [
          'Создать проект и запустить сервер.',
          'Подключить базу и применить стартовые миграции.',
          'Сделать первый коммит.',
          'Открыть задание и выписать сущности, поля, статусы, логины админа.',
          'Нарисовать схему на бумаге или в draw.io.'
        ] },
        { t: 'ok', label: 'Готово, если', v: 'Сервер открывается, база подключена, коммит есть, схема нарисована. Можно переходить к миграциям.' }
      ],
      practice: [
        'Создайте проект под свою тему и запустите его.',
        'Подключите SQLite и выполните стартовые миграции.',
        'Сделайте первый коммит и запушьте в репозиторий.',
        'Выпишите из задания все поля, статусы и логин администратора.'
      ],
      quiz: [
        { q: 'Что должно быть в первом коммите?', options: ['Только .env', 'Рабочий каркас Laravel без секретов', 'База с паролями'], answer: 1, explain: '.env и база в репозиторий не попадают.' },
        { q: 'Зачем проверять storage и bootstrap/cache?', options: ['Туда пишутся логи, сессии и кеш', 'Там лежат модели', 'Это публичная папка'], answer: 0, explain: 'Без прав записи Laravel не запустится.' }
      ]
    },
    {
      id: 'build-schema',
      title: 'Шаг 2. Миграции, модели и ER',
      minutes: 22,
      level: 'практика',
      tags: ['практика', 'бд'],
      goal: 'Создать три таблицы, связи и ER-диаграмму, которую можно сдать.',
      blocks: [
        { t: 'p', v: 'По КИМ нужно спроектировать базу и сохранить ER-диаграмму в папку проекта. Начните со схемы, а не с интерфейса: если таблиц нет, формы будут пустыми.' },
        { t: 'h', v: 'Миграции' },
        { t: 'code', lang: 'bash', file: 'терминал', v: 'php artisan make:model Room -m\nphp artisan make:model Application -m' },
        { t: 'code', lang: 'php', file: 'database/migrations/..._create_rooms_table.php', v: `Schema::create('rooms', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('type');
    $table->text('description');
    $table->unsignedInteger('capacity');
    $table->string('image')->nullable();
    $table->timestamps();
});` },
        { t: 'code', lang: 'php', file: 'database/migrations/..._create_applications_table.php', v: `Schema::create('applications', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('room_id')->constrained()->cascadeOnDelete();
    $table->date('start_date');
    $table->enum('payment_type', ['in_person', 'sbp'])->default('in_person');
    $table->enum('status', ['new', 'assigned', 'completed'])->default('new');
    $table->unsignedTinyInteger('rating')->nullable();
    $table->text('review')->nullable();
    $table->timestamps();
});` },
        { t: 'p', v: 'Если тема про автомобили, замените `rooms` на `cars`, `room_id` на `car_id`, поля — на марку, модель, год. Структура та же.' },
        { t: 'h', v: 'Модели' },
        { t: 'code', lang: 'php', file: 'app/Models/Room.php', v: `class Room extends Model
{
    protected $fillable = ['name', 'type', 'description', 'capacity', 'image'];

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }
}` },
        { t: 'code', lang: 'php', file: 'app/Models/Application.php', v: `class Application extends Model
{
    protected $fillable = [
        'user_id', 'room_id', 'start_date', 'payment_type',
        'status', 'rating', 'review',
    ];

    protected $casts = [
        'start_date' => 'date',
        'rating' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }
}` },
        { t: 'h', v: 'ER-диаграмма' },
        { t: 'p', v: 'По КИМ диаграмму создают в СУБД и сохраняют в проект. Подойдёт экспорт из HeidiSQL, DBeaver, MySQL Workbench, а также Mermaid или draw.io. Положите файл в `docs/er.svg` или `docs/er.png` и добавьте в репозиторий.' },
        { t: 'code', lang: 'text', file: 'docs/er.md (Mermaid)', v: `erDiagram
    USERS ||--o{ APPLICATIONS : creates
    ROOMS ||--o{ APPLICATIONS : booked

    USERS {
        bigint id PK
        string fio
        string login UK
        string phone
        string email UK
        string password
        string role
    }
    ROOMS {
        bigint id PK
        string name
        string type
        text description
        int capacity
        string image
    }
    APPLICATIONS {
        bigint id PK
        bigint user_id FK
        bigint room_id FK
        date start_date
        string payment_type
        string status
        int rating
        text review
    }` },
        { t: 'note', v: 'Не откладывайте ER на конец. Это отдельный критерий, и он дешёвый по времени: 10 минут — и файл лежит в проекте.' },
        { t: 'h', v: 'Проверка' },
        { t: 'code', lang: 'bash', file: 'терминал', v: 'php artisan migrate\nphp artisan tinker\n>>> App\\Models\\Room::count()\n>>> App\\Models\\Application::count()' },
        { t: 'p', v: 'Если миграции прошли, таблицы есть. Ошибка `no such table` означает, что миграция не применилась или имя таблицы не совпадает с моделью.' }
      ],
      practice: [
        'Создайте миграции трёх таблиц с внешними ключами.',
        'Настройте `$fillable` и связи в моделях.',
        'Примените миграции и проверьте таблицы в СУБД.',
        'Сохраните ER-диаграмму в `docs/` и закоммитьте.'
      ],
      quiz: [
        { q: 'Почему `applications` создаётся после `rooms`?', options: ['Из-за внешнего ключа', 'Из-за размера', 'Из-за timestamps'], answer: 0, explain: 'Ссылаться можно только на существующую таблицу.' },
        { q: 'Что означает `cascadeOnDelete()`?', options: ['Удаление связанных заявок', 'Запрет удаления', 'Кеширование'], answer: 0, explain: 'При удалении помещения заявки не останутся с битым ID.' }
      ]
    },
    {
      id: 'build-seed',
      title: 'Шаг 3. Сидеры: админ и демо-данные',
      minutes: 16,
      level: 'практика',
      tags: ['практика', 'бд'],
      goal: 'Заполнить базу так, чтобы эксперт сразу увидел работающие списки и фильтры.',
      blocks: [
        { t: 'p', v: 'Пустая база выглядит подозрительно: эксперт не понимает, работает ли список. Сидер с тремя помещениями, админом и парой заявок экономит время на демонстрации.' },
        { t: 'h', v: 'DatabaseSeeder' },
        { t: 'code', lang: 'php', file: 'database/seeders/DatabaseSeeder.php', v: `public function run(): void
{
    $this->call([
        RoomSeeder::class,
        UserSeeder::class,
        ApplicationSeeder::class,
    ]);
}` },
        { t: 'h', v: 'Админ' },
        { t: 'code', lang: 'php', file: 'database/seeders/UserSeeder.php', v: `use App\\Models\\User;
use Illuminate\\Database\\Seeder;
use Illuminate\\Support\\Facades\\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['login' => 'Conf2027'],
            [
                'fio' => 'Администратор Портала',
                'phone' => '8(999)000-00-00',
                'email' => 'admin@conferences.ru',
                'password' => Hash::make('Demo77'),
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['login' => 'maria2027'],
            [
                'fio' => 'Иванова Мария Сергеевна',
                'phone' => '8(912)345-67-89',
                'email' => 'maria@example.ru',
                'password' => Hash::make('password1'),
                'role' => 'user',
            ]
        );
    }
}` },
        { t: 'p', v: 'Логин и пароль админа берите из задания дословно. Если в КИМ `Conf2027` / `Demo77`, не придумывайте свои — эксперт будет входить именно этими данными.' },
        { t: 'h', v: 'Помещения' },
        { t: 'code', lang: 'php', file: 'database/seeders/RoomSeeder.php', v: `$rooms = [
    [
        'name' => 'Большая конференц-аудитория',
        'type' => 'auditory',
        'description' => '120 мест, проектор, микрофоны.',
        'capacity' => 120,
        'image' => 'img/rooms/auditory.jpg',
    ],
    [
        'name' => 'Коворкинг-зона «Вектор»',
        'type' => 'coworking',
        'description' => '8 столов, Wi-Fi, кофе-поинт.',
        'capacity' => 40,
        'image' => 'img/rooms/coworking.jpg',
    ],
    [
        'name' => 'Кинозал «Пленум»',
        'type' => 'cinema',
        'description' => 'Экран, Dolby, 90 мест.',
        'capacity' => 90,
        'image' => 'img/rooms/cinema.jpg',
    ],
];

foreach ($rooms as $room) {
    Room::updateOrCreate(['name' => $room['name']], $room);
}` },
        { t: 'h', v: 'Демо-заявки' },
        { t: 'code', lang: 'php', file: 'database/seeders/ApplicationSeeder.php', v: `$user = User::where('login', 'maria2027')->first();
$rooms = Room::all();

if (!$user || $rooms->isEmpty()) {
    return;
}

Application::updateOrCreate(
    ['user_id' => $user->id, 'room_id' => $rooms[0]->id],
    [
        'start_date' => now()->addDays(20)->toDateString(),
        'payment_type' => 'sbp',
        'status' => 'assigned',
    ]
);

Application::updateOrCreate(
    ['user_id' => $user->id, 'room_id' => $rooms[1]->id],
    [
        'start_date' => now()->subDays(10)->toDateString(),
        'payment_type' => 'in_person',
        'status' => 'completed',
        'rating' => 5,
        'review' => 'Всё прошло хорошо, звук и проектор работали.',
    ]
);` },
        { t: 'p', v: 'Одна заявка завершена — на ней можно сразу показать отзыв. Одна назначена — видно смену статуса. Не делайте все заявки «новыми»: демонстрация будет скучной.' },
        { t: 'h', v: 'Запуск' },
        { t: 'code', lang: 'bash', file: 'терминал', v: 'php artisan migrate:fresh --seed' },
        { t: 'note', v: 'Если сидер падает с `Class not found`, выполните `composer dump-autoload`. Если с `MassAssignmentException`, добавьте поля в `$fillable`.' },
        { t: 'ok', label: 'Готово, если', v: 'В базе есть админ, три помещения и две заявки разных статусов. `php artisan migrate:fresh --seed` выполняется без ошибок.' }
      ],
      practice: [
        'Создайте сидер админа с логином и паролем из задания.',
        'Заполните таблицу объектов тремя записями.',
        'Создайте две демо-заявки: назначенную и завершённую.',
        'Пересоздайте базу командой `migrate:fresh --seed` и проверьте данные.'
      ],
      quiz: [
        { q: 'Зачем `updateOrCreate` вместо `create`?', options: ['Чтобы не было дублей при повторном запуске', 'Чтобы быстрее работало', 'Чтобы хешировать пароль'], answer: 0, explain: 'Сидер можно запускать повторно без ошибок уникальности.' },
        { q: 'Где взять пароль администратора?', options: ['Придумать', 'Взять из задания', 'Сгенерировать случайный'], answer: 1, explain: 'Эксперт входит данными из КИМ.' }
      ]
    },
    {
      id: 'build-register',
      title: 'Шаг 4. Регистрация по требованиям КИМ',
      minutes: 24,
      level: 'практика',
      tags: ['практика', 'авторизация'],
      goal: 'Сделать страницу регистрации с точными правилами, русскими ошибками и кнопкой «Создать пользователя».',
      blocks: [
        { t: 'p', v: 'Требования к регистрации в КИМ перечислены дословно. Если вы отступите от них — потеряете баллы, даже если форма красивая. Проверьте каждое поле по списку.' },
        { t: 'table', head: ['Поле', 'Требование'], rows: [
          ['Логин', 'Латиница и цифры, не менее 6 символов, уникальный'],
          ['Пароль', 'Минимум 8 символов'],
          ['ФИО', 'Символы кириллицы и пробелы'],
          ['Телефон', 'Формат `8(XXX)XXX-XX-XX`'],
          ['Email', 'Формат электронной почты'],
          ['Все поля', 'Обязательны'],
          ['Кнопка', '«Создать пользователя»'],
          ['Ссылка', '«Уже зарегистрированы? Войти в систему» (или по заданию)']
        ] },
        { t: 'h', v: 'Контроллер' },
        { t: 'code', lang: 'php', file: 'app/Http/Controllers/AuthController.php', v: `public function storeRegister(Request $request)
{
    $data = $request->validate([
        'fio' => ['required', 'string', 'regex:/^[А-ЯЁа-яё\\s-]+$/u', 'max:255'],
        'login' => ['required', 'regex:/^[a-zA-Z0-9]{6,}$/', 'unique:users,login', 'max:50'],
        'phone' => ['required', 'regex:/^8\\(\\d{3}\\)\\d{3}-\\d{2}-\\d{2}$/'],
        'email' => ['required', 'email', 'unique:users,email', 'max:255'],
        'password' => ['required', 'string', 'min:8', 'max:255'],
    ], [
        'fio.required' => 'ФИО обязательно для заполнения.',
        'fio.regex' => 'ФИО должно содержать только кириллицу, пробелы и дефис.',
        'login.required' => 'Логин обязателен.',
        'login.regex' => 'Логин: латинские буквы и цифры, не менее 6 символов.',
        'login.unique' => 'Пользователь с таким логином уже существует.',
        'phone.required' => 'Телефон обязателен.',
        'phone.regex' => 'Телефон должен быть в формате 8(XXX)XXX-XX-XX.',
        'email.required' => 'Email обязателен.',
        'email.email' => 'Введите корректный email.',
        'email.unique' => 'Пользователь с таким email уже существует.',
        'password.required' => 'Пароль обязателен.',
        'password.min' => 'Пароль должен быть не менее 8 символов.',
    ]);

    User::create([
        ...$data,
        'password' => Hash::make($data['password']),
        'role' => 'user',
    ]);

    return redirect()->route('login')
        ->with('success', 'Пользователь создан. Войдите в систему.');
}` },
        { t: 'h', v: 'Форма' },
        { t: 'code', lang: 'blade', file: 'resources/views/register.blade.php', v: `@extends('layouts.app')

@section('title', 'Регистрация')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-7 col-lg-5">
        <h1 class="h2 mb-3">Регистрация</h1>

        <form method="POST" action="{{ route('register.store') }}" novalidate>
            @csrf

            <div class="mb-3">
                <label for="fio" class="form-label">ФИО</label>
                <input type="text" id="fio" name="fio"
                       class="form-control @error('fio') is-invalid @enderror"
                       value="{{ old('fio') }}"
                       placeholder="Иванов Иван Иванович">
                @error('fio') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label for="login" class="form-label">Логин</label>
                <input type="text" id="login" name="login"
                       class="form-control @error('login') is-invalid @enderror"
                       value="{{ old('login') }}"
                       placeholder="Латиница и цифры, от 6 символов">
                @error('login') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">Телефон</label>
                <input type="tel" id="phone" name="phone"
                       class="form-control @error('phone') is-invalid @enderror"
                       value="{{ old('phone') }}"
                       placeholder="8(999)123-45-67">
                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Адрес электронной почты</label>
                <input type="email" id="email" name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}">
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Пароль</label>
                <input type="password" id="password" name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       placeholder="Минимум 8 символов">
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn btn-primary w-100">Создать пользователя</button>
        </form>

        <p class="text-center mt-3 mb-0">
            Уже зарегистрированы?
            <a href="{{ route('login') }}">Войти в систему</a>
        </p>
    </div>
</div>
@endsection` },
        { t: 'h', v: 'Маска телефона' },
        { t: 'p', v: 'Требование «формат 8(XXX)XXX-XX-XX» проще выполнить, если подставлять скобки и дефисы при вводе. Пользователь вводит цифры, маска расставляет остальное.' },
        { t: 'code', lang: 'javascript', file: 'public/js/app.js', v: `const phone = document.querySelector('#phone');

if (phone) {
    phone.addEventListener('input', () => {
        let digits = phone.value.replace(/\\D/g, '').slice(0, 11);

        if (digits.startsWith('7')) {
            digits = '8' + digits.slice(1);
        }

        let out = digits;

        if (digits.length > 1) {
            out = digits[0] + '(' + digits.slice(1, 4);
        }
        if (digits.length >= 4) {
            out += ')' + digits.slice(4, 7);
        }
        if (digits.length >= 7) {
            out += '-' + digits.slice(7, 9);
        }
        if (digits.length >= 9) {
            out += '-' + digits.slice(9, 11);
        }

        phone.value = out;
    });
}` },
        { t: 'p', v: 'Маска не заменяет серверную проверку. Пользователь может отправить форму без JS или подделать запрос — регулярка в контроллере обязательна.' },
        { t: 'h', v: 'Ссылки между входом и регистрацией' },
        { t: 'p', v: 'В КИМ отдельно указан текст ссылки: «Еще не зарегистрированы? Регистрация» на странице входа и обратная ссылка на странице регистрации. Скопируйте формулировку из задания, не перефразируйте.' },
        { t: 'code', lang: 'blade', file: 'login.blade.php', v: `<p class="text-center mt-3">
    Еще не зарегистрированы?
    <a href="{{ route('register') }}">Регистрация</a>
</p>` },
        { t: 'ok', label: 'Проверка', v: 'Пустая форма показывает пять ошибок. Логин из 5 символов не проходит. Существующий логин не проходит. После ошибки значения сохраняются. Новый пользователь появляется в базе с хешем пароля.' }
      ],
      practice: [
        'Сделайте форму регистрации со всеми полями и подписями.',
        'Добавьте валидацию и русские сообщения для каждого правила.',
        'Проверьте уникальность логина и email.',
        'Добавьте маску телефона и проверьте её на 11 цифрах.'
      ],
      quiz: [
        { q: 'Какая регулярка соответствует логину из КИМ?', options: ['`/^[a-z]{6,}$/', '`/^[a-zA-Z0-9]{6,}$/', '`/^.{6,}$/'], answer: 1, explain: 'Латиница и цифры, минимум 6.' },
        { q: 'Почему маски телефона недостаточно?', options: ['Она не работает на мобильных', 'Её можно обойти, нужна серверная проверка', 'Она замедляет сайт'], answer: 1, explain: 'Клиентские проверки обходятся.' }
      ]
    },
    {
      id: 'build-login',
      title: 'Шаг 5. Вход, выход и роли',
      minutes: 22,
      level: 'практика',
      tags: ['практика', 'авторизация'],
      goal: 'Сделать вход с сообщениями об ошибках, выход и разделение пользователя и администратора.',
      blocks: [
        { t: 'p', v: 'После регистрации пользователь должен войти. Администратор — тоже, но на отдельную панель. Ошибки входа выводятся на форме, а не отдельной страницей.' },
        { t: 'h', v: 'Контроллер' },
        { t: 'code', lang: 'php', file: 'AuthController.php', v: `public function login()
{
    return view('login');
}

public function storeLogin(Request $request)
{
    $credentials = $request->validate([
        'login' => ['required', 'string'],
        'password' => ['required', 'string'],
    ], [
        'login.required' => 'Введите логин.',
        'password.required' => 'Введите пароль.',
    ]);

    if (!Auth::attempt($credentials)) {
        return back()
            ->withErrors(['login' => 'Неверный логин или пароль.'])
            ->withInput();
    }

    $request->session()->regenerate();

    return auth()->user()->isAdmin()
        ? redirect()->route('admin')
        : redirect()->route('profile');
}` },
        { t: 'p', v: '`Auth::attempt` ищет пользователя по `login` и сверяет пароль с хешем. Если не совпало — возвращаем назад с ошибкой. Не сообщайте, что именно неверно: «логин не найден» подсказывает злоумышленнику существующие логины.' },
        { t: 'h', v: 'Форма входа' },
        { t: 'code', lang: 'blade', file: 'resources/views/login.blade.php', v: `@extends('layouts.app')

@section('title', 'Вход')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-4">
        <h1 class="h2 mb-3">Вход</h1>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @error('login')
            <div class="alert alert-danger">{{ $message }}</div>
        @enderror

        <form method="POST" action="{{ route('login.store') }}">
            @csrf

            <div class="mb-3">
                <label for="login" class="form-label">Логин</label>
                <input type="text" id="login" name="login"
                       class="form-control" value="{{ old('login') }}" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Пароль</label>
                <input type="password" id="password" name="password"
                       class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Войти</button>
        </form>

        <p class="text-center mt-3">
            Еще не зарегистрированы?
            <a href="{{ route('register') }}">Регистрация</a>
        </p>
    </div>
</div>
@endsection` },
        { t: 'h', v: 'Выход' },
        { t: 'code', lang: 'php', file: 'AuthController.php', v: `public function logout(Request $request)
{
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('home');
}` },
        { t: 'code', lang: 'blade', file: 'шапка', v: `<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit" class="link-button">Выйти</button>
</form>` },
        { t: 'h', v: 'Защита маршрутов' },
        { t: 'code', lang: 'php', file: 'routes/web.php', v: `Route::middleware('auth')->group(function () {
    Route::get('/profile', [ApplicationController::class, 'index'])->name('profile');
    Route::get('/application', [ApplicationController::class, 'create'])->name('application');
    Route::post('/application', [ApplicationController::class, 'store'])->name('application.store');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin');
    Route::post('/application/{application}/status', [AdminController::class, 'updateStatus'])
        ->name('admin.status');
});` },
        { t: 'h', v: 'Админский вход' },
        { t: 'p', v: 'Если в задании отдельная страница `/admin/login`, сделайте её и проверяйте роль после `Auth::attempt`. Если отдельной страницы нет — пускайте через общий вход и перенаправляйте по роли, как в примере выше.' },
        { t: 'code', lang: 'php', file: 'AdminController.php', v: `public function storeLogin(Request $request)
{
    $credentials = $request->validate([
        'login' => ['required', 'string'],
        'password' => ['required', 'string'],
    ]);

    if (!Auth::attempt($credentials)) {
        return back()->with('error', 'Неверный логин или пароль.');
    }

    if (!auth()->user()->isAdmin()) {
        Auth::logout();
        return back()->with('error', 'Доступ разрешён только администратору.');
    }

    $request->session()->regenerate();

    return redirect()->route('admin');
}` },
        { t: 'warn', label: 'Проверка', v: 'Пользователь с ролью `user` не должен открыть `/admin` даже по прямой ссылке. Проверьте это вручную: войдите пользователем и введите URL.' },
        { t: 'ok', label: 'Готово, если', v: 'Неверный пароль показывает сообщение, верный — ведёт в кабинет или админку. Гость с `/profile` уходит на вход. Пользователь с `/admin` получает 403.' }
      ],
      practice: [
        'Сделайте вход с сообщением об ошибке.',
        'Настройте редирект по роли после входа.',
        'Закройте маршруты middleware `auth` и `admin`.',
        'Проверьте выход и повторный заход на защищённую страницу.'
      ],
      quiz: [
        { q: 'Что делает `Auth::attempt`?', options: ['Создаёт пользователя', 'Проверяет логин и пароль и пишет в сессию', 'Хеширует пароль'], answer: 1, explain: 'Пользователь должен существовать.' },
        { q: 'Почему нельзя писать «логин не найден»?', options: ['Это подсказывает существующие логины', 'Это длинно', 'Это ломает вёрстку'], answer: 0, explain: 'Единое сообщение безопаснее.' },
        { q: 'Куда перенаправит `auth` гостя?', options: ['На `/`', 'На маршрут с именем `login`', 'На 404'], answer: 1, explain: 'Поэтому маршрут входа должен называться `login`.' }
      ]
    },
    {
      id: 'build-application',
      title: 'Шаг 6. Форма заявки: от текстового поля к списку',
      minutes: 26,
      level: 'практика',
      tags: ['практика', 'формы'],
      goal: 'Сделать форму заявки так, как требует задание 1, и upgrade до выпадающего списка в задании 2.',
      blocks: [
        { t: 'p', v: 'Здесь важная деталь, которую часто пропускают. В задании 1 КИМ просит **ввести название помещения в текстовое поле**. В задании 2 — **выбрать из выпадающего списка**. Это два разных состояния одного экрана. Сначала сделайте текстовое поле, потом замените на `select`.' },
        { t: 'h', v: 'Вариант задания 1: текстовое поле' },
        { t: 'code', lang: 'blade', file: 'resources/views/application.blade.php', v: `<form method="POST" action="{{ route('application.store') }}" novalidate>
    @csrf

    <div class="mb-3">
        <label for="room_name" class="form-label">Помещение</label>
        <input type="text" id="room_name" name="room_name"
               class="form-control @error('room_name') is-invalid @enderror"
               value="{{ old('room_name') }}"
               placeholder="Например, Коворкинг-зона «Вектор»">
        @error('room_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label for="start_date" class="form-label">Дата начала конференции</label>
        <input type="date" id="start_date" name="start_date"
               class="form-control @error('start_date') is-invalid @enderror"
               value="{{ old('start_date') }}"
               min="{{ now()->toDateString() }}">
        @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label for="payment_type" class="form-label">Способ оплаты</label>
        <select id="payment_type" name="payment_type" class="form-select">
            <option value="in_person" @selected(old('payment_type') === 'in_person')>
                При очном посещении
            </option>
            <option value="sbp" @selected(old('payment_type') === 'sbp')>
                Переводом по системе СБП
            </option>
        </select>
        @error('payment_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <button type="submit" class="btn btn-primary">Отправить</button>
</form>` },
        { t: 'code', lang: 'php', file: 'ApplicationController.php', v: `public function store(Request $request)
{
    $data = $request->validate([
        'room_name' => ['required', 'string', 'max:255'],
        'start_date' => ['required', 'date', 'after_or_equal:today'],
        'payment_type' => ['required', 'in:in_person,sbp'],
    ], [
        'room_name.required' => 'Введите название помещения.',
        'start_date.required' => 'Укажите дату начала конференции.',
        'start_date.after_or_equal' => 'Дата не может быть в прошлом.',
        'payment_type.in' => 'Недопустимый способ оплаты.',
    ]);

    $room = Room::where('name', $data['room_name'])->first();

    if (!$room) {
        return back()
            ->withErrors(['room_name' => 'Помещение с таким названием не найдено.'])
            ->withInput();
    }

    Application::create([
        'user_id' => auth()->id(),
        'room_id' => $room->id,
        'start_date' => $data['start_date'],
        'payment_type' => $data['payment_type'],
        'status' => 'new',
    ]);

    return redirect()->route('profile')
        ->with('success', 'Заявка отправлена на рассмотрение.');
}` },
        { t: 'p', v: 'Пользователь вводит название, мы находим помещение и сохраняем `room_id`. Если не нашли — показываем ошибку у поля. Так требование «ввести название» выполнено, а база остаётся нормализованной.' },
        { t: 'h', v: 'Вариант задания 2: выпадающий список' },
        { t: 'code', lang: 'blade', file: 'форма', v: `<div class="mb-3">
    <label for="room_id" class="form-label">Помещение</label>
    <select id="room_id" name="room_id" class="form-select @error('room_id') is-invalid @enderror">
        <option value="">Выберите помещение</option>
        @foreach ($rooms as $room)
            <option value="{{ $room->id }}" @selected(old('room_id') == $room->id)>
                {{ $room->name }} — до {{ $room->capacity }} мест
            </option>
        @endforeach
    </select>
    @error('room_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>` },
        { t: 'code', lang: 'php', file: 'контроллер', v: `$data = $request->validate([
    'room_id' => ['required', 'exists:rooms,id'],
    'start_date' => ['required', 'date', 'after_or_equal:today'],
    'payment_type' => ['required', 'in:in_person,sbp'],
], [
    'room_id.required' => 'Выберите помещение.',
    'room_id.exists' => 'Выбранное помещение не найдено.',
    'start_date.after_or_equal' => 'Дата не может быть в прошлом.',
]);

Application::create([
    ...$data,
    'user_id' => auth()->id(),
    'status' => 'new',
]);` },
        { t: 'code', lang: 'php', file: 'create()', v: `public function create()
{
    $rooms = Room::orderBy('name')->get();

    return view('application', compact('rooms'));
}` },
        { t: 'note', v: 'В обоих вариантах дата — обязательное поле и не в прошлом. В задании сказано «текстовое поле» для даты; `input type="date"` остаётся текстовым по сути и даёт удобный календарь. Если эксперт строго требует текст — поставьте `type="text"` с проверкой формата.' },
        { t: 'h', v: 'Главная со списком помещений' },
        { t: 'code', lang: 'blade', file: 'resources/views/home.blade.php', v: `@extends('layouts.app')

@section('content')
    <section class="hero">
        <h1>Помещения для конференций</h1>
        <p>Выберите аудиторию, коворкинг или кинозал и оставьте заявку.</p>
        <a href="{{ route('application') }}" class="btn btn-primary">Оставить заявку</a>
    </section>

    <div class="row g-3">
        @foreach ($rooms as $room)
            <div class="col-12 col-md-6 col-lg-4">
                <article class="card h-100">
                    @if ($room->image)
                        <img src="{{ asset($room->image) }}" class="card-img-top"
                             alt="{{ $room->name }}">
                    @endif
                    <div class="card-body">
                        <h2 class="h5">{{ $room->name }}</h2>
                        <p class="text-muted">{{ $room->description }}</p>
                        <p class="small mb-0">До {{ $room->capacity }} мест</p>
                    </div>
                </article>
            </div>
        @endforeach
    </div>
@endsection` },
        { t: 'p', v: 'Контроллер главной: `return view(\'home\', [\'rooms\' => Room::all()]);`. Не забудьте про пустое состояние, если помещений нет.' },
        { t: 'ok', label: 'Проверка', v: 'Заявка создаётся, появляется в кабинете, статус «Новая». Дата в прошлом не проходит. Несуществующее помещение (в варианте 1) даёт ошибку у поля.' }
      ],
      practice: [
        'Сделайте форму с текстовым полем помещения и проверкой существования.',
        'Проверьте дату: вчерашняя не проходит, завтрашняя проходит.',
        'Создайте заявку и найдите её в базе с правильным `user_id`.',
        'Замените текстовое поле на `select` и сравните поведение.'
      ],
      quiz: [
        { q: 'Как в задании 1 выбрать помещение?', options: ['Из выпадающего списка', 'Ввести название текстом', 'По ID'], answer: 1, explain: 'В задании 2 появляется выпадающий список.' },
        { q: 'Что сохраняем в заявку при вводе названия?', options: ['Строку названия', '`room_id` найденного помещения', 'Ничего'], answer: 1, explain: 'База остаётся нормализованной.' },
        { q: 'Какое правило не даст указать прошлую дату?', options: ['`date`', '`after_or_equal:today`', '`required`'], answer: 1, explain: '`date` проверяет формат, а `after_or_equal` — диапазон.' }
      ]
    },
    {
      id: 'build-profile',
      title: 'Шаг 7. Кабинет и отзыв',
      minutes: 20,
      level: 'практика',
      tags: ['практика', 'отзывы'],
      goal: 'Показать свои заявки со статусами и открыть отзыв только после завершения.',
      blocks: [
        { t: 'p', v: 'Личный кабинет — место, где пользователь видит результат своей работы. Здесь важны три вещи: только свои заявки, понятные статусы и отзыв, доступный после завершения.' },
        { t: 'h', v: 'Контроллер' },
        { t: 'code', lang: 'php', file: 'ApplicationController.php', v: `public function index()
{
    $applications = Application::with('room')
        ->where('user_id', auth()->id())
        ->orderByDesc('created_at')
        ->get();

    return view('profile', compact('applications'));
}` },
        { t: 'p', v: '`where(\'user_id\', auth()->id())` — обязательная строка. Без неё пользователь увидит чужие заявки. Проверять это будет эксперт, и это дешёвые баллы за безопасность.' },
        { t: 'h', v: 'Шаблон' },
        { t: 'code', lang: 'blade', file: 'resources/views/profile.blade.php', v: `@extends('layouts.app')

@section('title', 'Мои заявки')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h1 class="mb-0">Мои заявки</h1>
        <a href="{{ route('application') }}" class="btn btn-primary">Новая заявка</a>
    </div>

    @forelse ($applications as $application)
        <article class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h2 class="h5 mb-1">{{ $application->room->name }}</h2>
                        <span class="badge status status--{{ $application->status }}">
                            {{ $application->status_label }}
                        </span>
                    </div>
                    <small class="text-muted">
                        №{{ $application->id }} от {{ $application->created_at->format('d.m.Y H:i') }}
                    </small>
                </div>

                <dl class="row mt-3 mb-0">
                    <dt class="col-6 small text-muted">Дата начала</dt>
                    <dd class="col-6 mb-0">{{ $application->start_date->format('d.m.Y') }}</dd>
                    <dt class="col-6 small text-muted">Оплата</dt>
                    <dd class="col-6 mb-0">{{ $application->payment_label }}</dd>
                </dl>

                @if ($application->status === 'completed')
                    <hr>
                    @if ($application->review)
                        <p class="mb-0"><strong>Ваш отзыв:</strong> {{ $application->review }}</p>
                        <p class="mb-0">Оценка: {{ $application->rating }} из 5</p>
                    @else
                        <h3 class="h6">Оставить отзыв о качестве мероприятия</h3>
                        <form method="POST" action="{{ route('application.review', $application) }}">
                            @csrf
                            <div class="mb-2">
                                <label for="rating_{{ $application->id }}" class="form-label">Оценка</label>
                                <select id="rating_{{ $application->id }}" name="rating" class="form-select">
                                    @for ($i = 5; $i >= 1; $i--)
                                        <option value="{{ $i }}" @selected(old('rating') == $i)>{{ $i }}</option>
                                    @endfor
                                </select>
                                @error('rating') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-2">
                                <label for="review_{{ $application->id }}" class="form-label">Отзыв</label>
                                <textarea id="review_{{ $application->id }}" name="review"
                                          class="form-control" rows="3" maxlength="1000">{{ old('review') }}</textarea>
                                @error('review') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <button type="submit" class="btn btn-primary">Сохранить отзыв</button>
                        </form>
                    @endif
                @endif
            </div>
        </article>
    @empty
        <div class="alert alert-info">
            У вас пока нет заявок.
            <a href="{{ route('application') }}">Создайте первую</a>.
        </div>
    @endforelse
@endsection` },
        { t: 'h', v: 'Сохранение отзыва' },
        { t: 'code', lang: 'php', file: 'ApplicationController.php', v: `public function storeReview(Request $request, Application $application)
{
    if ($application->user_id !== auth()->id()) {
        abort(403, 'Доступ запрещён.');
    }

    if ($application->status !== 'completed') {
        return back()->with('error', 'Отзыв доступен только после завершения мероприятия.');
    }

    $data = $request->validate([
        'rating' => ['required', 'integer', 'between:1,5'],
        'review' => ['nullable', 'string', 'max:1000'],
    ], [
        'rating.required' => 'Выберите оценку.',
        'rating.between' => 'Оценка должна быть от 1 до 5.',
        'review.max' => 'Отзыв не должен превышать 1000 символов.',
    ]);

    $application->update($data);

    return back()->with('success', 'Спасибо, отзыв сохранён.');
}` },
        { t: 'p', v: 'Проверка статуса — не только в шаблоне. Пользователь может отправить POST на URL отзыва вручную. Серверная проверка обязательна.' },
        { t: 'h', v: 'Маршрут' },
        { t: 'code', lang: 'php', file: 'routes/web.php', v: `Route::post('/application/{application}/review', [ApplicationController::class, 'storeReview'])
    ->middleware('auth')
    ->name('application.review');` },
        { t: 'h', v: 'Статусы на экране' },
        { t: 'p', v: 'Цветовой бейдж — мелочь, которая делает кабинет понятным. Не используйте только цвет: добавьте текст статуса, чтобы информация была доступна и дальтоникам.' },
        { t: 'code', lang: 'css', file: 'public/css/app.css', v: `.status--new { color: #8a5a00; background: #f6ecd5; }
.status--assigned { color: #1b4f8a; background: #e2e8ee; }
.status--completed { color: #2e6b4f; background: #e0ebe4; }

.status {
    display: inline-block;
    padding: 2px 8px;
    border: 1px solid currentColor;
    font-size: 12px;
    font-weight: 600;
}` },
        { t: 'ok', label: 'Проверка', v: 'Пользователь видит только свои заявки. Завершённая показывает форму отзыва, остальные — нет. Чужой URL отзыва даёт 403. После сохранения отзыв виден без перезагрузки.' }
      ],
      practice: [
        'Выведите заявки только текущего пользователя.',
        'Добавьте бейджи статусов с текстом.',
        'Откройте форму отзыва только для `completed`.',
        'Проверьте чужой URL отзыва и POST без статуса.'
      ],
      quiz: [
        { q: 'Что обязательно в запросе списка заявок?', options: ['`orderBy`', '`where(\'user_id\', auth()->id())`', '`paginate`'], answer: 1, explain: 'Иначе пользователь увидит чужие заявки.' },
        { q: 'Когда доступен отзыв?', options: ['Всегда', 'Только после `completed`', 'Только админу'], answer: 1, explain: 'Требование КИМ.' },
        { q: 'Почему проверки в шаблоне недостаточно?', options: ['Шаблон можно обойти прямым POST', 'Шаблон медленный', 'Blade не поддерживает условия'], answer: 0, explain: 'Серверная проверка обязательна.' }
      ]
    },
    {
      id: 'build-admin',
      title: 'Шаг 8. Админка: все заявки и статусы',
      minutes: 26,
      level: 'практика',
      tags: ['практика', 'админка'],
      goal: 'Показать все заявки, отфильтровать по статусу, найти по ФИО и сменить статус.',
      blocks: [
        { t: 'p', v: 'Админка — самый весомый экран. Здесь проверяют и список, и фильтры, и статусы, и разграничение доступа. Сделайте её простой: таблица или карточки, фильтр, поиск, кнопка смены статуса.' },
        { t: 'h', v: 'Контроллер' },
        { t: 'code', lang: 'php', file: 'AdminController.php', v: `public function index(Request $request)
{
    $query = Application::with(['user', 'room']);

    $query->when($request->filled('status'), function ($q) use ($request) {
        $q->where('status', $request->status);
    });

    $query->when($request->filled('search'), function ($q) use ($request) {
        $search = trim($request->search);

        $q->where(function ($sub) use ($search) {
            $sub->whereHas('user', function ($u) use ($search) {
                $u->where('fio', 'like', "%{$search}%")
                  ->orWhere('login', 'like', "%{$search}%");
            })->orWhereHas('room', function ($r) use ($search) {
                $r->where('name', 'like', "%{$search}%");
            });
        });
    });

    $applications = $query->orderByDesc('created_at')->paginate(10)->withQueryString();

    $stats = [
        'total' => Application::count(),
        'new' => Application::where('status', 'new')->count(),
        'assigned' => Application::where('status', 'assigned')->count(),
        'completed' => Application::where('status', 'completed')->count(),
    ];

    return view('admin.index', compact('applications', 'stats'));
}` },
        { t: 'p', v: '`when()` не добавляет условие, если параметр пуст. Вложенный `where(function ...)` группирует OR-условия, иначе фильтр по статусу может «потеряться». Это классическая ошибка при поиске.' },
        { t: 'h', v: 'Смена статуса' },
        { t: 'code', lang: 'php', file: 'AdminController.php', v: `public function updateStatus(Request $request, Application $application)
{
    $data = $request->validate([
        'status' => ['required', 'in:new,assigned,completed'],
    ], [
        'status.required' => 'Выберите статус.',
        'status.in' => 'Недопустимый статус.',
    ]);

    $application->update($data);

    return back()->with('success', 'Статус изменён на «' . $application->status_label . '».');
}` },
        { t: 'code', lang: 'php', file: 'routes/web.php', v: `Route::post('/application/{application}/status', [AdminController::class, 'updateStatus'])
    ->name('admin.status');` },
        { t: 'h', v: 'Шаблон админки' },
        { t: 'code', lang: 'blade', file: 'resources/views/admin/index.blade.php', v: `@extends('layouts.app')

@section('title', 'Панель администратора')

@section('content')
    <h1 class="mb-4">Заявки</h1>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <span>Всего</span>
                <strong>{{ $stats['total'] }}</strong>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <span>Новые</span>
                <strong>{{ $stats['new'] }}</strong>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <span>Назначены</span>
                <strong>{{ $stats['assigned'] }}</strong>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <span>Завершены</span>
                <strong>{{ $stats['completed'] }}</strong>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route('admin') }}" class="row g-2 mb-4">
        <div class="col-12 col-md-5">
            <input type="search" name="search" class="form-control"
                   value="{{ request('search') }}"
                   placeholder="ФИО, логин или помещение">
        </div>
        <div class="col-8 col-md-4">
            <select name="status" class="form-select">
                <option value="">Все статусы</option>
                @foreach (\\App\\Models\\Application::STATUS_LABELS as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-4 col-md-3 d-flex gap-2">
            <button class="btn btn-primary w-100">Найти</button>
            <a href="{{ route('admin') }}" class="btn btn-outline-secondary">Сброс</a>
        </div>
    </form>

    @forelse ($applications as $application)
        <article class="application-card">
            <div class="application-card__head">
                <div>
                    <h2 class="h5 mb-1">{{ $application->user->fio }}</h2>
                    <p class="small text-muted mb-0">
                        {{ $application->user->login }} · {{ $application->user->phone }}
                    </p>
                </div>
                <span class="badge status status--{{ $application->status }}">
                    {{ $application->status_label }}
                </span>
            </div>

            <dl class="row mt-3 mb-3">
                <dt class="col-5 small text-muted">Помещение</dt>
                <dd class="col-7">{{ $application->room->name }}</dd>
                <dt class="col-5 small text-muted">Дата начала</dt>
                <dd class="col-7">{{ $application->start_date->format('d.m.Y') }}</dd>
                <dt class="col-5 small text-muted">Оплата</dt>
                <dd class="col-7">{{ $application->payment_label }}</dd>
            </dl>

            <form method="POST" action="{{ route('admin.status', $application) }}"
                  class="d-flex gap-2 align-items-center">
                @csrf
                <label for="status_{{ $application->id }}" class="small">Статус</label>
                <select id="status_{{ $application->id }}" name="status" class="form-select form-select-sm">
                    @foreach (\\App\\Models\\Application::STATUS_LABELS as $value => $label)
                        <option value="{{ $value }}" @selected($application->status === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                <button class="btn btn-sm btn-primary">Сохранить</button>
            </form>
        </article>
    @empty
        <div class="alert alert-info">Заявок нет или фильтр ничего не нашёл.</div>
    @endforelse

    {{ $applications->links() }}
@endsection` },
        { t: 'h', v: 'Карточки и таблица' },
        { t: 'p', v: 'В задании 2 просят «отображение карточек заявок» — используйте карточки, а не только таблицу. На мобильном карточки читаются лучше. Таблица тоже допустима, но должна прокручиваться, а не ломать страницу.' },
        { t: 'code', lang: 'css', file: 'public/css/app.css', v: `.application-card {
    padding: 18px 20px;
    margin-bottom: 12px;
    background: #fff;
    border: 1px solid #d9d5cb;
}

.application-card__head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
}

.stat-card {
    padding: 14px 16px;
    background: #fff;
    border: 1px solid #d9d5cb;
}

.stat-card span {
    display: block;
    font-size: 12px;
    color: #6b6f6d;
}

.stat-card strong {
    display: block;
    margin-top: 4px;
    font-size: 26px;
    line-height: 1;
}

@media (max-width: 480px) {
    .application-card__head { flex-direction: column; }
}` },
        { t: 'h', v: 'Что проверить' },
        { t: 'ul', v: [
          'Админ видит все заявки, пользователь — только свои.',
          'Фильтр по статусу и поиск работают вместе.',
          'После смены статуса он сохраняется и виден пользователю.',
          'Пагинация не сбрасывает фильтр.',
          'Пустой результат показывает сообщение.',
          'На 390 px карточки не вылезают за экран.'
        ] },
        { t: 'ok', label: 'Готово, если', v: 'Вы можете войти админом, увидеть список, отфильтровать новые, найти по фамилии и перевести заявку в «Завершено».' }
      ],
      practice: [
        'Сделайте админский список с карточками.',
        'Добавьте фильтр по статусу и поиск по ФИО.',
        'Сделайте смену статуса через POST.',
        'Проверьте пагинацию с активным фильтром.'
      ],
      quiz: [
        { q: 'Почему OR-условия поиска группируют в `where(function ...)`?', options: ['Чтобы не конфликтовали с фильтром статуса', 'Для скорости', 'Для пагинации'], answer: 0, explain: 'Иначе OR «съедает» условие статуса.' },
        { q: 'Что сохраняет фильтры в пагинации?', options: ['`withQueryString()`', '`links()`', '`paginate(10)`'], answer: 0, explain: 'Иначе страница 2 откроется без фильтра.' },
        { q: 'Кто может менять статус?', options: ['Любой авторизованный', 'Только администратор', 'Гость'], answer: 1, explain: 'Проверка роли обязательна на сервере.' }
      ]
    },
    {
      id: 'build-check',
      title: 'Шаг 9. Проверка перед сдачей',
      minutes: 18,
      level: 'практика',
      tags: ['практика', 'сдача'],
      goal: 'Пройти чек-лист из 20 пунктов и не потерять баллы на мелочах.',
      blocks: [
        { t: 'p', v: 'Последние 20 минут экзамена — не время дописывать новые функции. Время проверять то, что уже есть. Ниже список, который закрывает большинство критериев.' },
        { t: 'h', v: 'Запуск с нуля' },
        { t: 'code', lang: 'bash', file: 'терминал', v: 'php artisan optimize:clear\nphp artisan migrate:fresh --seed\nphp artisan serve' },
        { t: 'p', v: 'Если после этого что-то сломалось — лучше узнать сейчас, чем на защите. Проверьте вход админом из задания и создание заявки новым пользователем.' },
        { t: 'h', v: 'Сценарий целиком' },
        { t: 'steps', v: [
          { b: 'Регистрация', t: 'Создайте нового пользователя. Проверьте каждую ошибку: пустое поле, короткий логин, занятый логин, кривой телефон, короткий пароль.' },
          { b: 'Вход', t: 'Войдите новым пользователем. Проверьте неверный пароль и сообщение.' },
          { b: 'Заявка', t: 'Создайте заявку, проверьте дату в прошлом и пустое помещение.' },
          { b: 'Кабинет', t: 'Убедитесь, что заявка видна, статус «Новая», чужих заявок нет.' },
          { b: 'Админ', t: 'Войдите админом, найдите заявку, переведите в «Завершено».' },
          { b: 'Отзыв', t: 'Вернитесь пользователем, оставьте отзыв. Проверьте, что до завершения формы не было.' },
          { b: 'Мобильный', t: 'Откройте 390×844, проверьте меню, формы, карточки, отсутствие горизонтальной прокрутки.' },
          { b: 'Git', t: 'Сделайте финальный коммит и push. Проверьте, что репозиторий открывается.' }
        ] },
        { t: 'h', v: 'Чек-лист' },
        { t: 'ul', v: [
          '`migrate:fresh --seed` работает без ошибок.',
          'Админ входит логином и паролем из задания.',
          'Пароли в базе — хеши, не открытый текст.',
          'Регистрация не даёт выбрать роль.',
          'Валидация показывает ошибки у полей на русском.',
          'После ошибки поля не очищаются.',
          'Пользователь видит только свои заявки.',
          'Чужие заявки и отзывы дают 403.',
          'Админка закрыта middleware.',
          'Статус меняется и сохраняется.',
          'Отзыв доступен только после завершения.',
          'ER-диаграмма лежит в проекте.',
          'Изображения оптимизированы.',
          'Мобильный макет 390×844 — отдельные HTML.',
          'Нет горизонтальной прокрутки на 390/768/1440.',
          'Кнопки и ссылки имеют видимый фокус.',
          'В консоли нет ошибок.',
          'Нет CDN-ссылок, которые не откроются без интернета.',
          'Есть коммиты в начале и в конце.',
          'README или комментарий: как запустить.'
        ] },
        { t: 'warn', label: 'Стоп-лист', v: 'Не начинайте новую функцию за 15 минут до конца. Не переустанавливайте зависимости. Не делайте `migrate:fresh` без сидера. Не коммитьте `.env`.' },
        { t: 'ok', label: 'Сдано', v: 'Вы проходите сценарий от регистрации до отзыва без подсказок и не заглядывая в код. Значит, проект готов.' }
      ],
      practice: [
        'Пройдите весь сценарий на чистой базе.',
        'Проверьте 20 пунктов чек-листа и отметьте невыполненные.',
        'Откройте сайт на 390, 768 и 1440 px.',
        'Сделайте финальный коммит и проверьте репозиторий.'
      ],
      quiz: [
        { q: 'Что делать за 15 минут до конца?', options: ['Добавить новую функцию', 'Проверить сценарий и закоммитить', 'Переустановить Laravel'], answer: 1, explain: 'Новые функции ломают готовое.' },
        { q: 'Что проверять после `migrate:fresh --seed`?', options: ['Только главную', 'Вход админом и создание заявки', 'Цвет кнопок'], answer: 1, explain: 'Это доказывает, что база и сидеры согласованы.' }
      ]
    }
  ]
});
