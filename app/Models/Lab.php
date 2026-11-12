<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lab extends Model
{
    protected $fillable = [
        'module_id',
        'lesson_slug',
        'slug',
        'title',
        'level',
        'minutes',
        'goal',
        'brief',
        'steps',
        'starter_code',
        'language',
        'hints',
        'solution',
        'checks',
        'position',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'steps' => 'array',
            'hints' => 'array',
            'checks' => 'array',
            'is_published' => 'boolean',
        ];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(LabSubmission::class);
    }

    public function toCourseArray(): array
    {
        return [
            'slug' => $this->slug,
            'title' => $this->title,
            'lesson_slug' => $this->lesson_slug,
            'minutes' => $this->minutes,
            'level' => $this->level,
        ];
    }
}
