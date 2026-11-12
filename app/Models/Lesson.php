<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lesson extends Model
{
    protected $fillable = [
        'module_id',
        'slug',
        'title',
        'minutes',
        'level',
        'tags',
        'goal',
        'blocks',
        'practice',
        'quiz',
        'position',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'blocks' => 'array',
            'practice' => 'array',
            'quiz' => 'array',
            'is_published' => 'boolean',
        ];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function toCourseArray(): array
    {
        return [
            'id' => $this->slug,
            'title' => $this->title,
            'minutes' => $this->minutes,
            'level' => $this->level,
            'tags' => $this->tags ?? [],
            'goal' => $this->goal,
            'blocks' => $this->blocks ?? [],
            'practice' => $this->practice ?? [],
            'quiz' => $this->quiz ?? [],
        ];
    }
}
