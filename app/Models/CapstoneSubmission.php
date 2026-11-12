<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CapstoneSubmission extends Model
{
    protected $fillable = [
        'user_id',
        'project_path',
        'repo_url',
        'deploy_url',
        'commit_hash',
        'score',
        'max_score',
        'results',
        'runtime',
        'runtime_passed',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'results' => 'array',
            'runtime' => 'array',
            'runtime_passed' => 'boolean',
            'score' => 'integer',
            'max_score' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function percent(): int
    {
        return $this->max_score > 0 ? (int) round($this->score / $this->max_score * 100) : 0;
    }
}
