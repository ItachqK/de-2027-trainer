@extends('labs.layout')

@section('title', 'Проект по теме')

@section('content')
  <section class="lab-hero">
    <div>
      <span class="kicker">Любая тема → готовый проект по КИМ</span>
      <h1>Проект по теме: команды и код</h1>
      <p class="lead">
        Введите тему — например, «Обмен книгами» или «Пицца на районе». Генератор соберёт
        пошаговую инструкцию на 4 часа: команды, полный код каждого файла, проверки и типичные ошибки.
        Плюс готовый архив проекта, который можно распаковать поверх свежего Laravel.
      </p>
    </div>
    <div class="lab-hero-progress">
      <span>Формат экзамена</span>
      <b>75 + 25</b>
      <div class="bar"><i style="width: 75%"></i></div>
      <small>75 баллов — инвариант КИМ, 25 — вариативная часть</small>
    </div>
  </section>

  @if (session('blueprint_error'))
    <div class="alert alert--error" role="alert">{{ session('blueprint_error') }}</div>
  @endif

  <div class="topic-note">
    <b>Тема любая, критерии одни.</b>
    Меняются название проекта, объект заявки, статусы и способы оплаты. Не меняются:
    пять страниц, две роли, три статуса, два способа оплаты, отзыв после завершения,
    валидация и безопасность. Инструкция ниже собирается под вашу тему, но структура остаётся экзаменационной.
  </div>

  <form method="POST" action="{{ route('blueprint.build') }}" class="topic-form">
    @csrf

    <fieldset class="topic-group">
      <legend>1. Тема проекта</legend>
      <div class="topic-grid">
        <div class="field field--wide">
          <label for="topic">Тема</label>
          <input type="text" id="topic" name="topic" value="{{ old('topic', $input['topic']) }}"
                 placeholder="Обмен книгами" required autofocus>
          <span class="field-hint">Одно словосочетание: «Пицца на районе», «Ремонт часов», «Фотостудия».</span>
        </div>

        <div class="field">
          <label for="project_name">Название проекта</label>
          <input type="text" id="project_name" name="project_name" value="{{ old('project_name', $input['project_name']) }}" placeholder="{{ $preview['project_name'] }}">
          <span class="field-hint">Пусто — возьмём тему как есть.</span>
        </div>

        <div class="field">
          <label for="object_singular">Что бронируют</label>
          <input type="text" id="object_singular" name="object_singular" value="{{ old('object_singular', $input['object_singular']) }}" placeholder="{{ $preview['object_singular'] }}">
          <span class="field-hint">Одно слово: книга, столик, номер.</span>
        </div>
      </div>
    </fieldset>

    <details class="bp-details">
      <summary>Уточнить детали под свою тему <span>необязательно: пустые поля заполнятся по теме</span></summary>

      <fieldset class="topic-group">
        <legend>2. Предметная область</legend>
        <div class="topic-grid">
          <div class="field field--wide">
            <label for="purpose">Для чего система</label>
            <input type="text" id="purpose" name="purpose" value="{{ old('purpose', $input['purpose']) }}" placeholder="{{ $preview['purpose'] }}">
            <span class="field-hint">Продолжение фразы «информационная система для …».</span>
          </div>

          <div class="field">
            <label for="object_plural">Множественное число</label>
            <input type="text" id="object_plural" name="object_plural" value="{{ old('object_plural', $input['object_plural']) }}" placeholder="{{ $preview['object_plural'] }}">
          </div>

          <div class="field">
            <label for="date_label">Название даты</label>
            <input type="text" id="date_label" name="date_label" value="{{ old('date_label', $input['date_label']) }}" placeholder="{{ $preview['date_label'] }}">
          </div>

          <div class="field field--wide">
            <label for="object_options">Варианты выбора</label>
            <input type="text" id="object_options" name="object_options" value="{{ old('object_options', $input['object_options']) }}" placeholder="{{ $preview['object_options'] }}">
            <span class="field-hint">Через запятую, минимум два: «роман, учебник, детская книга».</span>
          </div>
        </div>
      </fieldset>

      <fieldset class="topic-group">
        <legend>3. Статусы и оплата</legend>
        <div class="topic-grid">
          <div class="field">
            <label for="status_new">Первый статус</label>
            <input type="text" id="status_new" name="status_new" value="{{ old('status_new', $input['status_new']) }}" placeholder="{{ $preview['status_new'] }}">
          </div>
          <div class="field">
            <label for="status_assigned">Второй статус</label>
            <input type="text" id="status_assigned" name="status_assigned" value="{{ old('status_assigned', $input['status_assigned']) }}" placeholder="{{ $preview['status_assigned'] }}">
          </div>
          <div class="field">
            <label for="status_done">Третий статус</label>
            <input type="text" id="status_done" name="status_done" value="{{ old('status_done', $input['status_done']) }}" placeholder="{{ $preview['status_done'] }}">
          </div>
          <div class="field">
            <label for="pay_1">Первый способ оплаты</label>
            <input type="text" id="pay_1" name="pay_1" value="{{ old('pay_1', $input['pay_1']) }}" placeholder="{{ $preview['pay_1'] }}">
          </div>
          <div class="field">
            <label for="pay_2">Второй способ оплаты</label>
            <input type="text" id="pay_2" name="pay_2" value="{{ old('pay_2', $input['pay_2']) }}" placeholder="{{ $preview['pay_2'] }}">
          </div>
          <div class="field">
            <label for="review_text">Что оценивает отзыв</label>
            <input type="text" id="review_text" name="review_text" value="{{ old('review_text', $input['review_text']) }}" placeholder="{{ $preview['review_text'] }}">
          </div>
        </div>
      </fieldset>

      <fieldset class="topic-group">
        <legend>4. Администратор</legend>
        <div class="topic-grid">
          <div class="field">
            <label for="admin_login">Логин</label>
            <input type="text" id="admin_login" name="admin_login" value="{{ old('admin_login', $input['admin_login']) }}" placeholder="{{ $preview['admin_login'] }}">
          </div>
          <div class="field">
            <label for="admin_password">Пароль</label>
            <input type="text" id="admin_password" name="admin_password" value="{{ old('admin_password', $input['admin_password']) }}" placeholder="{{ $preview['admin_password'] }}">
          </div>
        </div>
      </fieldset>
    </details>

    <div class="topic-actions">
      <button type="submit" class="btn btn-accent">Собрать проект →</button>
      @if ($result)
        <a class="btn btn-ghost" href="{{ route('blueprint.download') }}">Скачать инструкцию .md</a>
        <a class="btn btn-ghost" href="{{ route('blueprint.project') }}">Скачать проект .zip</a>
        <a class="btn btn-ghost" href="{{ route('blueprint.index') }}">Новая тема</a>
      @endif
      <a class="btn btn-ghost" href="{{ route('topic.index') }}">Сначала ТЗ на тему</a>
    </div>
  </form>

  @if ($result)
    @php($d = $result['data'])

    <section class="topic-result" id="result">
      <div class="section-head">
        <h2>{{ $d['project_name'] }} — инструкция и проект</h2>
        <p>Собрано под вашу тему. Файлы в архиве уже содержат нужные названия, статусы и оплату.</p>
      </div>

      <div class="bp-stats">
        <div><b>{{ $result['stats']['chapters'] }}</b><span>глав</span></div>
        <div><b>{{ $result['stats']['files'] }}</b><span>файлов</span></div>
        <div><b>{{ $result['stats']['minutes'] }}</b><span>минут</span></div>
        <div><b>{{ $result['stats']['tests'] }}</b><span>тестов</span></div>
        <div><b>{{ number_format($result['stats']['lines'], 0, ',', ' ') }}</b><span>строк кода</span></div>
      </div>

      <div class="bp-summary">
        <div><span>Объект заявки</span><b>{{ $d['object_singular'] }}</b></div>
        <div><span>Варианты</span><b>{{ $d['object_options'] }}</b></div>
        <div><span>Статусы</span><b>{{ $d['status_new'] }} → {{ $d['status_assigned'] }} → {{ $d['status_done'] }}</b></div>
        <div><span>Оплата</span><b>{{ $d['pay_1'] }} / {{ $d['pay_2'] }}</b></div>
        <div><span>Администратор</span><b>{{ $d['admin_login'] }} / {{ $d['admin_password'] }}</b></div>
      </div>

      <article class="topic-card">
        <h3>План на 4 часа</h3>
        <p class="topic-hint">Старт в 09:00. В конце каждого этапа — проверка, чтобы не тащить ошибку дальше.</p>
        <div class="bp-plan-wrap">
          <table class="bp-plan">
            <thead>
              <tr><th>#</th><th>Этап</th><th>Время</th><th>Минут</th></tr>
            </thead>
            <tbody>
              @foreach ($result['plan'] as $row)
                <tr>
                  <td>{{ $row['n'] }}</td>
                  <td>{{ $row['title'] }}</td>
                  <td>{{ $row['from'] }}–{{ $row['to'] }}</td>
                  <td>{{ $row['minutes'] }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </article>

      @foreach ($result['chapters'] as $chapter)
        <article class="topic-card bp-chapter">
          <header class="bp-chapter-head">
            <span class="bp-chapter-n">{{ $chapter['n'] }}</span>
            <div>
              <h3>{{ $chapter['title'] }}</h3>
              <p class="topic-hint">{{ $chapter['minutes'] }} минут · {{ count($chapter['code']) }} файлов</p>
            </div>
          </header>

          <p>{{ $chapter['goal'] }}</p>

          @if ($chapter['commands'])
            <h4>Команды</h4>
            <div class="code">
              <div class="code-head">
                <span class="code-file"><b>терминал</b></span>
                <button class="copy-btn" type="button" data-copy>Копировать</button>
              </div>
              <pre><code>{{ implode("\n", $chapter['commands']) }}</code></pre>
            </div>
          @endif

          @if ($chapter['code'])
            <h4>Код</h4>
            @foreach ($chapter['code'] as $path => $code)
              <details class="bp-file">
                <summary><code>{{ $path }}</code><span>{{ substr_count($code, "\n") + 1 }} строк</span></summary>
                <div class="code">
                  <div class="code-head">
                    <span class="code-file"><b>{{ $path }}</b></span>
                    <button class="copy-btn" type="button" data-copy>Копировать</button>
                  </div>
                  <pre><code>{{ rtrim($code) }}</code></pre>
                </div>
              </details>
            @endforeach
          @endif

          <div class="bp-two">
            <div>
              <h4>Проверка</h4>
              <ul class="topic-list">
                @foreach ($chapter['checks'] as $check)
                  <li>{{ $check }}</li>
                @endforeach
              </ul>
            </div>
            <div>
              <h4>Типичные ошибки</h4>
              <ul class="topic-list topic-list--warn">
                @foreach ($chapter['mistakes'] as $mistake)
                  <li>{{ $mistake }}</li>
                @endforeach
              </ul>
            </div>
          </div>
        </article>
      @endforeach

      <article class="topic-card">
        <h3>Чек-лист 75 баллов</h3>
        <p class="topic-hint">Проверяйте перед защитой: каждый пункт — это баллы, которые легко потерять.</p>
        @foreach ($result['checklist'] as $group)
          <div class="topic-criteria">
            <h4>{{ $group['group'] }}</h4>
            <ul>
              @foreach ($group['items'] as $item)
                <li><span>{{ $item[1] }}</span><b>{{ $item[0] }}</b></li>
              @endforeach
            </ul>
          </div>
        @endforeach
      </article>

      <article class="topic-card">
        <h3>Защита: что сказать</h3>
        <blockquote class="bp-speech">{{ $result['defense']['speech'] }}</blockquote>

        <h4>Демонстрация</h4>
        <ol class="bp-demo">
          @foreach ($result['defense']['demo'] as $step)
            <li>{{ $step }}</li>
          @endforeach
        </ol>

        <h4>Вопросы и ответы</h4>
        <div class="bp-qa">
          @foreach ($result['defense']['questions'] as $pair)
            <details>
              <summary>{{ $pair[0] }}</summary>
              <p>{{ $pair[1] }}</p>
            </details>
          @endforeach
        </div>
      </article>

      <article class="topic-card">
        <h3>Как развернуть архив</h3>
        <ol class="bp-demo">
          <li>Создайте проект: <code>composer create-project laravel/laravel {{ $d['slug'] }}</code>.</li>
          <li>Распакуйте архив в папку проекта и подтвердите замену файлов.</li>
          <li>Выполните команды из главы 1, затем <code>php artisan migrate:fresh --seed</code>.</li>
          <li>Запустите <code>php artisan serve</code> и откройте <code>http://127.0.0.1:8000</code>.</li>
        </ol>
        <div class="topic-actions">
          <a class="btn btn-accent" href="{{ route('blueprint.project') }}">Скачать проект .zip</a>
          <a class="btn btn-ghost" href="{{ route('blueprint.download') }}">Скачать инструкцию .md</a>
          <a class="btn btn-ghost" href="{{ route('blueprint.index') }}">Новая тема</a>
        </div>
      </article>
    </section>

    <script>
      document.querySelectorAll('[data-copy]').forEach(function (button) {
        button.addEventListener('click', function () {
          var block = button.closest('.code');
          var code = block ? block.querySelector('code') : null;
          if (!code) return;
          navigator.clipboard.writeText(code.textContent).then(function () {
            var old = button.textContent;
            button.textContent = 'Скопировано';
            button.classList.add('is-copied');
            setTimeout(function () {
              button.textContent = old;
              button.classList.remove('is-copied');
            }, 1600);
          });
        });
      });
    </script>
  @endif
@endsection
