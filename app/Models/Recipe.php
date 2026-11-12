<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'when_text',
        'tags',
        'code',
        'lang',
        'file',
        'after_text',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
        ];
    }

    public function toCourseArray(): array
    {
        return [
            'id' => $this->slug,
            'title' => $this->title,
            'when' => $this->when_text,
            'tags' => $this->tags ?? [],
            'code' => $this->code,
            'lang' => $this->lang,
            'file' => $this->file,
            'after' => $this->after_text,
        ];
    }
}
