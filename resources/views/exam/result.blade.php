@extends('labs.layout')

@section('title', 'Разбор экзамена')

@section('content')
  @php
    $inline = fn (string $text) => preg_replace('/`([^`]+)`/', '<code>$1</code>', e($text));
    $score = (int) ($session->score ?? 0);
    $verdict = $score >= 65
        ? 'Можно идти на экзамен'
        : ($score >= 50 ? 'Нужно добить слабые места' : 'Рано, вернитесь к практике');
    $minutes = $session->finished_at
        ? max(1, (int) round($session->started_at->diffInMinutes($session->finished_at)))
        : null;
  @endphp

  <nav class="lab-crumbs" aria-label="Хлебные крошки">
    <a href="{{ route('exam.index') }}">Экзамен</a>
    <span>/</span>
    <span>Разбор</span>
  </nav>

  <section class="exam-result-hero">
    <div>
      <span class="kicker">Разбор · {{ $variant->title }}</span>
      <h1>{{ $verdict }}</h1>
      <p class="lead">
        Самопроверка по чек-листу. Это не оценка эксперта, а честный срез:
        что уже работает, а что стоит баллов.
      </p>
      @if ($minutes)
        <p class="exam-result-meta">Прогон занял {{ $minutes }} мин из 210.</p>
      @endif
    </div>
    <div class="exam-score">
      <span>Баллы</span>
      <b>{{ $score }}</b>
      <small>из 75</small>
    </div>
  </section>

  <div class="exam-result-grid">
    <section class="exam-result-panel">
      <h2>Закрыто: {{ count($checked) }} из {{ count($checklist) }}</h2>
      <div class="exam-result-list">
        @foreach ($checklist as $key => $label)
          @php $done = in_array($key, $checked, true); @endphp
          <div class="exam-result-item {{ $done ? 'is-done' : 'is-missing' }}">
            <span>{{ $done ? '✓' : '×' }}</span>
            <span>{!! $inline($label) !!}</span>
          </div>
        @endforeach
      </div>
    </section>

    <aside class="exam-result-side">
      <div class="variant-panel">
        <span class="kicker">Что дальше</span>
        @if ($score >= 65)
          <p>Повторите прогон на новой теме и проверьте проект через аудит. Держите темп.</p>
        @elseif ($score >= 50)
          <p>Вернитесь к незакрытым пунктам, потом повторите экзамен. Не меняйте тему, пока не закроете базу.</p>
        @else
          <p>Сначала практикум и тренажёр: регистрация, заявки, статусы. Потом снова полный прогон.</p>
        @endif
        <a class="btn btn-accent" href="{{ route('exam.start') }}" onclick="event.preventDefault(); document.getElementById('retryForm').submit();">Ещё прогон →</a>
        <form id="retryForm" method="POST" action="{{ route('exam.start') }}" hidden>@csrf</form>
      </div>

      <div class="variant-panel">
        <span class="kicker">Ссылки</span>
        <p>
          <a href="{{ route('variants.show', $variant) }}">Открыть ТЗ</a><br>
          <a href="{{ route('capstone.index') }}">Проверить проект на 75 баллов</a><br>
          <a href="{{ route('exam.index') }}">Все прогоны</a>
        </p>
      </div>

      @if ($session->notes)
        <div class="variant-panel">
          <span class="kicker">Заметки</span>
          <p>{{ $session->notes }}</p>
        </div>
      @endif
    </aside>
  </div>
@endsection
