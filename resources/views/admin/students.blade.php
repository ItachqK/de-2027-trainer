@extends('admin.layout')

@section('title', 'Студенты')
@section('heading', 'Студенты')

@section('content')
  <div class="table-wrap">
    <table class="data admin-table">
      <thead>
        <tr>
          <th>Студент</th>
          <th>Логин</th>
          <th>Был в сети</th>
          <th>Прогресс</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse ($students as $student)
          @php
            $done = count(array_filter($student->state?->data['done'] ?? []));
            $percent = $totalLessons ? round($done / $totalLessons * 100) : 0;
          @endphp
          <tr>
            <td>{{ $student->fio }}</td>
            <td><code>{{ $student->login }}</code></td>
            <td>{{ $student->last_seen_at?->format('d.m.Y H:i') ?? 'ещё не заходил' }}</td>
            <td>
              <div class="admin-progress">
                <span class="bar"><i style="width: {{ $percent }}%"></i></span>
                <small>{{ $done }} / {{ $totalLessons }} · {{ $percent }}%</small>
              </div>
            </td>
            <td><a href="{{ route('admin.student', $student) }}">Открыть</a></td>
          </tr>
        @empty
          <tr><td colspan="5">Студентов пока нет.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endsection
