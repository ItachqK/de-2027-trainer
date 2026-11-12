<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Drill extends Model
{
    protected $fillable = [
        'slug',
        'track',
        'title',
        'level',
        'minutes',
        'prompt',
        'requirements',
        'starter_code',
        'language',
        'checks',
        'solution',
        'position',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'requirements' => 'array',
            'checks' => 'array',
            'is_published' => 'boolean',
        ];
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(DrillAttempt::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(DrillProgress::class);
    }

    public function trackLabel(): string
    {
        return match ($this->track) {
            'html' => 'HTML',
            'css' => 'CSS',
            'bootstrap' => 'Bootstrap',
            'php' => 'PHP',
            'sql' => 'SQL',
            'laravel' => 'Laravel',
            default => $this->track,
        };
    }
}
