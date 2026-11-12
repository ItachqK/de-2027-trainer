@extends('labs.layout')

@section('title', 'Своя тема')

@section('content')
  <section class="lab-hero">
    <div>
      <span class="kicker">Вариативная часть · своя предметная область</span>
      <h1>Своя тема по критериям КИМ</h1>
      <p class="lead">
        На демоэкзамене тема не фиксирована: образовательная организация определяет её сама.
        Критерии, баллы и структура остаются из КИМ. Заполните поля своим предметом —
        получите техническое задание, модель базы данных и чек-лист проверки.
      </p>
    </div>
    <div class="lab-hero-progress">
      <span>Инвариант / вариатив</span>
      <b>75 + 25</b>
      <div class="bar"><i style="width: 75%"></i></div>
      <small>75 баллов — общие критерии, 25 — вариативная часть</small>
    </div>
  </section>

  <div class="topic-note">
    <b>Что это значит на практике.</b>
    Конференции, пиццерия, обмен книгами, салон — тема любая. Эксперт проверяет одно и то же:
    регистрацию с валидацией, вход, заявки только свои, админку со статусами, безопасность и адаптивный дизайн.
    Меняются слова, не сценарий.
  </div>

  <form method="POST" action="{{ route('topic.build') }}" class="topic-form">
    @csrf

    @if ($errors->any())
      <div class="alert alert--error">{{ $errors->first() }}</div>
    @endif

    <fieldset class="topic-group">
      <legend>1. Тема</legend>
      <div class="topic-grid">
        <div class="field">
          <label for="project_name">Название проекта</label>
          <input type="text" id="project_name" name="project_name" value="{{ old('project_name', $input['project_name']) }}" required>
          <span class="field-hint">Например: «Конференции.РФ», «Пицца на районе», «Обмен книгами».</span>
        </div>

        <div class="field field--wide">
          <label for="purpose">Для чего система</label>
          <input type="text" id="purpose" name="purpose" value="{{ old('purpose', $input['purpose']) }}" required>
          <span class="field-hint">Продолжите фразу «информационная система для …».</span>
        </div>

        <div class="field">
          <label for="object_singular">Что бронируют или заказывают</label>
          <input type="text" id="object_singular" name="object_singular" value="{{ old('object_singular', $input['object_singular']) }}" required>
          <span class="field-hint">Одно слово: помещение, столик, книгу, услугу.</span>
        </div>

        <div class="field">
          <label for="object_plural">То же во множественном числе</label>
          <input type="text" id="object_plural" name="object_plural" value="{{ old('object_plural', $input['object_plural']) }}" required>
          <span class="field-hint">Помещения, столики, книги, услуги.</span>
        </div>

        <div class="field field--wide">
          <label for="object_options">Варианты выбора</label>
          <input type="text" id="object_options" name="object_options" value="{{ old('object_options', $input['object_options']) }}" required>
          <span class="field-hint">Через запятую: аудитория, коворкинг, кинозал.</span>
        </div>

        <div class="field">
          <label for="event_word">Что проводится</label>
          <input type="text" id="event_word" name="event_word" value="{{ old('event_word', $input['event_word']) }}" required>
          <span class="field-hint">Конференция, встреча, примерка, доставка.</span>
        </div>

        <div class="field">
          <label for="date_label">Какая дата указывается</label>
          <input type="text" id="date_label" name="date_label" value="{{ old('date_label', $input['date_label']) }}" required>
          <span class="field-hint">Дата начала конференции, дата доставки, время визита.</span>
        </div>
      </div>
    </fieldset>

    <fieldset class="topic-group">
      <legend>2. Статусы, оплата, отзыв</legend>
      <div class="topic-grid">
        <div class="field">
          <label for="status_new">Статус при создании</label>
          <input type="text" id="status_new" name="status_new" value="{{ old('status_new', $input['status_new']) }}" required>
        </div>

        <div class="field">
          <label for="status_assigned">Статус после назначения</label>
          <input type="text" id="status_assigned" name="status_assigned" value="{{ old('status_assigned', $input['status_assigned']) }}" required>
        </div>

        <div class="field">
          <label for="status_done">Финальный статус</label>
          <input type="text" id="status_done" name="status_done" value="{{ old('status_done', $input['status_done']) }}" required>
        </div>

        <div class="field">
          <label for="pay_1">Первый способ оплаты</label>
          <input type="text" id="pay_1" name="pay_1" value="{{ old('pay_1', $input['pay_1']) }}" required>
        </div>

        <div class="field">
          <label for="pay_2">Второй способ оплаты</label>
          <input type="text" id="pay_2" name="pay_2" value="{{ old('pay_2', $input['pay_2']) }}" required>
        </div>

        <div class="field field--wide">
          <label for="review_text">Что оценивает пользователь</label>
          <input type="text" id="review_text" name="review_text" value="{{ old('review_text', $input['review_text']) }}" required>
          <span class="field-hint">Отзыв о качестве мероприятия, обслуживания, доставки.</span>
        </div>
      </div>
    </fieldset>

    <fieldset class="topic-group">
      <legend>3. Администратор</legend>
      <div class="topic-grid">
        <div class="field">
          <label for="admin_login">Логин администратора</label>
          <input type="text" id="admin_login" name="admin_login" value="{{ old('admin_login', $input['admin_login']) }}" required>
        </div>

        <div class="field">
          <label for="admin_password">Пароль администратора</label>
          <input type="text" id="admin_password" name="admin_password" value="{{ old('admin_password', $input['admin_password']) }}" required>
        </div>
      </div>
    </fieldset>

    <div class="topic-actions">
      <button type="submit" class="btn btn-accent">Собрать ТЗ →</button>
      <button type="submit" formaction="{{ route('topic.download') }}" class="btn btn-ghost">Скачать .md</button>
      <a class="btn btn-ghost" href="{{ route('blueprint.index', ['topic' => $input['project_name']]) }}">Собрать проект по теме →</a>
      <a class="btn btn-ghost" href="{{ route('build.index') }}">Перейти к сборке</a>
    </div>
  </form>

  @if ($brief)
    <section class="topic-result">
      <div class="section-head">
        <h2>ТЗ: {{ $input['project_name'] }}</h2>
        <p>Ниже — готовый текст задания, схема базы и что именно проверит эксперт.</p>
      </div>

      <article class="topic-card">
        <h3>1. Предметная область</h3>
        @foreach ($brief['domain'] as $paragraph)
          <p>{{ $paragraph }}</p>
        @endforeach
      </article>

      <article class="topic-card">
        <h3>2. Функционал: пять страниц</h3>
        <ol class="topic-pages">
          @foreach ($brief['pages'] as $page)
            <li>
              <b>{{ $page['title'] }}</b>
              <p>{{ $page['text'] }}</p>
              <ul>
                @foreach ($page['rules'] as $rule)
                  <li>{{ $rule }}</li>
                @endforeach
              </ul>
            </li>
          @endforeach
        </ol>
      </article>

      <article class="topic-card">
        <h3>3. Модель данных</h3>
        <div class="topic-tables">
          @foreach ($brief['tables'] as $table)
            <div class="topic-table">
              <h4><code>{{ $table['name'] }}</code> — {{ $table['purpose'] }}</h4>
              <table>
                <thead>
                  <tr><th>Поле</th><th>Тип</th><th>Назначение</th></tr>
                </thead>
                <tbody>
                  @foreach ($table['fields'] as $field)
                    <tr>
                      <td><code>{{ $field[0] }}</code></td>
                      <td>{{ $field[1] }}</td>
                      <td>{{ $field[2] }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endforeach
        </div>
      </article>

      <article class="topic-card">
        <h3>4. Критерии оценивания</h3>
        <p class="topic-hint">Инвариантная часть — 75 баллов. Эти формулировки одинаковы для любой темы.</p>
        @foreach ($brief['criteria'] as $group)
          <div class="topic-criteria">
            <h4>{{ $group['group'] }}</h4>
            <ul>
              @foreach ($group['items'] as $item)
                <li><span>{{ $item[0] }}</span><b>{{ $item[1] }}</b></li>
              @endforeach
            </ul>
          </div>
        @endforeach
      </article>

      <article class="topic-card">
        <h3>5. Что проверит эксперт</h3>
        <div class="topic-checks">
          @foreach ($brief['checks'] as $check)
            <div>
              <h4>{{ $check['title'] }}</h4>
              <ul>
                @foreach ($check['items'] as $item)
                  <li>{{ $item }}</li>
                @endforeach
              </ul>
            </div>
          @endforeach
        </div>
      </article>

      <article class="topic-card topic-card--split">
        <div>
          <h3>Нельзя менять</h3>
          <ul class="topic-list">
            @foreach ($brief['invariant'] as $item)
              <li>{{ $item }}</li>
            @endforeach
          </ul>
        </div>
        <div>
          <h3>Можно менять под свою тему</h3>
          <ul class="topic-list">
            @foreach ($brief['variable'] as $item)
              <li>{{ $item }}</li>
            @endforeach
          </ul>
        </div>
      </article>
    </section>
  @endif
@endsection
