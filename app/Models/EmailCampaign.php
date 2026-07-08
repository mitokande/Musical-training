<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class EmailCampaign extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'subject', 'preheader', 'template_id', 'custom_html',
        'segment', 'status', 'scheduled_at', 'started_at', 'completed_at',
        'total_recipients', 'sent_count', 'delivered_count', 'opened_count',
        'clicked_count', 'bounced_count', 'complained_count', 'failed_count',
        'created_by',
    ];

    protected $casts = [
        'segment' => 'array',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public const STATUSES = ['draft', 'scheduled', 'sending', 'sent', 'cancelled', 'failed'];

    protected static function booted(): void
    {
        static::creating(function (self $campaign) {
            $campaign->slug = $campaign->slug ?: Str::slug($campaign->name).'-'.Str::lower(Str::random(6));
        });
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(EmailMessage::class, 'campaign_id');
    }

    public function htmlBody(): string
    {
        return $this->custom_html ?: ($this->template?->html_body ?? '');
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'scheduled']);
    }

    public function deliveryRate(): ?float
    {
        return $this->sent_count > 0 ? round($this->delivered_count / $this->sent_count * 100, 1) : null;
    }

    public function openRate(): ?float
    {
        return $this->delivered_count > 0 ? round($this->opened_count / $this->delivered_count * 100, 1) : null;
    }

    public function clickRate(): ?float
    {
        return $this->delivered_count > 0 ? round($this->clicked_count / $this->delivered_count * 100, 1) : null;
    }
}
