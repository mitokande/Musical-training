<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The Zoom meeting backing a confirmed appointment. `active` rows occupy their
 * host's licence for the lesson window (plus a buffer) — that is what
 * ZoomHostAllocator scans. Cancelling a lesson flips the row to `cancelled`
 * and frees the host.
 */
class ZoomMeeting extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'appointment_id', 'zoom_host_id', 'zoom_meeting_id', 'zoom_meeting_uuid',
        'join_url', 'passcode', 'starts_at', 'ends_at', 'status',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(TeacherAppointment::class, 'appointment_id');
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(ZoomHost::class, 'zoom_host_id');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
}
