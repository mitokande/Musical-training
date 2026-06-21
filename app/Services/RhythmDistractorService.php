<?php

namespace App\Services;

/**
 * Generates near-miss distractor options for the Rhythm Recognition exercise.
 *
 * Three strategies keep distractors visually and rhythmically close to the
 * correct answer so the user must actually discriminate the rhythm rather than
 * guessing from obvious differences:
 *
 *  • reorderWithin – shuffles tokens inside one beat group (same total)
 *  • swapGroups    – swaps two equal-size groups with different contents
 *  • replaceGroup  – substitutes one group with a curated same-duration alternative
 *
 * All three preserve the time-signature and the total measure duration.
 */
class RhythmDistractorService
{
    public function __construct(
        private readonly RhythmGroupingService $groupingSvc,
    ) {}

    /**
     * Curated same-duration replacement patterns, indexed by
     * [total twelfths][difficulty level].
     *
     * 'easy' ⊆ 'medium' ⊆ 'hard' (each level extends the previous).
     * All entries verified: sum of TWELFTHS values equals the key.
     */
    private const BEAT_ALTS = [
        // ── 12 twelfths = 1 quarter beat (simple meters x/4) ─────────────────
        12 => [
            'easy'   => [
                ['quarter'],
                ['eighth', 'eighth'],
            ],
            'medium' => [
                ['quarter'],
                ['eighth', 'eighth'],
                ['dotted-eighth', 'sixteenth'],
                ['sixteenth', 'dotted-eighth'],
                ['eighth', 'sixteenth', 'sixteenth'],
                ['sixteenth', 'eighth', 'sixteenth'],
                ['sixteenth', 'sixteenth', 'eighth'],
            ],
            'hard'   => [
                ['quarter'],
                ['eighth', 'eighth'],
                ['dotted-eighth', 'sixteenth'],
                ['sixteenth', 'dotted-eighth'],
                ['eighth', 'sixteenth', 'sixteenth'],
                ['sixteenth', 'eighth', 'sixteenth'],
                ['sixteenth', 'sixteenth', 'eighth'],
                ['sixteenth', 'sixteenth', 'sixteenth', 'sixteenth'],
            ],
        ],
        // ── 18 twelfths = 1 dotted-quarter beat (compound meters x/8) ────────
        18 => [
            'easy'   => [
                ['dotted-quarter'],
                ['quarter', 'eighth'],
                ['eighth', 'quarter'],
                ['eighth', 'eighth', 'eighth'],
            ],
            'medium' => [
                ['dotted-quarter'],
                ['quarter', 'eighth'],
                ['eighth', 'quarter'],
                ['eighth', 'eighth', 'eighth'],
                ['dotted-eighth', 'sixteenth', 'eighth'],
                ['eighth', 'dotted-eighth', 'sixteenth'],
                ['eighth', 'eighth', 'sixteenth', 'sixteenth'],
                ['sixteenth', 'sixteenth', 'eighth', 'eighth'],
            ],
            'hard'   => [
                ['dotted-quarter'],
                ['quarter', 'eighth'],
                ['eighth', 'quarter'],
                ['eighth', 'eighth', 'eighth'],
                ['dotted-eighth', 'sixteenth', 'eighth'],
                ['eighth', 'dotted-eighth', 'sixteenth'],
                ['eighth', 'eighth', 'sixteenth', 'sixteenth'],
                ['sixteenth', 'sixteenth', 'eighth', 'eighth'],
                ['sixteenth', 'dotted-eighth', 'eighth'],
                ['eighth', 'sixteenth', 'dotted-eighth'],
            ],
        ],
        // ── 24 twelfths = 2 quarter beats ────────────────────────────────────
        24 => [
            'easy'   => [
                ['half'],
                ['quarter', 'quarter'],
            ],
            'medium' => [
                ['half'],
                ['quarter', 'quarter'],
                ['dotted-quarter', 'eighth'],
                ['eighth', 'dotted-quarter'],
            ],
            'hard'   => [
                ['half'],
                ['quarter', 'quarter'],
                ['dotted-quarter', 'eighth'],
                ['eighth', 'dotted-quarter'],
                ['eighth', 'quarter', 'eighth'],
            ],
        ],
        // ── 36 twelfths = 3 quarter beats  OR  2 dotted-quarter beats (6/8 whole bar) ──
        36 => [
            'easy'   => [
                ['dotted-half'],
            ],
            'medium' => [
                ['dotted-half'],
                ['half', 'quarter'],
                ['quarter', 'half'],
            ],
            'hard'   => [
                ['dotted-half'],
                ['half', 'quarter'],
                ['quarter', 'half'],
                ['quarter', 'quarter', 'quarter'],
            ],
        ],
        // ── 48 twelfths = whole note / 4 quarter beats ───────────────────────
        48 => [
            'easy'   => [['whole']],
            'medium' => [['whole'], ['half', 'half']],
            'hard'   => [['whole'], ['half', 'half']],
        ],
        // ── 54 twelfths = 3 dotted-quarter beats (9/8 whole bar) ────────────
        54 => [
            'easy'   => [['dotted-half', 'dotted-quarter']],
            'medium' => [
                ['dotted-half', 'dotted-quarter'],
                ['dotted-quarter', 'dotted-half'],
            ],
            'hard'   => [
                ['dotted-half', 'dotted-quarter'],
                ['dotted-quarter', 'dotted-half'],
            ],
        ],
    ];

    // ─── Public API ────────────────────────────────────────────────────────────

    /**
     * Generate up to 3 near-miss distractors for the given correct rhythm.
     *
     * @param  array   $correct     Flat token sequence of the canonical correct answer.
     * @param  string  $timeSig     Time signature string, e.g. '4/4', '6/8'.
     * @param  string  $difficulty  'easy' | 'medium' | 'hard'
     * @return array   At most 3 distinct distractor token sequences.
     */
    public function generate(array $correct, string $timeSig, string $difficulty = 'medium'): array
    {
        [, $den] = array_map('intval', explode('/', $timeSig));
        $beatT   = $this->groupingSvc->visualGroupTwelfths($den);
        $totalT  = $this->sumTwelfths($correct);

        $groups     = $this->accumulateGroups($correct, $beatT);
        $distractors = [];
        $attempts    = 0;

        while (count($distractors) < 3 && $attempts < 80) {
            $attempts++;
            $candidate = $this->makeDistractor($groups, $difficulty);
            if ($candidate === null) {
                continue;
            }
            if ($candidate === $correct) {
                continue;
            }
            if (in_array($candidate, $distractors)) {
                continue;
            }
            if ($this->sumTwelfths($candidate) !== $totalT) {
                continue;
            }
            $distractors[] = $candidate;
        }

        return $distractors;
    }

    /**
     * Split a flat token sequence into beat-aligned groups by accumulating
     * tokens until the running sum is an exact multiple of $beatTwelfths.
     * Multi-beat notes (half, dotted-half, whole) naturally form super-groups
     * of 2 or more beat units; they are never split mid-note.
     *
     * @param  string[] $tokens
     * @return array<array{tokens: string[], twelfths: int}>
     */
    public function accumulateGroups(array $tokens, int $beatTwelfths): array
    {
        $groups  = [];
        $current = [];
        $sum     = 0;

        foreach ($tokens as $token) {
            $dur = $this->groupingSvc->noteTwelfths($token);
            $current[] = $token;
            $sum += $dur;

            if ($beatTwelfths > 0 && $sum % $beatTwelfths === 0) {
                $groups[] = ['tokens' => $current, 'twelfths' => $sum];
                $current  = [];
                $sum      = 0;
            }
        }

        if (! empty($current)) {
            // Remaining tokens (shouldn't happen with a valid bar; kept for safety)
            $groups[] = ['tokens' => $current, 'twelfths' => $sum];
        }

        return $groups;
    }

    // ─── Strategy dispatcher ───────────────────────────────────────────────────

    private function makeDistractor(array $groups, string $difficulty): ?array
    {
        // 'replace' appears twice on medium/hard to be picked more often.
        // Easy keeps one 'replace' so simple patterns (all quarters) can still vary.
        $pool = $difficulty === 'easy'
            ? ['reorder', 'swap', 'replace']
            : ['reorder', 'swap', 'replace', 'replace'];

        $pool = $this->shuffled($pool);

        foreach ($pool as $strategy) {
            $result = match ($strategy) {
                'reorder' => $this->reorderWithin($groups),
                'swap'    => $this->swapGroups($groups),
                'replace' => $this->replaceGroup($groups, $difficulty),
                default   => null,
            };
            if ($result !== null) {
                return $result;
            }
        }

        return null;
    }

    // ─── Strategies ───────────────────────────────────────────────────────────

    /**
     * Reorder the tokens inside a randomly chosen multi-token, rest-free group.
     * The group's total duration is preserved; only token order changes.
     */
    private function reorderWithin(array $groups): ?array
    {
        $eligible = array_keys(array_filter(
            $groups,
            fn ($g) => count($g['tokens']) > 1 && ! $this->hasRest($g['tokens'])
        ));

        if (empty($eligible)) {
            return null;
        }

        shuffle($eligible);

        foreach ($eligible as $idx) {
            $tokens = $groups[$idx]['tokens'];
            $orig   = implode(',', $tokens);
            $perms  = $this->uniquePermutations($tokens);

            foreach ($this->shuffled($perms) as $perm) {
                if (implode(',', $perm) !== $orig) {
                    $newGroups               = $groups;
                    $newGroups[$idx]['tokens'] = $perm;

                    return $this->flatten($newGroups);
                }
            }
        }

        return null;
    }

    /**
     * Swap two groups that share the same twelfths count but differ in content.
     * Equal-size constraint guarantees beat alignment is preserved after the swap.
     */
    private function swapGroups(array $groups): ?array
    {
        $pairs = [];
        $n     = count($groups);

        for ($i = 0; $i < $n - 1; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                if (
                    $groups[$i]['twelfths'] === $groups[$j]['twelfths']
                    && $groups[$i]['tokens'] !== $groups[$j]['tokens']
                ) {
                    $pairs[] = [$i, $j];
                }
            }
        }

        if (empty($pairs)) {
            return null;
        }

        [$i, $j]                         = $pairs[array_rand($pairs)];
        $newGroups                       = $groups;
        [$newGroups[$i], $newGroups[$j]] = [$newGroups[$j], $newGroups[$i]];

        return $this->flatten($newGroups);
    }

    /**
     * Replace one rest-free group with a different same-duration pattern from
     * the curated BEAT_ALTS table.
     */
    private function replaceGroup(array $groups, string $difficulty): ?array
    {
        $level   = in_array($difficulty, ['easy', 'medium', 'hard']) ? $difficulty : 'medium';
        $indices = array_keys($groups);
        shuffle($indices);

        foreach ($indices as $idx) {
            $g = $groups[$idx];

            if ($this->hasRest($g['tokens'])) {
                continue;
            }

            $twelfths = $g['twelfths'];
            $alts     = self::BEAT_ALTS[$twelfths][$level] ?? [];
            $groupStr = implode(',', $g['tokens']);

            $valid = array_values(array_filter(
                $alts,
                fn ($a) => implode(',', $a) !== $groupStr && ! $this->hasRest($a)
            ));

            if (! empty($valid)) {
                $newGroups               = $groups;
                $newGroups[$idx]['tokens'] = $valid[array_rand($valid)];

                return $this->flatten($newGroups);
            }
        }

        return null;
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function flatten(array $groups): array
    {
        $out = [];
        foreach ($groups as $g) {
            array_push($out, ...$g['tokens']);
        }

        return $out;
    }

    private function sumTwelfths(array $tokens): int
    {
        return (int) array_sum(
            array_map([$this->groupingSvc, 'noteTwelfths'], $tokens)
        );
    }

    private function hasRest(array $tokens): bool
    {
        return (bool) array_filter($tokens, fn ($t) => str_contains($t, '_rest'));
    }

    /**
     * All unique permutations of an array (deduplicates when tokens repeat).
     * Capped at 120 entries for safety with larger groups.
     */
    public function uniquePermutations(array $arr): array
    {
        $n = count($arr);

        if ($n <= 1) {
            return [$arr];
        }
        if ($n === 2) {
            return $arr[0] === $arr[1] ? [$arr] : [$arr, [$arr[1], $arr[0]]];
        }

        $result = [];
        $seen   = [];

        foreach ($arr as $i => $item) {
            $rest = $arr;
            array_splice($rest, $i, 1);

            foreach ($this->uniquePermutations($rest) as $perm) {
                $candidate = array_merge([$item], $perm);
                $key       = implode(',', $candidate);

                if (! isset($seen[$key])) {
                    $seen[$key] = true;
                    $result[]   = $candidate;

                    if (count($result) >= 120) {
                        return $result;
                    }
                }
            }
        }

        return $result;
    }

    private function shuffled(array $arr): array
    {
        shuffle($arr);

        return $arr;
    }
}
