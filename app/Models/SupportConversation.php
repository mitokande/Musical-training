<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportConversation extends Model
{
    protected $fillable = [
        'subject', 'subject_key', 'contact_email', 'contact_name', 'user_id',
        'status', 'assigned_admin_id', 'last_message_at', 'message_count',
    ];

    protected $casts = ['last_message_at' => 'datetime'];

    public const STATUSES = ['open', 'pending', 'closed'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_admin_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportMessage::class, 'conversation_id');
    }

    public static function normalizeSubject(string $subject): string
    {
        $key = preg_replace('/^\s*((re|fwd?|aw|yan[ıi]t)\s*:\s*)+/iu', '', trim($subject));

        return mb_strtolower(mb_substr($key ?: $subject, 0, 200));
    }
}
