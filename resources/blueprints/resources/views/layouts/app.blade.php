<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', '@@PROJECT@@')</title>
  <meta name="description" content="@@PROJECT@@ — информационная система для @@PURPOSE@@. Учебный проект по КИМ 09.02.07-3-2027.">
  <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  <script src="{{ asset('js/app.js') }}" defer></script>
</head>
<body>
  <a class="skip-link" href="#main">Перейти к содержанию</a>

  <header class="site-header">
    <div class="wrap header-inner">
      <a class="brand" href="{{ route('home') }}">
        <span class="brand-mark" aria-hidden="true">@@INITIALS@@</span>
        <span class="brand-text">
          <strong>@@PROJECT@@</strong>
          <span>заявки онлайн</span>
        </span>
      </a>

      <nav class="site-nav" aria-label="Основная навигация">
        <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'is-active' : '' }}">Главная</a>
        @auth
          <a href="{{ route('bookings.index') }}" class="{{ request()->routeIs('bookings.*') ? 'is-active' : '' }}">Мои заявки</a>
          @if (auth()->user()->isAdmin())
            <a href="{{ route('admin.bookings.index') }}" class="{{ request()->routeIs('admin.*') ? 'is-active' : '' }}">Админка</a>
          @endif
        @endauth
      </nav>

      <div class="header-user">
        @auth
          <span class="who">{{ auth()->user()->firstName() }}</span>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="link-btn">Выйти</button>
          </form>
        @else
          <a class="link-btn" href="{{ route('login') }}">Войти</a>
          <a class="btn btn-small" href="{{ route('register') }}">Регистрация</a>
        @endauth
      </div>
    </div>
  </header>

  <main id="main">
    @if (session('success'))
      <div class="wrap">
        <div class="alert alert--success" role="status">{{ session('success') }}</div>
      </div>
    @endif

    @yield('content')
  </main>

  <footer class="site-footer">
    <div class="wrap footer-inner">
      <div>
        <strong>@@PROJECT@@</strong>
        <p>Информационная система для @@PURPOSE@@.<br>Ежедневно с 9:00 до 20:00, +7 (343) 200-18-14.</p>
      </div>
      <div class="footer-note">
        <p>Учебный проект по КИМ 09.02.07-3-2027. Названия, даты и отзывы — демонстрационные.</p>
        <p>Laravel · SQLite · Blade</p>
      </div>
    </div>
  </footer>
</body>
</html>
