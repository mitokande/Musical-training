<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExerciseCategory extends Model
{
    protected $fillable = [
        'name', 'slug', 'parent_id', 'description', 'icon',
        'sort_order', 'is_active', 'is_premium',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_premium' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ExerciseCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ExerciseCategory::class, 'parent_id');
    }

    public function practices(): HasMany
    {
        return $this->hasMany(Practice::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function learningPathExercises(): HasMany
    {
        return $this->hasMany(LearningPathExercise::class, 'category_id')->orderBy('sort_order');
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
