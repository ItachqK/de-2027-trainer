// Экспорт уроков из статического тренажёра в JSON для Laravel-сидера.
// Запуск: node tools/export-course.mjs
import fs from 'node:fs';
import vm from 'node:vm';

const ctx = { window: {} };
vm.createContext(ctx);

const files = ['m0-start', 'm1-web', 'm2-php', 'm3-db', 'm4-laravel', 'm5-build', 'm6-design', 'm7-quality', 'm8-exam'];
for (const f of files) {
  vm.runInContext(fs.readFileSync(`../demo-exam-trainer/public/assets/js/content/${f}.js`, 'utf8'), ctx, { filename: f });
}
vm.runInContext(fs.readFileSync('../demo-exam-trainer/public/assets/js/content/recipes.js', 'utf8'), ctx, { filename: 'recipes' });

const modules = ctx.window.COURSE_MODULES.map((m) => ({
  num: m.num,
  title: m.title,
  lead: m.lead,
  lessons: m.lessons.map((l) => ({
    slug: l.id,
    title: l.title,
    minutes: l.minutes,
    level: l.level,
    tags: l.tags || [],
    goal: l.goal,
    blocks: l.blocks,
    practice: l.practice || [],
    quiz: l.quiz || [],
  })),
}));

const recipes = ctx.window.RECIPES.map((r) => ({
  slug: r.id,
  title: r.title,
  when: r.when,
  tags: r.tags || [],
  code: r.code,
  lang: r.lang || 'php',
  file: r.file || null,
  after: r.after || null,
}));

const out = { modules, recipes };
fs.mkdirSync('database/seeders/data', { recursive: true });
fs.writeFileSync('database/seeders/data/course.json', JSON.stringify(out, null, 2), 'utf8');
console.log(`modules: ${modules.length}, lessons: ${modules.reduce((n, m) => n + m.lessons.length, 0)}, recipes: ${recipes.length}`);
