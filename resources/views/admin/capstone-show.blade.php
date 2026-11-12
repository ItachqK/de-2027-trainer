@extends('admin.layout')

@section('title', 'Проверка проекта')
@section('heading', 'Проверка проекта')

@section('content')
  <div class="admin-stats">
    <div class="admin-stat">
      <span>Студент</span>
      <b>{{ $submission->user?->fio ?? '—' }}</b>
    </div>
    <div class="admin-stat">
      <span>Баллы</span>
      <b>{{ $submission->score }} / {{ $submission->max_score }}</b>
    </div>
    <div class="admin-stat">
      <span>Процент</span>
      <b>{{ $submission->percent() }}%</b>
    </div>
    <div class="admin-stat">
      <span>Дата</span>
      <b>{{ $submission->created_at->format('d.m') }}</b>
    </div>
  </div>

  <section class="admin-panel">
    <div class="admin-panel-head">
      <h2>Источник</h2>
      <a href="{{ route('admin.capstone') }}">← Все проверки</a>
    </div>
    <ul class="admin-list">
      <li><span>Папка</span><code>{{ $submission->project_path ?: '—' }}</code></li>
      <li><span>Репозиторий</span><span>{{ $submission->repo_url ?: '—' }}</span></li>
      <li><span>Деплой</span><span>{{ $submission->deploy_url ?: '—' }}</span></li>
      <li><span>Коммит</span><code>{{ $submission->commit_hash ?: '—' }}</code></li>
    </ul>
    @if ($submission->notes)
      <p class="admin-empty">Заметки студента: {{ $submission->notes }}</p>
    @endif
  </section>

  <section class="admin-panel">
    <div class="admin-panel-head">
      <h2>Разбор по критериям</h2>
    </div>

    <div class="capstone-groups">
      @foreach ($submission->results['groups'] as $group => $data)
        <div class="capstone-group">
          <div class="capstone-group-head">
            <h3>{{ $group }}</h3>
            <span>{{ $data['earned'] }} / {{ $data['max'] }}</span>
          </div>
          @foreach ($submission->results['checks'] as $check)
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

  @if ($submission->runtime)
    @php
      $runtime = $submission->runtime;
      $clean = function ($text) {
          return preg_replace('/\x1b\[[0-9;]*m/', '', (string) $text);
      };
    @endphp

    <section class="admin-panel">
      <div class="admin-panel-head">
        <h2>Запуск проекта</h2>
        <span class="tag-mini {{ $runtime['passed'] ? 'is-done' : '' }}">
          {{ $runtime['passed'] ? 'сценарий пройден' : 'есть ошибки' }}
        </span>
      </div>

      <div class="capstone-groups">
        @foreach ($runtime['steps'] as $step)
          <div class="lab-result {{ $step['skipped'] ? '' : ($step['passed'] ? 'is-pass' : 'is-fail') }}">
            <span class="lab-result-mark">{{ $step['skipped'] ? '—' : ($step['passed'] ? '✓' : '×') }}</span>
            <span><b>{{ $step['label'] }}</b><small>{{ $step['message'] }}</small></span>
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
@endsection
