<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeacherSubscriptionBenefit extends Model
{
    public const TYPE_DISCOUNT = 'discount';

    public const TYPE_FREE_PERIOD = 'free_period';

    public const STATUS_ACTIVE = 'active';

    // School free-period grants wait for an admin approval from the
    // Payments → Premium Incentives screen before becoming active.
    public const STATUS_PENDING = 'pending_approval';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_REVOKED = 'revoked';

    public const STATUS_SUPERSEDED = 'superseded';

    protected $fillable = [
        'user_id', 'type', 'discount_percentage', 'qualifying_student_count',
        'status', 'source', 'starts_at', 'ends_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(TeacherSubscriptionBenefitHistory::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && (! $this->ends_at || $this->ends_at->isFuture());
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()));
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }
}
