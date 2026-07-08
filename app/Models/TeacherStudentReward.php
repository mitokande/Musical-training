<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherStudentReward extends Model
{
    public const TYPE_STICKER = 'sticker';

    public const TYPE_BADGE = 'badge';

    public const TYPE_LABEL = 'label';

    public const TYPE_MILESTONE = 'milestone';

    protected $fillable = [
        'teacher_id', 'student_id', 'teacher_assignment_id',
        'type', 'label', 'icon', 'note',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(TeacherAssignment::class, 'teacher_assignment_id');
    }
}
