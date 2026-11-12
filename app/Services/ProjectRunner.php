<?php

namespace App\Services;

use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use PDO;
use Symfony\Component\Process\Process;
use Throwable;

class ProjectRunner
{
    /**
     * Запускает проект на временной SQLite, прогоняет миграции, тесты и HTTP-сценарий.
     * Код проекта исполняется в его папке; база подменяется на временную.
     */
    public function run(string $dir): array
    {
        @set_time_limit(300);

        $started = microtime(true);
        $runDir = storage_path('app/capstone/run-'.Str::uuid());
        File::ensureDirectoryExists($runDir);
        $dbPath = $runDir.'/database.sqlite';
        touch($dbPath);

        $steps = [];
        $outputs = [];
        $server = null;

        $step = function (string $label, bool $passed, string $message, bool $skipped = false) use (&$steps) {
            $steps[] = compact('label', 'passed', 'message', 'skipped');
        };

        try {
            if (! is_file($dir.'/artisan') || ! is_file($dir.'/vendor/autoload.php')) {
                $step('Запуск проекта', false, 'Нет artisan или vendor/autoload.php. Установите зависимости.');

                return $this->result(false, $steps, $outputs, $started);
            }

            $env = $this->environment($dir, $dbPath);
            $testEnv = array_merge($env, [
                'APP_ENV' => 'testing',
                'DB_DATABASE' => ':memory:',
                'SESSION_DRIVER' => 'array',
                'CACHE_STORE' => 'array',
            ]);

            $guard = $runDir.'/guard.php';
            File::put($guard, '<?php require '.var_export($dir.'/vendor/autoload.php', true)
                .'; $app = require '.var_export($dir.'/bootstrap/app.php', true)
                .'; $app->make(\\Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap(); echo config("database.default")."|".config("database.connections.sqlite.database");');

            $check = $this->runProcess([PHP_BINARY, $guard], $dir, $env, 30);
            $dbIsTemp = str_contains($check['output'], $dbPath);

            $step('Временная база', $dbIsTemp, $dbIsTemp
                ? 'Проект переключён на временную SQLite: реальная база не затрагивается.'
                : 'Не удалось переключить БД на временную. Запуск остановлен, чтобы не испортить данные.');

            if (! $dbIsTemp) {
                return $this->result(false, $steps, $outputs, $started);
            }

            $migrate = $this->runProcess([PHP_BINARY, 'artisan', 'migrate:fresh', '--seed', '--force'], $dir, $env, 180);
            $outputs['migrate'] = $this->limit($migrate['output']);
            $step('Миграции и сидеры', $migrate['ok'], $migrate['ok']
                ? 'migrate:fresh --seed выполнен без ошибок.'
                : 'Миграции/сидеры упали: '.$this->firstError($migrate['output']));

            $tests = $this->runProcess([PHP_BINARY, 'artisan', 'test'], $dir, $testEnv, 240);
            $outputs['tests'] = $this->limit($tests['output']);
            $step('Тесты проекта', $tests['ok'], $tests['ok']
                ? 'php artisan test прошёл.'
                : 'Тесты упали или не запустились: '.$this->firstError($tests['output']));

            // Тесты могли очистить временную базу (RefreshDatabase) — готовим её заново.
            $reseed = $this->runProcess([PHP_BINARY, 'artisan', 'migrate:fresh', '--seed', '--force'], $dir, $env, 180);
            $outputs['migrate_after_tests'] = $this->limit($reseed['output']);
            $step('База после тестов', $reseed['ok'], $reseed['ok']
                ? 'Временная база пересоздана и заполнена перед сценарием.'
                : 'Не удалось пересоздать базу после тестов: '.$this->firstError($reseed['output']));

            $port = $this->freePort();
            $base = 'http://127.0.0.1:'.$port;

            $server = new Process([PHP_BINARY, 'artisan', 'serve', '--host=127.0.0.1', '--port='.$port], $dir, $env, null, null);
            $server->disableOutput();
            $server->start();

            $ready = $this->waitForServer($base);
            $step('Сервер', $ready, $ready
                ? 'Проект отвечает на '.$base
                : 'Сервер не поднялся за 20 секунд.');

            if ($ready) {
                $outputs['scenario'] = $this->scenario($base, $dbPath, $dir, $step);
            }
        } catch (Throwable $e) {
            $step('Запуск проекта', false, 'Ошибка запуска: '.mb_substr($e->getMessage(), 0, 300));
        } finally {
            if ($server) {
                $this->stopServer($server);
            }

            File::deleteDirectory($runDir);
        }

        $passed = collect($steps)->every(fn (array $step) => $step['passed'] || $step['skipped']);

        return $this->result($passed, $steps, $outputs, $started);
    }

    private function scenario(string $base, string $dbPath, string $dir, callable $step): array
    {
        $jar = new CookieJar();
        $login = 'check'.Str::lower(Str::random(6));
        $password = 'Password2027!';
        $email = $login.'@example.test';
        $fio = 'Тестовый Проверяющий';

        $get = function (string $path) use ($base, $jar) {
            try {
                $response = Http::withOptions(['cookies' => $jar])->timeout(10)->get($base.$path);

                return ['status' => $response->status(), 'body' => $response->body()];
            } catch (Throwable $e) {
                return ['status' => 0, 'body' => ''];
            }
        };

        $post = function (string $path, array $data) use ($base, $jar) {
            try {
                $response = Http::withOptions(['cookies' => $jar])->asForm()->timeout(15)->post($base.$path, $data);

                return ['status' => $response->status(), 'body' => $response->body()];
            } catch (Throwable $e) {
                return ['status' => 0, 'body' => ''];
            }
        };

        $register = $get('/register');

        if ($register['status'] !== 200) {
            $step('Регистрация', false, 'Страница /register не открылась ('.$register['status'].').');
        } else {
            $token = $this->token($register['body']);
            $response = $token ? $post('/register', [
                '_token' => $token,
                'fio' => $fio,
                'login' => $login,
                'phone' => '8(999)123-45-67',
                'email' => $email,
                'password' => $password,
            ]) : ['status' => 0, 'body' => ''];

            $created = $this->userExists($dbPath, $login);
            $step('Регистрация', $created, $created
                ? 'Пользователь создан и записан в базу.'
                : 'Не удалось зарегистрировать пользователя ('.$response['status'].').');
        }

        $loginPage = $get('/login');

        if ($loginPage['status'] !== 200) {
            $step('Вход', false, 'Страница /login не открылась.');
        } else {
            $token = $this->token($loginPage['body']);
            $response = $token ? $post('/login', [
                '_token' => $token,
                'login' => $login,
                'password' => $password,
            ]) : ['status' => 0, 'body' => ''];

            $profile = $get('/profile');
            $logged = $profile['status'] === 200
                && ! $this->looksLikeLogin($profile['body']);

            $step('Вход', $logged, $logged
                ? 'Вход выполнен, кабинет открывается.'
                : 'Вход не подтвердился ('.$response['status'].').');
        }

        $application = $get('/application');

        if ($application['status'] !== 200) {
            $step('Заявка', false, 'Страница /application не открылась.');
        } else {
            $token = $this->token($application['body']);
            $data = ['_token' => $token, 'start_date' => now()->addDays(5)->toDateString(), 'payment_type' => 'in_person'];

            if (str_contains($application['body'], 'name="room_id"')) {
                $roomId = $this->firstRoomId($application['body'], $dbPath);
                $data['room_id'] = $roomId;
            } else {
                $data['room_name'] = $this->firstRoomName($dbPath);
            }

            $response = $token ? $post('/application', $data) : ['status' => 0, 'body' => ''];
            $created = $this->applicationExists($dbPath, $login);

            $step('Заявка', $created, $created
                ? 'Заявка создана и сохранена в базе.'
                : 'Заявка не сохранилась ('.$response['status'].'). '.$this->formError($response['body']));

            $profile = $get('/profile');
            $step('Кабинет', $profile['status'] === 200 && mb_stripos($profile['body'], 'заявк') !== false,
                $profile['status'] === 200 ? 'Кабинет открывается после создания заявки.' : 'Кабинет не открылся.');
        }

        $adminAsStudent = $get('/admin');
        $protected = $adminAsStudent['status'] === 403
            || $adminAsStudent['status'] === 302
            || $this->looksLikeLogin($adminAsStudent['body']);
        $step('Защита админки', $protected, $protected
            ? 'Студент не получает админку.'
            : 'Студент открыл /admin — нет проверки роли.');

        [$adminLogin, $adminPassword] = $this->adminCredentials($dir);

        if (! $adminLogin || ! $adminPassword) {
            $step('Вход администратора', true, 'Логин администратора не найден в сидерах — шаг пропущен.', true);
        } else {
            $adminJar = new CookieJar();
            $adminGet = function (string $path) use ($base, $adminJar) {
                try {
                    $r = Http::withOptions(['cookies' => $adminJar])->timeout(10)->get($base.$path);

                    return ['status' => $r->status(), 'body' => $r->body()];
                } catch (Throwable $e) {
                    return ['status' => 0, 'body' => ''];
                }
            };
            $adminPost = function (string $path, array $data) use ($base, $adminJar) {
                try {
                    $r = Http::withOptions(['cookies' => $adminJar])->asForm()->timeout(15)->post($base.$path, $data);

                    return ['status' => $r->status(), 'body' => $r->body()];
                } catch (Throwable $e) {
                    return ['status' => 0, 'body' => ''];
                }
            };

            $page = $adminGet('/admin/login');
            $path = '/admin/login';

            if ($page['status'] !== 200) {
                $page = $adminGet('/login');
                $path = '/login';
            }

            $token = $page['status'] === 200 ? $this->token($page['body']) : null;
            $response = $token ? $adminPost($path, [
                '_token' => $token,
                'login' => $adminLogin,
                'password' => $adminPassword,
            ]) : ['status' => 0, 'body' => ''];

            $admin = $adminGet('/admin');
            $ok = $admin['status'] === 200
                && (mb_stripos($admin['body'], 'заявк') !== false || mb_stripos($admin['body'], 'статус') !== false);

            $step('Вход администратора', $ok, $ok
                ? 'Администратор вошёл и видит заявки.'
                : 'Админский вход не удался ('.$response['status'].').');
        }

        return ['login' => $login, 'email' => $email];
    }

    private function environment(string $dir, string $dbPath): array
    {
        $key = $this->appKey($dir);

        return [
            'APP_ENV' => 'local',
            'APP_DEBUG' => 'true',
            'APP_KEY' => $key,
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => $dbPath,
            'SESSION_DRIVER' => 'cookie',
            'CACHE_STORE' => 'array',
            'QUEUE_CONNECTION' => 'sync',
            'MAIL_MAILER' => 'log',
            'LOG_CHANNEL' => 'single',
        ];
    }

    private function appKey(string $dir): string
    {
        $env = @file_get_contents($dir.'/.env') ?: '';
        if (preg_match('/^APP_KEY=(.+)$/m', $env, $m)) {
            $key = trim($m[1], " \t\"'");
            if ($key !== '') {
                return $key;
            }
        }

        return 'base64:'.base64_encode(random_bytes(32));
    }

    private function runProcess(array $command, string $cwd, array $env, int $timeout): array
    {
        $process = new Process($command, $cwd, $env, null, $timeout);
        $process->run();

        return [
            'ok' => $process->isSuccessful(),
            'output' => trim($process->getOutput()."\n".$process->getErrorOutput()),
        ];
    }

    private function waitForServer(string $base): bool
    {
        for ($i = 0; $i < 40; $i++) {
            try {
                $response = Http::timeout(1)->get($base.'/');
                if ($response->status() > 0) {
                    return true;
                }
            } catch (Throwable $e) {
                // сервер ещё стартует
            }
            usleep(500000);
        }

        return false;
    }

    private function stopServer(Process $server): void
    {
        if (! $server->isRunning()) {
            return;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $pid = (int) $server->getPid();
            if ($pid > 0) {
                Process::fromShellCommandline('taskkill /PID '.$pid.' /T /F')->run();
            }
        } else {
            $server->stop(3);
        }
    }

    private function freePort(): int
    {
        $socket = stream_socket_server('tcp://127.0.0.1:0');
        $name = stream_socket_get_name($socket, false);
        fclose($socket);

        return (int) substr($name, strrpos($name, ':') + 1);
    }

    private function token(string $html): ?string
    {
        if (preg_match('/name="_token"\s+value="([^"]+)"/', $html, $m)) {
            return html_entity_decode($m[1], ENT_QUOTES);
        }

        if (preg_match('/value="([^"]+)"\s+name="_token"/', $html, $m)) {
            return html_entity_decode($m[1], ENT_QUOTES);
        }

        return null;
    }

    private function looksLikeLogin(string $html): bool
    {
        return mb_stripos($html, 'Неверный логин') !== false
            || (mb_stripos($html, 'Войти') !== false && mb_stripos($html, 'заявк') === false);
    }

    private function userExists(string $dbPath, string $login): bool
    {
        try {
            $pdo = new PDO('sqlite:'.$dbPath);
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE login = ?');
            $stmt->execute([$login]);

            return (int) $stmt->fetchColumn() > 0;
        } catch (Throwable $e) {
            return false;
        }
    }

    private function applicationExists(string $dbPath, string $login): bool
    {
        try {
            $pdo = new PDO('sqlite:'.$dbPath);
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM applications a JOIN users u ON u.id = a.user_id WHERE u.login = ?');
            $stmt->execute([$login]);

            return (int) $stmt->fetchColumn() > 0;
        } catch (Throwable $e) {
            return false;
        }
    }

    private function firstRoomId(string $html, string $dbPath): string
    {
        if (preg_match_all('/<option[^>]*value="(\d+)"[^>]*>/', $html, $m)) {
            foreach ($m[1] as $value) {
                if ((int) $value > 0) {
                    return $value;
                }
            }
        }

        try {
            $pdo = new PDO('sqlite:'.$dbPath);
            $id = $pdo->query('SELECT id FROM rooms ORDER BY id LIMIT 1')->fetchColumn();

            return $id ? (string) $id : '1';
        } catch (Throwable $e) {
            return '1';
        }
    }

    private function firstRoomName(string $dbPath): string
    {
        try {
            $pdo = new PDO('sqlite:'.$dbPath);
            $name = $pdo->query('SELECT name FROM rooms ORDER BY id LIMIT 1')->fetchColumn();

            return $name ?: 'Аудитория';
        } catch (Throwable $e) {
            return 'Аудитория';
        }
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    private function adminCredentials(string $dir): array
    {
        $content = '';

        foreach (glob($dir.'/database/seeders/*.php') ?: [] as $file) {
            $content .= "\n".@file_get_contents($file);
        }

        preg_match_all('/[\'"]login[\'"]\s*=>\s*[\'"]([^\'"]+)[\'"]/', $content, $logins);
        preg_match_all('/(?:Hash::make|password_hash)\(\s*[\'"]([^\'"]+)[\'"]/', $content, $passwords);

        return [$logins[1][0] ?? null, $passwords[1][0] ?? null];
    }

    private function firstError(string $output): string
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($output));

        foreach ($lines as $line) {
            if (preg_match('/(error|exception|failed|fatal)/i', $line)) {
                return mb_substr(trim($line), 0, 240);
            }
        }

        return mb_substr(trim($output), 0, 240) ?: 'без вывода';
    }

    private function formError(string $html): string
    {
        if (preg_match('/class="invalid-feedback[^"]*"[^>]*>(.*?)<\/div>/s', $html, $m)) {
            return mb_substr(trim(strip_tags($m[1])), 0, 200);
        }

        if (preg_match('/class="alert alert-danger[^"]*"[^>]*>(.*?)<\/div>/s', $html, $m)) {
            return mb_substr(trim(strip_tags($m[1])), 0, 200);
        }

        return '';
    }

    private function limit(string $output): string
    {
        return mb_substr($output, 0, 4000);
    }

    private function result(bool $passed, array $steps, array $outputs, float $started): array
    {
        return [
            'ran' => true,
            'passed' => $passed,
            'steps' => $steps,
            'outputs' => $outputs,
            'duration' => round(microtime(true) - $started, 1),
        ];
    }
}
