<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherPaymentLink extends Model
{
    public const VISIBILITY_PUBLIC = 'public';

    public const VISIBILITY_APPROVED_STUDENTS = 'approved_students';

    public const VISIBILITY_APPOINTMENT = 'appointment_confirmation';

    public const VISIBILITIES = [
        self::VISIBILITY_PUBLIC,
        self::VISIBILITY_APPROVED_STUDENTS,
        self::VISIBILITY_APPOINTMENT,
    ];

    protected $fillable = [
        'teacher_profile_id', 'label', 'url', 'description', 'price_text',
        'lesson_type', 'visibility', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function teacherProfile(): BelongsTo
    {
        return $this->belongsTo(TeacherProfile::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublic($query)
    {
        return $query->where('visibility', self::VISIBILITY_PUBLIC);
    }
}
