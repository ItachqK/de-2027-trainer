<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectVariant extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'theme',
        'summary',
        'minutes',
        'level',
        'entities',
        'statuses',
        'routes',
        'requirements',
        'admin_login',
        'admin_password',
        'position',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'entities' => 'array',
            'statuses' => 'array',
            'routes' => 'array',
            'requirements' => 'array',
            'is_published' => 'boolean',
        ];
    }

    public function toMarkdown(): string
    {
        $lines = [];
        $lines[] = '# '.$this->title;
        $lines[] = '';
        $lines[] = '**Тема:** '.$this->theme;
        $lines[] = '';
        $lines[] = $this->summary;
        $lines[] = '';
        $lines[] = '## Сущности и поля';
        $lines[] = '';

        foreach ($this->entities as $entity) {
            $lines[] = '### '.$entity['title'].' (`'.$entity['name'].'`)';
            $lines[] = '';
            $lines[] = '| Поле | Тип | Примечание |';
            $lines[] = '|---|---|---|';

            foreach ($entity['fields'] as $field) {
                $lines[] = '| `'.$field['name'].'` | '.$field['type'].' | '.($field['note'] ?? '').' |';
            }

            $lines[] = '';
        }

        $lines[] = '## Статусы';
        $lines[] = '';

        foreach ($this->statuses as $status) {
            $lines[] = '- `'.$status['key'].'` — '.$status['label'].' (меняет: '.$status['who'].')';
        }

        $lines[] = '';
        $lines[] = '## Маршруты';
        $lines[] = '';

        foreach ($this->routes as $route) {
            $lines[] = '- `'.$route['method'].' '.$route['uri'].'` — '.$route['action'];
        }

        $lines[] = '';
        $lines[] = '## Требования';
        $lines[] = '';

        foreach ($this->requirements as $requirement) {
            $lines[] = '- '.$requirement;
        }

        $lines[] = '';
        $lines[] = '## Администратор';
        $lines[] = '';
        $lines[] = '- Логин: `'.$this->admin_login.'`';
        $lines[] = '- Пароль: `'.$this->admin_password.'`';
        $lines[] = '';
        $lines[] = '## Сдача';
        $lines[] = '';
        $lines[] = '- Репозиторий с коммитами.';
        $lines[] = '- Работающий сценарий: регистрация → вход → заявка → кабинет → админка → статус.';
        $lines[] = '- Мобильный макет 390×844 без горизонтальной прокрутки.';
        $lines[] = '- `php artisan migrate:fresh --seed` на чистой базе.';

        return implode("\n", $lines)."\n";
    }
}
