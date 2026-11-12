@extends('admin.layout')

@section('title', 'Уроки')
@section('heading', 'Уроки')

@section('content')
  <div class="admin-toolbar">
    <p>Всего {{ $lessons->count() }} уроков. Правки сразу попадают в курс после сохранения.</p>
    <a href="{{ route('admin.lessons.create') }}" class="btn btn-sm btn-accent">Добавить урок</a>
  </div>

  <div class="table-wrap">
    <table class="data admin-table">
      <thead>
        <tr>
          <th>Модуль</th>
          <th>Урок</th>
          <th>Slug</th>
          <th>Статус</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse ($lessons as $lesson)
          <tr>
            <td>{{ $lesson->module->num }} · {{ $lesson->module->title }}</td>
            <td>{{ $lesson->title }}</td>
            <td><code>{{ $lesson->slug }}</code></td>
            <td>
              <span class="tag-mini {{ $lesson->is_published ? 'is-done' : '' }}">
                {{ $lesson->is_published ? 'опубликован' : 'черновик' }}
              </span>
            </td>
            <td class="admin-actions">
              <a href="{{ route('admin.lessons.edit', $lesson) }}">Изменить</a>
              <form method="POST" action="{{ route('admin.lessons.destroy', $lesson) }}"
                    onsubmit="return confirm('Удалить урок «{{ $lesson->title }}»?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="link-btn link-btn--danger">Удалить</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="5">Уроков нет.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endsection
