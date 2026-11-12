<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BuildStep extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'goal',
        'minutes',
        'commands',
        'files',
        'snippets',
        'checks',
        'position',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'commands' => 'array',
            'files' => 'array',
            'snippets' => 'array',
            'checks' => 'array',
            'is_published' => 'boolean',
        ];
    }

    public function progress(): HasMany
    {
        return $this->hasMany(BuildProgress::class);
    }
}
