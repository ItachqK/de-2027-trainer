<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseStudy extends Model
{
    protected $fillable = [
        'slug',
        'project_variant_id',
        'title',
        'subtitle',
        'summary',
        'stack',
        'duration',
        'level',
        'chapters',
        'chronicle',
        'position',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'chapters' => 'array',
            'chronicle' => 'array',
            'is_published' => 'boolean',
        ];
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProjectVariant::class, 'project_variant_id');
    }
}
