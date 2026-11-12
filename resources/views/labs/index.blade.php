@extends('labs.layout')

@section('title', 'Практикум')

@section('content')
  <div class="study-note">
    <b>Режим обучения:</b> сначала сформулируйте план, затем напишите код в редакторе и нажмите «Проверить».
    Решение открывается только после успешной сдачи.
  </div>
  <section class="lab-hero">
    <div>
      <span class="kicker">Работа руками · {{ $total }} лабораторных</span>
      <h1>Практикум</h1>
      <p class="lead">
        Не тесты, а задачи: пишете код, отправляете на проверку и получаете разбор по критериям.
        Начните с HTML-карточки, закончите чек-листом сдачи проекта.
      </p>
    </div>
    <div class="lab-hero-progress">
      <span>Сдано</span>
      <b>{{ $passed }} / {{ $total }}</b>
      <div class="bar"><i style="width: {{ $total ? round($passed / $total * 100) : 0 }}%"></i></div>
      <small>{{ $total ? round($passed / $total * 100) : 0 }}% практикума</small>
    </div>
  </section>

  @php
    $grouped = $labs->groupBy(fn ($lab) => $lab->module?->num ?? '—');
  @endphp

  @foreach ($grouped as $moduleNum => $moduleLabs)
    <section class="lab-group">
      <div class="lab-group-head">
        <span class="lab-group-num">{{ $moduleNum }}</span>
        <h2>{{ $moduleLabs->first()->module?->title ?? 'Практика' }}</h2>
        <span class="lab-group-count">{{ $moduleLabs->count() }} задач</span>
      </div>

      <div class="lab-list">
        @foreach ($moduleLabs as $lab)
          @php
            $submission = $submissions[$lab->id] ?? null;
            $status = $submission?->status;
          @endphp
          <a class="lab-row" href="{{ route('labs.show', $lab) }}">
            <span class="lab-row-num">{{ str_pad($lab->position, 2, '0', STR_PAD_LEFT) }}</span>
            <span class="lab-row-body">
              <span class="lab-row-title">{{ $lab->title }}</span>
              <span class="lab-row-goal">{{ $lab->goal }}</span>
            </span>
            <span class="lab-row-meta">
              <span>{{ $lab->minutes }} мин</span>
              <span>{{ $lab->level }}</span>
            </span>
            <span class="lab-row-status {{ $status === 'passed' ? 'is-passed' : ($status === 'failed' ? 'is-failed' : '') }}">
              {{ $status === 'passed' ? 'сдано' : ($status === 'failed' ? 'есть ошибки' : ($status === 'draft' ? 'черновик' : 'не начато')) }}
            </span>
            <span class="lab-row-go">
              {{ $status === 'passed' ? 'повторить' : ($status ? 'продолжить' : 'начать') }} →
            </span>
          </a>
        @endforeach
      </div>
    </section>
  @endforeach

  <div class="lab-note">
    <strong>Как это работает.</strong> Проверка смотрит на код и результат SQL, а не на «правильный вариант».
    Если критерий не пройден — в результате будет написано, что именно поправить. Решение открывается после успешной проверки.
  </div>
@endsection
