@extends('layouts.app')

@section('title', 'Мои заявки — @@PROJECT@@')

@section('content')
  <section class="page wrap">
    <header class="page-head">
      <div>
        <p class="kicker">Личный кабинет</p>
        <h1>Мои заявки</h1>
        <p class="lead">Заявок: {{ $bookings->total() }}. Статус меняет администратор — вы видите его сразу.</p>
      </div>
      <a class="btn" href="{{ route('bookings.create') }}">Новая заявка</a>
    </header>

    @if ($bookings->isEmpty())
      <div class="empty">
        <h2>Заявок пока нет</h2>
        <p>Выберите вариант, заполните поле «@@DATE_LABEL@@» и укажите способ оплаты. Заявка появится здесь сразу после отправки.</p>
        <a class="btn" href="{{ route('bookings.create') }}">Оформить первую заявку</a>
      </div>
    @else
      <ul class="booking-list">
        @foreach ($bookings as $booking)
          <li class="booking">
            <div class="booking-main">
              <div class="booking-top">
                <span class="booking-id">Заявка №{{ $booking->id }}</span>
                @include('partials.status', ['status' => $booking->status, 'label' => $booking->statusLabel()])
              </div>
              <h2>{{ $booking->object_title }}</h2>
              <dl class="booking-rows">
                <div><dt>@@DATE_LABEL_UC@@</dt><dd>{{ $booking->event_date->translatedFormat('j F Y') }}</dd></div>
                <div><dt>Оплата</dt><dd>{{ $booking->paymentLabel() }}</dd></div>
                <div><dt>Создана</dt><dd>{{ $booking->created_at->translatedFormat('j F, H:i') }}</dd></div>
              </dl>
              @if ($booking->comment)
                <p class="booking-comment">«{{ $booking->comment }}»</p>
              @endif
            </div>

            <div class="booking-side">
              @if ($booking->review)
                <div class="review-done">
                  <span class="stars" aria-label="Оценка {{ $booking->review->rating }} из 5">{{ str_repeat('★', $booking->review->rating) }}<span class="stars-empty">{{ str_repeat('☆', 5 - $booking->review->rating) }}</span></span>
                  <p>Отзыв опубликован</p>
                </div>
              @elseif ($booking->canBeReviewed())
                <details class="review-form">
                  <summary>Оставить отзыв</summary>
                  <form method="POST" action="{{ route('reviews.store', $booking) }}" class="form">
                    @csrf
                    <div class="field">
                      <label for="rating-{{ $booking->id }}">Оценка</label>
                      <select id="rating-{{ $booking->id }}" name="rating" required>
                        <option value="5">5 — отлично</option>
                        <option value="4">4 — хорошо</option>
                        <option value="3">3 — нормально</option>
                        <option value="2">2 — плохо</option>
                        <option value="1">1 — очень плохо</option>
                      </select>
                    </div>
                    <div class="field">
                      <label for="text-{{ $booking->id }}">@@REVIEW@@</label>
                      <textarea id="text-{{ $booking->id }}" name="text" rows="3" required minlength="10" maxlength="600"></textarea>
                    </div>
                    @error('text')
                      <p class="field-error">{{ $message }}</p>
                    @enderror
                    <button type="submit" class="btn btn-small">Опубликовать</button>
                  </form>
                </details>
              @elseif ($booking->isDone())
                <p class="booking-hint">Заявка завершена.</p>
              @else
                <p class="booking-hint">Отзыв станет доступен после статуса «@@STATUS_DONE@@».</p>
              @endif
            </div>
          </li>
        @endforeach
      </ul>

      {{ $bookings->links() }}
    @endif
  </section>
@endsection
