<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Практикум') — ДЭ-2027</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="icon" href="{{ asset('assets/favicon.svg') }}" type="image/svg+xml">
  <link rel="stylesheet" href="{{ asset('assets/fonts/fonts.css') }}?v={{ filemtime(public_path('assets/fonts/fonts.css')) }}">
  <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}?v={{ filemtime(public_path('assets/css/app.css')) }}">
</head>
<body class="workshop-body">
  <header class="workshop-top">
    <a class="brand" href="{{ route('app') }}">
      <span class="brand-mark" aria-hidden="true">ДЭ</span>
      <span class="brand-text">
        <strong>ДЭ-2027</strong>
        <span>практикум</span>
      </span>
    </a>

    <nav class="workshop-nav" aria-label="Практикум">
      <a href="{{ route('plan.index') }}" class="{{ request()->routeIs('plan.*') ? 'is-active' : '' }}">План</a>
      <a href="{{ route('drills.index') }}" class="{{ request()->routeIs('drills.*') ? 'is-active' : '' }}">Тренажёр</a>
      <a href="{{ route('variants.index') }}" class="{{ request()->routeIs('variants.*') ? 'is-active' : '' }}">Варианты ТЗ</a>
      <a href="{{ route('topic.index') }}" class="{{ request()->routeIs('topic.*') ? 'is-active' : '' }}">Своя тема</a>
      <a href="{{ route('blueprint.index') }}" class="{{ request()->routeIs('blueprint.*') ? 'is-active' : '' }}">Проект по теме</a>
      <a href="{{ route('exam.index') }}" class="{{ request()->routeIs('exam.*') ? 'is-active' : '' }}">Экзамен</a>
      <a href="{{ route('build.index') }}" class="{{ request()->routeIs('build.*') ? 'is-active' : '' }}">Сборка</a>
      <a href="{{ route('cases.index') }}" class="{{ request()->routeIs('cases.*') ? 'is-active' : '' }}">Разборы</a>
      <a href="{{ route('labs.index') }}" class="{{ request()->routeIs('labs.*') ? 'is-active' : '' }}">Лабораторные</a>
      <a href="{{ route('project.show') }}" class="{{ request()->routeIs('project.*') ? 'is-active' : '' }}">Мой проект</a>
      <a href="{{ route('capstone.index') }}" class="{{ request()->routeIs('capstone.*') ? 'is-active' : '' }}">Проверка проекта</a>
      <a href="{{ route('app') }}">← К курсу</a>
    </nav>

    <div class="workshop-user">
      <span>{{ auth()->user()->displayName() }}</span>
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="link-btn">Выйти</button>
      </form>
    </div>
  </header>

  <main class="workshop-main">
    @yield('content')
  </main>

  <script src="{{ asset('assets/js/lab.js') }}?v={{ filemtime(public_path('assets/js/lab.js')) }}"></script>
</body>
</html>
