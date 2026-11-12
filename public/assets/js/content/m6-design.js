window.COURSE_MODULES = window.COURSE_MODULES || [];
window.COURSE_MODULES.push({
  id: 'm6',
  num: '06',
  title: 'Графический дизайн',
  lead: 'Мобильный макет 390×844, изображения и руководство по стилю. Второе задание — 25 баллов.',
  lessons: [
    {
      id: 'mobile-layout',
      title: 'Макет 390×844: отдельные экраны',
      minutes: 26,
      level: 'средний',
      tags: ['дизайн', 'мобильный', 'html'],
      goal: 'Сделать шесть статических HTML-страниц под смартфон, которые выглядят как одно приложение.',
      blocks: [
        { t: 'p', v: 'В задании 2 просят разработать дизайн всех страниц для смартфона 390×844. Это не «адаптив приложения», а отдельный статический макет: папка `mobile-design` с HTML-файлами и одним общим CSS. Эксперт открывает их и смотрит, как выглядит продукт на телефоне.' },
        { t: 'note', label: 'Из КИМ', v: '«Дизайн допустимо представить в виде .html файлов (отдельный файл для каждой страницы)». Значит, можно не подключать Laravel: достаточно статики. Но страницы должны быть согласованы между собой.' },
        { t: 'h', v: 'Структура папки' },
        { t: 'code', lang: 'text', file: 'mobile-design/', v: `mobile-design/
├── index.html          главная
├── register.html       регистрация
├── login.html          вход
├── application.html    создание заявки
├── profile.html        личный кабинет
├── admin.html          панель администратора
├── style.css           общий стиль
├── img/
│   ├── room-auditory.jpg
│   ├── room-coworking.jpg
│   └── room-cinema.jpg
└── fonts/
    └── pt-sans/` },
        { t: 'p', v: 'Один CSS на все страницы — обязательно. Если у каждого файла свой стиль, макет рассыпается: другие отступы, другие кнопки, другой шрифт.' },
        { t: 'h', v: 'Каркас страницы' },
        { t: 'code', lang: 'html', file: 'mobile-design/index.html', v: `<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=390, initial-scale=1.0">
    <title>Конференции.РФ — Главная</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="phone">
        <header class="topbar">
            <span class="logo">Конференции.РФ</span>
            <a href="login.html">Вход</a>
        </header>

        <main class="content">
            <h1>Конференции.РФ</h1>
            <p class="lead">
                Бронируйте аудиторию, коворкинг или кинозал
                для Всероссийских конференций.
            </p>
            <a href="register.html" class="btn">Зарегистрироваться</a>

            <h2>Доступные помещения</h2>

            <article class="card">
                <img src="img/room-auditory.jpg" alt="Аудитория на 120 мест">
                <div class="card-body">
                    <span class="badge">Аудитория</span>
                    <h3>Большая конференц-аудитория</h3>
                    <p class="muted">120 мест, проектор, микрофоны.</p>
                    <a href="application.html" class="btn btn-light">Забронировать</a>
                </div>
            </article>
        </main>

        <nav class="tabbar" aria-label="Основное меню">
            <a href="index.html" class="active">Главная</a>
            <a href="profile.html">Заявки</a>
            <a href="login.html">Вход</a>
        </nav>
    </div>
</body>
</html>` },
        { t: 'p', v: '`width=390` во viewport — спорное решение: на реальном телефоне страница может масштабироваться. Для макета это допустимо, потому что проверяют именно 390×844. Если хотите честный адаптив, используйте `width=device-width`, но тогда проверяйте на реальных размерах.' },
        { t: 'h', v: 'Общий стиль' },
        { t: 'code', lang: 'css', file: 'mobile-design/style.css', v: `:root {
    --blue: #007bff;
    --navy: #0d47a1;
    --grey: #6c757d;
    --bg: #f8f9fa;
    --white: #ffffff;
    --line: #dee2e6;
    --danger: #dc3545;
}

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: 'PT Sans', Arial, sans-serif;
    font-size: 16px;
    line-height: 1.5;
    color: #212529;
    background: var(--bg);
}

.phone {
    width: 390px;
    min-height: 844px;
    margin: 0 auto;
    background: var(--white);
    position: relative;
    padding-bottom: 64px;
    border-left: 1px solid var(--line);
    border-right: 1px solid var(--line);
}

.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    background: var(--navy);
    color: var(--white);
}

.topbar a {
    color: var(--white);
    text-decoration: none;
    font-size: 14px;
}

.content {
    padding: 16px;
}

h1 {
    margin: 0 0 8px;
    font-size: 36px;
    line-height: 1.1;
}

h2 {
    margin: 24px 0 12px;
    font-size: 24px;
}

h3 {
    margin: 6px 0 6px;
    font-size: 18px;
}

.muted {
    margin: 0 0 10px;
    font-size: 12px;
    font-style: italic;
    color: var(--grey);
}

.btn {
    display: block;
    width: 100%;
    min-height: 44px;
    padding: 12px 16px;
    border: 0;
    border-radius: 4px;
    background: var(--blue);
    color: var(--white);
    font: inherit;
    font-weight: 700;
    text-align: center;
    text-decoration: none;
    cursor: pointer;
}

.btn-light {
    background: var(--white);
    color: var(--navy);
    border: 1px solid var(--blue);
}

.card {
    margin-bottom: 16px;
    border: 1px solid var(--line);
    border-radius: 4px;
    overflow: hidden;
}

.card img {
    display: block;
    width: 100%;
    height: 140px;
    object-fit: cover;
}

.card-body {
    padding: 12px;
}

.badge {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 10px;
    background: #e9ecef;
    color: var(--grey);
    font-size: 12px;
    font-weight: 700;
}

.tabbar {
    position: fixed;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 390px;
    display: flex;
    justify-content: space-around;
    padding: 10px 0;
    background: var(--white);
    border-top: 1px solid var(--line);
}

.tabbar a {
    color: var(--grey);
    font-size: 12px;
    text-decoration: none;
}

.tabbar a.active {
    color: var(--blue);
}` },
        { t: 'h', v: 'Формы с ошибками' },
        { t: 'p', v: 'В задании 2 отдельно просят показать ошибки валидации на форме. В статическом макете это просто блоки под полями — но они должны выглядеть правдоподобно.' },
        { t: 'code', lang: 'html', file: 'register.html', v: `<form class="form">
    <div class="field">
        <label for="login">Логин</label>
        <input type="text" id="login" name="login" class="input-error"
               value="maria" aria-describedby="login-error">
        <span class="error" id="login-error">
            Логин: латиница и цифры, не менее 6 символов.
        </span>
    </div>

    <div class="field">
        <label for="phone">Телефон</label>
        <input type="tel" id="phone" name="phone" value="8(912)345-67-89">
    </div>

    <button type="submit" class="btn">Зарегистрироваться</button>
</form>` },
        { t: 'code', lang: 'css', file: 'style.css', v: `.field {
    margin-bottom: 14px;
}

label {
    display: block;
    margin-bottom: 5px;
    font-weight: 700;
    color: var(--navy);
    font-size: 14px;
}

input, select, textarea {
    width: 100%;
    min-height: 44px;
    padding: 10px 12px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font: inherit;
    background: var(--white);
}

.input-error {
    border-color: var(--danger);
}

.error {
    display: block;
    margin-top: 4px;
    color: var(--danger);
    font-size: 13px;
}` },
        { t: 'p', v: 'Подпись поля — снаружи, не внутри placeholder. Ошибка — под полем и связана с ним через `aria-describedby`. Это одновременно дизайн и доступность.' },
        { t: 'h', v: 'Экран админки' },
        { t: 'p', v: 'В макете админки нужны фильтр, карточки заявок и смена статуса. Не рисуйте пустую таблицу: добавьте три заявки с разными статусами — так видно, что интерфейс продуман.' },
        { t: 'code', lang: 'html', file: 'admin.html', v: `<div class="content">
    <h1 class="h2">Заявки</h1>

    <div class="filter">
        <input type="search" placeholder="Поиск по ФИО">
        <select>
            <option>Все статусы</option>
            <option>Новая</option>
            <option>Назначено</option>
            <option>Завершено</option>
        </select>
    </div>

    <article class="app-card">
        <div class="app-head">
            <strong>Иванова Мария</strong>
            <span class="badge badge--new">Новая</span>
        </div>
        <p class="muted">Коворкинг «Вектор» · 12.11.2026 · СБП</p>
        <select class="status-select">
            <option>Новая</option>
            <option>Мероприятие назначено</option>
            <option>Завершено</option>
        </select>
    </article>
</div>` },
        { t: 'h', v: 'Навигация между экранами' },
        { t: 'p', v: 'Все страницы должны ссылаться друг на друга: с главной — на регистрацию и заявку, с входа — на регистрацию, из кабинета — на новую заявку. Мёртвые ссылки `href="#"` выглядят как недоделка. Если страницы ещё нет, лучше не ставить ссылку вовсе.' },
        { t: 'h', v: 'Что проверить' },
        { t: 'ul', v: [
          'Ширина ровно 390, высота не меньше 844.',
          'Один шрифт и одна палитра на всех экранах.',
          'Нижнее меню не перекрывает контент (отступ снизу).',
          'Кнопки не меньше 44 px по высоте.',
          'Длинные названия переносятся, а не обрезаются.',
          'Формы показывают ошибки.',
          'Нет горизонтальной прокрутки внутри 390 px.',
          'Ссылки ведут на существующие файлы.'
        ] },
        { t: 'ok', label: 'Готово, если', v: 'Открыли `index.html`, прошли по ссылкам все шесть экранов, и нигде не пришлось скроллить вбок.' }
      ],
      practice: [
        'Создайте папку `mobile-design` с `style.css` и шестью HTML-файлами.',
        'Свёрстайте главную и регистрацию с ошибками.',
        'Сделайте кабинет и админку с карточками.',
        'Проверьте все ссылки и отсутствие горизонтальной прокрутки.'
      ],
      quiz: [
        { q: 'Что требуется в задании 2?', options: ['Адаптив Laravel-приложения', 'Отдельные HTML-страницы под 390×844', 'PDF-макет'], answer: 1, explain: 'Статический мобильный макет, по файлу на страницу.' },
        { q: 'Почему один CSS на все страницы?', options: ['Так быстрее', 'Чтобы макет был согласован', 'Это требование HTML'], answer: 1, explain: 'Единые токены, отступы и компоненты.' },
        { q: 'Как показать ошибку валидации в макете?', options: ['Красной рамкой и текстом под полем', 'Только красной рамкой', 'Alert вверху страницы'], answer: 0, explain: 'Ошибка должна быть понятна и связана с полем.' }
      ]
    },
    {
      id: 'images',
      title: 'Изображения: вес, размер, alt',
      minutes: 18,
      level: 'средний',
      tags: ['дизайн', 'изображения', 'производительность'],
      goal: 'Подготовить картинки так, чтобы макет и приложение грузились быстро и выглядели резко.',
      blocks: [
        { t: 'p', v: 'В задании 2 сказано: изображения нужно оптимизировать и улучшить. На практике это значит: уменьшить до нужного размера, сжать, переименовать по-человечески и добавить `alt`. Фотография на 4 МБ с телефона — худший вариант.' },
        { t: 'h', v: 'Размеры под экран' },
        { t: 'table', head: ['Где используется', 'Ширина', 'Вес'], rows: [
          ['Карточка в мобильном макете', '390–780 px', '40–120 КБ'],
          ['Карточка в десктопной сетке', '600–900 px', '80–200 КБ'],
          ['Главный баннер', '1200–1600 px', '150–300 КБ'],
          ['Иконка', '24–64 px', 'SVG или 2–5 КБ']
        ] },
        { t: 'p', v: 'Для Retina достаточно двойной ширины: карточка 390 px → картинка 780 px. Дальше смысла нет: файл растёт, разница не видна.' },
        { t: 'h', v: 'Форматы' },
        { t: 'ul', v: [
          '**JPEG** — фотографии, градиенты, сложные изображения.',
          '**PNG** — прозрачность, скриншоты, логотипы с чёткими краями.',
          '**WebP** — современный формат, меньше JPEG при том же качестве. Поддерживается всеми актуальными браузерами.',
          '**SVG** — иконки, логотипы, простые схемы. Масштабируется без потерь.'
        ] },
        { t: 'h', v: 'Сжатие без Photoshop' },
        { t: 'p', v: 'На экзамене может не быть графического редактора. Подойдут онлайн-сервисы (если разрешён интернет — обычно нет), встроенный Paint или ImageMagick. Заранее проверьте, что умеете уменьшать изображение.' },
        { t: 'code', lang: 'bash', file: 'ImageMagick', v: 'magick input.jpg -resize 1200x -quality 82 output.jpg\nmagick input.png -resize 780x -strip output.png' },
        { t: 'p', v: '`-strip` удаляет метаданные (EXIF), которые тоже весят. Для JPEG качество 80–85 — оптимум. Ниже 70 появляются артефакты вокруг текста.' },
        { t: 'h', v: 'Переименование' },
        { t: 'p', v: '`IMG_20261012_184533.jpg` ничего не говорит. `room-auditory.jpg`, `car-camry.jpg`, `doctor-ivanova.jpg` — говорят. Латиница, нижний регистр, дефисы. Это помогает и в коде, и на защите.' },
        { t: 'h', v: 'В HTML' },
        { t: 'code', lang: 'html', file: 'разметка', v: `<img src="img/room-auditory.jpg"
     alt="Большая конференц-аудитория на 120 мест"
     width="390" height="220"
     loading="lazy">` },
        { t: 'p', v: '`alt` описывает, что на картинке и зачем она. «Фото» — плохо. «Аудитория на 120 мест с проектором» — хорошо. `width` и `height` предотвращают скачок макета при загрузке (CLS).' },
        { t: 'h', v: 'В Laravel' },
        { t: 'code', lang: 'php', file: 'контроллер', v: `$path = $request->file('image')->store('rooms', 'public');

// В шаблоне
<img src="{{ asset('storage/' . $room->image) }}"
     alt="{{ $room->name }}"
     width="600" height="400"
     loading="lazy">` },
        { t: 'p', v: 'Загруженные пользователем файлы тоже стоит ограничивать: `max:2048`, `mimes:jpg,png,webp`. Иначе один эксперт загрузит 12 МБ и будет ждать.' },
        { t: 'h', v: 'Оптимизация в PHP' },
        { t: 'p', v: 'Если нужно сжимать на сервере, используйте GD или Imagick. Пример с GD был в уроке про файлы. На экзамене проще подготовить картинки заранее и положить в `public/img`.' },
        { t: 'note', v: 'Не используйте изображения с чужих сайтов по прямым ссылкам. На экзамене интернета нет, а на защите спросят, откуда фото. Берите приложенные к заданию медиафайлы или свои.' },
        { t: 'h', v: 'Проверка' },
        { t: 'ul', v: [
          'Вес каждой картинки до 300 КБ.',
          'Ширина не больше двойной ширины контейнера.',
          'Имена латиницей и по смыслу.',
          'У всех `<img>` есть `alt`.',
          'Нет битых ссылок на изображения.',
          'Картинки не растянуты и не сплющены (сохранены пропорции).'
        ] },
        { t: 'ok', label: 'Готово, если', v: 'Страница с шестью карточками открывается меньше чем за секунду на локальном сервере, а суммарный вес изображений меньше 1 МБ.' }
      ],
      practice: [
        'Уменьшите три фотографии до 780 px по ширине и сожмите до 100 КБ.',
        'Переименуйте файлы латиницей по смыслу.',
        'Добавьте `alt`, `width`, `height` и `loading="lazy"`.',
        'Проверьте вес папки `img` и отсутствие битых ссылок.'
      ],
      quiz: [
        { q: 'Какая ширина достаточна для карточки 390 px на Retina?', options: ['390 px', '780 px', '3000 px'], answer: 1, explain: 'Двойная ширина для плотных экранов.' },
        { q: 'Что делает `-strip` в ImageMagick?', options: ['Удаляет метаданные', 'Меняет формат', 'Уменьшает резкость'], answer: 0, explain: 'EXIF и профили могут весить немало.' },
        { q: 'Зачем `width` и `height` у img?', options: ['Для SEO', 'Чтобы не было скачка макета при загрузке', 'Для анимации'], answer: 1, explain: 'Браузер резервирует место до загрузки.' }
      ]
    },
    {
      id: 'design-check',
      title: 'Руководство по стилю и проверка дизайна',
      minutes: 20,
      level: 'средний',
      tags: ['дизайн', 'стиль', 'проверка'],
      goal: 'Привести интерфейс к фирменному стилю из задания и пройти визуальную проверку.',
      blocks: [
        { t: 'p', v: 'В комплекте с КИМ идёт руководство по стилю. Его нужно соблюдать: палитра, шрифт, размеры. Это не рекомендация, а критерий «разработка дизайн-концепций в соответствии с корпоративным стилем заказчика» — 11 баллов.' },
        { t: 'h', v: 'Палитра из руководства' },
        { t: 'table', head: ['Роль', 'HEX', 'Где использовать'], rows: [
          ['Голубой', '`#007bff`', 'Основные кнопки, ссылки, акценты'],
          ['Тёмно-синий', '`#0d47a1`', 'Шапка, заголовки, hover'],
          ['Серый', '`#6c757d`', 'Вспомогательный текст'],
          ['Фон', '`#f8f9fa`', 'Фон страниц'],
          ['Выделяющий', '`#ffffff`', 'Карточки, поля, контраст']
        ] },
        { t: 'code', lang: 'css', file: 'переменные', v: `:root {
    --color-primary: #007bff;
    --color-primary-dark: #0d47a1;
    --color-muted: #6c757d;
    --color-bg: #f8f9fa;
    --color-surface: #ffffff;
    --color-border: #dee2e6;
    --color-danger: #dc3545;
    --color-success: #28a745;
}` },
        { t: 'h', v: 'Типографика' },
        { t: 'table', head: ['Элемент', 'Размер', 'Начертание'], rows: [
          ['H1', '36 px', 'PT Sans Bold'],
          ['H2–H3', '24 / 18 px', 'PT Sans Bold'],
          ['Основной текст', '16 px', 'PT Sans'],
          ['Вспомогательный', '12 px', 'PT Sans Italic']
        ] },
        { t: 'p', v: 'PT Sans должен лежать локально: `public/fonts/pt-sans`. Подключите через `@font-face`, а не через Google Fonts — на экзамене интернета нет.' },
        { t: 'code', lang: 'css', file: '@font-face', v: `@font-face {
    font-family: 'PT Sans';
    src: url('fonts/pt-sans/PTSans-Regular.ttf') format('truetype');
    font-weight: 400;
    font-display: swap;
}

@font-face {
    font-family: 'PT Sans';
    src: url('fonts/pt-sans/PTSans-Bold.ttf') format('truetype');
    font-weight: 700;
    font-display: swap;
}

body {
    font-family: 'PT Sans', Arial, sans-serif;
    font-size: 16px;
    line-height: 1.5;
}

h1 { font-size: 36px; font-weight: 700; }
h2 { font-size: 24px; font-weight: 700; }
h3 { font-size: 18px; font-weight: 700; }
.small, .muted { font-size: 12px; font-style: italic; color: #6c757d; }` },
        { t: 'h', v: 'Не только палитра' },
        { t: 'p', v: 'Соответствие стилю — это ещё и последовательность. Одинаковые кнопки, одинаковые отступы, одинаковые карточки. Если на главной кнопка скруглена на 4 px, а в форме на 12 — стиль развалился.' },
        { t: 'ul', v: [
          'Один радиус: 4 px. Не смешивайте 0, 8 и 20.',
          'Одна тень или ни одной. Лучше граница.',
          'Одинаковый отступ карточек: 16 px.',
          'Одинаковая высота полей и кнопок: 44 px.',
          'Один способ выделения активного пункта меню.',
          'Одинаковые бейджи статусов на всех экранах.'
        ] },
        { t: 'h', v: 'Иерархия' },
        { t: 'p', v: 'На каждом экране должен быть один главный объект. На главной — заголовок и кнопка. В кабинете — список заявок. В админке — фильтр и карточки. Если всё одинаково крупное, глаз не знает, куда смотреть.' },
        { t: 'h', v: 'Пустые состояния' },
        { t: 'p', v: 'Пустой список — тоже экран. «У вас пока нет заявок» + ссылка на создание. Не оставляйте белое пятно и не показывайте пустую таблицу с заголовками.' },
        { t: 'h', v: 'Проверка по скриншотам' },
        { t: 'steps', v: [
          { b: 'Сделайте скриншоты', t: 'Главная, регистрация с ошибками, кабинет, форма, админка. На 390 и 1440.' },
          { b: 'Посмотрите на первый экран', t: 'Понятно ли за три секунды, что это за система и что делать?' },
          { b: 'Проверьте сетку', t: 'Всё ли выровнено по одной левой линии? Нет ли случайных отступов?' },
          { b: 'Проверьте контраст', t: 'Серый текст на белом читается? Кнопка не сливается с фоном?' },
          { b: 'Проверьте клавиатуру', t: 'Tab проходит по всем элементам, фокус виден.' },
          { b: 'Уберите лишнее', t: 'Тени, градиенты, три акцента, декоративные иконки — всё, что не помогает.' }
        ] },
        { t: 'h', v: 'Чек-лист дизайна' },
        { t: 'ul', v: [
          'Палитра совпадает с руководством.',
          'PT Sans подключён локально.',
          'Размеры заголовков соответствуют 36/24/18.',
          'Один радиус и один уровень тени.',
          'Кнопки одинаковой высоты, минимум 44 px.',
          'Поля имеют видимые подписи.',
          'Ошибки показаны у полей.',
          'Статусы читаются без цвета.',
          'Есть пустые состояния.',
          'Нет горизонтальной прокрутки.',
          'Фокус виден.',
          'Изображения не растянуты.',
          'Мобильный макет согласован с приложением.'
        ] },
        { t: 'warn', label: 'Частая ошибка', v: 'Красивый макет, который не совпадает с приложением: другие цвета, другой шрифт, другие названия кнопок. Эксперт воспринимает это как два разных продукта. Сначала стиль, потом обе реализации по нему.' },
        { t: 'ok', label: 'Готово, если', v: 'Вы можете показать скриншоты 390 и 1440 и объяснить, почему каждый цвет и размер взяты из руководства.' }
      ],
      practice: [
        'Выпишите палитру и размеры шрифтов из руководства в CSS-переменные.',
        'Подключите PT Sans локально.',
        'Приведите кнопки, поля и карточки к одному радиусу и высоте.',
        'Сделайте скриншоты 390 и 1440 и проверьте по чек-листу.'
      ],
      quiz: [
        { q: 'Какой шрифт требует руководство?', options: ['Inter', 'PT Sans', 'Roboto'], answer: 1, explain: 'Один основной шрифт — PT Sans.' },
        { q: 'Сколько уровней тени допустимо?', options: ['Один', 'Три', 'Сколько угодно'], answer: 0, explain: 'Лучше один или ни одного, иначе интерфейс выглядит случайным.' },
        { q: 'Что делать с пустым списком?', options: ['Оставить пустую таблицу', 'Показать сообщение и ссылку на действие', 'Скрыть страницу'], answer: 1, explain: 'Пустое состояние — часть интерфейса.' }
      ]
    }
  ]
});
