<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherAssignmentAttempt extends Model
{
    protected $fillable = [
        'recipient_id', 'attempt_number', 'answers', 'correct_count',
        'question_count', 'score', 'duration_seconds', 'started_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'answers' => 'array',
            'score' => 'decimal:2',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(TeacherAssignmentRecipient::class, 'recipient_id');
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }
}
