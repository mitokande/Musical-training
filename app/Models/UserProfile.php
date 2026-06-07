<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'primary_instrument',
        'secondary_instruments',
        'musical_level',
        'education_status',
        'weekly_practice_hours',
        'learning_goals',
        'interests',
        'bio',
    ];

    protected function casts(): array
    {
        return [
            'secondary_instruments' => 'array',
            'learning_goals' => 'array',
            'interests' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
