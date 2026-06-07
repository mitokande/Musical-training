<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'description', 'type', 'value', 'max_uses',
        'used_count', 'applicable_plans', 'applicable_roles',
        'starts_at', 'expires_at', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'applicable_plans' => 'array',
            'applicable_roles' => 'array',
            'is_active' => 'boolean',
            'value' => 'decimal:2',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function isValid(): bool
    {
        if (!$this->is_active) return false;
        if ($this->max_uses && $this->used_count >= $this->max_uses) return false;
        if ($this->starts_at && $this->starts_at->isFuture()) return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;

        return true;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
