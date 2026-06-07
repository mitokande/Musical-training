<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExerciseSession extends Model
{
    protected $fillable = [
        'user_id', 'template_id', 'exercise_type', 'difficulty',
        'question_count', 'ai_mode', 'settings_json', 'score',
        'accuracy', 'duration_seconds', 'started_at', 'completed_at',
    ];

    protected $casts = [
        'settings_json' => 'array',
        'ai_mode' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function template()
    {
        return $this->belongsTo(ExerciseSetupTemplate::class, 'template_id');
    }
}
