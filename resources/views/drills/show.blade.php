@extends('labs.layout')

@section('title', $drill->title)

@section('content')
  @php
    $inline = fn (string $text) => preg_replace('/`([^`]+)`/', '<code>$1</code>', e($text));
  @endphp

  <nav class="lab-crumbs" aria-label="Хлебные крошки">
    <a href="{{ route('drills.index') }}">Тренажёр</a>
    <span>/</span>
    <span>{{ $drill->trackLabel() }}</span>
  </nav>

  <header class="lab-head">
    <div>
      <span class="kicker">Вариант · {{ $drill->trackLabel() }} · {{ $drill->level }}</span>
      <h1>{{ $drill->title }}</h1>
      <p class="lead">{!! $inline($drill->prompt) !!}</p>
    </div>
    <div class="lab-head-meta">
      <span>{{ $drill->minutes }} мин</span>
      <span>{{ $drill->level }}</span>
      @if ($progress->passes > 0 && $progress->next_review_at)
        <span>Повтор: {{ $progress->next_review_at->format('d.m') }}</span>
      @endif
    </div>
  </header>

  @if ($errors->any())
    <div class="alert alert--error">{{ $errors->first() }}</div>
  @endif

  <div class="lab-split">
    <section class="lab-brief">
      <h2>Требования</h2>
      <ul class="drill-req">
        @foreach ($drill->requirements as $requirement)
          <li>{!! $inline($requirement) !!}</li>
        @endforeach
      </ul>

      @if ($progress->passes > 0)
        <details class="lab-solution">
          <summary>Показать решение</summary>
          <pre><code>{{ $drill->solution }}</code></pre>
        </details>
      @else
        <p class="lab-locked">Решение откроется после успешной проверки. Сначала попробуйте сами.</p>
      @endif

      @if ($attempts->isNotEmpty())
        <h3>Последние попытки</h3>
        <ul class="drill-attempts">
          @foreach ($attempts as $attempt)
            <li>
              <span class="{{ $attempt->passed ? 'is-ok' : 'is-fail' }}">{{ $attempt->passed ? 'сдано' : 'ошибки' }}</span>
              {{ $attempt->created_at->format('d.m H:i') }}
            </li>
          @endforeach
        </ul>
      @endif
    </section>

    <section class="lab-work">
      <div class="drill-timer" data-minutes="{{ $drill->minutes }}">
        <span>Ориентир по времени</span>
        <b id="drillTimer">{{ $drill->minutes }}:00</b>
      </div>

      <form method="POST" action="{{ route('drills.check', $drill) }}" class="lab-form">
        @csrf

        <div class="lab-editor-head">
          <label for="code">Ваш код · {{ $drill->language }}</label>
          <button type="button" class="link-btn" id="resetCode" data-starter="{{ $drill->starter_code }}">Сбросить к заготовке</button>
        </div>

        <textarea id="code" name="code" class="lab-editor" spellcheck="false"
                  aria-label="Код решения">{{ old('code', $progress->last_code ?? $drill->starter_code) }}</textarea>

        <div class="lab-actions">
          <button type="submit" class="btn btn-accent">Проверить</button>
          <a class="btn btn-ghost" href="{{ route('drills.next') }}">Следующий вариант</a>
          <span class="lab-shortcut">Ctrl + Enter — проверить</span>
        </div>
      </form>

      @if ($lastAttempt)
        <div id="result" class="lab-results {{ $lastAttempt->passed ? 'is-passed' : 'is-failed' }}">
          <div class="lab-results-head">
            <div>
              <span class="kicker">{{ $lastAttempt->passed ? 'принято' : 'есть ошибки' }}</span>
              <h2>{{ $lastAttempt->passed ? 'Вариант решён' : 'Проверка не пройдена' }}</h2>
            </div>
            <span class="lab-attempts">Попыток: {{ $progress->attempts }}</span>
          </div>

          <div class="lab-result-list">
            @foreach ($lastAttempt->results ?? [] as $result)
              <div class="lab-result {{ $result['passed'] ? 'is-pass' : 'is-fail' }}">
                <span class="lab-result-mark">{{ $result['passed'] ? '✓' : '×' }}</span>
                <span>
                  <b>{{ $result['label'] }}</b>
                  <small>{{ $result['message'] }}</small>
                </span>
              </div>
            @endforeach
          </div>

          @if ($lastAttempt->passed)
            <div class="lab-passed">
              <p>
                Критерии закрыты. Следующий повтор — {{ $progress->next_review_at?->format('d.m') }}.
                Если хотите закрепить, решите похожий вариант сейчас.
              </p>
              @if ($next)
                <a class="btn btn-accent" href="{{ route('drills.show', $next) }}">Следующий: {{ $next->title }} →</a>
              @endif
            </div>
          @else
            <p class="lab-retry">Поправьте код и отправьте снова. Ошибки выше показывают, какие критерии не закрыты.</p>
          @endif
        </div>
      @else
        <div class="lab-empty">
          Напишите решение и нажмите «Проверить». Проверка локальная: PHP проходит синтаксическую проверку, SQL выполняется на учебной базе.
        </div>
      @endif
    </section>
  </div>

  <script>
    (function () {
      var box = document.querySelector('.drill-timer');
      var label = document.getElementById('drillTimer');

      if (!box || !label) return;

      var total = parseInt(box.getAttribute('data-minutes'), 10) * 60;
      var started = Date.now();

      function tick() {
        var left = total - Math.floor((Date.now() - started) / 1000);

        if (left <= 0) {
          label.textContent = '00:00';
          box.classList.add('is-over');
          return;
        }

        var minutes = String(Math.floor(left / 60)).padStart(2, '0');
        var seconds = String(left % 60).padStart(2, '0');
        label.textContent = minutes + ':' + seconds;
        window.setTimeout(tick, 1000);
      }

      tick();
    })();
  </script>
@endsection
