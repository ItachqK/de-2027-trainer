<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuildProgress extends Model
{
    protected $fillable = [
        'user_id',
        'build_step_id',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(BuildStep::class, 'build_step_id');
    }
}
