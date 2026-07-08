<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherSubscriptionBenefitHistory extends Model
{
    protected $fillable = [
        'user_id', 'teacher_subscription_benefit_id', 'event', 'details', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function benefit(): BelongsTo
    {
        return $this->belongsTo(TeacherSubscriptionBenefit::class, 'teacher_subscription_benefit_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
