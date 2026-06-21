<?php

namespace App\Services;

/**
 * Beat-grouping utility for rhythm patterns.
 *
 * Durations are measured in "twelfths of a quarter note":
 *   quarter = 12, eighth = 6, sixteenth = 3, triplet-eighth = 4 (3×4 = 12 = quarter)
 *   dotted-quarter = 18, dotted-half = 36, half = 24, whole = 48
 *
 * Metric beat sizes (beatTwelfths):
 *   x/4 simple   → 12 twelfths (quarter note)
 *   x/8 compound → 18 twelfths (dotted-quarter note)
 *   x/2 alla breve → 24 twelfths (half note)
 *
 * Visual grouping sizes (visualGroupTwelfths) — used for beaming and display:
 *   x/2 → 12 twelfths (quarter note, same as x/4) — beams never cross a quarter boundary
 *   all others → same as beatTwelfths
 */
class RhythmGroupingService
{
    private const TWELFTHS = [
        'whole'             => 48,
        'dotted-half'       => 36,
        'half'              => 24,
        'dotted-quarter'    => 18,
        'quarter'           => 12,
        'dotted-eighth'     => 9,
        'eighth'            => 6,
        'triplet-eighth'    => 4,
        'sixteenth'         => 3,
        'whole_rest'        => 48,
        'half_rest'         => 24,
        'quarter_rest'      => 12,
        'eighth_rest'       => 6,
    ];

    /** Duration of a note value in twelfths. Returns 0 for unknown values. */
    public function noteTwelfths(string $noteValue): int
    {
        return self::TWELFTHS[$noteValue] ?? 0;
    }

    /**
     * Metric beat duration in twelfths (used for measure-duration and metronome timing).
     *   x/8 compound → dotted-quarter (18)
     *   x/4 simple   → quarter (12)
     *   x/2          → half (24)
     */
    public function beatTwelfths(int $denominator): int
    {
        return match ($denominator) {
            8  => 18,
            2  => 24,
            default => 12,
        };
    }

    /**
     * Visual grouping unit in twelfths (used for beaming, staff rendering, distractors).
     *
     * x/2 uses quarter-note groups (12) even though the metric beat is a half note.
     * This means beams never cross a quarter-note boundary in 2/2, 3/2, or 4/2 —
     * matching simple x/4 behavior exactly.
     * All other denominators return the same value as beatTwelfths.
     */
    public function visualGroupTwelfths(int $denominator): int
    {
        return $denominator === 2 ? 12 : $this->beatTwelfths($denominator);
    }

    /**
     * Number of visual groups per measure (measure duration ÷ visualGroupTwelfths).
     *   2/2 → 4, 3/2 → 6, 4/2 → 8
     *   4/4 → 4, 6/8 → 2, 9/8 → 3
     */
    public function visualGroupCount(int $numerator, int $denominator): int
    {
        $groupT = $this->visualGroupTwelfths($denominator);

        return $groupT > 0 ? intdiv($this->measureTwelfths($numerator, $denominator), $groupT) : 0;
    }

    /**
     * Full measure duration in twelfths.
     * For x/8: each eighth = 6 twelfths, so measure = num × 6.
     * For x/4 or x/2: measure = num × beatTwelfths (metric beat, NOT visual group).
     */
    public function measureTwelfths(int $numerator, int $denominator): int
    {
        if ($denominator === 8) {
            return $numerator * 6;
        }

        return $numerator * $this->beatTwelfths($denominator);
    }

    /**
     * Number of metric beat groups in a measure.
     * 6/8 → 2 dotted-quarter beats, 9/8 → 3, 3/8 → 1.
     * x/4 → x beats, x/2 → x half-note beats.
     *
     * Use visualGroupCount() when grouping for staff display or beaming.
     */
    public function beatCount(int $numerator, int $denominator): int
    {
        return $denominator === 8 ? intdiv($numerator, 3) : $numerator;
    }

    /**
     * Validate that a flat note array fills exactly one measure of the given time signature.
     */
    public function validate(array $noteValues, string $timeSig): bool
    {
        [$num, $den] = array_map('intval', explode('/', $timeSig));
        $expected = $this->measureTwelfths($num, $den);

        $actual = 0;
        foreach ($noteValues as $nv) {
            $actual += $this->noteTwelfths($nv);
        }

        return $actual === $expected;
    }

    /**
     * Group a flat note array into visual beat groups based on the time signature.
     *
     * Each note is assigned to the group where it STARTS.
     * Notes spanning multiple groups (whole, half, dotted-half in x/4) appear in
     * the group corresponding to their onset position — they are never split.
     * Adjacent rests are never merged; they stay as separate entries.
     *
     * For x/2 (2/2, 3/2, 4/2) groups are by quarter-note (visualGroupTwelfths = 12),
     * NOT by the metric half-note beat — so eighth-note beams never cross a
     * quarter-note boundary.
     *
     * Returns an array of group-indexed sub-arrays (0-based).
     * Always returns exactly visualGroupCount($num, $den) groups (some may be empty
     * when a long note from a previous group spans into that group).
     *
     * @param  array<string> $noteValues flat sequence of note/rest names
     * @return array<int, array<string>> indexed visual groups
     */
    public function group(array $noteValues, string $timeSig): array
    {
        [$num, $den] = array_map('intval', explode('/', $timeSig));
        $groupT   = $this->visualGroupTwelfths($den);
        $numGroups = $this->visualGroupCount($num, $den);

        $groups = array_fill(0, max(1, $numGroups), []);
        $pos    = 0;

        foreach ($noteValues as $nv) {
            $dur      = $this->noteTwelfths($nv);
            $groupIdx = $groupT > 0
                ? min($numGroups - 1, intdiv($pos, $groupT))
                : 0;
            $groups[$groupIdx][] = $nv;
            $pos += $dur;
        }

        return $groups;
    }
}
