<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Immutable canonical question snapshot for a teacher assignment.
 *
 * question_data holds the serialized canonical question exactly as produced
 * by LearningPathQuestionGenerator::serializeForSession(). Once the parent
 * assignment is sent, rows must never be mutated — student playback, staff
 * rendering, audio, answer options, and evaluation all read this snapshot.
 */
class TeacherAssignmentQuestion extends Model
{
    protected $fillable = ['teacher_assignment_id', 'position', 'question_data'];

    protected function casts(): array
    {
        return [
            'question_data' => 'array',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(TeacherAssignment::class, 'teacher_assignment_id');
    }
}
