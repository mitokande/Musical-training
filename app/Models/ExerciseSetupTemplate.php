<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExerciseSetupTemplate extends Model
{
    protected $fillable = [
        'user_id', 'name', 'category', 'exercise_type',
        'settings_json', 'is_ai_generated', 'is_favorite',
    ];

    protected $casts = [
        'settings_json' => 'array',
        'is_ai_generated' => 'boolean',
        'is_favorite' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
