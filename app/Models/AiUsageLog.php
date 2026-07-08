<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsageLog extends Model
{
    protected $fillable = [
        'user_id',
        'feature',
        'model',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'cost_usd',
        'status',
        'error_message',
        'duration_ms',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'cost_usd' => 'decimal:6',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
