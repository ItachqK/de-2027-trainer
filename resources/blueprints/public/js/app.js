// @@PROJECT@@ — небольшие улучшения интерфейса без зависимостей.
// Всё работает и без JS: формы, статусы и навигация — серверные.

(function () {
  'use strict';

  // 1. Успешное сообщение само убирается, чтобы не занимать экран.
  var alerts = document.querySelectorAll('.alert--success');
  alerts.forEach(function (alert) {
    window.setTimeout(function () {
      alert.style.transition = 'opacity .25s ease';
      alert.style.opacity = '0';
      window.setTimeout(function () { alert.remove(); }, 260);
    }, 6000);
  });

  // 2. Защита от двойной отправки: кнопка блокируется после первого клика.
  document.querySelectorAll('form').forEach(function (form) {
    form.addEventListener('submit', function () {
      var button = form.querySelector('button[type="submit"]');
      if (!button) return;
      window.setTimeout(function () {
        button.disabled = true;
        button.textContent = 'Отправляем…';
      }, 0);
    });
  });

  // 3. Раскрытый блок отзыва прокручивается в зону видимости.
  document.querySelectorAll('.review-form').forEach(function (details) {
    details.addEventListener('toggle', function () {
      if (!details.open) return;
      var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      details.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'nearest' });
    });
  });
})();
