<?php

/**
 * Экспорт генератора «Проект по теме» в статическую веб-версию.
 *
 * GitHub Pages не умеет PHP, поэтому все шаблоны, главы, чек-лист и речь
 * выгружаются в один JSON-файл, а движок подстановки работает в браузере.
 *
 * Запуск: php tools/export-web.php [папка-назначения]
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Support\Blueprint;

$target = $argv[1] ?? dirname(__DIR__).'/docs';
$assets = $target.'/assets';

if (! is_dir($assets) && ! mkdir($assets, 0777, true) && ! is_dir($assets)) {
    fwrite(STDERR, "Не удалось создать папку {$assets}\n");
    exit(1);
}

$tokens = [
    'topic' => '@@PROJECT@@',
    'project_name' => '@@PROJECT@@',
    'purpose' => '@@PURPOSE@@',
    'object_singular' => '@@OBJECT@@',
    'object_plural' => '@@OBJECT_PLURAL@@',
    'object_options' => '@@OPTIONS@@',
    'date_label' => '@@DATE_LABEL@@',
    'status_new' => '@@STATUS_NEW@@',
    'status_assigned' => '@@STATUS_ASSIGNED@@',
    'status_done' => '@@STATUS_DONE@@',
    'pay_1' => '@@PAY1@@',
    'pay_2' => '@@PAY2@@',
    'review_text' => '@@REVIEW@@',
    'admin_login' => '@@ADMIN_LOGIN@@',
    'admin_password' => '@@ADMIN_PASSWORD@@',
    'initials' => '@@INITIALS@@',
    'slug' => '@@SLUG@@',
];

$templates = [];
$root = resource_path('blueprints');
$rootPath = str_replace('\\', '/', $root);

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if (! $file->isFile()) {
        continue;
    }

    $path = str_replace('\\', '/', $file->getPathname());
    $relative = ltrim(substr($path, strlen($rootPath)), '/');
    $templates[$relative] = (string) file_get_contents($file->getPathname());
}

ksort($templates);

$payload = [
    'generated' => date('c'),
    'fields' => Blueprint::FIELDS,
    'defaults' => Blueprint::defaults(),
    'presets' => Blueprint::dictionary(),
    'chapters' => Blueprint::chapters($tokens, []),
    'checklist' => Blueprint::checklist(),
    'defense' => Blueprint::defense($tokens),
    'templates' => $templates,
];

$json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

if ($json === false) {
    fwrite(STDERR, 'json_encode: '.json_last_error_msg()."\n");
    exit(1);
}

$banner = "/* Сгенерировано автоматически: php tools/export-web.php. Не редактируйте вручную. */\n";
file_put_contents($assets.'/data.js', $banner.'window.BP_DATA = '.$json.";\n");

$size = round(filesize($assets.'/data.js') / 1024);
echo 'Тем в словаре: '.count(Blueprint::dictionary()).PHP_EOL;
echo 'Глав: '.count($payload['chapters']).PHP_EOL;
echo 'Шаблонов: '.count($templates).PHP_EOL;
echo 'data.js: '.$size.' КБ'.PHP_EOL;
echo 'Папка: '.$assets.PHP_EOL;
