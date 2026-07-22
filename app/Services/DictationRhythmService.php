<?php

namespace App\Services;

/**
 * Canonical beat-pattern rhythm source for melodic dictation.
 *
 * Both PracticeMelodicDictation (Exercise Setup flow) and
 * LearningPathQuestionGenerator::generateMelodicDictation (Learning Path /
 * Teacher Assignment flows) build dictation rhythms through this service —
 * never generate dictation rhythms inline. Bars are filled one beat-pattern
 * at a time so no pattern ever straddles a barline, and later bars usually
 * repeat the first bar's rhythm so the phrase reads as a motif.
 */
class DictationRhythmService
{
    // Rhythm pattern pools for simple time (each sub-array fills the stated beat count)
    private const SIMPLE_ONE_BEAT = [
        ['quarter'],
        ['eighth', 'eighth'],
        ['sixteenth', 'sixteenth', 'sixteenth', 'sixteenth'],
        ['eighth', 'sixteenth', 'sixteenth'],
        ['sixteenth', 'sixteenth', 'eighth'],
        ['sixteenth', 'eighth', 'sixteenth'],
        ['dotted-eighth', 'sixteenth'],
        ['sixteenth', 'dotted-eighth'],
    ];

    private const SIMPLE_TWO_BEAT = [
        ['half'],
        ['dotted-quarter', 'eighth'],
        ['eighth', 'quarter', 'eighth'],
        ['eighth', 'dotted-quarter'],
    ];

    private const SIMPLE_THREE_BEAT = [
        ['dotted-half'],
    ];

    private const SIMPLE_FOUR_BEAT = [
        ['whole'],
    ];

    // Rhythm pattern pools for compound time (each sub-array fills one dotted-quarter beat group)
    private const COMPOUND_ONE_BEAT = [
        ['dotted-quarter'],
        ['quarter', 'eighth'],
        ['eighth', 'quarter'],
        ['eighth', 'eighth', 'eighth'],
        ['eighth', 'eighth', 'sixteenth', 'sixteenth'],
        ['sixteenth', 'sixteenth', 'eighth', 'eighth'],
        ['eighth', 'sixteenth', 'sixteenth', 'eighth'],
        ['sixteenth', 'sixteenth', 'sixteenth', 'sixteenth', 'eighth'],
        ['sixteenth', 'sixteenth', 'eighth', 'sixteenth', 'sixteenth'],
        ['eighth', 'sixteenth', 'sixteenth', 'sixteenth', 'sixteenth'],
        ['sixteenth', 'sixteenth', 'sixteenth', 'sixteenth', 'sixteenth', 'sixteenth'],
        ['dotted-eighth', 'sixteenth', 'eighth'],
        ['sixteenth', 'dotted-eighth', 'eighth'],
        ['eighth', 'dotted-eighth', 'sixteenth'],
        ['eighth', 'sixteenth', 'dotted-eighth'],
    ];

    private const COMPOUND_TWO_BEAT = [
        ['dotted-half'],
    ];

    /**
     * Generate a rhythm by selecting beat-level patterns from the pool.
     * Bars are filled one at a time so no pattern ever straddles a barline,
     * and later bars repeat the first bar's rhythm most of the time so the
     * phrase reads as a motif rather than two unrelated bars.
     * The returned array length always equals the required melody note count,
     * so notes[] and note_values[] are guaranteed to be in sync.
     */
    public function generateBeatPatternRhythm(int $bars, string $timeSig, array $allowedValues): array
    {
        $barRhythms = [];
        for ($bar = 0; $bar < $bars; $bar++) {
            if ($bar > 0 && mt_rand(0, 9) < 6) {
                $barRhythms[] = $barRhythms[$bar - 1];

                continue;
            }
            $barRhythms[] = $this->generateOneBarRhythm($timeSig, $allowedValues);
        }

        return array_merge(...$barRhythms);
    }

    public function generateOneBarRhythm(string $timeSig, array $allowedValues): array
    {
        [$num, $den] = array_map('intval', explode('/', $timeSig));
        $isCompound = ($den === 8 && $num % 3 === 0);

        $canUse = fn (array $p) => empty(array_diff($p, $allowedValues));
        $result = [];

        if ($isCompound) {
            // Dotted-quarter is the fundamental beat unit in compound time — always allow it.
            $ext = array_unique(array_merge($allowedValues, ['dotted-quarter', 'dotted-half']));
            $canUseC = fn (array $p) => empty(array_diff($p, $ext));

            $totalGroups = intdiv($num, 3);

            $oneBeat = array_values(array_filter(self::COMPOUND_ONE_BEAT, $canUseC));
            $twoBeat = array_values(array_filter(self::COMPOUND_TWO_BEAT, $canUseC));
            if (empty($oneBeat)) {
                $oneBeat = [['dotted-quarter']];
            }

            $bg = 0;
            while ($bg < $totalGroups) {
                $rem = $totalGroups - $bg;
                if ($rem >= 2 && ! empty($twoBeat) && mt_rand(0, 5) === 0) {
                    $result = array_merge($result, $twoBeat[array_rand($twoBeat)]);
                    $bg += 2;
                } else {
                    $result = array_merge($result, $oneBeat[array_rand($oneBeat)]);
                    $bg += 1;
                }
            }
        } else {
            // /2 meters: each beat = half note = 2 quarter-note beats
            // 2/2 → 4, 3/2 → 6, 4/2 → 8 quarter-beat units per bar
            $totalBeats = ($den === 2) ? $num * 2 : $num;

            $oneBeat = array_values(array_filter(self::SIMPLE_ONE_BEAT, $canUse));
            $twoBeat = array_values(array_filter(self::SIMPLE_TWO_BEAT, $canUse));
            $threeBeat = array_values(array_filter(self::SIMPLE_THREE_BEAT, $canUse));
            $fourBeat = array_values(array_filter(self::SIMPLE_FOUR_BEAT, $canUse));
            if (empty($oneBeat)) {
                $oneBeat = [['quarter']];
            }

            $beat = 0;
            while ($beat < $totalBeats) {
                $rem = $totalBeats - $beat;
                if ($rem >= 4 && ! empty($fourBeat) && mt_rand(0, 9) === 0) {
                    $result = array_merge($result, $fourBeat[array_rand($fourBeat)]);
                    $beat += 4;
                } elseif ($rem >= 3 && ! empty($threeBeat) && mt_rand(0, 7) === 0) {
                    $result = array_merge($result, $threeBeat[array_rand($threeBeat)]);
                    $beat += 3;
                } elseif ($rem >= 2 && ! empty($twoBeat) && mt_rand(0, 3) === 0) {
                    $result = array_merge($result, $twoBeat[array_rand($twoBeat)]);
                    $beat += 2;
                } else {
                    $result = array_merge($result, $oneBeat[array_rand($oneBeat)]);
                    $beat += 1;
                }
            }
        }

        return $result;
    }
}
