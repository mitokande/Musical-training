<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeacherAssignment extends Model
{
    use SoftDeletes;

    public const TYPE_EXERCISE = 'exercise';

    public const TYPE_LEARNING_PATH = 'learning_path';

    public const TYPE_AI_GENERATED = 'ai_generated';

    public const TYPE_PRACTICE_GOAL = 'practice_goal';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_SENT = 'sent';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'teacher_id', 'title', 'description', 'instructions', 'type',
        'practice_type', 'learning_path_exercise_id', 'config_json',
        'ai_prompt', 'question_count', 'difficulty', 'starts_at', 'due_at',
        'max_attempts', 'daily_practice_minutes', 'weekly_practice_minutes',
        'attachment_path', 'attachment_name', 'reward_label', 'status', 'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'config_json' => 'array',
            'starts_at' => 'datetime',
            'due_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function learningPathExercise(): BelongsTo
    {
        return $this->belongsTo(LearningPathExercise::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(TeacherAssignmentQuestion::class)->orderBy('position');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(TeacherAssignmentRecipient::class);
    }

    /** Files attached from the teacher's media library (e.g. practice goals). */
    public function media(): BelongsToMany
    {
        return $this->belongsToMany(TeacherMedia::class, 'teacher_assignment_media')->withTimestamps();
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isSent(): bool
    {
        return in_array($this->status, [self::STATUS_SENT, self::STATUS_COMPLETED], true);
    }

    /** Questions are editable only while the assignment is an unsent draft. */
    public function questionsLocked(): bool
    {
        return ! in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SCHEDULED], true);
    }

    public function isOverdue(): bool
    {
        return $this->due_at !== null && $this->due_at->isPast();
    }

    public function scopeForTeacher(Builder $query, int $teacherId): Builder
    {
        return $query->where('teacher_id', $teacherId);
    }
}
