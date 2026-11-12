window.COURSE_MODULES = window.COURSE_MODULES || [];
window.COURSE_MODULES.push({
  id: 'm2',
  num: '02',
  title: 'PHP-минимум',
  lead: 'Синтаксис, формы, сессии, PDO и файлы — ровно то, что Laravel прячет под капотом.',
  lessons: [
    {
      id: 'php-syntax',
      title: 'PHP за один вечер',
      minutes: 22,
      level: 'база',
      tags: ['php'],
      goal: 'Читать и писать простые PHP-скрипты: переменные, условия, циклы, массивы, функции.',
      blocks: [
        { t: 'p', v: 'PHP выполняется на сервере и отдаёт браузеру готовый HTML. Файл начинается с `<?php` и может содержать HTML вокруг. На экзамене вы будете писать в основном внутри Laravel, но понимать базовый PHP обязательно: иначе Blade и Eloquent останутся магией.' },
        { t: 'h', v: 'Переменные и вывод' },
        { t: 'code', lang: 'php', file: 'hello.php', v: `<?php
declare(strict_types=1);

$name = 'Мария';
$age = 21;
$price = 1500.50;
$isAdmin = false;

echo "Привет, {$name}! Тебе {$age} лет.\\n";
echo 'Стоимость: ' . number_format($price, 2, ',', ' ') . ' ₽' . PHP_EOL;

var_dump($isAdmin); // bool(false)` },
        { t: 'p', v: 'Точка с запятой обязательна. Конкатенация — точка, не плюс. В двойных кавычках переменные подставляются, в одинарных — нет. `declare(strict_types=1)` включает строгую типизацию аргументов и возвращаемых значений.' },
        { t: 'h', v: 'Условия' },
        { t: 'code', lang: 'php', file: 'conditions.php', v: `<?php

$status = 'assigned';

if ($status === 'new') {
    $label = 'Новая';
} elseif ($status === 'assigned') {
    $label = 'Мероприятие назначено';
} else {
    $label = 'Завершено';
}

// То же самое, но короче
$label = match ($status) {
    'new' => 'Новая',
    'assigned' => 'Мероприятие назначено',
    'completed' => 'Завершено',
    default => 'Неизвестно',
};

$isFinished = $status === 'completed';` },
        { t: 'p', v: 'Используйте `===`, а не `==`: строгое сравнение не превращает `\'0\'` в `false`. `match` появился в PHP 8 и сравнивает строго.' },
        { t: 'h', v: 'Циклы и массивы' },
        { t: 'code', lang: 'php', file: 'arrays.php', v: `<?php

$rooms = [
    ['name' => 'Аудитория', 'capacity' => 120],
    ['name' => 'Коворкинг', 'capacity' => 40],
    ['name' => 'Кинозал', 'capacity' => 90],
];

foreach ($rooms as $room) {
    echo $room['name'] . ' — ' . $room['capacity'] . ' мест' . PHP_EOL;
}

$statuses = ['new', 'assigned', 'completed'];
$count = count($statuses);
$hasNew = in_array('new', $statuses, true);

// Ассоциативный массив — как строка таблицы
$application = [
    'id' => 12,
    'room' => 'Коворкинг',
    'status' => 'new',
];

echo $application['room'];` },
        { t: 'p', v: 'Массивы в PHP бывают индексированные (`[0, 1, 2]`) и ассоциативные (`[\'key\' => \'value\']`). В Laravel почти все данные приходят как ассоциативные массивы или объекты моделей.' },
        { t: 'h', v: 'Функции' },
        { t: 'code', lang: 'php', file: 'functions.php', v: `<?php
declare(strict_types=1);

function formatPhone(string $digits): string
{
    $digits = preg_replace('/\\D/', '', $digits);

    if (strlen($digits) === 11) {
        return substr($digits, 0, 1) . '(' . substr($digits, 1, 3) . ')'
            . substr($digits, 4, 3) . '-' . substr($digits, 7, 2) . '-' . substr($digits, 9, 2);
    }

    return $digits;
}

function statusLabel(string $status): string
{
    return match ($status) {
        'new' => 'Новая',
        'assigned' => 'Назначено',
        'completed' => 'Завершено',
        default => $status,
    };
}

echo formatPhone('89991234567'); // 8(999)123-45-67
echo statusLabel('assigned');   // Назначено` },
        { t: 'h', v: 'Суперглобальные переменные' },
        { t: 'table', head: ['Переменная', 'Что внутри'], rows: [
          ['`$_GET`', 'Параметры из адресной строки'],
          ['`$_POST`', 'Данные POST-формы'],
          ['`$_FILES`', 'Загруженные файлы'],
          ['`$_SESSION`', 'Данные текущей сессии'],
          ['`$_SERVER`', 'Метод, URL, заголовки, IP']
        ] },
        { t: 'p', v: 'В Laravel к ним обращаются через `$request`, но знать, откуда берутся данные, полезно: при отладке вы всё равно увидите `$_POST` в дампах.' },
        { t: 'h', v: 'Подключение файлов' },
        { t: 'code', lang: 'php', file: 'index.php', v: `<?php

require __DIR__ . '/functions.php';

$title = 'Главная';
include __DIR__ . '/templates/header.php';` },
        { t: 'p', v: '`require` падает, если файл не найден, `include` — предупреждает. В Laravel этим занимается автозагрузка Composer, руками ничего подключать не нужно.' },
        { t: 'note', v: 'Не учите PHP «целиком». Для экзамена достаточно: переменные, условия, циклы, массивы, функции, работа с формой и базой. Остальное — по мере надобности.' },
        { t: 'h', v: 'Как запускать примеры' },
        { t: 'code', lang: 'bash', file: 'терминал', v: 'php hello.php\nphp -S 127.0.0.1:8080   # встроенный сервер для папки' },
        { t: 'p', v: 'Встроенный сервер отдаёт файлы из текущей папки. Создайте `index.php`, запустите команду и откройте `http://127.0.0.1:8080`.' }
      ],
      practice: [
        'Создайте `hello.php`, который выводит ваше имя и текущую дату.',
        'Напишите функцию, которая превращает статус в русскую подпись через `match`.',
        'Создайте массив из трёх заявок и выведите их циклом.',
        'Запустите файл через `php hello.php` и через встроенный сервер.'
      ],
      quiz: [
        { q: 'Чем `===` отличается от `==`?', options: ['Ничем', 'Строгим сравнением без приведения типов', 'Сравнивает только числа'], answer: 1, explain: '`==` приводит типы, `===` сравнивает значение и тип.' },
        { q: 'Что выведет `echo "Цена: $price";`, если `$price = 100`?', options: ['Цена: $price', 'Цена: 100', 'Ошибку'], answer: 1, explain: 'В двойных кавычках переменные интерполируются.' },
        { q: 'Какая функция безопасно экранирует HTML при выводе?', options: ['htmlspecialchars', 'trim', 'strlen'], answer: 0, explain: 'Она превращает < > & в сущности и не даёт выполнить чужой HTML/JS.' }
      ]
    },
    {
      id: 'php-forms',
      title: 'Формы, валидация и вывод без дыр',
      minutes: 24,
      level: 'база',
      tags: ['php', 'формы', 'безопасность'],
      goal: 'Обработать POST-форму, проверить данные, вернуть ошибки и сохранить введённое без XSS.',
      blocks: [
        { t: 'p', v: 'Схема обработки формы одинаковая в любом фреймворке: принять данные → проверить → если ошибки, вернуть форму с сообщениями → если нет, сохранить и перенаправить. Laravel автоматизирует это, но принцип тот же.' },
        { t: 'h', v: 'Приём данных' },
        { t: 'code', lang: 'php', file: 'register.php', v: `<?php

$fio = trim($_POST['fio'] ?? '');
$login = trim($_POST['login'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';` },
        { t: 'p', v: '`??` возвращает значение по умолчанию, если ключа нет. Это защищает от предупреждений, когда поле не пришло. `trim` убирает пробелы по краям — иначе логин `\' user \'` и `\'user\'` будут разными.' },
        { t: 'h', v: 'Проверки' },
        { t: 'code', lang: 'php', file: 'register.php', v: `<?php

$errors = [];

if ($fio === '') {
    $errors['fio'] = 'ФИО обязательно для заполнения.';
} elseif (!preg_match('/^[А-ЯЁа-яё\\s-]+$/u', $fio)) {
    $errors['fio'] = 'ФИО должно содержать только кириллицу, пробелы и дефис.';
}

if ($login === '') {
    $errors['login'] = 'Логин обязателен.';
} elseif (!preg_match('/^[a-zA-Z0-9]{6,}$/', $login)) {
    $errors['login'] = 'Логин: латинские буквы и цифры, не менее 6 символов.';
}

if ($phone === '') {
    $errors['phone'] = 'Телефон обязателен.';
} elseif (!preg_match('/^8\\(\\d{3}\\)\\d{3}-\\d{2}-\\d{2}$/', $phone)) {
    $errors['phone'] = 'Телефон в формате 8(XXX)XXX-XX-XX.';
}

if ($email === '') {
    $errors['email'] = 'Email обязателен.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Введите корректный email.';
}

if (mb_strlen($password) < 8) {
    $errors['password'] = 'Пароль должен быть не менее 8 символов.';
}

if ($errors) {
    // Вернуть форму с ошибками и старыми значениями
    $_SESSION['errors'] = $errors;
    $_SESSION['old'] = compact('fio', 'login', 'phone', 'email');
    header('Location: /register.php');
    exit;
}` },
        { t: 'p', v: '`preg_match` с флагом `/u` корректно работает с кириллицей. `mb_strlen` считает символы, а не байты: пароль из восьми кириллических букв — это 16 байт, и `strlen` ошибётся.' },
        { t: 'h', v: 'Вывод в HTML' },
        { t: 'code', lang: 'php', file: 'register.php', v: `<?php
$old = $_SESSION['old'] ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);
?>

<form method="POST" action="/register.php">
    <label for="fio">ФИО</label>
    <input type="text" id="fio" name="fio"
           value="<?= htmlspecialchars($old['fio'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <?php if (isset($errors['fio'])): ?>
        <span class="error"><?= htmlspecialchars($errors['fio'], ENT_QUOTES, 'UTF-8') ?></span>
    <?php endif; ?>

    <button type="submit">Создать пользователя</button>
</form>` },
        { t: 'warn', label: 'XSS', v: 'Никогда не выводите пользовательские данные без `htmlspecialchars`. Строка `<script>alert(1)</script>` в поле «ФИО» выполнится в браузере другого пользователя. В Blade `{{ }}` экранирует автоматически, а `{!! !!}` — нет.' },
        { t: 'h', v: 'Паттерн PRG' },
        { t: 'p', v: 'После успешного POST всегда делайте redirect (Post/Redirect/Get). Иначе F5 отправит форму повторно и создаст дубль заявки. Сообщение об успехе храните во flash-сессии — оно покажется один раз.' },
        { t: 'code', lang: 'php', file: 'обработчик', v: `$_SESSION['success'] = 'Пользователь создан.';
header('Location: /login.php');
exit;` },
        { t: 'h', v: 'Сохранение пароля' },
        { t: 'code', lang: 'php', file: 'register.php', v: `$hash = password_hash($password, PASSWORD_DEFAULT);
// В базе: $2y$12$... — необратимо

// Проверка при входе
if (password_verify($inputPassword, $user['password'])) {
    // вход разрешён
}` },
        { t: 'p', v: '`md5` и `sha1` для паролей использовать нельзя: они быстро подбираются. `password_hash` сам добавляет соль и медленно считает хеш. В Laravel то же самое делает `Hash::make`.' },
        { t: 'h', v: 'Порядок действий' },
        { t: 'steps', v: [
          { b: 'Принять и обрезать', t: '`trim`, значения по умолчанию через `??`.' },
          { b: 'Проверить каждое поле', t: 'Обязательность, формат, длина, уникальность.' },
          { b: 'Собрать ошибки', t: 'Ассоциативный массив «поле → сообщение».' },
          { b: 'Вернуть форму', t: 'Сохранить старые значения и ошибки, сделать redirect.' },
          { b: 'Сохранить и перенаправить', t: 'Только если ошибок нет. Хешировать пароль.' }
        ] }
      ],
      practice: [
        'Сделайте форму регистрации на чистом PHP с проверкой всех полей.',
        'Отправьте `<script>alert(1)</script>` в ФИО и убедитесь, что оно выводится текстом.',
        'Сделайте так, чтобы после ошибки поля не очищались.',
        'Сохраните пароль через `password_hash` и проверьте вход через `password_verify`.'
      ],
      quiz: [
        { q: 'Почему после успешного POST нужен redirect?', options: ['Чтобы скрыть данные', 'Чтобы F5 не создавал повторную запись', 'Чтобы работал CSRF'], answer: 1, explain: 'PRG защищает от повторной отправки формы.' },
        { q: 'Что делает `htmlspecialchars`?', options: ['Удаляет теги', 'Экранирует HTML-сущности', 'Проверяет email'], answer: 1, explain: 'Он не удаляет, а делает текст безопасным для вывода.' },
        { q: 'Какая функция подходит для хранения пароля?', options: ['md5', 'password_hash', 'base64_encode'], answer: 1, explain: 'Только медленный адаптивный хеш с солью.' }
      ]
    },
    {
      id: 'php-sessions',
      title: 'Сессии, вход и flash-сообщения',
      minutes: 20,
      level: 'база',
      tags: ['php', 'авторизация'],
      goal: 'Сделать вход и выход на чистом PHP, понять, как сервер узнаёт пользователя между запросами.',
      blocks: [
        { t: 'p', v: 'HTTP не помнит предыдущий запрос. Чтобы после входа сервер узнавал пользователя, в cookie кладётся идентификатор сессии, а данные лежат на сервере. В Laravel сессия настраивается сама, но механизм тот же.' },
        { t: 'h', v: 'Старт сессии' },
        { t: 'code', lang: 'php', file: 'bootstrap.php', v: `<?php
session_start();

// Проверка входа в защищённом месте
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}` },
        { t: 'p', v: '`session_start()` должен вызываться до любого вывода в браузер. Если перед ним есть пробел или HTML, появится warning «headers already sent».' },
        { t: 'h', v: 'Вход' },
        { t: 'code', lang: 'php', file: 'login.php', v: `<?php
session_start();

$login = trim($_POST['login'] ?? '');
$password = $_POST['password'] ?? '';

$stmt = $pdo->prepare('SELECT * FROM users WHERE login = ? LIMIT 1');
$stmt->execute([$login]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    session_regenerate_id(true);       // защита от фиксации сессии
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];

    header('Location: /profile.php');
    exit;
}

$_SESSION['error'] = 'Неверный логин или пароль.';
header('Location: /login.php');
exit;` },
        { t: 'h', v: 'Выход' },
        { t: 'code', lang: 'php', file: 'logout.php', v: `<?php
session_start();
$_SESSION = [];
session_destroy();

header('Location: /login.php');
exit;` },
        { t: 'p', v: 'Просто удалить `user_id` недостаточно: лучше очистить всю сессию и уничтожить cookie сессии. В Laravel `Auth::logout()` + `$request->session()->invalidate()` делают это правильно.' },
        { t: 'h', v: 'Flash-сообщения' },
        { t: 'code', lang: 'php', file: 'flash', v: `// Перед redirect
$_SESSION['success'] = 'Заявка отправлена.';

// На следующей странице
$success = $_SESSION['success'] ?? null;
unset($_SESSION['success']);
?>

<?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>` },
        { t: 'p', v: 'Flash живёт один показ. Если не удалять после вывода, сообщение будет висеть после перезагрузки и раздражать.' },
        { t: 'h', v: 'Роли' },
        { t: 'code', lang: 'php', file: 'admin.php', v: `<?php
session_start();

if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Доступ запрещён.');
}` },
        { t: 'p', v: 'Проверять роль нужно на каждом защищённом действии, а не только на входе. Пользователь может открыть URL напрямую. На экзамене это отдельный критерий: «разграничение прав доступа».' },
        { t: 'h', v: 'Что хранить в сессии' },
        { t: 'table', head: ['Можно', 'Нельзя'], rows: [
          ['ID пользователя', 'Пароль даже в хеше'],
          ['Роль', 'Полные данные карты'],
          ['CSRF-токен', 'Объекты моделей целиком'],
          ['Flash-сообщения', 'Секретные ключи приложения']
        ] },
        { t: 'note', v: 'На экзамене не обязательно писать сессии руками — Laravel это делает лучше. Но понимание нужно, чтобы осознанно отвечать на вопросы и не ломать `Auth::attempt`.' },
        { t: 'h', v: 'Сравнение с Laravel' },
        { t: 'table', head: ['Чистый PHP', 'Laravel'], rows: [
          ['`$_SESSION[\'user_id\']`', '`Auth::id()`'],
          ['`password_verify`', '`Auth::attempt` (сам вызывает проверку)'],
          ['`session_regenerate_id`', '`$request->session()->regenerate()`'],
          ['`header(\'Location: ...\')`', '`redirect()->route(...)`'],
          ['Flash в `$_SESSION`', '`->with(\'success\', ...)`']
        ] }
      ],
      practice: [
        'Сделайте страницу входа: форма, проверка, `$_SESSION[\'user_id\']`.',
        'Закройте `/profile.php` проверкой входа и проверьте прямой заход без сессии.',
        'Выведите flash-сообщение после входа и убедитесь, что оно исчезает после F5.',
        'Проверьте выход: после `logout` защищённая страница снова требует вход.'
      ],
      quiz: [
        { q: 'Зачем `session_regenerate_id(true)` при входе?', options: ['Чтобы ускорить PHP', 'Чтобы защититься от фиксации сессии', 'Чтобы сохранить пароль'], answer: 1, explain: 'Новый идентификатор не даёт злоумышленнику использовать заранее известный.' },
        { q: 'Где хранится flash-сообщение?', options: ['В сессии до первого показа', 'В базе навсегда', 'В cookie открытым текстом'], answer: 0, explain: 'После вывода его удаляют.' },
        { q: 'Почему проверки роли недостаточно только на странице входа?', options: ['Пользователь может открыть URL напрямую', 'Роль меняется сама', 'Laravel не поддерживает роли'], answer: 0, explain: 'Каждое защищённое действие должно проверять права.' }
      ]
    },
    {
      id: 'php-db',
      title: 'PDO и SQL: данные без ORM',
      minutes: 26,
      level: 'база',
      tags: ['php', 'sql', 'pdo'],
      goal: 'Подключиться к базе, сделать CRUD через prepared statements и понять, откуда берутся SQL-инъекции.',
      blocks: [
        { t: 'p', v: 'PDO — универсальный способ работать с базой из PHP. Eloquent внутри использует похожий слой. Если вы понимаете PDO, вы понимаете, что происходит, когда Laravel пишет `where(\'status\', \'new\')`.' },
        { t: 'h', v: 'Подключение' },
        { t: 'code', lang: 'php', file: 'db.php', v: `<?php
declare(strict_types=1);

$dsn = 'sqlite:' . __DIR__ . '/database.sqlite';

$pdo = new PDO($dsn, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);` },
        { t: 'p', v: '`ERRMODE_EXCEPTION` превращает ошибки SQL в исключения, которые видно в логах. `FETCH_ASSOC` возвращает массивы с именами полей. Без этого PDO молча вернёт `false`, и вы будете долго искать проблему.' },
        { t: 'h', v: 'Схема' },
        { t: 'code', lang: 'sql', file: 'schema.sql', v: `CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    fio TEXT NOT NULL,
    login TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'user',
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE applications (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    room_id INTEGER NOT NULL,
    start_date TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'new',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);` },
        { t: 'h', v: 'Создание записи' },
        { t: 'code', lang: 'php', file: 'create.php', v: `<?php
$sql = 'INSERT INTO applications (user_id, room_id, start_date, status)
        VALUES (:user_id, :room_id, :start_date, :status)';

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':user_id' => $userId,
    ':room_id' => $roomId,
    ':start_date' => $startDate,
    ':status' => 'new',
]);

$id = (int) $pdo->lastInsertId();` },
        { t: 'p', v: 'Именованные параметры читабельнее вопросительных знаков. PDO сам экранирует значения — но только значения, не имена таблиц и колонок.' },
        { t: 'h', v: 'Чтение' },
        { t: 'code', lang: 'php', file: 'read.php', v: `<?php
// Все заявки пользователя
$stmt = $pdo->prepare('SELECT * FROM applications WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$userId]);
$applications = $stmt->fetchAll();

// Одна запись
$stmt = $pdo->prepare('SELECT * FROM users WHERE login = ? LIMIT 1');
$stmt->execute([$login]);
$user = $stmt->fetch();

if (!$user) {
    exit('Пользователь не найден');
}` },
        { t: 'h', v: 'Обновление и удаление' },
        { t: 'code', lang: 'php', file: 'update.php', v: `<?php
$stmt = $pdo->prepare('UPDATE applications SET status = ? WHERE id = ?');
$stmt->execute(['completed', $applicationId]);

$stmt = $pdo->prepare('DELETE FROM applications WHERE id = ?');
$stmt->execute([$applicationId]);` },
        { t: 'h', v: 'SQL-инъекция' },
        { t: 'code', lang: 'php', file: 'плохо', v: `// НИКОГДА так не делайте
$sql = "SELECT * FROM users WHERE login = '{$_POST['login']}'";
$pdo->query($sql);` },
        { t: 'p', v: 'Если ввести `\' OR 1=1 --`, запрос вернёт всех пользователей. Prepared statements решают проблему: значение передаётся отдельно от SQL-кода и не может изменить структуру запроса.' },
        { t: 'warn', label: 'Имена таблиц', v: 'Нельзя передать имя таблицы параметром. Если оно приходит от пользователя, сверяйте его с белым списком: `in_array($table, [\'posts\', \'categories\'], true)`.' },
        { t: 'h', v: 'Транзакции' },
        { t: 'code', lang: 'php', file: 'transaction', v: `$pdo->beginTransaction();

try {
    $pdo->prepare('INSERT INTO applications (user_id, room_id, start_date) VALUES (?, ?, ?)')
        ->execute([$userId, $roomId, $date]);

    $pdo->prepare('UPDATE rooms SET booked = 1 WHERE id = ?')
        ->execute([$roomId]);

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    throw $e;
}` },
        { t: 'p', v: 'Транзакция нужна, когда меняются две таблицы и «половина операции» недопустима. На экзамене чаще достаточно одной вставки, но знать механизм полезно.' },
        { t: 'h', v: 'Как это выглядит в Laravel' },
        { t: 'table', head: ['PDO', 'Laravel'], rows: [
          ['`prepare` + `execute`', '`Model::create([...])`'],
          ['`SELECT * WHERE user_id = ?`', '`Model::where(\'user_id\', $id)->get()`'],
          ['`UPDATE ... SET status = ?`', '`$model->update([\'status\' => ...])`'],
          ['`DELETE FROM ...`', '`$model->delete()`'],
          ['Транзакция', '`DB::transaction(fn () => ...)`']
        ] },
        { t: 'note', v: 'На экзамене не нужно писать SQL руками, если вы используете Eloquent. Но на вопрос «что такое SQL-инъекция и как от неё защититься» нужно отвечать уверенно.' }
      ],
      practice: [
        'Создайте SQLite-базу и таблицу `applications` через SQL.',
        'Вставьте три заявки через prepared statement.',
        'Выведите заявки пользователя с ID 1, отсортировав по дате.',
        'Попробуйте SQL-инъекцию в учебном запросе и затем исправьте его через параметр.'
      ],
      quiz: [
        { q: 'Что защищает от SQL-инъекции?', options: ['htmlspecialchars', 'Prepared statements с параметрами', 'trim'], answer: 1, explain: 'Значения передаются отдельно от SQL-кода.' },
        { q: 'Зачем `PDO::ATTR_ERRMODE => ERRMODE_EXCEPTION`?', options: ['Чтобы ошибки не были видны', 'Чтобы ошибка SQL выбрасывала исключение', 'Чтобы ускорить запросы'], answer: 1, explain: 'Иначе PDO может молча вернуть false.' },
        { q: 'Можно ли передать имя таблицы через `?` в подготовленном запросе?', options: ['Да', 'Нет, только значения', 'Только в MySQL'], answer: 1, explain: 'Параметры — это значения. Имена проверяют по белому списку.' }
      ]
    },
    {
      id: 'php-files',
      title: 'Загрузка файлов без дыр',
      minutes: 18,
      level: 'средний',
      tags: ['php', 'файлы', 'безопасность'],
      goal: 'Принять изображение, проверить его, сохранить с уникальным именем и показать на странице.',
      blocks: [
        { t: 'p', v: 'Загрузка изображения — почти обязательный пункт задания: картинка помещения, автомобиля, поста. Здесь легко ошибиться: сохранить исполняемый файл, перезаписать чужой или потерять расширение.' },
        { t: 'h', v: 'Что приходит в `$_FILES`' },
        { t: 'code', lang: 'php', file: 'dump', v: `$_FILES['image'] = [
    'name' => 'room.jpg',        // имя от пользователя — не доверять
    'type' => 'image/jpeg',      // из браузера — не доверять
    'tmp_name' => 'C:\\tmp\\php8A3F.tmp',
    'error' => 0,                // 0 = успех
    'size' => 184320,            // байты
];` },
        { t: 'h', v: 'Проверка' },
        { t: 'code', lang: 'php', file: 'upload.php', v: `<?php

$file = $_FILES['image'] ?? null;

if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
    exit('Файл не загружен.');
}

if ($file['size'] > 2 * 1024 * 1024) {
    exit('Файл больше 2 МБ.');
}

$allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

$mime = mime_content_type($file['tmp_name']);

if (!isset($allowed[$mime])) {
    exit('Можно загружать только JPG, PNG и WebP.');
}

$extension = $allowed[$mime];
$name = bin2hex(random_bytes(16)) . '.' . $extension;
$destination = __DIR__ . '/uploads/' . $name;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    exit('Не удалось сохранить файл.');
}` },
        { t: 'p', v: 'Проверяйте MIME по содержимому (`mime_content_type`), а не по расширению или `$_FILES[\'type\']`. Файл `virus.php.jpg` с картинкой внутри пройдёт по расширению, но не должен исполняться. Имя генерируйте сами: `random_bytes` исключает коллизии и подстановку `../`.' },
        { t: 'h', v: 'Папка загрузок' },
        { t: 'code', lang: 'php', file: 'upload.php', v: `$dir = __DIR__ . '/uploads';

if (!is_dir($dir)) {
    mkdir($dir, 0775, true);
}

// В .htaccess или конфиге сервера запретить выполнение PHP в uploads:
// <FilesMatch "\\.(php|phtml|phar)$">
//     Require all denied
// </FilesMatch>` },
        { t: 'warn', label: 'Никогда', v: 'Не сохраняйте файл с именем, которое прислал пользователь. `../../index.php` или `shell.php` — реальные атаки, а не страшилки.' },
        { t: 'h', v: 'Удаление старого файла' },
        { t: 'code', lang: 'php', file: 'update.php', v: `if ($oldImage && is_file(__DIR__ . '/uploads/' . $oldImage)) {
    unlink(__DIR__ . '/uploads/' . $oldImage);
}` },
        { t: 'p', v: 'При замене изображения удаляйте старое, иначе папка распухнет. Проверяйте, что путь ведёт внутрь вашей папки: `realpath` и сравнение префикса.' },
        { t: 'h', v: 'Сжатие изображения' },
        { t: 'code', lang: 'php', file: 'resize.php', v: `<?php

function resizeImage(string $source, string $target, int $maxWidth = 1200): void
{
    [$width, $height, $type] = getimagesize($source);

    if ($width <= $maxWidth) {
        copy($source, $target);
        return;
    }

    $ratio = $maxWidth / $width;
    $newWidth = $maxWidth;
    $newHeight = (int) round($height * $ratio);

    $src = match ($type) {
        IMAGETYPE_JPEG => imagecreatefromjpeg($source),
        IMAGETYPE_PNG => imagecreatefrompng($source),
        IMAGETYPE_WEBP => imagecreatefromwebp($source),
        default => throw new RuntimeException('Неподдерживаемый формат'),
    };

    $dst = imagecreatetruecolor($newWidth, $newHeight);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    imagejpeg($dst, $target, 82);
    imagedestroy($src);
    imagedestroy($dst);
}` },
        { t: 'p', v: 'На экзамене требование «оптимизировать медиафайлы» можно выполнить заранее: уменьшить картинки до 1200 px по ширине и пересохранить JPEG с качеством 80–85. Это уменьшает вес в разы и не требует кода в приложении.' },
        { t: 'h', v: 'В Laravel' },
        { t: 'code', lang: 'php', file: 'PostController.php', v: `$path = $request->file('image')->store('posts', 'public');
// storage/app/public/posts/xxxx.jpg

// В модели/шаблоне:
// asset('storage/' . $post->image)

// Один раз в терминале:
// php artisan storage:link` },
        { t: 'p', v: 'Laravel сам генерирует имя и проверяет файл, если указать правила `image|mimes:jpg,png,webp|max:2048`. Не забудьте `storage:link`, иначе браузер получит 404.' }
      ],
      practice: [
        'Сделайте форму с `enctype="multipart/form-data"` и загрузите JPEG.',
        'Ограничьте размер 2 МБ и тип JPG/PNG/WebP.',
        'Сохраните файл под случайным именем и выведите его на странице.',
        'Попробуйте загрузить `.php`-файл и убедитесь, что проверка его отклоняет.'
      ],
      quiz: [
        { q: 'По чему проверять тип файла?', options: ['По расширению из имени', 'По MIME, определённому по содержимому', 'По `$_FILES[\'type\']`'], answer: 1, explain: 'Расширение и тип от браузера подделываются.' },
        { q: 'Зачем генерировать имя файла?', options: ['Для красоты', 'Чтобы избежать коллизий и path traversal', 'Чтобы файл быстрее открывался'], answer: 1, explain: 'Пользовательское имя может содержать `../` или перезаписать чужой файл.' },
        { q: 'Что делает `php artisan storage:link`?', options: ['Создаёт символическую ссылку public/storage → storage/app/public', 'Сжимает картинки', 'Удаляет старые файлы'], answer: 0, explain: 'Без ссылки загруженные файлы недоступны из браузера.' }
      ]
    }
  ]
});
