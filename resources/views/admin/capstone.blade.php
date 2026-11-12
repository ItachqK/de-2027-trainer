@extends('admin.layout')

@section('title', 'Капстоун')
@section('heading', 'Проверки проектов')

@section('content')
  <div class="admin-toolbar">
    <p>Всего {{ $submissions->count() }} проверок. Баллы ориентировочные: статический аудит файлов, без запуска проекта.</p>
  </div>

  <div class="table-wrap">
    <table class="data admin-table">
      <thead>
        <tr>
          <th>Студент</th>
          <th>Проект</th>
          <th>Баллы</th>
          <th>Запуск</th>
          <th>Дата</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse ($submissions as $submission)
          <tr>
            <td>{{ $submission->user?->fio ?? '—' }}</td>
            <td>
              {{ $submission->project_path ?: $submission->repo_url ?: '—' }}
              @if ($submission->deploy_url)
                <br><small class="muted">{{ $submission->deploy_url }}</small>
              @endif
            </td>
            <td><b>{{ $submission->score }}</b> / {{ $submission->max_score }}</td>
            <td>
              @if ($submission->runtime)
                <span class="tag-mini {{ $submission->runtime_passed ? 'is-done' : '' }}">
                  {{ $submission->runtime_passed ? 'пройден' : 'ошибки' }}
                </span>
              @else
                <span class="muted">—</span>
              @endif
            </td>
            <td>{{ $submission->created_at->format('d.m.Y H:i') }}</td>
            <td><a href="{{ route('admin.capstone.show', $submission) }}">Открыть</a></td>
          </tr>
        @empty
          <tr><td colspan="5">Проверок пока нет.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endsection
