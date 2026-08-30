<?php

namespace App\Services\Practice;

use App\Models\Practice;
use Illuminate\Support\Facades\Cache;

/**
 * Single source of truth for practice-type identity: the slug ⇄ practices.id
 * mapping, the Exercise Setup Studio key mapping, and the per-type answer field.
 *
 * The legacy types (1–6) keep their hardcoded ids — they are seeded with fixed
 * primary keys and referenced directly all over the codebase. The four newer
 * types are resolved from the `practices` table, where the seeder assigns ids
 * dynamically.
 */
class PracticeCatalog
{
    /** Legacy fixed practice ids. */
    public const LEGACY_IDS = [
        'single-note-practice' => 1,
        'interval-direction-practice' => 2,
        'interval-comparison-practice' => 3,
        'melodic-interval-practice' => 4,
        'harmonic-interval-practice' => 5,
        'interval-construction-practice' => 6,
    ];

    /** Slugs whose practices.id must be looked up at runtime. */
    public const DB_RESOLVED_SLUGS = [
        'chord-practice',
        'scale-practice',
        'rhythm-practice',
        'melodic-dictation',
    ];

    /** Exercise Setup Studio key => practice slug. */
    public const STUDIO_KEYS = [
        'melodic-intervals' => 'melodic-interval-practice',
        'harmonic-intervals' => 'harmonic-interval-practice',
        'intervals-direction' => 'interval-direction-practice',
        'intervals-construction' => 'interval-construction-practice',
        'interval-comparison' => 'interval-comparison-practice',
        'single-note' => 'single-note-practice',
        'chords' => 'chord-practice',
        'scales' => 'scale-practice',
        'rhythm' => 'rhythm-practice',
        'melodic-dictation' => 'melodic-dictation',
    ];

    /** The attribute on a question that holds the correct answer. */
    public const ANSWER_FIELDS = [
        'single-note-practice' => 'target',
        'interval-direction-practice' => 'direction',
        'interval-comparison-practice' => 'target',
        'melodic-interval-practice' => 'interval',
        'harmonic-interval-practice' => 'interval',
        'interval-construction-practice' => 'note2',
        'chord-practice' => 'chord_type',
        'scale-practice' => 'scale_type',
        'rhythm-practice' => 'note_values',
        'melodic-dictation' => 'notes',
    ];

    /**
     * Slug => the `app.exercises.*` key that names it.
     *
     * The website has rendered these names from the lang files all along, while
     * the API served practices.name / .description straight out of the table —
     * columns that only ever held English. Reusing the existing keys keeps one
     * source of truth for both clients, and needs no new column and no write to
     * the practices table.
     */
    public const LABEL_KEYS = [
        'single-note-practice' => 'single_note',
        'interval-direction-practice' => 'interval_direction',
        'interval-comparison-practice' => 'interval_comparison',
        'melodic-interval-practice' => 'melodic_interval',
        'harmonic-interval-practice' => 'harmonic_interval',
        'interval-construction-practice' => 'interval_construction',
        'chord-practice' => 'chord',
        'scale-practice' => 'scale',
        'rhythm-practice' => 'rhythm',
        'melodic-dictation' => 'melodic_dictation',
    ];

    private const CACHE_KEY = 'practice_catalog.db_ids';

    /**
     * The translated name of a practice type, falling back to the stored column
     * and then to the slug itself, so a type with no lang key still reads.
     */
    public function displayName(string $slug, ?string $fallback = null): string
    {
        return $this->translate($slug, '')
            ?? $fallback
            ?? ucwords(str_replace('-', ' ', $slug));
    }

    /** The translated one-line description, or the stored column. */
    public function displayDescription(string $slug, ?string $fallback = null): ?string
    {
        return $this->translate($slug, '_desc') ?? $fallback;
    }

    /**
     * A translation, or null when there is no key for this slug.
     *
     * __() echoes the key back when it finds nothing, and an unresolved key
     * reaching the app would read as a bug rather than as English; a nested
     * lookup can also return the whole sub-array, which is never a label.
     */
    private function translate(string $slug, string $suffix): ?string
    {
        if (! isset(self::LABEL_KEYS[$slug])) {
            return null;
        }

        $key = 'app.exercises.'.self::LABEL_KEYS[$slug].$suffix;
        $value = __($key);

        return is_string($value) && $value !== $key ? $value : null;
    }

    /** Every practice slug the app knows about, in curriculum order. */
    public function slugs(): array
    {
        return array_keys(self::ANSWER_FIELDS);
    }

    public function isKnownSlug(?string $slug): bool
    {
        return $slug !== null && isset(self::ANSWER_FIELDS[$slug]);
    }

    /**
     * Resolve a practice slug to its `practices` table id.
     * Returns null when the slug is unknown or has no seeded row.
     */
    public function practiceIdForSlug(?string $slug): ?int
    {
        if ($slug === null) {
            return null;
        }

        if (isset(self::LEGACY_IDS[$slug])) {
            return self::LEGACY_IDS[$slug];
        }

        return $this->databaseIds()[$slug] ?? null;
    }

    /** Reverse lookup: practices.id => slug. */
    public function slugForPracticeId(?int $practiceId): ?string
    {
        if ($practiceId === null) {
            return null;
        }

        $slug = array_search($practiceId, self::LEGACY_IDS, true);
        if ($slug !== false) {
            return $slug;
        }

        $slug = array_search($practiceId, $this->databaseIds(), true);

        return $slug === false ? null : $slug;
    }

    public function slugForStudioKey(?string $studioKey): ?string
    {
        return self::STUDIO_KEYS[$studioKey] ?? null;
    }

    public function answerField(string $slug): ?string
    {
        return self::ANSWER_FIELDS[$slug] ?? null;
    }

    /** Interval types are the only ones that feed UserIntervalStat. */
    public function isIntervalType(string $slug): bool
    {
        return isset(self::LEGACY_IDS[$slug]) && $slug !== 'single-note-practice';
    }

    public function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array<string,int>
     */
    private function databaseIds(): array
    {
        return Cache::remember(self::CACHE_KEY, 300, function () {
            return Practice::whereIn('slug', self::DB_RESOLVED_SLUGS)
                ->pluck('id', 'slug')
                ->map(fn ($id) => (int) $id)
                ->all();
        });
    }
}
