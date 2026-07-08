<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherBookingSetting extends Model
{
    protected $fillable = [
        'teacher_id', 'booking_enabled', 'lesson_duration_minutes',
        'buffer_minutes', 'advance_booking_days', 'min_notice_hours', 'timezone',
    ];

    protected function casts(): array
    {
        return [
            'booking_enabled' => 'boolean',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public static function forTeacher(int $teacherId): self
    {
        return static::firstOrCreate(['teacher_id' => $teacherId]);
    }
}
