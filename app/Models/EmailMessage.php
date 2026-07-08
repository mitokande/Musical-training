<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class EmailMessage extends Model
{
    protected $fillable = [
        'tracking_token', 'user_id', 'recipient_email', 'campaign_id',
        'automation_id', 'template_id', 'email_type', 'subject',
        'ses_message_id', 'status', 'sent_at', 'delivered_at', 'opened_at',
        'clicked_at', 'error', 'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
    ];

    // email types that respect marketing suppression + frequency cap
    public const MARKETING_TYPES = ['campaign', 'automation'];

    protected static function booted(): void
    {
        static::creating(function (self $message) {
            $message->tracking_token = $message->tracking_token ?: (string) Str::uuid();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'campaign_id');
    }

    public function automation(): BelongsTo
    {
        return $this->belongsTo(EmailAutomation::class, 'automation_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(EmailEvent::class, 'email_message_id');
    }

    public function isMarketing(): bool
    {
        return in_array($this->email_type, self::MARKETING_TYPES);
    }
}
