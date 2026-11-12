window.COURSE_MODULES = window.COURSE_MODULES || [];
window.COURSE_MODULES.push({
  id: 'm1',
  num: '01',
  title: 'Веб-минимум',
  lead: 'Как браузер получает страницу, как устроен HTML и CSS, как не утонуть в Bootstrap.',
  lessons: [
    {
      id: 'http-html',
      title: 'Как браузер получает страницу',
      minutes: 14,
      level: 'база',
      tags: ['html', 'http'],
      goal: 'Понять путь от адресной строки до готовой страницы и научиться читать базовую HTML-разметку.',
      blocks: [
        { t: 'p', v: 'Вы вводите адрес, браузер отправляет HTTP-запрос на сервер, сервер отвечает HTML-документом. Браузер читает разметку, подгружает CSS и картинки, выполняет JavaScript. Всё, что делает Laravel, — это собирает HTML на сервере и отдаёт его браузеру.' },
        { t: 'h', v: 'Из чего состоит адрес' },
        { t: 'code', lang: 'text', file: 'URL', v: 'http://127.0.0.1:8000/application?status=new\n│      │           │    │            │\n│      │           │    │            └─ query-параметры\n│      │           │    └────────────── путь\n│      │           └─────────────────── порт\n│      └─────────────────────────────── хост (localhost)\n└────────────────────────────────────── протокол' },
        { t: 'p', v: '`127.0.0.1` и `localhost` — это ваш компьютер. Порт отделяет одно приложение от другого: Laravel по умолчанию слушает 8000, MySQL — 3306. Если порт занят, сервер не запустится.' },
        { t: 'h', v: 'GET и POST' },
        { t: 'table', head: ['', 'GET', 'POST'], rows: [
          ['Когда', 'Открыть страницу, поиск, фильтр', 'Отправить форму, создать или изменить данные'],
          ['Где данные', 'В URL, после `?`', 'В теле запроса'],
          ['Видно в адресной строке', 'Да', 'Нет'],
          ['Повторная отправка', 'Безопасна', 'Браузер предупредит о повторной отправке']
        ] },
        { t: 'p', v: 'Правило простое: **GET — показать, POST — изменить**. В Laravel маршрут объявляется методом: `Route::get()` или `Route::post()`. Если форма отправлена методом GET, а маршрут ждёт POST — получите 405.' },
        { t: 'h', v: 'Коды ответа, которые надо узнавать' },
        { t: 'ul', v: [
          '`200` — всё хорошо.',
          '`302` — перенаправление. Так работает вход: POST → редирект на кабинет.',
          '`403` — запрещено. Например, чужой профиль.',
          '`404` — не найдено. Опечатка в URL или запись удалена.',
          '`419` — истекла сессия или нет CSRF-токена.',
          '`500` — ошибка в PHP. Смотрите `storage/logs/laravel.log`.'
        ] },
        { t: 'h', v: 'Минимальная страница' },
        { t: 'code', lang: 'html', file: 'resources/views/example.blade.php', v: `<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Заявки — Конференции.РФ</title>
    <link rel="stylesheet" href="/css/app.css">
</head>
<body>
    <header class="site-header">
        <a href="/" class="logo">Конференции.РФ</a>
        <nav aria-label="Основное меню">
            <a href="/profile">Кабинет</a>
            <a href="/logout">Выйти</a>
        </nav>
    </header>

    <main class="container">
        <h1>Мои заявки</h1>

        <article class="card">
            <h2>Коворкинг-зона «Вектор»</h2>
            <p>Статус: <strong>Мероприятие назначено</strong></p>
            <p>Дата начала: 12 ноября 2026</p>
        </article>
    </main>

    <footer class="site-footer">Конференции.РФ © 2027</footer>
</body>
</html>` },
        { t: 'p', v: 'Обратите внимание на семантику: `header`, `nav`, `main`, `article`, `footer`. Это не украшение — по этим тегам работают скринридеры и клавиатурная навигация. Эксперт по критериям доступности смотрит именно на структуру.' },
        { t: 'note', v: '`<meta name="viewport">` обязателен. Без него мобильный браузер отрендерит страницу как десктопную и уменьшит её — адаптивность не заработает, сколько бы медиазапросов вы ни написали.' },
        { t: 'h', v: 'Где это живёт в Laravel' },
        { t: 'p', v: 'Файлы `resources/views/*.blade.php` — это HTML с вставками PHP. Blade компилируется в обычный PHP и кешируется. Внутри можно писать обычный HTML, а динамику добавлять через `{{ $переменная }}`.' },
        { t: 'code', lang: 'php', file: 'routes/web.php', v: `<?php

use Illuminate\\Support\\Facades\\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/profile', function () {
    return view('profile', [
        'name' => 'Иванова Мария',
    ]);
});` },
        { t: 'p', v: 'Пока не думайте о моделях и базе. Сначала добейтесь, чтобы маршрут открывал шаблон и в нём выводились переданные данные.' }
      ],
      practice: [
        'Откройте любой сайт, нажмите F12 → Network и найдите первый HTML-документ.',
        'Определите, каким методом отправляется поиск на этом сайте: GET или POST.',
        'Создайте в Laravel маршрут `/hello`, который возвращает view с вашим именем.',
        'Сломайте URL (добавьте лишний символ) и посмотрите на страницу 404.'
      ],
      quiz: [
        { q: 'Форма входа отправляет логин и пароль. Какой метод правильный?', options: ['GET', 'POST', 'DELETE'], answer: 1, explain: 'Данные изменяют состояние сессии и не должны попадать в URL и историю браузера.' },
        { q: 'Что означает ответ 419 в Laravel?', options: ['Страница не найдена', 'Истекла сессия или отсутствует CSRF-токен', 'Нет прав доступа'], answer: 1, explain: '419 — Page Expired. Проверьте @csrf в форме и настройки сессии.' },
        { q: 'Зачем нужен meta viewport?', options: ['Для SEO', 'Чтобы мобильный браузер использовал реальную ширину экрана', 'Чтобы работали картинки'], answer: 1, explain: 'Без него медиазапросы не дадут ожидаемого результата на телефоне.' }
      ]
    },
    {
      id: 'forms-html',
      title: 'Формы, которые не теряют данные',
      minutes: 16,
      level: 'база',
      tags: ['html', 'формы'],
      goal: 'Собрать форму регистрации так, чтобы браузер отправлял понятные данные, а пользователь понимал, где ошибка.',
      blocks: [
        { t: 'p', v: 'Форма — главный инструмент экзамена. Через неё создаётся заявка, регистрируется пользователь, меняется статус. Если вы неправильно назовёте поле, PHP его не увидит, и дальше всё развалится.' },
        { t: 'h', v: 'Каркас формы' },
        { t: 'code', lang: 'html', file: 'resources/views/register.blade.php', v: `<form method="POST" action="/register" novalidate>
    @csrf

    <div class="field">
        <label for="fio">ФИО</label>
        <input type="text" id="fio" name="fio"
               value="{{ old('fio') }}"
               placeholder="Иванов Иван Иванович"
               autocomplete="name" required>
        @error('fio')
            <span class="error">{{ $message }}</span>
        @enderror
    </div>

    <div class="field">
        <label for="login">Логин</label>
        <input type="text" id="login" name="login"
               value="{{ old('login') }}"
               placeholder="Латинские буквы и цифры, от 6 символов"
               autocomplete="username" required>
        @error('login')
            <span class="error">{{ $message }}</span>
        @enderror
    </div>

    <div class="field">
        <label for="phone">Телефон</label>
        <input type="tel" id="phone" name="phone"
               value="{{ old('phone') }}"
               placeholder="8(999)123-45-67"
               autocomplete="tel" required>
        @error('phone')
            <span class="error">{{ $message }}</span>
        @enderror
    </div>

    <div class="field">
        <label for="email">Email</label>
        <input type="email" id="email" name="email"
               value="{{ old('email') }}"
               autocomplete="email" required>
        @error('email')
            <span class="error">{{ $message }}</span>
        @enderror
    </div>

    <div class="field">
        <label for="password">Пароль</label>
        <input type="password" id="password" name="password"
               autocomplete="new-password" minlength="8" required>
        @error('password')
            <span class="error">{{ $message }}</span>
        @enderror
    </div>

    <button type="submit" class="btn">Создать пользователя</button>
</form>` },
        { t: 'h', v: 'Четыре вещи, без которых форма не работает' },
        { t: 'ol', v: [
          '`method` и `action` — куда и каким методом отправлять.',
          '`name` у каждого поля — именно это имя придёт в `$request`. `id` нужен только для `label`.',
          '`@csrf` — защита от подделки запросов. Без него Laravel вернёт 419.',
          '`type` — `email`, `tel`, `password`, `date`, `number`. Браузер подскажет клавиатуру и проверит формат.'
        ] },
        { t: 'note', label: 'Почему `novalidate`', v: 'На экзамене часто просят показать ошибки валидации Laravel. Если оставить встроенную браузерную проверку, она перехватит отправку и вы не увидите свои сообщения. Уберите `novalidate`, если хотите проверку браузера, но тогда продумайте оба слоя.' },
        { t: 'h', v: 'select и textarea' },
        { t: 'code', lang: 'html', file: 'resources/views/application.blade.php', v: `<div class="field">
    <label for="room_id">Помещение</label>
    <select id="room_id" name="room_id" required>
        <option value="">Выберите помещение</option>
        @foreach ($rooms as $room)
            <option value="{{ $room->id }}"
                @selected(old('room_id') == $room->id)>
                {{ $room->name }} — до {{ $room->capacity }} мест
            </option>
        @endforeach
    </select>
    @error('room_id')
        <span class="error">{{ $message }}</span>
    @enderror
</div>

<div class="field">
    <label for="start_date">Дата начала</label>
    <input type="date" id="start_date" name="start_date"
           value="{{ old('start_date') }}"
           min="{{ now()->toDateString() }}" required>
    @error('start_date')
        <span class="error">{{ $message }}</span>
    @enderror
</div>

<div class="field">
    <label for="payment_type">Способ оплаты</label>
    <select id="payment_type" name="payment_type" required>
        <option value="in_person" @selected(old('payment_type') === 'in_person')>При очном посещении</option>
        <option value="sbp" @selected(old('payment_type') === 'sbp')>Переводом по СБП</option>
    </select>
</div>` },
        { t: 'p', v: '`@selected()` и `@checked()` — Blade-директивы Laravel 9+. Они добавляют атрибут, если условие истинно. Это чище, чем вручную писать `{{ old(...) ? \'selected\' : \'\' }}`.' },
        { t: 'h', v: 'Загрузка файла' },
        { t: 'code', lang: 'html', file: 'форма с файлом', v: `<form method="POST" action="/posts" enctype="multipart/form-data">
    @csrf
    <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
    <button type="submit">Сохранить</button>
</form>` },
        { t: 'p', v: 'Без `enctype="multipart/form-data"` файл не дойдёт до сервера. Это одна из самых частых причин «картинка не загружается», хотя код контроллера правильный.' },
        { t: 'h', v: 'Как читать данные на сервере' },
        { t: 'code', lang: 'php', file: 'app/Http/Controllers/AuthController.php', v: `$fio = $request->input('fio');
$login = $request->input('login');
$all = $request->all();

// Файл приходит отдельным объектом
if ($request->hasFile('image')) {
    $path = $request->file('image')->store('posts', 'public');
}` },
        { t: 'note', v: 'Имена полей пишите латиницей, в нижнем регистре, через подчёркивание: `start_date`, `payment_type`, `room_id`. Русские имена и пробелы создают проблемы при валидации и в JS.' },
        { t: 'h', v: 'Проверка перед отправкой' },
        { t: 'p', v: 'На экзамене полезно иметь простую JS-проверку, но она не заменяет серверную. Пользователь может отключить JavaScript. Обязательно валидируйте на сервере, а клиентскую проверку используйте как подсказку.' }
      ],
      practice: [
        'Свёрстайте форму регистрации с полями из примера и `label` у каждого поля.',
        'Отправьте форму методом GET и посмотрите, как данные выглядят в адресной строке.',
        'Поменяйте метод на POST без `@csrf` и получите ошибку 419.',
        'Добавьте `select` с тремя помещениями и проверьте, что выбранное значение сохраняется после ошибки.'
      ],
      quiz: [
        { q: 'Какое имя попадёт в `$request` у поля `<input id="phone" name="phone">`?', options: ['phone', 'id', 'input'], answer: 0, explain: 'Сервер получает данные по атрибуту name. id нужен для label и JS.' },
        { q: 'Что будет, если забыть `@csrf` в POST-форме?', options: ['Форма отправится дважды', 'Laravel вернёт 419', 'Поле name не передастся'], answer: 1, explain: 'CSRF-токен обязателен для POST/PUT/DELETE в веб-маршрутах.' },
        { q: 'Зачем `enctype="multipart/form-data"`?', options: ['Для загрузки файлов', 'Для паролей', 'Для CSRF'], answer: 0, explain: 'Только с этим типом кодирования файл передаётся на сервер.' }
      ]
    },
    {
      id: 'css-layout',
      title: 'CSS: сетка, флекс и адаптив',
      minutes: 20,
      level: 'база',
      tags: ['css', 'адаптив'],
      goal: 'Научиться собирать карточки и формы на flex/grid и ломать макет на нужную ширину без магии.',
      blocks: [
        { t: 'p', v: 'CSS на экзамене нужен не «чтобы красиво», а чтобы страница не разваливалась на 390 px и эксперт видел адаптивность. Достаточно уверенно владеть тремя вещами: блочная модель, flex, grid и медиазапросы.' },
        { t: 'h', v: 'Блочная модель' },
        { t: 'p', v: 'Каждый элемент — прямоугольник: содержимое, padding, border, margin. С `box-sizing: border-box` ширина включает padding и border, и это единственный адекватный вариант для интерфейсов.' },
        { t: 'code', lang: 'css', file: 'public/css/app.css', v: `*, *::before, *::after {
    box-sizing: border-box;
}

.card {
    padding: 20px;
    border: 1px solid #d9d5cb;
    background: #fff;
}

.card h2 {
    margin: 0 0 8px;
    font-size: 20px;
}` },
        { t: 'h', v: 'Flex: одна ось' },
        { t: 'p', v: 'Flex раскладывает элементы в строку или колонку. Используйте его для навигации, строки кнопок, карточки «иконка + текст».' },
        { t: 'code', lang: 'css', file: 'flex', v: `.topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}

.actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}` },
        { t: 'h', v: 'Grid: две оси' },
        { t: 'p', v: 'Grid нужен для сетки карточек и формы в две колонки. `repeat(auto-fit, minmax(240px, 1fr))` — самый полезный приём: карточки сами переносятся на новую строку.' },
        { t: 'code', lang: 'css', file: 'grid', v: `.rooms {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 16px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

@media (max-width: 640px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}` },
        { t: 'h', v: 'Медиазапросы' },
        { t: 'p', v: 'Сначала проектируйте мобильную версию, потом расширяйте. Два-три брейкпоинта достаточно: 640, 900, 1200. Не подгоняйте под конкретный iPhone — проверяйте 390, 768 и 1440.' },
        { t: 'code', lang: 'css', file: 'mobile-first', v: `.nav {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

@media (min-width: 768px) {
    .nav {
        flex-direction: row;
        gap: 24px;
    }
}` },
        { t: 'h', v: 'Типографика и читаемость' },
        { t: 'ul', v: [
          'Основной текст 16–18 px, межстрочный интервал 1.5–1.65.',
          'Ширина текстовой колонки 55–75 символов: `max-width: 65ch`.',
          'Не используйте больше двух шрифтов и трёх размеров заголовков.',
          'Контраст текста к фону не ниже 4.5:1. Серый на белом `#999` — уже плохо.',
          'Кнопки и ссылки должны иметь видимый `:focus-visible`.'
        ] },
        { t: 'h', v: 'Карточка заявки: полный пример' },
        { t: 'code', lang: 'html', file: 'resources/views/profile.blade.php', v: `<div class="application">
    <div class="application-head">
        <div>
            <h2>Коворкинг-зона «Вектор»</h2>
            <span class="status status--assigned">Мероприятие назначено</span>
        </div>
        <time datetime="{{ $application->created_at->toDateString() }}">
            №{{ $application->id }} от {{ $application->created_at->format('d.m.Y') }}
        </time>
    </div>

    <dl class="application-meta">
        <div>
            <dt>Дата начала</dt>
            <dd>{{ \\Carbon\\Carbon::parse($application->start_date)->format('d.m.Y') }}</dd>
        </div>
        <div>
            <dt>Оплата</dt>
            <dd>{{ $application->payment_label }}</dd>
        </div>
    </dl>
</div>` },
        { t: 'code', lang: 'css', file: 'public/css/app.css', v: `.application {
    padding: 20px;
    border: 1px solid #d9d5cb;
    background: #fff;
}

.application-head {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    gap: 8px;
}

.application-head h2 {
    margin: 0 0 6px;
    font-size: 20px;
}

.application-meta {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin: 16px 0 0;
}

.application-meta dt {
    font-size: 12px;
    color: #6b6f6d;
}

.application-meta dd {
    margin: 2px 0 0;
    font-weight: 600;
}

.status {
    display: inline-block;
    padding: 2px 8px;
    font-size: 12px;
    border: 1px solid currentColor;
}

.status--assigned { color: #1b4f8a; }
.status--completed { color: #2e6b4f; }
.status--new { color: #8a5a00; }

@media (max-width: 480px) {
    .application-meta { grid-template-columns: 1fr; }
}` },
        { t: 'note', v: 'Не центрируйте всё подряд. Заголовок, форма и карточки должны стоять на одной левой линии. Центрирование уместно для коротких пустых состояний и модальных окон.' },
        { t: 'h', v: 'Что проверить в DevTools' },
        { t: 'ol', v: [
          'Откройте 390 px и проверьте, нет ли горизонтальной прокрутки.',
          'Нажмите Tab: виден ли фокус на каждой кнопке и ссылке.',
          'Увеличьте масштаб до 200%: не обрезается ли текст.',
          'Проверьте, что длинное название помещения переносится, а не вылезает за карточку.'
        ] }
      ],
      practice: [
        'Свёрстайте сетку из трёх карточек, которая на 390 px становится одной колонкой.',
        'Сделайте строку навигации: на мобильном — колонка, на 768+ — строка.',
        'Добавьте `:focus-visible` для кнопок и ссылок.',
        'Проверьте макет на 390, 768 и 1440 px и убедитесь, что нет горизонтальной прокрутки.'
      ],
      quiz: [
        { q: 'Что делает `grid-template-columns: repeat(auto-fit, minmax(240px, 1fr))`?', options: ['Фиксирует три колонки', 'Создаёт столько колонок, сколько помещается, но не уже 240 px', 'Центрирует карточки'], answer: 1, explain: 'Колонки автоматически переносятся, минимальная ширина 240 px.' },
        { q: 'Почему `box-sizing: border-box` удобен?', options: ['Ширина включает padding и border', 'Убирает отступы', 'Делает текст жирным'], answer: 0, explain: 'Проще считать размеры: 100% ширины не вылезает за контейнер из-за padding.' },
        { q: 'Какой контраст текста считается минимальным для основного текста?', options: ['2:1', '3:1', '4.5:1'], answer: 2, explain: 'WCAG AA требует 4.5:1 для обычного текста.' }
      ]
    },
    {
      id: 'bootstrap',
      title: 'Bootstrap 5 без борьбы с ним',
      minutes: 18,
      level: 'база',
      tags: ['bootstrap', 'css'],
      goal: 'Использовать сетку и компоненты Bootstrap для быстрой вёрстки, не превращая страницу в набор одинаковых карточек.',
      blocks: [
        { t: 'p', v: 'Bootstrap часто указан в требованиях или лежит в проекте. Его смысл — не тратить время на базовую сетку и формы. Ошибка новичка — тащить все компоненты подряд и получать безликий шаблон. Берите сетку, формы, навбар и алерты, остальное делайте своим CSS.' },
        { t: 'h', v: 'Подключение' },
        { t: 'code', lang: 'html', file: 'resources/views/layouts/app.blade.php', v: `<link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
...
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>` },
        { t: 'p', v: 'Локальные файлы надёжнее CDN: на экзамене интернет может быть ограничен. Скачайте CSS и JS в `public/vendor/bootstrap` заранее.' },
        { t: 'h', v: 'Сетка' },
        { t: 'code', lang: 'html', file: 'сетка Bootstrap', v: `<div class="container">
    <div class="row g-3">
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100">
                <img src="{{ asset('img/rooms/auditory.jpg') }}" class="card-img-top" alt="Аудитория на 120 мест">
                <div class="card-body">
                    <h2 class="h5 card-title">Большая аудитория</h2>
                    <p class="card-text text-muted">120 мест, проектор, микрофоны.</p>
                    <a href="/application" class="btn btn-primary">Забронировать</a>
                </div>
            </div>
        </div>
    </div>
</div>` },
        { t: 'p', v: '`col-12 col-md-6 col-lg-4` означает: на телефоне одна колонка, с 768 px — две, с 992 px — три. `g-3` задаёт отступы сетки. `h-100` растягивает карточку по высоте ряда.' },
        { t: 'h', v: 'Формы' },
        { t: 'code', lang: 'html', file: 'форма Bootstrap', v: `<form method="POST" action="{{ route('register.store') }}" novalidate>
    @csrf

    <div class="mb-3">
        <label for="fio" class="form-label">ФИО</label>
        <input type="text" id="fio" name="fio"
               class="form-control @error('fio') is-invalid @enderror"
               value="{{ old('fio') }}">
        @error('fio')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <button type="submit" class="btn btn-primary w-100">Создать пользователя</button>
</form>` },
        { t: 'p', v: '`is-invalid` + `invalid-feedback` — готовый механизм показа ошибки. Главное — не забыть `@error` вокруг сообщения.' },
        { t: 'h', v: 'Что брать из Bootstrap' },
        { t: 'table', head: ['Нужно', 'Классы'], rows: [
          ['Контейнер', '`container`, `container-fluid`'],
          ['Сетка', '`row`, `col-12 col-md-6 col-lg-4`, `g-3`'],
          ['Формы', '`form-label`, `form-control`, `form-select`, `is-invalid`, `invalid-feedback`'],
          ['Кнопки', '`btn`, `btn-primary`, `btn-outline-secondary`, `w-100`'],
          ['Отступы', '`mb-3`, `mt-4`, `p-4`, `gap-2`'],
          ['Текст', '`text-muted`, `small`, `fw-semibold`'],
          ['Алерты', '`alert alert-success`, `alert-danger`'],
          ['Бейджи', '`badge text-bg-warning`']
        ] },
        { t: 'note', v: 'Не смешивайте Bootstrap 4 и 5. В пятой версии `ml-*` заменены на `ms-*`, `float-right` — на `float-end`, `data-toggle` — на `data-bs-toggle`. Если копируете пример из интернета, проверьте версию.' },
        { t: 'h', v: 'Как не получить шаблон' },
        { t: 'ul', v: [
          'Задайте свои цвета через CSS-переменные, а не правьте `bootstrap.min.css`.',
          'Оставьте один акцентный цвет. Не красьте каждую кнопку в разный.',
          'Уберите лишние тени и скругления: `border-radius` 0–4 px выглядит собраннее.',
          'Своя типографика: подключите шрифт из макета, а не системный Bootstrap.',
          'Проверьте пустые состояния: «У вас пока нет заявок» должно выглядеть нормально, а не как пустая таблица.'
        ] },
        { t: 'code', lang: 'css', file: 'public/css/app.css', v: `:root {
    --bs-primary: #1b4f8a;
    --bs-primary-rgb: 27, 79, 138;
}

.btn-primary {
    --bs-btn-bg: #1b4f8a;
    --bs-btn-border-color: #1b4f8a;
    --bs-btn-hover-bg: #143b68;
    --bs-btn-hover-border-color: #143b68;
}

.card {
    border-radius: 0;
    border-color: #d9d5cb;
}` },
        { t: 'h', v: 'Навбар с мобильным меню' },
        { t: 'code', lang: 'html', file: 'навбар', v: `<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="/">Конференции.РФ</a>
        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#mainNav"
                aria-controls="mainNav" aria-expanded="false" aria-label="Меню">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="/profile">Кабинет</a></li>
                <li class="nav-item"><a class="nav-link" href="/logout">Выйти</a></li>
            </ul>
        </div>
    </div>
</nav>` },
        { t: 'p', v: 'Без `bootstrap.bundle.min.js` кнопка-бургер не откроется. Это частая ошибка: CSS подключили, а JS забыли.' },
        { t: 'note', label: 'Прагматичный выбор', v: 'На экзамене важнее предсказуемость, чем оригинальность. Если времени мало — сетка Bootstrap + свой CSS на 100 строк дадут аккуратный результат быстрее, чем попытка написать всё с нуля.' }
      ],
      practice: [
        'Соберите страницу со списком помещений на сетке Bootstrap: 1 / 2 / 3 колонки.',
        'Сделайте форму регистрации с `is-invalid` и русскими сообщениями.',
        'Подключите навбар с работающим бургером и проверьте на 390 px.',
        'Переопределите цвет кнопки через CSS-переменные, не редактируя bootstrap.min.css.'
      ],
      quiz: [
        { q: 'Что означает класс `col-md-6`?', options: ['Шесть колонок на всех экранах', 'Половина ширины на экранах от 768 px', 'Скрыть на мобильном'], answer: 1, explain: 'md — брейкпоинт 768 px, 6 из 12 колонок.' },
        { q: 'Почему бургер не открывается?', options: ['Не подключён bootstrap.bundle.min.js', 'Нет класса container', 'Не указан lang'], answer: 0, explain: 'Collapse работает через JS Bootstrap.' },
        { q: 'Как правильно переопределить цвет кнопки?', options: ['Править bootstrap.min.css', 'Через свои CSS-переменные и классы после подключения Bootstrap', 'Инлайновым style на каждой кнопке'], answer: 1, explain: 'Так обновление библиотеки не сотрёт ваши правки.' }
      ]
    }
  ]
});
