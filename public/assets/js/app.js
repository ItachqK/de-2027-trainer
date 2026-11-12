/* ДЭ-2027 — клиент тренажёра. Без сборки и зависимостей, кроме highlight.js. */
(function () {
  'use strict';

  var LS_KEY = 'de2027.progress.v1';
  var EXAM_MS = 3.5 * 60 * 60 * 1000; // 3 ч 30 мин — ГИА ДЭ БУ по КИМ 09.02.07-3-2027
  var AUTH = window.__AUTH__ || { user: null, csrf: null };
  var modules = window.COURSE_MODULES || [];
  var recipes = window.RECIPES || [];
  var labs = [];

  var flat = [];
  var total = 0;

  function setCourse(course) {
    modules = course.modules || [];
    recipes = course.recipes || [];
    labs = course.labs || [];
    flat = [];
    modules.forEach(function (m) {
      (m.lessons || []).forEach(function (l) {
        flat.push({ lesson: l, module: m });
      });
    });
    total = flat.length;
  }

  setCourse({ modules: modules, recipes: recipes });

  /* ---------------------------------------------------------------- state */

  function baseState() {
    return {
      done: {}, checks: {}, quiz: {}, notes: {},
      last: '', timer: { endsAt: 0, remaining: EXAM_MS, running: false }
    };
  }

  function loadState() {
    var base = baseState();
    try {
      var raw = localStorage.getItem(LS_KEY);
      if (!raw) return base;
      var saved = JSON.parse(raw);
      return Object.assign(base, saved, {
        timer: Object.assign(base.timer, saved.timer || {})
      });
    } catch (e) { return base; }
  }

  var state = AUTH.user ? baseState() : loadState();

  if (AUTH.user && window.__STATE__ && typeof window.__STATE__ === 'object') {
    state = Object.assign(baseState(), window.__STATE__);
    state.timer = Object.assign(baseState().timer, window.__STATE__.timer || {});
  }

  var serverSaveTimer = null;

  function save() {
    if (AUTH.user) {
      clearTimeout(serverSaveTimer);
      serverSaveTimer = setTimeout(pushServerState, 500);
      return;
    }

    try { localStorage.setItem(LS_KEY, JSON.stringify(state)); } catch (e) { /* приватный режим */ }
  }

  function setSync(text, kind) {
    var el = document.getElementById('syncStatus');
    if (!el) return;
    el.textContent = text;
    el.className = 'sync ' + (kind || '');
  }

  function pushServerState() {
    if (!AUTH.user || !AUTH.csrf) return;
    setSync('сохраняем…', 'is-saving');

    fetch('/api/state', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': AUTH.csrf
      },
      body: JSON.stringify({ state: state })
    }).then(function (res) {
      if (!res.ok) throw new Error('HTTP ' + res.status);
      setSync('сохранено', 'is-saved');
    }).catch(function () {
      setSync('не сохранено', 'is-error');
    });
  }

  function doneCount() { return Object.keys(state.done).filter(function (id) { return state.done[id]; }).length; }

  function moduleDone(m) {
    return m.lessons.length > 0 && m.lessons.every(function (l) { return state.done[l.id]; });
  }

  /* --------------------------------------------------------------- helpers */

  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  function md(s) {
    return esc(s)
      .replace(/`([^`]+)`/g, '<code>$1</code>')
      .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
  }

  function norm(s) {
    return String(s || '').toLowerCase().replace(/ё/g, 'е').replace(/\s+/g, ' ').trim();
  }

  function plural(n, one, few, many) {
    var a = Math.abs(n) % 100, b = a % 10;
    if (a > 10 && a < 20) return many;
    if (b > 1 && b < 5) return few;
    if (b === 1) return one;
    return many;
  }

  function pad(n) { return (n < 10 ? '0' : '') + n; }

  function findLesson(id) {
    for (var i = 0; i < flat.length; i++) if (flat[i].lesson.id === id) return flat[i];
    return null;
  }

  function lessonIndex(id) {
    for (var i = 0; i < flat.length; i++) if (flat[i].lesson.id === id) return i;
    return -1;
  }

  function plainLesson(l) {
    var parts = [l.title, l.goal || '', (l.tags || []).join(' ')];
    (l.blocks || []).forEach(function (b) {
      if (typeof b.v === 'string') parts.push(b.v);
      else if (Array.isArray(b.v)) parts.push(b.v.map(function (x) { return typeof x === 'string' ? x : (x.b || '') + ' ' + (x.t || ''); }).join(' '));
      if (b.head) parts.push(b.head.join(' '));
      if (b.rows) parts.push(b.rows.map(function (r) { return r.join(' '); }).join(' '));
    });
    (l.practice || []).forEach(function (p) { parts.push(p); });
    return parts.join(' ');
  }

  var ICON = {
    check: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 12.5l5 5L20 6.5"/></svg>',
    arrow: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>',
    clock: '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/></svg>',
    copy: '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="8" y="8" width="11" height="12"/><path d="M16 8V4H4v12h4"/></svg>',
    level: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 20V10M12 20V4M19 20v-7"/></svg>',
    chevron: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 5l7 7-7 7"/></svg>'
  };

  /* --------------------------------------------------------------- render */

  function renderInlineBlocks(blocks) {
    if (!blocks) return '';
    return blocks.map(renderBlock).join('');
  }

  function renderBlock(b) {
    if (!b || !b.t) return '';
    switch (b.t) {
      case 'p': return '<p>' + md(b.v) + '</p>';
      case 'h': return '<h3>' + md(b.v) + '</h3>';
      case 'h4': return '<h4>' + md(b.v) + '</h4>';
      case 'quote': return '<blockquote>' + md(b.v) + '</blockquote>';
      case 'ul': return '<ul>' + b.v.map(function (x) { return '<li>' + md(x) + '</li>'; }).join('') + '</ul>';
      case 'ol': return '<ol>' + b.v.map(function (x) { return '<li>' + md(x) + '</li>'; }).join('') + '</ol>';
      case 'file': return '<div class="file">' + esc(b.v) + '</div>';
      case 'note': return callout('note', b.label || 'Важно', b.v);
      case 'warn': return callout('warn', b.label || 'Так не делай', b.v);
      case 'ok': return callout('ok', b.label || 'Готово', b.v);
      case 'goal': return callout('goal', b.label || 'Цель урока', b.v);
      case 'code': return codeBlock(b);
      case 'table': return tableBlock(b);
      case 'steps': return stepsBlock(b);
      default: return '';
    }
  }

  function callout(kind, label, v) {
    var inner = Array.isArray(v) ? '<ul>' + v.map(function (x) { return '<li>' + md(x) + '</li>'; }).join('') + '</ul>' : '<p>' + md(v) + '</p>';
    return '<div class="callout callout--' + kind + '"><span class="callout-label">' + esc(label) + '</span>' + inner + '</div>';
  }

  function codeBlock(b) {
    var lang = b.lang || 'php';
    var hlLang = lang === 'blade' ? 'xml' : (lang === 'env' ? 'ini' : lang);
    var file = b.file ? '<b>' + esc(b.file) + '</b>' : (b.caption ? esc(b.caption) : '');
    return '<div class="code">' +
      '<div class="code-head">' +
        '<span class="code-file">' + file + '</span>' +
        '<span style="display:flex;align-items:center;gap:10px">' +
          '<span class="code-lang">' + esc(lang) + '</span>' +
          '<button class="copy-btn" type="button" data-copy>' + ICON.copy + ' Копировать</button>' +
        '</span>' +
      '</div>' +
      '<pre><code class="language-' + esc(hlLang) + '">' + esc(b.v) + '</code></pre>' +
    '</div>';
  }

  function tableBlock(b) {
    var head = (b.head || []).map(function (h) { return '<th>' + md(h) + '</th>'; }).join('');
    var rows = (b.rows || []).map(function (r) {
      return '<tr>' + r.map(function (c) { return '<td>' + md(c) + '</td>'; }).join('') + '</tr>';
    }).join('');
    return '<div class="table-wrap"><table class="data"><thead><tr>' + head + '</tr></thead><tbody>' + rows + '</tbody></table></div>';
  }

  function stepsBlock(b) {
    var items = b.v.map(function (x) {
      if (typeof x === 'string') return '<li><b>' + md(x) + '</b></li>';
      var body = x.t ? '<p>' + md(x.t) + '</p>' : '';
      var code = x.code ? codeBlock(x.code) : '';
      return '<li><b>' + md(x.b || '') + '</b>' + body + code + '</li>';
    }).join('');
    return '<ol class="steps">' + items + '</ol>';
  }

  function highlightAll(root) {
    if (!window.hljs) return;
    root.querySelectorAll('pre code').forEach(function (el) {
      try { window.hljs.highlightElement(el); } catch (e) { /* неизвестный язык */ }
    });
  }

  function renderRail() {
    var nav = document.getElementById('railNav');
    var currentId = currentLessonId();
    var scrollTop = nav.scrollTop;
    nav.innerHTML = modules.map(function (m) {
      var done = m.lessons.filter(function (l) { return state.done[l.id]; }).length;
      var isOpen = m.lessons.some(function (l) { return l.id === currentId; }) || state.openModule === m.id;
      var lessons = m.lessons.map(function (l) {
        var isDone = !!state.done[l.id];
        return '<li><a class="rail-lesson' + (l.id === currentId ? ' is-active' : '') + (isDone ? ' is-done' : '') + '" href="#/lesson/' + esc(l.id) + '">' +
          '<span class="rail-lesson-mark" aria-hidden="true">' + (isDone ? ICON.check : '') + '</span>' +
          '<span>' + esc(l.title) + '</span></a></li>';
      }).join('');
      return '<div class="rail-module' + (isOpen ? ' is-open' : '') + '">' +
        '<button class="rail-module-btn" type="button" data-module="' + esc(m.id) + '" aria-expanded="' + (isOpen ? 'true' : 'false') + '">' +
          '<span class="rail-module-num">' + esc(m.num) + '</span>' +
          '<span class="rail-module-title">' + esc(m.title) + '</span>' +
          '<span class="rail-module-count">' + done + '/' + m.lessons.length + '</span>' +
          '<svg class="rail-module-chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 5l7 7-7 7"/></svg>' +
        '</button>' +
        '<ul class="rail-lessons">' + lessons + '</ul>' +
      '</div>';
    }).join('');

    var d = doneCount();
    document.getElementById('railProgressText').textContent = d + ' / ' + total;
    document.getElementById('railProgressBar').style.width = (total ? Math.round(d / total * 100) : 0) + '%';
    document.getElementById('topbarCount').textContent = d + ' / ' + total;
    nav.scrollTop = scrollTop;
  }

  function currentLessonId() {
    var parts = location.hash.replace(/^#\/?/, '').split('/');
    return parts[0] === 'lesson' ? parts[1] : '';
  }

  function setCrumbs(html) {
    document.getElementById('crumbs').innerHTML = html;
  }

  /* ---------------------------------------------------------------- router */

  function route() {
    var raw = location.hash.replace(/^#\/?/, '');
    var parts = raw.split('/');
    var page = parts[0] || 'home';

    if (page !== 'exam') clearInterval(timerTick);
    renderRail();
    closeRail();
    closeSearch();

    if (page === 'lesson' && parts[1]) {
      var found = findLesson(parts[1]);
      if (found) { renderLesson(found); return; }
    }
    if (page === 'recipes') { renderRecipes(parts[1]); return; }
    if (page === 'brief') { renderBrief(); return; }
    if (page === 'exam') { renderExam(); return; }
    renderHome();
  }

  function afterRender(selector) {
    highlightAll(document.getElementById('main'));
    window.scrollTo(0, 0);
    document.getElementById('main').focus({ preventScroll: true });
    if (selector) {
      var el = document.querySelector(selector);
      if (el) el.scrollIntoView({ block: 'start', behavior: 'smooth' });
    }
  }

  /* ------------------------------------------------------------------ home */

  function renderHome() {
    document.title = 'ДЭ-2027 — платформа подготовки к демоэкзамену';
    var d = doneCount();
    var pct = total ? Math.round(d / total * 100) : 0;
    var last = state.last && findLesson(state.last) ? state.last : (flat[0] && flat[0].lesson.id);
    var lastItem = findLesson(last);
    var allDone = total > 0 && d === total;
    var nextLesson = allDone
      ? flat[0]
      : (flat.find(function (x) { return !state.done[x.lesson.id]; }) || lastItem);

    var facts = [
      [String(total), plural(total, 'урок', 'урока', 'уроков') + ' с заданиями'],
      [String(modules.length), 'модулей: от HTML до админки'],
      ['3:30', 'столько идёт экзамен'],
      ['2', 'разобранных типовых проекта']
    ].map(function (f) {
      return '<div class="fact"><b>' + f[0] + '</b><span>' + f[1] + '</span></div>';
    }).join('');

    var routeRows = modules.map(function (m) {
      var md = m.lessons.filter(function (l) { return state.done[l.id]; }).length;
      var mp = m.lessons.length ? Math.round(md / m.lessons.length * 100) : 0;
      var first = m.lessons[0] ? m.lessons[0].id : '';
      var isDone = md === m.lessons.length && m.lessons.length > 0;
      return '<a class="route-item' + (isDone ? ' is-done' : '') + '" href="#/lesson/' + esc(first) + '">' +
        '<span class="route-num">' + esc(m.num) + '</span>' +
        '<span class="route-body">' +
          '<span class="route-title">' + esc(m.title) + (isDone ? ' <span class="tag-mini is-done">пройден</span>' : '') + '</span>' +
          '<span class="route-desc">' + esc(m.lead) + '</span>' +
        '</span>' +
        '<span class="route-progress"><span class="bar"><i style="width:' + mp + '%"></i></span><span>' + md + '/' + m.lessons.length + '</span></span>' +
        '<span class="route-go">' + (isDone ? 'повторить' : (md ? 'продолжить' : 'начать')) + ' →</span>' +
      '</a>';
    }).join('');

    var plan = [
      ['Неделя 1', 'HTML, CSS, Bootstrap', 'Свёрстать три экрана: список, форма, кабинет. Без фреймворка.'],
      ['Неделя 2', 'PHP и формы', 'Регистрация и вход на чистом PHP + SQLite. Понять сессии.'],
      ['Неделя 3', 'SQL и Laravel', 'Миграции, модели, связи. Собрать CRUD категорий.'],
      ['Неделя 4', 'Аутентификация', 'Регистрация, вход, роли, защита маршрутов.'],
      ['Неделя 5', 'Заявки и админка', 'Форма заявки, статусы, фильтры, отзывы.'],
      ['Неделя 6', 'Дизайн и мобильный макет', '390×844, шесть HTML-экранов, оптимизация картинок.'],
      ['Неделя 7', 'Адаптив и качество', 'Микроанимации, доступность, проверка по критериям.'],
      ['Неделя 8', 'Репетиция', 'Полный проект за 3 ч 30 мин по таймеру, разбор ошибок.']
    ].map(function (r) {
      return '<tr><td>' + r[0] + '</td><td>' + r[1] + '</td><td>' + r[2] + '</td></tr>';
    }).join('');

    document.getElementById('main').innerHTML =
      '<section class="hero">' +
        '<div>' +
          '<span class="kicker">Демоэкзамен · 09.02.07 · базовый уровень</span>' +
          '<h1>Собрать типовой проект за 210 минут</h1>' +
           '<p class="lead">Не знаете, с чего начать? Идите по короткому маршруту: разберите понятие, решите маленькую задачу, соберите полноценный проект и только потом включайте таймер экзамена.</p>' +
           '<div class="hero-actions">' +
             '<a class="btn btn-accent" href="#/lesson/' + esc(nextLesson ? nextLesson.lesson.id : '') + '">' +
               (allDone ? 'Повторить маршрут' : (d ? 'Продолжить обучение' : 'Начать обучение')) + ICON.arrow +
             '</a>' +
             '<a class="btn btn-ghost" href="/build">Собрать проект самому</a>' +
           '</div>' +
           '<div class="hero-note"><b>' + pct + '%</b> курса пройдено · ' + d + ' из ' + total + ' уроков · ' + (AUTH.user ? 'прогресс сохраняется на сервере' : 'прогресс хранится в браузере') + '</div>' +
        '</div>' +
        '<aside class="ticket" aria-label="Параметры экзамена">' +
          '<div class="ticket-head"><span>Экзаменационный лист</span><b>ГИА ДЭ БУ</b></div>' +
          '<div class="ticket-title">Типовой проект:<br>заявки + админка</div>' +
          '<p class="ticket-sub">Одна и та же схема под любой темой: конференции, тест-драйв, медцентр, аренда.</p>' +
          '<div class="ticket-rows">' +
            '<div class="ticket-row"><span class="n">01</span><span class="t">Проектирование и разработка</span><span class="v">1:30</span></div>' +
            '<div class="ticket-row"><span class="n">02</span><span class="t">Графический дизайн, 390×844</span><span class="v">1:00</span></div>' +
            '<div class="ticket-row"><span class="n">03</span><span class="t">Адаптив, интерактив, качество</span><span class="v">1:00</span></div>' +
          '</div>' +
          '<div class="ticket-foot">' +
            '<small>Стек: Laravel · MySQL/SQLite<br>Blade · Bootstrap · JavaScript</small>' +
            '<span class="stamp">' + (d ? 'в работе' : 'цель') + '</span>' +
          '</div>' +
        '</aside>' +
      '</section>' +
       '<section class="learning-start" aria-label="С чего начать">' +
         '<div class="learning-start-head"><span class="kicker">Первые 30 минут</span><h2>Куда нажимать</h2><p>Не открывайте всё сразу. Один шаг — одно действие.</p></div>' +
         '<div class="learning-start-steps">' +
           '<a href="#/lesson/' + esc(flat[0] ? flat[0].lesson.id : '') + '"><b>01</b><strong>Урок</strong><span>Понять базу</span></a>' +
           '<a href="/drills"><b>02</b><strong>Тренажёр</strong><span>Написать самому</span></a>' +
           '<a href="/build"><b>03</b><strong>Сборка</strong><span>Собрать проект</span></a>' +
           '<a href="/exam"><b>04</b><strong>Экзамен</strong><span>Проверить время</span></a>' +
         '</div>' +
       '</section>' +
       '<section class="facts" aria-label="Курс в цифрах">' + facts + '</section>' +
      '<a class="practice-band" href="/labs">' +
        '<span class="practice-band-num">' + (labs.length || 14) + '</span>' +
        '<span class="practice-band-body">' +
          '<span class="kicker">Практикум</span>' +
          '<strong>Лабораторные: код, SQL и Laravel</strong>' +
          '<small>Пишете решение, отправляете на проверку и получаете разбор по критериям. Не тест с вариантами — рабочая задача.</small>' +
        '</span>' +
        '<span class="practice-band-go">Открыть практикум →</span>' +
      '</a>' +
      '<section class="study-method" aria-labelledby="study-method-title">' +
        '<div class="section-head"><h2 id="study-method-title">Как учиться, а не переписывать</h2><p>Один и тот же цикл для любого проекта из КИМ.</p></div>' +
        '<div class="study-method-grid">' +
          '<article><b>01 · Понять</b><h3>Прочитайте требование</h3><p>Назовите сущности, поля, роли, страницы и статусы своими словами.</p></article>' +
          '<article><b>02 · Спроектировать</b><h3>Сначала план</h3><p>Нарисуйте таблицы и связи, затем запишите маршрут пользователя от формы до базы.</p></article>' +
          '<article><b>03 · Написать</b><h3>Без подсматривания</h3><p>Откройте режим «Учусь сам» — код и команды появятся только после вашего плана.</p></article>' +
          '<article><b>04 · Проверить</b><h3>Ошибка — это проверка</h3><p>Запустите тест, прочитайте ошибку, исправьте причину и только потом сравните решение.</p></article>' +
        '</div>' +
        '<div class="study-method-actions"><a class="btn btn-accent" href="/build">Начать самостоятельную сборку →</a><a class="btn btn-ghost" href="/labs">Решать задачи без решения</a><a class="btn btn-ghost" href="/topic">Своя тема по критериям КИМ</a></div>' +
      '</section>' +
      '<div class="section-head"><h2>Маршрут</h2><p>Девять модулей. Идти по порядку — не обязательно, но первые три дают базу для остальных.</p></div>' +
      '<div class="route">' + routeRows + '</div>' +
      '<div class="section-head"><h2>Инструменты</h2><p>Три вещи, которые понадобятся на экзамене и во время подготовки.</p></div>' +
      '<div class="tool-grid">' +
        '<a class="tool" href="#/recipes"><span class="tool-num">01 / справочник</span><h3>Рецепты кода</h3><p>Валидация, вход, загрузка файла, статусы, фильтры. Готовые куски с объяснением, откуда их вставлять.</p><span class="tool-go">Открыть рецепты →</span></a>' +
        '<a class="tool" href="#/brief"><span class="tool-num">02 / подготовка</span><h3>Конструктор ТЗ</h3><p>Введите тему и сущности — получите структуру таблиц, маршрутов и проверок под свой вариант.</p><span class="tool-go">Собрать ТЗ →</span></a>' +
        '<a class="tool" href="#/exam"><span class="tool-num">03 / репетиция</span><h3>Таймер на 3:30</h3><p>Разбивает экзамен на этапы и показывает, где вы должны быть сейчас. Проверяйте себя до экзамена.</p><span class="tool-go">Запустить таймер →</span></a>' +
      '</div>' +
      '<div class="section-head"><h2>План на восемь недель</h2><p>По 6–8 часов в неделю. Если до экзамена меньше — сжимайте теорию, но не пропускайте практику.</p></div>' +
      '<table class="plan"><thead><tr><th>Когда</th><th>Тема</th><th>Что сделать руками</th></tr></thead><tbody>' + plan + '</tbody></table>' +
      '<div class="home-foot">' +
        '<span>Тренажёр не заменяет официальный КИМ: проверяйте формулировки задания по своему комплекту.</span>' +
        '<a href="#/lesson/' + esc(flat[0] ? flat[0].lesson.id : '') + '">К первому уроку →</a>' +
      '</div>';

    setCrumbs('<a href="#/">Курс</a><span class="sep">/</span><span class="current">Главная</span>');
    afterRender();
  }

  /* ---------------------------------------------------------------- lesson */

  function renderLesson(item) {
    var l = item.lesson, m = item.module;
    document.title = l.title + ' — ДЭ-2027';
    var idx = lessonIndex(l.id);
    var prev = flat[idx - 1], next = flat[idx + 1];
    var isDone = !!state.done[l.id];
    var checks = state.checks[l.id] || {};

    state.last = l.id;
    save();

    var practice = (l.practice || []).map(function (p, i) {
      return '<label class="check">' +
        '<input type="checkbox" data-check="' + i + '"' + (checks[i] ? ' checked' : '') + '>' +
        '<span class="check-box" aria-hidden="true">' + ICON.check + '</span>' +
        '<span class="check-text">' + md(p) + '</span>' +
      '</label>';
    }).join('');

    var quiz = (l.quiz || []).map(function (q, qi) {
      var saved = state.quiz[l.id + ':' + qi] || {};
      var options = q.options.map(function (o, oi) {
        var cls = 'quiz-option';
        if (saved.checked) {
          cls += ' is-locked';
          if (oi === q.answer) cls += ' is-correct';
          else if (oi === saved.picked) cls += ' is-wrong';
        }
        return '<label class="' + cls + '">' +
          '<input type="radio" name="q_' + qi + '" value="' + oi + '"' + (saved.picked === oi ? ' checked' : '') + (saved.checked ? ' disabled' : '') + '>' +
          '<span>' + md(o) + '</span>' +
        '</label>';
      }).join('');
      return '<div class="quiz-q" data-q="' + qi + '">' +
        '<fieldset><legend class="quiz-question">' + (qi + 1) + '. ' + md(q.q) + '</legend>' +
        '<div class="quiz-options">' + options + '</div></fieldset>' +
        '<div class="quiz-actions">' +
          '<button class="btn btn-sm btn-ghost" type="button" data-quiz-check="' + qi + '"' + (saved.checked ? ' disabled' : '') + '>' +
            (saved.checked ? 'Проверено' : 'Проверить') + '</button>' +
          '<span class="quiz-result" data-quiz-result="' + qi + '">' + (saved.checked ? (saved.picked === q.answer ? 'Верно' : 'Неверно') : '') + '</span>' +
        '</div>' +
        '<div class="quiz-explain' + (saved.checked ? (saved.picked === q.answer ? ' is-ok' : ' is-bad') : '') + '" data-quiz-explain="' + qi + '"' + (saved.checked ? '' : ' hidden') + '>' + md(q.explain || '') + '</div>' +
      '</div>';
    }).join('');

    var body = renderInlineBlocks(l.blocks);
    var lab = null;

    for (var li = 0; li < labs.length; li++) {
      if (labs[li].lesson_slug === l.id) { lab = labs[li]; break; }
    }

    var labCallout = lab
      ? '<a class="lesson-lab" href="/labs/' + esc(lab.slug) + '">' +
          '<span class="kicker">Практика</span>' +
          '<strong>' + esc(lab.title) + '</strong>' +
          '<span>' + lab.minutes + ' мин · открыть лабораторную →</span>' +
        '</a>'
      : '';

    document.getElementById('main').innerHTML =
      '<article>' +
        '<header class="lesson-head">' +
          '<div class="lesson-eyebrow">' +
            '<span class="mod">Модуль ' + esc(m.num) + ' · ' + esc(m.title) + '</span>' +
            '<span class="dot"></span>' +
            '<span>Урок ' + (idx + 1) + ' из ' + total + '</span>' +
          '</div>' +
          '<h1>' + esc(l.title) + '</h1>' +
          '<div class="lesson-meta">' +
            '<span>' + ICON.clock + (l.minutes || 10) + ' мин</span>' +
            '<span>' + ICON.level + esc(l.level || 'база') + '</span>' +
            (l.tags && l.tags.length ? '<span>' + l.tags.map(function (t) { return '#' + esc(t); }).join(' ') + '</span>' : '') +
          '</div>' +
        '</header>' +
        '<div class="lesson-body">' +
          '<div class="callout callout--goal"><span class="callout-label">Цель урока</span><p>' + md(l.goal || '') + '</p></div>' +
          '<div class="lesson-self-check"><b>Перед чтением кода</b><span>Ответьте себе: что должно появиться после этого урока? Какой файл отвечает за действие? Как вы проверите результат?</span></div>' +
          body +
        '</div>' +
        (practice ? '<section class="practice"><div class="practice-head"><h2>Практика</h2><span data-check-count>' + Object.keys(checks).filter(function (k) { return checks[k]; }).length + ' / ' + (l.practice || []).length + '</span></div><div class="checklist">' + practice + '</div></section>' : '') +
        labCallout +
        (quiz ? '<details class="quiz-collapse"><summary><span>Самопроверка теории</span><small>' + (l.quiz || []).length + ' ' + plural((l.quiz || []).length, 'вопрос', 'вопроса', 'вопросов') + ' · необязательно</small></summary><div class="quiz">' + quiz + '</div></details>' : '') +
        '<section class="notes"><label for="notes">Заметки по уроку</label><textarea id="notes" placeholder="Что оказалось сложным, что переделать в своём проекте">' + esc(state.notes[l.id] || '') + '</textarea><div class="saved" id="notesSaved">Сохранено</div></section>' +
        '<footer class="lesson-foot">' +
          '<div class="done-wrap">' +
            '<button class="btn ' + (isDone ? 'btn-ghost' : 'btn-accent') + '" type="button" data-done="' + esc(l.id) + '">' +
              (isDone ? 'Вернуть в работу' : 'Отметить урок пройденным') +
            '</button>' +
            (isDone ? '<span class="stamp">Сдано</span>' : '<span class="hint">Отмечайте, когда сделали практику. Прогресс сохраняется автоматически.</span>') +
          '</div>' +
        '</footer>' +
        '<nav class="pager" aria-label="Соседние уроки">' +
          (prev ? '<a href="#/lesson/' + esc(prev.lesson.id) + '" class="prev"><span class="dir">← Предыдущий</span><span class="name">' + esc(prev.lesson.title) + '</span></a>' : '<span class="empty"></span>') +
          (next ? '<a href="#/lesson/' + esc(next.lesson.id) + '" class="next"><span class="dir">Следующий →</span><span class="name">' + esc(next.lesson.title) + '</span></a>' : '<span class="empty"></span>') +
        '</nav>' +
      '</article>';

    setCrumbs('<a href="#/">Курс</a><span class="sep">/</span><a href="#/lesson/' + esc(m.lessons[0].id) + '">' + esc(m.title) + '</a><span class="sep">/</span><span class="current">' + esc(l.title) + '</span>');
    afterRender();
  }

  /* --------------------------------------------------------------- recipes */

  var recipeFilter = 'все';

  function renderRecipes(focusId) {
    document.title = 'Рецепты кода — ДЭ-2027';
    if (focusId) recipeFilter = 'все';
    var tags = ['все'];
    recipes.forEach(function (r) {
      (r.tags || []).forEach(function (t) { if (tags.indexOf(t) === -1) tags.push(t); });
    });

    var filters = tags.map(function (t) {
      return '<button class="filter-btn' + (t === recipeFilter ? ' is-active' : '') + '" type="button" data-filter="' + esc(t) + '">' + esc(t) + '</button>';
    }).join('');

    var list = recipes.filter(function (r) {
      return recipeFilter === 'все' || (r.tags || []).indexOf(recipeFilter) !== -1;
    }).map(function (r, i) {
      var num = String(recipes.indexOf(r) + 1);
      if (num.length < 2) num = '0' + num;
      var tagsHtml = (r.tags || []).map(function (t) { return '<span class="tag-mini">' + esc(t) + '</span>'; }).join('');
      var open = !!state.openRecipes && state.openRecipes[r.id];
      return '<article class="recipe' + (open ? ' is-open' : '') + '" id="recipe-' + esc(r.id) + '">' +
        '<div class="recipe-head">' +
          '<span class="recipe-num">' + num + '</span>' +
          '<div><h2>' + esc(r.title) + '</h2><p class="recipe-when">' + md(r.when) + '</p>' +
            '<button class="recipe-toggle" type="button" data-recipe="' + esc(r.id) + '">' + ICON.chevron + (open ? 'Скрыть код' : 'Показать код') + '</button>' +
          '</div>' +
          '<div class="recipe-tags">' + tagsHtml + '</div>' +
        '</div>' +
        '<div class="recipe-body">' + codeBlock({ v: r.code, lang: r.lang || 'php', file: r.file }) +
          (r.after ? '<p class="small muted" style="margin-top:10px">' + md(r.after) + '</p>' : '') +
        '</div>' +
      '</article>';
    }).join('');

    document.getElementById('main').innerHTML =
      '<header class="page-head">' +
        '<span class="kicker">Справочник</span>' +
        '<h1>Рецепты кода</h1>' +
        '<p>Куски, которые повторяются из варианта в вариант. Читайте не только код, но и строку «когда нужно»: на экзамене важно вовремя вспомнить, куда это вставлять.</p>' +
      '</header>' +
      '<div class="filters" role="group" aria-label="Фильтр по теме">' + filters + '</div>' +
      (list ? '<div class="recipe-grid">' + list + '</div>' : '<div class="empty-state">В этой категории пока нет рецептов.</div>') +
      '<div class="home-foot"><span>Всего ' + recipes.length + ' ' + plural(recipes.length, 'рецепт', 'рецепта', 'рецептов') + '.</span><a href="#/lesson/laravel-auth">К уроку про вход →</a></div>';

    setCrumbs('<a href="#/">Курс</a><span class="sep">/</span><span class="current">Рецепты кода</span>');
    afterRender();

    if (focusId) {
      var card = document.getElementById('recipe-' + focusId);
      if (card) {
        card.classList.add('is-open');
        var toggle = card.querySelector('[data-recipe]');
        if (toggle) toggle.innerHTML = ICON.chevron + 'Скрыть код';
        state.openRecipes = state.openRecipes || {};
        state.openRecipes[focusId] = true;
        save();
        highlightAll(card);
        var targetY = card.getBoundingClientRect().top + window.scrollY - 84;
        window.scrollTo({ top: targetY, behavior: 'smooth' });
      }
    }
  }

  /* ----------------------------------------------------------------- brief */

  function renderBrief() {
    document.title = 'Конструктор ТЗ — ДЭ-2027';
    document.getElementById('main').innerHTML =
      '<header class="page-head">' +
        '<span class="kicker">Подготовка к своему варианту</span>' +
        '<h1>Конструктор технического задания</h1>' +
        '<p>На экзамене тема другая, а структура та же. Введите свои сущности — получите таблицы, маршруты, проверки и чек-лист. Это черновик для вас, не официальный документ.</p>' +
      '</header>' +
      '<div class="brief-grid">' +
        '<form class="brief-form" id="briefForm" autocomplete="off">' +
          '<h2>Исходные данные</h2>' +
          '<p>Поля можно менять в любой момент — документ пересобирается сразу.</p>' +
          '<div class="field"><label for="bTheme">Тема / название системы</label><input id="bTheme" name="theme" value="Конференции.РФ" maxlength="80"></div>' +
          '<div class="field"><label for="bObject">Объект, который выбирают</label><input id="bObject" name="object" value="Помещение" maxlength="40"><span class="hint">Например: помещение, автомобиль, врач, курс</span></div>' +
          '<div class="field"><label for="bRequest">Сущность заявки</label><input id="bRequest" name="request" value="Заявка" maxlength="40"><span class="hint">Что создаёт пользователь</span></div>' +
          '<div class="field"><label for="bStatuses">Статусы через запятую</label><input id="bStatuses" name="statuses" value="Новая, Мероприятие назначено, Завершено" maxlength="160"></div>' +
          '<div class="field"><label for="bPayment">Способы оплаты</label><input id="bPayment" name="payment" value="При очном посещении, Переводом по СБП" maxlength="120"></div>' +
          '<div class="field"><label for="bExtra">Дополнительное поле заявки</label><input id="bExtra" name="extra" value="Дата начала" maxlength="60"></div>' +
          '<div class="field"><label for="bReview">Что оценивают после завершения</label><input id="bReview" name="review" value="Качество мероприятия" maxlength="80"></div>' +
          '<div class="field"><label for="bAdmin">Логин и пароль администратора</label><input id="bAdmin" name="admin" value="Conf2027 / Demo77" maxlength="60"></div>' +
        '</form>' +
        '<section class="brief-out" id="briefOut" aria-live="polite"></section>' +
      '</div>';

    setCrumbs('<a href="#/">Курс</a><span class="sep">/</span><span class="current">Конструктор ТЗ</span>');
    updateBrief();
    afterRender();
  }

  function briefData() {
    var get = function (id) { var el = document.getElementById(id); return el ? el.value.trim() : ''; };
    var statuses = get('bStatuses').split(',').map(function (s) { return s.trim(); }).filter(Boolean);
    return {
      theme: get('bTheme') || 'Типовой проект',
      object: get('bObject') || 'Объект',
      request: get('bRequest') || 'Заявка',
      statuses: statuses.length ? statuses : ['Новая', 'В работе', 'Завершено'],
      payment: get('bPayment').split(',').map(function (s) { return s.trim(); }).filter(Boolean),
      extra: get('bExtra') || 'Дата',
      review: get('bReview') || 'Качество услуги',
      admin: get('bAdmin') || 'admin / admin'
    };
  }

  function briefHtml(d) {
    var today = new Date().toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' });
    var statusKeys = ['new', 'assigned', 'completed', 'rejected'];
    var paymentKeys = ['in_person', 'sbp', 'card', 'cash'];
    var statusRows = d.statuses.map(function (s, i) {
      var key = statusKeys[i] || ('status_' + (i + 1));
      return '<tr><td><code>' + key + '</code></td><td>' + esc(s) + '</td></tr>';
    }).join('');
    var paymentRows = d.payment.map(function (p, i) {
      var key = paymentKeys[i] || ('payment_' + (i + 1));
      return '<tr><td><code>' + key + '</code></td><td>' + esc(p) + '</td></tr>';
    }).join('');

    return '<h2>' + esc(d.theme) + '</h2>' +
      '<div class="doc-meta"><span>Черновик ТЗ</span><span>' + today + '</span><span>09.02.07 · ГИА ДЭ БУ</span><span>3 ч 30 мин</span></div>' +
      '<p>Система хранит записи типа «' + esc(d.request) + '» в базе и показывает их администратору. Пользователь выбирает объект «' + esc(d.object) + '» (в задании 1 — вводом названия, в задании 2 — из выпадающего списка), указывает значение поля «' + esc(d.extra) + '» и способ оплаты. Администратор меняет статус и видит все записи с фильтрами.</p>' +
      '<h3>1. Таблицы</h3>' +
      '<div class="table-wrap"><table class="data"><thead><tr><th>Таблица</th><th>Поля</th></tr></thead><tbody>' +
        '<tr><td><code>users</code></td><td><code>id</code>, <code>fio</code>, <code>login</code> (unique), <code>phone</code>, <code>email</code> (unique), <code>password</code>, <code>role</code> (<code>admin</code>/<code>user</code>), <code>timestamps</code></td></tr>' +
        '<tr><td><code>objects</code></td><td><code>id</code>, <code>name</code>, <code>description</code>, <code>image</code>, <code>timestamps</code></td></tr>' +
        '<tr><td><code>requests</code></td><td><code>id</code>, <code>user_id</code> → users, <code>object_id</code> → objects, <code>extra</code>, <code>payment_type</code>, <code>status</code>, <code>rating</code>, <code>review</code>, <code>timestamps</code></td></tr>' +
      '</tbody></table></div>' +
      '<p class="small muted">Имена <code>objects</code> и <code>requests</code> замените на сущности своей темы: <code>rooms</code>, <code>applications</code>; <code>cars</code>, <code>test_drives</code>.</p>' +
      '<h3>2. Статусы</h3>' +
      '<div class="table-wrap"><table class="data"><thead><tr><th>В базе</th><th>На экране</th></tr></thead><tbody>' + statusRows + '</tbody></table></div>' +
      '<h3>3. Способы оплаты</h3>' +
      '<div class="table-wrap"><table class="data"><thead><tr><th>В базе</th><th>На экране</th></tr></thead><tbody>' + paymentRows + '</tbody></table></div>' +
      '<h3>4. Экраны и маршруты</h3>' +
      '<div class="table-wrap"><table class="data"><thead><tr><th>Метод</th><th>URL</th><th>Что делает</th></tr></thead><tbody>' +
        '<tr><td>GET</td><td><code>/</code></td><td>Главная: описание и список ' + esc(d.object.toLowerCase()) + '</td></tr>' +
        '<tr><td>GET/POST</td><td><code>/register</code></td><td>Регистрация с валидацией</td></tr>' +
        '<tr><td>GET/POST</td><td><code>/login</code></td><td>Вход, ошибки, ссылка на регистрацию</td></tr>' +
        '<tr><td>GET</td><td><code>/profile</code></td><td>Свои ' + esc(d.request.toLowerCase()) + ' и отзыв после завершения</td></tr>' +
        '<tr><td>GET/POST</td><td><code>/application</code></td><td>Форма: ' + esc(d.object.toLowerCase()) + ', ' + esc(d.extra.toLowerCase()) + ', оплата</td></tr>' +
        '<tr><td>GET/POST</td><td><code>/admin/login</code></td><td>Вход администратора</td></tr>' +
        '<tr><td>GET</td><td><code>/admin</code></td><td>Все ' + esc(d.request.toLowerCase()) + ', фильтр по статусу, поиск</td></tr>' +
        '<tr><td>POST</td><td><code>/admin/application/{id}/status</code></td><td>Смена статуса</td></tr>' +
      '</tbody></table></div>' +
      '<h3>5. Проверки полей</h3>' +
      '<ul>' +
        '<li>Логин: латиница и цифры, минимум 6 символов, уникальный.</li>' +
        '<li>Пароль: минимум 8 символов, хранится через <code>Hash::make</code>.</li>' +
        '<li>ФИО: только кириллица, пробелы и дефис.</li>' +
        '<li>Телефон: <code>8(XXX)XXX-XX-XX</code>, маска на вводе.</li>' +
        '<li>Email: корректный адрес, уникальный.</li>' +
        '<li>' + esc(d.extra) + ': обязательное поле, дата не в прошлом.</li>' +
        '<li>Оценка: целое число от 1 до 5, отзыв — до 1000 символов.</li>' +
      '</ul>' +
      '<h3>6. Доступ</h3>' +
      '<ul>' +
        '<li>Гость: главная, регистрация, вход.</li>' +
        '<li>Пользователь: кабинет, создание ' + esc(d.request.toLowerCase()) + ', отзыв после статуса «' + esc(d.statuses[d.statuses.length - 1] || 'Завершено') + '».</li>' +
        '<li>Администратор: <code>' + esc(d.admin) + '</code>, панель со всеми ' + esc(d.request.toLowerCase()) + '.</li>' +
        '<li>Прямой доступ к чужим ' + esc(d.request.toLowerCase()) + ' — 403.</li>' +
      '</ul>' +
      '<h3>7. Что проверить перед сдачей</h3>' +
      '<ul>' +
        '<li>Миграции запускаются с нуля: <code>php artisan migrate:fresh --seed</code>.</li>' +
        '<li>Валидация показывает русские сообщения у каждого поля.</li>' +
        '<li>Пароли не хранятся открытым текстом.</li>' +
        '<li>Файлы изображений лежат в <code>storage/app/public</code>, ссылка создана.</li>' +
        '<li>Мобильный макет 390×844: отдельный HTML-файл на каждую страницу.</li>' +
        '<li>Нет горизонтальной прокрутки на 390, 768 и 1440 px.</li>' +
        '<li>Статусы меняются, отзыв доступен только после завершения.</li>' +
      '</ul>' +
      '<div class="brief-actions">' +
        '<button class="btn btn-sm" type="button" data-brief-copy>Скопировать текстом</button>' +
        '<button class="btn btn-sm btn-ghost" type="button" data-brief-download>Скачать .txt</button>' +
        '<button class="btn btn-sm btn-ghost" type="button" data-brief-print>Печать</button>' +
      '</div>';
  }

  function briefText(d) {
    var out = [];
    out.push(d.theme.toUpperCase());
    out.push('Черновик ТЗ · 09.02.07 · ГИА ДЭ БУ · 3 ч 30 мин');
    out.push('');
    out.push('Пользователь выбирает объект «' + d.object + '», указывает значение поля «' + d.extra + '» и способ оплаты. Администратор видит все записи и меняет статус.');
    out.push('');
    out.push('ТАБЛИЦЫ');
    out.push('users: id, fio, login (unique), phone, email (unique), password, role, timestamps');
    out.push('objects: id, name, description, image, timestamps');
    out.push('requests: id, user_id, object_id, extra, payment_type, status, rating, review, timestamps');
    out.push('');
    out.push('СТАТУСЫ: ' + d.statuses.join(' → '));
    out.push('ОПЛАТА: ' + d.payment.join(' / '));
    out.push('');
    out.push('МАРШРУТЫ');
    ['GET / — главная', 'GET/POST /register — регистрация', 'GET/POST /login — вход', 'GET /profile — свои ' + d.request.toLowerCase(), 'GET/POST /application — создание', 'GET/POST /admin/login — вход администратора', 'GET /admin — все ' + d.request.toLowerCase(), 'POST /admin/application/{id}/status — смена статуса'].forEach(function (x) { out.push('- ' + x); });
    out.push('');
    out.push('ВАЛИДАЦИЯ');
    ['Логин: латиница и цифры, от 6 символов, unique', 'Пароль: от 8 символов, Hash::make', 'ФИО: кириллица, пробелы, дефис', 'Телефон: 8(XXX)XXX-XX-XX', 'Email: корректный, unique', d.extra + ': обязательное поле по требованиям задания', 'Оценка 1–5, отзыв до 1000 символов'].forEach(function (x) { out.push('- ' + x); });
    out.push('');
    out.push('ДОСТУП: гость / пользователь / администратор (' + d.admin + '). Чужие ' + d.request.toLowerCase() + ' — 403.');
    out.push('');
    out.push('ПРОВЕРКА: migrate:fresh --seed, русские ошибки, хеш пароля, storage:link, мобильный макет 390×844, нет горизонтальной прокрутки, статусы и отзыв работают.');
    return out.join('\n');
  }

  function updateBrief() {
    var d = briefData();
    var out = document.getElementById('briefOut');
    if (out) out.innerHTML = briefHtml(d);
    return d;
  }

  /* ------------------------------------------------------------------ exam */

  var timerTick = null;

  function timerRemaining() {
    var t = state.timer;
    if (t.running && t.endsAt) return Math.max(0, t.endsAt - Date.now());
    return Math.max(0, t.remaining || 0);
  }

  function renderExam() {
    document.title = 'Таймер экзамена — ДЭ-2027';
    document.getElementById('main').innerHTML =
      '<div class="exam-page">' +
        '<header class="page-head">' +
          '<span class="kicker">Репетиция</span>' +
          '<h1>3 часа 30 минут на проект</h1>' +
          '<p>Таймер помогает привыкнуть к темпу экзамена. Запускайте его, когда садитесь делать проект целиком, а не отдельный урок.</p>' +
        '</header>' +
        '<div class="timer-box">' +
          '<div class="timer-top"><span>До конца экзамена</span><span class="state" id="timerState">' + (state.timer.running ? 'идёт' : 'на паузе') + '</span></div>' +
          '<div class="timer-digits" id="timerDigits" aria-live="off"><span id="tH">04</span><span class="colon">:</span><span id="tM">00</span><span class="colon">:</span><span class="sec" id="tS">00</span></div>' +
          '<div class="timer-actions">' +
            '<button class="btn btn-accent" type="button" id="timerToggle">' + (state.timer.running ? 'Пауза' : 'Старт') + '</button>' +
            '<button class="btn" type="button" id="timerReset">Сброс на 3:30:00</button>' +
          '</div>' +
          '<p class="timer-note">Таймер идёт в фоне и переживает перезагрузку страницы. Это не официальный регламент — сверяйтесь с КИМ.</p>' +
        '</div>' +
        '<h2 style="margin-top:0">Где вы должны быть</h2>' +
        '<table class="milestones" id="milestones"><thead><tr><th>Время</th><th>Этап</th><th>Результат</th></tr></thead><tbody>' +
          '<tr data-at="0"><td>0:00–0:25</td><td>База и каркас</td><td>Проект создан, таблицы и модели готовы, сидер с админом</td></tr>' +
          '<tr data-at="25"><td>0:25–1:20</td><td>Аутентификация</td><td>Регистрация, вход, выход, роли, защита маршрутов</td></tr>' +
          '<tr data-at="80"><td>1:20–2:00</td><td>Основной сценарий</td><td>Форма заявки, кабинет, админка, статусы, отзыв</td></tr>' +
          '<tr data-at="120"><td>2:00–2:40</td><td>Дизайн и мобильный макет</td><td>390×844, отдельные HTML, картинки сжаты</td></tr>' +
          '<tr data-at="160"><td>2:40–3:05</td><td>Адаптив и интерактив</td><td>Меню, маска, рейтинг, hover, пустые состояния</td></tr>' +
          '<tr data-at="185"><td>3:05–3:30</td><td>Проверка</td><td>migrate:fresh, консоль, 390/768/1440, коммит</td></tr>' +
        '</tbody></table>' +
        '<div class="callout callout--note" style="margin-top:26px"><span class="callout-label">Как тренироваться</span><p>Первый прогон — с подсказками и интернетом. Второй — только с открытым ТЗ. Третий — без интернета, кроме документации, которая будет на экзамене. Цель не «успеть всё», а довести до конца основной сценарий и не потерять баллы на мелочах.</p></div>' +
      '</div>';

    setCrumbs('<a href="#/">Курс</a><span class="sep">/</span><span class="current">Таймер экзамена</span>');
    paintTimer();
    afterRender();

    clearInterval(timerTick);
    timerTick = setInterval(function () {
      if (state.timer.running) paintTimer();
    }, 1000);
  }

  function paintTimer() {
    var ms = timerRemaining();
    var totalSec = Math.floor(ms / 1000);
    var h = Math.floor(totalSec / 3600);
    var m = Math.floor((totalSec % 3600) / 60);
    var s = totalSec % 60;
    var eh = document.getElementById('tH');
    if (!eh) return;
    eh.textContent = pad(h);
    document.getElementById('tM').textContent = pad(m);
    document.getElementById('tS').textContent = pad(s);
    document.getElementById('timerState').textContent = ms === 0 ? 'время вышло' : (state.timer.running ? 'идёт' : 'на паузе');

    if (ms === 0 && state.timer.running) {
      state.timer.running = false;
      state.timer.endsAt = 0;
      state.timer.remaining = 0;
      save();
      var toggleBtn = document.getElementById('timerToggle');
      if (toggleBtn) toggleBtn.textContent = 'Старт';
    }

    var elapsedMin = (EXAM_MS - ms) / 60000;
    document.querySelectorAll('#milestones tr[data-at]').forEach(function (tr) {
      tr.classList.toggle('is-past', elapsedMin >= Number(tr.getAttribute('data-at')));
    });
  }

  function toggleTimer() {
    var t = state.timer;
    if (t.running) {
      t.remaining = timerRemaining();
      t.running = false;
      t.endsAt = 0;
    } else {
      if (!t.remaining) t.remaining = EXAM_MS;
      t.endsAt = Date.now() + t.remaining;
      t.running = true;
    }
    save();
    var btn = document.getElementById('timerToggle');
    if (btn) btn.textContent = t.running ? 'Пауза' : 'Старт';
    paintTimer();
  }

  function resetTimer() {
    state.timer = { endsAt: 0, remaining: EXAM_MS, running: false };
    save();
    renderExam();
  }

  /* ----------------------------------------------------------------- search */

  var searchIndex = [];
  var searchSelected = 0;
  var searchItems = [];

  function buildSearchIndex() {
    searchIndex = [];
    flat.forEach(function (x) {
      searchIndex.push({
        kind: 'урок',
        title: x.lesson.title,
        sub: 'Модуль ' + x.module.num + ' · ' + x.module.title,
        href: '#/lesson/' + x.lesson.id,
        text: norm(plainLesson(x.lesson))
      });
    });
    recipes.forEach(function (r) {
      searchIndex.push({
        kind: 'рецепт',
        title: r.title,
        sub: (r.tags || []).join(' · '),
        href: '#/recipes/' + r.id,
        text: norm(r.title + ' ' + r.when + ' ' + r.code)
      });
    });
  }

  function openSearch() {
    var modal = document.getElementById('searchModal');
    modal.hidden = false;
    document.body.style.overflow = 'hidden';
    var input = document.getElementById('searchInput');
    input.value = '';
    searchSelected = 0;
    renderSearch('');
    setTimeout(function () { input.focus(); }, 20);
  }

  function closeSearch() {
    var modal = document.getElementById('searchModal');
    if (!modal || modal.hidden) return;
    modal.hidden = true;
    document.body.style.overflow = '';
  }

  function renderSearch(q) {
    var tokens = norm(q).split(' ').filter(Boolean);
    var ranked = searchIndex.map(function (item) {
      var title = norm(item.title);
      var score = 0;
      tokens.forEach(function (t) {
        if (title.indexOf(t) === 0) score += 4;
        else if (title.indexOf(t) !== -1) score += 3;
        else if (item.text.indexOf(t) !== -1) score += 1;
      });
      return { item: item, score: score };
    }).filter(function (x) {
      return tokens.every(function (t) { return x.item.text.indexOf(t) !== -1 || norm(x.item.title).indexOf(t) !== -1; });
    }).sort(function (a, b) { return b.score - a.score; });

    searchItems = ranked.slice(0, 9).map(function (x) { return x.item; });

    if (!q.trim()) {
      searchItems = searchIndex.slice(0, 7);
    }

    var box = document.getElementById('searchResults');
    if (!searchItems.length) {
      box.innerHTML = '<div class="search-empty">Ничего не нашлось. Попробуйте «валидация», «статус», «blade».</div>';
      return;
    }
    box.innerHTML = searchItems.map(function (it, i) {
      return '<a class="search-item' + (i === searchSelected ? ' is-sel' : '') + '" href="' + it.href + '">' +
        '<span class="kind">' + it.kind + '</span>' +
        '<span class="ttl">' + esc(it.title) + '</span>' +
        '<span class="mod">' + esc(it.sub) + '</span>' +
      '</a>';
    }).join('');
  }

  function moveSearch(delta) {
    if (!searchItems.length) return;
    searchSelected = (searchSelected + delta + searchItems.length) % searchItems.length;
    var items = document.querySelectorAll('.search-item');
    items.forEach(function (el, i) { el.classList.toggle('is-sel', i === searchSelected); });
    var sel = items[searchSelected];
    if (sel) sel.scrollIntoView({ block: 'nearest' });
  }

  function goSearch() {
    var item = searchItems[searchSelected];
    if (item) { closeSearch(); location.hash = item.href; }
  }

  /* ------------------------------------------------------------------ rail */

  function openRail() {
    document.getElementById('rail').classList.add('is-open');
    document.getElementById('railScrim').hidden = false;
    document.getElementById('menuBtn').setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
  }

  function closeRail() {
    document.getElementById('rail').classList.remove('is-open');
    document.getElementById('railScrim').hidden = true;
    var btn = document.getElementById('menuBtn');
    if (btn) btn.setAttribute('aria-expanded', 'false');
    if (!document.getElementById('searchModal').hidden) return;
    document.body.style.overflow = '';
  }

  /* ----------------------------------------------------------------- events */

  document.addEventListener('click', function (e) {
    var t = e.target;

    var copy = t.closest('[data-copy]');
    if (copy) {
      var pre = copy.closest('.code').querySelector('pre');
      var text = pre ? pre.innerText : '';
      var done = function () {
        copy.classList.add('is-copied');
        copy.innerHTML = ICON.check + ' Скопировано';
        setTimeout(function () { copy.classList.remove('is-copied'); copy.innerHTML = ICON.copy + ' Копировать'; }, 1600);
      };
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(done).catch(function () { fallbackCopy(text, done); });
      } else {
        fallbackCopy(text, done);
      }
      return;
    }

    var mod = t.closest('[data-module]');
    if (mod) {
      var id = mod.getAttribute('data-module');
      state.openModule = state.openModule === id ? '' : id;
      mod.closest('.rail-module').classList.toggle('is-open');
      mod.setAttribute('aria-expanded', mod.closest('.rail-module').classList.contains('is-open') ? 'true' : 'false');
      save();
      return;
    }

    var doneBtn = t.closest('[data-done]');
    if (doneBtn) {
      var lid = doneBtn.getAttribute('data-done');
      var scrollY = window.scrollY;
      state.done[lid] = !state.done[lid];
      save();
      renderRail();
      var item = findLesson(lid);
      if (item) {
        renderLesson(item);
        window.scrollTo(0, scrollY);
      }
      return;
    }

    var quizBtn = t.closest('[data-quiz-check]');
    if (quizBtn) {
      var qi = Number(quizBtn.getAttribute('data-quiz-check'));
      var lessonId = currentLessonId();
      var item = findLesson(lessonId);
      var q = item && item.lesson.quiz ? item.lesson.quiz[qi] : null;
      if (!q) return;
      var selected = document.querySelector('.quiz-q[data-q="' + qi + '"] input:checked');
      var wrap = document.querySelector('.quiz-q[data-q="' + qi + '"]');
      if (!selected) {
        wrap.querySelector('.quiz-result').textContent = 'Выберите вариант';
        return;
      }
      var picked = Number(selected.value);
      state.quiz[lessonId + ':' + qi] = { picked: picked, checked: true };
      save();
      wrap.querySelectorAll('.quiz-option').forEach(function (opt, i) {
        opt.classList.add('is-locked');
        opt.querySelector('input').disabled = true;
        if (i === q.answer) opt.classList.add('is-correct');
        else if (i === picked) opt.classList.add('is-wrong');
      });
      var ok = picked === q.answer;
      wrap.querySelector('.quiz-result').textContent = ok ? 'Верно' : 'Неверно';
      var ex = wrap.querySelector('.quiz-explain');
      ex.hidden = false;
      ex.classList.add(ok ? 'is-ok' : 'is-bad');
      quizBtn.disabled = true;
      quizBtn.textContent = 'Проверено';
      return;
    }

    var recipeBtn = t.closest('[data-recipe]');
    if (recipeBtn) {
      var rid = recipeBtn.getAttribute('data-recipe');
      var card = recipeBtn.closest('.recipe');
      var willOpen = !card.classList.contains('is-open');
      card.classList.toggle('is-open', willOpen);
      recipeBtn.innerHTML = ICON.chevron + (willOpen ? 'Скрыть код' : 'Показать код');
      state.openRecipes = state.openRecipes || {};
      state.openRecipes[rid] = willOpen;
      save();
      if (willOpen) highlightAll(card);
      return;
    }

    var filter = t.closest('[data-filter]');
    if (filter) {
      recipeFilter = filter.getAttribute('data-filter');
      renderRecipes();
      return;
    }

    if (t.closest('[data-brief-copy]')) {
      var d = briefData();
      copyText(briefText(d));
      var b = t.closest('[data-brief-copy]');
      b.textContent = 'Скопировано';
      setTimeout(function () { b.textContent = 'Скопировать текстом'; }, 1500);
      return;
    }
    if (t.closest('[data-brief-download]')) {
      var dl = briefData();
      var blob = new Blob([briefText(dl)], { type: 'text/plain;charset=utf-8' });
      var a = document.createElement('a');
      a.href = URL.createObjectURL(blob);
      a.download = 'tz-' + (dl.theme.toLowerCase().replace(/[^a-zа-яё0-9]+/gi, '-').replace(/^-|-$/g, '') || 'project') + '.txt';
      a.click();
      setTimeout(function () { URL.revokeObjectURL(a.href); }, 1000);
      return;
    }
    if (t.closest('[data-brief-print]')) { window.print(); return; }

    if (t.closest('[data-close-modal]')) { closeSearch(); return; }

    if (t.closest('#menuBtn')) { openRail(); return; }
    if (t.closest('#railClose')) { closeRail(); return; }
    if (t.closest('#railScrim')) { closeRail(); return; }
    if (t.closest('#searchBtn')) { openSearch(); return; }
    if (t.closest('#timerToggle')) { toggleTimer(); return; }
    if (t.closest('#timerReset')) { resetTimer(); return; }

    var navLink = t.closest('.rail a, .rail-tools a');
    if (navLink && window.innerWidth <= 980) closeRail();
  });

  document.addEventListener('change', function (e) {
    var check = e.target.closest('[data-check]');
    if (check) {
      var lid = currentLessonId();
      state.checks[lid] = state.checks[lid] || {};
      state.checks[lid][check.getAttribute('data-check')] = check.checked;
      save();
      var count = document.querySelector('[data-check-count]');
      if (count) {
        var vals = state.checks[lid];
        var n = Object.keys(vals).filter(function (k) { return vals[k]; }).length;
        count.textContent = n + ' / ' + vals.length;
      }
    }
  });

  document.addEventListener('input', function (e) {
    if (e.target.id === 'notes') {
      var lid = currentLessonId();
      state.notes[lid] = e.target.value;
      clearTimeout(window.__notesT);
      window.__notesT = setTimeout(function () {
        save();
        var s = document.getElementById('notesSaved');
        if (s) { s.classList.add('is-on'); setTimeout(function () { s.classList.remove('is-on'); }, 1200); }
      }, 350);
    }

    if (e.target.closest('#briefForm')) updateBrief();

    if (e.target.id === 'searchInput') {
      searchSelected = 0;
      renderSearch(e.target.value);
    }
  });

  document.addEventListener('keydown', function (e) {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
      e.preventDefault();
      openSearch();
      return;
    }
    if (e.key === 'Escape') {
      if (!document.getElementById('searchModal').hidden) closeSearch();
      else closeRail();
      return;
    }
    if (!document.getElementById('searchModal').hidden) {
      if (e.key === 'ArrowDown') { e.preventDefault(); moveSearch(1); }
      if (e.key === 'ArrowUp') { e.preventDefault(); moveSearch(-1); }
      if (e.key === 'Enter') { e.preventDefault(); goSearch(); }
    }
  });

  document.getElementById('resetProgress').addEventListener('click', function () {
    if (!window.confirm('Сбросить весь прогресс, заметки и таймер? Это действие нельзя отменить.')) return;

    if (AUTH.user && AUTH.csrf) {
      state = baseState();
      pushServerState();
      try { localStorage.removeItem(LS_KEY); } catch (e) { /* ignore */ }
      setTimeout(function () { location.hash = '#/'; location.reload(); }, 500);
      return;
    }

    try { localStorage.removeItem(LS_KEY); } catch (e) { /* ignore */ }
    location.hash = '#/';
    location.reload();
  });

  /* --------------------------------------------------------------- utilities */

  function fallbackCopy(text, done) {
    var ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed';
    ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.select();
    try { document.execCommand('copy'); done(); } catch (e) { /* ignore */ }
    document.body.removeChild(ta);
  }

  function copyText(text) { fallbackCopy(text, function () {}); }

  /* ------------------------------------------------------------------- boot */

  function initApp() {
    buildSearchIndex();
    renderRail();

    if (state.timer.running && state.timer.endsAt) {
      state.timer.remaining = Math.max(0, state.timer.endsAt - Date.now());
      if (state.timer.remaining === 0) state.timer.running = false;
      save();
    }

    setSync(AUTH.user ? 'на сервере' : 'локально', AUTH.user ? 'is-saved' : '');
    window.addEventListener('hashchange', route);
    route();
  }

  fetch('/api/course', { headers: { 'Accept': 'application/json' } })
    .then(function (res) { return res.ok ? res.json() : null; })
    .then(function (data) {
      if (data && Array.isArray(data.modules) && data.modules.length) {
        setCourse(data);
      }
    })
    .catch(function () { /* офлайн-режим: остаёмся на встроенном контенте */ })
    .finally(initApp);
})();

