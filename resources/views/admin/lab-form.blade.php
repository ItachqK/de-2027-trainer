@extends('admin.layout')

@section('title', 'Редактирование лабораторной')
@section('heading', 'Редактирование лабораторной')

@section('content')
  @php
    $checksJson = old('checks', json_encode($lab->checks ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
  @endphp

  <form method="POST" action="{{ route('admin.labs.update', $lab) }}" class="admin-form">
    @csrf
    @method('PUT')

    <div class="admin-form-grid">
      <div class="field">
        <label for="module_id">Модуль</label>
        <select id="module_id" name="module_id">
          <option value="">Без модуля</option>
          @foreach (\App\Models\Module::orderBy('position')->get() as $module)
            <option value="{{ $module->id }}" @selected((int) old('module_id', $lab->module_id) === $module->id)>
              {{ $module->num }} · {{ $module->title }}
            </option>
          @endforeach
        </select>
        @error('module_id') <span class="field-error">{{ $message }}</span> @enderror
      </div>

      <div class="field">
        <label for="lesson_slug">Slug урока</label>
        <input type="text" id="lesson_slug" name="lesson_slug" value="{{ old('lesson_slug', $lab->lesson_slug) }}">
        @error('lesson_slug') <span class="field-error">{{ $message }}</span> @enderror
      </div>

      <div class="field">
        <label for="title">Название</label>
        <input type="text" id="title" name="title" value="{{ old('title', $lab->title) }}" required>
        @error('title') <span class="field-error">{{ $message }}</span> @enderror
      </div>

      <div class="field">
        <label for="minutes">Минут</label>
        <input type="number" id="minutes" name="minutes" min="1" max="180" value="{{ old('minutes', $lab->minutes) }}" required>
        @error('minutes') <span class="field-error">{{ $message }}</span> @enderror
      </div>

      <div class="field">
        <label for="level">Уровень</label>
        <select id="level" name="level">
          @foreach (['база', 'средний', 'практика'] as $level)
            <option value="{{ $level }}" @selected(old('level', $lab->level) === $level)>{{ $level }}</option>
          @endforeach
        </select>
        @error('level') <span class="field-error">{{ $message }}</span> @enderror
      </div>

      <div class="field">
        <label for="language">Язык</label>
        <select id="language" name="language">
          @foreach (['html', 'css', 'php', 'sql', 'blade', 'javascript', 'text'] as $language)
            <option value="{{ $language }}" @selected(old('language', $lab->language) === $language)>{{ $language }}</option>
          @endforeach
        </select>
        @error('language') <span class="field-error">{{ $message }}</span> @enderror
      </div>
    </div>

    <div class="field">
      <label for="goal">Цель</label>
      <textarea id="goal" name="goal" rows="2" required>{{ old('goal', $lab->goal) }}</textarea>
      @error('goal') <span class="field-error">{{ $message }}</span> @enderror
    </div>

    <div class="field">
      <label for="brief">Задача</label>
      <textarea id="brief" name="brief" rows="4" required>{{ old('brief', $lab->brief) }}</textarea>
      @error('brief') <span class="field-error">{{ $message }}</span> @enderror
    </div>

    <div class="field">
      <label for="steps">Шаги — по одному на строку</label>
      <textarea id="steps" name="steps" rows="5">{{ old('steps', implode("\n", $lab->steps ?? [])) }}</textarea>
      @error('steps') <span class="field-error">{{ $message }}</span> @enderror
    </div>

    <div class="field">
      <label for="starter_code">Заготовка кода</label>
      <textarea id="starter_code" name="starter_code" rows="8" class="code-input" spellcheck="false">{{ old('starter_code', $lab->starter_code) }}</textarea>
      @error('starter_code') <span class="field-error">{{ $message }}</span> @enderror
    </div>

    <div class="field">
      <label for="hints">Подсказки — по одной на строку</label>
      <textarea id="hints" name="hints" rows="4">{{ old('hints', implode("\n", $lab->hints ?? [])) }}</textarea>
      @error('hints') <span class="field-error">{{ $message }}</span> @enderror
    </div>

    <div class="field">
      <label for="solution">Решение</label>
      <textarea id="solution" name="solution" rows="10" class="code-input" spellcheck="false">{{ old('solution', $lab->solution) }}</textarea>
      @error('solution') <span class="field-error">{{ $message }}</span> @enderror
    </div>

    <div class="field">
      <label for="checks">Правила проверки — JSON</label>
      <textarea id="checks" name="checks" rows="14" class="code-input" spellcheck="false" required>{{ $checksJson }}</textarea>
      @error('checks') <span class="field-error">{{ $message }}</span> @enderror
      <span class="field-hint">Формат: {"php_lint": true, "checks": [{"type": "contains", "label": "...", "value": "..."}], "sql": {...}}</span>
    </div>

    <label class="check admin-check">
      <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $lab->is_published))>
      <span class="check-box" aria-hidden="true">
        <svg viewBox="0 0 24 24"><path d="M4 12.5l5 5L20 6.5"/></svg>
      </span>
      <span class="check-text">Опубликована в практикуме</span>
    </label>

    <div class="admin-form-actions">
      <button type="submit" class="btn btn-accent">Сохранить</button>
      <a href="{{ route('admin.labs') }}" class="btn btn-ghost">Отмена</a>
      @if ($lab->is_published)
        <a href="{{ route('labs.show', $lab) }}" class="btn btn-ghost" target="_blank" rel="noopener">Открыть лабораторную</a>
      @endif
    </div>
  </form>
@endsection
