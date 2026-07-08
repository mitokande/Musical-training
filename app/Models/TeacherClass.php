<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TeacherClass extends Model
{
    protected $fillable = [
        'teacher_id', 'name', 'description', 'level', 'instrument_focus',
        'cover_path', 'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'archived_at' => 'datetime',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'teacher_class_students', 'teacher_class_id', 'student_id')
            ->withTimestamps();
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }
}
