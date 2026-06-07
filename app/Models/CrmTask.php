<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmTask extends Model
{
    protected $fillable = [
        'admin_id', 'user_id', 'title', 'description', 'type',
        'priority', 'status', 'due_date', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'completed')
            ->where('due_date', '<', now());
    }
}
