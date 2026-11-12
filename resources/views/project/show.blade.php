@extends('labs.layout')

@section('title', 'Мой проект')

@section('content')
  @php
    $completed = $project->exists ? $project->completedStages() : 0;
    $totalStages = count($stages);
    $selected = old('stages', array_keys(array_filter($project->stages ?? [])));
  @endphp

  <section class="lab-hero">
    <div>
      <span class="kicker">Подготовка · проект целиком</span>
      <h1>Мой проект</h1>
      <p class="lead">
        Здесь вы собираете не отдельную лабораторную, а свой экзаменационный проект: ссылку на репозиторий,
        коммит, деплой и стадии готовности. Это чек-лист, к которому возвращаются перед сдачей.
      </p>
    </div>
    <div class="lab-hero-progress">
      <span>Готовность проекта</span>
      <b>{{ $completed }} / {{ $totalStages }}</b>
      <div class="bar"><i style="width: {{ $totalStages ? round($completed / $totalStages * 100) : 0 }}%"></i></div>
      <small>{{ $totalStages ? round($completed / $totalStages * 100) : 0 }}% стадий</small>
    </div>
  </section>

  @if (session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
  @endif

  @if ($errors->any())
    <div class="alert alert--error">{{ $errors->first() }}</div>
  @endif

  @if ($variant)
    <div class="variant-current">
      <div>
        <span class="kicker">Ваш вариант</span>
        <b>{{ $variant->title }}</b>
        <small>{{ $variant->theme }} · {{ $variant->minutes }} мин</small>
      </div>
      <a class="btn btn-ghost" href="{{ route('variants.show', $variant) }}">Открыть ТЗ →</a>
    </div>
  @else
    <div class="variant-current is-empty">
      <div>
        <span class="kicker">Вариант не выбран</span>
        <b>Возьмите готовое ТЗ проекта</b>
        <small>6 тем: конференции, медцентр, автосервис, аренда, фитнес, курсы</small>
      </div>
      <a class="btn btn-accent" href="{{ route('variants.index') }}">Выбрать вариант →</a>
    </div>
  @endif

  <div class="lab-split">
    <section class="lab-brief">
      <h2>Что это даёт</h2>
      <p>
        На защите спрашивают не «сколько уроков вы прошли», а «покажите проект». Здесь фиксируются ссылки
        и стадии, чтобы за неделю до экзамена было видно, что уже готово, а что нет.
      </p>

      <h3>Стадии</h3>
      <ul class="project-stages">
        @foreach ($stages as $key => $label)
          <li class="{{ in_array($key, $selected, true) ? 'is-done' : '' }}">
            <span class="project-stage-mark">{{ in_array($key, $selected, true) ? '✓' : '—' }}</span>
            {{ $label }}
          </li>
        @endforeach
      </ul>

      <h3>Перед сдачей</h3>
      <ul class="lab-steps">
        <li>Проверьте запуск с нуля: <code>php artisan migrate:fresh --seed</code>.</li>
        <li>Пройдите сценарий: регистрация → заявка → админка → статус → отзыв.</li>
        <li>Откройте 390, 768 и 1440 px: нет горизонтальной прокрутки.</li>
        <li>Убедитесь, что в репозитории есть коммиты и README.</li>
      </ul>
    </section>

    <section class="lab-work">
      <form method="POST" action="{{ route('project.save') }}" class="lab-form project-form">
        @csrf

        <div class="project-form-body">
          <div class="field">
            <label for="repo_url">Ссылка на репозиторий</label>
            <input type="url" id="repo_url" name="repo_url" value="{{ old('repo_url', $project->repo_url) }}"
                   placeholder="https://github.com/you/conference">
            @error('repo_url') <span class="field-error">{{ $message }}</span> @enderror
          </div>

          <div class="field">
            <label for="deploy_url">Ссылка на работающий проект</label>
            <input type="url" id="deploy_url" name="deploy_url" value="{{ old('deploy_url', $project->deploy_url) }}"
                   placeholder="http://127.0.0.1:8000 или адрес на сервере">
            @error('deploy_url') <span class="field-error">{{ $message }}</span> @enderror
          </div>

          <div class="field">
            <label for="commit_hash">Последний коммит</label>
            <input type="text" id="commit_hash" name="commit_hash" value="{{ old('commit_hash', $project->commit_hash) }}"
                   placeholder="a1b2c3d или сообщение коммита">
            @error('commit_hash') <span class="field-error">{{ $message }}</span> @enderror
          </div>

          <div class="field">
            <label>Стадии готовности</label>
            <div class="project-checks">
              @foreach ($stages as $key => $label)
                <label class="check">
                  <input type="checkbox" name="stages[]" value="{{ $key }}" @checked(in_array($key, $selected, true))>
                  <span class="check-box" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M4 12.5l5 5L20 6.5"/></svg>
                  </span>
                  <span class="check-text">{{ $label }}</span>
                </label>
              @endforeach
            </div>
            @error('stages') <span class="field-error">{{ $message }}</span> @enderror
          </div>

          <div class="field">
            <label for="notes">Заметки: что осталось сделать</label>
            <textarea id="notes" name="notes" rows="5"
                      placeholder="Например: добавить фильтр по статусу, проверить 403 на чужую заявку">{{ old('notes', $project->notes) }}</textarea>
            @error('notes') <span class="field-error">{{ $message }}</span> @enderror
          </div>
        </div>

        <div class="lab-actions">
          <button type="submit" class="btn btn-accent">Сохранить проект</button>
          <span class="lab-shortcut">Данные видны только вам и администратору</span>
        </div>
      </form>
    </section>
  </div>
@endsection
