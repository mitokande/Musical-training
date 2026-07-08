<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherService extends Model
{
    protected $fillable = [
        'teacher_profile_id', 'title', 'description', 'lesson_type', 'format',
        'duration_minutes', 'price_text', 'is_active', 'sort_order',
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
}
