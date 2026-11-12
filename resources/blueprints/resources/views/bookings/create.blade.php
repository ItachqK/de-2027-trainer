@extends('layouts.app')

@section('title', 'Новая заявка — @@PROJECT@@')

@section('content')
  <section class="page wrap page--narrow">
    <header class="page-head">
      <div>
        <p class="kicker">Шаг 2 из 2</p>
        <h1>Новая заявка</h1>
        <p class="lead">Выберите вариант, заполните «@@DATE_LABEL@@» и укажите способ оплаты. Поля со звёздочкой обязательны.</p>
      </div>
    </header>

    @if ($errors->any())
      <div class="alert alert--error" role="alert">
        @foreach ($errors->all() as $error)
          <p>{{ $error }}</p>
        @endforeach
      </div>
    @endif

    <form method="POST" action="{{ route('bookings.store') }}" class="form card-form">
      @csrf

      <div class="field">
        <label for="object_title">Что бронируете *</label>
        <select id="object_title" name="object_title" required @error('object_title') aria-invalid="true" @enderror>
          <option value="">— выберите из списка —</option>
          @foreach ($options as $option)
            <option value="{{ $option }}" @selected(old('object_title') === $option)>{{ $option }}</option>
          @endforeach
        </select>
        <span class="hint">Список вариантов задан в техническом задании.</span>
      </div>

      <div class="field-row">
        <div class="field">
          <label for="event_date">@@DATE_LABEL_UC@@ *</label>
          <input type="date" id="event_date" name="event_date" value="{{ old('event_date') }}"
                 min="{{ now()->toDateString() }}" required @error('event_date') aria-invalid="true" @enderror>
          <span class="hint">Дата не может быть в прошлом.</span>
        </div>

        <div class="field">
          <label for="payment">Способ оплаты *</label>
          <select id="payment" name="payment" required @error('payment') aria-invalid="true" @enderror>
            <option value="">— выберите —</option>
            <option value="onsite" @selected(old('payment') === 'onsite')>@@PAY1@@</option>
            <option value="sbp" @selected(old('payment') === 'sbp')>@@PAY2@@</option>
          </select>
          <span class="hint">Всего два способа — как в задании.</span>
        </div>
      </div>

      <div class="field">
        <label for="comment">Комментарий</label>
        <textarea id="comment" name="comment" rows="4" maxlength="500"
                  placeholder="Например: нужен счёт на оплату">{{ old('comment') }}</textarea>
        <span class="hint">Необязательно, до 500 символов.</span>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn">Отправить заявку</button>
        <a class="link-arrow" href="{{ route('bookings.index') }}">Вернуться к заявкам</a>
      </div>
    </form>
  </section>
@endsection
