<?php

namespace App\Services;

use App\Models\Lab;
use App\Support\LabFixtures;
use PDO;
use Throwable;

class LabChecker
{
    /**
     * @return array{passed: bool, results: array<int, array{label: string, passed: bool, message: string}>}
     */
    public function check(Lab $lab, string $code): array
    {
        return $this->checkRules($lab->checks ?? [], $code);
    }

    /**
     * @param  array<string, mixed>  $rules
     * @return array{passed: bool, results: array<int, array{label: string, passed: bool, message: string}>}
     */
    public function checkRules(array $rules, string $code): array
    {
        $results = [];

        if (($rules['php_lint'] ?? false) === true) {
            $results[] = $this->lintPhp($code);
        }

        foreach (($rules['checks'] ?? []) as $check) {
            $results[] = $this->runCheck($check, $code);
        }

        if (isset($rules['sql']) && is_array($rules['sql'])) {
            $results[] = $this->runSql($rules['sql'], $code);
        }

        $passed = $results !== [] && collect($results)->every(fn (array $result) => $result['passed']);

        return ['passed' => $passed, 'results' => $results];
    }

    private function lintPhp(string $code): array
    {
        if (! function_exists('exec')) {
            return $this->result('PHP-синтаксис', false, 'Функция exec отключена на сервере.');
        }

        $source = str_contains($code, '<?php') ? $code : "<?php\n".$code;

        // Фрагмент метода нельзя проверить вне класса — оборачиваем.
        if (! str_contains($code, '<?php') && preg_match('/^\s*(public|private|protected)\s+function/m', $code)) {
            $source = "<?php\nclass LabCheck {\n".$code."\n}";
        }
        $path = tempnam(storage_path('app'), 'lab_lint_');

        if ($path === false) {
            return $this->result('PHP-синтаксис', false, 'Не удалось создать временный файл.');
        }

        $phpFile = $path.'.php';
        file_put_contents($phpFile, $source);

        $output = [];
        $status = 0;
        exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($phpFile).' 2>&1', $output, $status);

        @unlink($phpFile);
        @unlink($path);

        return $status === 0
            ? $this->result('PHP-синтаксис', true, 'Синтаксис в порядке.')
            : $this->result('PHP-синтаксис', false, 'PHP нашёл ошибку: '.mb_substr(implode(' ', $output), 0, 240));
    }

    private function runCheck(array $check, string $code): array
    {
        $label = $check['label'] ?? 'Проверка';
        $type = $check['type'] ?? 'contains';

        if ($type === 'contains') {
            $passed = mb_stripos($code, (string) ($check['value'] ?? '')) !== false;

            return $this->result($label, $passed, $passed ? 'Критерий выполнен.' : ($check['fail'] ?? 'Не найдено: '.($check['value'] ?? '')));
        }

        if ($type === 'not_contains') {
            $passed = mb_stripos($code, (string) ($check['value'] ?? '')) === false;

            return $this->result($label, $passed, $passed ? 'Запрещённой конструкции нет.' : ($check['fail'] ?? 'Уберите: '.($check['value'] ?? '')));
        }

        if ($type === 'regex') {
            return $this->regex($label, (string) ($check['pattern'] ?? ''), $code, $check['fail'] ?? null);
        }

        if ($type === 'min_length') {
            $passed = mb_strlen(trim($code)) >= (int) ($check['value'] ?? 0);

            return $this->result($label, $passed, $passed ? 'Объём достаточный.' : ($check['fail'] ?? 'Слишком короткий ответ.'));
        }

        return $this->result($label, false, 'Неизвестный тип проверки.');
    }

    private function regex(string $label, string $pattern, string $code, ?string $fail): array
    {
        $matched = @preg_match($pattern, $code);

        if ($matched === false) {
            return $this->result($label, false, 'Некорректное правило проверки.');
        }

        return $this->result($label, $matched === 1, $matched === 1 ? 'Шаблон найден.' : ($fail ?? 'Шаблон не найден.'));
    }

    private function runSql(array $rule, string $code): array
    {
        $query = trim($code);
        $label = $rule['label'] ?? 'SQL-запрос';

        if (! preg_match('/^(select|with)\b/i', $query)) {
            return $this->result($label, false, 'Нужен SELECT-запрос.');
        }

        if (str_contains(rtrim($query, ';'), ';')) {
            return $this->result($label, false, 'Отправьте один запрос без нескольких инструкций.');
        }

        try {
            $pdo = new PDO('sqlite::memory:');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->exec(LabFixtures::sql($rule['fixture'] ?? 'conference'));
            $pdo->exec('PRAGMA query_only = ON');

            $statement = $pdo->query($query);
            $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return $this->result($label, false, 'SQL не выполнился: '.mb_substr($e->getMessage(), 0, 200));
        }

        $columns = $rule['columns'] ?? [];
        $expected = $rule['rows'] ?? [];

        if ($columns !== []) {
            $actualColumns = $rows === [] ? [] : array_keys($rows[0]);

            if ($actualColumns !== $columns) {
                return $this->result(
                    $label,
                    false,
                    'Колонки не совпадают. Ожидались: '.implode(', ', $columns).'. Получены: '.($actualColumns === [] ? 'ничего' : implode(', ', $actualColumns)).'.'
                );
            }
        }

        $normalized = array_map(function (array $row) use ($columns) {
            return array_map(fn ($column) => (string) ($row[$column] ?? ''), $columns);
        }, $rows);

        $expectedNormalized = array_map(fn (array $row) => array_map('strval', $row), $expected);

        if (! ($rule['ordered'] ?? false)) {
            usort($normalized, fn ($a, $b) => strcmp(json_encode($a), json_encode($b)));
            usort($expectedNormalized, fn ($a, $b) => strcmp(json_encode($a), json_encode($b)));
        }

        if ($normalized !== $expectedNormalized) {
            return $this->result(
                $label,
                false,
                'Запрос вернул не те строки. Строк получено: '.count($normalized).', ожидалось: '.count($expectedNormalized).'. Проверьте JOIN, WHERE и сортировку.'
            );
        }

        return $this->result($label, true, 'Запрос вернул нужные строки.');
    }

    private function result(string $label, bool $passed, string $message): array
    {
        return ['label' => $label, 'passed' => $passed, 'message' => $message];
    }
}
