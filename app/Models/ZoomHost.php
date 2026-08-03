<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A licensed Zoom user on the Harmoniva corporate account that lessons can be
 * hosted on. Synced from Zoom rather than entered by hand — see the
 * zoom:sync-hosts command.
 */
class ZoomHost extends Model
{
    protected $fillable = [
        'zoom_user_id', 'email', 'display_name', 'is_active', 'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'synced_at' => 'datetime',
        ];
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(ZoomMeeting::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function label(): string
    {
        return $this->display_name ?: $this->email;
    }
}
