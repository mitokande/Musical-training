<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherAvailabilityException extends Model
{
    protected $fillable = ['teacher_id', 'date', 'start_time', 'end_time', 'is_blocked', 'note'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_blocked' => 'boolean',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
