<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailSuppression extends Model
{
    protected $fillable = ['email', 'reason', 'source', 'notes', 'suppressed_at'];

    protected $casts = ['suppressed_at' => 'datetime'];

    public const REASONS = ['hard_bounce', 'soft_bounce', 'complaint', 'unsubscribe', 'manual'];
}
