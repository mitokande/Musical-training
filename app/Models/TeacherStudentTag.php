<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TeacherStudentTag extends Model
{
    protected $fillable = ['teacher_id', 'name', 'color'];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'teacher_student_tag_assignments', 'tag_id', 'student_id')
            ->withTimestamps();
    }
}
