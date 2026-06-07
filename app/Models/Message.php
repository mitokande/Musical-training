<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    protected $fillable = [
        'sender_id', 'receiver_id', 'subject', 'body', 'type',
        'priority', 'status', 'parent_id', 'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Message::class, 'parent_id');
    }

    public function scopeUnread($query)
    {
        return $query->where('status', 'unread');
    }

    public function scopeSupportTickets($query)
    {
        return $query->where('type', 'support_ticket');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('sender_id', $userId)->orWhere('receiver_id', $userId);
    }
}
