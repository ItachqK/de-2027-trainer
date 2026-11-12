<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamSession extends Model
{
    public const DURATION_SECONDS = 3 * 60 * 60 + 30 * 60;

    protected $fillable = [
        'user_id',
        'project_variant_id',
        'started_at',
        'finished_at',
        'checklist',
        'notes',
        'score',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'checklist' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProjectVariant::class, 'project_variant_id');
    }

    public function isFinished(): bool
    {
        return $this->finished_at !== null;
    }

    public function remainingSeconds(): int
    {
        if ($this->isFinished()) {
            return 0;
        }

        return max(0, self::DURATION_SECONDS - (int) $this->started_at->diffInSeconds(now()));
    }

    public function scoreLabel(): string
    {
        return (int) ($this->score ?? 0).' / 75';
    }
}
