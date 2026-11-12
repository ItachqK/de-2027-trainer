@extends('labs.layout')

@section('title', 'Сборка с нуля')

@section('content')
  @php
    $total = $steps->count();
    $completed = count(array_intersect($done, $steps->pluck('id')->all()));
    $percent = $total ? round($completed / $total * 100) : 0;
  @endphp

  <section class="lab-hero">
    <div>
      <span class="kicker">Полный разбор · от пустой папки до сдачи</span>
      <h1>Сборка с нуля</h1>
      <p class="lead">
        10 шагов: команды, полные файлы, проверки. Не фрагменты, а проект целиком:
        миграции, модели, сидеры, регистрация, вход, заявки, кабинет и админка.
      </p>
    </div>
    <div class="lab-hero-progress">
      <span>Пройдено</span>
      <b>{{ $completed }} / {{ $total }}</b>
      <div class="bar"><i style="width: {{ $percent }}%"></i></div>
      <small>{{ $percent }}% сборки</small>
    </div>
  </section>

  @if (session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
  @endif

  <div class="build-toolbar">
    @if ($next)
      <a class="btn btn-accent" href="{{ route('build.show', $next) }}">
        {{ $completed ? 'Продолжить: '.$next->title : 'Начать сборку →' }}
      </a>
    @else
      <span class="build-done">Сборка пройдена</span>
    @endif

    <a class="btn btn-ghost" href="{{ route('build.download') }}">Скачать исходники (.zip)</a>
    <a class="btn btn-ghost" href="{{ $next ? route('build.show', [$next, 'mode' => 'learn']) : route('build.index') }}">Учусь сам →</a>

    @if ($completed)
      <form method="POST" action="{{ route('build.reset') }}">
        @csrf
        <button type="submit" class="btn btn-ghost">Сбросить прогресс</button>
      </form>
    @endif
  </div>

  <div class="build-list">
    @foreach ($steps as $step)
      @php $isDone = in_array($step->id, $done, true); @endphp
      <a class="build-row {{ $isDone ? 'is-done' : '' }} {{ $next && $next->id === $step->id ? 'is-next' : '' }}"
         href="{{ route('build.show', $step) }}">
        <span class="build-row-num">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
        <span class="build-row-body">
          <b>{{ $step->title }}</b>
          <small>{{ $step->goal }}</small>
        </span>
        <span class="build-row-meta">{{ $step->minutes }} мин</span>
        <span class="build-row-state">
          {{ $isDone ? 'пройден' : ($next && $next->id === $step->id ? 'следующий' : '—') }}
        </span>
      </a>
    @endforeach
  </div>

  <div class="lab-note">
    <strong>Как это работает.</strong>
    Каждый шаг — команды и полные файлы. Копируйте код, выполняйте команды, отмечайте шаг.
    В конце скачайте ZIP и сравните со своим проектом. Внутри — исходники без `vendor`:
    создайте Laravel-проект и наложите файлы, как написано в README.
  </div>
@endsection
