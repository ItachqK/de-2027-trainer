@extends('labs.layout')

@section('title', 'Варианты ТЗ')

@section('content')
  <section class="lab-hero">
    <div>
      <span class="kicker">Полные задания · {{ $variants->count() }} вариантов</span>
      <h1>Варианты ТЗ</h1>
      <p class="lead">
        Готовые темы для полного проекта: сущности, поля, статусы, маршруты и требования.
        Возьмите вариант, соберите проект в IDE и проверьте его на 75 баллов.
      </p>
    </div>
    <div class="lab-hero-progress">
      <span>Что делать</span>
      <b>3 шага</b>
      <small>выбрать → собрать → проверить</small>
    </div>
  </section>

  @if (session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
  @endif

  <div class="variant-toolbar">
    <a class="btn btn-accent" href="{{ route('variants.random') }}">Случайный вариант →</a>
    <a class="btn btn-ghost" href="{{ route('capstone.index') }}">Проверить готовый проект</a>
  </div>

  <div class="variant-grid">
    @foreach ($variants as $index => $variant)
      <a class="variant-card {{ $selected === $variant->slug ? 'is-selected' : '' }}" href="{{ route('variants.show', $variant) }}">
        <span class="variant-card-num">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
        <span class="variant-card-theme">{{ $variant->theme }}</span>
        <strong>{{ $variant->title }}</strong>
        <span class="variant-card-summary">{{ $variant->summary }}</span>
        <span class="variant-card-meta">
          <span>{{ count($variant->entities) }} таблицы</span>
          <span>{{ $variant->minutes }} мин</span>
          @if ($selected === $variant->slug)
            <span class="variant-card-badge">выбран</span>
          @endif
        </span>
      </a>
    @endforeach
  </div>

  <div class="lab-note">
    <strong>Как работать.</strong>
    Выберите вариант и нажмите «Выбрать этот вариант» — он появится в «Мой проект».
    Соберите Laravel-приложение по ТЗ, затем откройте «Проверка проекта»: аудит покажет,
    что уже закрыто, а что ещё стоит баллов.
  </div>
@endsection
