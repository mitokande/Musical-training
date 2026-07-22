<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolTeacherRelationship extends Model
{
    public const STATUS_PENDING_TEACHER_APPROVAL = 'pending_teacher_approval';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_DECLINED = 'declined';

    public const STATUS_REVOKED_BY_SCHOOL = 'revoked_by_school';

    public const STATUS_REVOKED_BY_TEACHER = 'revoked_by_teacher';

    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'school_id', 'teacher_id', 'status', 'approved_at', 'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(User::class, 'school_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
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
