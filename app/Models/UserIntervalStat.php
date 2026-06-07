<?php

namespace App\Models;

use App\Services\MusicTheoryService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserIntervalStat extends Model
{
    /**
     * The interval practice types tracked, keyed by practice_id.
     * `max_semitones` bounds which intervals are reachable for that type
     * (direction excludes the octave; see AIController interval-direction config).
     */
    public const INTERVAL_PRACTICE_TYPES = [
        2 => ['slug' => 'interval-direction-practice',    'name' => 'Interval Direction',    'max_semitones' => 11],
        3 => ['slug' => 'interval-comparison-practice',   'name' => 'Interval Comparison',   'max_semitones' => 12],
        4 => ['slug' => 'melodic-interval-practice',      'name' => 'Melodic Interval',      'max_semitones' => 12],
        5 => ['slug' => 'harmonic-interval-practice',     'name' => 'Harmonic Interval',     'max_semitones' => 12],
        6 => ['slug' => 'interval-construction-practice', 'name' => 'Interval Construction', 'max_semitones' => 12],
    ];

    /** Multiplier applied to an interval the user has never attempted. */
    public const UNTESTED_MULTIPLIER = 2.0;

    protected $fillable = [
        'user_id',
        'practice_id',
        'interval_name',
        'total_questions',
        'correct_answers',
        'incorrect_answers',
        'last_answered_at',
    ];

    protected function casts(): array
    {
        return [
            'last_answered_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record a single answered interval question, accumulating per
     * (user, practice type, interval name).
     */
    public static function record(int $userId, int $practiceId, string $interval, bool $isCorrect): self
    {
        $row = static::firstOrCreate(
            [
                'user_id'       => $userId,
                'practice_id'   => $practiceId,
                'interval_name' => $interval,
            ],
            [
                'total_questions'   => 0,
                'correct_answers'   => 0,
                'incorrect_answers' => 0,
            ]
        );

        $row->increment('total_questions');
        $row->increment($isCorrect ? 'correct_answers' : 'incorrect_answers');
        $row->forceFill(['last_answered_at' => now()])->save();

        return $row;
    }

    public function getAccuracyAttribute(): float
    {
        return $this->total_questions > 0
            ? round($this->correct_answers / $this->total_questions * 100, 1)
            : 0.0;
    }

    /**
     * Convert an accuracy percentage (0-100) into a practice-weight multiplier.
     *
     * The multiplier rises as accuracy falls, so weaker intervals are weighted
     * more heavily for future adaptive practice. Range: [1.0, 2.0].
     *   100% accuracy -> 1.0  (well mastered, least extra practice)
     *    50% accuracy -> 1.5
     *     0% accuracy -> 2.0  (needs the most practice)
     */
    public static function multiplierFromAccuracy(float $accuracyPercent): float
    {
        $accuracyFraction = max(0.0, min(1.0, $accuracyPercent / 100));
        return round(1.0 + (1.0 - $accuracyFraction) * 1.0, 2);
    }

    /**
     * Build a user's accuracy multipliers for every tracked interval across all
     * five interval practice types.
     *
     * Returns an ordered array, one entry per practice type:
     * [
     *   [
     *     'practice_id' => 4,
     *     'slug'        => 'melodic-interval-practice',
     *     'name'        => 'Melodic Interval',
     *     'intervals'   => [
     *        ['interval' => 'Minor 2nd', 'semitones' => 1, 'total_questions' => 10,
     *         'correct_answers' => 7, 'incorrect_answers' => 3, 'accuracy' => 70.0,
     *         'multiplier' => 1.3, 'tested' => true, 'last_answered_at' => Carbon|null],
     *        ...
     *     ],
     *   ],
     *   ...
     * ]
     */
    public static function accuracyMultipliersForUser(int $userId): array
    {
        $music = app(MusicTheoryService::class);

        // Index existing rows by practice_id + interval_name for O(1) lookup.
        $rows = static::where('user_id', $userId)->get()
            ->groupBy('practice_id')
            ->map(fn ($group) => $group->keyBy('interval_name'));

        $result = [];

        foreach (self::INTERVAL_PRACTICE_TYPES as $practiceId => $meta) {
            $intervals = [];

            for ($semitones = 1; $semitones <= $meta['max_semitones']; $semitones++) {
                $name = $music->intervalNameFromSemitones($semitones);
                if ($name === null) {
                    continue;
                }

                /** @var static|null $row */
                $row    = $rows[$practiceId][$name] ?? null;
                $tested = $row !== null && $row->total_questions > 0;

                $accuracy   = $tested ? $row->accuracy : 0.0;
                $multiplier = $tested
                    ? self::multiplierFromAccuracy($accuracy)
                    : self::UNTESTED_MULTIPLIER;

                $intervals[] = [
                    'interval'          => $name,
                    'semitones'         => $semitones,
                    'total_questions'   => $row->total_questions ?? 0,
                    'correct_answers'   => $row->correct_answers ?? 0,
                    'incorrect_answers' => $row->incorrect_answers ?? 0,
                    'accuracy'          => $accuracy,
                    'multiplier'        => $multiplier,
                    'tested'            => $tested,
                    'last_answered_at'  => $row->last_answered_at ?? null,
                ];
            }

            $result[] = [
                'practice_id' => $practiceId,
                'slug'        => $meta['slug'],
                'name'        => $meta['name'],
                'intervals'   => $intervals,
            ];
        }

        return $result;
    }
}
