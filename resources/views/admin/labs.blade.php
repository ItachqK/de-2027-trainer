@extends('admin.layout')

@section('title', 'Лабораторные')
@section('heading', 'Лабораторные')

@section('content')
  <div class="admin-toolbar">
    <p>Всего {{ $labs->count() }} лабораторных. Проверки и решения редактируются как JSON.</p>
    <a href="{{ route('labs.index') }}" class="btn btn-sm btn-ghost">Открыть практикум</a>
  </div>

  <div class="table-wrap">
    <table class="data admin-table">
      <thead>
        <tr>
          <th>Модуль</th>
          <th>Лабораторная</th>
          <th>Язык</th>
          <th>Проверки</th>
          <th>Статус</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse ($labs as $lab)
          <tr>
            <td>{{ $lab->module?->num ?? '—' }} · {{ $lab->module?->title ?? 'Без модуля' }}</td>
            <td>{{ $lab->title }}</td>
            <td><code>{{ $lab->language }}</code></td>
            <td>
              {{ count($lab->checks['checks'] ?? []) }}
              @if (! empty($lab->checks['sql'])) + SQL @endif
              @if (! empty($lab->checks['php_lint'])) + lint @endif
            </td>
            <td>
              <span class="tag-mini {{ $lab->is_published ? 'is-done' : '' }}">
                {{ $lab->is_published ? 'опубликована' : 'черновик' }}
              </span>
            </td>
            <td><a href="{{ route('admin.labs.edit', $lab) }}">Изменить</a></td>
          </tr>
        @empty
          <tr><td colspan="6">Лабораторных нет. Запустите LabSeeder.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endsection
