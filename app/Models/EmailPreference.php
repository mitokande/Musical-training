<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A user's marketing-email preferences. The single authority for deciding
 * whether a given marketing message (campaign or lifecycle automation) may
 * reach a user, layered on top of the existing suppression list.
 *
 * Categories:
 *   onboarding — the welcome email (always on unless the user chose important_only)
 *   tips       — first-exercise / learning-path / re-engagement nudges
 *   progress   — the weekly progress digest
 *   offers     — premium upsell / intro and promotional campaigns
 *   product    — product news and announcement campaigns
 *
 * Transactional mail (trial notices, booking confirmations, password resets)
 * never reaches this class — it bypasses the marketing gate entirely.
 */
class EmailPreference extends Model
{
    protected $fillable = [
        'user_id', 'frequency', 'topic_tips', 'topic_progress', 'topic_offers', 'topic_product', 'topic_teaching',
    ];

    protected $casts = [
        'topic_tips' => 'boolean',
        'topic_progress' => 'boolean',
        'topic_offers' => 'boolean',
        'topic_product' => 'boolean',
        'topic_teaching' => 'boolean',
    ];

    public const FREQUENCIES = ['all', 'weekly', 'important_only'];

    /**
     * Which category each lifecycle automation belongs to. Keys not listed
     * here (the transactional trial automations) never hit the gate.
     */
    public const CATEGORY_BY_KEY = [
        'welcome' => 'onboarding',
        'first_exercise_reminder' => 'tips',
        'learning_path_reminder' => 'tips',
        're_engagement' => 'tips',
        'weekly_progress' => 'progress',
        'premium_upsell' => 'offers',
        'premium_intro' => 'offers',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The user's saved preferences, or a non-persisted default (everything on)
     * when they have never touched them. Callers must not assume it exists in
     * the database.
     */
    public static function for(User $user): self
    {
        return $user->emailPreference ?? new self([
            'user_id' => $user->id,
            'frequency' => 'all',
            'topic_tips' => true,
            'topic_progress' => true,
            'topic_offers' => true,
            'topic_product' => true,
            'topic_teaching' => true,
        ]);
    }

    /**
     * Resolve the marketing category for a dispatch. Automations map by key;
     * everything else (campaigns, ad-hoc marketing) defaults to product news.
     */
    public static function categoryFor(?EmailAutomation $automation, string $emailType): string
    {
        if ($automation) {
            return self::CATEGORY_BY_KEY[$automation->key] ?? 'product';
        }

        return 'product';
    }

    /**
     * Whether this user accepts marketing mail of the given category.
     */
    public function allows(string $category): bool
    {
        $frequency = $this->frequency ?: 'all';

        if ($frequency === 'important_only') {
            return false; // no marketing mail at all
        }

        if ($category === 'onboarding') {
            return true; // the welcome email always sends (unless important_only above)
        }

        if ($frequency === 'weekly') {
            return $category === 'progress';
        }

        // frequency === 'all' — honour the per-topic toggles
        return match ($category) {
            'tips' => (bool) $this->topic_tips,
            'progress' => (bool) $this->topic_progress,
            'offers' => (bool) $this->topic_offers,
            'product' => (bool) $this->topic_product,
            'teaching' => (bool) $this->topic_teaching,
            default => true,
        };
    }
}
