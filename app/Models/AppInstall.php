<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One installation of the mobile app, as its telemetry describes it.
 *
 * The install id is minted by the app on first launch and is not an
 * advertising id: clearing the app's data or reinstalling makes a new one.
 * `user_id` is the account last seen signed in on it.
 */
class AppInstall extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(AppEvent::class, 'install_id', 'install_id');
    }
}
