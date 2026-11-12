@extends('layouts.app')

@section('title', 'Вход — @@PROJECT@@')

@section('content')
  <section class="auth wrap">
    <div class="auth-panel">
      <div>
        <p class="auth-quote">«@@PROJECT@@»</p>
        <p class="auth-quote-by">Информационная система для @@PURPOSE@@. Вход по логину, который вы указали при регистрации.</p>
      </div>
      <ul class="auth-list">
        <li>Свои заявки и их статусы</li>
        <li>Два способа оплаты</li>
        <li>Отзыв после завершения заявки</li>
      </ul>
    </div>

    <div class="auth-form">
      <h1>Вход в аккаунт</h1>
      <p class="auth-sub">Введите логин и пароль. Если аккаунта нет — зарегистрируйтесь.</p>

      @if ($errors->any())
        <div class="alert alert--error" role="alert">
          @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
          @endforeach
        </div>
      @endif

      <form method="POST" action="{{ route('login') }}" class="form">
        @csrf

        <div class="field">
          <label for="login">Логин</label>
          <input type="text" id="login" name="login" value="{{ old('login') }}" required autofocus
                 autocomplete="username" @error('login') aria-invalid="true" @enderror>
        </div>

        <div class="field">
          <label for="password">Пароль</label>
          <input type="password" id="password" name="password" required autocomplete="current-password"
                 @error('password') aria-invalid="true" @enderror>
        </div>

        <label class="check">
          <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
          <span>Запомнить меня на этом компьютере</span>
        </label>

        <button type="submit" class="btn btn-block">Войти</button>
      </form>

      <p class="auth-alt">Ещё не зарегистрированы? <a href="{{ route('register') }}">Создайте аккаунт</a></p>
    </div>
  </section>
@endsection
