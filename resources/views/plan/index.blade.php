@extends('labs.layout')

@section('title', 'План на сегодня')

@section('content')
  @php($hours = rtrim(rtrim(number_format($plan['hoursPerWeek'], 1, ',', ''), '0'), ','))
  <section class="drill-hero">
    <div>
      <span class="kicker">подготовка · {{ $plan['daysLeft'] }} дней до экзамена · {{ $hours }} ч в неделю</span>
      <h1>План на сегодня</h1>
      <p class="lead">
        Одно число — насколько вы готовы, одно главное дело — что делать сейчас.
        Готовность считается по реальному прогрессу: уроки, тренажёр, лабораторные,
        прогон экзамена и аудит проекта.
      </p>
    </div>
    <div class="drill-hero-stat">
      <span>готовность</span>
      <b>{{ $plan['readiness'] }}%</b>
      <small>из 100</small>
    </div>
  </section>

  <section class="drill-tracks plan-blocks" aria-label="Готовность по блокам">
    @foreach ($plan['blocks'] as $block)
      <a class="drill-track" href="{{ $block['url'] }}">
        <span>{{ $block['label'] }}</span>
        <b>{{ $block['detail'] }}</b>
        <span class="bar"><i style="width: {{ $block['percent'] }}%"></i></span>
      </a>
    @endforeach
  </section>

  <div class="lab-note">
    <strong>Как считается.</strong>
    Четыре блока по 25%: теория, тренажёр, лабораторные и экзамен с аудитом.
    Это не оценка эксперта и не гарантия балла — это срез, по которому видно,
    что провисает. Обновляется при каждом заходе.
  </div>

  @if ($plan['weakest'])
    <div class="study-note">
      <b>Тормозит: {{ mb_strtolower($plan['weakest']['label']) }}.</b>
      {{ $plan['weakest']['hint'] }}
      <a href="{{ $plan['weakest']['url'] }}">Открыть →</a>
    </div>
  @endif

  <section class="drill-today">
    <div class="drill-today-head">
      <div>
        <h2>На сегодня</h2>
        <p>
          Главное дело — примерно {{ $plan['primaryMinutes'] }} мин.
          Остальное — если останутся силы.
        </p>
      </div>
      @if (! empty($plan['tasks']))
        <a class="btn btn-accent" href="{{ $plan['tasks'][0]['url'] }}">Начать →</a>
      @endif
    </div>

    <div class="drill-queue">
      @foreach ($plan['tasks'] as $index => $task)
        <a class="drill-row" href="{{ $task['url'] }}">
          <span class="drill-row-num">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
          <span class="drill-row-body">
            <b>{{ $task['title'] }}</b>
            <small>{{ $task['meta'] }}</small>
          </span>
          <span class="drill-row-mode is-{{ $task['primary'] ? 'weak' : 'new' }}">
            {{ $task['primary'] ? 'главное' : $task['kind'] }}
          </span>
          <span class="drill-row-go">→</span>
        </a>
      @endforeach
    </div>

    @if (empty($plan['tasks']))
      <p class="lead">Все четыре блока закрыты. Осталось повторить защиту и прогнать экзамен ещё раз.</p>
    @endif
  </section>

  <section class="drill-group">
    <div class="drill-group-head">
      <h2>План на 15 недель</h2>
      <span>{{ $hours }} ч в неделю</span>
    </div>

    <div class="drill-list">
      @foreach ($plan['weeks'] as $week)
        <div class="drill-item plan-week{{ $week['state'] === 'current' ? ' is-current' : '' }}">
          <span class="drill-item-title">
            Неделя {{ $week['num'] }} · {{ $week['focus'] }}
            <span class="plan-week-detail">{{ $week['detail'] }}</span>
          </span>
          <span class="drill-item-meta">{{ $week['dates'] }}</span>
          <span class="drill-item-state is-{{ $week['state'] === 'current' ? 'due' : ($week['state'] === 'past' ? 'learned' : 'new') }}">
            @if ($week['state'] === 'current')
              сейчас
            @elseif ($week['state'] === 'past')
              позади
            @else
              впереди
            @endif
          </span>
        </div>
      @endforeach
    </div>
  </section>

  <div class="lab-note">
    <strong>Почему так мало в неделю.</strong>
    В курсе около 30 часов практики и два полных прогона экзамена. При {{ $hours }} ч
    в неделю это 15 недель. Если время появится — не ускоряйте теорию, лучше
    добавьте ещё один вариант тренажёра или второй прогон.
  </div>
@endsection
