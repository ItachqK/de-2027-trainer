@extends('labs.layout')

@section('title', 'Разборы проектов')

@section('content')
  <section class="lab-hero">
    <div>
      <span class="kicker">Теория · как это делается</span>
      <h1>Разборы проектов</h1>
      <p class="lead">
        Выберите проект и прочитайте, как его строили: порядок команд, полный код,
        объяснения и типичные ошибки. Ничего собирать не нужно — это разбор.
      </p>
    </div>
    <div class="lab-hero-progress">
      <span>Формат</span>
      <b>4 проекта</b>
      <small>от консоли до сдачи</small>
    </div>
  </section>

  <div class="case-grid">
    @foreach ($cases as $case)
      <a class="case-card" href="{{ route('cases.show', $case) }}">
        <span class="case-card-num">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
        <span class="case-card-theme">{{ $case->variant?->theme ?? 'Учебный проект' }}</span>
        <strong>{{ $case->title }}</strong>
        <span class="case-card-sub">{{ $case->subtitle }}</span>
        <span class="case-card-summary">{{ $case->summary }}</span>
        <span class="case-card-meta">
          <span>{{ count($case->chapters) }} глав</span>
          <span>{{ $case->duration }} мин</span>
          <span>{{ $case->level }}</span>
          <span class="case-card-depth">полный код</span>
        </span>
      </a>
    @endforeach
  </div>

  <div class="lab-note">
    <strong>Чем это отличается от «Сборки».</strong>
    «Сборка» — практика: вы копируете код и отмечаете шаги. «Разбор» — теория:
    читаете, почему сделано именно так, и смотрите команды и файлы. Можно начать с разбора, потом собрать руками.
  </div>
@endsection
