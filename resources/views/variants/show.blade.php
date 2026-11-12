@extends('labs.layout')

@section('title', $variant->title)

@section('content')
  @php
    $inline = fn (string $text) => preg_replace('/`([^`]+)`/', '<code>$1</code>', e($text));
  @endphp

  <nav class="lab-crumbs" aria-label="Хлебные крошки">
    <a href="{{ route('variants.index') }}">Варианты ТЗ</a>
    <span>/</span>
    <span>{{ $variant->theme }}</span>
  </nav>

  <header class="lab-head">
    <div>
      <span class="kicker">Полное ТЗ · {{ $variant->theme }}</span>
      <h1>{{ $variant->title }}</h1>
      <p class="lead">{{ $variant->summary }}</p>
    </div>
    <div class="lab-head-meta">
      <span>{{ $variant->minutes }} мин</span>
      <span>{{ $variant->level }}</span>
      @if ($selected)
        <span class="variant-selected">выбран</span>
      @endif
    </div>
  </header>

  @if (session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
  @endif

  <div class="variant-actions">
    @if ($selected)
      <span class="variant-selected">Вариант выбран</span>
      <a class="btn btn-ghost" href="{{ route('project.show') }}">Открыть «Мой проект»</a>
    @else
      <form method="POST" action="{{ route('variants.select', $variant) }}">
        @csrf
        <button type="submit" class="btn btn-accent">Выбрать этот вариант</button>
      </form>
    @endif

    <a class="btn btn-ghost" href="{{ route('variants.download', $variant) }}">Скачать ТЗ (.md)</a>
    <a class="btn btn-ghost" href="{{ route('capstone.index') }}">Проверить проект</a>
  </div>

  <div class="lab-split">
    <section class="lab-brief">
      <h2>Сущности и поля</h2>

      @foreach ($variant->entities as $entity)
        <h3>{{ $entity['title'] }} <code>{{ $entity['name'] }}</code></h3>
        <div class="variant-table-wrap">
          <table class="variant-table">
            <thead>
              <tr>
                <th>Поле</th>
                <th>Тип</th>
                <th>Примечание</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($entity['fields'] as $field)
                <tr>
                  <td><code>{{ $field['name'] }}</code></td>
                  <td>{{ $field['type'] }}</td>
                  <td>{{ $field['note'] ?? '' }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endforeach

      <h2>Статусы</h2>
      <div class="variant-table-wrap">
        <table class="variant-table">
          <thead>
            <tr>
              <th>Код</th>
              <th>Подпись</th>
              <th>Кто меняет</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($variant->statuses as $status)
              <tr>
                <td><code>{{ $status['key'] }}</code></td>
                <td>{{ $status['label'] }}</td>
                <td>{{ $status['who'] }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <h2>Маршруты</h2>
      <div class="variant-table-wrap">
        <table class="variant-table">
          <thead>
            <tr>
              <th>Метод</th>
              <th>URL</th>
              <th>Действие</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($variant->routes as $route)
              <tr>
                <td><code>{{ $route['method'] }}</code></td>
                <td><code>{{ $route['uri'] }}</code></td>
                <td>{{ $route['action'] }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <h2>Требования</h2>
      <ul class="drill-req">
        @foreach ($variant->requirements as $requirement)
          <li>{!! $inline($requirement) !!}</li>
        @endforeach
      </ul>
    </section>

    <aside class="lab-work">
      <div class="variant-panel">
        <span class="kicker">Администратор</span>
        <dl class="variant-credentials">
          <div>
            <dt>Логин</dt>
            <dd><code>{{ $variant->admin_login }}</code></dd>
          </div>
          <div>
            <dt>Пароль</dt>
            <dd><code>{{ $variant->admin_password }}</code></dd>
          </div>
        </dl>
        <p>Создайте администратора сидером, чтобы вход работал на чистой базе.</p>
      </div>

      <div class="variant-panel">
        <span class="kicker">Порядок работы</span>
        <ol class="lab-steps">
          <li>Создайте Laravel-проект и первый коммит.</li>
          <li>Спроектируйте таблицы и связи по списку выше.</li>
          <li>Сделайте регистрацию, вход и роли.</li>
          <li>Добавьте форму заявки и кабинет.</li>
          <li>Соберите админку со статусами и фильтром.</li>
          <li>Сделайте мобильный макет 390×844.</li>
          <li>Пройдите аудит и почините провалы.</li>
        </ol>
      </div>

      <div class="variant-panel">
        <span class="kicker">Чек-лист сдачи</span>
        <ul class="lab-steps">
          <li><code>php artisan migrate:fresh --seed</code> на чистой базе.</li>
          <li>Сценарий: регистрация → заявка → админка → статус.</li>
          <li>390 / 768 / 1440 px без прокрутки.</li>
          <li>Коммиты и README в репозитории.</li>
        </ul>
      </div>
    </aside>
  </div>
@endsection
