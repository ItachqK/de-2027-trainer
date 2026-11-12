<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Project extends Model
{
    public const STAGES = [
        'database' => 'База данных и ER-диаграмма',
        'auth' => 'Регистрация, вход и роли',
        'applications' => 'Заявки и статусы',
        'admin' => 'Админка, фильтры и поиск',
        'mobile' => 'Мобильный макет 390×844',
        'design' => 'Стиль, изображения и адаптив',
        'quality' => 'Доступность и качество кода',
        'git' => 'Репозиторий, коммиты и README',
    ];

    protected $fillable = [
        'user_id',
        'repo_url',
        'deploy_url',
        'commit_hash',
        'variant_slug',
        'notes',
        'stages',
    ];

    protected function casts(): array
    {
        return [
            'stages' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function completedStages(): int
    {
        $stages = $this->stages ?? [];

        return count(array_filter($stages));
    }
}
