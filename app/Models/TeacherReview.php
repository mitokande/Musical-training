<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherReview extends Model
{
    public const STATUS_APPROVED = 'approved';

    public const STATUS_HIDDEN = 'hidden';

    public const STATUS_PENDING = 'pending';

    protected $fillable = [
        'teacher_profile_id', 'student_id', 'rating', 'body',
        'status', 'reported_at', 'report_reason',
    ];

    protected function casts(): array
    {
        return [
            'reported_at' => 'datetime',
        ];
    }

    public function teacherProfile(): BelongsTo
    {
        return $this->belongsTo(TeacherProfile::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }
}
