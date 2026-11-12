@extends('labs.layout')

@section('title', 'Экзамен 3:30')

@section('content')
  <section class="lab-hero">
    <div>
      <span class="kicker">Полный прогон · 3 часа 30 минут</span>
      <h1>Экзамен 3:30</h1>
      <p class="lead">
        Случайное ТЗ, таймер и чек-лист на 75 баллов. Без подсказок и решений:
        только задание, ваш проект и время. После — разбор, что закрыто, а что нет.
      </p>
    </div>
    <div class="lab-hero-progress">
      <span>Формат</span>
      <b>15 × 5</b>
      <small>пунктов чек-листа по 5 баллов</small>
    </div>
  </section>

  @if (session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
  @endif

  @if ($active)
    <div class="exam-resume">
      <div>
        <span class="kicker">Есть незавершённый экзамен</span>
        <b>{{ $active->variant?->title }}</b>
        <small>
          Начат {{ $active->started_at->format('d.m H:i') }} ·
          осталось {{ gmdate('H:i', $active->remainingSeconds()) }}
        </small>
      </div>
      <a class="btn btn-accent" href="{{ route('exam.show', $active) }}">Продолжить →</a>
    </div>
  @endif

  <div class="exam-start">
    <form method="POST" action="{{ route('exam.start') }}" class="exam-start-form">
      @csrf
      <div class="field">
        <label for="variant_id">Тема проекта</label>
        <select id="variant_id" name="variant_id">
          <option value="">Случайная тема</option>
          @foreach ($variants as $variant)
            <option value="{{ $variant->id }}">{{ $variant->title }}</option>
          @endforeach
        </select>
      </div>
      <button type="submit" class="btn btn-accent">Начать экзамен →</button>
    </form>

    <div class="exam-rules">
      <span class="kicker">Правила</span>
      <ul>
        <li>3:30 по таймеру, без подсказок и готовых решений.</li>
        <li>Собираете проект в IDE, как на демоэкзамене.</li>
        <li>В конце отмечаете чек-лист — это самопроверка на 75 баллов.</li>
        <li>Таймер не останавливает работу: он показывает, где вы по времени.</li>
      </ul>
    </div>
  </div>

  <section class="exam-history">
    <div class="exam-history-head">
      <h2>Прошлые прогоны</h2>
      <a href="{{ route('variants.index') }}">Все ТЗ →</a>
    </div>

    @if ($sessions->isEmpty())
      <p class="lab-empty">Пока ни одного экзамена. Начните с случайной темы — это честнее всего.</p>
    @else
      <div class="exam-table-wrap">
        <table class="exam-table">
          <thead>
            <tr>
              <th>Дата</th>
              <th>Тема</th>
              <th>Баллы</th>
              <th>Статус</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @foreach ($sessions as $session)
              <tr>
                <td>{{ $session->started_at->format('d.m.Y H:i') }}</td>
                <td>{{ $session->variant?->title }}</td>
                <td>{{ $session->isFinished() ? $session->scoreLabel() : '—' }}</td>
                <td>{{ $session->isFinished() ? 'завершён' : 'идёт' }}</td>
                <td>
                  @if ($session->isFinished())
                    <a href="{{ route('exam.result', $session) }}">Разбор →</a>
                  @else
                    <a href="{{ route('exam.show', $session) }}">Продолжить →</a>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </section>
@endsection
