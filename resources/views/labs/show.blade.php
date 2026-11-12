@extends('labs.layout')

@section('title', $lab->title)

@section('content')
  <nav class="lab-crumbs" aria-label="Хлебные крошки">
    <a href="{{ route('labs.index') }}">Практикум</a>
    <span>/</span>
    <span>{{ $lab->module?->num }} · {{ $lab->module?->title }}</span>
  </nav>

  <header class="lab-head">
    <div>
      <span class="kicker">Лабораторная · {{ $lab->language }}</span>
      <h1>{{ $lab->title }}</h1>
      <p class="lead">{{ $lab->goal }}</p>
    </div>
    <div class="lab-head-meta">
      <span>{{ $lab->minutes }} мин</span>
      <span>{{ $lab->level }}</span>
      @if ($lesson)
        <a href="{{ route('app') }}#/lesson/{{ $lesson->slug }}">Урок: {{ $lesson->title }}</a>
      @endif
    </div>
  </header>

  @if ($errors->any())
    <div class="alert alert--error">{{ $errors->first() }}</div>
  @endif

  @if (session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
  @endif

  <div class="lab-split">
    <section class="lab-brief">
      <h2>Задача</h2>
      <p>{!! nl2br(e($lab->brief)) !!}</p>

      <h3>Шаги</h3>
      <ol class="lab-steps">
        @foreach ($lab->steps as $step)
          <li>{{ $step }}</li>
        @endforeach
      </ol>

      @if ($lab->hints)
        <details class="lab-hints">
          <summary>Подсказки ({{ count($lab->hints) }})</summary>
          <ul>
            @foreach ($lab->hints as $hint)
              <li>{{ $hint }}</li>
            @endforeach
          </ul>
        </details>
      @endif

      @if ($submission?->passed())
        <details class="lab-solution">
          <summary>Показать решение</summary>
          <pre><code>{{ $lab->solution }}</code></pre>
        </details>
      @else
        <p class="lab-locked">Решение откроется после успешной проверки. Сначала попробуйте сами — подсказки выше.</p>
      @endif
    </section>

    <section class="lab-work">
      <form method="POST" action="{{ route('labs.check', $lab) }}" class="lab-form">
        @csrf

        <div class="lab-editor-head">
          <label for="code">Ваш код · {{ $lab->language }}</label>
          <button type="button" class="link-btn" id="resetCode" data-starter="{{ $lab->starter_code }}">Сбросить к заготовке</button>
        </div>

        <textarea id="code" name="code" class="lab-editor" spellcheck="false"
                  aria-label="Код решения">{{ old('code', $submission->code ?? $lab->starter_code) }}</textarea>

        <div class="lab-actions">
          <button type="submit" class="btn btn-accent">Проверить</button>
          <button type="submit" class="btn btn-ghost" formaction="{{ route('labs.draft', $lab) }}">Сохранить черновик</button>
          <span class="lab-shortcut">Ctrl + Enter — проверить</span>
        </div>
      </form>

      @if ($submission?->results)
        <div id="result" class="lab-results {{ $submission->status === 'passed' ? 'is-passed' : 'is-failed' }}">
          <div class="lab-results-head">
            <div>
              <span class="kicker">{{ $submission->status === 'passed' ? 'принято' : 'есть ошибки' }}</span>
              <h2>{{ $submission->status === 'passed' ? 'Лабораторная сдана' : 'Проверка не пройдена' }}</h2>
            </div>
            <span class="lab-attempts">Попыток: {{ $submission->attempts }}</span>
          </div>

          <div class="lab-result-list">
            @foreach ($submission->results as $result)
              <div class="lab-result {{ $result['passed'] ? 'is-pass' : 'is-fail' }}">
                <span class="lab-result-mark">{{ $result['passed'] ? '✓' : '×' }}</span>
                <span>
                  <b>{{ $result['label'] }}</b>
                  <small>{{ $result['message'] }}</small>
                </span>
              </div>
            @endforeach
          </div>

          @if ($submission->status === 'passed')
            <div class="lab-passed">
              <p>Критерии закрыты. Можно посмотреть решение и перейти к следующей задаче.</p>
              @if ($next)
                <a class="btn btn-accent" href="{{ route('labs.show', $next) }}">Следующая: {{ $next->title }} →</a>
              @else
                <a class="btn btn-accent" href="{{ route('labs.index') }}">Вернуться в практикум →</a>
              @endif
            </div>
          @else
            <p class="lab-retry">Поправьте код и отправьте снова. Подсказки — в левой колонке, они не штрафуют.</p>
          @endif
        </div>
      @else
        <div class="lab-empty">
          Напишите решение и нажмите «Проверить». Проверка не запускает ваш код на сервере: PHP проходит только синтаксическую проверку, SQL выполняется на учебной базе.
        </div>
      @endif
    </section>
  </div>
@endsection
