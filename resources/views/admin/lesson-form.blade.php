@extends('admin.layout')

@section('title', $lesson->exists ? 'Редактирование урока' : 'Новый урок')
@section('heading', $lesson->exists ? 'Редактирование урока' : 'Новый урок')

@section('content')
  @php
    $blocksJson = old('blocks', json_encode($lesson->blocks ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    $quizJson = old('quiz', json_encode($lesson->quiz ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    $published = old('is_published', $lesson->is_published ?? true);
  @endphp

  <form method="POST" action="{{ $action }}" class="admin-form">
    @csrf
    @if ($method !== 'POST')
      @method($method)
    @endif

    <div class="admin-form-grid">
      <div class="field">
        <label for="module_id">Модуль</label>
        <select id="module_id" name="module_id" required>
          @foreach ($modules as $module)
            <option value="{{ $module->id }}" @selected((int) old('module_id', $lesson->module_id) === $module->id)>
              {{ $module->num }} · {{ $module->title }}
            </option>
          @endforeach
        </select>
        @error('module_id') <span class="field-error">{{ $message }}</span> @enderror
      </div>

      <div class="field">
        <label for="slug">Slug</label>
        <input type="text" id="slug" name="slug" value="{{ old('slug', $lesson->slug) }}"
               placeholder="laravel-auth" required>
        @error('slug') <span class="field-error">{{ $message }}</span> @enderror
      </div>

      <div class="field">
        <label for="title">Название</label>
        <input type="text" id="title" name="title" value="{{ old('title', $lesson->title) }}" required>
        @error('title') <span class="field-error">{{ $message }}</span> @enderror
      </div>

      <div class="field">
        <label for="minutes">Минут</label>
        <input type="number" id="minutes" name="minutes" min="1" max="180"
               value="{{ old('minutes', $lesson->minutes ?? 15) }}" required>
        @error('minutes') <span class="field-error">{{ $message }}</span> @enderror
      </div>

      <div class="field">
        <label for="level">Уровень</label>
        <select id="level" name="level" required>
          @foreach (['база', 'средний', 'практика'] as $level)
            <option value="{{ $level }}" @selected(old('level', $lesson->level) === $level)>{{ $level }}</option>
          @endforeach
        </select>
        @error('level') <span class="field-error">{{ $message }}</span> @enderror
      </div>

      <div class="field">
        <label for="tags">Теги через запятую</label>
        <input type="text" id="tags" name="tags" value="{{ old('tags', implode(', ', $lesson->tags ?? [])) }}">
        @error('tags') <span class="field-error">{{ $message }}</span> @enderror
      </div>
    </div>

    <div class="field">
      <label for="goal">Цель урока</label>
      <textarea id="goal" name="goal" rows="3" required>{{ old('goal', $lesson->goal) }}</textarea>
      @error('goal') <span class="field-error">{{ $message }}</span> @enderror
    </div>

    <div class="field">
      <label for="practice">Практика — по одному пункту на строку</label>
      <textarea id="practice" name="practice" rows="5">{{ old('practice', implode("\n", $lesson->practice ?? [])) }}</textarea>
      @error('practice') <span class="field-error">{{ $message }}</span> @enderror
    </div>

    <div class="field">
      <label for="blocks">Блоки урока — JSON-массив</label>
      <textarea id="blocks" name="blocks" rows="18" class="code-input" spellcheck="false" required>{{ $blocksJson }}</textarea>
      @error('blocks') <span class="field-error">{{ $message }}</span> @enderror
      <span class="field-hint">Типы блоков: p, h, h4, quote, ul, ol, file, note, warn, ok, goal, code, table, steps.</span>
    </div>

    <div class="field">
      <label for="quiz">Вопросы — JSON-массив</label>
      <textarea id="quiz" name="quiz" rows="12" class="code-input" spellcheck="false" required>{{ $quizJson }}</textarea>
      @error('quiz') <span class="field-error">{{ $message }}</span> @enderror
      <span class="field-hint">Формат: [{"q": "...", "options": ["A", "B"], "answer": 0, "explain": "..."}]</span>
    </div>

    <label class="check admin-check">
      <input type="checkbox" name="is_published" value="1" @checked($published)>
      <span class="check-box" aria-hidden="true">
        <svg viewBox="0 0 24 24"><path d="M4 12.5l5 5L20 6.5"/></svg>
      </span>
      <span class="check-text">Опубликован в курсе</span>
    </label>

    <div class="admin-form-actions">
      <button type="submit" class="btn btn-accent">Сохранить</button>
      <a href="{{ route('admin.lessons') }}" class="btn btn-ghost">Отмена</a>
      @if ($lesson->exists && $lesson->is_published)
        <a href="{{ route('app') }}#/lesson/{{ $lesson->slug }}" class="btn btn-ghost" target="_blank" rel="noopener">Открыть в курсе</a>
      @endif
    </div>
  </form>

  @if ($lesson->exists)
    <form method="POST" action="{{ route('admin.lessons.destroy', $lesson) }}" class="admin-delete"
          onsubmit="return confirm('Удалить урок «{{ $lesson->title }}»?')">
      @csrf
      @method('DELETE')
      <button type="submit" class="link-btn link-btn--danger">Удалить урок</button>
    </form>
  @endif
@endsection
