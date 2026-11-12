<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ДЭ-2027 — платформа подготовки к демоэкзамену</title>
  <meta name="description" content="Пошаговый курс от нуля до типового проекта демоэкзамена 09.02.07: Laravel, база данных, админка, мобильный дизайн и проверка по критериям.">
  <meta name="theme-color" content="#17191b">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="icon" href="{{ asset('assets/favicon.svg') }}" type="image/svg+xml">
  <link rel="stylesheet" href="{{ asset('assets/fonts/fonts.css') }}?v={{ filemtime(public_path('assets/fonts/fonts.css')) }}">
  <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}?v={{ filemtime(public_path('assets/css/app.css')) }}">
</head>
<body>
  <a class="skip-link" href="#main">Перейти к содержанию</a>

  <div class="shell">
    <aside class="rail" id="rail" aria-label="Навигация по курсу">
      <div class="rail-head">
        <a class="brand" href="#/">
          <span class="brand-mark" aria-hidden="true">ДЭ</span>
          <span class="brand-text">
            <strong>ДЭ-2027</strong>
            <span>платформа подготовки</span>
          </span>
        </a>
        <button class="rail-close" id="railClose" type="button" aria-label="Закрыть меню">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
        </button>
      </div>

      <div class="rail-progress">
        <div class="rail-progress-line">
          <span>Пройдено</span>
          <b id="railProgressText">0 / 0</b>
        </div>
        <div class="bar bar-dark"><i id="railProgressBar" style="width:0%"></i></div>
      </div>

      <div class="exam-countdown" id="examCountdown" aria-label="Обратный отсчёт до экзамена">
        <span class="exam-countdown-label">До экзамена</span>
        <strong id="examCountdownValue">—</strong>
        <small>28 февраля 2027</small>
      </div>

      <nav class="rail-nav" id="railNav"></nav>

      <div class="rail-tools">
        <a href="{{ route('labs.index') }}">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 6a4 4 0 0 1-5.6 5.6L4 16l4 4 4.4-4.4A4 4 0 0 0 18 10z"/><path d="M15 3l6 6"/></svg>
          Практикум
        </a>
        <a href="{{ route('drills.index') }}">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V5M4 19h16"/><path d="M8 16v-5M12 16V7M16 16v-3"/></svg>
          Тренажёр
        </a>
        <a href="{{ route('variants.index') }}">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 3h14v18H5z"/><path d="M9 8h6M9 12h6M9 16h4"/></svg>
          Варианты ТЗ
        </a>
        <a href="{{ route('exam.index') }}">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3h12v18H6z"/><path d="M9 8h6M9 12h4"/><path d="M15 15l2 2 4-4"/></svg>
          Экзамен
        </a>
        <a href="{{ route('build.index') }}">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7l4-4 12 12-4 4z"/><path d="M9 12l3 3M3 21l4-4"/></svg>
          Сборка с нуля
        </a>
        <a href="{{ route('cases.index') }}">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v14H4z"/><path d="M8 9h8M8 13h5"/></svg>
          Разборы проектов
        </a>
        <a href="{{ route('project.show') }}">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h6l2 2h8v11H4z"/><path d="M4 10h16"/></svg>
          Мой проект
        </a>
        <a href="{{ route('capstone.index') }}">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 3h14v18H5z"/><path d="M9 8h6M9 12h6M9 16h4"/></svg>
          Проверка проекта
        </a>
        <a href="#/recipes" data-nav="recipes">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16M4 12h16M4 19h10"/></svg>
          Рецепты кода
        </a>
        <a href="#/brief" data-nav="brief">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3h7l4 4v14H7zM14 3v5h5"/><path d="M10 13h6M10 17h4"/></svg>
          Конструктор ТЗ
        </a>
        <a href="#/exam" data-nav="exam">
          <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="13" r="8"/><path d="M12 9v4l3 2M9 2h6"/></svg>
          Таймер экзамена
        </a>
      </div>

      <div class="rail-user">
        @if ($authUser)
          <div class="rail-user-name">{{ $authUser['fio'] }}</div>
          <div class="rail-user-meta">
            @if ($authUser['role'] === 'admin')
              <a href="{{ route('admin.dashboard') }}">Админка</a>
              <span class="dot-sep">·</span>
            @endif
            <span id="syncStatus" class="sync is-saved">на сервере</span>
          </div>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="link-btn">Выйти</button>
          </form>
        @else
          <div class="rail-user-name">Гостевой режим</div>
          <div class="rail-user-meta">прогресс только в браузере</div>
          <div class="rail-user-actions">
            <a href="{{ route('login') }}">Войти</a>
            <a href="{{ route('register') }}">Регистрация</a>
          </div>
        @endif
      </div>

      <div class="rail-foot">
        <span>09.02.07 · ГИА ДЭ БУ</span>
        <button type="button" class="link-btn" id="resetProgress">Сбросить прогресс</button>
      </div>
    </aside>

    <div class="rail-scrim" id="railScrim" hidden></div>

    <div class="workspace">
      <header class="topbar">
        <button class="icon-btn menu-btn" id="menuBtn" type="button" aria-label="Открыть меню" aria-expanded="false" aria-controls="rail">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
        </button>

        <nav class="crumbs" id="crumbs" aria-label="Хлебные крошки"></nav>

        <div class="topbar-right">
          <button class="search-trigger" id="searchBtn" type="button">
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M16.5 16.5L21 21"/></svg>
            <span>Найти урок</span>
            <kbd>Ctrl K</kbd>
          </button>
          <span class="topbar-count" id="topbarCount" aria-hidden="true"></span>
        </div>
      </header>

      @if (session('success'))
        <div class="app-flash" role="status">{{ session('success') }}</div>
      @endif

      <main id="main" tabindex="-1"></main>
    </div>
  </div>

  <div class="modal" id="searchModal" hidden>
    <div class="modal-backdrop" data-close-modal></div>
    <div class="search-panel" role="dialog" aria-modal="true" aria-labelledby="searchTitle">
      <h2 class="visually-hidden" id="searchTitle">Поиск по курсу</h2>
      <div class="search-field">
        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M16.5 16.5L21 21"/></svg>
        <input type="search" id="searchInput" placeholder="Например: валидация, статусы, миграции" autocomplete="off">
        <kbd>Esc</kbd>
      </div>
      <div class="search-results" id="searchResults"></div>
      <div class="search-hint">
        <span><kbd>↑</kbd><kbd>↓</kbd> выбрать</span>
        <span><kbd>Enter</kbd> открыть</span>
        <span>Ищет по урокам и рецептам</span>
      </div>
    </div>
  </div>

  <script src="{{ asset('assets/js/vendor/highlight.min.js') }}"></script>
  <script src="{{ asset('assets/js/content/m0-start.js') }}"></script>
  <script src="{{ asset('assets/js/content/m1-web.js') }}"></script>
  <script src="{{ asset('assets/js/content/m2-php.js') }}"></script>
  <script src="{{ asset('assets/js/content/m3-db.js') }}"></script>
  <script src="{{ asset('assets/js/content/m4-laravel.js') }}"></script>
  <script src="{{ asset('assets/js/content/m5-build.js') }}"></script>
  <script src="{{ asset('assets/js/content/m6-design.js') }}"></script>
  <script src="{{ asset('assets/js/content/m7-quality.js') }}"></script>
  <script src="{{ asset('assets/js/content/m8-exam.js') }}"></script>
  <script src="{{ asset('assets/js/content/recipes.js') }}"></script>
  <script>
    window.__AUTH__ = @json(['user' => $authUser, 'csrf' => csrf_token()]);
    window.__STATE__ = @json($serverState);

    (function () {
      var value = document.getElementById('examCountdownValue');
      if (!value) return;
      var target = new Date('2027-02-28T00:00:00+05:00').getTime();
      var pad = function (n) { return String(n).padStart(2, '0'); };

      function updateCountdown() {
        var remaining = Math.max(0, target - Date.now());
        var totalSeconds = Math.floor(remaining / 1000);
        var days = Math.floor(totalSeconds / 86400);
        var hours = Math.floor((totalSeconds % 86400) / 3600);
        var minutes = Math.floor((totalSeconds % 3600) / 60);
        var seconds = totalSeconds % 60;
        value.textContent = remaining === 0
          ? 'Экзамен начался'
          : days + ' дн. ' + pad(hours) + ':' + pad(minutes) + ':' + pad(seconds);
      }

      updateCountdown();
      window.setInterval(updateCountdown, 1000);
    })();
  </script>
  <script src="{{ asset('assets/js/app.js') }}?v={{ filemtime(public_path('assets/js/app.js')) }}"></script>
</body>
</html>

