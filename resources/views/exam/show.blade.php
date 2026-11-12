@extends('labs.layout')

@section('title', 'Экзамен · '.$variant->title)

@section('content')
  @php
    $inline = fn (string $text) => preg_replace('/`([^`]+)`/', '<code>$1</code>', e($text));
  @endphp

  <nav class="lab-crumbs" aria-label="Хлебные крошки">
    <a href="{{ route('exam.index') }}">Экзамен</a>
    <span>/</span>
    <span>{{ $variant->title }}</span>
  </nav>

  <header class="lab-head exam-head">
    <div>
      <span class="kicker">Экзаменационный прогон · без подсказок</span>
      <h1>{{ $variant->title }}</h1>
      <p class="lead">{{ $variant->summary }}</p>
    </div>
    <div class="exam-timer" data-remaining="{{ $remaining }}">
      <span>Осталось</span>
      <b id="examTimer">{{ gmdate('H:i:s', $remaining) }}</b>
      <small>3:30 на всё</small>
    </div>
  </header>

  <div class="exam-layout">
    <section class="exam-brief">
      <div class="exam-brief-head">
        <h2>Задание</h2>
        <a href="{{ route('variants.show', $variant) }}" target="_blank" rel="noopener">Открыть полное ТЗ ↗</a>
      </div>

      <h3>Сущности</h3>
      <div class="variant-table-wrap">
        <table class="variant-table">
          <thead>
            <tr>
              <th>Таблица</th>
              <th>Поля</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($variant->entities as $entity)
              <tr>
                <td><code>{{ $entity['name'] }}</code></td>
                <td>
                  @foreach ($entity['fields'] as $field)
                    <code>{{ $field['name'] }}</code>@if (! $loop->last), @endif
                  @endforeach
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <h3>Статусы</h3>
      <p class="exam-statuses">
        @foreach ($variant->statuses as $status)
          <span><code>{{ $status['key'] }}</code> — {{ $status['label'] }}</span>
        @endforeach
      </p>

      <h3>Требования</h3>
      <ul class="drill-req">
        @foreach ($variant->requirements as $requirement)
          <li>{!! $inline($requirement) !!}</li>
        @endforeach
      </ul>

      <div class="exam-admin">
        <span class="kicker">Администратор</span>
        <p>
          Логин: <code>{{ $variant->admin_login }}</code><br>
          Пароль: <code>{{ $variant->admin_password }}</code>
        </p>
      </div>
    </section>

    <aside class="exam-side">
      <form method="POST" action="{{ route('exam.finish', $session) }}" class="exam-form">
        @csrf

        <div class="exam-form-head">
          <div>
            <span class="kicker">Самопроверка</span>
            <h2>Чек-лист</h2>
          </div>
          <b id="examChecked">0 / {{ count($checklist) }}</b>
        </div>

        <p class="exam-hint">Отмечайте только то, что реально работает в проекте. Каждый пункт — 5 баллов.</p>

        <div class="exam-checks">
          @foreach ($checklist as $key => $label)
            <label class="check">
              <input type="checkbox" name="checklist[]" value="{{ $key }}">
              <span class="check-box" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M4 12.5l5 5L20 6.5"/></svg>
              </span>
              <span class="check-text">{!! $inline($label) !!}</span>
            </label>
          @endforeach
        </div>

        <div class="field">
          <label for="notes">Что не успели / что проверить</label>
          <textarea id="notes" name="notes" rows="4" placeholder="Например: не сделал отзыв, не проверил 403 на чужую заявку">{{ old('notes', $session->notes) }}</textarea>
        </div>

        <div class="exam-actions">
          <button type="submit" class="btn btn-accent">Завершить и проверить</button>
          <a class="btn btn-ghost" href="{{ route('exam.index') }}">Выйти</a>
        </div>
      </form>
    </aside>
  </div>

  <script>
    (function () {
      var box = document.querySelector('.exam-timer');
      var label = document.getElementById('examTimer');

      if (!box || !label) return;

      var left = parseInt(box.getAttribute('data-remaining'), 10) || 0;

      function tick() {
        if (left <= 0) {
          label.textContent = '00:00:00';
          box.classList.add('is-over');
          return;
        }

        var h = String(Math.floor(left / 3600)).padStart(2, '0');
        var m = String(Math.floor((left % 3600) / 60)).padStart(2, '0');
        var s = String(left % 60).padStart(2, '0');

        label.textContent = h + ':' + m + ':' + s;
        left--;
        window.setTimeout(tick, 1000);
      }

      tick();

      var boxes = document.querySelectorAll('input[name="checklist[]"]');
      var counter = document.getElementById('examChecked');

      function update() {
        var checked = document.querySelectorAll('input[name="checklist[]"]:checked').length;
        if (counter) counter.textContent = checked + ' / ' + boxes.length;
      }

      Array.prototype.forEach.call(boxes, function (item) {
        item.addEventListener('change', update);
      });

      update();
    })();
  </script>
@endsection
