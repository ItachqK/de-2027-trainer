<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DrillAttempt extends Model
{
    protected $fillable = [
        'user_id',
        'drill_id',
        'code',
        'passed',
        'results',
    ];

    protected function casts(): array
    {
        return [
            'passed' => 'boolean',
            'results' => 'array',
        ];
    }

    public function drill(): BelongsTo
    {
        return $this->belongsTo(Drill::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
