<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>403 — Доступ запрещён</title>
  <link rel="icon" href="{{ asset('assets/favicon.svg') }}" type="image/svg+xml">
  <link rel="stylesheet" href="{{ asset('assets/fonts/fonts.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
</head>
<body class="error-body">
  <main class="error-page">
    <span class="kicker">403 · запрещено</span>
    <h1>Сюда нельзя</h1>
    <p>Раздел доступен только администратору курса. Войдите под другой учётной записью или вернитесь к обучению.</p>
    <div class="btn-row">
      <a class="btn btn-accent" href="{{ route('app') }}">Вернуться к курсу</a>
      <a class="btn btn-ghost" href="{{ route('login') }}">Сменить аккаунт</a>
    </div>
  </main>
</body>
</html>
