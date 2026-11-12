<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Админка') — ДЭ-2027</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="icon" href="{{ asset('assets/favicon.svg') }}" type="image/svg+xml">
  <link rel="stylesheet" href="{{ asset('assets/fonts/fonts.css') }}?v={{ filemtime(public_path('assets/fonts/fonts.css')) }}">
  <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}?v={{ filemtime(public_path('assets/css/app.css')) }}">
</head>
<body class="admin-body">
  <div class="admin-shell">
    <aside class="admin-rail">
      <a class="brand" href="{{ route('app') }}">
        <span class="brand-mark" aria-hidden="true">ДЭ</span>
        <span class="brand-text">
          <strong>ДЭ-2027</strong>
          <span>админка платформы</span>
        </span>
      </a>

      <nav class="admin-nav" aria-label="Админка">
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">Обзор</a>
        <a href="{{ route('admin.students') }}" class="{{ request()->routeIs('admin.students', 'admin.student') ? 'is-active' : '' }}">Студенты</a>
        <a href="{{ route('admin.lessons') }}" class="{{ request()->routeIs('admin.lessons*') ? 'is-active' : '' }}">Уроки</a>
        <a href="{{ route('admin.labs') }}" class="{{ request()->routeIs('admin.labs*') ? 'is-active' : '' }}">Лабораторные</a>
        <a href="{{ route('admin.capstone') }}" class="{{ request()->routeIs('admin.capstone*') ? 'is-active' : '' }}">Капстоун</a>
      </nav>

      <div class="admin-rail-foot">
        <span>{{ auth()->user()->displayName() }}</span>
        <a href="{{ route('app') }}">← К курсу</a>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="link-btn">Выйти</button>
        </form>
      </div>
    </aside>

    <main class="admin-main">
      <header class="admin-top">
        <div>
          <span class="kicker">Управление платформой</span>
          <h1>@yield('heading', 'Админка')</h1>
        </div>
        <a href="{{ route('app') }}" class="btn btn-sm btn-ghost">Открыть курс</a>
      </header>

      @if (session('success'))
        <div class="alert alert--success">{{ session('success') }}</div>
      @endif

      @yield('content')
    </main>
  </div>
</body>
</html>
