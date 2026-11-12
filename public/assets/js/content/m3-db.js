window.COURSE_MODULES = window.COURSE_MODULES || [];
window.COURSE_MODULES.push({
  id: 'm3',
  num: '03',
  title: 'База данных',
  lead: 'Три таблицы, две связи, один статус. Схема, которую вы будете повторять на экзамене.',
  lessons: [
    {
      id: 'db-tables',
      title: 'Таблицы, ключи и типы',
      minutes: 20,
      level: 'база',
      tags: ['sql', 'бд'],
      goal: 'Спроектировать таблицы для заявок так, чтобы данные не дублировались и связи не рвались.',
      blocks: [
        { t: 'p', v: 'База — это не «файл с данными», а набор таблиц со строгими правилами. Если схема плохая, никакой красивый интерфейс не спасёт: появятся дубли, пустые связи и ошибки при удалении.' },
        { t: 'h', v: 'Строка, столбец, тип' },
        { t: 'p', v: 'Каждая таблица описывает одну сущность. Столбец имеет тип: число, строка, дата, текст. Тип ограничивает данные и помогает базе работать быстрее.' },
        { t: 'table', head: ['Тип', 'Когда использовать', 'Пример'], rows: [
          ['`INTEGER` / `BIGINT`', 'ID, количества, рейтинг', '`id`, `capacity`, `rating`'],
          ['`VARCHAR(255)`', 'Короткие строки', '`fio`, `login`, `email`'],
          ['`TEXT`', 'Длинный текст', '`description`, `review`'],
          ['`DATE`', 'Только дата', '`start_date`'],
          ['`DATETIME`', 'Дата и время', '`created_at`'],
          ['`DECIMAL(10,2)`', 'Деньги', '`price`'],
          ['`BOOLEAN`', 'Да/нет', '`is_active`']
        ] },
        { t: 'h', v: 'Первичный ключ' },
        { t: 'p', v: '`id` — уникальный номер строки. В Laravel это `$table->id()` (bigint, auto increment). По нему строятся все связи. Никогда не используйте ФИО или email как первичный ключ: они меняются.' },
        { t: 'h', v: 'Внешний ключ' },
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
        { t: 'p', v: '`foreignId(...)->constrained()` создаёт колонку, внешний ключ и связь с таблицей по имени. `cascadeOnDelete()` удаляет заявки вместе с пользователем. Это лучше, чем оставлять «сирот» с несуществующим `user_id`.' },
        { t: 'h', v: 'Ограничения' },
        { t: 'table', head: ['Ограничение', 'Что делает', 'Где нужно'], rows: [
          ['`NOT NULL`', 'Запрещает пустое значение', 'Обязательные поля'],
          ['`UNIQUE`', 'Запрещает дубли', '`login`, `email`'],
          ['`DEFAULT`', 'Подставляет значение', '`status`, `role`'],
          ['`FOREIGN KEY`', 'Проверяет существование связанной строки', '`user_id`, `room_id`'],
          ['`INDEX`', 'Ускоряет поиск', '`status`, `created_at`']
        ] },
        { t: 'code', lang: 'php', file: 'users', v: `Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('fio');
    $table->string('login')->unique();
    $table->string('phone');
    $table->string('email')->unique();
    $table->string('password');
    $table->enum('role', ['admin', 'user'])->default('user');
    $table->timestamps();
});` },
        { t: 'h', v: 'Нормализация на пальцах' },
        { t: 'ul', v: [
          'Не храните название помещения в каждой заявке — храните `room_id`.',
          'Не храните список статусов текстом — используйте `enum` или отдельную таблицу.',
          'Не дублируйте ФИО пользователя в заявке — связь по `user_id`.',
          'Одно поле — одно значение. Телефон и email — разные колонки, не «контакты» через запятую.'
        ] },
        { t: 'p', v: 'Это и есть нормализация: данные живут в одном месте, а связи ссылаются на них по ключу. На экзамене достаточно первых трёх правил.' },
        { t: 'h', v: 'Даты и время' },
        { t: 'p', v: 'Для даты начала используйте `DATE`, для момента создания — `timestamps()`. Не храните даты строками `\'12.11.2026\'`: по ним нельзя сортировать и сравнивать. В MySQL формат — `YYYY-MM-DD`.' },
        { t: 'h', v: 'Проверка схемы' },
        { t: 'code', lang: 'sql', file: 'SQL', v: `-- Все заявки со статусом new, сначала новые
SELECT id, user_id, room_id, start_date
FROM applications
WHERE status = 'new'
ORDER BY created_at DESC;

-- Количество заявок по статусам
SELECT status, COUNT(*) AS total
FROM applications
GROUP BY status;` },
        { t: 'note', v: 'На экзамене не обязательно писать сложные SQL-запросы: Eloquent сделает это за вас. Но схему вы проектируете сами, и именно она проверяется в первом модуле.' },
        { t: 'h', v: 'Чек-лист схемы' },
        { t: 'ol', v: [
          'У каждой таблицы есть `id`.',
          'Все обязательные поля `NOT NULL`, необязательные — `nullable()`.',
          'Логины и email уникальны.',
          'Связи оформлены внешними ключами.',
          'Статусы ограничены списком значений.',
          'Есть `created_at` и `updated_at` для сортировки и отображения.'
        ] }
      ],
      practice: [
        'Нарисуйте схему из трёх таблиц для своей темы: users, объекты, заявки.',
        'Определите типы для каждого поля и отметьте обязательные.',
        'Создайте миграции и выполните `php artisan migrate`.',
        'Откройте базу через DB Browser for SQLite или HeidiSQL и проверьте внешние ключи.'
      ],
      quiz: [
        { q: 'Почему название помещения не хранят в таблице заявок?', options: ['Это запрещено SQL', 'Будет дублирование и рассинхронизация при переименовании', 'Название слишком длинное'], answer: 1, explain: 'Правильно хранить `room_id` и брать название через связь.' },
        { q: 'Что делает `cascadeOnDelete()`?', options: ['Удаляет связанные записи при удалении родителя', 'Запрещает удаление', 'Создаёт индекс'], answer: 0, explain: 'Заявки удалятся вместе с пользователем, «сирот» не останется.' },
        { q: 'Какое ограничение не даст создать двух пользователей с одинаковым логином?', options: ['NOT NULL', 'UNIQUE', 'DEFAULT'], answer: 1, explain: 'Уникальный индекс на колонке.' }
      ]
    },
    {
      id: 'db-relations-er',
      title: 'Связи, JOIN и ER-диаграмма',
      minutes: 22,
      level: 'средний',
      tags: ['sql', 'бд', 'er'],
      goal: 'Читать и рисовать связи один-ко-многим, писать JOIN и сделать ER-диаграмму для сдачи.',
      blocks: [
        { t: 'p', v: 'Связи — то, что превращает набор таблиц в систему. На экзамене почти всегда нужна одна связь «один-ко-многим»: у пользователя много заявок, у помещения много заявок.' },
        { t: 'h', v: 'Один-ко-многим' },
        { t: 'p', v: 'Одна строка в `users` связана с многими строками в `applications`. Внешний ключ `user_id` лежит на стороне «многих». В Laravel это `hasMany` и `belongsTo`.' },
        { t: 'code', lang: 'php', file: 'app/Models/User.php', v: `public function applications(): HasMany
{
    return $this->hasMany(Application::class);
}` },
        { t: 'code', lang: 'php', file: 'app/Models/Application.php', v: `public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}

public function room(): BelongsTo
{
    return $this->belongsTo(Room::class);
}` },
        { t: 'p', v: 'Читается так: «у пользователя много заявок», «заявка принадлежит пользователю и помещению». Имена методов — множественное число для `hasMany`, единственное для `belongsTo`.' },
        { t: 'h', v: 'Много-ко-многим' },
        { t: 'p', v: 'Если у поста много тегов, а у тега много постов, нужна промежуточная таблица `post_tag` с двумя внешними ключами. На экзамене это встречается в админке блога, но реже, чем заявки.' },
        { t: 'code', lang: 'php', file: 'миграция post_tag', v: `Schema::create('post_tag', function (Blueprint $table) {
    $table->foreignId('post_id')->constrained()->cascadeOnDelete();
    $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
    $table->primary(['post_id', 'tag_id']);
});` },
        { t: 'h', v: 'JOIN' },
        { t: 'p', v: 'JOIN собирает данные из нескольких таблиц в один результат. Eloquent с `with()` делает это отдельными запросами и склеивает в PHP — обычно этого достаточно.' },
        { t: 'code', lang: 'sql', file: 'JOIN', v: `SELECT
    applications.id,
    applications.start_date,
    applications.status,
    users.fio,
    rooms.name AS room_name
FROM applications
JOIN users ON users.id = applications.user_id
JOIN rooms ON rooms.id = applications.room_id
WHERE applications.status = 'new'
ORDER BY applications.created_at DESC;` },
        { t: 'p', v: '`JOIN` оставляет только строки, где связь существует. `LEFT JOIN` сохранит заявки даже без пользователя — полезно для диагностики, но в норме таких быть не должно.' },
        { t: 'h', v: 'Жадная загрузка' },
        { t: 'code', lang: 'php', file: 'контроллер', v: `// Плохо: для каждой заявки отдельный запрос к room
$applications = Application::all();

// Хорошо: один дополнительный запрос на всех
$applications = Application::with(['user', 'room'])
    ->orderByDesc('created_at')
    ->get();` },
        { t: 'p', v: 'Проблема N+1 — классический вопрос на защите. Если в цикле Blade обращаться к `$application->room->name` без `with()`, Laravel сделает запрос на каждую строку. На маленьких данных незаметно, на больших — медленно.' },
        { t: 'h', v: 'ER-диаграмма' },
        { t: 'p', v: 'ER-диаграмма — это схема: прямоугольники-таблицы, поля внутри, линии связей с подписями `1` и `N`. Её часто требуют в документации или просят показать на защите.' },
        { t: 'code', lang: 'text', file: 'структура', v: `users                     rooms
┌──────────────────┐      ┌──────────────────┐
│ id (PK)          │      │ id (PK)          │
│ fio              │      │ name             │
│ login (unique)   │      │ type             │
│ phone            │      │ capacity         │
│ email (unique)   │      │ image            │
│ password         │      └────────┬─────────┘
│ role             │               │ 1
└────────┬─────────┘               │
         │ 1                       │
         │                         │ N
         │        applications     │
         │      ┌──────────────────┴─┐
         └──── N│ id (PK)              │
                │ user_id (FK)         │
                │ room_id (FK)         │
                │ start_date           │
                │ payment_type         │
                │ status               │
                │ rating, review       │
                └──────────────────────┘` },
        { t: 'p', v: 'Нарисуйте такую же схему для своей темы. В docs достаточно SVG или PNG, сделанного в draw.io, Mermaid или даже аккуратно в Word. Главное — чтобы были видны ключи и кардинальность.' },
        { t: 'h', v: 'Mermaid для документации' },
        { t: 'code', lang: 'text', file: 'docs/er.md', v: `erDiagram
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
        { t: 'p', v: 'Mermaid рендерится в GitHub, GitLab и многих редакторах. Это быстрый способ сделать диаграмму без графического редактора.' },
        { t: 'h', v: 'Проверка связей' },
        { t: 'code', lang: 'php', file: 'tinker', v: `php artisan tinker

$user = App\\Models\\User::first();
$user->applications;          // коллекция заявок
$user->applications()->count();

$application = App\\Models\\Application::with('room')->first();
$application->room->name;` },
        { t: 'note', v: 'Если связь возвращает пустую коллекцию, проверьте три вещи: имя метода, имя внешнего ключа и наличие данных. Laravel ожидает `user_id` для `user()`; если колонка называется иначе, укажите её вторым аргументом.' },
        { t: 'h', v: 'Типичные ошибки' },
        { t: 'ul', v: [
          'Забыли `foreignId` и храните ID как обычное число — база не проверяет существование.',
          'Назвали метод `application()` вместо `applications()` — `hasMany` вернёт пустоту.',
          'Удалили пользователя, а заявки остались с битым `user_id`.',
          'Не сделали `with()` и получили N+1.',
          'Нарисовали ER без кардинальности — эксперт не понимает, где один, а где много.'
        ] }
      ],
      practice: [
        'Свяжите модели `User` и `Application` методами `hasMany` и `belongsTo`.',
        'Сделайте `Application::with([\'user\', \'room\'])->get()` и проверьте количество запросов через Debugbar или лог.',
        'Нарисуйте ER-диаграмму своей схемы в draw.io или Mermaid.',
        'Напишите SQL с JOIN, который выводит ФИО пользователя и название помещения для каждой заявки.'
      ],
      quiz: [
        { q: 'Где лежит внешний ключ при связи один-ко-многим?', options: ['На стороне «одного»', 'На стороне «многих»', 'В отдельной таблице всегда'], answer: 1, explain: 'В `applications` лежит `user_id`, потому что заявок много.' },
        { q: 'Что решает `with([\'room\'])`?', options: ['Проблему N+1', 'Проблему CSRF', 'Дубли записей'], answer: 0, explain: 'Связь загружается одним дополнительным запросом.' },
        { q: 'Что показывает `1` и `N` на ER-диаграмме?', options: ['Количество полей', 'Кардинальность связи', 'Типы данных'], answer: 1, explain: 'Один пользователь — много заявок.' }
      ]
    }
  ]
});
