@extends('layouts.app')

@section('title', 'Регистрация — @@PROJECT@@')

@section('content')
  <section class="auth wrap">
    <div class="auth-panel auth-panel--dark">
      <div>
        <p class="auth-quote">Регистрация открывает личный кабинет с заявками.</p>
        <p class="auth-quote-by">@@PROJECT@@ · @@PURPOSE@@</p>
      </div>
      <ul class="auth-list">
        <li>Логин — латиница и цифры, от 6 символов</li>
        <li>Пароль — от 8 символов, буква и цифра</li>
        <li>Телефон — 8(912)345-67-89</li>
      </ul>
    </div>

    <div class="auth-form">
      <h1>Создание аккаунта</h1>
      <p class="auth-sub">После регистрации вы сразу попадёте в свои заявки.</p>

      @if ($errors->any())
        <div class="alert alert--error" role="alert">
          @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
          @endforeach
        </div>
      @endif

      <form method="POST" action="{{ route('register') }}" class="form">
        @csrf

        <div class="field">
          <label for="fio">Фамилия, имя, отчество</label>
          <input type="text" id="fio" name="fio" value="{{ old('fio') }}" required autofocus
                 autocomplete="name" placeholder="Иванова Мария Сергеевна"
                 @error('fio') aria-invalid="true" @enderror>
          <span class="hint">Только кириллица, пробелы и дефис.</span>
        </div>

        <div class="field-row">
          <div class="field">
            <label for="login">Логин</label>
            <input type="text" id="login" name="login" value="{{ old('login') }}" required
                   autocomplete="username" placeholder="maria2027"
                   @error('login') aria-invalid="true" @enderror>
            <span class="hint">Латиница и цифры, от 6 символов.</span>
          </div>

          <div class="field">
            <label for="phone">Телефон</label>
            <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required
                   autocomplete="tel" placeholder="8(912)345-67-89"
                   @error('phone') aria-invalid="true" @enderror>
            <span class="hint">Формат 8(XXX)XXX-XX-XX.</span>
          </div>
        </div>

        <div class="field">
          <label for="email">Электронная почта</label>
          <input type="email" id="email" name="email" value="{{ old('email') }}" required
                 autocomplete="email" placeholder="maria@example.ru"
                 @error('email') aria-invalid="true" @enderror>
        </div>

        <div class="field-row">
          <div class="field">
            <label for="password">Пароль</label>
            <input type="password" id="password" name="password" required autocomplete="new-password"
                   @error('password') aria-invalid="true" @enderror>
            <span class="hint">От 8 символов, буква и цифра.</span>
          </div>

          <div class="field">
            <label for="password_confirmation">Пароль ещё раз</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required
                   autocomplete="new-password">
          </div>
        </div>

        <button type="submit" class="btn btn-block">Создать аккаунт</button>
      </form>

      <p class="auth-alt">Уже есть аккаунт? <a href="{{ route('login') }}">Войти</a></p>
    </div>
  </section>
@endsection
