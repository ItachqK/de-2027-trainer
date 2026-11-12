<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>404 — Страница не найдена</title>
  <link rel="icon" href="{{ asset('assets/favicon.svg') }}" type="image/svg+xml">
  <link rel="stylesheet" href="{{ asset('assets/fonts/fonts.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
</head>
<body class="error-body">
  <main class="error-page">
    <span class="kicker">404 · не найдено</span>
    <h1>Такой страницы нет</h1>
    <p>Возможно, урок переименовали или удалили. Откройте карту курса и выберите тему заново.</p>
    <div class="btn-row">
      <a class="btn btn-accent" href="{{ route('app') }}">К курсу</a>
      <a class="btn btn-ghost" href="{{ route('app') }}#/recipes">Рецепты кода</a>
    </div>
  </main>
</body>
</html>
