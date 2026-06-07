<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLearningPathProgress extends Model
{
    protected $fillable = [
        'user_id',
        'learning_path_exercise_id',
        'question_count_attempted',
        'total_questions',
        'correct_answers',
        'score',
        'completed',
        'completed_at',
    ];

    protected $casts = [
        'completed'    => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function exercise(): BelongsTo
    {
        return $this->belongsTo(LearningPathExercise::class, 'learning_path_exercise_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
