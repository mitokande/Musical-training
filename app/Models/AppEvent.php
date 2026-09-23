<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One thing a learner did in the mobile app — a screen, an answer, a purchase.
 *
 * Written only by TelemetryIngestor, in bulk; nothing updates a row except the
 * one-time attribution of an install's anonymous events to the account that
 * signs in on it. The vocabulary is the app's (`src/telemetry/events.ts`), and
 * `props` is whatever that event carries — never free text a learner typed.
 */
class AppEvent extends Model
{
    use MassPrunable;

    /**
     * How long raw events are kept. A little over a year, so any month can
     * still be compared with the same month last year; aggregates worth keeping
     * longer belong in their own tables.
     */
    public const RETENTION_DAYS = 400;

    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'props' => 'array',
        'occurred_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function prunable(): Builder
    {
        return static::where('occurred_at', '<', now()->subDays(self::RETENTION_DAYS));
    }
}
