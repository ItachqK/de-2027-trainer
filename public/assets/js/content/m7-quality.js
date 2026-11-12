window.COURSE_MODULES = window.COURSE_MODULES || [];
window.COURSE_MODULES.push({
  id: 'm7',
  num: '07',
  title: 'Качество и интерактив',
  lead: 'Адаптив, микроанимации, доступность и чистота кода. Третье задание — ещё 25 баллов.',
  lessons: [
    {
      id: 'responsive-motion',
      title: 'Адаптивность и микроанимации',
      minutes: 24,
      level: 'средний',
      tags: ['адаптив', 'анимации', 'css'],
      goal: 'Сделать интерфейс удобным на 390, 768 и 1440 px и добавить анимации, которые не мешают.',
      blocks: [
        { t: 'p', v: 'Третье задание просит интегрировать адаптивный дизайн и предусмотреть анимацию и микроанимации. Это не «добавить параллакс», а сделать интерфейс живым и понятным: кнопка реагирует, меню открывается, ошибка появляется плавно.' },
        { t: 'h', v: 'Три ширины, которые проверяют' },
        { t: 'table', head: ['Ширина', 'Что смотреть'], rows: [
          ['390 px', 'Одна колонка, меню-бургер, кнопки на всю ширину, нет горизонтальной прокрутки'],
          ['768 px', 'Две колонки, таблицы прокручиваются, формы не растянуты'],
          ['1440 px', 'Контент не растянут на весь экран, есть поля, сетка 3–4 колонки']
        ] },
        { t: 'h', v: 'Мобильный-first' },
        { t: 'p', v: 'Сначала стили для узкого экрана, потом расширяем. Так проще не забыть мобильное состояние.' },
        { t: 'code', lang: 'css', file: 'public/css/app.css', v: `.rooms {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
}

.application-card__head {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

@media (min-width: 768px) {
    .rooms {
        grid-template-columns: repeat(2, 1fr);
    }

    .application-card__head {
        flex-direction: row;
        justify-content: space-between;
        align-items: flex-start;
    }
}

@media (min-width: 1100px) {
    .rooms {
        grid-template-columns: repeat(3, 1fr);
    }

    main {
        max-width: 1100px;
        margin: 0 auto;
    }
}` },
        { t: 'h', v: 'Таблицы на мобильном' },
        { t: 'p', v: 'Таблица с восемью колонками на 390 px не помещается. Два варианта: горизонтальная прокрутка внутри контейнера или карточки на мобильном. Второй лучше для админки заявок.' },
        { t: 'code', lang: 'css', file: 'таблица', v: `.table-wrap {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.table-wrap table {
    min-width: 640px;
}

@media (max-width: 720px) {
    .table-wrap table {
        min-width: 560px;
    }
}` },
        { t: 'p', v: 'Прокрутка должна быть внутри `.table-wrap`, а не у всей страницы. Если `body` скроллится вбок — вы потеряли балл за адаптивность.' },
        { t: 'h', v: 'Микроанимации' },
        { t: 'p', v: 'Микроанимация — короткая реакция на действие. 150–250 мс, `ease-out`, только `transform` и `opacity`. Не анимируйте `width`, `height`, `top`, `left` — это вызывает перерасчёт макета.' },
        { t: 'code', lang: 'css', file: 'кнопки и карточки', v: `.btn {
    transition: background-color .18s ease, transform .18s ease, box-shadow .18s ease;
}

.btn:hover {
    background: #0d47a1;
    transform: translateY(-1px);
}

.btn:active {
    transform: translateY(0);
}

.card {
    transition: border-color .18s ease, transform .18s ease;
}

.card:hover {
    border-color: #007bff;
    transform: translateY(-2px);
}` },
        { t: 'code', lang: 'css', file: 'появление', v: `@keyframes fade-up {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-in {
    animation: fade-up .4s ease-out both;
}

.alert {
    animation: fade-up .25s ease-out both;
}` },
        { t: 'p', v: 'Анимация появления уместна для алертов, модальных окон и первого экрана. Не анимируйте каждый блок при скролле: это раздражает и нагружает браузер.' },
        { t: 'h', v: 'Уважение к настройкам' },
        { t: 'code', lang: 'css', file: 'reduced motion', v: `@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: .01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: .01ms !important;
        scroll-behavior: auto !important;
    }
}` },
        { t: 'p', v: 'Это обязательный блок, если вы используете анимации. Пользователи с вестибулярными расстройствами отключают движение в системе — интерфейс должен уважать это.' },
        { t: 'h', v: 'Интерактив, который просят' },
        { t: 'ul', v: [
          'Маска телефона при вводе.',
          'Подтверждение удаления через `confirm()` или модальное окно.',
          'Показ/скрытие пароля.',
          'Рейтинг звёздами с подсветкой.',
          'Плавное появление сообщений.',
          'Мобильное меню-бургер.',
          'Фильтр, который применяется без перезагрузки (если успеваете).'
        ] },
        { t: 'code', lang: 'javascript', file: 'public/js/app.js', v: `document.querySelectorAll('[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!confirm(form.dataset.confirm)) {
            event.preventDefault();
        }
    });
});

const toggle = document.querySelector('[data-password-toggle]');

if (toggle) {
    toggle.addEventListener('click', () => {
        const input = document.querySelector(toggle.dataset.passwordToggle);
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        toggle.textContent = isPassword ? 'Скрыть' : 'Показать';
        toggle.setAttribute('aria-pressed', String(isPassword));
    });
}` },
        { t: 'code', lang: 'blade', file: 'форма', v: `<form method="POST" action="{{ route('admin.posts.destroy', $post) }}"
      data-confirm="Удалить пост? Действие нельзя отменить.">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-outline-danger">Удалить</button>
</form>

<div class="password-field">
    <input type="password" id="password" name="password">
    <button type="button" data-password-toggle="#password" aria-pressed="false">Показать</button>
</div>` },
        { t: 'h', v: 'Проверка' },
        { t: 'ol', v: [
          'Откройте 390 px: нет горизонтальной прокрутки.',
          'Откройте 768 px: сетка перестроилась, таблицы не вылезают.',
          'Откройте 1440 px: контент не растянут на 2000 px.',
          'Наведите на кнопку: есть реакция за 150–250 мс.',
          'Нажмите Tab: фокус виден.',
          'Включите reduced motion в системе: анимации исчезли.',
          'Проверьте консоль: нет ошибок JS.'
        ] },
        { t: 'note', v: 'Не добавляйте библиотеки анимаций ради галочки. Три CSS-перехода и один `@keyframes` выглядят лучше, чем тяжёлый плагин с эффектами, который ломает скролл.' },
        { t: 'ok', label: 'Готово, если', v: 'На всех трёх ширинах интерфейс читается, кнопки нажимаются, анимации короткие и отключаются системной настройкой.' }
      ],
      practice: [
        'Проверьте приложение на 390, 768 и 1440 px.',
        'Переведите сетку карточек на 1/2/3 колонки.',
        'Добавьте hover и focus для кнопок и карточек.',
        'Добавьте `prefers-reduced-motion` и проверьте его.'
      ],
      quiz: [
        { q: 'Какие свойства безопасно анимировать?', options: ['width и height', 'transform и opacity', 'top и left'], answer: 1, explain: 'Они не вызывают перерасчёт макета.' },
        { q: 'Зачем `prefers-reduced-motion`?', options: ['Для SEO', 'Чтобы уважать настройку пользователя и не вызывать дискомфорт', 'Для печати'], answer: 1, explain: 'Это часть доступности.' },
        { q: 'Где должна быть горизонтальная прокрутка таблицы?', options: ['У body', 'Внутри контейнера таблицы', 'Нигде'], answer: 1, explain: 'Иначе вся страница едет вбок.' }
      ]
    },
    {
      id: 'a11y',
      title: 'Доступность без фанатизма',
      minutes: 18,
      level: 'средний',
      tags: ['доступность', 'ux'],
      goal: 'Закрыть базовые требования: подписи, фокус, контраст, клавиатура и понятные ошибки.',
      blocks: [
        { t: 'p', v: 'Доступность — это не отдельная фича, а качество интерфейса. Эксперт проверяет, можно ли пользоваться формой с клавиатуры, видно ли фокус, есть ли подписи у полей. Это дешёвые баллы.' },
        { t: 'h', v: 'Семантика' },
        { t: 'code', lang: 'html', file: 'структура', v: `<header>
    <nav aria-label="Основное меню">...</nav>
</header>

<main id="main">
    <h1>Мои заявки</h1>

    <section aria-labelledby="active-title">
        <h2 id="active-title">Активные</h2>
        ...
    </section>
</main>

<footer>...</footer>` },
        { t: 'p', v: 'Один `h1` на страницу, дальше `h2`, `h3` без пропусков. Не используйте `div` там, где есть `button`, `nav`, `main`, `article`.' },
        { t: 'h', v: 'Подписи полей' },
        { t: 'code', lang: 'html', file: 'правильно', v: `<label for="phone">Телефон</label>
<input type="tel" id="phone" name="phone"
       autocomplete="tel" aria-describedby="phone-hint phone-error">
<span id="phone-hint" class="hint">Формат: 8(999)123-45-67</span>
<span id="phone-error" class="error">Телефон обязателен.</span>` },
        { t: 'p', v: 'Placeholder — не подпись: он исчезает при вводе и плохо читается. Подпись должна быть снаружи. Связь `label for` и `input id` обязательна.' },
        { t: 'h', v: 'Фокус' },
        { t: 'code', lang: 'css', file: 'фокус', v: `:focus-visible {
    outline: 2px solid #007bff;
    outline-offset: 2px;
}

.btn:focus-visible,
.form-control:focus-visible {
    outline: 2px solid #0d47a1;
    outline-offset: 2px;
}` },
        { t: 'p', v: 'Не убирайте `outline` без замены. Если хотите свой стиль — задайте `:focus-visible` с заметной рамкой и контрастом.' },
        { t: 'h', v: 'Клавиатура' },
        { t: 'ul', v: [
          'Все действия доступны с Tab и Enter.',
          'Модальное окно закрывается по Esc.',
          'Фокус не уходит за пределы открытого окна.',
          'Ссылка «Перейти к содержанию» помогает пропустить меню.',
          'Кнопка-бургер имеет `aria-expanded` и `aria-controls`.'
        ] },
        { t: 'code', lang: 'html', file: 'skip link', v: `<a class="skip-link" href="#main">Перейти к содержанию</a>
...
<main id="main" tabindex="-1">` },
        { t: 'code', lang: 'css', file: 'skip link', v: `.skip-link {
    position: absolute;
    left: 12px;
    top: -60px;
    padding: 10px 16px;
    background: #0d47a1;
    color: #fff;
    z-index: 100;
}

.skip-link:focus {
    top: 12px;
}` },
        { t: 'h', v: 'Контраст' },
        { t: 'p', v: 'Основной текст — не ниже 4.5:1, крупный (от 24 px) — не ниже 3:1. Серый `#6c757d` на белом даёт около 4.7:1 — проходит. Светло-серый `#adb5bd` — нет.' },
        { t: 'table', head: ['Пара', 'Контраст', 'Вывод'], rows: [
          ['`#212529` на `#ffffff`', '15.4:1', 'Отлично'],
          ['`#6c757d` на `#ffffff`', '4.7:1', 'Проходит для текста'],
          ['`#adb5bd` на `#ffffff`', '2.4:1', 'Только для декоративных элементов'],
          ['`#ffffff` на `#007bff`', '3.9:1', 'Для крупного текста и кнопок; для мелкого — на грани']
        ] },
        { t: 'h', v: 'Изображения' },
        { t: 'p', v: '`alt` описывает смысл, а не файл. Декоративная картинка — `alt=""`, чтобы скринридер её пропустил. Иконка-кнопка без текста должна иметь `aria-label`.' },
        { t: 'code', lang: 'html', file: 'иконка', v: `<button type="button" aria-label="Закрыть уведомление">
    <svg aria-hidden="true">...</svg>
</button>` },
        { t: 'h', v: 'Ошибки' },
        { t: 'p', v: 'Ошибка говорит, что случилось и что делать: «Проверьте телефон — нужно 11 цифр», а не «Некорректное значение». Сообщение должно быть рядом с полем и связано с ним.' },
        { t: 'h', v: 'Размеры' },
        { t: 'p', v: 'Кнопки и ссылки — минимум 44×44 px. На мобильном между соседними кнопками не меньше 8 px. Мелкие иконки без отступа — частая причина промахов.' },
        { t: 'h', v: 'Быстрая проверка' },
        { t: 'ol', v: [
          'Пройдите форму только клавиатурой.',
          'Включите масштаб 200% — ничего не обрезается.',
          'Проверьте контраст через DevTools.',
          'Отключите изображения — alt читается.',
          'Проверьте `h1–h3` через Lighthouse.',
          'Нажмите Tab на каждом экране: фокус виден.'
        ] },
        { t: 'note', v: 'Не нужно делать идеальный WCAG на 100%. Достаточно семантики, подписей, фокуса, контраста и клавиатуры. Этого хватает для критериев и реального удобства.' }
      ],
      practice: [
        'Проверьте все формы клавиатурой.',
        'Добавьте `:focus-visible` для кнопок и ссылок.',
        'Свяжите ошибки с полями через `aria-describedby`.',
        'Проверьте контраст серого текста и кнопок.'
      ],
      quiz: [
        { q: 'Чем заменить placeholder как подпись?', options: ['`<label for>`', '`title`', 'Ничем'], answer: 0, explain: 'Подпись должна быть видимой и постоянной.' },
        { q: 'Что делает `alt=""`?', options: ['Скрывает картинку', 'Говорит скринридеру, что картинка декоративная', 'Ломает загрузку'], answer: 1, explain: 'Пустой alt допустим для декоративных изображений.' },
        { q: 'Минимальный размер кнопки на мобильном?', options: ['24×24', '32×32', '44×44'], answer: 2, explain: 'Рекомендация по размеру касания.' }
      ]
    },
    {
      id: 'code-quality',
      title: 'Качество кода и производительность',
      minutes: 22,
      level: 'средний',
      tags: ['качество', 'производительность'],
      goal: 'Привести проект к виду, за который не стыдно на защите: структура, имена, безопасность, скорость.',
      blocks: [
        { t: 'p', v: '«Качество кода» — отдельный критерий. Он не про количество комментариев, а про то, может ли другой человек открыть проект и понять, что где лежит. И не сломается ли он от первого же действия.' },
        { t: 'h', v: 'Тонкие контроллеры' },
        { t: 'code', lang: 'php', file: 'плохо', v: `public function store(Request $request)
{
    // 80 строк: валидация, поиск, создание, отправка письма,
    // запись лога, редирект, генерация PDF
}` },
        { t: 'code', lang: 'php', file: 'лучше', v: `public function store(StoreApplicationRequest $request)
{
    $application = Application::create([
        ...$request->validated(),
        'user_id' => auth()->id(),
        'status' => 'new',
    ]);

    return redirect()
        ->route('profile')
        ->with('success', 'Заявка отправлена.');
}` },
        { t: 'p', v: 'Контроллер принимает запрос, вызывает модель, возвращает ответ. Валидация — в FormRequest, подписи — в модели, повторяющиеся куски — в компонентах Blade.' },
        { t: 'h', v: 'Имена' },
        { t: 'ul', v: [
          'Классы — `ApplicationController`, `StoreApplicationRequest`.',
          'Методы — глаголы: `store`, `updateStatus`, `isAdmin`.',
          'Переменные — по смыслу: `$applications`, а не `$data2`.',
          'Таблицы — множественное число: `applications`.',
          'Маршруты — имена: `admin.status`, `application.review`.',
          'Файлы — латиница, без пробелов.'
        ] },
        { t: 'h', v: 'Безопасность' },
        { t: 'table', head: ['Риск', 'Защита'], rows: [
          ['SQL-инъекция', 'Eloquent / prepared statements'],
          ['XSS', '`{{ }}` вместо `{!! !!}`'],
          ['CSRF', '`@csrf` в формах'],
          ['Подмена роли', '`$fillable` без `role` при регистрации'],
          ['Чужие данные', 'Проверка `user_id`'],
          ['Слабый пароль', '`min:8` + `Hash::make`'],
          ['Загрузка shell', '`image|mimes|max`, случайное имя'],
          ['Открытый админ', 'Middleware `admin`']
        ] },
        { t: 'h', v: 'Доработка базы в третьем задании' },
        { t: 'p', v: 'В задании 3 просят доработать БД так, чтобы она решала все задачи заказчика. На практике это значит: добавить поля, которых не хватило в первом задании (причина отказа, комментарий администратора, дата завершения), ограничения и индексы. Не создавайте новые таблицы без необходимости — расширяйте существующие.' },
        { t: 'code', lang: 'php', file: 'миграция доработки', v: `Schema::table('applications', function (Blueprint $table) {
    $table->timestamp('completed_at')->nullable();
    $table->string('rejection_reason')->nullable();
    $table->index('status');
    $table->index('created_at');
});` },
        { t: 'h', v: 'Производительность' },
        { t: 'code', lang: 'php', file: 'N+1', v: `// Плохо: запрос на каждую заявку
$applications = Application::all();

// Хорошо: два запроса
$applications = Application::with(['user', 'room'])->get();` },
        { t: 'code', lang: 'php', file: 'пагинация', v: `$applications = Application::with('room')
    ->orderByDesc('created_at')
    ->paginate(10);` },
        { t: 'p', v: 'Не выводите все 5000 заявок на одной странице. `paginate(10)` — норма. Добавьте индексы на поля фильтрации: `status`, `user_id`, `created_at`.' },
        { t: 'code', lang: 'php', file: 'миграция', v: `$table->index('status');
$table->index('created_at');` },
        { t: 'h', v: 'Чистота' },
        { t: 'ul', v: [
          'Нет `dd()`, `var_dump()`, `console.log()` в финальной версии.',
          'Нет закомментированных блоков «на всякий случай».',
          'Нет мёртвых маршрутов и шаблонов.',
          '`.env` не в репозитории, `.env.example` — в репозитории.',
          'Есть README: как запустить, какие логины.',
          'Коммиты осмысленные: «Регистрация и валидация», а не «fix».'
        ] },
        { t: 'h', v: 'Стандарт кодирования' },
        { t: 'p', v: 'Laravel использует PSR-12. Можно прогнать `./vendor/bin/pint`, если он установлен. На экзамене достаточно: 4 пробела, открывающая скобка метода на новой строке, пробелы вокруг операторов, пустая строка между методами.' },
        { t: 'code', lang: 'bash', file: 'терминал', v: './vendor/bin/pint --test\n./vendor/bin/pint' },
        { t: 'h', v: 'Проверка скорости' },
        { t: 'ol', v: [
          'Откройте DevTools → Network, обновите страницу.',
          'Посмотрите размер HTML, CSS, JS и картинок.',
          'Проверьте, нет ли запросов к CDN (на экзамене они упадут).',
          'Откройте Performance и посмотрите, нет ли долгих задач.',
          'Проверьте количество SQL-запросов через Debugbar или лог.'
        ] },
        { t: 'h', v: 'Финальный чек-лист' },
        { t: 'ul', v: [
          'Проект запускается с нуля одной командой.',
          'Нет ошибок в `laravel.log` после сценария.',
          'Нет ошибок в консоли браузера.',
          'Все формы имеют серверную валидацию.',
          'Все изменения сохраняются после F5.',
          'Нет открытых паролей и ключей.',
          'Код разбит по ответственностям.',
          'Есть README и коммиты.'
        ] },
        { t: 'ok', label: 'Готово, если', v: 'Другой человек может открыть README, запустить проект и пройти сценарий без ваших подсказок.' }
      ],
      practice: [
        'Вынесите валидацию в FormRequest хотя бы для одной формы.',
        'Добавьте `with()` там, где в цикле есть связи.',
        'Удалите `dd()`, `console.log()` и мёртвый код.',
        'Напишите README с запуском и логинами.'
      ],
      quiz: [
        { q: 'Что такое проблема N+1?', options: ['Запрос на каждую строку связи', 'Ошибка 500', 'Дубли в базе'], answer: 0, explain: 'Решается `with()`.' },
        { q: 'Где указать данные администратора для проверки?', options: ['В README и в сидере (пароль — хешем)', 'В JavaScript на странице входа', 'В открытом виде в базе'], answer: 0, explain: 'Эксперт должен найти логин и пароль в README, а в базе хранится только хеш.' },
        { q: 'Зачем `paginate()`?', options: ['Для красоты', 'Чтобы не грузить все записи сразу', 'Для CSRF'], answer: 1, explain: 'Меньше данных и запросов на страницу.' }
      ]
    }
  ]
});
