@extends('layouts.app')

@section('title', 'Админка — @@PROJECT@@')

@section('content')
  <section class="page wrap">
    <header class="page-head">
      <div>
        <p class="kicker">Роль: администратор</p>
        <h1>Заявки пользователей</h1>
        <p class="lead">Меняйте статус только вперёд: «@@STATUS_NEW@@» → «@@STATUS_ASSIGNED@@» → «@@STATUS_DONE@@».</p>
      </div>
      <span class="counter">Всего: {{ $counts['all'] }}</span>
    </header>

    @if ($errors->any())
      <div class="alert alert--error" role="alert">
        @foreach ($errors->all() as $error)
          <p>{{ $error }}</p>
        @endforeach
      </div>
    @endif

    <div class="admin-bar">
      <nav class="tabs" aria-label="Фильтр по статусу">
        <a href="{{ route('admin.bookings.index') }}" class="{{ $status === null ? 'is-active' : '' }}">Все <b>{{ $counts['all'] }}</b></a>
        <a href="{{ route('admin.bookings.index', ['status' => 'new']) }}" class="{{ $status === 'new' ? 'is-active' : '' }}">@@STATUS_NEW@@ <b>{{ $counts['new'] }}</b></a>
        <a href="{{ route('admin.bookings.index', ['status' => 'assigned']) }}" class="{{ $status === 'assigned' ? 'is-active' : '' }}">@@STATUS_ASSIGNED@@ <b>{{ $counts['assigned'] }}</b></a>
        <a href="{{ route('admin.bookings.index', ['status' => 'done']) }}" class="{{ $status === 'done' ? 'is-active' : '' }}">@@STATUS_DONE@@ <b>{{ $counts['done'] }}</b></a>
      </nav>

      <form method="GET" action="{{ route('admin.bookings.index') }}" class="search-form">
        @if ($status)
          <input type="hidden" name="status" value="{{ $status }}">
        @endif
        <label class="visually-hidden" for="search">Поиск по логину или названию</label>
        <input type="search" id="search" name="search" value="{{ $search }}" placeholder="Логин или «@@OBJECT@@»">
        <button type="submit" class="btn btn-small">Найти</button>
      </form>
    </div>

    @if ($bookings->isEmpty())
      <div class="empty">
        <h2>Ничего не найдено</h2>
        <p>Сбросьте фильтр или измените запрос поиска.</p>
        <a class="btn btn-small" href="{{ route('admin.bookings.index') }}">Показать все заявки</a>
      </div>
    @else
      <div class="table-wrap">
        <table class="admin-table">
          <caption class="visually-hidden">Список заявок пользователей</caption>
          <thead>
            <tr>
              <th scope="col">№</th>
              <th scope="col">Пользователь</th>
              <th scope="col">@@OBJECT@@</th>
              <th scope="col">@@DATE_LABEL_UC@@</th>
              <th scope="col">Оплата</th>
              <th scope="col">Статус</th>
              <th scope="col">Действие</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($bookings as $booking)
              <tr>
                <td>{{ $booking->id }}</td>
                <td>
                  <b>{{ $booking->user->fio }}</b>
                  <span>{{ $booking->user->login }} · {{ $booking->user->phone }}</span>
                </td>
                <td>{{ $booking->object_title }}</td>
                <td>{{ $booking->event_date->translatedFormat('j.m.Y') }}</td>
                <td>{{ $booking->paymentLabel() }}</td>
                <td>@include('partials.status', ['status' => $booking->status, 'label' => $booking->statusLabel()])</td>
                <td>
                  @php
                    $next = match ($booking->status) {
                        'new' => 'assigned',
                        'assigned' => 'done',
                        default => null,
                    };
                  @endphp
                  @if ($next)
                    <form method="POST" action="{{ route('admin.bookings.status', $booking) }}">
                      @csrf
                      @method('PATCH')
                      <input type="hidden" name="status" value="{{ $next }}">
                      <button type="submit" class="btn btn-small">В «{{ \App\Models\Booking::STATUSES[$next] }}»</button>
                    </form>
                  @else
                    <span class="muted">Завершена</span>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      {{ $bookings->links() }}
    @endif
  </section>
@endsection
