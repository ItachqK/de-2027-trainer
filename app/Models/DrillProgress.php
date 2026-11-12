<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DrillProgress extends Model
{
    protected $fillable = [
        'user_id',
        'drill_id',
        'attempts',
        'passes',
        'streak',
        'last_code',
        'last_passed_at',
        'next_review_at',
    ];

    protected function casts(): array
    {
        return [
            'last_passed_at' => 'datetime',
            'next_review_at' => 'datetime',
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
