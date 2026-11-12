@extends('labs.layout')

@section('title', $case->title.' — шаг '.$chapterIndex)

@section('content')
  @php
    $inline = fn (string $text) => preg_replace('/`([^`]+)`/', '<code>$1</code>', e($text));
    $actions = collect($chapter['commands'] ?? [])->pluck('label')->all();

    if (empty($actions)) {
        $actions = collect($chapter['files'] ?? [])
            ->take(8)
            ->map(fn (array $file) => 'Создать: '.$file['path'])
            ->all();
    }
  @endphp

  <nav class="lab-crumbs" aria-label="Хлебные крошки">
    <a href="{{ route('cases.index') }}">Разборы</a>
    <span>/</span>
    <span>{{ $case->title }}</span>
    <span>/</span>
    <span>Шаг {{ $chapterIndex }}</span>
  </nav>

  <header class="lab-head">
    <div>
      <span class="kicker">Разбор проекта · шаг {{ $chapterIndex }} из {{ $total }}</span>
      <h1>{{ $case->title }}</h1>
      <p class="lead">{{ $case->summary }}</p>
    </div>
    <div class="lab-head-meta">
      <span>{{ $case->duration }} мин</span>
      <span>{{ $case->level }}</span>
      @if ($variant)
        <a href="{{ route('variants.show', $variant) }}">ТЗ проекта</a>
      @endif
    </div>
  </header>

  <section class="case-plan">
    <div class="case-plan-head">
      <div>
        <span class="kicker">План</span>
        <h2>Что делать по шагам</h2>
      </div>
      <a class="btn btn-ghost btn-sm" href="{{ route('cases.chronicle', $case) }}">Хроника: как это делалось →</a>
      <a class="btn btn-ghost btn-sm" href="{{ route('build.index') }}">Собрать руками →</a>
    </div>

    <ol class="case-plan-list">
      @foreach ($chapters as $index => $item)
        <li class="{{ $index + 1 === $chapterIndex ? 'is-current' : '' }}">
          <a href="{{ route('cases.show', ['case' => $case, 'chapter' => $index + 1]) }}">
            {{ $index + 1 }}. {{ $item['title'] }}
          </a>
          <span>{!! $inline($item['goal']) !!}</span>
        </li>
      @endforeach
    </ol>

    <p class="case-plan-note">
      Вы смотрите один шаг за раз. Код открыт: читайте объяснение, затем файлы.
      Кнопки «Назад» и «Дальше» внизу переключают шаги.
    </p>
  </section>

  <div class="case-layout">
    <aside class="case-toc">
      <span class="kicker">Шаги</span>
      <ol>
        @foreach ($chapters as $index => $item)
          <li class="{{ $index + 1 === $chapterIndex ? 'is-current' : '' }}">
            <a href="{{ route('cases.show', ['case' => $case, 'chapter' => $index + 1]) }}">
              {{ $item['title'] }}
            </a>
          </li>
        @endforeach
      </ol>
      <a class="case-toc-link" href="{{ route('build.index') }}">Собрать руками →</a>
    </aside>

    <div class="case-body">
      <section class="case-chapter" id="chapter-{{ $chapterIndex }}">
        <span class="case-chapter-num">Глава {{ $chapterIndex }} из {{ $total }}</span>
        <h2>{{ $chapter['title'] }}</h2>
        <p class="case-goal">{!! $inline($chapter['goal']) !!}</p>

        @if (! empty($actions))
          <div class="case-do">
            <strong>Что делать</strong>
            <ul>
              @foreach ($actions as $action)
                <li>{!! $inline($action) !!}</li>
              @endforeach
            </ul>
          </div>
        @endif

        @foreach ($chapter['paragraphs'] as $paragraph)
          <p>{!! $inline($paragraph) !!}</p>
        @endforeach

        @if (! empty($chapter['commands']))
          <h3>Команды</h3>
          @foreach ($chapter['commands'] as $command)
            @php $cmdIndex = $loop->iteration; @endphp
            <div class="build-command">
              <div class="build-command-head">
                <b>{{ $command['label'] }}</b>
                <button type="button" class="copy-btn" data-copy="case-{{ $case->id }}-cmd-{{ $chapterIndex }}-{{ $cmdIndex }}">Копировать</button>
              </div>
              <pre id="case-{{ $case->id }}-cmd-{{ $chapterIndex }}-{{ $cmdIndex }}"><code>{{ $command['code'] }}</code></pre>
            </div>
          @endforeach
        @endif

        @if (! empty($chapter['files']))
          <h3>Код</h3>
          @foreach ($chapter['files'] as $file)
            @php $fileIndex = $loop->iteration; @endphp
            <details class="build-file" open>
              <summary>
                <span class="build-file-path">{{ $file['path'] }}</span>
                <span class="build-file-lang">{{ $file['language'] }}</span>
                <button type="button" class="copy-btn" data-copy="case-{{ $case->id }}-file-{{ $chapterIndex }}-{{ $fileIndex }}">Копировать</button>
              </summary>
              <pre id="case-{{ $case->id }}-file-{{ $chapterIndex }}-{{ $fileIndex }}"><code>{{ $file['code'] }}</code></pre>
            </details>
          @endforeach
        @endif

        <div class="case-notes">
          <div>
            <h3>Что проверяет эксперт</h3>
            <ul>
              @foreach ($chapter['expert'] as $item)
                <li>{!! $inline($item) !!}</li>
              @endforeach
            </ul>
          </div>
          <div>
            <h3>Типичные ошибки</h3>
            <ul class="case-mistakes">
              @foreach ($chapter['mistakes'] as $item)
                <li>{!! $inline($item) !!}</li>
              @endforeach
            </ul>
          </div>
        </div>
      </section>

      <div class="case-step-nav">
        @if ($chapterIndex > 1)
          <a class="btn btn-ghost" href="{{ route('cases.show', ['case' => $case, 'chapter' => $chapterIndex - 1]) }}">
            ← {{ $chapters[$chapterIndex - 2]['title'] }}
          </a>
        @endif

        @if ($chapterIndex < $total)
          <a class="btn btn-accent" href="{{ route('cases.show', ['case' => $case, 'chapter' => $chapterIndex + 1]) }}">
            Дальше: {{ $chapters[$chapterIndex]['title'] }} →
          </a>
        @else
          <a class="btn btn-accent" href="{{ route('cases.index') }}">К списку разборов →</a>
        @endif
      </div>

      <div class="build-actions">
        <a class="btn btn-ghost" href="{{ route('cases.index') }}">Другой разбор</a>
        @if ($variant)
          <a class="btn btn-ghost" href="{{ route('variants.show', $variant) }}">Открыть ТЗ</a>
        @endif
      </div>
    </div>
  </div>

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
  </script>
@endsection
