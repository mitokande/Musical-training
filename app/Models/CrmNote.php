<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmNote extends Model
{
    protected $fillable = [
        'user_id', 'admin_id', 'note', 'type', 'is_pinned', 'follow_up_date',
    ];

    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
            'follow_up_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeWithFollowUp($query)
    {
        return $query->whereNotNull('follow_up_date');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
