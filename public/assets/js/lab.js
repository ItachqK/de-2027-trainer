(function () {
  'use strict';

  var editor = document.getElementById('code');

  if (editor) {
    editor.addEventListener('keydown', function (event) {
      if (event.key === 'Tab') {
        event.preventDefault();

        var start = editor.selectionStart;
        var end = editor.selectionEnd;
        var indent = '    ';

        editor.value = editor.value.slice(0, start) + indent + editor.value.slice(end);
        editor.selectionStart = editor.selectionEnd = start + indent.length;
      }

      if ((event.ctrlKey || event.metaKey) && event.key === 'Enter') {
        event.preventDefault();
        editor.form.submit();
      }
    });
  }

  var reset = document.getElementById('resetCode');

  if (reset && editor) {
    reset.addEventListener('click', function () {
      if (!window.confirm('Сбросить код к заготовке? Черновик в браузере будет заменён.')) return;

      editor.value = reset.getAttribute('data-starter') || '';
      editor.focus();
    });
  }

  if (location.hash === '#result') {
    var result = document.getElementById('result');

    if (result) {
      result.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }
})();
