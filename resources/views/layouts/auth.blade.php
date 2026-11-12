<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Вход') — ДЭ-2027</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="icon" href="{{ asset('assets/favicon.svg') }}" type="image/svg+xml">
  <link rel="stylesheet" href="{{ asset('assets/fonts/fonts.css') }}?v={{ filemtime(public_path('assets/fonts/fonts.css')) }}">
  <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}?v={{ filemtime(public_path('assets/css/app.css')) }}">
</head>
<body class="auth-body">
  <div class="auth-shell">
    <aside class="auth-aside">
      <a class="brand" href="{{ route('app') }}">
        <span class="brand-mark" aria-hidden="true">ДЭ</span>
        <span class="brand-text">
          <strong>ДЭ-2027</strong>
          <span>платформа подготовки к демоэкзамену</span>
        </span>
      </a>

      <div class="auth-aside-body">
        <span class="kicker">09.02.07 · ГИА ДЭ БУ</span>
        <h1>Учиться<br>по шагам,<br>а не по PDF</h1>
        <p class="lead">46 уроков, практика после каждого, проверка знаний и серверный прогресс. Всё, что нужно для типового проекта на Laravel.</p>

        <div class="auth-facts">
          <div><b>46</b><span>уроков</span></div>
          <div><b>9</b><span>модулей</span></div>
          <div><b>3:30</b><span>экзамен</span></div>
        </div>
      </div>

      <div class="auth-aside-foot">
        <span>Локальный сервер · SQLite · Laravel 13</span>
        <span class="stamp">цель</span>
      </div>
    </aside>

    <main class="auth-main">
      <div class="auth-card">
        @yield('content')
      </div>
    </main>
  </div>
</body>
</html>
