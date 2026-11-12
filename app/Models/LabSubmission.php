<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabSubmission extends Model
{
    protected $fillable = [
        'user_id',
        'lab_id',
        'code',
        'status',
        'results',
        'attempts',
    ];

    protected function casts(): array
    {
        return [
            'results' => 'array',
            'attempts' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lab(): BelongsTo
    {
        return $this->belongsTo(Lab::class);
    }

    public function passed(): bool
    {
        return $this->status === 'passed';
    }
}
