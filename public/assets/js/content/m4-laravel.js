window.COURSE_MODULES = window.COURSE_MODULES || [];
window.COURSE_MODULES.push({
  id: 'm4',
  num: '04',
  title: 'Laravel',
  lead: 'Маршруты, Blade, Eloquent, валидация и вход. Восемь тем, из которых собирается первый модуль экзамена.',
  lessons: [
    {
      id: 'laravel-install',
      title: 'Laravel: структура и жизненный цикл',
      minutes: 18,
      level: 'база',
      tags: ['laravel'],
      goal: 'Понять, как запрос проходит от URL до шаблона, и где лежит каждая часть проекта.',
      blocks: [
        { t: 'p', v: 'Laravel — это набор готовых решений: маршрутизация, шаблоны, работа с базой, валидация, сессии, безопасность. Вместо тысячи строк самописного PHP вы пишете контроллер и шаблон. На экзамене это экономит час-полтора.' },
        { t: 'h', v: 'Путь запроса' },
        { t: 'code', lang: 'text', file: 'схема', v: 'Браузер → public/index.php → bootstrap/app.php → routes/web.php\n        → Controller → Model (база) → Blade-шаблон → HTML → браузер' },
        { t: 'p', v: 'Все запросы входят через `public/index.php`. Дальше Laravel загружает настройки, находит маршрут, вызывает контроллер, тот берёт данные через модель и передаёт их в шаблон. Шаблон компилируется в PHP и отдаётся браузеру.' },
        { t: 'h', v: 'Структура' },
        { t: 'table', head: ['Путь', 'Назначение'], rows: [
          ['`routes/web.php`', 'Маршруты веб-приложения'],
          ['`app/Http/Controllers`', 'Контроллеры'],
          ['`app/Http/Middleware`', 'Проверки до контроллера (вход, роль)'],
          ['`app/Models`', 'Модели таблиц'],
          ['`database/migrations`', 'Схема базы'],
          ['`database/seeders`', 'Начальные данные'],
          ['`resources/views`', 'Blade-шаблоны'],
          ['`public`', 'Единственная публичная папка'],
          ['`storage`', 'Логи, кеш, загруженные файлы'],
          ['`.env`', 'Пароли, ключи, настройки базы']
        ] },
        { t: 'h', v: 'Первый запуск' },
        { t: 'code', lang: 'bash', file: 'терминал', v: 'composer create-project laravel/laravel conference\ncd conference\nphp artisan key:generate\nphp artisan migrate\nphp artisan serve' },
        { t: 'p', v: '`key:generate` создаёт `APP_KEY` — им шифруются cookie и сессии. Без ключа Laravel не запустится. `migrate` создаёт служебные таблицы (пользователи, сессии, кеш).' },
        { t: 'h', v: 'Настройки' },
        { t: 'code', lang: 'env', file: '.env', v: `APP_NAME="Конференции.РФ"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=sqlite

SESSION_DRIVER=database
CACHE_STORE=database` },
        { t: 'p', v: 'Для MySQL:`DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=conference`, `DB_USERNAME=root`, `DB_PASSWORD=`. После изменения `.env` при странном поведении выполните `php artisan config:clear`.' },
        { t: 'note', v: 'На экзамене интернет недоступен. Все библиотеки (Bootstrap, иконки, редактор) должны лежать в `public` или `vendor` проекта. CDN-ссылки не откроются — это потерянные баллы за дизайн.' },
        { t: 'h', v: 'Полезные команды' },
        { t: 'table', head: ['Команда', 'Что делает'], rows: [
          ['`php artisan serve`', 'Запускает сервер на 8000'],
          ['`php artisan route:list`', 'Показывает все маршруты'],
          ['`php artisan migrate`', 'Применяет миграции'],
          ['`php artisan migrate:fresh --seed`', 'Пересоздаёт базу и заполняет'],
          ['`php artisan make:model Room -m`', 'Модель + миграция'],
          ['`php artisan make:controller AuthController`', 'Контроллер'],
          ['`php artisan make:middleware AdminMiddleware`', 'Middleware'],
          ['`php artisan storage:link`', 'Ссылка на загруженные файлы'],
          ['`php artisan optimize:clear`', 'Сбрасывает кеши']
        ] },
        { t: 'h', v: 'Где смотреть ошибки' },
        { t: 'p', v: 'При `APP_DEBUG=true` Laravel показывает подробную страницу ошибки. Если страница белая — смотрите `storage/logs/laravel.log`. Частые причины: неверный namespace, опечатка в имени маршрута, отсутствующая колонка в базе.' },
        { t: 'h', v: 'MVC на одном примере' },
        { t: 'code', lang: 'php', file: 'routes/web.php', v: `Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');` },
        { t: 'code', lang: 'php', file: 'app/Http/Controllers/RoomController.php', v: `public function index()
{
    $rooms = Room::all();

    return view('rooms.index', compact('rooms'));
}` },
        { t: 'code', lang: 'blade', file: 'resources/views/rooms/index.blade.php', v: `@foreach ($rooms as $room)
    <article>
        <h2>{{ $room->name }}</h2>
        <p>{{ $room->description }}</p>
    </article>
@endforeach` },
        { t: 'p', v: 'Три файла — одна страница. Контроллер не содержит HTML, шаблон не содержит запросов к базе. Это разделение и проверяют в критериях «качество кода».' },
        { t: 'note', label: 'Где здесь ООП', v: 'Контроллер и модель — классы с методами и свойствами, они наследуются от `Controller` и `Model`. На защите достаточно показать `Application extends Model`, связи-методы и контроллер: это и есть объектно-ориентированная часть проекта. Фреймворк и библиотеки (Laravel, Bootstrap) закрывают требование «полный стек».' },
        { t: 'ok', label: 'Проверка', v: '`php artisan route:list` показывает ваш маршрут, страница открывается, в шаблоне выводятся данные из базы. Если да — каркас работает.' }
      ],
      practice: [
        'Создайте проект и запустите его на 8000.',
        'Выполните `php artisan route:list` и найдите стартовый маршрут `/`.',
        'Создайте контроллер `RoomController` и маршрут `/rooms`.',
        'Выведите в шаблоне три помещения из массива (пока без базы).'
      ],
      quiz: [
        { q: 'Какая папка доступна из браузера?', options: ['app', 'public', 'resources'], answer: 1, explain: 'Точка входа — public/index.php, остальное должно быть закрыто.' },
        { q: 'Что делает `php artisan key:generate`?', options: ['Создаёт APP_KEY для шифрования', 'Создаёт базу', 'Генерирует пароль админа'], answer: 0, explain: 'Без ключа не работают сессии и шифрование cookie.' },
        { q: 'Где искать ошибку, если страница белая?', options: ['В public/index.php', 'В storage/logs/laravel.log', 'В composer.json'], answer: 1, explain: 'Там Laravel пишет исключения.' }
      ]
    },
    {
      id: 'laravel-routes',
      title: 'Маршруты и контроллеры',
      minutes: 20,
      level: 'база',
      tags: ['laravel', 'маршруты'],
      goal: 'Связать URL с контроллером, передать параметры и закрыть раздел проверкой входа.',
      blocks: [
        { t: 'p', v: 'Маршрут — это правило: «такой-то метод и URL выполняет такой-то код». В Laravel все веб-маршруты лежат в `routes/web.php`. Контроллер — класс с методами под действия.' },
        { t: 'h', v: 'Простой маршрут' },
        { t: 'code', lang: 'php', file: 'routes/web.php', v: `<?php

use App\\Http\\Controllers\\HomeController;
use Illuminate\\Support\\Facades\\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');` },
        { t: 'p', v: '`->name(\'home\')` даёт маршруту имя. В шаблонах и редиректах используйте `route(\'home\')`, а не `/`: при смене URL не придётся править десять файлов.' },
        { t: 'h', v: 'Параметры' },
        { t: 'code', lang: 'php', file: 'routes/web.php', v: `Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');
Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');` },
        { t: 'p', v: 'Если имя параметра совпадает с именем модели (`{post}` и `Post $post`), Laravel сам найдёт запись по ID. Если записи нет — вернёт 404. Это называется route model binding.' },
        { t: 'code', lang: 'php', file: 'контроллер', v: `public function show(Post $post)
{
    return view('posts.show', compact('post'));
}` },
        { t: 'h', v: 'Группы и middleware' },
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
        { t: 'p', v: '`auth` — стандартный middleware Laravel: неавторизованного отправляет на `/login`. `admin` — свой, его нужно зарегистрировать в `bootstrap/app.php` (Laravel 11+) или `Kernel.php` (10-).' },
        { t: 'code', lang: 'php', file: 'bootstrap/app.php', v: `->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'admin' => \\App\\Http\\Middleware\\AdminMiddleware::class,
    ]);
})` },
        { t: 'h', v: 'Ресурсный контроллер' },
        { t: 'code', lang: 'bash', file: 'терминал', v: 'php artisan make:controller Admin/PostController --resource --model=Post' },
        { t: 'p', v: 'Команда создаёт семь методов: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`. Один маршрут регистрирует все:' },
        { t: 'code', lang: 'php', file: 'routes/web.php', v: `Route::resource('admin/posts', PostController::class);` },
        { t: 'table', head: ['Метод', 'URL', 'Действие'], rows: [
          ['GET', '/admin/posts', 'Список'],
          ['GET', '/admin/posts/create', 'Форма создания'],
          ['POST', '/admin/posts', 'Сохранить'],
          ['GET', '/admin/posts/{post}', 'Показать'],
          ['GET', '/admin/posts/{post}/edit', 'Форма редактирования'],
          ['PUT/PATCH', '/admin/posts/{post}', 'Обновить'],
          ['DELETE', '/admin/posts/{post}', 'Удалить']
        ] },
        { t: 'h', v: 'Редиректы и ответы' },
        { t: 'code', lang: 'php', file: 'контроллер', v: `return view('profile', compact('applications'));
return redirect()->route('profile')->with('success', 'Заявка отправлена.');
return redirect()->back()->withInput()->withErrors($validator);
abort(403, 'Доступ запрещён.');` },
        { t: 'p', v: '`with(\'success\', ...)` кладёт сообщение в сессию на один запрос. `withInput()` сохраняет старые значения формы, `withErrors()` — ошибки для `@error`.' },
        { t: 'h', v: 'Проверка списка' },
        { t: 'code', lang: 'bash', file: 'терминал', v: 'php artisan route:list --path=admin' },
        { t: 'p', v: 'Если маршрут не появился, проверьте кеш: `php artisan route:clear`. Если ошибка «Target class does not exist» — опечатка в namespace или имени контроллера.' },
        { t: 'note', v: 'Не делайте всю логику в замыканиях маршрутов. На экзамене за это снижают за «качество кода», а вам будет сложнее поддерживать проект.' }
      ],
      practice: [
        'Создайте маршруты для главной, регистрации и входа с именами.',
        'Создайте ресурсный контроллер и посмотрите `route:list`.',
        'Передайте параметр `{room}` и выведите название помещения.',
        'Закройте `/profile` middleware `auth` и проверьте редирект на вход.'
      ],
      quiz: [
        { q: 'Зачем `->name(\'profile\')`?', options: ['Для красоты URL', 'Чтобы ссылаться на маршрут по имени и не ломать ссылки', 'Чтобы включить кеш'], answer: 1, explain: '`route(\'profile\')` не зависит от конкретного URL.' },
        { q: 'Что произойдёт при `{post}` и отсутствующей записи?', options: ['Пустая страница', '404', '500'], answer: 1, explain: 'Route model binding автоматически вернёт 404.' },
        { q: 'Какой middleware закрывает страницы для гостей?', options: ['auth', 'csrf', 'web'], answer: 0, explain: 'Он перенаправляет на маршрут login.' }
      ]
    },
    {
      id: 'laravel-blade',
      title: 'Blade: шаблоны без копипасты',
      minutes: 22,
      level: 'база',
      tags: ['laravel', 'blade'],
      goal: 'Собрать общий layout, вывести данные, показать ошибки и не допустить XSS.',
      blocks: [
        { t: 'p', v: 'Blade — шаблонизатор Laravel. Он позволяет держать HTML в одном месте, а данные подставлять из контроллера. Главное преимущество — наследование: один layout на все страницы.' },
        { t: 'h', v: 'Layout' },
        { t: 'code', lang: 'blade', file: 'resources/views/layouts/app.blade.php', v: `<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Конференции.РФ')</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <header class="site-header">
        <a href="{{ route('home') }}" class="logo">Конференции.РФ</a>

        <nav aria-label="Основное меню">
            @auth
                @if (auth()->user()->role === 'admin')
                    <a href="{{ route('admin') }}">Панель</a>
                @else
                    <a href="{{ route('profile') }}">Мои заявки</a>
                    <a href="{{ route('application') }}">Новая заявка</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="link-button">Выйти</button>
                </form>
            @else
                <a href="{{ route('login') }}">Вход</a>
                <a href="{{ route('register') }}">Регистрация</a>
            @endauth
        </nav>
    </header>

    <main class="container py-4">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @yield('content')
    </main>

    <footer class="site-footer">Конференции.РФ © 2027</footer>

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>` },
        { t: 'p', v: '`@yield(\'title\')` — место для заголовка, `@yield(\'content\')` — для содержимого. Дочерний шаблон указывает layout и заполняет секции.' },
        { t: 'code', lang: 'blade', file: 'resources/views/profile.blade.php', v: `@extends('layouts.app')

@section('title', 'Мои заявки')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Мои заявки</h1>
        <a href="{{ route('application') }}" class="btn btn-primary">Новая заявка</a>
    </div>

    @forelse ($applications as $application)
        <article class="card mb-3">
            <div class="card-body">
                <h2 class="h5">{{ $application->room->name }}</h2>
                <span class="badge">{{ $application->status_label }}</span>
                <p class="mb-0 mt-2">
                    Дата: {{ \\Carbon\\Carbon::parse($application->start_date)->format('d.m.Y') }}
                </p>
            </div>
        </article>
    @empty
        <div class="alert alert-info">
            У вас пока нет заявок.
            <a href="{{ route('application') }}">Создайте первую</a>.
        </div>
    @endforelse
@endsection` },
        { t: 'h', v: 'Экранирование' },
        { t: 'table', head: ['Синтаксис', 'Что делает'], rows: [
          ['`{{ $value }}`', 'Выводит с экранированием HTML — безопасно'],
          ['`{!! $value !!}`', 'Выводит как HTML — только для доверенного контента'],
          ['`{{-- комментарий --}}`', 'Комментарий Blade, не попадёт в HTML'],
          ['`@csrf`', 'Скрытое поле с токеном'],
          ['`@method(\'PUT\')`', 'Подменяет метод формы']
        ] },
        { t: 'warn', label: 'XSS', v: 'Если вывести отзыв пользователя через `{!! !!}`, он сможет вставить `<script>` и украсть сессию. `{!! !!}` допустим только для HTML, который вы сгенерировали сами (например, текст из WYSIWYG после очистки).' },
        { t: 'h', v: 'Формы и ошибки' },
        { t: 'code', lang: 'blade', file: 'resources/views/register.blade.php', v: `<form method="POST" action="{{ route('register.store') }}" novalidate>
    @csrf

    <div class="mb-3">
        <label for="login" class="form-label">Логин</label>
        <input type="text" id="login" name="login"
               class="form-control @error('login') is-invalid @enderror"
               value="{{ old('login') }}"
               required>
        @error('login')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <button type="submit" class="btn btn-primary w-100">Создать пользователя</button>
</form>` },
        { t: 'p', v: '`old(\'login\')` вернёт введённое значение после ошибки. `@error(\'login\')` покажет сообщение только для этого поля. Это ровно то, что требуется в задании 2: «ошибки валидации должны отображаться на форме».' },
        { t: 'h', v: 'Циклы и условия' },
        { t: 'code', lang: 'blade', file: 'примеры', v: `@foreach ($rooms as $room)
    <option value="{{ $room->id }}" @selected(old('room_id') == $room->id)>
        {{ $room->name }}
    </option>
@endforeach

@if ($application->status === 'completed')
    <p>Мероприятие завершено</p>
@elseif ($application->status === 'assigned')
    <p>Мероприятие назначено</p>
@else
    <p>Заявка на рассмотрении</p>
@endif

@for ($i = 1; $i <= 5; $i++)
    <span class="star">{{ $i }}</span>
@endfor` },
        { t: 'h', v: 'Компоненты' },
        { t: 'p', v: 'Повторяющиеся куски выносят в компоненты: `<x-status-badge :status="$application->status" />`. Для экзамена достаточно layout + нескольких include, но компонент выглядит аккуратнее.' },
        { t: 'code', lang: 'blade', file: 'resources/views/components/status-badge.blade.php', v: `@props(['status'])

@php
    $labels = [
        'new' => 'Новая',
        'assigned' => 'Мероприятие назначено',
        'completed' => 'Завершено',
    ];
@endphp

<span class="status status--{{ $status }}">{{ $labels[$status] ?? $status }}</span>` },
        { t: 'code', lang: 'blade', file: 'использование', v: `<x-status-badge :status="$application->status" />` },
        { t: 'note', v: 'Не пишите бизнес-логику в Blade. Шаблон должен выводить, а не считать. Если появляется `@php` с запросами к базе — перенесите это в контроллер или модель.' },
        { t: 'h', v: 'Проверка' },
        { t: 'ul', v: [
          'Ошибки видны у конкретного поля, а не одним списком сверху.',
          'После ошибки введённые данные не пропадают.',
          'В шапке меняется меню для гостя, пользователя и админа.',
          'В исходном коде нет `<script>` из пользовательских данных.',
          'Пустой список показывает понятное сообщение, а не пустую страницу.'
        ] }
      ],
      practice: [
        'Сделайте layout с шапкой, футером и секцией content.',
        'Переведите на него страницы регистрации и входа.',
        'Добавьте вывод ошибок и `old()` в форму.',
        'Сделайте пустое состояние для списка заявок.'
      ],
      quiz: [
        { q: 'Чем `{{ }}` отличается от `{!! !!}`?', options: ['Ничем', '`{{ }}` экранирует HTML, `{!! !!}` — нет', '`{!! !!}` быстрее'], answer: 1, explain: 'Поэтому пользовательские данные выводят только через `{{ }}`.' },
        { q: 'Что вернёт `old(\'login\')`?', options: ['Логин текущего пользователя', 'Значение из предыдущего запроса формы', 'Логин из базы'], answer: 1, explain: 'Используется после ошибки валидации.' },
        { q: 'Какой директивой подключить layout?', options: ['@extends', '@include', '@layout'], answer: 0, explain: '`@extends(\'layouts.app\')` в начале дочернего шаблона.' }
      ]
    },
    {
      id: 'laravel-eloquent',
      title: 'Eloquent: модели и связи',
      minutes: 24,
      level: 'средний',
      tags: ['laravel', 'eloquent', 'бд'],
      goal: 'Описать таблицы моделями, настроить связи и получать данные без SQL.',
      blocks: [
        { t: 'p', v: 'Модель — класс, который представляет таблицу. Одна модель = одна таблица. Eloquent берёт на себя запросы, связи, приведение типов и аксессоры.' },
        { t: 'h', v: 'Создание' },
        { t: 'code', lang: 'bash', file: 'терминал', v: 'php artisan make:model Room -m\nphp artisan make:model Application -m' },
        { t: 'p', v: 'Флаг `-m` сразу создаёт миграцию. Имя модели в единственном числе (`Room`), таблица — во множественном (`rooms`). Laravel делает это автоматически.' },
        { t: 'h', v: 'Модель' },
        { t: 'code', lang: 'php', file: 'app/Models/Application.php', v: `<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;

class Application extends Model
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
}` },
        { t: 'p', v: '`$fillable` — белый список полей для `create()` и `update()`. Без него Laravel выбросит `MassAssignmentException`. Это защита от подмены `role` через форму.' },
        { t: 'h', v: 'Связи' },
        { t: 'code', lang: 'php', file: 'app/Models/User.php', v: `public function applications(): HasMany
{
    return $this->hasMany(Application::class);
}

public function isAdmin(): bool
{
    return $this->role === 'admin';
}` },
        { t: 'table', head: ['Связь', 'Метод', 'Пример'], rows: [
          ['Один-ко-многим', '`hasMany`', '`$user->applications`'],
          ['Обратная', '`belongsTo`', '`$application->user`'],
          ['Много-ко-многим', '`belongsToMany`', '`$post->tags`'],
          ['Один-к-одному', '`hasOne`', '`$user->profile`']
        ] },
        { t: 'h', v: 'Запросы' },
        { t: 'code', lang: 'php', file: 'примеры', v: `// Все заявки пользователя
$applications = Application::where('user_id', auth()->id())
    ->orderByDesc('created_at')
    ->get();

// Со связями, чтобы не было N+1
$applications = Application::with(['user', 'room'])
    ->where('status', 'new')
    ->get();

// Одна запись или 404
$application = Application::findOrFail($id);

// Создание
$application = Application::create([
    'user_id' => auth()->id(),
    'room_id' => $request->room_id,
    'start_date' => $request->start_date,
    'payment_type' => $request->payment_type,
    'status' => 'new',
]);

// Обновление
$application->update(['status' => 'completed']);

// Удаление
$application->delete();` },
        { t: 'h', v: 'Аксессоры для подписей' },
        { t: 'code', lang: 'php', file: 'app/Models/Application.php', v: `public const STATUS_LABELS = [
    'new' => 'Новая',
    'assigned' => 'Мероприятие назначено',
    'completed' => 'Завершено',
];

public const PAYMENT_LABELS = [
    'in_person' => 'При очном посещении',
    'sbp' => 'Переводом по системе СБП',
];

public function getStatusLabelAttribute(): string
{
    return self::STATUS_LABELS[$this->status] ?? $this->status;
}

public function getPaymentLabelAttribute(): string
{
    return self::PAYMENT_LABELS[$this->payment_type] ?? $this->payment_type;
}` },
        { t: 'p', v: 'В Blade: `{{ $application->status_label }}`. Это лучше, чем `@if` на пять веток в каждом шаблоне. Подписи хранятся в одном месте.' },
        { t: 'h', v: 'Фильтры' },
        { t: 'code', lang: 'php', file: 'AdminController.php', v: `$query = Application::with(['user', 'room']);

$query->when($request->filled('status'), function ($q) use ($request) {
    $q->where('status', $request->status);
});

$query->when($request->filled('search'), function ($q) use ($request) {
    $search = trim($request->search);
    $q->whereHas('user', fn ($u) => $u->where('fio', 'like', "%{$search}%"));
});

$applications = $query->orderByDesc('created_at')->paginate(10);` },
        { t: 'p', v: '`when()` добавляет условие только если значение есть. `whereHas()` фильтрует по связи. `paginate(10)` разбивает на страницы и добавляет ссылки.' },
        { t: 'h', v: 'Проверка владельца' },
        { t: 'code', lang: 'php', file: 'контроллер', v: `if ($application->user_id !== auth()->id()) {
    abort(403, 'Доступ запрещён.');
}` },
        { t: 'p', v: 'Скрывать чужие заявки только в запросе недостаточно: если пользователь откроет `/application/5/review` напрямую, нужна проверка владельца. Это те самые баллы за безопасность.' },
        { t: 'h', v: 'Даты' },
        { t: 'p', v: 'С `$casts = [\'start_date\' => \'date\']` поле становится объектом Carbon. В шаблоне: `{{ $application->start_date->format(\'d.m.Y\') }}`. Без каста используйте `\\Carbon\\Carbon::parse(...)`.' },
        { t: 'note', v: 'Не используйте `Model::all()` в контроллере для больших таблиц. Для админки — `paginate()`, для выпадающего списка — `orderBy(\'name\')->get([\'id\', \'name\'])`.' }
      ],
      practice: [
        'Создайте модели `Room` и `Application` с миграциями.',
        'Настройте `$fillable` и связи `hasMany` / `belongsTo`.',
        'Создайте заявку через `Application::create()` в tinker.',
        'Выведите заявки с `with(\'room\')` и проверьте, что название помещения доступно.'
      ],
      quiz: [
        { q: 'Зачем `$fillable`?', options: ['Для красоты', 'Чтобы разрешить массовое заполнение только безопасных полей', 'Чтобы ускорить запросы'], answer: 1, explain: 'Защита от подмены role и других служебных полей.' },
        { q: 'Какая связь у пользователя с заявками?', options: ['belongsTo', 'hasMany', 'belongsToMany'], answer: 1, explain: 'У одного пользователя много заявок.' },
        { q: 'Что решает `with(\'room\')`?', options: ['Проблему N+1', 'Ошибку 419', 'Дубли'], answer: 0, explain: 'Связь загружается одним запросом.' }
      ]
    },
    {
      id: 'laravel-migrations',
      title: 'Миграции и сидеры',
      minutes: 18,
      level: 'средний',
      tags: ['laravel', 'бд'],
      goal: 'Создать схему и демо-данные так, чтобы проект поднимался одной командой на любой машине.',
      blocks: [
        { t: 'p', v: 'Миграция — это версия схемы базы в коде. Вместо того чтобы вручную создавать таблицы в phpMyAdmin, вы пишете файл и применяете его командой. На экзамене это обязательный навык: эксперт может пересоздать базу.' },
        { t: 'h', v: 'Создание' },
        { t: 'code', lang: 'bash', file: 'терминал', v: 'php artisan make:migration create_rooms_table\nphp artisan make:migration create_applications_table' },
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
        { t: 'note', v: 'Порядок важен: таблица `applications` ссылается на `users` и `rooms`, значит создаётся после них. Laravel сортирует миграции по имени файла с датой — соблюдайте хронологию.' },
        { t: 'h', v: 'Изменение существующей таблицы' },
        { t: 'code', lang: 'bash', file: 'терминал', v: 'php artisan make:migration add_image_to_posts_table --table=posts' },
        { t: 'code', lang: 'php', file: 'миграция', v: `public function up(): void
{
    Schema::table('posts', function (Blueprint $table) {
        $table->string('image')->nullable()->after('content');
    });
}

public function down(): void
{
    Schema::table('posts', function (Blueprint $table) {
        $table->dropColumn('image');
    });
}` },
        { t: 'p', v: 'Не редактируйте старые миграции, если база уже применялась: создайте новую. `down()` должен откатывать изменения — это проверяют при `migrate:rollback`.' },
        { t: 'h', v: 'Сидер' },
        { t: 'code', lang: 'php', file: 'database/seeders/DatabaseSeeder.php', v: `public function run(): void
{
    $this->call([
        RoomSeeder::class,
        UserSeeder::class,
    ]);
}` },
        { t: 'code', lang: 'php', file: 'database/seeders/UserSeeder.php', v: `User::updateOrCreate(
    ['login' => 'Conf2027'],
    [
        'fio' => 'Администратор Портала',
        'phone' => '8(999)000-00-00',
        'email' => 'admin@conferences.ru',
        'password' => Hash::make('Demo77'),
        'role' => 'admin',
    ]
);` },
        { t: 'p', v: '`updateOrCreate` не создаёт дубль при повторном запуске. Пароль обязательно через `Hash::make`.' },
        { t: 'h', v: 'Демо-данные' },
        { t: 'code', lang: 'php', file: 'RoomSeeder.php', v: `$rooms = [
    ['name' => 'Большая конференц-аудитория', 'type' => 'auditory', 'capacity' => 120],
    ['name' => 'Коворкинг-зона «Вектор»', 'type' => 'coworking', 'capacity' => 40],
    ['name' => 'Кинозал «Пленум»', 'type' => 'cinema', 'capacity' => 90],
];

foreach ($rooms as $room) {
    Room::updateOrCreate(['name' => $room['name']], $room);
}` },
        { t: 'p', v: 'Заявки для демонстрации тоже полезны: эксперт увидит, что список и фильтры работают, даже если не создаст данные сам. Сделайте 3–5 заявок с разными статусами.' },
        { t: 'h', v: 'Команды' },
        { t: 'table', head: ['Команда', 'Когда'], rows: [
          ['`php artisan migrate`', 'Применить новые миграции'],
          ['`php artisan migrate:fresh`', 'Удалить все таблицы и создать заново'],
          ['`php artisan migrate:fresh --seed`', 'Пересоздать и заполнить'],
          ['`php artisan migrate:rollback`', 'Откатить последний пакет'],
          ['`php artisan db:seed`', 'Запустить сидеры'],
          ['`php artisan db:seed --class=UserSeeder`', 'Один сидер']
        ] },
        { t: 'warn', label: 'Осторожно', v: '`migrate:fresh` удаляет все данные. На экзамене это нормально, если вы готовы заново заполнить базу. Не запускайте на реальном проекте.' },
        { t: 'h', v: 'Проверка с нуля' },
        { t: 'p', v: 'Главный тест перед сдачей: удалить базу (или `database.sqlite`), выполнить `php artisan migrate:fresh --seed` и убедиться, что приложение работает и админ входит. Если нет — вы сдаёте проект, который живёт только на вашей машине.' }
      ],
      practice: [
        'Создайте миграции `rooms` и `applications` с внешними ключами.',
        'Заполните `rooms` сидером из трёх помещений.',
        'Создайте админа `Conf2027` / `Demo77` через сидер.',
        'Удалите базу, выполните `migrate:fresh --seed` и проверьте вход.'
      ],
      quiz: [
        { q: 'Зачем нужен `down()` в миграции?', options: ['Для отката изменений', 'Для заполнения данными', 'Для красоты'], answer: 0, explain: '`migrate:rollback` вызывает именно его.' },
        { q: 'Что сделает `migrate:fresh --seed`?', options: ['Добавит новые таблицы', 'Удалит все таблицы, создаст заново и заполнит', 'Сделает бэкап'], answer: 1, explain: 'Полный сброс схемы и сидеры.' },
        { q: 'Почему пароль в сидере через `Hash::make`?', options: ['Чтобы он был длиннее', 'Чтобы в базе не было открытого пароля', 'Чтобы работал `unique`'], answer: 1, explain: 'Требование безопасности и критерий оценки.' }
      ]
    },
    {
      id: 'laravel-validation',
      title: 'Валидация с русскими сообщениями',
      minutes: 22,
      level: 'средний',
      tags: ['laravel', 'валидация'],
      goal: 'Проверить все поля по требованиям КИМ и показать ошибки у каждого поля.',
      blocks: [
        { t: 'p', v: 'Валидация — это не «проверка на всякий случай», а формализованное требование задания. Логин — латиница и цифры от 6 символов, пароль — от 8, ФИО — кириллица, телефон — `8(XXX)XXX-XX-XX`, email — корректный. Всё это делается правилами Laravel.' },
        { t: 'h', v: 'Базовый вариант' },
        { t: 'code', lang: 'php', file: 'AuthController.php', v: `$data = $request->validate([
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
    'phone.regex' => 'Телефон в формате 8(XXX)XXX-XX-XX.',
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
]);` },
        { t: 'p', v: '`$request->validate()` при ошибке сам делает redirect назад с ошибками и старыми значениями. В контроллере остаётся только успешный путь. Это короче, чем `Validator::make()` с ручной проверкой `fails()`.' },
        { t: 'note', label: 'Про регулярку ФИО', v: 'В КИМ сказано «символы кириллицы и пробелы». Дефис добавлен для двойных фамилий — уточните по своему комплекту. Главное, чтобы правило совпадало с формулировкой задания.' },
        { t: 'h', v: 'Правила, которые нужны на экзамене' },
        { t: 'table', head: ['Правило', 'Смысл', 'Пример'], rows: [
          ['`required`', 'Поле обязательно', 'все поля'],
          ['`string`', 'Строка', 'ФИО, логин'],
          ['`min:8`', 'Минимум символов', 'пароль'],
          ['`max:255`', 'Максимум', 'все строки'],
          ['`regex:/.../`', 'Регулярное выражение', 'логин, телефон, ФИО'],
          ['`unique:users,login`', 'Нет дубля в таблице', 'логин, email'],
          ['`exists:rooms,id`', 'Запись существует', 'room_id'],
          ['`in:in_person,sbp`', 'Одно из значений', 'оплата, статус'],
          ['`date`', 'Корректная дата', 'start_date'],
          ['`after_or_equal:today`', 'Не в прошлом', 'дата конференции'],
          ['`integer`', 'Целое', 'рейтинг'],
          ['`between:1,5`', 'Диапазон', 'рейтинг'],
          ['`image`', 'Файл-картинка', 'изображение'],
          ['`mimes:jpg,png,webp`', 'Расширения', 'изображение'],
          ['`max:2048`', 'Размер в КБ', 'изображение']
        ] },
        { t: 'h', v: 'Заявка' },
        { t: 'code', lang: 'php', file: 'ApplicationController.php', v: `$data = $request->validate([
    'room_id' => ['required', 'exists:rooms,id'],
    'start_date' => ['required', 'date', 'after_or_equal:today'],
    'payment_type' => ['required', 'in:in_person,sbp'],
], [
    'room_id.required' => 'Выберите помещение.',
    'room_id.exists' => 'Выбранное помещение не найдено.',
    'start_date.required' => 'Укажите дату начала конференции.',
    'start_date.after_or_equal' => 'Дата не может быть в прошлом.',
    'payment_type.required' => 'Выберите способ оплаты.',
    'payment_type.in' => 'Недопустимый способ оплаты.',
]);

Application::create([
    ...$data,
    'user_id' => auth()->id(),
    'status' => 'new',
]);` },
        { t: 'p', v: 'Для задания 1 поле помещения — текстовое (`room_name`), и его нужно проверить на существование в таблице. Для задания 2 — выпадающий список с `room_id`. Не перепутайте: это разные требования.' },
        { t: 'h', v: 'FormRequest для больших форм' },
        { t: 'code', lang: 'bash', file: 'терминал', v: 'php artisan make:request StoreApplicationRequest' },
        { t: 'code', lang: 'php', file: 'app/Http/Requests/StoreApplicationRequest.php', v: `public function authorize(): bool
{
    return auth()->check();
}

public function rules(): array
{
    return [
        'room_id' => ['required', 'exists:rooms,id'],
        'start_date' => ['required', 'date', 'after_or_equal:today'],
        'payment_type' => ['required', 'in:in_person,sbp'],
    ];
}

public function messages(): array
{
    return [
        'room_id.required' => 'Выберите помещение.',
        'start_date.after_or_equal' => 'Дата не может быть в прошлом.',
    ];
}` },
        { t: 'p', v: 'В контроллере: `public function store(StoreApplicationRequest $request)`. Валидация выполняется до метода. Удобно, но для экзамена достаточно `$request->validate()`.' },
        { t: 'h', v: 'Ошибки в шаблоне' },
        { t: 'code', lang: 'blade', file: 'форма', v: `@if ($errors->any())
    <div class="alert alert-danger">
        Проверьте поля формы.
    </div>
@endif

<input name="login" value="{{ old('login') }}"
       class="form-control @error('login') is-invalid @enderror">
@error('login')
    <div class="invalid-feedback">{{ $message }}</div>
@enderror` },
        { t: 'p', v: 'Не выводите все ошибки одним списком, если задание требует «ошибки на форме». Сообщение должно стоять рядом с полем.' },
        { t: 'h', v: 'Проверка вручную' },
        { t: 'ul', v: [
          'Отправьте пустую форму — все поля подсвечены.',
          'Введите логин из 5 символов — ошибка формата.',
          'Введите существующий логин — ошибка уникальности.',
          'Введите дату вчерашним числом — ошибка даты.',
          'После ошибки поля сохраняют введённое.'
        ] }
      ],
      practice: [
        'Добавьте валидацию регистрации со всеми сообщениями из примера.',
        'Проверьте каждое правило, специально вводя неверные данные.',
        'Сделайте валидацию формы заявки с датой не в прошлом.',
        'Выведите ошибки у полей через `@error`.'
      ],
      quiz: [
        { q: 'Что делает `$request->validate()` при ошибке?', options: ['Возвращает JSON', 'Редиректит назад с ошибками и старыми значениями', 'Удаляет запись'], answer: 1, explain: 'Поэтому в контроллере код после валидации выполняется только при успехе.' },
        { q: 'Какое правило проверит, что помещение есть в таблице?', options: ['unique:rooms,id', 'exists:rooms,id', 'in:rooms'], answer: 1, explain: '`exists` проверяет наличие записи.' },
        { q: 'Как вывести ошибку конкретного поля в Blade?', options: ['@error(\'login\')', '@if($errors)', '@csrf'], answer: 0, explain: 'Директива `@error` показывает сообщение только для поля.' }
      ]
    },
    {
      id: 'laravel-auth',
      title: 'Регистрация и вход вручную',
      minutes: 28,
      level: 'средний',
      tags: ['laravel', 'авторизация', 'безопасность'],
      goal: 'Сделать полный цикл: регистрация → вход → роли → выход, без Breeze и лишних пакетов.',
      blocks: [
        { t: 'p', v: 'На экзамене не нужен Laravel Breeze: он тянет десятки файлов и свои представления. Проще сделать три метода в одном контроллере — так вы контролируете каждую строку и точно попадаете в требования.' },
        { t: 'h', v: 'Таблица пользователей' },
        { t: 'code', lang: 'php', file: 'миграция users', v: `Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('fio');
    $table->string('login')->unique();
    $table->string('phone');
    $table->string('email')->unique();
    $table->string('password');
    $table->enum('role', ['admin', 'user'])->default('user');
    $table->rememberToken();
    $table->timestamps();
});` },
        { t: 'p', v: 'Стандартная таблица Laravel содержит `name` и `email`. Мы добавляем `fio`, `login`, `phone`, `role`. Поле `name` можно удалить или не использовать. Провайдер аутентификации по умолчанию ищет пользователя по `email`, но `Auth::attempt` умеет работать с любым набором полей.' },
        { t: 'h', v: 'Модель' },
        { t: 'code', lang: 'php', file: 'app/Models/User.php', v: `protected $fillable = [
    'fio', 'login', 'phone', 'email', 'password', 'role',
];

protected $hidden = [
    'password', 'remember_token',
];

public function isAdmin(): bool
{
    return $this->role === 'admin';
}` },
        { t: 'p', v: 'Пароль хешируется в контроллере через `Hash::make()`. Альтернатива — добавить в модель `protected $casts = [\'password\' => \'hashed\']` и передавать пароль как есть; тогда `Hash::make()` вызывать не нужно. Выберите один способ, не оба.' },
        { t: 'h', v: 'Контроллер' },
        { t: 'code', lang: 'php', file: 'app/Http/Controllers/AuthController.php', v: `<?php

namespace App\\Http\\Controllers;

use App\\Models\\User;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\Auth;
use Illuminate\\Support\\Facades\\Hash;

class AuthController extends Controller
{
    public function register()
    {
        return view('register');
    }

    public function storeRegister(Request $request)
    {
        $data = $request->validate([
            'fio' => ['required', 'regex:/^[А-ЯЁа-яё\\s-]+$/u', 'max:255'],
            'login' => ['required', 'regex:/^[a-zA-Z0-9]{6,}$/', 'unique:users,login'],
            'phone' => ['required', 'regex:/^8\\(\\d{3}\\)\\d{3}-\\d{2}-\\d{2}$/'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8'],
        ], [
            'login.regex' => 'Логин: латиница и цифры, не менее 6 символов.',
            'login.unique' => 'Такой логин уже занят.',
            'phone.regex' => 'Телефон в формате 8(XXX)XXX-XX-XX.',
            'password.min' => 'Пароль должен быть не менее 8 символов.',
        ]);

        User::create([
            ...$data,
            'password' => Hash::make($data['password']),
            'role' => 'user',
        ]);

        return redirect()->route('login')
            ->with('success', 'Пользователь создан. Войдите в систему.');
    }

    public function login()
    {
        return view('login');
    }

    public function storeLogin(Request $request)
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return auth()->user()->isAdmin()
                ? redirect()->route('admin')
                : redirect()->route('profile');
        }

        return back()
            ->withErrors(['login' => 'Неверный логин или пароль.'])
            ->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}` },
        { t: 'p', v: '`Auth::attempt` сам найдёт пользователя по `login`, проверит `password` через хеш и запишет его в сессию. `session()->regenerate()` защищает от фиксации сессии. `invalidate()` и `regenerateToken()` корректно завершают сессию.' },
        { t: 'h', v: 'Маршруты' },
        { t: 'code', lang: 'php', file: 'routes/web.php', v: `Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::post('/register', [AuthController::class, 'storeRegister'])->name('register.store');

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'storeLogin'])->name('login.store');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');` },
        { t: 'note', v: 'В Laravel middleware `auth` по умолчанию перенаправляет на маршрут с именем `login`. Поэтому важно назвать маршрут входа именно `login`, иначе гостя выбросит на 404.' },
        { t: 'h', v: 'Выход через форму' },
        { t: 'code', lang: 'blade', file: 'шапка', v: `<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit" class="link-button">Выйти</button>
</form>` },
        { t: 'p', v: 'Выход должен быть POST с CSRF. GET-ссылка на выход позволяет злоумышленнику разлогинить пользователя через картинку. Это мелочь, но её замечают на защите.' },
        { t: 'h', v: 'Админский вход' },
        { t: 'p', v: 'В КИМ администратор входит по `Conf2027` / `Demo77`. Можно сделать отдельную страницу `/admin/login` и проверить роль после `Auth::attempt`:' },
        { t: 'code', lang: 'php', file: 'AdminController.php', v: `public function storeLogin(Request $request)
{
    $credentials = $request->validate([
        'login' => ['required', 'string'],
        'password' => ['required', 'string'],
    ]);

    if (Auth::attempt($credentials)) {
        if (!auth()->user()->isAdmin()) {
            Auth::logout();
            return back()->with('error', 'Доступ только для администратора.');
        }

        $request->session()->regenerate();
        return redirect()->route('admin');
    }

    return back()->with('error', 'Неверный логин или пароль администратора.');
}` },
        { t: 'p', v: 'Альтернатива — пускать админа через обычный вход и перенаправлять по роли. Проверьте формулировку задания: если просят отдельную панель с отдельным входом, делайте отдельный маршрут.' },
        { t: 'h', v: 'Middleware администратора' },
        { t: 'code', lang: 'bash', file: 'терминал', v: 'php artisan make:middleware AdminMiddleware' },
        { t: 'code', lang: 'php', file: 'app/Http/Middleware/AdminMiddleware.php', v: `public function handle(Request $request, Closure $next): Response
{
    if (!auth()->check() || !auth()->user()->isAdmin()) {
        abort(403, 'Доступ запрещён.');
    }

    return $next($request);
}` },
        { t: 'code', lang: 'php', file: 'bootstrap/app.php', v: `->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'admin' => \\App\\Http\\Middleware\\AdminMiddleware::class,
    ]);
})` },
        { t: 'h', v: 'Чек-лист безопасности' },
        { t: 'ul', v: [
          'Пароль хранится только в виде хеша.',
          'Регистрация не позволяет выбрать роль `admin`.',
          '`/admin` закрыт middleware, а не только ссылкой в меню.',
          'Чужие заявки недоступны по прямому URL.',
          'Выход — POST с `@csrf`.',
          'Формы входа и регистрации имеют `@csrf`.',
          'Сообщение об ошибке входа не подсказывает, существует ли логин.'
        ] },
        { t: 'warn', label: 'Не делайте так', v: 'Не сравнивайте пароль строками (`$user->password === $request->password`), не храните `role` в скрытом поле формы и не доверяйте `user_id` из запроса — берите `auth()->id()`.' }
      ],
      practice: [
        'Сделайте регистрацию с валидацией и хешированием пароля.',
        'Сделайте вход через `Auth::attempt` и выход через POST.',
        'Закройте `/profile` и `/application` middleware `auth`.',
        'Создайте админа сидером и проверьте, что `/admin` ему доступен, а пользователю — 403.'
      ],
      quiz: [
        { q: 'Почему после входа нужен `session()->regenerate()`?', options: ['Для скорости', 'Для защиты от фиксации сессии', 'Для запоминания логина'], answer: 1, explain: 'Новый ID сессии исключает использование старого.' },
        { q: 'Где брать ID пользователя при создании заявки?', options: ['Из скрытого поля формы', 'Из `auth()->id()`', 'Из URL'], answer: 1, explain: 'Данным формы доверять нельзя.' },
        { q: 'Что будет, если админский раздел закрыт только ссылкой в меню?', options: ['Ничего', 'Пользователь откроет URL напрямую', 'Сайт упадёт'], answer: 1, explain: 'Нужен middleware или проверка роли в каждом действии.' }
      ]
    },
    {
      id: 'laravel-admin-crud',
      title: 'CRUD, файлы, статусы и фильтры',
      minutes: 30,
      level: 'средний',
      tags: ['laravel', 'crud', 'админка'],
      goal: 'Собрать админку: список, создание, редактирование, удаление, загрузка картинки, смена статуса и поиск.',
      blocks: [
        { t: 'p', v: 'Это ядро первого и третьего заданий. Если вы умеете делать CRUD и менять статус, вы закрываете большую часть баллов. Схема одинаковая для постов, помещений, автомобилей и заявок.' },
        { t: 'h', v: 'Ресурсный контроллер' },
        { t: 'code', lang: 'bash', file: 'терминал', v: 'php artisan make:controller Admin/PostController --resource --model=Post\nphp artisan make:request StorePostRequest\nphp artisan make:request UpdatePostRequest' },
        { t: 'code', lang: 'php', file: 'routes/web.php', v: `Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    Route::resource('posts', PostController::class);
});` },
        { t: 'p', v: '`->name(\'admin.\')` добавляет префикс к именам: `admin.posts.index`, `admin.posts.create` и т.д. В шаблонах используйте `route(\'admin.posts.index\')`.' },
        { t: 'h', v: 'Список с фильтром и поиском' },
        { t: 'code', lang: 'php', file: 'Admin/PostController.php', v: `public function index(Request $request)
{
    $posts = Post::with('category')
        ->when($request->filled('category_id'), fn ($q) =>
            $q->where('category_id', $request->category_id))
        ->when($request->filled('search'), fn ($q) =>
            $q->where('title', 'like', '%' . $request->search . '%'))
        ->orderByDesc('created_at')
        ->paginate(10)
        ->withQueryString();

    $categories = Category::orderBy('name')->get();

    return view('admin.posts.index', compact('posts', 'categories'));
}` },
        { t: 'p', v: '`withQueryString()` сохраняет фильтры в ссылках пагинации. Без него страница 2 сбросит поиск.' },
        { t: 'code', lang: 'blade', file: 'admin/posts/index.blade.php', v: `<form method="GET" action="{{ route('admin.posts.index') }}" class="row g-2 mb-3">
    <div class="col-md-4">
        <input type="search" name="search" class="form-control"
               value="{{ request('search') }}" placeholder="Поиск по заголовку">
    </div>
    <div class="col-md-4">
        <select name="category_id" class="form-select">
            <option value="">Все категории</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <button class="btn btn-primary">Найти</button>
        <a href="{{ route('admin.posts.index') }}" class="btn btn-outline-secondary">Сбросить</a>
    </div>
</form>

@forelse ($posts as $post)
    <article class="admin-card">
        <h2>{{ $post->title }}</h2>
        <p>{{ $post->category->name }}</p>
        <a href="{{ route('admin.posts.edit', $post) }}">Редактировать</a>
        <form method="POST" action="{{ route('admin.posts.destroy', $post) }}"
              onsubmit="return confirm('Удалить пост?')">
            @csrf
            @method('DELETE')
            <button type="submit">Удалить</button>
        </form>
    </article>
@empty
    <p>Ничего не найдено.</p>
@endforelse

{{ $posts->links() }}` },
        { t: 'h', v: 'Создание и загрузка файла' },
        { t: 'code', lang: 'php', file: 'Admin/PostController.php', v: `public function store(StorePostRequest $request)
{
    $data = $request->validated();

    if ($request->hasFile('image')) {
        $data['image'] = $request->file('image')->store('posts', 'public');
    }

    Post::create($data);

    return redirect()->route('admin.posts.index')
        ->with('success', 'Пост создан.');
}` },
        { t: 'code', lang: 'php', file: 'StorePostRequest.php', v: `public function rules(): array
{
    return [
        'title' => ['required', 'string', 'max:255'],
        'content' => ['required', 'string'],
        'category_id' => ['required', 'exists:categories,id'],
        'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
    ];
}` },
        { t: 'p', v: '`store(\'posts\', \'public\')` кладёт файл в `storage/app/public/posts` и возвращает относительный путь. В шаблоне: `asset(\'storage/\' . $post->image)`. Один раз выполните `php artisan storage:link`.' },
        { t: 'h', v: 'Редактирование' },
        { t: 'code', lang: 'php', file: 'Admin/PostController.php', v: `public function update(UpdatePostRequest $request, Post $post)
{
    $data = $request->validated();

    if ($request->hasFile('image')) {
        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }

        $data['image'] = $request->file('image')->store('posts', 'public');
    }

    $post->update($data);

    return redirect()->route('admin.posts.index')
        ->with('success', 'Пост обновлён.');
}` },
        { t: 'code', lang: 'blade', file: 'форма', v: `<form method="POST" action="{{ route('admin.posts.update', $post) }}"
      enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <input type="text" name="title" value="{{ old('title', $post->title) }}">

    @if ($post->image)
        <img src="{{ asset('storage/' . $post->image) }}" alt="" width="160">
        <label>
            <input type="checkbox" name="remove_image" value="1">
            Удалить изображение
        </label>
    @endif

    <input type="file" name="image" accept="image/*">
    <button type="submit">Сохранить</button>
</form>` },
        { t: 'h', v: 'Смена статуса' },
        { t: 'code', lang: 'php', file: 'AdminController.php', v: `public function updateStatus(Request $request, Application $application)
{
    $data = $request->validate([
        'status' => ['required', 'in:new,assigned,completed'],
    ], [
        'status.in' => 'Недопустимый статус.',
    ]);

    $application->update($data);

    return back()->with('success', 'Статус обновлён: ' . $application->status_label);
}` },
        { t: 'code', lang: 'blade', file: 'админка', v: `<form method="POST" action="{{ route('admin.status', $application) }}" class="status-form">
    @csrf
    <select name="status" class="form-select form-select-sm">
        @foreach (\\App\\Models\\Application::STATUS_LABELS as $value => $label)
            <option value="{{ $value }}" @selected($application->status === $value)>
                {{ $label }}
            </option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-sm btn-primary">Сохранить</button>
</form>` },
        { t: 'p', v: 'Статус должен меняться только через POST/PATCH и только у администратора. Не делайте ссылку `?status=completed` — её можно подделать и она попадёт в историю.' },
        { t: 'h', v: 'Удаление' },
        { t: 'code', lang: 'php', file: 'контроллер', v: `public function destroy(Post $post)
{
    if ($post->image) {
        Storage::disk('public')->delete($post->image);
    }

    $post->delete();

    return back()->with('success', 'Пост удалён.');
}` },
        { t: 'p', v: 'Удаляйте связанные файлы, иначе `storage` будет расти. Подтверждение удаления делайте через `confirm()` — это требование задания 3 про интерактивность.' },
        { t: 'h', v: 'Что проверить' },
        { t: 'ul', v: [
          'Список показывает данные из базы, а не массив в контроллере.',
          'Фильтр и поиск работают вместе и сохраняются при пагинации.',
          'Создание и редактирование показывают ошибки у полей.',
          'Картинка загружается, отображается и удаляется.',
          'Статус меняется и сохраняется после F5.',
          'Удаление спрашивает подтверждение.',
          'Пустой список выглядит нормально.'
        ] }
      ],
      practice: [
        'Сделайте CRUD для помещений или постов.',
        'Добавьте загрузку изображения и вывод его в списке.',
        'Сделайте смену статуса заявки через POST.',
        'Добавьте фильтр по статусу и поиск по ФИО.'
      ],
      quiz: [
        { q: 'Зачем `@method(\'PUT\')` в форме редактирования?', options: ['HTML-формы умеют только GET/POST', 'Для CSRF', 'Для загрузки файла'], answer: 0, explain: 'Blade подменяет метод через скрытое поле.' },
        { q: 'Где окажется файл после `store(\'posts\', \'public\')`?', options: ['public/posts', 'storage/app/public/posts', 'storage/posts'], answer: 1, explain: 'Нужен `storage:link` для доступа из браузера.' },
        { q: 'Что сохраняет фильтры в пагинации?', options: ['`withQueryString()`', '`paginate()`', '`orderBy()`'], answer: 0, explain: 'Иначе ссылки на страницы потеряют параметры.' }
      ]
    }
  ]
});
