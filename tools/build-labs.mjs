// Сборка практикума: 14 лабораторных с проверками и решениями.
// Запуск: node tools/build-labs.mjs
import fs from 'node:fs';

const labs = [
  {
    slug: 'html-application-card',
    module_num: '01',
    lesson_slug: 'forms-html',
    title: 'Собрать карточку заявки на HTML',
    level: 'база',
    minutes: 20,
    goal: 'Собрать семантическую карточку заявки: заголовок, статус, дата и характеристики.',
    brief: 'В кабинете каждая заявка — отдельная карточка. Нужно сверстать её без Bootstrap: article, заголовок, бейдж статуса, time и список характеристик. Это тот кусок, который потом переносится в Blade почти без изменений.',
    steps: [
      'Создайте article с классом application.',
      'Внутри сделайте header с заголовком h2 и бейджем status status--assigned.',
      'Добавьте time с датой 12.11.2026.',
      'Добавьте dl с двумя парами dt/dd: дата начала и оплата.',
    ],
    starter_code: String.raw`<article class="application">
  <!-- заголовок, статус, дата, характеристики -->
</article>`,
    language: 'html',
    hints: [
      'Для статуса используйте класс status и модификатор status--assigned.',
      'Дата в time: атрибут datetime="2026-11-12", текст 12.11.2026.',
      'Характеристики — dl, внутри div с dt и dd.',
    ],
    solution: String.raw`<article class="application">
  <header class="application-head">
    <div>
      <h2>Коворкинг-зона «Вектор»</h2>
      <span class="status status--assigned">Мероприятие назначено</span>
    </div>
    <time datetime="2026-11-12">12.11.2026</time>
  </header>

  <dl class="application-meta">
    <div>
      <dt>Дата начала</dt>
      <dd>12.11.2026</dd>
    </div>
    <div>
      <dt>Оплата</dt>
      <dd>Переводом по СБП</dd>
    </div>
  </dl>
</article>`,
    checks: {
      checks: [
        { type: 'contains', label: 'Есть article', value: '<article' },
        { type: 'contains', label: 'Класс application', value: 'class="application"' },
        { type: 'contains', label: 'Шапка карточки', value: 'application-head' },
        { type: 'contains', label: 'Бейдж статуса', value: 'status--' },
        { type: 'contains', label: 'Семантическая дата', value: '<time' },
        { type: 'contains', label: 'Список характеристик', value: '<dl' },
        { type: 'contains', label: 'Пары dt/dd', value: '<dt' },
        { type: 'contains', label: 'Значения dd', value: '<dd' },
      ],
    },
    position: 1,
  },
  {
    slug: 'css-responsive-grid',
    module_num: '01',
    lesson_slug: 'css-layout',
    title: 'Сетка карточек 1 / 2 / 3',
    level: 'база',
    minutes: 20,
    goal: 'Сделать адаптивную сетку карточек без горизонтальной прокрутки.',
    brief: 'На телефоне одна колонка, на планшете две, на десктопе три. Нужно написать CSS для .rooms: grid, minmax, медиазапросы на 768 и 1100 px.',
    steps: [
      'Задайте .rooms display: grid и gap.',
      'Используйте repeat(auto-fit, minmax(240px, 1fr)) для базовой сетки.',
      'На 768 px — две колонки, на 1100 px — три.',
      'Проверьте, что на 390 px нет горизонтальной прокрутки.',
    ],
    starter_code: String.raw`.rooms {
  /* сетка */
}`,
    language: 'css',
    hints: [
      'grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));',
      'Медиазапрос: @media (min-width: 768px) { ... }',
      'Не фиксируйте ширину карточки в px — используйте 1fr.',
    ],
    solution: String.raw`.rooms {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 16px;
}

@media (min-width: 768px) {
  .rooms {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 1100px) {
  .rooms {
    grid-template-columns: repeat(3, 1fr);
  }
}`,
    checks: {
      checks: [
        { type: 'contains', label: 'Grid включён', value: 'display: grid' },
        { type: 'contains', label: 'Колонки', value: 'grid-template-columns' },
        { type: 'contains', label: 'minmax', value: 'minmax(' },
        { type: 'contains', label: 'Медиазапрос', value: '@media' },
        { type: 'contains', label: 'Планшетный брейкпоинт', value: '768' },
        { type: 'contains', label: 'Десктопный брейкпоинт', value: '1100' },
      ],
    },
    position: 2,
  },
  {
    slug: 'bootstrap-form',
    module_num: '01',
    lesson_slug: 'bootstrap',
    title: 'Форма Bootstrap с ошибкой поля',
    level: 'база',
    minutes: 20,
    goal: 'Собрать форму с классами Bootstrap и сообщением об ошибке у конкретного поля.',
    brief: 'Нужна форма входа или регистрации, где ошибка выглядит как часть интерфейса: красная рамка и текст под полем. Используйте form-label, form-control, is-invalid, invalid-feedback, @error и old().',
    steps: [
      'Создайте форму с method="POST" и @csrf.',
      'Добавьте label с классом form-label.',
      'Поле получите класс form-control и условный is-invalid.',
      'Под полем выведите @error с invalid-feedback.',
    ],
    starter_code: String.raw`<form method="POST" action="{{ route('login.store') }}">
    @csrf
    <!-- поле логина с ошибкой -->
</form>`,
    language: 'blade',
    hints: [
      'Класс поля: form-control @error(\'login\') is-invalid @enderror.',
      'Значение: value="{{ old(\'login\') }}".',
      'Ошибка: @error(\'login\') <div class="invalid-feedback">{{ $message }}</div> @enderror.',
    ],
    solution: String.raw`<form method="POST" action="{{ route('login.store') }}" novalidate>
    @csrf

    <div class="mb-3">
        <label for="login" class="form-label">Логин</label>
        <input type="text" id="login" name="login"
               class="form-control @error('login') is-invalid @enderror"
               value="{{ old('login') }}">
        @error('login')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <button type="submit" class="btn btn-primary">Войти</button>
</form>`,
    checks: {
      checks: [
        { type: 'contains', label: 'CSRF', value: '@csrf' },
        { type: 'contains', label: 'form-label', value: 'form-label' },
        { type: 'contains', label: 'form-control', value: 'form-control' },
        { type: 'contains', label: 'is-invalid', value: 'is-invalid' },
        { type: 'contains', label: 'invalid-feedback', value: 'invalid-feedback' },
        { type: 'contains', label: '@error', value: '@error' },
        { type: 'contains', label: 'old()', value: 'old(' },
      ],
    },
    position: 3,
  },
  {
    slug: 'php-register-validation',
    module_num: '02',
    lesson_slug: 'php-forms',
    title: 'Валидация регистрации на PHP',
    level: 'средний',
    minutes: 30,
    goal: 'Написать серверную валидацию полей регистрации и хеширование пароля.',
    brief: 'Это задание без Laravel: чистый PHP. Проверьте ФИО, логин, телефон, email и пароль, сложите ошибки в массив и захешируйте пароль. Так вы поймёте, что именно автоматизирует Laravel.',
    steps: [
      'Примите поля через $_POST с trim и ??.',
      'Проверьте ФИО, логин, телефон и email регулярками/filter_var.',
      'Проверьте длину пароля через mb_strlen.',
      'Сложите ошибки в $errors и захешируйте пароль password_hash.',
    ],
    starter_code: String.raw`$errors = [];

$fio = trim($_POST['fio'] ?? '');
$login = trim($_POST['login'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// проверки...`,
    language: 'php',
    hints: [
      'ФИО: preg_match(\'/^[А-ЯЁа-яё\\s-]+$/u\', $fio).',
      'Логин: /^[a-zA-Z0-9]{6,}$/.',
      'Телефон: /^8\\(\\d{3}\\)\\d{3}-\\d{2}-\\d{2}$/.',
      'Email: filter_var($email, FILTER_VALIDATE_EMAIL).',
    ],
    solution: String.raw`$errors = [];

$fio = trim($_POST['fio'] ?? '');
$login = trim($_POST['login'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($fio === '' || !preg_match('/^[А-ЯЁа-яё\s-]+$/u', $fio)) {
    $errors['fio'] = 'ФИО: только кириллица, пробелы и дефис.';
}

if (!preg_match('/^[a-zA-Z0-9]{6,}$/', $login)) {
    $errors['login'] = 'Логин: латиница и цифры, от 6 символов.';
}

if (!preg_match('/^8\(\d{3}\)\d{3}-\d{2}-\d{2}$/', $phone)) {
    $errors['phone'] = 'Телефон: 8(XXX)XXX-XX-XX.';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Некорректный email.';
}

if (mb_strlen($password) < 8) {
    $errors['password'] = 'Пароль от 8 символов.';
}

$hash = password_hash($password, PASSWORD_DEFAULT);`,
    checks: {
      php_lint: true,
      checks: [
        { type: 'contains', label: 'Массив ошибок', value: '$errors' },
        { type: 'contains', label: 'Регулярные проверки', value: 'preg_match' },
        { type: 'contains', label: 'Проверка email', value: 'filter_var' },
        { type: 'contains', label: 'Длина пароля', value: 'mb_strlen' },
        { type: 'contains', label: 'Хеширование', value: 'password_hash' },
        { type: 'not_contains', label: 'Без md5', value: 'md5(' },
      ],
    },
    position: 4,
  },
  {
    slug: 'php-pdo-insert',
    module_num: '02',
    lesson_slug: 'php-db',
    title: 'Вставить заявку через PDO',
    level: 'средний',
    minutes: 25,
    goal: 'Сделать безопасную вставку заявки через prepared statement.',
    brief: 'Нужно сохранить заявку в таблицу applications, не склеивая SQL и данные. Используйте именованные параметры, execute и lastInsertId.',
    steps: [
      'Напишите INSERT с параметрами :user_id, :room_id, :start_date, :status.',
      'Подготовьте запрос через $pdo->prepare().',
      'Передайте значения в execute().',
      'Получите ID через lastInsertId().',
    ],
    starter_code: String.raw`$sql = 'INSERT INTO applications (...) VALUES (...)';
// подготовьте и выполните запрос`,
    language: 'php',
    hints: [
      'Не подставляйте $_POST прямо в SQL.',
      'Именованные параметры читабельнее вопросительных знаков.',
      'lastInsertId вернёт ID новой строки.',
    ],
    solution: String.raw`$sql = 'INSERT INTO applications (user_id, room_id, start_date, status)
        VALUES (:user_id, :room_id, :start_date, :status)';

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':user_id' => $userId,
    ':room_id' => $roomId,
    ':start_date' => $startDate,
    ':status' => 'new',
]);

$id = (int) $pdo->lastInsertId();`,
    checks: {
      php_lint: true,
      checks: [
        { type: 'contains', label: 'prepare', value: 'prepare(' },
        { type: 'contains', label: 'execute', value: 'execute(' },
        { type: 'contains', label: 'Параметр user_id', value: ':user_id' },
        { type: 'contains', label: 'Параметр room_id', value: ':room_id' },
        { type: 'contains', label: 'ID новой записи', value: 'lastInsertId' },
        { type: 'not_contains', label: 'Без подстановки user_id из формы', value: "$_POST['user_id']" },
      ],
    },
    position: 5,
  },
  {
    slug: 'sql-user-applications',
    module_num: '03',
    lesson_slug: 'db-tables',
    title: 'SQL: заявки пользователя',
    level: 'средний',
    minutes: 25,
    goal: 'Выбрать заявки конкретного пользователя со статусом new и названием помещения.',
    brief: 'В базе есть users, rooms и applications. Напишите SELECT, который вернёт название помещения и дату начала для заявок Марии (login maria2027) со статусом new.',
    steps: [
      'Соедините applications с rooms и users.',
      'Отфильтруйте users.login = \'maria2027\' и status = \'new\'.',
      'Верните колонки room_name и start_date.',
      'Проверьте результат на учебной базе.',
    ],
    starter_code: String.raw`SELECT
    -- room_name, start_date
FROM applications
-- JOIN ...
WHERE -- условия`,
    language: 'sql',
    hints: [
      'Название помещения: rooms.name AS room_name.',
      'Условие по логину: users.login = \'maria2027\'.',
      'Нужны оба JOIN: rooms и users.',
    ],
    solution: String.raw`SELECT rooms.name AS room_name, applications.start_date
FROM applications
JOIN rooms ON rooms.id = applications.room_id
JOIN users ON users.id = applications.user_id
WHERE users.login = 'maria2027'
  AND applications.status = 'new';`,
    checks: {
      sql: {
        fixture: 'conference',
        ordered: false,
        columns: ['room_name', 'start_date'],
        rows: [
          ['Коворкинг-зона «Вектор»', '2026-11-12'],
          ['Большая конференц-аудитория', '2026-09-15'],
        ],
      },
    },
    position: 6,
  },
  {
    slug: 'sql-join-applications',
    module_num: '03',
    lesson_slug: 'db-relations-er',
    title: 'SQL: JOIN заявок, пользователей и помещений',
    level: 'средний',
    minutes: 30,
    goal: 'Собрать ФИО, помещение и статус для всех новых заявок.',
    brief: 'Админке нужен список новых заявок с именем пользователя и названием помещения. Напишите JOIN-запрос и верните три колонки: fio, room_name, status.',
    steps: [
      'Соедините applications с users и rooms.',
      'Оставьте только status = \'new\'.',
      'Верните fio, room_name, status.',
      'Проверьте, что строк ровно три.',
    ],
    starter_code: String.raw`SELECT
    -- fio, room_name, status
FROM applications
-- JOIN ...
WHERE -- status`,
    language: 'sql',
    hints: [
      'users.fio и rooms.name AS room_name.',
      'JOIN users ON users.id = applications.user_id.',
      'JOIN rooms ON rooms.id = applications.room_id.',
    ],
    solution: String.raw`SELECT users.fio, rooms.name AS room_name, applications.status
FROM applications
JOIN users ON users.id = applications.user_id
JOIN rooms ON rooms.id = applications.room_id
WHERE applications.status = 'new';`,
    checks: {
      sql: {
        fixture: 'conference',
        ordered: false,
        columns: ['fio', 'room_name', 'status'],
        rows: [
          ['Иванова Мария Сергеевна', 'Коворкинг-зона «Вектор»', 'new'],
          ['Петров Пётр Петрович', 'Коворкинг-зона «Вектор»', 'new'],
          ['Иванова Мария Сергеевна', 'Большая конференц-аудитория', 'new'],
        ],
      },
    },
    position: 7,
  },
  {
    slug: 'laravel-migration',
    module_num: '04',
    lesson_slug: 'laravel-migrations',
    title: 'Миграция заявок со связями',
    level: 'средний',
    minutes: 25,
    goal: 'Написать миграцию applications с внешними ключами, статусом и отзывом.',
    brief: 'Нужна таблица заявок для портала. Свяжите её с users и rooms, добавьте дату, оплату, статус, рейтинг и отзыв. Проверка смотрит на реальные методы Laravel.',
    steps: [
      'Создайте таблицу applications.',
      'Добавьте foreignId user_id и room_id с constrained и cascadeOnDelete.',
      'Добавьте date start_date.',
      'Добавьте enum payment_type и status.',
      'Добавьте nullable rating/review и timestamps.',
    ],
    starter_code: String.raw`Schema::create('applications', function (Blueprint $table) {
    $table->id();
    // связи, дата, оплата, статус, отзыв
});`,
    language: 'php',
    hints: [
      'foreignId(\'user_id\')->constrained()->cascadeOnDelete();',
      'enum(\'status\', [\'new\', \'assigned\', \'completed\'])->default(\'new\');',
      'Не забудьте timestamps().',
    ],
    solution: String.raw`Schema::create('applications', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('room_id')->constrained()->cascadeOnDelete();
    $table->date('start_date');
    $table->enum('payment_type', ['in_person', 'sbp'])->default('in_person');
    $table->enum('status', ['new', 'assigned', 'completed'])->default('new');
    $table->unsignedTinyInteger('rating')->nullable();
    $table->text('review')->nullable();
    $table->timestamps();
});`,
    checks: {
      php_lint: true,
      checks: [
        { type: 'contains', label: 'Связь с пользователем', value: "foreignId('user_id')" },
        { type: 'contains', label: 'Внешний ключ', value: 'constrained()' },
        { type: 'contains', label: 'Каскадное удаление', value: 'cascadeOnDelete' },
        { type: 'contains', label: 'Статус', value: "enum('status'" },
        { type: 'contains', label: 'Timestamps', value: 'timestamps()' },
      ],
    },
    position: 8,
  },
  {
    slug: 'laravel-validation',
    module_num: '04',
    lesson_slug: 'laravel-validation',
    title: 'Валидация заявки в Laravel',
    level: 'средний',
    minutes: 25,
    goal: 'Написать правила и русские сообщения для формы заявки.',
    brief: 'Форма отправляет room_id, start_date и payment_type. Нужно проверить существование помещения, дату не в прошлом и допустимый способ оплаты, а также дать понятные сообщения.',
    steps: [
      'Вызовите $request->validate().',
      'Добавьте exists:rooms,id для room_id.',
      'Добавьте after_or_equal:today для даты.',
      'Добавьте in:in_person,sbp для оплаты.',
      'Напишите сообщения на русском.',
    ],
    starter_code: String.raw`$data = $request->validate([
    // правила
], [
    // сообщения
]);`,
    language: 'php',
    hints: [
      'exists:rooms,id проверяет наличие помещения.',
      'after_or_equal:today не пропустит прошлую дату.',
      'in:in_person,sbp ограничивает оплату.',
    ],
    solution: String.raw`$data = $request->validate([
    'room_id' => ['required', 'exists:rooms,id'],
    'start_date' => ['required', 'date', 'after_or_equal:today'],
    'payment_type' => ['required', 'in:in_person,sbp'],
], [
    'room_id.required' => 'Выберите помещение.',
    'room_id.exists' => 'Помещение не найдено.',
    'start_date.required' => 'Укажите дату начала.',
    'start_date.after_or_equal' => 'Дата не может быть в прошлом.',
    'payment_type.in' => 'Недопустимый способ оплаты.',
]);`,
    checks: {
      php_lint: true,
      checks: [
        { type: 'contains', label: 'Проверка помещения', value: 'exists:rooms,id' },
        { type: 'contains', label: 'Дата не в прошлом', value: 'after_or_equal:today' },
        { type: 'contains', label: 'Способ оплаты', value: 'in:in_person,sbp' },
        { type: 'contains', label: 'Русские сообщения', value: 'Дата не может быть в прошлом' },
      ],
    },
    position: 9,
  },
  {
    slug: 'laravel-auth',
    module_num: '04',
    lesson_slug: 'laravel-auth',
    title: 'Вход через Auth::attempt',
    level: 'средний',
    minutes: 25,
    goal: 'Написать вход по логину и паролю с защитой сессии и редиректом по роли.',
    brief: 'Нужен метод входа: принять логин и пароль, проверить через Auth::attempt, обновить сессию и отправить администратора в /admin, а студента — в /profile. При ошибке вернуть сообщение.',
    steps: [
      'Примите login и password.',
      'Вызовите Auth::attempt с этими полями.',
      'Обновите сессию через session()->regenerate().',
      'Сделайте редирект по isAdmin().',
      'При ошибке верните back с ошибкой.',
    ],
    starter_code: String.raw`if (Auth::attempt([...])) {
    // сессия и редирект
}

// ошибка`,
    language: 'php',
    hints: [
      'Auth::attempt([\'login\' => $login, \'password\' => $password]).',
      '$request->session()->regenerate().',
      'auth()->user()->isAdmin().',
    ],
    solution: String.raw`if (Auth::attempt(['login' => $login, 'password' => $password])) {
    $request->session()->regenerate();

    return auth()->user()->isAdmin()
        ? redirect()->route('admin')
        : redirect()->route('profile');
}

return back()->withErrors(['login' => 'Неверный логин или пароль.']);`,
    checks: {
      php_lint: true,
      checks: [
        { type: 'contains', label: 'Auth::attempt', value: 'Auth::attempt' },
        { type: 'contains', label: 'Защита сессии', value: 'session()->regenerate' },
        { type: 'contains', label: 'Проверка роли', value: 'isAdmin' },
        { type: 'contains', label: 'Редирект по роли', value: "redirect()->route('admin')" },
        { type: 'contains', label: 'Сообщение об ошибке', value: 'Неверный логин или пароль' },
      ],
    },
    position: 10,
  },
  {
    slug: 'blade-register-form',
    module_num: '05',
    lesson_slug: 'build-register',
    title: 'Blade-форма регистрации',
    level: 'средний',
    minutes: 25,
    goal: 'Собрать форму регистрации с CSRF, старыми значениями и ошибками у полей.',
    brief: 'Нужна форма, которая не теряет данные после ошибки и показывает сообщение у конкретного поля. Это требование задания 2 и ежедневная практика Laravel.',
    steps: [
      'Добавьте @csrf.',
      'Сделайте поле login с name, id и value="{{ old(\'login\') }}".',
      'Добавьте условный класс is-invalid.',
      'Выведите @error с invalid-feedback.',
      'Добавьте кнопку «Создать пользователя».',
    ],
    starter_code: String.raw`<form method="POST" action="{{ route('register.store') }}">
    <!-- поля -->
</form>`,
    language: 'blade',
    hints: [
      '@error(\'login\') работает только с именем поля.',
      'old(\'login\') вернёт значение после ошибки.',
      'is-invalid — класс Bootstrap для красной рамки.',
    ],
    solution: String.raw`<form method="POST" action="{{ route('register.store') }}" novalidate>
    @csrf

    <div class="mb-3">
        <label for="login" class="form-label">Логин</label>
        <input type="text" id="login" name="login"
               class="form-control @error('login') is-invalid @enderror"
               value="{{ old('login') }}">
        @error('login')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <button type="submit" class="btn btn-primary">Создать пользователя</button>
</form>`,
    checks: {
      checks: [
        { type: 'contains', label: 'CSRF', value: '@csrf' },
        { type: 'contains', label: 'Старое значение', value: "old('login')" },
        { type: 'contains', label: 'Ошибка поля', value: "@error('login')" },
        { type: 'contains', label: 'is-invalid', value: 'is-invalid' },
        { type: 'contains', label: 'invalid-feedback', value: 'invalid-feedback' },
        { type: 'contains', label: 'Кнопка из задания', value: 'Создать пользователя' },
      ],
    },
    position: 11,
  },
  {
    slug: 'admin-status-update',
    module_num: '05',
    lesson_slug: 'build-admin',
    title: 'Смена статуса заявки',
    level: 'средний',
    minutes: 25,
    goal: 'Сделать серверную смену статуса с проверкой допустимых значений.',
    brief: 'Администратор меняет статус заявки. Нужно принять status, проверить его список, обновить заявку и вернуть сообщение. Никаких ссылок ?status= — только POST.',
    steps: [
      'Примите status и провалидируйте in:new,assigned,completed.',
      'Обновите заявку через update().',
      'Верните back с сообщением.',
      'Убедитесь, что статус нельзя подставить из URL.',
    ],
    starter_code: String.raw`public function updateStatus(Request $request, Application $application)
{
    // валидация и обновление
}`,
    language: 'php',
    hints: [
      'Правило: [\'required\', \'in:new,assigned,completed\'].',
      '$application->update($data).',
      'return back()->with(\'success\', ...).',
    ],
    solution: String.raw`public function updateStatus(Request $request, Application $application)
{
    $data = $request->validate([
        'status' => ['required', 'in:new,assigned,completed'],
    ], [
        'status.in' => 'Недопустимый статус.',
    ]);

    $application->update($data);

    return back()->with('success', 'Статус обновлён.');
}`,
    checks: {
      php_lint: true,
      checks: [
        { type: 'contains', label: 'Список статусов', value: 'in:new,assigned,completed' },
        { type: 'contains', label: 'Валидация', value: 'validate(' },
        { type: 'contains', label: 'Обновление', value: 'update($data)' },
        { type: 'contains', label: 'Ответ пользователю', value: 'back()' },
      ],
    },
    position: 12,
  },
  {
    slug: 'mobile-screen',
    module_num: '06',
    lesson_slug: 'mobile-layout',
    title: 'Мобильный экран 390×844',
    level: 'средний',
    minutes: 30,
    goal: 'Собрать один экран мобильного макета с каркасом, карточкой и нижним меню.',
    brief: 'Нужен HTML-экран под смартфон: viewport width=390, обёртка phone, верхняя панель, контент, карточка заявки и нижнее меню. Это кирпич задания 2.',
    steps: [
      'Поставьте meta viewport с width=390.',
      'Добавьте обёртку class="phone".',
      'Сделайте topbar, content и card.',
      'Добавьте tabbar с двумя ссылками.',
      'Подключите style.css.',
    ],
    starter_code: String.raw`<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=390, initial-scale=1.0">
    <title>Мои заявки</title>
</head>
<body>
    <!-- phone, topbar, content, card, tabbar -->
</body>
</html>`,
    language: 'html',
    hints: [
      'Обёртка: <div class="phone">.',
      'Нижнее меню: <nav class="tabbar">.',
      'Карточка: <article class="card">.',
    ],
    solution: String.raw`<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=390, initial-scale=1.0">
    <title>Мои заявки</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="phone">
        <header class="topbar">Конференции.РФ</header>

        <main class="content">
            <h1>Мои заявки</h1>

            <article class="card">
                <h2>Коворкинг «Вектор»</h2>
                <span class="badge">Новая</span>
                <a class="btn" href="application.html">Забронировать</a>
            </article>
        </main>

        <nav class="tabbar">
            <a href="index.html">Главная</a>
            <a href="profile.html">Заявки</a>
        </nav>
    </div>
</body>
</html>`,
    checks: {
      checks: [
        { type: 'contains', label: 'Viewport 390', value: 'width=390' },
        { type: 'contains', label: 'Обёртка phone', value: 'class="phone"' },
        { type: 'contains', label: 'Верхняя панель', value: 'class="topbar"' },
        { type: 'contains', label: 'Контент', value: 'class="content"' },
        { type: 'contains', label: 'Карточка', value: 'class="card"' },
        { type: 'contains', label: 'Нижнее меню', value: 'class="tabbar"' },
        { type: 'contains', label: 'Подключён style.css', value: 'style.css' },
      ],
    },
    position: 13,
  },
  {
    slug: 'project-checklist',
    module_num: '08',
    lesson_slug: 'exam-rehearsal',
    title: 'Чек-лист сдачи проекта',
    level: 'практика',
    minutes: 20,
    goal: 'Собрать собственный чек-лист проверки проекта перед сдачей.',
    brief: 'Это не тест, а рабочий документ. Напишите минимум 8 пунктов, которые проверите за 20 минут до конца экзамена: запуск с нуля, права, мобильный макет, коммит. Чем конкретнее команда — тем полезнее.',
    steps: [
      'Начните с запуска: migrate:fresh --seed.',
      'Добавьте storage:link и проверку загрузки файла.',
      'Добавьте проверку 403 на чужие заявки.',
      'Добавьте мобильный макет 390 и отсутствие прокрутки.',
      'Закончите коммитом и push.',
    ],
    starter_code: String.raw`- 
- 
- `,
    language: 'text',
    hints: [
      'Пишите команды и ожидаемый результат, а не «проверить всё».',
      'Хороший пункт: «php artisan migrate:fresh --seed — база создаётся без ошибок».',
      'Добавьте и дизайн, и безопасность, и Git.',
    ],
    solution: String.raw`- php artisan migrate:fresh --seed — база создаётся без ошибок.
- Админ входит логином и паролем из задания.
- Пароль в базе хранится хешем, не открытым текстом.
- Пользователь видит только свои заявки; чужая заявка даёт 403.
- /admin закрыт для студента и открыт для администратора.
- Статус меняется через POST и сохраняется после F5.
- Отзыв доступен только после статуса «Завершено».
- php artisan storage:link выполнен, картинка открывается.
- Мобильный макет 390×844: отдельные HTML-файлы.
- На 390, 768 и 1440 px нет горизонтальной прокрутки.
- В консоли нет ошибок, CDN-ссылок нет.
- Финальный коммит и push в репозиторий.`,
    checks: {
      checks: [
        { type: 'min_length', label: 'Достаточно пунктов', value: 200 },
        { type: 'contains', label: 'Запуск с нуля', value: 'migrate:fresh --seed' },
        { type: 'contains', label: 'Симлинк storage', value: 'storage:link' },
        { type: 'contains', label: 'Проверка 403', value: '403' },
        { type: 'contains', label: 'Мобильный размер', value: '390' },
        { type: 'contains', label: 'Git', value: 'коммит' },
      ],
    },
    position: 14,
  },
];

fs.mkdirSync('database/seeders/data', { recursive: true });
fs.writeFileSync('database/seeders/data/labs.json', JSON.stringify({ labs }, null, 2), 'utf8');
console.log(`labs: ${labs.length}`);
