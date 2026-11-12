@extends('labs.layout')

@section('title', 'Хроника: '.$case->title)

@section('content')
  @php
    $inline = fn (string $text) => preg_replace('/`([^`]+)`/', '<code>$1</code>', e($text));
  @endphp

  <nav class="lab-crumbs" aria-label="Хлебные крошки">
    <a href="{{ route('cases.index') }}">Разборы</a>
    <span>/</span>
    <a href="{{ route('cases.show', $case) }}">{{ $case->title }}</a>
    <span>/</span>
    <span>Хроника</span>
  </nav>

  <header class="lab-head">
    <div>
      <span class="kicker">Хроника сборки · от пустой папки до релиза</span>
      <h1>{{ $case->title }}</h1>
      <p class="lead">
        Как проект собирался по шагам: команды, их вывод, полный код каждого файла,
        коммиты и релиз. Это не справочник, а последовательность действий — можно читать и повторять.
      </p>
    </div>
    <div class="lab-head-meta">
      <span>{{ $case->duration }} мин</span>
      <span>{{ count($chronicle) }} записей</span>
      <a href="{{ route('cases.show', $case) }}">Смотреть по шагам</a>
    </div>
  </header>

  <div class="chronicle">
    @foreach ($chronicle as $index => $entry)
      <article class="chronicle-entry">
        <div class="chronicle-time">
          <b>{{ $entry['time'] }}</b>
          <span>шаг {{ $index + 1 }}</span>
        </div>

        <div class="chronicle-body">
          <h2>{{ $entry['title'] }}</h2>
          <p class="chronicle-goal">{!! $inline($entry['goal']) !!}</p>

          @foreach (($entry['paragraphs'] ?? []) as $paragraph)
            <p>{!! $inline($paragraph) !!}</p>
          @endforeach

          @foreach (($entry['commands'] ?? []) as $commandIndex => $command)
            <div class="chronicle-command">
              <div class="chronicle-command-head">
                <b>{{ $command['label'] }}</b>
                <button type="button" class="copy-btn" data-copy="chron-{{ $case->id }}-{{ $index }}-{{ $commandIndex }}">Копировать</button>
              </div>
              <pre class="chronicle-cmd" id="chron-{{ $case->id }}-{{ $index }}-{{ $commandIndex }}"><code>$ {{ $command['code'] }}</code></pre>
              @if (! empty($command['output']))
                <pre class="chronicle-output"><code>{{ $command['output'] }}</code></pre>
              @endif
            </div>
          @endforeach

          @if (! empty($entry['files']))
            <div class="chronicle-files">
              <b>Код шага</b>
              @foreach ($entry['files'] as $fileIndex => $file)
                @if (is_array($file))
                  <details class="build-file" open>
                    <summary>
                      <span class="build-file-path">{{ $file['path'] }}</span>
                      <span class="build-file-lang">{{ $file['language'] }}</span>
                      <button type="button" class="copy-btn" data-copy="chron-{{ $case->id }}-{{ $index }}-file-{{ $fileIndex }}">Копировать</button>
                    </summary>
                    <pre id="chron-{{ $case->id }}-{{ $index }}-file-{{ $fileIndex }}"><code>{{ $file['code'] }}</code></pre>
                  </details>
                @else
                  <ul>
                    <li><code>{{ $file }}</code></li>
                  </ul>
                @endif
              @endforeach
            </div>
          @endif

          @if (! empty($entry['commit']))
            <div class="chronicle-commit">
              <b>Коммит</b>
              <pre><code>{{ $entry['commit']['output'] }}</code></pre>
            </div>
          @endif

          <p class="chronicle-result"><b>Итог:</b> {!! $inline($entry['result']) !!}</p>
        </div>
      </article>
    @endforeach
  </div>

  <div class="build-actions">
    <a class="btn btn-accent" href="{{ route('cases.show', $case) }}">Смотреть по шагам →</a>
    <a class="btn btn-ghost" href="{{ route('cases.index') }}">Другой разбор</a>
    @if ($variant)
      <a class="btn btn-ghost" href="{{ route('variants.show', $variant) }}">Открыть ТЗ</a>
    @endif
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
