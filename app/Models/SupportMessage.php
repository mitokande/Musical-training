<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportMessage extends Model
{
    protected $fillable = [
        'conversation_id', 'direction', 'message_id', 'in_reply_to', 'references',
        'from_name', 'from_email', 'to_email', 'subject', 'plain_text_body',
        'html_body_sanitized', 'attachment_metadata', 'sent_by_admin_id',
        'ses_message_id', 'received_at',
    ];

    protected $casts = [
        'attachment_metadata' => 'array',
        'received_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(SupportConversation::class, 'conversation_id');
    }

    public function sentByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by_admin_id');
    }
}
