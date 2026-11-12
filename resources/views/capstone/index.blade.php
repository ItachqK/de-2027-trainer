@extends('labs.layout')

@section('title', 'Проверка проекта')

@section('content')
  <section class="lab-hero">
    <div>
      <span class="kicker">Капстоун · аудит проекта</span>
      <h1>Проверка проекта</h1>
      <p class="lead">
        Укажите папку своего Laravel-проекта — платформа прочитает файлы и покажет, что уже есть,
        а чего не хватает по критериям КИМ. Это предварительная оценка, не замена эксперту.
      </p>
    </div>
    @if ($latest)
      <div class="lab-hero-progress">
        <span>Последняя проверка</span>
        <b>{{ $latest->score }} / {{ $latest->max_score }}</b>
        <div class="bar"><i style="width: {{ $latest->percent() }}%"></i></div>
        <small>{{ $latest->percent() }}% · {{ $latest->created_at->format('d.m.Y H:i') }}</small>
      </div>
    @endif
  </section>

  @if (session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
  @endif

  @if ($errors->any())
    <div class="alert alert--error">{{ $errors->first() }}</div>
  @endif

  <div class="lab-split">
    <section class="lab-brief">
      <h2>Что проверяется</h2>
      <ul class="lab-steps">
        <li>Каркас Laravel, маршруты, контроллеры и формы.</li>
        <li>Безопасность: хеши, роли, CSRF, доступ к своим заявкам.</li>
        <li>Миграции, связи и ER-диаграмма.</li>
        <li>Мобильный макет 390×844, CSS, адаптив и анимации.</li>
        <li>Ошибки валидации, статусы, фильтры и отзыв.</li>
        <li>README, .env.example, тесты и Git.</li>
      </ul>

      <h3>Путь к проекту</h3>
      <p>
        Укажите папку, где лежит <code>artisan</code>. Например:
        <code>C:\Users\Dqavo\Desktop\conference</code>.
        Разрешены папки Desktop, Documents и <code>storage/app/projects</code>.
      </p>
      <p class="small muted">
        Можно оставить путь пустым и указать HTTPS-ссылку на репозиторий — платформа попробует
        склонировать его через git. Для приватных репозиториев это не сработает.
      </p>

      <details class="lab-hints">
        <summary>Что не проверяется</summary>
        <ul>
          <li>Код проекта не запускается: читаются только файлы.</li>
          <li>Внешний вид и поведение в браузере оценивает эксперт.</li>
          <li>Оценка ориентировочная: 75 баллов — шкала КИМ, но без защиты.</li>
        </ul>
      </details>
    </section>

    <section class="lab-work">
      <form method="POST" action="{{ route('capstone.audit') }}" class="lab-form">
        @csrf

        <div class="project-form-body">
          <div class="field">
            <label for="project_path">Путь к папке проекта</label>
            <input type="text" id="project_path" name="project_path"
                   value="{{ old('project_path', $latest?->project_path) }}"
                   placeholder="C:\Users\Dqavo\Desktop\conference">
            @error('project_path') <span class="field-error">{{ $message }}</span> @enderror
          </div>

          <div class="field">
            <label for="repo_url">Или ссылка на репозиторий</label>
            <input type="url" id="repo_url" name="repo_url"
                   value="{{ old('repo_url', $latest?->repo_url) }}"
                   placeholder="https://github.com/you/conference">
            @error('repo_url') <span class="field-error">{{ $message }}</span> @enderror
          </div>

          <div class="field">
            <label for="deploy_url">Ссылка на работающий проект</label>
            <input type="url" id="deploy_url" name="deploy_url"
                   value="{{ old('deploy_url', $latest?->deploy_url) }}"
                   placeholder="http://127.0.0.1:8000">
            @error('deploy_url') <span class="field-error">{{ $message }}</span> @enderror
          </div>

          <div class="field">
            <label for="commit_hash">Последний коммит</label>
            <input type="text" id="commit_hash" name="commit_hash"
                   value="{{ old('commit_hash', $latest?->commit_hash) }}"
                   placeholder="a1b2c3d">
            @error('commit_hash') <span class="field-error">{{ $message }}</span> @enderror
          </div>

          <div class="field">
            <label for="notes">Что проверить вручную</label>
            <textarea id="notes" name="notes" rows="3"
                      placeholder="Например: проверить отзыв после завершения">{{ old('notes', $latest?->notes) }}</textarea>
            @error('notes') <span class="field-error">{{ $message }}</span> @enderror
          </div>

          <label class="check admin-check">
            <input type="checkbox" name="run_project" value="1" @checked(old('run_project'))>
            <span class="check-box" aria-hidden="true">
              <svg viewBox="0 0 24 24"><path d="M4 12.5l5 5L20 6.5"/></svg>
            </span>
            <span class="check-text">Запустить проект: миграции, тесты и сценарий</span>
          </label>
          <p class="field-hint">
            Платформа выполнит <code>migrate:fresh --seed</code> и <code>php artisan test</code> на временной SQLite,
            поднимет сервер и пройдёт регистрацию, заявку и админку. Может занять 1–3 минуты.
          </p>
        </div>

        <div class="lab-actions">
          <button type="submit" class="btn btn-accent">Проверить проект</button>
          <span class="lab-shortcut">Без галочки — только чтение файлов</span>
        </div>
      </form>
    </section>
  </div>

  @if ($latest)
    <section class="capstone-result">
      <div class="capstone-result-head">
        <div>
          <span class="kicker">Результат проверки</span>
          <h2>{{ $latest->score }} из {{ $latest->max_score }}</h2>
          <p class="small muted">
            {{ $latest->project_path ?: $latest->repo_url }}
            · {{ $latest->created_at->format('d.m.Y H:i') }}
          </p>
        </div>
        <span class="stamp">{{ $latest->percent() >= 60 ? 'норма' : 'доработать' }}</span>
      </div>

      <div class="capstone-groups">
        @foreach ($latest->results['groups'] as $group => $data)
          <div class="capstone-group">
            <div class="capstone-group-head">
              <h3>{{ $group }}</h3>
              <span>{{ $data['earned'] }} / {{ $data['max'] }}</span>
            </div>
            @foreach ($latest->results['checks'] as $check)
              @if ($check['group'] === $group)
                <div class="lab-result {{ $check['passed'] ? 'is-pass' : 'is-fail' }}">
                  <span class="lab-result-mark">{{ $check['passed'] ? '✓' : '×' }}</span>
                  <span>
                    <b>{{ $check['label'] }} <small class="capstone-points">+{{ $check['earned'] }}/{{ $check['points'] }}</small></b>
                    <small>{{ $check['message'] }}</small>
                  </span>
                </div>
              @endif
            @endforeach
          </div>
        @endforeach
      </div>
    </section>

    @if ($latest?->runtime)
    @php
      $runtime = $latest->runtime;
      $clean = function ($text) {
          return preg_replace('/\x1b\[[0-9;]*m/', '', (string) $text);
      };
    @endphp

    <section class="capstone-result capstone-runtime">
      <div class="capstone-result-head">
        <div>
          <span class="kicker">Запуск проекта</span>
          <h2>{{ $runtime['passed'] ? 'Сценарий пройден' : 'Есть ошибки запуска' }}</h2>
          <p class="small muted">Длительность: {{ $runtime['duration'] }} с</p>
        </div>
        <span class="stamp">{{ $runtime['passed'] ? 'ок' : 'ошибки' }}</span>
      </div>

      <div class="capstone-groups">
        @foreach ($runtime['steps'] as $step)
          <div class="lab-result {{ $step['skipped'] ? '' : ($step['passed'] ? 'is-pass' : 'is-fail') }}">
            <span class="lab-result-mark">{{ $step['skipped'] ? '—' : ($step['passed'] ? '✓' : '×') }}</span>
            <span>
              <b>{{ $step['label'] }}</b>
              <small>{{ $step['message'] }}</small>
            </span>
          </div>
        @endforeach
      </div>

      @if (! empty($runtime['outputs']))
        <details class="lab-solution">
          <summary>Логи запуска</summary>
          @foreach (['migrate' => 'Миграции и сидеры', 'tests' => 'Тесты'] as $key => $label)
            @if (! empty($runtime['outputs'][$key]))
              <h4>{{ $label }}</h4>
              <pre><code>{{ $clean($runtime['outputs'][$key]) }}</code></pre>
            @endif
          @endforeach
        </details>
      @endif
    </section>
  @endif

  @if ($submissions->count() > 1)
      <section class="capstone-history">
        <h2>История проверок</h2>
        <div class="table-wrap">
          <table class="data">
            <thead><tr><th>Дата</th><th>Проект</th><th>Баллы</th></tr></thead>
            <tbody>
              @foreach ($submissions as $submission)
                <tr>
                  <td>{{ $submission->created_at->format('d.m.Y H:i') }}</td>
                  <td>{{ $submission->project_path ?: $submission->repo_url }}</td>
                  <td>{{ $submission->score }} / {{ $submission->max_score }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </section>
    @endif
  @endif
@endsection
