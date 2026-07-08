<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherEducation extends Model
{
    protected $table = 'teacher_educations';

    protected $fillable = [
        'teacher_profile_id', 'institution', 'program', 'field_of_study',
        'graduation_year', 'sort_order',
    ];

    public function teacherProfile(): BelongsTo
    {
        return $this->belongsTo(TeacherProfile::class);
    }
}
