@extends('layouts.auth')

@section('title', 'Регистрация')

@section('content')
  <span class="kicker">Регистрация</span>
  <h1 class="auth-title">Создать аккаунт</h1>
  <p class="auth-sub">После регистрации прогресс будет сохраняться на сервере и открываться с любого устройства.</p>

  @if ($errors->any())
    <div class="alert alert--error">Проверьте поля формы.</div>
  @endif

  <form method="POST" action="{{ route('register.store') }}" novalidate>
    @csrf

    <div class="field">
      <label for="fio">ФИО</label>
      <input type="text" id="fio" name="fio" value="{{ old('fio') }}"
             placeholder="Иванов Иван Иванович" autocomplete="name" required>
      @error('fio') <span class="field-error">{{ $message }}</span> @enderror
    </div>

    <div class="field">
      <label for="login">Логин</label>
      <input type="text" id="login" name="login" value="{{ old('login') }}"
             placeholder="Латиница и цифры, от 6 символов" autocomplete="username" required>
      @error('login') <span class="field-error">{{ $message }}</span> @enderror
    </div>

    <div class="field">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" value="{{ old('email') }}"
             placeholder="you@example.ru" autocomplete="email" required>
      @error('email') <span class="field-error">{{ $message }}</span> @enderror
    </div>

    <div class="field">
      <label for="password">Пароль</label>
      <input type="password" id="password" name="password"
             placeholder="Минимум 8 символов" autocomplete="new-password" required>
      @error('password') <span class="field-error">{{ $message }}</span> @enderror
    </div>

    <button type="submit" class="btn btn-accent auth-submit">Создать пользователя</button>
  </form>

  <p class="auth-switch">
    Уже зарегистрированы?
    <a href="{{ route('login') }}">Войти в систему</a>
  </p>

  <p class="auth-back"><a href="{{ route('app') }}">← Вернуться к курсу без входа</a></p>
@endsection
