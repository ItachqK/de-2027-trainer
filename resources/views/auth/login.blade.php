@extends('layouts.auth')

@section('title', 'Вход')

@section('content')
  <span class="kicker">Вход</span>
  <h1 class="auth-title">Продолжить обучение</h1>
  <p class="auth-sub">Прогресс, ответы и заметки сохраняются в вашем аккаунте.</p>

  @if (session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
  @endif

  @error('login')
    <div class="alert alert--error">{{ $message }}</div>
  @enderror

  <form method="POST" action="{{ route('login.store') }}" novalidate>
    @csrf

    <div class="field">
      <label for="login">Логин</label>
      <input type="text" id="login" name="login" value="{{ old('login') }}"
             autocomplete="username" required autofocus>
      @error('login') <span class="field-error">{{ $message }}</span> @enderror
    </div>

    <div class="field">
      <label for="password">Пароль</label>
      <input type="password" id="password" name="password"
             autocomplete="current-password" required>
      @error('password') <span class="field-error">{{ $message }}</span> @enderror
    </div>

    <button type="submit" class="btn btn-accent auth-submit">Войти</button>
  </form>

  <p class="auth-switch">
    Еще не зарегистрированы?
    <a href="{{ route('register') }}">Регистрация</a>
  </p>

  <p class="auth-back"><a href="{{ route('app') }}">← Вернуться к курсу без входа</a></p>

  @if (session('logged_out'))
    <script>
      try { localStorage.removeItem('de2027.progress.v1'); } catch (e) {}
    </script>
  @endif
@endsection
