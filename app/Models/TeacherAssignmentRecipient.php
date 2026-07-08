<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeacherAssignmentRecipient extends Model
{
    public const STATUS_ASSIGNED = 'assigned';

    public const STATUS_STARTED = 'started';

    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'teacher_assignment_id', 'student_id', 'teacher_class_id', 'status',
        'started_at', 'completed_at', 'best_score', 'attempts_count',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'best_score' => 'decimal:2',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(TeacherAssignment::class, 'teacher_assignment_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function teacherClass(): BelongsTo
    {
        return $this->belongsTo(TeacherClass::class, 'teacher_class_id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(TeacherAssignmentAttempt::class, 'recipient_id');
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isOverdue(): bool
    {
        return ! $this->isCompleted() && $this->assignment?->isOverdue();
    }

    public function canAttempt(): bool
    {
        $max = $this->assignment?->max_attempts;

        return $max === null || $this->attempts_count < $max;
    }

    public function scopeForStudent(Builder $query, int $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }
}
