<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeacherAppointment extends Model
{
    public const STATUS_PENDING = 'pending_teacher_approval';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_CANCELLED_BY_TEACHER = 'cancelled_by_teacher';

    public const STATUS_CANCELLED_BY_STUDENT = 'cancelled_by_student';

    public const STATUS_RESCHEDULE_REQUESTED = 'reschedule_requested';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_NO_SHOW = 'no_show';

    /** Statuses that occupy a slot in the calendar. */
    public const BLOCKING_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_RESCHEDULE_REQUESTED,
    ];

    protected $fillable = [
        'teacher_id', 'student_id', 'starts_at', 'ends_at', 'status', 'topic',
        'teacher_note', 'meeting_provider', 'meeting_url',
        'requested_starts_at', 'requested_ends_at', 'timezone',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'requested_starts_at' => 'datetime',
            'requested_ends_at' => 'datetime',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(TeacherAppointmentActivity::class, 'appointment_id');
    }

    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function involves(User $user): bool
    {
        return in_array($user->id, [$this->teacher_id, $this->student_id], true);
    }

    public function scopeBlocking(Builder $query): Builder
    {
        return $query->whereIn('status', self::BLOCKING_STATUSES);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('starts_at', '>=', now());
    }
}
