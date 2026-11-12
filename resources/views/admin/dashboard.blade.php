@extends('admin.layout')

@section('title', 'Обзор')
@section('heading', 'Обзор')

@section('content')
  <div class="admin-stats">
    <div class="admin-stat">
      <span>Студенты</span>
      <b>{{ $studentsCount }}</b>
    </div>
    <div class="admin-stat">
      <span>Опубликовано уроков</span>
      <b>{{ $lessonsCount }}</b>
    </div>
    <div class="admin-stat">
      <span>Модули</span>
      <b>{{ $modulesCount }}</b>
    </div>
    <div class="admin-stat">
      <span>Средний прогресс</span>
      <b>{{ $average }}%</b>
    </div>
  </div>

  <div class="admin-grid">
    <section class="admin-panel">
      <div class="admin-panel-head">
        <h2>Последние студенты</h2>
        <a href="{{ route('admin.students') }}">Все →</a>
      </div>

      @forelse ($recentStudents as $student)
        @php
          $done = count(array_filter($student->state?->data['done'] ?? []));
          $percent = $totalLessons ? round($done / $totalLessons * 100) : 0;
        @endphp
        <a class="admin-student-row" href="{{ route('admin.student', $student) }}">
          <span>
            <b>{{ $student->fio }}</b>
            <small>{{ $student->login }}</small>
          </span>
          <span class="admin-progress">
            <span class="bar"><i style="width: {{ $percent }}%"></i></span>
            <small>{{ $done }} / {{ $totalLessons }}</small>
          </span>
        </a>
      @empty
        <p class="admin-empty">Пока нет зарегистрированных студентов.</p>
      @endforelse
    </section>

    <section class="admin-panel">
      <div class="admin-panel-head">
        <h2>Что дальше</h2>
      </div>
      <ul class="admin-notes">
        <li><b>{{ $activeStudents }}</b> студентов заходили за последние 7 дней.</li>
        <li>Уроки редактируются в разделе «Уроки»: текст, практика и вопросы хранятся в JSON.</li>
        <li>Лабораторные и их правила проверки — в разделе «Лабораторные».</li>
        <li>Прогресс приходит из состояния пользователя и обновляется после каждого действия.</li>
        <li>Пароль администратора задаётся сидером и хранится в базе хешем.</li>
      </ul>
    </section>
  </div>

  <section class="admin-panel admin-practice">
    <div class="admin-panel-head">
      <h2>Практикум</h2>
      <a href="{{ route('labs.index') }}">Открыть →</a>
    </div>
    <div class="admin-practice-grid">
      <div><b>{{ $labsCount }}</b><span>лабораторных</span></div>
      <div><b>{{ $labPassed }}</b><span>успешных сдач</span></div>
      <div><b>{{ $labStudents }}</b><span>студентов практиковались</span></div>
    </div>
  </section>

  <section class="admin-panel admin-structure">
    <div class="admin-panel-head">
      <h2>Структура курса</h2>
      <a href="{{ route('admin.lessons') }}">Все уроки →</a>
    </div>
    <div class="table-wrap">
      <table class="data admin-table">
        <thead>
          <tr><th>Модуль</th><th>Уроков</th><th>Опубликовано</th><th>Ведущий</th></tr>
        </thead>
        <tbody>
          @foreach ($modules as $module)
            <tr>
              <td>{{ $module->num }} · {{ $module->title }}</td>
              <td>{{ $module->lessons_count }}</td>
              <td>{{ $module->published_lessons_count }}</td>
              <td class="muted">{{ $module->lead }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </section>
@endsection
