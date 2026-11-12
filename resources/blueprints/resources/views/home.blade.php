@extends('layouts.app')

@section('title', '@@PROJECT@@ — заявки онлайн')

@section('content')
  <section class="hero">
    <div class="wrap hero-grid">
      <div class="hero-copy">
        <p class="kicker">Информационная система для @@PURPOSE@@</p>
        <h1>@@PROJECT@@</h1>
        <p class="lead">
          Выберите вариант из списка, заполните поле «@@DATE_LABEL@@» и укажите способ оплаты.
          Заявка сразу попадает администратору, а статус виден в личном кабинете.
        </p>
        <div class="hero-actions">
          @auth
            <a class="btn" href="{{ route('bookings.create') }}">Оформить заявку</a>
            <a class="link-arrow" href="{{ route('bookings.index') }}">Мои заявки</a>
          @else
            <a class="btn" href="{{ route('register') }}">Зарегистрироваться</a>
            <a class="link-arrow" href="{{ route('login') }}">У меня есть аккаунт</a>
          @endauth
        </div>
        <dl class="hero-facts">
          <div><dt>Статусов заявки</dt><dd>3</dd></div>
          <div><dt>Способа оплаты</dt><dd>2</dd></div>
          <div><dt>Отзыв</dt><dd>после заявки</dd></div>
        </dl>
      </div>

      <aside class="hero-card" aria-label="Как выглядит заявка">
        <div class="stamp" aria-hidden="true">
          <span>@@INITIALS@@</span>
          <small>принято</small>
        </div>
        <p class="hero-card-label">Заявка № 27</p>
        <h2>@@OBJECT@@</h2>
        <dl class="hero-card-rows">
          <div><dt>@@DATE_LABEL_UC@@</dt><dd>{{ now()->addDays(3)->translatedFormat('j F') }}</dd></div>
          <div><dt>Оплата</dt><dd>@@PAY1@@</dd></div>
          <div><dt>Статус</dt><dd><span class="status is-new">@@STATUS_NEW@@</span></dd></div>
        </dl>
        <p class="hero-card-note">Так заявка выглядит в личном кабинете сразу после отправки.</p>
      </aside>
    </div>
  </section>

  <section class="section wrap">
    <header class="section-head">
      <h2>Что можно выбрать</h2>
      <p>Список вариантов задаётся в техническом задании и хранится в базе.</p>
    </header>
    <ul class="chip-list">
      @foreach ($options as $option)
        <li class="chip">{{ $option }}</li>
      @endforeach
    </ul>
  </section>

  <section class="section section--tint">
    <div class="wrap">
      <header class="section-head">
        <h2>Как это работает</h2>
        <p>Пять страниц по техническому заданию: регистрация, вход, заявки, новая заявка, админка.</p>
      </header>
      <ol class="steps-list">
        <li><b>01</b><div><h3>Регистрация</h3><p>ФИО, логин, телефон, почта и пароль. Логин и почта уникальны.</p></div></li>
        <li><b>02</b><div><h3>Заявка</h3><p>Вариант, «@@DATE_LABEL@@», оплата и комментарий. Дата не в прошлом.</p></div></li>
        <li><b>03</b><div><h3>Статусы</h3><p>«@@STATUS_NEW@@» → «@@STATUS_ASSIGNED@@» → «@@STATUS_DONE@@». Только вперёд.</p></div></li>
        <li><b>04</b><div><h3>Отзыв</h3><p>@@REVIEW@@ — после завершения заявки, один раз.</p></div></li>
      </ol>
    </div>
  </section>

  @if ($reviews->isNotEmpty())
    <section class="section wrap">
      <header class="section-head">
        <h2>Отзывы</h2>
        <p>Их оставляют пользователи, чьи заявки уже завершены.</p>
      </header>
      <div class="review-grid">
        @foreach ($reviews as $review)
          <figure class="review">
            <div class="review-head">
              <span class="stars" aria-label="Оценка {{ $review->rating }} из 5">{{ str_repeat('★', $review->rating) }}<span class="stars-empty">{{ str_repeat('☆', 5 - $review->rating) }}</span></span>
              <time datetime="{{ $review->created_at->toDateString() }}">{{ $review->created_at->translatedFormat('j F') }}</time>
            </div>
            <blockquote>{{ $review->text }}</blockquote>
            <figcaption>
              {{ $review->user->fio }}
              <span>{{ $review->booking->object_title }}</span>
            </figcaption>
          </figure>
        @endforeach
      </div>
    </section>
  @endif

  <section class="cta wrap">
    <div class="cta-inner">
      <div>
        <h2>Готовы оформить первую заявку?</h2>
        <p>Регистрация занимает минуту. После входа вы увидите свои заявки и их статусы.</p>
      </div>
      <a class="btn btn-light" href="{{ auth()->check() ? route('bookings.create') : route('register') }}">
        {{ auth()->check() ? 'Новая заявка' : 'Создать аккаунт' }}
      </a>
    </div>
  </section>
@endsection
