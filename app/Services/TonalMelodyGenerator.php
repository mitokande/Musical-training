<?php

namespace App\Services;

/**
 * Canonical generator for short, singable, tonal dictation melodies.
 *
 * Both the Exercise Setup flow (PracticeMelodicDictation) and the Learning
 * Path flow (LearningPathQuestionGenerator) build their melodic-dictation
 * questions through this service, so staff rendering, playback and answer
 * checking always derive from the same melody data and obey the same rules:
 *
 *  - melodies stay inside one key (diatonic note pool, no stray chromatics)
 *  - they start on a tonic-triad degree (1, 3 or 5) and cadence per level
 *    (beginner: tonic; intermediate: tonic or dominant; advanced: mostly tonic)
 *  - beginner moves by step only (m2/M2); other levels keep ≥70% steps/thirds
 *  - leaps are capped per level (intermediate: occasional 4ths, at most one
 *    5th; advanced: rare 6ths/7ths), never occur back to back, and must
 *    resolve by contrary motion; tritone and octave+ leaps are never emitted
 *  - melodies don't zigzag mechanically between high and low notes
 *  - the total range is capped per level (beginner ~6th, intermediate octave)
 *
 * generateMelody() retries the weighted random walk until a candidate passes
 * validateMelody(), then falls back to a guaranteed-valid stepwise line.
 */
class TonalMelodyGenerator
{
    private const MAJOR_SCALES = [
        'C' => ['C', 'D', 'E', 'F', 'G', 'A', 'B'],
        'G' => ['G', 'A', 'B', 'C', 'D', 'E', 'F#'],
        'D' => ['D', 'E', 'F#', 'G', 'A', 'B', 'C#'],
        'A' => ['A', 'B', 'C#', 'D', 'E', 'F#', 'G#'],
        'E' => ['E', 'F#', 'G#', 'A', 'B', 'C#', 'D#'],
        'B' => ['B', 'C#', 'D#', 'E', 'F#', 'G#', 'A#'],
        'F#' => ['F#', 'G#', 'A#', 'B', 'C#', 'D#', 'E#'],
        'C#' => ['C#', 'D#', 'E#', 'F#', 'G#', 'A#', 'B#'],
        'F' => ['F', 'G', 'A', 'Bb', 'C', 'D', 'E'],
        'Bb' => ['Bb', 'C', 'D', 'Eb', 'F', 'G', 'A'],
        'Eb' => ['Eb', 'F', 'G', 'Ab', 'Bb', 'C', 'D'],
        'Ab' => ['Ab', 'Bb', 'C', 'Db', 'Eb', 'F', 'G'],
        'Db' => ['Db', 'Eb', 'F', 'Gb', 'Ab', 'Bb', 'C'],
        'Gb' => ['Gb', 'Ab', 'Bb', 'Cb', 'Db', 'Eb', 'F'],
        'Cb' => ['Cb', 'Db', 'Eb', 'Fb', 'Gb', 'Ab', 'Bb'],
    ];

    private const MAX_BUILD_ATTEMPTS = 25;

    // ── Key context ──────────────────────────────────────────────────────────

    /**
     * Build the full tonal context for a key inside a clef's comfortable range:
     * diatonic note pool plus tonic-triad / tonic / dominant anchor notes.
     *
     * @return array{pool: string[], triad: string[], tonics: string[], dominants: string[]}
     */
    public function contextForKey(string $majorKeyRoot, string $mode, string $clef): array
    {
        $noteNames = self::MAJOR_SCALES[$majorKeyRoot] ?? self::MAJOR_SCALES['C'];
        [$minMidi, $maxMidi] = $this->clefMidiRange($clef);

        $pool = [];
        for ($octave = 0; $octave <= 8; $octave++) {
            foreach ($noteNames as $note) {
                $noteWithOctave = $note.$octave;
                $midi = $this->noteToMidi($noteWithOctave);
                if ($midi >= $minMidi && $midi <= $maxMidi) {
                    $pool[] = $noteWithOctave;
                }
            }
        }

        return $this->contextFromPool($pool, $majorKeyRoot, $mode);
    }

    /**
     * Same as contextForKey() but for a caller-supplied note pool (e.g. a
     * Learning Path exercise's configured note_pool).
     *
     * @param  string[]  $notePool  notes with octave, e.g. ['C4','D4','E4']
     * @return array{pool: string[], triad: string[], tonics: string[], dominants: string[]}
     */
    public function contextFromPool(array $notePool, string $majorKeyRoot, string $mode = 'major'): array
    {
        $scale = self::MAJOR_SCALES[$majorKeyRoot] ?? self::MAJOR_SCALES['C'];

        // Relative minor shares the key signature; its tonic is scale degree 6.
        $triadNames = $mode === 'minor'
            ? [$scale[5], $scale[0], $scale[2]]
            : [$scale[0], $scale[2], $scale[4]];

        $triad = [];
        foreach ($notePool as $poolNote) {
            foreach ($triadNames as $tn) {
                if ($this->noteNameMatches($poolNote, $tn)) {
                    $triad[] = $poolNote;
                    break;
                }
            }
        }

        return [
            'pool' => array_values($notePool),
            'triad' => $triad ?: array_slice($notePool, 0, 3),
            'tonics' => $this->pickMiddleOfName($triadNames[0], $notePool),
            'dominants' => $this->pickMiddleOfName($triadNames[2], $notePool),
        ];
    }

    /**
     * Relative minor tonic of a major key (scale degree 6) — e.g. C→A, F→D.
     * Used to label/anchor minor-mode dictation built on the relative major's
     * key signature.
     */
    public function relativeMinorRoot(string $majorKeyRoot): string
    {
        $scale = self::MAJOR_SCALES[$majorKeyRoot] ?? self::MAJOR_SCALES['C'];

        return $scale[5];
    }

    // ── Melody generation ────────────────────────────────────────────────────

    /**
     * Generate exactly $noteCount diatonic pitches that satisfy all level
     * rules. Always returns a melody: after MAX_BUILD_ATTEMPTS failed random
     * walks it falls back to a stepwise line that is valid by construction.
     *
     * @param  array{pool: string[], triad: string[], tonics: string[], dominants: string[]}  $context
     * @return string[]
     */
    public function generateMelody(int $noteCount, array $context, string $difficulty = 'intermediate'): array
    {
        $difficulty = $this->normalizeDifficulty($difficulty);
        $pool = $context['pool'] ?? [];

        if (empty($pool) || $noteCount <= 0) {
            return array_fill(0, max(1, $noteCount), 'C4');
        }

        $poolMidi = [];
        foreach ($pool as $note) {
            $poolMidi[$note] = $this->noteToMidi($note);
        }

        $endNote = $this->pickEndNote($context, $difficulty);
        $startNote = $this->pickStartNote($context, $poolMidi, $endNote, $difficulty);

        for ($attempt = 0; $attempt < self::MAX_BUILD_ATTEMPTS; $attempt++) {
            $melody = $this->buildMelody($noteCount, $poolMidi, $startNote, $endNote, $difficulty);
            if ($melody !== null && $this->validateMelody($melody, $difficulty)) {
                return $melody;
            }
        }

        return $this->buildFallbackMelody($noteCount, $poolMidi, $startNote, $endNote, $difficulty);
    }

    /**
     * Check a melody against every level rule: beginner steps-only / ≥70%
     * steps+thirds elsewhere, per-level leap size and count caps (at most one
     * 5th at intermediate), no tritone or octave+ leaps, no consecutive leaps,
     * contrary-motion leap resolution, no mechanical zigzag contour, and the
     * per-level range cap.
     *
     * @param  string[]  $melody
     */
    public function validateMelody(array $melody, string $difficulty): bool
    {
        $difficulty = $this->normalizeDifficulty($difficulty);
        $n = count($melody);
        if ($n < 3) {
            return true;
        }

        $midis = array_map(fn ($note) => $this->noteToMidi($note), $melody);

        $intervals = [];
        $dirs = [];
        for ($i = 1; $i < $n; $i++) {
            $delta = $midis[$i] - $midis[$i - 1];
            $intervals[] = abs($delta);
            $dirs[] = $delta <=> 0;
        }
        $total = count($intervals);

        $conjunct = count(array_filter($intervals, fn ($d) => $d <= 4));
        if ($conjunct / $total < 0.70) {
            return false;
        }

        $leaps = 0;
        foreach ($intervals as $i => $d) {
            if ($d > $this->maxLeapSize($difficulty)) {
                return false;
            }
            // Augmented/diminished melodic intervals need explicit config — never emit
            if ($d === 6) {
                return false;
            }
            if ($d < 5) {
                continue;
            }

            $leaps++;
            if ($i > 0 && $intervals[$i - 1] >= 5) {
                return false;
            }
            if ($i < $total - 1 && ! $this->leapResolves($dirs[$i], $dirs[$i + 1], $intervals[$i + 1], $difficulty)) {
                return false;
            }
        }

        if ($leaps > $this->maxLeapCount($difficulty)) {
            return false;
        }
        // 5ths stay rare at intermediate; 6ths/7ths stay rare at advanced
        if ($difficulty === 'intermediate' && count(array_filter($intervals, fn ($d) => $d === 7)) > 1) {
            return false;
        }
        if ($difficulty === 'advanced' && count(array_filter($intervals, fn ($d) => $d >= 8)) > 1) {
            return false;
        }

        // Reject mechanical zigzag (e.g. C-D-C-D…): near-total direction
        // reversal between consecutive moves reads as random, not melodic.
        if ($total >= 5) {
            $pairs = 0;
            $reversals = 0;
            for ($i = 1; $i < $total; $i++) {
                if ($dirs[$i] !== 0 && $dirs[$i - 1] !== 0) {
                    $pairs++;
                    if ($dirs[$i] === -$dirs[$i - 1]) {
                        $reversals++;
                    }
                }
            }
            if ($pairs >= 4 && $reversals / $pairs > 0.75) {
                return false;
            }
        }

        return max($midis) - min($midis) <= $this->maxRange($difficulty);
    }

    // ── Internals ────────────────────────────────────────────────────────────

    private function normalizeDifficulty(string $difficulty): string
    {
        return in_array($difficulty, ['beginner', 'advanced'], true) ? $difficulty : 'intermediate';
    }

    private function maxRange(string $difficulty): int
    {
        return match ($difficulty) {
            'beginner' => 9,   // major 6th
            'advanced' => 16,  // major 10th
            default => 12,     // octave
        };
    }

    private function maxLeapSize(string $difficulty): int
    {
        return match ($difficulty) {
            'beginner' => 2,   // steps only (m2/M2)
            'advanced' => 11,  // major 7th — octave+ would need explicit config
            default => 7,      // perfect 5th
        };
    }

    private function maxLeapCount(string $difficulty): int
    {
        return match ($difficulty) {
            'beginner' => 0,
            'advanced' => 3,
            default => 2,
        };
    }

    /**
     * A leap (≥ P4) must be answered by contrary motion. Beginner additionally
     * caps the answering move at a third; intermediate/advanced also accept a
     * same-direction step (e.g. sol→do→re).
     */
    private function leapResolves(int $leapDir, int $nextDir, int $nextDist, string $difficulty): bool
    {
        $contrary = $nextDir === -$leapDir;

        return match ($difficulty) {
            'beginner' => $contrary && $nextDist <= 4,
            default => $contrary || $nextDist <= 2,
        };
    }

    private function pickEndNote(array $context, string $difficulty): string
    {
        $pool = $context['pool'];
        $tonics = ! empty($context['tonics']) ? $context['tonics'] : $pool;

        if ($difficulty === 'beginner') {
            $candidates = $tonics;
        } elseif ($difficulty === 'intermediate') {
            // 70% tonic, 30% dominant
            $dominants = ! empty($context['dominants']) ? $context['dominants'] : $tonics;
            $candidates = mt_rand(0, 9) < 7 ? $tonics : $dominants;
        } else {
            // Advanced: prefer tonic (60%) but allow any scale note
            $candidates = mt_rand(0, 4) < 3 ? $tonics : $pool;
        }

        return $candidates[array_rand($candidates)];
    }

    /**
     * Start on a tonic-triad degree, restricted to notes close enough to the
     * chosen ending that a melody within the level's range cap can exist.
     */
    private function pickStartNote(array $context, array $poolMidi, string $endNote, string $difficulty): string
    {
        $triad = ! empty($context['triad']) ? $context['triad'] : $context['pool'];
        $endMidi = $poolMidi[$endNote] ?? $this->noteToMidi($endNote);
        $headroom = max(4, $this->maxRange($difficulty) - 3);

        $near = array_values(array_filter(
            $triad,
            fn ($n) => abs(($poolMidi[$n] ?? $this->noteToMidi($n)) - $endMidi) <= $headroom
        ));

        if (! empty($near)) {
            return $near[array_rand($near)];
        }

        // No triad note near the ending: take the closest one
        usort($triad, fn ($a, $b) => abs(($poolMidi[$a] ?? 60) - $endMidi) <=> abs(($poolMidi[$b] ?? 60) - $endMidi));

        return $triad[0];
    }

    /**
     * Weighted random walk from start to end. Returns null when it paints
     * itself into a corner so the caller can retry.
     *
     * @return string[]|null
     */
    private function buildMelody(
        int $noteCount,
        array $poolMidi,
        string $startNote,
        string $endNote,
        string $difficulty
    ): ?array {
        if ($noteCount <= 1) {
            return [$startNote];
        }
        if ($noteCount === 2) {
            return [$startNote, $endNote];
        }

        $notes = [$startNote];
        $prevMidi = $poolMidi[$startNote] ?? 60;
        $endMidi = $poolMidi[$endNote] ?? 60;
        $lastInterval = 0;
        $lastMoveDir = 0;
        $leapsUsed = 0;
        $maxRange = $this->maxRange($difficulty);
        $maxLeaps = $this->maxLeapCount($difficulty);
        // The forced ending counts toward range from the very first move
        $rangeMin = min($prevMidi, $endMidi);
        $rangeMax = max($prevMidi, $endMidi);

        for ($i = 1; $i < $noteCount - 1; $i++) {
            $isSecondToLast = ($i === $noteCount - 2);
            $candidates = [];

            foreach ($poolMidi as $note => $midi) {
                $dist = abs($midi - $prevMidi);
                if (max($rangeMax, $midi) - min($rangeMin, $midi) > $maxRange) {
                    continue;
                }

                if ($isSecondToLast) {
                    // The closing interval must be reachable and legal
                    $distToEnd = abs($midi - $endMidi);
                    $closingLeapBudget = $leapsUsed + ($dist >= 5 ? 1 : 0) < $maxLeaps && $dist < 5
                        ? $this->maxLeapSize($difficulty)
                        // never close with a leap right after another leap;
                        // beginner closes by step only
                        : min(4, $this->maxLeapSize($difficulty));
                    if ($distToEnd === 0 || $distToEnd > $closingLeapBudget || $distToEnd === 6) {
                        continue;
                    }
                    // A leap into the second-to-last note must resolve toward the end
                    if ($dist >= 5 && ! $this->leapResolves($midi > $prevMidi ? 1 : -1, $endMidi <=> $midi, $distToEnd, $difficulty)) {
                        continue;
                    }
                }

                $weight = $this->noteWeight($dist, $midi, $prevMidi, $difficulty, $lastInterval, $lastMoveDir, $leapsUsed);
                if ($weight <= 0.0) {
                    continue;
                }

                $candidates[] = ['note' => $note, 'midi' => $midi, 'weight' => $weight];
            }

            if (empty($candidates)) {
                return null;
            }

            $selected = $this->weightedRandomSelect($candidates);
            $notes[] = $selected['note'];
            $newMidi = $selected['midi'];
            $lastInterval = abs($newMidi - $prevMidi);
            $lastMoveDir = $newMidi <=> $prevMidi;
            if ($lastInterval >= 5) {
                $leapsUsed++;
            }
            $rangeMin = min($rangeMin, $newMidi);
            $rangeMax = max($rangeMax, $newMidi);
            $prevMidi = $newMidi;
        }

        $notes[] = $endNote;

        return $notes;
    }

    private function noteWeight(
        int $dist,
        int $midi,
        int $prevMidi,
        string $difficulty,
        int $lastInterval,
        int $lastMoveDir,
        int $leapsUsed
    ): float {
        $baseWeight = match (true) {
            // Repeated note: only at beginner (narrow steps-only pools would
            // otherwise deadlock on start/end parity), never twice in a row,
            // never straight after a leap.
            $dist === 0 => ($difficulty === 'beginner' && $lastInterval !== 0 && $lastInterval < 5) ? 2.5 : 0.0,
            $dist <= 2 => match ($difficulty) {
                'beginner' => 10.0, 'advanced' => 6.0, default => 8.0
            },
            // Thirds: forbidden at beginner (steps only)
            $dist <= 4 => match ($difficulty) {
                'beginner' => 0.0, 'advanced' => 5.0, default => 5.0
            },
            // 4ths: occasional at intermediate, freer at advanced
            $dist === 5 => match ($difficulty) {
                'beginner' => 0.0, 'advanced' => 3.0, default => 2.0
            },
            // Tritone: aug/dim melodic intervals need explicit config — never emit
            $dist === 6 => 0.0,
            // 5ths: rare at intermediate
            $dist === 7 => match ($difficulty) {
                'advanced' => 2.5, 'intermediate' => 0.6, default => 0.0
            },
            // 6ths and 7ths: occasional at advanced only
            $dist <= 9 => $difficulty === 'advanced' ? 1.0 : 0.0,
            $dist <= 11 => $difficulty === 'advanced' ? 0.5 : 0.0,
            // Octave+: would need explicit config — never emit
            default => 0.0,
        };

        if ($baseWeight <= 0.0) {
            return 0.0;
        }

        // Per-level leap budget already spent
        if ($dist >= 5 && $leapsUsed >= $this->maxLeapCount($difficulty)) {
            return 0.0;
        }

        // Never two leaps in a row
        if ($lastInterval >= 5 && $dist >= 5) {
            return 0.0;
        }

        // After a leap: strongly favor contrary stepwise resolution
        $moveDir = $midi > $prevMidi ? 1 : -1;
        if ($lastInterval >= 5 && $lastMoveDir !== 0) {
            if (! $this->leapResolves($lastMoveDir, $moveDir, $dist, $difficulty)) {
                return 0.0;
            }
            if ($moveDir === -$lastMoveDir && $dist <= 2) {
                $baseWeight *= 3.0;
            }
        }

        return $baseWeight;
    }

    private function weightedRandomSelect(array $candidates): array
    {
        $totalWeight = 0.0;
        foreach ($candidates as $c) {
            $totalWeight += $c['weight'];
        }

        if ($totalWeight <= 0.0) {
            return $candidates[array_rand($candidates)];
        }

        $rand = (mt_rand() / mt_getrandmax()) * $totalWeight;
        $cumulative = 0.0;

        foreach ($candidates as $candidate) {
            $cumulative += $candidate['weight'];
            if ($rand <= $cumulative) {
                return $candidate;
            }
        }

        return end($candidates);
    }

    /**
     * Last-resort melody: a stepwise line that oscillates near the start and
     * walks home to the ending in time. Valid by construction — every move is
     * one scale step, so no leap, ratio or resolution rule can fail.
     *
     * @return string[]
     */
    private function buildFallbackMelody(
        int $noteCount,
        array $poolMidi,
        string $startNote,
        string $endNote,
        string $difficulty
    ): array {
        if ($noteCount <= 1) {
            return [$startNote];
        }
        if ($noteCount === 2) {
            return [$startNote, $endNote];
        }

        $sorted = array_keys($poolMidi);
        usort($sorted, fn ($a, $b) => $poolMidi[$a] <=> $poolMidi[$b]);

        $idx = array_search($startNote, $sorted);
        $endIdx = array_search($endNote, $sorted);
        if ($idx === false) {
            $idx = 0;
        }
        if ($endIdx === false) {
            $endIdx = $idx;
        }

        $maxRange = $this->maxRange($difficulty);
        $endMidi = $poolMidi[$sorted[$endIdx]];
        $rangeMin = min($poolMidi[$sorted[$idx]], $endMidi);
        $rangeMax = max($poolMidi[$sorted[$idx]], $endMidi);
        $melody = [$sorted[$idx]];
        $up = true;
        $run = 0;

        $fits = function (int $i) use ($sorted, $poolMidi, $maxRange, &$rangeMin, &$rangeMax): bool {
            if ($i < 0 || $i >= count($sorted)) {
                return false;
            }
            $midi = $poolMidi[$sorted[$i]];

            return max($rangeMax, $midi) - min($rangeMin, $midi) <= $maxRange;
        };

        for ($i = 1; $i < $noteCount - 1; $i++) {
            $remaining = $noteCount - 1 - $i;
            if (abs($endIdx - $idx) >= $remaining) {
                // Out of slack: walk straight toward the ending
                $idx += $endIdx > $idx ? 1 : -1;
            } else {
                // Wave contour: runs of two steps per direction, so the line
                // doesn't alternate mechanically between two notes
                $next = $idx + ($up ? 1 : -1);
                if ($run >= 2 || ! $fits($next)) {
                    $up = ! $up;
                    $run = 0;
                    $next = $idx + ($up ? 1 : -1);
                    if (! $fits($next)) {
                        $next = $idx; // boxed in: repeat the note rather than break a rule
                    }
                }
                $idx = $next;
                $run++;
            }
            $rangeMin = min($rangeMin, $poolMidi[$sorted[$idx]]);
            $rangeMax = max($rangeMax, $poolMidi[$sorted[$idx]]);
            $melody[] = $sorted[$idx];
        }

        $melody[] = $endNote;

        return $melody;
    }

    // ── Accidentals ──────────────────────────────────────────────────────────

    /**
     * Post-process a generated diatonic melody to add tonally appropriate
     * accidentals outside the key signature, according to difficulty rules.
     *
     * Minor keys: harmonic minor (leading tone) and melodic minor ascending
     * behaviour are applied based on difficulty. Beginner adds a leading tone
     * ~35% of the time; intermediate always adds it and sometimes raises the 6th
     * as well (melodic minor ascending); advanced allows both with extra freedom.
     *
     * Major keys: up to one (intermediate) or two (advanced) chromatic approach
     * tones may be added; beginner stays fully diatonic.
     *
     * Always returns the unmodified melody if accidentals would introduce a
     * tritone or other invalid melodic interval.
     *
     * $flavor pins the accidental treatment for focused lessons (Learning Path):
     *   'none'     — fully diatonic: pure natural minor / no chromatic
     *                approach tones in major ('natural' is accepted as an alias)
     *   'harmonic' — minor: always raise the 7th on an ascending 7→1 approach
     *   'melodic'  — minor: always raise 6+7 in ascending 6–7–1 runs
     *   'auto'     — difficulty-based mix (Exercise Setup Studio behaviour)
     * Major mode honours 'none' and treats everything else as 'auto'.
     */
    public function applyAccidentals(
        array $melody,
        string $majorKeyRoot,
        string $mode,
        string $difficulty,
        string $flavor = 'auto'
    ): array {
        $difficulty = $this->normalizeDifficulty($difficulty);
        $scale = self::MAJOR_SCALES[$majorKeyRoot] ?? self::MAJOR_SCALES['C'];

        if ($flavor === 'none' || $flavor === 'natural') {
            return $melody;
        }

        if ($mode === 'minor') {
            $result = match ($flavor) {
                'harmonic' => $this->applyLeadingTone(
                    $melody, $scale[4], $this->raiseNoteName($scale[4]), $scale[5],
                    avoidAugSecond: $difficulty === 'beginner', natural6th: $scale[3]
                ),
                'melodic' => $this->applyMelodicMinorAscending(
                    $melody, $scale[3], $this->raiseNoteName($scale[3]),
                    $scale[4], $this->raiseNoteName($scale[4]), $scale[5]
                ),
                default => $this->applyMinorAccidentals($melody, $scale, $difficulty),
            };
        } elseif ($difficulty !== 'beginner') {
            $result = $this->applyMajorChromaticAccidentals($melody, $scale, $difficulty);
        } else {
            return $melody; // beginner major: fully diatonic
        }

        // Guard: never emit a tritone (aug4/dim5) or a leap wider than an octave
        // that was not already present in the diatonic source melody.
        if (! $this->accidentalIntervalsOk($result)) {
            return $melody;
        }

        return $result;
    }

    /**
     * Guarantee that a focused harmonic / melodic minor melody actually sounds
     * its signature accidental. applyAccidentals() only raises the 7th
     * (harmonic) or the 6th + 7th (melodic) where the diatonic melody already
     * ascends into the tonic (…7→1, or …6→7→1); many generated lines never
     * contain that approach, so the lesson's whole point — hearing the raised
     * leading tone / melodic-minor climb — would be silent in most questions.
     *
     * When a melody lacks the ascending approach, this rewrites its final notes
     * into a valid ascending cadence drawn from the lesson's own pool (…7→1 for
     * harmonic, …6→7→1 for melodic) so applyAccidentals() then always raises
     * them. Returns the melody unchanged when it already ascends into the tonic
     * (preserving natural variety) or when the pool / level cannot host a valid
     * cadence (the caller then rejects the candidate and retries).
     *
     * @param  string[]  $melody  diatonic melody, note names with octave
     * @param  string[]  $pool  the lesson's diatonic note pool
     * @return string[]
     */
    public function ensureMinorCadence(
        array $melody,
        string $majorKeyRoot,
        string $flavor,
        array $pool,
        string $difficulty = 'intermediate'
    ): array {
        if (! in_array($flavor, ['harmonic', 'melodic'], true)) {
            return $melody;
        }

        $difficulty = $this->normalizeDifficulty($difficulty);
        $scale = self::MAJOR_SCALES[$majorKeyRoot] ?? self::MAJOR_SCALES['C'];
        $tonicName = $scale[5];    // relative minor tonic, e.g. A
        $seventhName = $scale[4];  // natural 7th, e.g. G
        $sixthName = $scale[3];    // natural 6th, e.g. F

        $tailLen = $flavor === 'melodic' ? 3 : 2;
        $n = count($melody);
        if ($n < $tailLen) {
            return $melody;
        }

        // Already contains the lesson's signature ascent? Leave it untouched so
        // natural melodies keep their variety. Harmonic needs any ascending
        // 7→1; melodic needs the full 6→7→1 climb (so the raised 6th sounds,
        // not just the leading tone).
        if ($this->melodyHasMinorAscent($melody, $flavor, $sixthName, $seventhName, $tonicName)) {
            return $melody;
        }

        // Locate a tonic pitch in the pool with the natural 7th a whole step
        // below it (and, for melodic minor, the natural 6th below that), so the
        // forced cadence stays inside the lesson register. Prefer the highest
        // tonic so its lower neighbours are most likely to exist in the pool.
        $midiToNote = [];
        foreach ($pool as $p) {
            $midiToNote[$this->noteToMidi($p)] = $p;
        }
        $tonicMidis = [];
        foreach ($pool as $p) {
            if ($this->getNoteNamePart($p) === $tonicName) {
                $tonicMidis[] = $this->noteToMidi($p);
            }
        }
        rsort($tonicMidis);

        foreach ($tonicMidis as $tonicMidi) {
            $seventhMidi = $tonicMidi - 2;  // whole step below the tonic
            if (! isset($midiToNote[$seventhMidi])) {
                continue;
            }
            $tail = [$midiToNote[$seventhMidi], $midiToNote[$tonicMidi]];

            if ($flavor === 'melodic') {
                $sixthMidi = $tonicMidi - 4;  // whole step below the 7th
                if (! isset($midiToNote[$sixthMidi])) {
                    continue;
                }
                array_unshift($tail, $midiToNote[$sixthMidi]);
            }

            $candidate = $melody;
            array_splice($candidate, $n - $tailLen, $tailLen, $tail);

            // The forced tail must not break the level's motion/range rules
            // where it joins the body of the melody.
            if ($this->validateMelody($candidate, $difficulty)) {
                return $candidate;
            }
        }

        return $melody;
    }

    /**
     * Whether a diatonic minor melody already contains the ascending approach
     * that applyAccidentals() raises for the given flavor: any ascending 7→1
     * step (harmonic) or a strictly ascending 6→7→1 run (melodic).
     *
     * @param  string[]  $melody
     */
    private function melodyHasMinorAscent(
        array $melody,
        string $flavor,
        string $sixthName,
        string $seventhName,
        string $tonicName
    ): bool {
        $n = count($melody);

        if ($flavor === 'melodic') {
            for ($i = 0; $i < $n - 2; $i++) {
                if ($this->getNoteNamePart($melody[$i]) === $sixthName
                    && $this->getNoteNamePart($melody[$i + 1]) === $seventhName
                    && $this->getNoteNamePart($melody[$i + 2]) === $tonicName
                    && $this->noteToMidi($melody[$i + 1]) > $this->noteToMidi($melody[$i])
                    && $this->noteToMidi($melody[$i + 2]) > $this->noteToMidi($melody[$i + 1])
                ) {
                    return true;
                }
            }

            return false;
        }

        for ($i = 0; $i < $n - 1; $i++) {
            if ($this->getNoteNamePart($melody[$i]) === $seventhName
                && $this->getNoteNamePart($melody[$i + 1]) === $tonicName
                && $this->noteToMidi($melody[$i + 1]) > $this->noteToMidi($melody[$i])
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Confirm a post-accidental minor melody actually sounds its signature
     * degree: the raised leading tone (harmonic) or the raised 6th of the
     * melodic-minor climb (melodic — implies the raised 7th sounds too). The
     * Learning Path generator rejects candidates that fail this so no focused
     * minor question is left without the accidental it teaches.
     *
     * @param  string[]  $melody
     */
    public function melodyMeetsMinorFlavor(array $melody, string $majorKeyRoot, string $flavor): bool
    {
        if (! in_array($flavor, ['harmonic', 'melodic'], true)) {
            return true;
        }

        $scale = self::MAJOR_SCALES[$majorKeyRoot] ?? self::MAJOR_SCALES['C'];
        $required = $flavor === 'melodic'
            ? $this->raiseNoteName($scale[3])   // raised 6th, e.g. F#
            : $this->raiseNoteName($scale[4]);  // leading tone, e.g. G#

        foreach ($melody as $note) {
            if ($this->getNoteNamePart($note) === $required) {
                return true;
            }
        }

        return false;
    }

    /**
     * Apply harmonic / melodic minor accidentals based on difficulty:
     *  beginner    — ~35 % include leading tone only
     *  intermediate — always leading tone; ~40 % melodic minor ascending as well
     *  advanced    — ~50 % melodic minor, ~50 % harmonic minor (leading tone)
     */
    private function applyMinorAccidentals(array $melody, array $scale, string $difficulty): array
    {
        $natural7th = $scale[4];                          // e.g. G  in A minor
        $leadingTone = $this->raiseNoteName($natural7th);  // e.g. G#
        $natural6th = $scale[3];                          // e.g. F  in A minor
        $raised6th = $this->raiseNoteName($natural6th);  // e.g. F#
        $tonicName = $scale[5];                          // minor tonic, e.g. A

        if ($difficulty === 'beginner') {
            if (mt_rand(0, 99) >= 35) {
                return $melody; // ~65 % stay in natural minor
            }

            return $this->applyLeadingTone(
                $melody, $natural7th, $leadingTone, $tonicName,
                avoidAugSecond: true, natural6th: $natural6th
            );
        }

        if ($difficulty === 'intermediate') {
            if (mt_rand(0, 99) < 40) {
                return $this->applyMelodicMinorAscending(
                    $melody, $natural6th, $raised6th, $natural7th, $leadingTone, $tonicName
                );
            }

            return $this->applyLeadingTone(
                $melody, $natural7th, $leadingTone, $tonicName,
                avoidAugSecond: false, natural6th: $natural6th
            );
        }

        // Advanced: ~50 % melodic minor, ~50 % harmonic minor
        if (mt_rand(0, 99) < 50) {
            return $this->applyMelodicMinorAscending(
                $melody, $natural6th, $raised6th, $natural7th, $leadingTone, $tonicName
            );
        }

        return $this->applyLeadingTone(
            $melody, $natural7th, $leadingTone, $tonicName,
            avoidAugSecond: false, natural6th: $natural6th
        );
    }

    /**
     * Raise the natural 7th to the leading tone wherever it immediately
     * precedes the tonic in ascending pitch motion.
     *
     * @param  bool  $avoidAugSecond  When true, skip raising if the
     *                                preceding note is the natural 6th
     *                                (prevents an aug-2nd at beginner level).
     * @param  string  $natural6th  Name of the natural 6th (needed only
     *                              when $avoidAugSecond is true).
     */
    private function applyLeadingTone(
        array $melody,
        string $natural7th,
        string $leadingTone,
        string $tonicName,
        bool $avoidAugSecond,
        string $natural6th = ''
    ): array {
        $result = $melody;
        $n = count($result);

        for ($i = 0; $i < $n - 1; $i++) {
            $noteName = $this->getNoteNamePart($result[$i]);
            $nextName = $this->getNoteNamePart($result[$i + 1]);

            if ($noteName !== $natural7th || $nextName !== $tonicName) {
                continue;
            }

            // The 7th → tonic move must be ascending in actual pitch
            if ($this->noteToMidi($result[$i + 1]) <= $this->noteToMidi($result[$i])) {
                continue;
            }

            // At beginner level: avoid aug-2nd between natural-6th and raised-7th
            if ($avoidAugSecond && $natural6th !== '' && $i > 0
                && $this->getNoteNamePart($result[$i - 1]) === $natural6th
            ) {
                continue;
            }

            $result[$i] = $leadingTone.$this->getOctavePart($result[$i]);
        }

        return $result;
    }

    /**
     * In any strictly ascending 6–7–1 run, raise both the 6th (melodic minor
     * form) and the 7th (leading tone). Then also apply the plain leading tone
     * for any remaining 7–1 approach elsewhere in the melody.
     */
    private function applyMelodicMinorAscending(
        array $melody,
        string $natural6th,
        string $raised6th,
        string $natural7th,
        string $leadingTone,
        string $tonicName
    ): array {
        $result = $melody;
        $n = count($result);

        for ($i = 0; $i < $n - 2; $i++) {
            $name0 = $this->getNoteNamePart($result[$i]);
            $name1 = $this->getNoteNamePart($result[$i + 1]);
            $name2 = $this->getNoteNamePart($result[$i + 2]);

            if ($name0 !== $natural6th || $name1 !== $natural7th || $name2 !== $tonicName) {
                continue;
            }

            $midi0 = $this->noteToMidi($result[$i]);
            $midi1 = $this->noteToMidi($result[$i + 1]);
            $midi2 = $this->noteToMidi($result[$i + 2]);

            if ($midi0 >= $midi1 || $midi1 >= $midi2) {
                continue; // only raise in strictly ascending motion
            }

            $result[$i] = $raised6th.$this->getOctavePart($result[$i]);
            $result[$i + 1] = $leadingTone.$this->getOctavePart($result[$i + 1]);
        }

        // Also apply the plain leading tone in any remaining 7→1 approach
        return $this->applyLeadingTone(
            $result, $natural7th, $leadingTone, $tonicName,
            avoidAugSecond: false, natural6th: $natural6th
        );
    }

    /**
     * Add up to one (intermediate) or two (advanced) chromatic approach-tone
     * alterations to a major-key melody.
     *
     * A "chromatic approach tone" here is a diatonic note raised by one
     * semitone so that it becomes a half-step approach to the next diatonic
     * note (which must be exactly a whole step higher in the original melody).
     * The tonic and dominant are left unaltered to preserve tonal stability.
     */
    private function applyMajorChromaticAccidentals(
        array $melody,
        array $scale,
        string $difficulty
    ): array {
        $maxAccidentals = $difficulty === 'advanced' ? 2 : 1;
        $n = count($melody);

        // Collect inner candidate positions (not first, not last)
        $candidates = [];
        for ($i = 1; $i < $n - 1; $i++) {
            $currentName = $this->getNoteNamePart($melody[$i]);
            $nextName = $this->getNoteNamePart($melody[$i + 1]);

            if (! in_array($currentName, $scale, true) || ! in_array($nextName, $scale, true)) {
                continue;
            }
            // Leave tonic and dominant unaltered
            if ($currentName === $scale[0] || $currentName === $scale[4]) {
                continue;
            }
            // Must be ascending by exactly a whole step for a proper approach
            if ($this->noteToMidi($melody[$i + 1]) - $this->noteToMidi($melody[$i]) !== 2) {
                continue;
            }

            $candidates[] = $i;
        }

        if (empty($candidates)) {
            return $melody;
        }

        shuffle($candidates);
        $candidates = array_slice($candidates, 0, $maxAccidentals);
        sort($candidates);

        $result = $melody;
        $usedPitchClasses = [];
        $applied = 0;

        foreach ($candidates as $i) {
            if ($applied >= $maxAccidentals) {
                break;
            }
            $currentName = $this->getNoteNamePart($result[$i]);
            $raised = $this->raiseNoteName($currentName);

            if (! in_array($raised, $usedPitchClasses, true)) {
                $usedPitchClasses[] = $raised;
                $applied++;
            }

            $result[$i] = $raised.$this->getOctavePart($result[$i]);
        }

        return $result;
    }

    /**
     * After applying accidentals, confirm that no tritone (6-semitone) or
     * wider-than-octave leap has been introduced between adjacent melody notes.
     */
    private function accidentalIntervalsOk(array $melody): bool
    {
        $n = count($melody);
        for ($i = 1; $i < $n; $i++) {
            $dist = abs($this->noteToMidi($melody[$i]) - $this->noteToMidi($melody[$i - 1]));
            if ($dist === 6 || $dist > 12) {
                return false;
            }
        }

        return true;
    }

    /** Extract the note name (letter + accidental) from a note-with-octave string. */
    private function getNoteNamePart(string $noteWithOctave): string
    {
        if (preg_match('/^([A-G](?:bb|##|b|#)?)(\d+)$/i', $noteWithOctave, $m)) {
            return $m[1];
        }

        return $noteWithOctave;
    }

    /** Extract the octave digit from a note-with-octave string. */
    private function getOctavePart(string $noteWithOctave): string
    {
        if (preg_match('/^([A-G](?:bb|##|b|#)?)(\d+)$/i', $noteWithOctave, $m)) {
            return $m[2];
        }

        return '4';
    }

    /**
     * Raise a note name (letter + accidental, without octave) by one semitone.
     *   ''   → '#'   (e.g., G  → G# )
     *   '#'  → '##'  (e.g., F# → F##)
     *   'b'  → ''    (e.g., Bb → B natural)
     *   'bb' → 'b'   (e.g., Ebb → Eb)
     */
    private function raiseNoteName(string $noteName): string
    {
        if (! preg_match('/^([A-G])(bb|##|b|#)?$/i', $noteName, $m)) {
            return $noteName;
        }

        $letter = strtoupper($m[1]);
        $acc = $m[2] ?? '';

        $raisedAcc = match ($acc) {
            'b' => '',
            'bb' => 'b',
            '#' => '##',
            '##' => '##', // already at maximum — leave as-is
            default => '#',
        };

        return $letter.$raisedAcc;
    }

    // ── Note helpers ─────────────────────────────────────────────────────────

    public function noteToMidi(string $noteWithOctave): int
    {
        $noteMap = ['C' => 0, 'D' => 2, 'E' => 4, 'F' => 5, 'G' => 7, 'A' => 9, 'B' => 11];

        if (! preg_match('/^([A-G])(bb|##|b|#)?(\d+)$/i', $noteWithOctave, $m)) {
            return 60;
        }

        $base = $noteMap[strtoupper($m[1])] ?? 0;
        $acc = match ($m[2] ?? '') {
            '#' => 1, '##' => 2, 'b' => -1, 'bb' => -2, default => 0
        };

        return ($m[3] + 1) * 12 + $base + $acc;
    }

    private function clefMidiRange(string $clef): array
    {
        return match ($clef) {
            'bass' => [36, 60],   // C2 → C4
            'alto' => [48, 72],   // C3 → C5
            default => [55, 79],  // G3 → G5 (treble)
        };
    }

    /** "C#5" matches name "C#" but not "C". */
    private function noteNameMatches(string $noteWithOctave, string $name): bool
    {
        return strlen($noteWithOctave) === strlen($name) + 1
            && str_starts_with($noteWithOctave, $name);
    }

    /**
     * All pool notes with the given name, reduced to the middle octave so
     * anchor notes sit in the comfortable center of the register.
     *
     * @return string[]
     */
    private function pickMiddleOfName(string $name, array $notePool): array
    {
        $matches = [];
        foreach ($notePool as $n) {
            if ($this->noteNameMatches($n, $name)) {
                $matches[] = $n;
            }
        }

        if (count($matches) > 1) {
            usort($matches, fn ($a, $b) => $this->noteToMidi($a) <=> $this->noteToMidi($b));

            return [$matches[intdiv(count($matches), 2)]];
        }

        return $matches ?: (empty($notePool) ? [] : [$notePool[0]]);
    }
}
