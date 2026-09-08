<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailSuppression extends Model
{
    protected $fillable = ['email', 'reason', 'source', 'notes', 'suppressed_at'];

    protected $casts = ['suppressed_at' => 'datetime'];

    // 'apple_relay_disabled': the holder of a Sign in with Apple relay
    // alias turned forwarding off, told to us by Apple's notification
    // endpoint. Lifted again by the matching 'email-enabled' event.
    public const REASONS = ['hard_bounce', 'soft_bounce', 'complaint', 'unsubscribe', 'manual', 'apple_relay_disabled'];
}
