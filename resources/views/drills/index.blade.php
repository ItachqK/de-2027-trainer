@extends('labs.layout')

@section('title', 'Тренажёр')

@section('content')
  <div class="study-note">
    <b>Как учиться:</b> сначала прочитайте требования и напишите решение сами. Эталонный код закрыт до успешной проверки.
    После результата сравните подход и повторите задачу без копирования.
  </div>
  <section class="drill-hero">
    <div>
      <span class="kicker">ежедневная практика · {{ $stats['total'] }} вариантов</span>
      <h1>Тренажёр</h1>
      <p class="lead">
        Один вариант за раз: пишешь код, получаешь проверку, повторяешь через день.
        Очередь собирается сама — новые задачи и те, что пора вспомнить.
      </p>
    </div>
    <div class="drill-hero-stat">
      <span>серия</span>
      <b>{{ $stats['streak'] }}</b>
      <small>{{ $stats['streak'] === 1 ? 'день подряд' : 'дней подряд' }}</small>
    </div>
  </section>

  @if (session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
  @endif

  <section class="drill-today">
    <div class="drill-today-head">
      <div>
        <h2>На сегодня</h2>
        <p>В очереди: {{ count($queue) }} · примерно {{ $queueMinutes }} мин</p>
      </div>
      <a class="btn btn-accent" href="{{ route('drills.next') }}">Начать →</a>
    </div>

    <div class="drill-queue">
      @foreach ($queue as $index => $item)
        <a class="drill-row" href="{{ route('drills.show', $item['drill']) }}">
          <span class="drill-row-num">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
          <span class="drill-row-body">
            <b>{{ $item['drill']->title }}</b>
            <small>{{ $item['drill']->trackLabel() }} · {{ $item['drill']->minutes }} мин · {{ $item['drill']->level }}</small>
          </span>
          <span class="drill-row-mode is-{{ $item['mode'] }}">
            {{ $item['mode'] === 'repeat' ? 'повтор' : ($item['mode'] === 'new' ? 'новый' : 'слабое место') }}
          </span>
          <span class="drill-row-go">→</span>
        </a>
      @endforeach
    </div>
  </section>

  <section class="drill-stats" aria-label="Статистика тренажёра">
    <div>
      <span>решено</span>
      <b>{{ $stats['solved'] }} / {{ $stats['total'] }}</b>
    </div>
    <div>
      <span>попыток</span>
      <b>{{ $stats['attempts'] }}</b>
    </div>
    <div>
      <span>точность</span>
      <b>{{ $stats['accuracy'] }}%</b>
    </div>
    <div>
      <span>серия</span>
      <b>{{ $stats['streak'] }} дн.</b>
    </div>
  </section>

  <section class="drill-tracks" aria-label="Направления">
    @foreach ($tracks as $track)
      <a class="drill-track" href="#track-{{ $track['key'] }}">
        <span>{{ $track['label'] }}</span>
        <b>{{ $track['solved'] }} / {{ $track['total'] }}</b>
        <span class="bar"><i style="width: {{ $track['total'] ? round($track['solved'] / $track['total'] * 100) : 0 }}%"></i></span>
      </a>
    @endforeach
  </section>

  @foreach ($tracks as $track)
    <section class="drill-group" id="track-{{ $track['key'] }}">
      <div class="drill-group-head">
        <h2>{{ $track['label'] }}</h2>
        <span>{{ $track['solved'] }} / {{ $track['total'] }} решено</span>
      </div>

      <div class="drill-list">
        @foreach ($drills->where('track', $track['key']) as $drill)
          @php
            $state = $states[$drill->id];
            $item = $progress->get($drill->id);
          @endphp
          <a class="drill-item" href="{{ route('drills.show', $drill) }}">
            <span class="drill-item-title">{{ $drill->title }}</span>
            <span class="drill-item-meta">{{ $drill->minutes }} мин · {{ $drill->level }}</span>
            <span class="drill-item-state is-{{ $state }}">
              @if ($state === 'new')
                новый
              @elseif ($state === 'due')
                пора повторить
              @else
                выучен @if ($item?->next_review_at) · {{ $item->next_review_at->format('d.m') }} @endif
              @endif
            </span>
          </a>
        @endforeach
      </div>
    </section>
  @endforeach

  <div class="lab-note">
    <strong>Как это работает.</strong>
    После успешной проверки вариант возвращается через 1, 3, 7, 16, 35 дней — так он остаётся в памяти.
    Если ошиблись, задача вернётся завтра. Проверка идёт на этом компьютере: PHP — синтаксис, SQL — реальный запрос на учебной базе.
  </div>
@endsection
