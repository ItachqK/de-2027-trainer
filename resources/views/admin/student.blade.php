@extends('admin.layout')

@section('title', $student->fio)
@section('heading', $student->fio)

@section('content')
  <div class="admin-stats">
    <div class="admin-stat">
      <span>Пройдено уроков</span>
      <b>{{ count($done) }} / {{ $totalLessons }}</b>
    </div>
    <div class="admin-stat">
      <span>Отвечено на вопросы</span>
      <b>{{ $quizCount }}</b>
    </div>
    <div class="admin-stat">
      <span>Заметок</span>
      <b>{{ $notesCount }}</b>
    </div>
    <div class="admin-stat">
      <span>Последний вход</span>
      <b>{{ $student->last_seen_at?->format('d.m') ?? '—' }}</b>
    </div>
  </div>

  <section class="admin-panel">
    <div class="admin-panel-head">
      <h2>Пройденные уроки</h2>
      <a href="{{ route('admin.students') }}">← Все студенты</a>
    </div>

    @if (count($done))
      <ul class="admin-list">
        @foreach ($done as $slug)
          <li>
            <span>{{ $lessons[$slug]->title ?? $slug }}</span>
            <code>{{ $slug }}</code>
          </li>
        @endforeach
      </ul>
    @else
      <p class="admin-empty">Студент ещё не отметил ни одного урока.</p>
    @endif
  </section>

  <section class="admin-panel">
    <div class="admin-panel-head">
      <h2>Проект</h2>
    </div>

    @if ($project)
      <ul class="admin-list">
        <li><span>Готовность</span><b>{{ $project->completedStages() }} / {{ count(\App\Models\Project::STAGES) }}</b></li>
        <li><span>Репозиторий</span><span>{{ $project->repo_url ?: 'не указан' }}</span></li>
        <li><span>Деплой</span><span>{{ $project->deploy_url ?: 'не указан' }}</span></li>
        <li><span>Коммит</span><code>{{ $project->commit_hash ?: '—' }}</code></li>
      </ul>
      @if ($project->notes)
        <p class="admin-empty">Заметки: {{ $project->notes }}</p>
      @endif
    @else
      <p class="admin-empty">Студент ещё не заполнил проект.</p>
    @endif
  </section>

  <section class="admin-panel">
    <div class="admin-panel-head">
      <h2>Практикум</h2>
      <a href="{{ route('labs.index') }}">Все лабораторные →</a>
    </div>

    @if ($labSubmissions->count())
      <ul class="admin-list">
        @foreach ($labSubmissions as $submission)
          <li>
            <span>
              {{ $submission->lab?->title ?? 'Лабораторная' }}
              <small class="admin-list-meta">попыток: {{ $submission->attempts }}</small>
            </span>
            <span class="tag-mini {{ $submission->status === 'passed' ? 'is-done' : '' }}">
              {{ $submission->status === 'passed' ? 'сдано' : ($submission->status === 'failed' ? 'есть ошибки' : 'черновик') }}
            </span>
          </li>
        @endforeach
      </ul>
    @else
      <p class="admin-empty">Студент ещё не открывал лабораторные.</p>
    @endif
  </section>

  <details class="admin-json">
    <summary>Состояние пользователя (JSON)</summary>
    <pre>{{ json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
  </details>
@endsection
