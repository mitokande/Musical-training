<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiCoachingSession extends Model
{
    protected $fillable = [
        'user_id', 'session_data', 'model_used', 'tokens_used',
    ];

    protected function casts(): array
    {
        return [
            'session_data' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
