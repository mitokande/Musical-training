<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionnaireQuestion extends Model
{
    protected $fillable = [
        'key',
        'question_text',
        'question_type',
        'options',
        'category',
        'sort_order',
        'is_required',
        'is_active',
        'translations',
    ];

    protected function casts(): array
    {
        return [
            'options'      => 'array',
            'translations' => 'array',
            'is_required'  => 'boolean',
            'is_active'    => 'boolean',
        ];
    }

    /**
     * Return question text in current locale, falling back to 'tr' then question_text.
     */
    public function getLocalizedText(): string
    {
        $locale = app()->getLocale();
        $translations = $this->translations ?? [];

        return $translations[$locale]['question_text']
            ?? $translations['en']['question_text']
            ?? $this->question_text;
    }

    /**
     * Return option labels translated into the current locale.
     * The array order matches $this->options so values (keys) stay consistent.
     */
    public function getLocalizedOptions(): ?array
    {
        if (! $this->options) {
            return null;
        }

        $locale = app()->getLocale();
        $translations = $this->translations ?? [];

        return $translations[$locale]['options']
            ?? $translations['en']['options']
            ?? $this->options;
    }

    public function responses(): HasMany
    {
        return $this->hasMany(QuestionnaireResponse::class, 'question_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
