<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherProfile extends Model
{
    protected $fillable = [
        'user_id', 'title', 'short_bio', 'long_bio', 'specializations',
        'teaching_subjects', 'education_background', 'experience_years',
        'hourly_rate', 'currency', 'lesson_format', 'website_url',
        'social_links', 'payment_link', 'location', 'languages',
        'accepts_students', 'max_students', 'public_profile',
    ];

    protected function casts(): array
    {
        return [
            'specializations' => 'array',
            'teaching_subjects' => 'array',
            'social_links' => 'array',
            'languages' => 'array',
            'accepts_students' => 'boolean',
            'public_profile' => 'boolean',
            'hourly_rate' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
