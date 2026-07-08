<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherStudentRelationship extends Model
{
    public const STATUS_PENDING_TEACHER_REQUEST = 'pending_teacher_request';

    public const STATUS_PENDING_STUDENT_APPROVAL = 'pending_student_approval';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_DECLINED = 'declined';

    public const STATUS_REVOKED_BY_TEACHER = 'revoked_by_teacher';

    public const STATUS_REVOKED_BY_STUDENT = 'revoked_by_student';

    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'teacher_id', 'student_id', 'status', 'approved_at', 'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'revoked_at' => 'datetime',
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

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
}
