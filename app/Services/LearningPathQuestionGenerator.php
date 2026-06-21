<?php

namespace App\Services;

use App\Models\ChordPractice;
use App\Models\HarmonicIntervalPractice;
use App\Models\IntervalComparisonPractice;
use App\Models\IntervalConstructionPractice;
use App\Models\IntervalDirectionPractice;
use App\Models\LearningPathExercise;
use App\Models\MelodicDictationPractice;
use App\Models\MelodicIntervalPractice;
use App\Models\RhythmPractice;
use App\Models\ScalePractice;
use App\Models\SingleNotePractice;
use Illuminate\Support\Collection;

class LearningPathQuestionGenerator
{
    public function __construct(
        private MusicTheoryService $music,
        private TonalMelodyGenerator $melodyGenerator,
        private RhythmDistractorService $rhythmDistractor,
    ) {}

    public function generate(LearningPathExercise $exercise, int $questionCount): Collection
    {
        $config = $exercise->config_json;
        $type = $config['practice_type'] ?? '';

        return match ($type) {
            'melodic-interval-practice' => $this->generateMelodicIntervals($config, $questionCount),
            'harmonic-interval-practice' => $this->generateHarmonicIntervals($config, $questionCount),
            'interval-direction-practice' => $this->generateIntervalDirection($config, $questionCount),
            'interval-construction-practice' => $this->generateIntervalConstruction($config, $questionCount),
            'interval-comparison-practice' => $this->generateIntervalComparison($config, $questionCount),
            'scale-practice' => $this->generateScales($config, $questionCount),
            'chord-practice' => $this->generateChords($config, $questionCount),
            'rhythm-practice' => $this->generateRhythm($config, $questionCount),
            'melodic-dictation' => $this->generateMelodicDictation($config, $questionCount),
            'single-note-practice' => $this->generateSingleNote($config, $questionCount),
            default => collect(),
        };
    }

    /**
     * Pick distractors honoring optional `distractor_count` / `distractor_mode`
     * config. When neither key is present the provided $fallback closure runs,
     * preserving the legacy per-type heuristics for the Learning Path and
     * Exercise Setup flows. The AI difficulty flow supplies both keys.
     *
     * $kind: 'interval' (interval-name pool) or 'note' (note-name pool).
     *   - 'near'        -> closest candidates by semitone distance (hardest)
     *   - 'far'|'mixed' -> random from pool (current behavior)
     */
    private function selectDistractors(string $correct, array $pool, array $cfg, string $kind, callable $fallback): array
    {
        if (! isset($cfg['distractor_count']) && ! isset($cfg['distractor_mode'])) {
            return $fallback();
        }

        $count = max(1, (int) ($cfg['distractor_count'] ?? 3));
        $mode = $cfg['distractor_mode'] ?? 'mixed';

        if ($mode === 'near') {
            $ordered = $kind === 'interval'
                ? $this->music->intervalNamesByCloseness($correct, $pool)
                : $this->music->notesByCloseness($correct, $pool);

            return array_slice($ordered, 0, $count);
        }

        return $this->music->buildOptions($correct, $pool, $count);
    }

    /**
     * Canonical 12-name interval pool (one per semitone 1..12), used as the
     * distractor source when explicit distractor settings are supplied.
     */
    private function canonicalIntervalPool(): array
    {
        $pool = [];
        for ($s = 1; $s <= 12; $s++) {
            $name = $this->music->intervalNameFromSemitones($s);
            if ($name !== null) {
                $pool[] = $name;
            }
        }

        return $pool;
    }

    /**
     * Octaves where both the root note and the note `$semitones` away from it
     * (negative = below) stay inside the clef's playable range (CLEF_RANGES).
     */
    private function octavesWithinClefRange(string $note, int $semitones, string $clef): array
    {
        [$min, $max] = $this->music->clefRangeMidi($clef);

        $octaves = [];
        for ($oct = 0; $oct <= 8; $oct++) {
            $midi = $this->music->midiNumber($note, $oct);
            if ($midi === null) {
                return [];
            }
            $lo = min($midi, $midi + $semitones);
            $hi = max($midi, $midi + $semitones);
            if ($lo >= $min && $hi <= $max) {
                $octaves[] = $oct;
            }
        }

        return $octaves;
    }

    /**
     * Map a direction config value to the per-question directions to generate.
     * 'mixed' adds both variants to the pool; shuffleTake then yields a blend.
     */
    private function resolveDirections(string $direction): array
    {
        return match ($direction) {
            'descending' => ['descending'],
            'mixed' => ['ascending', 'descending'],
            default => ['ascending'],
        };
    }

    // ── MELODIC INTERVALS ────────────────────────────────────────────────────

    private function generateMelodicIntervals(array $cfg, int $count): Collection
    {
        $intervals = $cfg['allowed_intervals'] ?? ['Major 2nd'];
        $notes = $cfg['allowed_notes'] ?? ['C', 'D', 'E', 'F', 'G'];
        $octaves = $cfg['octave_range'] ?? ['4'];
        $clef = $cfg['clef'] ?? null;
        $directions = $this->resolveDirections($cfg['direction'] ?? 'ascending');
        $allIntervals = array_keys(MusicTheoryService::INTERVAL_SEMITONES);

        $distractorCount = count($intervals) <= 2 ? 1 : 3;
        $distractorPool = $distractorCount > 1 && count($intervals) < 4
            ? $allIntervals
            : $intervals;
        $canonicalPool = $this->canonicalIntervalPool();

        $pool = [];
        foreach ($intervals as $interval) {
            $semitones = MusicTheoryService::INTERVAL_SEMITONES[$interval] ?? null;
            if ($semitones === null) {
                continue;
            }
            foreach ($notes as $note) {
                foreach ($directions as $direction) {
                    $signedSemitones = $direction === 'descending' ? -$semitones : $semitones;
                    if ($clef !== null) {
                        $validOctaves = $this->octavesWithinClefRange($note, $signedSemitones, $clef);
                        if (empty($validOctaves)) {
                            continue;
                        }
                        $octave = $validOctaves[array_rand($validOctaves)];
                    } else {
                        $octave = (int) $octaves[array_rand($octaves)];
                    }
                    $result = $direction === 'descending'
                        ? $this->music->preferredNoteBelowByInterval($note, $octave, $interval)
                        : $this->music->preferredNoteAboveByInterval($note, $octave, $interval);
                    if ($result === null) {
                        continue;
                    }

                    $distractors = $this->selectDistractors(
                        $interval,
                        $canonicalPool,
                        $cfg,
                        'interval',
                        fn () => $this->music->buildOptions($interval, $distractorPool, $distractorCount)
                    );
                    $fullOptions = array_merge([$interval], $distractors);
                    shuffle($fullOptions);

                    $q = new MelodicIntervalPractice;
                    $q->id = null;
                    $q->interval = $interval;
                    $q->note1 = $note;
                    $q->note2 = $result['note'];
                    $q->octave = $octave;
                    $q->note2_octave = $result['octave'];
                    $q->direction = $direction;
                    $q->options = $fullOptions;
                    $pool[] = $q;
                }
            }
        }

        return collect($this->shuffleTake($pool, $count));
    }

    // ── HARMONIC INTERVALS ───────────────────────────────────────────────────

    private function generateHarmonicIntervals(array $cfg, int $count): Collection
    {
        $intervals = $cfg['allowed_intervals'] ?? ['Major 3rd'];
        $notes = $cfg['allowed_notes'] ?? ['C', 'D', 'E', 'F', 'G'];
        $octaves = $cfg['octave_range'] ?? ['4'];
        $clef = $cfg['clef'] ?? null;
        $allIntervals = array_keys(MusicTheoryService::INTERVAL_SEMITONES);
        $canonicalPool = $this->canonicalIntervalPool();

        $pool = [];
        foreach ($intervals as $interval) {
            $semitones = MusicTheoryService::INTERVAL_SEMITONES[$interval] ?? null;
            if ($semitones === null) {
                continue;
            }
            foreach ($notes as $note) {
                if ($clef !== null) {
                    $validOctaves = $this->octavesWithinClefRange($note, $semitones, $clef);
                    if (empty($validOctaves)) {
                        continue;
                    }
                    $octave = $validOctaves[array_rand($validOctaves)];
                } else {
                    $octave = (int) $octaves[array_rand($octaves)];
                }
                $result = $this->music->preferredNoteAboveByInterval($note, $octave, $interval);
                if ($result === null) {
                    continue;
                }

                $distractors = $this->selectDistractors(
                    $interval,
                    $canonicalPool,
                    $cfg,
                    'interval',
                    fn () => $this->music->buildOptions($interval, $allIntervals, 3)
                );
                $fullOptions = array_merge([$interval], $distractors);
                shuffle($fullOptions);

                $q = new HarmonicIntervalPractice;
                $q->id = null;
                $q->interval = $interval;
                $q->note1 = $note;
                $q->note2 = $result['note'];
                $q->octave = $octave;
                $q->note2_octave = $result['octave'];
                $q->options = $fullOptions;
                $pool[] = $q;
            }
        }

        return collect($this->shuffleTake($pool, $count));
    }

    // ── INTERVAL DIRECTION ───────────────────────────────────────────────────

    private function generateIntervalDirection(array $cfg, int $count): Collection
    {
        $semitones = $cfg['allowed_intervals_semitones'] ?? [1, 2];
        $notes = $cfg['allowed_notes'] ?? ['C', 'D', 'E', 'F', 'G'];
        // Explicit octave (Learning Path config) wins; otherwise both notes are
        // kept inside the clef's playable range.
        $octaveCfg = isset($cfg['octave']) ? (int) $cfg['octave'] : null;
        $clef = $cfg['clef'] ?? 'treble';

        $pool = [];
        foreach ($semitones as $st) {
            $st = (int) $st;
            $intervalName = $this->music->intervalNameFromSemitones($st);
            foreach ($notes as $note) {
                // Ascending variant
                $octavesUp = $octaveCfg !== null
                    ? [$octaveCfg]
                    : $this->octavesWithinClefRange($note, $st, $clef);
                if (! empty($octavesUp)) {
                    $octave = $octavesUp[array_rand($octavesUp)];
                    $above = $intervalName !== null
                        ? $this->music->preferredNoteAboveByInterval($note, $octave, $intervalName)
                        : $this->music->noteAboveBySemitones($note, $octave, $st);
                    if ($above !== null) {
                        // Verify direction from actual pitch (guards against unison)
                        $dir = $this->music->getDirection($note, $octave, $above['note'], $above['octave']);
                        if ($dir === 'ascending') {
                            $q = new IntervalDirectionPractice;
                            $q->id = null;
                            $q->clef = $clef;
                            $q->note1 = $note;
                            $q->note2 = $above['note'];
                            $q->direction = 'ascending';
                            $q->octave = $octave;
                            $q->note2_octave = $above['octave'];
                            $pool[] = $q;
                        }
                    }
                }

                // Descending variant
                $octavesDown = $octaveCfg !== null
                    ? [$octaveCfg]
                    : $this->octavesWithinClefRange($note, -$st, $clef);
                if (! empty($octavesDown)) {
                    $octave = $octavesDown[array_rand($octavesDown)];
                    $below = $intervalName !== null
                        ? $this->music->preferredNoteBelowByInterval($note, $octave, $intervalName)
                        : $this->music->noteBelowBySemitones($note, $octave, $st);
                    if ($below !== null) {
                        $dir = $this->music->getDirection($note, $octave, $below['note'], $below['octave']);
                        if ($dir === 'descending') {
                            $q = new IntervalDirectionPractice;
                            $q->id = null;
                            $q->clef = $clef;
                            $q->note1 = $note;
                            $q->note2 = $below['note'];
                            $q->direction = 'descending';
                            $q->octave = $octave;
                            $q->note2_octave = $below['octave'];
                            $pool[] = $q;
                        }
                    }
                }
            }
        }

        return collect($this->shuffleTake($pool, $count));
    }

    // ── INTERVAL CONSTRUCTION ────────────────────────────────────────────────

    private function generateIntervalConstruction(array $cfg, int $count): Collection
    {
        $intervals = $cfg['allowed_intervals'] ?? ['Major 2nd'];
        $roots = $cfg['allowed_root_notes'] ?? ['C', 'D', 'E', 'F', 'G'];
        // Explicit octave (Learning Path / AI config) wins; otherwise pick an
        // octave that keeps root and target inside the clef's playable range.
        $octaveCfg = isset($cfg['octave']) ? (int) $cfg['octave'] : null;
        $clef = $cfg['clef'] ?? null;

        // Full expanded diatonic note pool (naturals, flats, sharps, double accidentals)
        $allDiatonicNotes = [
            'C', 'C#', 'Db', 'D', 'D#', 'Eb', 'E', 'E#', 'Fb',
            'F', 'F#', 'Gb', 'G', 'G#', 'Ab', 'A', 'A#', 'Bb',
            'B', 'B#', 'Cb', 'C##', 'D##', 'E##', 'F##', 'G##', 'A##', 'B##',
            'Dbb', 'Ebb', 'Fbb', 'Gbb', 'Abb', 'Bbb', 'Cbb',
        ];

        // The AI difficulty flow supplies distractor settings and its downstream
        // conversion now recomputes note2 with proper diatonic spelling (flats
        // where the interval calls for them), so when those settings are present
        // we generate the correct answer + distractors from the diatonic pool to
        // stay consistent. Without them, the legacy diatonic-spelling behavior is
        // preserved for LP / Exercise Setup.
        $hasDistractorCfg = isset($cfg['distractor_count']) || isset($cfg['distractor_mode']);

        // Single-accidental diatonic spellings only, used for AI distractors so
        // answer options stay readable (no double sharps/flats).
        $cleanDiatonicPool = array_values(array_filter(
            $allDiatonicNotes,
            // Case-sensitive: 'Bb' (B-flat) must not match the double-flat 'bb'
            fn ($n) => ! preg_match('/(##|bb|x)$/', $n)
        ));

        $directions = $this->resolveDirections($cfg['direction'] ?? 'ascending');

        $pool = [];
        foreach ($intervals as $interval) {
            $semitones = MusicTheoryService::INTERVAL_SEMITONES[$interval] ?? null;
            if ($semitones === null) {
                continue;
            }
            foreach ($roots as $root) {
                foreach ($directions as $direction) {
                    $signedSemitones = $direction === 'descending' ? -$semitones : $semitones;
                    if ($octaveCfg !== null) {
                        $octave = $octaveCfg;
                    } elseif ($clef !== null) {
                        $validOctaves = $this->octavesWithinClefRange($root, $signedSemitones, $clef);
                        if (empty($validOctaves)) {
                            continue;
                        }
                        $octave = $validOctaves[array_rand($validOctaves)];
                    } else {
                        $octave = 4;
                    }

                    if ($hasDistractorCfg) {
                        $result = $direction === 'descending'
                            ? $this->music->preferredNoteBelowByInterval($root, $octave, $interval)
                            : $this->music->preferredNoteAboveByInterval($root, $octave, $interval);
                        if ($result === null) {
                            continue;
                        }

                        $correctNote = $result['note'];

                        // Diatonic distractors excluding any enharmonic equivalent of
                        // the correct answer; honour the configured distractor count.
                        $distractorCount = max(1, (int) ($cfg['distractor_count'] ?? 3));
                        $distractors = [];
                        $shuffled = $cleanDiatonicPool;
                        shuffle($shuffled);
                        foreach ($shuffled as $candidate) {
                            if (count($distractors) >= $distractorCount) {
                                break;
                            }
                            if ($this->music->notesAreEnharmonic($candidate, $correctNote)) {
                                continue;
                            }
                            $distractors[] = $candidate;
                        }
                    } else {
                        // Use diatonic spelling for the correct answer
                        $result = $direction === 'descending'
                            ? $this->music->preferredNoteBelowByInterval($root, $octave, $interval)
                            : $this->music->diatonicNoteAboveByInterval($root, $octave, $interval);
                        if ($result === null) {
                            continue;
                        }

                        $correctNote = $result['note'];

                        // Build distractors: exclude enharmonic equivalents of the correct answer
                        $distractors = [];
                        $shuffled = $allDiatonicNotes;
                        shuffle($shuffled);
                        foreach ($shuffled as $candidate) {
                            if (count($distractors) >= 3) {
                                break;
                            }
                            if ($this->music->notesAreEnharmonic($candidate, $correctNote)) {
                                continue;
                            }
                            $distractors[] = $candidate;
                        }
                    }

                    $options = array_merge([$correctNote], $distractors);
                    shuffle($options);

                    $q = new IntervalConstructionPractice;
                    $q->id = null;
                    $q->interval = $interval;
                    $q->note1 = $root;
                    $q->note2 = $correctNote;
                    $q->octave = $octave;
                    $q->note2_octave = $result['octave'];
                    $q->direction = $direction;
                    $q->setRelation('_options', $options);
                    $pool[] = $q;
                }
            }
        }

        return collect($this->shuffleTake($pool, $count));
    }

    // ── INTERVAL COMPARISON ──────────────────────────────────────────────────

    private function generateIntervalComparison(array $cfg, int $count): Collection
    {
        $pairs = $cfg['allowed_interval_pairs'] ?? [['C,D', 'C,E']];
        $clef = $cfg['clef'] ?? 'treble';
        // Without an explicit octave, place the same-octave pair inside the
        // clef's playable range (bass: C3–B3; treble/alto: C4–B4).
        $octave = $cfg['octave'] ?? ($clef === 'bass' ? '3' : '4');

        // Preferred note name per chromatic semitone (0–11), flat-preferred for ambiguous.
        $semToNote = ['C', 'Db', 'D', 'Eb', 'E', 'F', 'F#', 'G', 'Ab', 'A', 'Bb', 'B'];
        // Natural notes and their semitone positions (used to pick valid roots).
        $naturalSemitones = ['C' => 0, 'D' => 2, 'E' => 4, 'F' => 5, 'G' => 7, 'A' => 9, 'B' => 11];

        $pool = [];
        foreach ($pairs as $pair) {
            [$a, $b] = $pair;

            $semA = $this->pairSemitones($a);
            $semB = $this->pairSemitones($b);
            if ($semA === null || $semB === null) {
                continue;
            }
            if ($semA === $semB) {
                continue;
            }

            $target = $semA > $semB ? 'a' : 'b';
            $maxSem = max($semA, $semB);

            // Restrict roots so neither top note wraps past semitone 11 (avoids
            // pairSemitones computing the wrong abs-distance on wrap-around).
            $validRoots = array_keys(
                array_filter($naturalSemitones, fn ($st) => $st + $maxSem <= 11)
            );
            if (empty($validRoots)) {
                $validRoots = ['C'];
            }

            // Pick independent roots for the normal and reversed variants.
            $root1 = $validRoots[array_rand($validRoots)];
            $root2 = $validRoots[array_rand($validRoots)];
            $st1 = $naturalSemitones[$root1];
            $st2 = $naturalSemitones[$root2];

            $q = new IntervalComparisonPractice;
            $q->id = null;
            $q->interval_a = $root1 . ',' . $semToNote[$st1 + $semA];
            $q->interval_b = $root1 . ',' . $semToNote[$st1 + $semB];
            $q->target = $target;
            $q->octave = $octave;
            $q->clef = $clef;
            $pool[] = $q;

            $qRev = new IntervalComparisonPractice;
            $qRev->id = null;
            $qRev->interval_a = $root2 . ',' . $semToNote[$st2 + $semB];
            $qRev->interval_b = $root2 . ',' . $semToNote[$st2 + $semA];
            $qRev->target = $target === 'a' ? 'b' : 'a';
            $qRev->octave = $octave;
            $qRev->clef = $clef;
            $pool[] = $qRev;
        }

        return collect($this->shuffleTake($pool, $count));
    }

    // ── SCALES ───────────────────────────────────────────────────────────────

    private function generateScales(array $cfg, int $count): Collection
    {
        $scaleTypes = $cfg['allowed_scale_types'] ?? ['major'];
        $roots = $cfg['allowed_root_notes'] ?? ['C'];
        $direction = $cfg['direction'] ?? 'ascending';
        $distractors = $cfg['distractor_pool'] ?? ['natural-minor', 'dorian'];

        // Explicit octave (Learning Path / AI config) wins; otherwise pick an
        // octave that keeps the whole scale (root + octave span) inside the
        // selected clef's playable range (CLEF_RANGES).
        $octaveCfg = isset($cfg['octave']) ? (string) $cfg['octave'] : null;
        $clef = $cfg['clef'] ?? null;

        $pool = [];
        foreach ($scaleTypes as $type) {
            foreach ($roots as $root) {
                if ($octaveCfg !== null) {
                    $octave = $octaveCfg;
                } elseif ($clef !== null) {
                    $validOctaves = $this->octavesWithinClefRange($root, 12, $clef);
                    if (empty($validOctaves)) {
                        continue;
                    }
                    $octave = (string) $validOctaves[array_rand($validOctaves)];
                } else {
                    $octave = '4';
                }

                $otherOptions = $this->music->buildOptions($type, $distractors, min(3, count($distractors)));

                $q = new ScalePractice;
                $q->id = null;
                $q->scale_type = $type;
                $q->root_note = $root;
                $q->direction = $direction;
                $q->octave = $octave;
                if ($clef !== null) {
                    $q->clef = $clef;
                }
                $q->other_options = $otherOptions;
                $pool[] = $q;
            }
        }

        return collect($this->shuffleTake($pool, $count));
    }

    // ── CHORDS ───────────────────────────────────────────────────────────────

    private function generateChords(array $cfg, int $count): Collection
    {
        $chordTypes = $cfg['allowed_chord_types'] ?? ['major', 'minor'];
        $roots = $cfg['allowed_root_notes'] ?? ['C', 'D', 'E', 'F', 'G'];
        $voicing = $cfg['voicing'] ?? 'block';
        $inversions = $cfg['include_inversions'] ?? false;
        $distractors = $cfg['distractor_pool'] ?? ['augmented', 'diminished'];

        $inversionValues = $inversions ? [0, 1, 2] : [0];

        // Explicit octave (Learning Path / AI config) wins; otherwise pick an
        // octave that keeps every chord tone (root + widest interval) inside
        // the selected clef's playable range (CLEF_RANGES).
        $octaveCfg = isset($cfg['octave']) ? (string) $cfg['octave'] : null;
        $clef = $cfg['clef'] ?? null;

        $pool = [];
        foreach ($chordTypes as $type) {
            $chordIntervals = ChordPractice::chordIntervals()[$type] ?? null;
            $span = $chordIntervals !== null ? max($chordIntervals) : 12;
            foreach ($roots as $root) {
                foreach ($inversionValues as $inv) {
                    if ($octaveCfg !== null) {
                        $octave = $octaveCfg;
                    } elseif ($clef !== null) {
                        $validOctaves = $this->octavesWithinClefRange($root, $span, $clef);
                        if (empty($validOctaves)) {
                            continue;
                        }
                        $octave = (string) $validOctaves[array_rand($validOctaves)];
                    } else {
                        $octave = '4';
                    }

                    $otherOptions = $this->music->buildOptions($type, $distractors, min(3, count($distractors)));

                    $q = new ChordPractice;
                    $q->id = null;
                    $q->chord_type = $type;
                    $q->root_note = $root;
                    $q->voicing = $voicing;
                    $q->inversion = $inv;
                    $q->octave = $octave;
                    if ($clef !== null) {
                        $q->clef = $clef;
                    }
                    $q->other_options = $otherOptions;
                    $pool[] = $q;
                }
            }
        }

        return collect($this->shuffleTake($pool, $count));
    }

    // ── RHYTHM ───────────────────────────────────────────────────────────────

    private function generateRhythm(array $cfg, int $count): Collection
    {
        $timeSigs = $cfg['time_signatures'] ?? ['4/4'];
        $tempoRange = $cfg['tempo_range'] ?? [76, 84];
        $bars = $cfg['bars'] ?? 1;
        $difficulty = $cfg['rhythm_difficulty'] ?? 'medium';
        $allowedValues = $cfg['allowed_note_values'] ?? null;
        $includeRests = $cfg['include_rests'] ?? false;

        $pool = [];

        foreach ($timeSigs as $timeSig) {
            [$num, $den] = array_map('intval', explode('/', $timeSig));
            // x/8 → compound (dotted-quarter beat). x/4 and x/2 use the simple (quarter-cell)
            // pool; x/2 is felt in half-note beats, i.e. num × 2 quarter-units per bar.
            $family = $den === 8 ? 'compound' : 'simple';
            $beats = match ($den) {
                8 => intdiv($num, 3), // compound beats (dotted-quarter)
                2 => $num * 2,        // half-note beats → quarter-units
                default => $num,            // x/4 → quarter beats
            };
            // When filtering by user-selected note values use the full 'hard' pool so every
            // possible token is present before the filter runs (e.g. triplet cells are only
            // in 'hard'). Without a filter keep the requested difficulty.
            $baseDifficulty = ! empty($allowedValues) ? 'hard' : $difficulty;
            $cells = $this->rhythmCells($family, $baseDifficulty);

            if (! empty($allowedValues)) {
                $filtered = array_values(array_filter($cells, fn ($c) => empty(array_diff($c['tokens'], $allowedValues))));
                // Only apply the filter if it leaves at least one cell; otherwise fall back to
                // the base pool so the meter can still be filled.
                if (! empty($filtered)) {
                    $cells = $filtered;
                }

                // Append rest cells for simple-meter families. Compound-meter dotted rests are
                // not supported by the VexFlow renderer so they are excluded.
                if ($family === 'simple' && ! empty($cells)) {
                    $restMap = ['whole_rest' => 4, 'half_rest' => 2, 'quarter_rest' => 1];
                    foreach ($restMap as $token => $len) {
                        if (in_array($token, $allowedValues)) {
                            $cells[] = ['len' => $len, 'tokens' => [$token]];
                        }
                    }
                    // eighth_rest: two per beat (8r + 8r = 1 quarter-beat)
                    if (in_array('eighth_rest', $allowedValues)) {
                        $cells[] = ['len' => 1, 'tokens' => ['eighth_rest', 'eighth_rest']];
                    }
                }
            }

            for ($i = 0; $i < max(20, $count * 2); $i++) {
                $pattern = $this->assembleRhythmBars($cells, $beats, $bars);
                if (empty($pattern)) {
                    continue;
                }

                // When rests are required, deterministically inject exactly one rest (≥ 1/8).
                if ($includeRests) {
                    $pattern = $this->injectOneRest($pattern);
                    if ($pattern === null) {
                        continue; // no eligible token to replace — retry
                    }
                }

                // Near-miss distractors via RhythmDistractorService; fall back to random
                // assembly if the service cannot produce enough variants (e.g. all-whole-note bar).
                $otherOptions = $this->rhythmDistractor->generate($pattern, $timeSig, $difficulty);
                if (count($otherOptions) < 3) {
                    for ($j = 0; count($otherOptions) < 3 && $j < 12; $j++) {
                        $alt = $this->assembleRhythmBars($cells, $beats, $bars);
                        if ($includeRests) {
                            $alt = $this->injectOneRest($alt) ?? $alt;
                        }
                        if ($alt !== $pattern && ! in_array($alt, $otherOptions)) {
                            $otherOptions[] = $alt;
                        }
                    }
                }

                $q = new RhythmPractice;
                $q->id = null;
                $q->time_signature = $timeSig;
                $q->note_values = $pattern;
                $q->other_options = $otherOptions;
                $q->tempo = rand($tempoRange[0], $tempoRange[1]);
                $q->bars = $bars;
                $pool[] = $q;
            }
        }

        return collect($this->shuffleTake($pool, $count));
    }

    /**
     * Replace one eligible note token in the pattern with its rest equivalent.
     * Eligible tokens: whole, half, quarter, eighth (≥ 1/8 note).
     * Never replaces the first token so bars do not start on a rest.
     * Returns null if no eligible position exists.
     */
    private function injectOneRest(array $pattern): ?array
    {
        $noteToRest = [
            'whole'   => 'whole_rest',
            'half'    => 'half_rest',
            'quarter' => 'quarter_rest',
            'eighth'  => 'eighth_rest',
        ];

        $eligible = [];
        for ($i = 1; $i < count($pattern); $i++) {
            if (isset($noteToRest[$pattern[$i]])) {
                $eligible[] = $i;
            }
        }

        if (empty($eligible)) {
            return null;
        }

        $pos = $eligible[array_rand($eligible)];
        $pattern[$pos] = $noteToRest[$pattern[$pos]];

        return $pattern;
    }

    /**
     * Assemble `$bars` bars by concatenating beat-aligned cells from the pool. Each bar is
     * filled by randomly picking cells whose length fits the remaining beats; a 1-beat cell
     * always exists, so a bar always completes.
     */
    private function assembleRhythmBars(array $cells, int $beatsPerBar, int $bars): array
    {
        $pattern = [];

        // A "dense" cell carries sixteenths or a triplet. Cap them per bar so a bar mixes
        // busy and calm beats instead of becoming a wall of notes (keeps harder levels
        // readable). At most ~half the beats may be dense.
        $isDense = fn ($c) => ! empty(array_intersect($c['tokens'], ['sixteenth', 'triplet-eighth']));
        $isRest  = fn ($c) => ! empty(array_intersect($c['tokens'], ['whole_rest', 'half_rest', 'quarter_rest', 'eighth_rest']));
        $maxDense     = max(1, intdiv($beatsPerBar, 2));
        // Allow at most half the bar to be rests (minimum 1 so a quarter_rest can appear
        // in short meters). Budget is checked per-cell so a half_rest (2 beats) is only
        // included when 2 or more rest-beats are still available.
        $maxRestBeats = max(1, intdiv($beatsPerBar, 2));

        for ($b = 0; $b < $bars; $b++) {
            $remaining     = $beatsPerBar;
            $denseUsed     = 0;
            $restBeatsUsed = 0;
            $barStart      = true;

            while ($remaining > 0) {
                $fitting = array_values(array_filter($cells, fn ($c) => $c['len'] <= $remaining));
                if ($denseUsed >= $maxDense) {
                    $calm = array_values(array_filter($fitting, fn ($c) => ! $isDense($c)));
                    if (! empty($calm)) {
                        $fitting = $calm; // a 1-beat calm cell always exists, so the bar still completes
                    }
                }
                // Bars must start on a note beat; rest cells that would exceed the budget are
                // removed per-cell so that smaller rests remain available.
                $restBudget = $maxRestBeats - $restBeatsUsed;
                $fitting = array_values(array_filter($fitting, function ($c) use ($isRest, $barStart, $restBudget) {
                    if (! $isRest($c)) {
                        return true;
                    }

                    return ! $barStart && $c['len'] <= $restBudget;
                }));
                // Safety net: if all candidates were rest-filtered, fall back to non-rest cells.
                if (empty($fitting)) {
                    $allFit  = array_values(array_filter($cells, fn ($c) => $c['len'] <= $remaining));
                    $nonRest = array_values(array_filter($allFit, fn ($c) => ! $isRest($c)));
                    $fitting = ! empty($nonRest) ? $nonRest : $allFit;
                }
                if (empty($fitting)) {
                    return []; // pool can't fill this meter (shouldn't happen)
                }
                $cell = $fitting[array_rand($fitting)];
                array_push($pattern, ...$cell['tokens']);
                if ($isDense($cell)) {
                    $denseUsed++;
                }
                if ($isRest($cell)) {
                    $restBeatsUsed += $cell['len'];
                }
                $remaining -= $cell['len'];
                $barStart   = false;
            }
        }

        return $pattern;
    }

    /**
     * The curated rhythm-cell pool. Each cell is one or more beats of a beat-aligned pattern,
     * so concatenating cells always yields correctly grouped bars. `$family` is `simple`
     * (beat = quarter) or `compound` (beat = dotted-quarter). Harder difficulties add busier
     * subdivisions, syncopation and (simple only) triplets.
     */
    private function rhythmCells(string $family, string $difficulty): array
    {
        $cell = fn (int $len, array $tokens) => ['len' => $len, 'tokens' => $tokens];

        if ($family === 'compound') {
            $cells = [
                $cell(2, ['dotted-half']),
                $cell(1, ['dotted-quarter']),
                $cell(1, ['quarter', 'eighth']),
                $cell(1, ['eighth', 'quarter']),
                $cell(1, ['eighth', 'eighth', 'eighth']),
            ];
            if ($difficulty === 'medium' || $difficulty === 'hard' || $difficulty === 'adaptive') {
                $cells = array_merge($cells, [
                    $cell(1, ['dotted-eighth', 'sixteenth', 'eighth']),
                    $cell(1, ['eighth', 'dotted-eighth', 'sixteenth']),
                    $cell(1, ['sixteenth', 'sixteenth', 'eighth', 'eighth']),
                    $cell(1, ['eighth', 'eighth', 'sixteenth', 'sixteenth']),
                ]);
            }
            if ($difficulty === 'hard') {
                // Kept to ≤4 notes per beat — the 5- and 6-sixteenth beats read as a wall
                // of notes and aren't musically useful here.
                $cells = array_merge($cells, [
                    $cell(1, ['sixteenth', 'dotted-eighth', 'eighth']),
                    $cell(1, ['eighth', 'sixteenth', 'dotted-eighth']),
                    $cell(1, ['eighth', 'sixteenth', 'sixteenth', 'eighth']),
                ]);
            }

            return $cells;
        }

        // simple
        $cells = [
            $cell(4, ['whole']),
            $cell(2, ['half']),
            $cell(3, ['dotted-half']),
            $cell(1, ['quarter']),
            $cell(1, ['eighth', 'eighth']),
        ];
        if ($difficulty === 'medium' || $difficulty === 'hard' || $difficulty === 'adaptive') {
            $cells = array_merge($cells, [
                $cell(1, ['sixteenth', 'sixteenth', 'sixteenth', 'sixteenth']),
                $cell(1, ['eighth', 'sixteenth', 'sixteenth']),
                $cell(1, ['sixteenth', 'sixteenth', 'eighth']),
                $cell(1, ['dotted-eighth', 'sixteenth']),
            ]);
        }
        if ($difficulty === 'hard') {
            $cells = array_merge($cells, [
                $cell(1, ['sixteenth', 'eighth', 'sixteenth']),
                $cell(1, ['sixteenth', 'dotted-eighth']),
                $cell(2, ['dotted-quarter', 'eighth']),
                $cell(2, ['eighth', 'dotted-quarter']),
                $cell(2, ['eighth', 'quarter', 'eighth']),
                $cell(1, ['triplet-eighth', 'triplet-eighth', 'triplet-eighth']),
            ]);
        }

        return $cells;
    }

    // ── MELODIC DICTATION ────────────────────────────────────────────────────

    private function generateMelodicDictation(array $cfg, int $count): Collection
    {
        $notePool = $cfg['note_pool'] ?? ['C4', 'D4', 'E4', 'F4', 'G4'];
        $melodyLength = $cfg['melody_length'] ?? 4;
        $clef = $cfg['clef'] ?? 'treble';
        $keySigs = $cfg['key_signatures'] ?? ['C'];
        $tempoRange = $cfg['tempo_range'] ?? [52, 60];
        $includeRhythm = $cfg['include_rhythm'] ?? false;
        $bars = $cfg['bars'] ?? 1;
        $difficulty = $cfg['difficulty'] ?? 'beginner';
        $mode = $cfg['mode'] ?? 'major'; // 'major' or 'minor'

        // Melodies are generated tonally inside the configured key: the
        // TonalMelodyGenerator anchors start/end on tonic-triad degrees and
        // enforces stepwise-dominant, leap-resolved, range-capped motion.
        $contexts = [];
        foreach ($keySigs as $keySig) {
            $contexts[$keySig] = $this->melodyGenerator->contextFromPool($notePool, $keySig, $mode);
        }

        $unique = [];
        $seen = [];

        for ($i = 0; $i < max($count * 4, 40) && count($unique) < $count * 2; $i++) {
            $keySig = $keySigs[array_rand($keySigs)];
            $melody = $this->melodyGenerator->generateMelody($melodyLength, $contexts[$keySig], $difficulty);
            $melody = $this->melodyGenerator->applyAccidentals($melody, $keySig, $mode, $difficulty);

            $key = implode(',', $melody);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            $q = new MelodicDictationPractice;
            $q->id = null;
            $q->notes = $melody;
            $q->bars = $bars;
            $q->clef = $clef;
            $q->key_signature = $keySig;
            $q->tonic = $keySig;
            $q->tempo = rand($tempoRange[0], $tempoRange[1]);
            $q->include_rhythm = $includeRhythm;
            $unique[] = $q;
        }

        return collect($this->shuffleTake($unique, $count));
    }

    // ── SINGLE NOTE ──────────────────────────────────────────────────────────

    private function generateSingleNote(array $cfg, int $count): Collection
    {
        $notes = $cfg['allowed_notes'] ?? ['C', 'D', 'E', 'F', 'G'];
        $octaveRange = $cfg['octave_range'] ?? ['4'];
        $distractorCount = $cfg['distractor_count'] ?? 3;

        $pool = [];
        foreach ($notes as $note) {
            foreach ($octaveRange as $octave) {
                $distractors = $this->music->buildOptions($note, $notes, min(3, count($notes) - 1));
                $allOptions = array_merge([$note], $distractors);
                shuffle($allOptions);

                $q = new SingleNotePractice;
                $q->id = null;
                $q->target = $note;
                $q->target_type = 'note';
                $q->other_options = implode(',', $allOptions);
                $q->octave = $octave;
                $pool[] = $q;
            }
        }

        return collect($this->shuffleTake($pool, $count));
    }

    // ── UTILITIES ────────────────────────────────────────────────────────────

    private function pairSemitones(string $pair): ?int
    {
        return $this->music->intervalPairSemitones($pair);
    }

    private function shuffleTake(array $pool, int $count): array
    {
        if (empty($pool)) {
            return [];
        }
        while (count($pool) < $count) {
            $pool = array_merge($pool, $pool);
        }
        shuffle($pool);

        return array_slice($pool, 0, $count);
    }

    private function beatsPerMeasure(string $timeSig): int
    {
        return match ($timeSig) {
            '2/4' => 4,
            '3/4' => 6,
            '4/4' => 8,
            '6/8' => 6,
            '9/8' => 9,
            '3/8' => 3,
            '5/8' => 5,
            '7/8' => 7,
            default => 8,
        };
    }

    // ── SESSION SERIALIZATION ─────────────────────────────────────────────────

    public function reconstructFromSession(array $serialized, string $practiceType): Collection
    {
        $modelMap = [
            'melodic-interval-practice' => MelodicIntervalPractice::class,
            'harmonic-interval-practice' => HarmonicIntervalPractice::class,
            'interval-direction-practice' => IntervalDirectionPractice::class,
            'interval-construction-practice' => IntervalConstructionPractice::class,
            'interval-comparison-practice' => IntervalComparisonPractice::class,
            'scale-practice' => ScalePractice::class,
            'chord-practice' => ChordPractice::class,
            'rhythm-practice' => RhythmPractice::class,
            'melodic-dictation' => MelodicDictationPractice::class,
            'single-note-practice' => SingleNotePractice::class,
        ];
        $class = $modelMap[$practiceType] ?? null;
        if (! $class) {
            return collect();
        }

        return collect($serialized)->map(function ($data, $index) use ($class) {
            $model = new $class;
            foreach ($data as $key => $value) {
                if ($key === 'id') {
                    continue;
                }
                $model->{$key} = $value;
            }
            $model->id = $index + 1;

            return $model;
        })->values();
    }

    public function serializeForSession(Collection $questions): array
    {
        return $questions->map(function ($q) {
            $attrs = $q->getAttributes();
            foreach ($attrs as $key => $value) {
                if (is_string($value) && strlen($value) > 0 && $value[0] === '[') {
                    $decoded = json_decode($value, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $attrs[$key] = $decoded;
                    }
                }
            }

            return $attrs;
        })->values()->toArray();
    }

    /**
     * Extract the canonical correct answer from a serialized session question.
     * Delegates to MusicTheoryService for the canonical implementation.
     */
    public function getAnswerFromSessionQuestion(array $questionData, string $slug): string
    {
        return $this->music->getAnswerFromQuestion($questionData, $slug);
    }
}
