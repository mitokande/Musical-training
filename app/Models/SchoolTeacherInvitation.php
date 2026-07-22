<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SchoolTeacherInvitation extends Model
{
    public const TYPE_EMAIL = 'email';

    public const TYPE_LINK = 'link';

    public const STATUS_PENDING = 'pending';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_REVOKED = 'revoked';

    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'school_id', 'type', 'email', 'name', 'token',
        'status', 'expires_at', 'accepted_by', 'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    public static function generateToken(): string
    {
        return Str::random(48);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(User::class, 'school_id');
    }

    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }

    public function isUsable(): bool
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        return $this->expires_at === null || $this->expires_at->isFuture();
    }

    public function acceptUrl(): string
    {
        return route('school-invitations.accept', $this->token);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }
}
