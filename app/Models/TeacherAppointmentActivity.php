<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherAppointmentActivity extends Model
{
    protected $fillable = [
        'appointment_id', 'actor_id', 'action', 'from_status', 'to_status', 'notes',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(TeacherAppointment::class, 'appointment_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
