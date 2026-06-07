<?php

namespace App\Services;

class MusicTheoryService
{
    // Interval name → semitone count (canonical source for all subsystems)
    public const INTERVAL_SEMITONES = [
        'Perfect Unison' => 0,
        'Minor 2nd' => 1,
        'Major 2nd' => 2,
        'Minor 3rd' => 3,
        'Major 3rd' => 4,
        'Perfect 4th' => 5,
        'Tritone' => 6,
        'Augmented 4th' => 6,
        'Diminished 5th' => 6,
        'Perfect 5th' => 7,
        'Minor 6th' => 8,
        'Major 6th' => 9,
        'Minor 7th' => 10,
        'Major 7th' => 11,
        'Perfect Octave' => 12,
    ];

    // Note name → chromatic index 0-11
    public const NOTE_SEMITONES = [
        'C' => 0, 'C#' => 1, 'Db' => 1,
        'D' => 2, 'D#' => 3, 'Eb' => 3,
        'E' => 4,
        'F' => 5, 'F#' => 6, 'Gb' => 6,
        'G' => 7, 'G#' => 8, 'Ab' => 8,
        'A' => 9, 'A#' => 10, 'Bb' => 10,
        'B' => 11,
    ];

    // Canonical sharp-based chromatic scale (index 0-11)
    public const CHROMATIC_NOTES = ['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'];

    // Semitone count → canonical interval name
    private const SEMITONES_TO_INTERVAL = [
        0 => 'Perfect Unison',
        1 => 'Minor 2nd',
        2 => 'Major 2nd',
        3 => 'Minor 3rd',
        4 => 'Major 3rd',
        5 => 'Perfect 4th',
        6 => 'Tritone',
        7 => 'Perfect 5th',
        8 => 'Minor 6th',
        9 => 'Major 6th',
        10 => 'Minor 7th',
        11 => 'Major 7th',
        12 => 'Perfect Octave',
    ];

    // Interval aliases that should be normalized to canonical names
    private const INTERVAL_ALIASES = [
        'augmented 4th' => 'Tritone',
        'diminished 5th' => 'Tritone',
        'tritone' => 'Tritone',
    ];

    // Interval name → letter degree (1=unison, 2=2nd, 3=3rd, …)
    private const INTERVAL_DEGREES = [
        'Perfect Unison' => 1,
        'Minor 2nd' => 2,
        'Major 2nd' => 2,
        'Minor 3rd' => 3,
        'Major 3rd' => 3,
        'Perfect 4th' => 4,
        'Augmented 4th' => 4,
        'Tritone' => 4,
        'Diminished 5th' => 5,
        'Perfect 5th' => 5,
        'Minor 6th' => 6,
        'Major 6th' => 6,
        'Minor 7th' => 7,
        'Major 7th' => 7,
        'Perfect Octave' => 8,
    ];

    // Letters in diatonic order and their natural semitone offsets from C
    private const LETTER_ORDER = ['C', 'D', 'E', 'F', 'G', 'A', 'B'];

    private const LETTER_NATURAL = ['C' => 0, 'D' => 2, 'E' => 4, 'F' => 5, 'G' => 7, 'A' => 9, 'B' => 11];

    /**
     * Compute MIDI note number. C4 (middle C) = 60.
     * Formula: (octave + 1) * 12 + chromatic_index
     */
    public function midiNumber(string $note, int $octave): ?int
    {
        $index = self::NOTE_SEMITONES[$note] ?? null;
        if ($index === null) {
            return null;
        }

        return ($octave + 1) * 12 + $index;
    }

    /**
     * Same-octave semitone span of a "note1,note2" pair — how the staff and
     * audio actually present an interval-comparison pair (both notes share one
     * octave). Returns null if the pair is malformed or uses unknown notes.
     */
    public function intervalPairSemitones(string $pair): ?int
    {
        $parts = array_map('trim', explode(',', $pair));
        if (count($parts) !== 2) {
            return null;
        }
        $a = self::NOTE_SEMITONES[$parts[0]] ?? null;
        $b = self::NOTE_SEMITONES[$parts[1]] ?? null;
        if ($a === null || $b === null) {
            return null;
        }

        return abs($b - $a);
    }

    /**
     * Which of two interval pairs is larger: 'a', 'b', or null when they are
     * equal in size or either pair is invalid (an unanswerable comparison).
     */
    public function largerIntervalPair(string $pairA, string $pairB): ?string
    {
        $sa = $this->intervalPairSemitones($pairA);
        $sb = $this->intervalPairSemitones($pairB);
        if ($sa === null || $sb === null || $sa === $sb) {
            return null;
        }

        return $sa > $sb ? 'a' : 'b';
    }

    /**
     * Convert MIDI number back to ['note' => string, 'octave' => int].
     */
    public function noteFromMidi(int $midi): array
    {
        $octave = (int) floor($midi / 12) - 1;
        $index = $midi % 12;

        return ['note' => self::CHROMATIC_NOTES[$index], 'octave' => $octave];
    }

    /**
     * Determine the direction from note1 to note2.
     * Returns 'ascending', 'descending', or 'unison'.
     * This is the canonical, pitch-aware implementation — direction is derived
     * from actual MIDI pitch values, never from a stored label.
     */
    public function getDirection(string $note1, int $octave1, string $note2, int $octave2): string
    {
        $midi1 = $this->midiNumber($note1, $octave1);
        $midi2 = $this->midiNumber($note2, $octave2);

        if ($midi1 === null || $midi2 === null) {
            return 'unison';
        }

        if ($midi2 > $midi1) {
            return 'ascending';
        }
        if ($midi2 < $midi1) {
            return 'descending';
        }

        return 'unison';
    }

    /**
     * Signed semitone distance from note1 to note2.
     * Positive = ascending, negative = descending, zero = unison.
     */
    public function semitonesBetween(string $note1, int $octave1, string $note2, int $octave2): int
    {
        $midi1 = $this->midiNumber($note1, $octave1) ?? 0;
        $midi2 = $this->midiNumber($note2, $octave2) ?? 0;

        return $midi2 - $midi1;
    }

    /**
     * Map absolute semitone count (0-12) to a canonical interval name.
     * Returns null for out-of-range values.
     */
    public function intervalNameFromSemitones(int $semitones): ?string
    {
        return self::SEMITONES_TO_INTERVAL[$semitones] ?? null;
    }

    /**
     * Compute the diatonic note that is the named interval ABOVE $note.
     * Uses proper letter-name counting so the result may contain flats,
     * double-sharps, or double-flats (e.g. Major 7th above F# → E#).
     * Returns ['note' => string, 'octave' => int] or null for unknown inputs.
     */
    public function diatonicNoteAboveByInterval(string $note, int $octave, string $intervalName): ?array
    {
        $degree = self::INTERVAL_DEGREES[$intervalName] ?? null;
        $semitones = self::INTERVAL_SEMITONES[$intervalName] ?? null;

        if ($degree === null || $semitones === null) {
            return null;
        }

        // Parse root note letter and accidental offset
        if (! preg_match('/^([A-G])(#{1,2}|b{1,2}|x)?$/i', $note, $m)) {
            return null;
        }
        $rootLetter = strtoupper($m[1]);
        $accStr = $m[2] ?? '';
        $rootAccOff = $this->accidentalOffset($accStr);

        $rootLetterIdx = array_search($rootLetter, self::LETTER_ORDER, true);
        if ($rootLetterIdx === false) {
            return null;
        }

        $rawIdx = $rootLetterIdx + ($degree - 1);
        $targetLetterIdx = $rawIdx % 7;
        $octaveIncrement = (int) floor($rawIdx / 7);
        $targetLetter = self::LETTER_ORDER[$targetLetterIdx];

        $rootMidi = ($octave + 1) * 12 + self::LETTER_NATURAL[$rootLetter] + $rootAccOff;
        $targetMidi = $rootMidi + $semitones;
        $targetOctave = $octave + $octaveIncrement;
        $targetLetterMidi = ($targetOctave + 1) * 12 + self::LETTER_NATURAL[$targetLetter];
        $accOffset = $targetMidi - $targetLetterMidi;

        $accidental = match ($accOffset) {
            0 => '',
            1 => '#',
            2 => '##',
            -1 => 'b',
            -2 => 'bb',
            default => ($accOffset > 0 ? str_repeat('#', $accOffset) : str_repeat('b', -$accOffset)),
        };

        return ['note' => $targetLetter.$accidental, 'octave' => $targetOctave];
    }

    /**
     * Spell a note diatonically from a root: the note that is `$letterStep` letter-names
     * and `$semitones` semitones above `$rootNote` at `$octave`. This lets scales and
     * chords show the correct accidental — flats where the spelling calls for them
     * (e.g. 2 letters + 3 semitones above C → Eb, not D#; augmented 5th above C → G#).
     *
     * Returns ['note' => 'Eb', 'octave' => 4]. To avoid confusing double accidentals,
     * a result that would need ##/bb falls back to a single-accidental (flat-preferred)
     * enharmonic spelling. Returns null for an unparseable root.
     */
    public function spellNote(string $rootNote, int $octave, int $letterStep, int $semitones): ?array
    {
        if (! preg_match('/^([A-G])(#{1,2}|b{1,2}|x)?$/i', trim($rootNote), $m)) {
            return null;
        }
        $rootLetter = strtoupper($m[1]);
        $rootAccOff = $this->accidentalOffset($m[2] ?? '');

        $rootLetterIdx = array_search($rootLetter, self::LETTER_ORDER, true);
        if ($rootLetterIdx === false) {
            return null;
        }

        $rawIdx = $rootLetterIdx + $letterStep;
        $targetLetterIdx = (($rawIdx % 7) + 7) % 7;
        $octaveIncrement = (int) floor($rawIdx / 7);
        $targetLetter = self::LETTER_ORDER[$targetLetterIdx];

        $rootMidi = ($octave + 1) * 12 + self::LETTER_NATURAL[$rootLetter] + $rootAccOff;
        $targetMidi = $rootMidi + $semitones;
        $targetOctave = $octave + $octaveIncrement;
        $targetLetterMidi = ($targetOctave + 1) * 12 + self::LETTER_NATURAL[$targetLetter];
        $accOffset = $targetMidi - $targetLetterMidi;

        // Double accidental (e.g. a diminished-7th's bb7) → readable single-flat enharmonic.
        if ($accOffset < -1 || $accOffset > 1) {
            $flats = ['C', 'Db', 'D', 'Eb', 'E', 'F', 'Gb', 'G', 'Ab', 'A', 'Bb', 'B'];
            $pc = (($targetMidi % 12) + 12) % 12;

            return ['note' => $flats[$pc], 'octave' => intdiv($targetMidi, 12) - 1];
        }

        $accidental = $accOffset === 1 ? '#' : ($accOffset === -1 ? 'b' : '');

        return ['note' => $targetLetter.$accidental, 'octave' => $targetOctave];
    }

    /**
     * Preferred display spelling for the note a named interval above $note.
     *
     * Uses proper diatonic spelling (so flats appear where the interval calls
     * for them, e.g. Minor 3rd above C → Eb). If the diatonic result needs a
     * double accidental (##, x, bb) — which only happens from accidental roots
     * — it falls back to the simpler chromatic (sharp) spelling to avoid
     * confusing output. Returns ['note' => string, 'octave' => int] or null.
     */
    public function preferredNoteAboveByInterval(string $note, int $octave, string $intervalName): ?array
    {
        $diatonic = $this->diatonicNoteAboveByInterval($note, $octave, $intervalName);

        if ($diatonic === null) {
            return $this->noteAboveByInterval($note, $octave, $intervalName);
        }

        if (preg_match('/(##|bb|x)$/i', $diatonic['note'])) {
            return $this->noteAboveByInterval($note, $octave, $intervalName) ?? $diatonic;
        }

        return $diatonic;
    }

    /**
     * Return the chromatic index (0–11) of a note name, supporting extended
     * accidentals: #, ##, x, b, bb.  Returns null for unrecognised input.
     */
    public function parseNoteChromatic(string $note): ?int
    {
        if (! preg_match('/^([A-G])(#{1,2}|b{1,2}|x)?$/i', trim($note), $m)) {
            // Also check existing NOTE_SEMITONES map for standard notes
            return self::NOTE_SEMITONES[ucfirst(strtolower($note))] ?? null;
        }
        $letter = strtoupper($m[1]);
        $acc = $m[2] ?? '';
        $base = self::LETTER_NATURAL[$letter] ?? null;
        if ($base === null) {
            return null;
        }

        return ((($base + $this->accidentalOffset($acc)) % 12) + 12) % 12;
    }

    /**
     * Returns true if two note names represent the same pitch class (enharmonic).
     */
    public function notesAreEnharmonic(string $a, string $b): bool
    {
        $ca = $this->parseNoteChromatic($a);
        $cb = $this->parseNoteChromatic($b);

        return $ca !== null && $cb !== null && $ca === $cb;
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function accidentalOffset(string $acc): int
    {
        return match ($acc) {
            '#' => 1,
            '##', 'x' => 2,
            'b' => -1,
            'bb' => -2,
            default => 0,
        };
    }

    /**
     * Calculate the note that is exactly $semitones above $note at $octave.
     * Returns ['note' => string, 'octave' => int].
     */
    public function noteAboveBySemitones(string $note, int $octave, int $semitones): ?array
    {
        $midi = $this->midiNumber($note, $octave);
        if ($midi === null) {
            return null;
        }

        return $this->noteFromMidi($midi + $semitones);
    }

    /**
     * Calculate the note that is exactly $semitones below $note at $octave.
     */
    public function noteBelowBySemitones(string $note, int $octave, int $semitones): ?array
    {
        $midi = $this->midiNumber($note, $octave);
        if ($midi === null) {
            return null;
        }

        return $this->noteFromMidi($midi - $semitones);
    }

    /**
     * Calculate the note that is the named interval above $note at $octave.
     * Returns ['note' => string, 'octave' => int] or null for unknown interval names.
     */
    public function noteAboveByInterval(string $note, int $octave, string $intervalName): ?array
    {
        $semitones = self::INTERVAL_SEMITONES[$intervalName] ?? null;
        if ($semitones === null) {
            return null;
        }

        return $this->noteAboveBySemitones($note, $octave, $semitones);
    }

    /**
     * Calculate the note that is the named interval below $note at $octave.
     */
    public function noteBelowByInterval(string $note, int $octave, string $intervalName): ?array
    {
        $semitones = self::INTERVAL_SEMITONES[$intervalName] ?? null;
        if ($semitones === null) {
            return null;
        }

        return $this->noteBelowBySemitones($note, $octave, $semitones);
    }

    /**
     * Given note1 at octave1 and a bare note name for note2, return the octave
     * for note2 that places it in the stated direction relative to note1.
     *
     * Uses the "nearest neighbour" rule: ascending → smallest octave of note2
     * that is strictly above note1; descending → largest octave strictly below.
     *
     * This is the canonical fix for the AI / cross-octave bug: OpenAI (and the
     * old generator) would store a single shared octave, producing e.g. D#4
     * when they intended D#5 (ascending from B4).
     */
    public function resolveNote2OctaveFromDirection(string $note1, int $octave1, string $note2, string $direction): int
    {
        $idx1 = self::NOTE_SEMITONES[$note1] ?? null;
        $idx2 = self::NOTE_SEMITONES[$note2] ?? null;

        if ($idx1 === null || $idx2 === null) {
            return $octave1;
        }

        // Unison — direction is ambiguous, keep same octave
        if ($note1 === $note2 || $this->normalizeNote($note1) === $this->normalizeNote($note2)) {
            return $octave1;
        }

        if ($direction === 'ascending') {
            // Same octave already higher → stay; otherwise bump up one
            $octave2 = $idx2 > $idx1 ? $octave1 : $octave1 + 1;
        } else {
            // Same octave already lower → stay; otherwise drop one
            $octave2 = $idx2 < $idx1 ? $octave1 : $octave1 - 1;
        }

        return max(1, min(8, $octave2));
    }

    /**
     * Parse a combined note+octave string like "C4" or "D#5".
     * Returns ['note' => string, 'octave' => int] or null if unparseable.
     */
    public function parseNoteOctave(string $combined): ?array
    {
        if (preg_match('/^([A-Ga-g][#b]?)(\d+)$/', trim($combined), $m)) {
            $note = strtoupper($m[1][0]).($m[1][1] ?? '');

            return ['note' => $note, 'octave' => (int) $m[2]];
        }

        return null;
    }

    /**
     * Normalize an interval name to its canonical form.
     * "Augmented 4th" and "Diminished 5th" → "Tritone".
     */
    public function normalizeIntervalName(string $interval): string
    {
        $lower = strtolower(trim($interval));

        return self::INTERVAL_ALIASES[$lower] ?? $interval;
    }

    /**
     * Normalize a note name to the canonical sharp-based form.
     * "Db" → "C#", "Eb" → "D#", etc.
     */
    public function normalizeNote(string $note): string
    {
        $index = self::NOTE_SEMITONES[$note] ?? null;
        if ($index === null) {
            return $note;
        }

        return self::CHROMATIC_NOTES[$index];
    }

    /**
     * Extract the canonical correct answer from serialized question data.
     * Single point of truth for answer extraction across all practice types.
     */
    public function getAnswerFromQuestion(array $data, string $type): string
    {
        return match ($type) {
            'melodic-interval-practice',
            'harmonic-interval-practice' => $this->normalizeIntervalName($data['interval'] ?? ''),
            'interval-direction-practice' => $data['direction'] ?? '',
            'interval-construction-practice' => $data['note2'] ?? '',
            'interval-comparison-practice' => $data['target'] ?? '',
            'chord-practice' => $data['chord_type'] ?? '',
            'scale-practice' => $data['scale_type'] ?? '',
            'rhythm-practice' => $this->decodeArrayField($data['note_values'] ?? null),
            'melodic-dictation' => $this->decodeArrayField($data['notes'] ?? null),
            'single-note-practice' => $data['target'] ?? '',
            default => '',
        };
    }

    /**
     * Resolve the canonical interval name to attribute a single answered
     * question to, for per-interval accuracy tracking. Returns null for
     * non-interval practice types or questions that cannot be resolved.
     *
     * Mapping per type:
     *   - melodic / harmonic / construction: the stored `interval` name
     *   - direction: the absolute semitone distance actually played
     *   - comparison: the LARGER (target) interval of the pair
     */
    public function intervalForStats(array $data, string $type): ?string
    {
        switch ($type) {
            case 'melodic-interval-practice':
            case 'harmonic-interval-practice':
            case 'interval-construction-practice':
                $name = $this->normalizeIntervalName($data['interval'] ?? '');

                return $name !== '' ? $name : null;

            case 'interval-direction-practice':
                $octave1 = (int) ($data['octave'] ?? 4);
                $octave2 = (int) ($data['note2_octave'] ?? $octave1);
                $semitones = abs($this->semitonesBetween(
                    $data['note1'] ?? 'C',
                    $octave1,
                    $data['note2'] ?? 'C',
                    $octave2
                ));

                return $this->intervalNameFromSemitones($semitones);

            case 'interval-comparison-practice':
                $pair = ($data['target'] ?? 'a') === 'b'
                    ? ($data['interval_b'] ?? '')
                    : ($data['interval_a'] ?? '');
                $semitones = $this->intervalPairSemitones($pair);

                return $semitones === null ? null : $this->intervalNameFromSemitones($semitones);
        }

        return null;
    }

    /**
     * Build distractor options: shuffle pool, exclude correct, take $count.
     * Guarantees correct never appears in the returned array.
     */
    public function buildOptions(string $correct, array $pool, int $count): array
    {
        $others = array_values(array_filter($pool, fn ($v) => $this->normalizeIntervalName($v) !== $this->normalizeIntervalName($correct) && $v !== $correct));
        shuffle($others);

        return array_slice($others, 0, $count);
    }

    /**
     * Rank a pool of interval names by closeness (in semitones) to $correct,
     * closest first. Excludes the correct interval and any same-semitone alias.
     * Used for "near" distractor mode (hardest — adjacent interval sizes).
     */
    public function intervalNamesByCloseness(string $correct, array $pool): array
    {
        $target = self::INTERVAL_SEMITONES[$this->normalizeIntervalName($correct)]
            ?? (self::INTERVAL_SEMITONES[$correct] ?? null);
        if ($target === null) {
            return [];
        }

        $ranked = [];
        foreach ($pool as $name) {
            $st = self::INTERVAL_SEMITONES[$name] ?? null;
            if ($st === null || $st === $target) {
                continue;
            }
            $ranked[$name] = abs($st - $target);
        }
        asort($ranked);

        return array_keys($ranked);
    }

    /**
     * Rank a pool of note names by chromatic closeness to $correct, closest
     * first, using circular (mod-12) semitone distance. Excludes the correct
     * pitch (and enharmonic equivalents). Used for "near" distractor mode.
     */
    public function notesByCloseness(string $correct, array $pool): array
    {
        $target = self::NOTE_SEMITONES[$correct] ?? null;
        if ($target === null) {
            return [];
        }

        $ranked = [];
        foreach ($pool as $note) {
            $idx = self::NOTE_SEMITONES[$note] ?? null;
            if ($idx === null || $idx === $target) {
                continue;
            }
            $d = abs($idx - $target);
            $ranked[$note] = min($d, 12 - $d);
        }
        asort($ranked);

        return array_keys($ranked);
    }

    /**
     * Validate a serialized question array for internal consistency.
     *
     * Returns:
     *   'valid'    => bool   — true if all checks pass
     *   'status'   => string — 'valid' | 'invalid' | 'needs_review'
     *   'issues'   => array  — list of issue keys: 'direction_mismatch', 'answer_mismatch', 'unknown_note', etc.
     */
    public function validateQuestionConsistency(array $question, string $type): array
    {
        $issues = [];

        $note1 = $question['note1'] ?? null;
        $octave = isset($question['octave']) ? (int) $question['octave'] : null;
        $note2 = $question['note2'] ?? null;
        $note2Octave = isset($question['note2_octave']) ? (int) $question['note2_octave'] : $octave;

        // Basic note validity
        if ($note1 !== null && ! isset(self::NOTE_SEMITONES[$note1])) {
            $issues[] = 'unknown_note1';
        }
        if ($note2 !== null && ! isset(self::NOTE_SEMITONES[$note2])) {
            $issues[] = 'unknown_note2';
        }

        if (! empty($issues)) {
            return ['valid' => false, 'status' => 'invalid', 'issues' => $issues];
        }

        switch ($type) {
            case 'melodic-interval-practice':
            case 'harmonic-interval-practice':
                $issues = array_merge($issues, $this->validateIntervalQuestion($question, $note1, $octave, $note2, $note2Octave));
                break;

            case 'interval-direction-practice':
                $issues = array_merge($issues, $this->validateDirectionQuestion($question, $note1, $octave, $note2, $note2Octave));
                break;

            case 'interval-construction-practice':
                $issues = array_merge($issues, $this->validateConstructionQuestion($question, $note1, $octave, $note2, $note2Octave));
                break;
        }

        $needsReview = in_array('enharmonic_equivalent', $issues);
        $hasError = ! empty(array_diff($issues, ['enharmonic_equivalent']));

        $status = $hasError ? 'invalid' : ($needsReview ? 'needs_review' : 'valid');

        return [
            'valid' => $status === 'valid',
            'status' => $status,
            'issues' => $issues,
        ];
    }

    // ── Validation helpers ────────────────────────────────────────────────────

    private function validateIntervalQuestion(array $q, ?string $note1, ?int $octave, ?string $note2, ?int $note2Octave): array
    {
        $issues = [];
        if ($note1 === null || $octave === null || $note2 === null || $note2Octave === null) {
            return ['missing_fields'];
        }

        $storedInterval = $q['interval'] ?? '';
        $storedSemitones = self::INTERVAL_SEMITONES[$storedInterval] ?? null;
        $actualSemitones = abs($this->semitonesBetween($note1, $octave, $note2, $note2Octave));
        $expectedInterval = $this->intervalNameFromSemitones($actualSemitones);

        if ($storedSemitones === null) {
            $issues[] = 'unknown_interval';
        } elseif ($storedSemitones !== $actualSemitones) {
            // Check enharmonic: same semitone count but different spelling
            if ($this->normalizeIntervalName($storedInterval) === $this->normalizeIntervalName($expectedInterval ?? '')) {
                $issues[] = 'enharmonic_equivalent';
            } else {
                $issues[] = 'answer_mismatch';
            }
        }

        return $issues;
    }

    private function validateDirectionQuestion(array $q, ?string $note1, ?int $octave, ?string $note2, ?int $note2Octave): array
    {
        $issues = [];
        if ($note1 === null || $octave === null || $note2 === null || $note2Octave === null) {
            return ['missing_fields'];
        }

        $storedDirection = $q['direction'] ?? '';
        $derivedDirection = $this->getDirection($note1, $octave, $note2, $note2Octave);

        // unison questions have ambiguous direction
        if ($derivedDirection === 'unison') {
            $issues[] = 'enharmonic_equivalent';
        } elseif ($storedDirection !== $derivedDirection) {
            $issues[] = 'direction_mismatch';
        }

        return $issues;
    }

    private function validateConstructionQuestion(array $q, ?string $note1, ?int $octave, ?string $note2, ?int $note2Octave): array
    {
        $issues = [];
        if ($note1 === null || $octave === null || $note2 === null) {
            return ['missing_fields'];
        }

        $intervalName = $q['interval'] ?? '';
        $expected = $this->noteAboveByInterval($note1, $octave, $intervalName);

        if ($expected === null) {
            $issues[] = 'unknown_interval';

            return $issues;
        }

        $canonicalStored = $this->normalizeNote($note2);
        $canonicalExpected = $this->normalizeNote($expected['note']);

        if ($canonicalStored !== $canonicalExpected) {
            // Check if enharmonically equivalent
            $storedIndex = self::NOTE_SEMITONES[$note2] ?? -1;
            $expectedIndex = self::NOTE_SEMITONES[$expected['note']] ?? -2;
            if ($storedIndex === $expectedIndex) {
                $issues[] = 'enharmonic_equivalent';
            } else {
                $issues[] = 'answer_mismatch';
            }
        }

        // Validate octave if note2_octave is explicitly stored
        if ($note2Octave !== null && $note2Octave !== $octave && $expected['octave'] !== $note2Octave) {
            $issues[] = 'octave_mismatch';
        }

        return $issues;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function decodeArrayField(mixed $value): string
    {
        if (is_array($value)) {
            return implode(',', $value);
        }
        if (is_string($value) && strlen($value) > 0 && $value[0] === '[') {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return implode(',', $decoded);
            }
        }

        return (string) ($value ?? '');
    }
}
