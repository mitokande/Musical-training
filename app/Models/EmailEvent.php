<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailEvent extends Model
{
    protected $fillable = [
        'email_message_id', 'ses_message_id', 'event_type', 'recipient_email',
        'source', 'occurred_at', 'metadata', 'dedup_hash',
    ];

    protected $casts = [
        'metadata' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(EmailMessage::class, 'email_message_id');
    }
}
