@extends('labs.layout')

@section('title', $step->title)

@section('content')
  @php
    $inline = fn (string $text) => preg_replace('/`([^`]+)`/', '<code>$1</code>', e($text));
  @endphp

  <nav class="lab-crumbs" aria-label="Хлебные крошки">
    <a href="{{ route('build.index') }}">Сборка с нуля</a>
    <span>/</span>
    <span>{{ $step->title }}</span>
  </nav>

  <header class="lab-head">
    <div>
      <span class="kicker">Шаг {{ $step->position + 1 }} из {{ $total }} · полная сборка</span>
      <h1>{{ $step->title }}</h1>
      <p class="lead">{!! $inline($step->goal) !!}</p>
    </div>
    <div class="lab-head-meta">
      <span>{{ $step->minutes }} мин</span>
      <span>{{ $completed ? 'шаг пройден' : 'в работе' }}</span>
      <a href="{{ route('build.download') }}">Скачать исходники</a>
      <a href="{{ route('build.show', array_merge([$step], $mode === 'learn' ? [] : ['mode' => 'learn'])) }}">
        {{ $mode === 'learn' ? 'Обычный режим' : 'Учусь сам' }}
      </a>
    </div>
  </header>

  @if ($mode === 'learn')
    <section class="build-study-card">
      <span class="kicker">Режим понимания · без копирования</span>
      <h2>Сначала напиши сам</h2>
      <p>Не открывай эталон сразу. Ответь на вопросы, создай файлы и попробуй выполнить шаг. Потом раскрой код и сравни решение.</p>
      <ol class="build-study-questions">
        @foreach ($questions as $question)
          <li>{{ $question }}</li>
        @endforeach
      </ol>
      <label class="build-study-label" for="study-notes">Мой план / ответ</label>
      <textarea id="study-notes" class="build-study-notes" rows="7" placeholder="Напиши своими словами, что сейчас сделаешь..."></textarea>
      <button type="button" class="btn btn-accent" id="show-answer">Я ответил — показать материалы</button>
    </section>

    <div id="study-materials" hidden>
  @endif

  @if (session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
  @endif

  @if ($step->commands)
    <section class="build-section">
      <h2>Команды</h2>
      <p class="build-section-lead">Выполняйте по порядку в терминале, из папки проекта. Команда <code>migrate</code> применяет только уже созданные файлы — сами файлы создаются отдельно.</p>

      @foreach ($step->commands as $index => $command)
        <div class="build-command">
          <div class="build-command-head">
            <b>{{ $command['label'] }}</b>
            <button type="button" class="copy-btn" data-copy="build-command-{{ $index }}">Копировать</button>
          </div>
          <pre id="build-command-{{ $index }}"><code>{{ $command['code'] }}</code></pre>
        </div>
      @endforeach
    </section>
  @endif

  @if ($step->files)
    <section class="build-section">
      <h2>Файлы</h2>
      <p class="build-section-lead">Создайте файлы по указанным путям. Код полный, не фрагмент.</p>

      @foreach ($step->files as $index => $file)
        <details class="build-file" open>
          <summary>
            <span class="build-file-path">{{ $file['path'] }}</span>
            <span class="build-file-lang">{{ $file['language'] }}</span>
            <button type="button" class="copy-btn" data-copy="build-file-{{ $index }}">Копировать</button>
          </summary>
          <pre id="build-file-{{ $index }}"><code>{{ $file['code'] }}</code></pre>
        </details>
      @endforeach
    </section>
  @endif

  @if ($step->snippets)
    <section class="build-section">
      <h2>Добавить в маршруты</h2>
      <p class="build-section-lead">Эти строки добавляются в существующий файл, а не заменяют его.</p>

      @foreach ($step->snippets as $index => $snippet)
        <div class="build-snippet">
          <div class="build-command-head">
            <b>{{ $snippet['path'] }}</b>
            <button type="button" class="copy-btn" data-copy="build-snippet-{{ $index }}">Копировать</button>
          </div>
          <p>{{ $snippet['note'] }}</p>
          <pre id="build-snippet-{{ $index }}"><code>{{ $snippet['code'] }}</code></pre>
        </div>
      @endforeach
    </section>
  @endif

  <section class="build-section">
    <h2>Проверка шага</h2>
    <ul class="build-checks">
      @foreach ($step->checks as $check)
        <li>{!! $inline($check) !!}</li>
      @endforeach
    </ul>
  </section>

  <div class="build-actions">
    @if (! $completed)
      <form method="POST" action="{{ route('build.complete', $step) }}">
        @csrf
        <button type="submit" class="btn btn-accent">
          {{ $next ? 'Шаг готов, дальше →' : 'Завершить сборку' }}
        </button>
      </form>
    @else
      <span class="build-done">Шаг пройден</span>
    @endif

    @if ($prev)
      <a class="btn btn-ghost" href="{{ route('build.show', array_merge([$prev], $mode === 'learn' ? ['mode' => 'learn'] : [])) }}">← {{ $prev->title }}</a>
    @endif

    @if ($next)
      <a class="btn btn-ghost" href="{{ route('build.show', array_merge([$next], $mode === 'learn' ? ['mode' => 'learn'] : [])) }}">{{ $next->title }} →</a>
    @endif
  </div>

  @if ($mode === 'learn')
    </div>
  @endif

  <script>
    (function () {
      document.querySelectorAll('[data-copy]').forEach(function (button) {
        button.addEventListener('click', function (event) {
          event.preventDefault();
          event.stopPropagation();

          var block = document.getElementById(button.getAttribute('data-copy'));

          if (!block) return;

          navigator.clipboard.writeText(block.innerText).then(function () {
            var original = button.textContent;
            button.textContent = 'Скопировано';
            window.setTimeout(function () { button.textContent = original; }, 1200);
          });
        });
      });
    })();

    (function () {
      var button = document.getElementById('show-answer');
      var materials = document.getElementById('study-materials');
      var notes = document.getElementById('study-notes');
      if (!button || !materials || !notes) return;
      var key = 'build-study-{{ $step->slug }}';
      notes.value = localStorage.getItem(key) || '';
      notes.addEventListener('input', function () { localStorage.setItem(key, notes.value); });
      button.addEventListener('click', function () {
        materials.hidden = false;
        button.textContent = 'Материалы открыты';
        materials.scrollIntoView({ behavior: 'smooth', block: 'start' });
      });
    })();
  </script>
@endsection
