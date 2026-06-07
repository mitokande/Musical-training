<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LearningPathExercise extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'title',
        'description',
        'translations',
        'level',
        'sort_order',
        'slug',
        'config_json',
        'tags',
        'skills_trained',
        'estimated_duration_minutes',
        'is_active',
    ];

    protected $casts = [
        'config_json'    => 'array',
        'tags'           => 'array',
        'skills_trained' => 'array',
        'translations'   => 'array',
        'is_active'      => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExerciseCategory::class, 'category_id');
    }

    public function userProgress(): HasMany
    {
        return $this->hasMany(UserLearningPathProgress::class, 'learning_path_exercise_id');
    }

    public function progressForUser(int $userId): ?UserLearningPathProgress
    {
        return $this->userProgress()->where('user_id', $userId)->first();
    }

    public function isPremiumVariant(int $questionCount): bool
    {
        return $questionCount > ($this->config_json['question_counts']['free'] ?? 5);
    }

    public function getLocalizedTitle(): string
    {
        $locale = app()->getLocale();
        return $this->translations[$locale]['title'] ?? $this->title;
    }

    public function getLocalizedDescription(): string
    {
        $locale = app()->getLocale();
        return $this->translations[$locale]['description'] ?? $this->description;
    }
}
