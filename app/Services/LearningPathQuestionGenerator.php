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
        private DictationRhythmService $dictationRhythm,
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
        $canonicalPool = $this->canonicalIntervalPool();

        // The adaptive AI flow weights weak intervals by repeating them in
        // allowed_intervals; distractor decisions must use the distinct set so
        // the option list never contains duplicates.
        $distinctIntervals = array_values(array_unique($intervals));
        if (count($distinctIntervals) === 1) {
            // A single-interval pool cannot supply its own distractors — draw
            // from the canonical set so the question still has real options.
            $distractorCount = 3;
            $distractorPool = $canonicalPool;
        } else {
            $distractorCount = count($distinctIntervals) <= 2 ? 1 : 3;
            // Canonical pool (not INTERVAL_SEMITONES keys) so enharmonic
            // aliases (Augmented 4th / Diminished 5th / Tritone) cannot
            // surface as two same-sounding options in one question.
            $distractorPool = $distractorCount > 1 && count($distinctIntervals) < 4
                ? $canonicalPool
                : $distinctIntervals;
        }

        $pool = [];
        foreach ($intervals as $interval) {
            $semitones = MusicTheoryService::INTERVAL_SEMITONES[$interval] ?? null;
            if ($semitones === null) {
                continue;
            }
            foreach ($notes as $note) {
                foreach ($directions as $direction) {
                    $signedSemitones = $direction === 'descending' ? -$semitones : $semitones;
                    // One pool variant per valid octave placement (not one random
                    // pick) so small configs still yield varied questions.
                    if ($clef !== null) {
                        $validOctaves = $this->octavesWithinClefRange($note, $signedSemitones, $clef);
                    } else {
                        $validOctaves = array_map('intval', $octaves);
                    }
                    foreach ($validOctaves as $octave) {
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
                        if ($clef !== null) {
                            $q->clef = $clef;
                        }
                        $q->options = $fullOptions;
                        $pool[] = $q;
                    }
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
        $canonicalPool = $this->canonicalIntervalPool();

        // Same distractor rules as the melodic path: distinct set (adaptive AI
        // repeats intervals to weight them), single-interval pools draw from
        // the canonical set, and the canonical pool (never INTERVAL_SEMITONES
        // keys) keeps enharmonic aliases from appearing as two same-sounding
        // options in one question.
        $distinctIntervals = array_values(array_unique($intervals));
        if (count($distinctIntervals) === 1) {
            $distractorCount = 3;
            $distractorPool = $canonicalPool;
        } else {
            $distractorCount = count($distinctIntervals) <= 2 ? 1 : 3;
            $distractorPool = $distractorCount > 1 && count($distinctIntervals) < 4
                ? $canonicalPool
                : $distinctIntervals;
        }

        $pool = [];
        foreach ($intervals as $interval) {
            $semitones = MusicTheoryService::INTERVAL_SEMITONES[$interval] ?? null;
            if ($semitones === null) {
                continue;
            }
            foreach ($notes as $note) {
                // One pool variant per valid octave placement (not one random
                // pick) so small configs still yield varied questions.
                if ($clef !== null) {
                    $validOctaves = $this->octavesWithinClefRange($note, $semitones, $clef);
                } else {
                    $validOctaves = array_map('intval', $octaves);
                }
                foreach ($validOctaves as $octave) {
                    $result = $this->music->preferredNoteAboveByInterval($note, $octave, $interval);
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

                    $q = new HarmonicIntervalPractice;
                    $q->id = null;
                    $q->interval = $interval;
                    $q->note1 = $note;
                    $q->note2 = $result['note'];
                    $q->octave = $octave;
                    $q->note2_octave = $result['octave'];
                    if ($clef !== null) {
                        $q->clef = $clef;
                    }
                    $q->options = $fullOptions;
                    $pool[] = $q;
                }
            }
        }

        return collect($this->shuffleTake($pool, $count));
    }

    // ── INTERVAL DIRECTION ───────────────────────────────────────────────────

    private function generateIntervalDirection(array $cfg, int $count): Collection
    {
        $semitones = $cfg['allowed_intervals_semitones'] ?? [1, 2];
        $notes = $cfg['allowed_notes'] ?? ['C', 'D', 'E', 'F', 'G'];
        // Explicit octave (legacy Learning Path config) wins; otherwise both
        // notes are kept inside the clef's playable range. `clef` may be an
        // array — each question variant then draws its register from a
        // randomly chosen clef in the set.
        $octaveCfg = isset($cfg['octave']) ? (int) $cfg['octave'] : null;
        $clefCfg = $cfg['clef'] ?? 'treble';
        $clefs = array_values((array) $clefCfg) ?: ['treble'];

        $pool = [];
        foreach ($semitones as $st) {
            $st = (int) $st;
            $intervalName = $this->music->intervalNameFromSemitones($st);
            foreach ($notes as $note) {
                // Ascending variant
                $clef = $clefs[array_rand($clefs)];
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
                $clef = $clefs[array_rand($clefs)];
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
        // Explicit octave (legacy / ad-hoc config) wins; otherwise every octave
        // that keeps root and target inside the clef's playable range
        // (CLEF_RANGES) yields a pool variant — the Exercise Setup rule.
        $octaveCfg = isset($cfg['octave']) ? (int) $cfg['octave'] : null;
        $clef = $cfg['clef'] ?? null;
        $directions = $this->resolveDirections($cfg['direction'] ?? 'ascending');

        // Answer options use single-accidental spellings only (the Exercise
        // Setup palette). E#/Fb are valid correct answers (e.g. Tritone above
        // B → E#) but never distractors — a student shouldn't have to weigh
        // E# against F as two different wrong choices.
        $distractorPool = array_values(array_diff(
            array_keys(MusicTheoryService::NOTE_SEMITONES),
            ['E#', 'Fb']
        ));
        $distractorCount = max(1, (int) ($cfg['distractor_count'] ?? 3));
        $nearMode = ($cfg['distractor_mode'] ?? null) === 'near';

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
                        $validOctaves = [$octaveCfg];
                    } elseif ($clef !== null) {
                        $validOctaves = $this->octavesWithinClefRange($root, $signedSemitones, $clef);
                    } else {
                        $validOctaves = [4];
                    }
                    foreach ($validOctaves as $octave) {
                        // Diatonic (letter-correct) spelling for the answer.
                        // preferred* already falls back to a readable chromatic
                        // spelling when the strict diatonic result would need a
                        // double accidental.
                        $result = $direction === 'descending'
                            ? $this->music->preferredNoteBelowByInterval($root, $octave, $interval)
                            : $this->music->preferredNoteAboveByInterval($root, $octave, $interval);
                        // B#/Cb survive preferred* (single accidental) but are
                        // excluded from NOTE_SEMITONES — their written octave
                        // differs from the sounding one, breaking playback.
                        // Respell chromatically instead of dropping the combo.
                        if ($result !== null && ! isset(MusicTheoryService::NOTE_SEMITONES[$result['note']])) {
                            $result = $direction === 'descending'
                                ? $this->music->noteBelowByInterval($root, $octave, $interval)
                                : $this->music->noteAboveByInterval($root, $octave, $interval);
                        }
                        if ($result === null || ! isset(MusicTheoryService::NOTE_SEMITONES[$result['note']])) {
                            continue;
                        }

                        $correctNote = $result['note'];

                        // Distractors: 'near' mode ranks the pool by chromatic
                        // closeness (half-step discrimination); default mode
                        // draws at random. Either way each option is a distinct
                        // pitch class — never an enharmonic respelling of the
                        // answer or of another option.
                        $candidates = $nearMode
                            ? $this->music->notesByCloseness($correctNote, $distractorPool)
                            : $this->shuffled($distractorPool);
                        $distractors = [];
                        $usedPitchClasses = [$this->music->parseNoteChromatic($correctNote)];
                        foreach ($candidates as $candidate) {
                            if (count($distractors) >= $distractorCount) {
                                break;
                            }
                            $pc = $this->music->parseNoteChromatic($candidate);
                            if ($pc === null || in_array($pc, $usedPitchClasses, true)) {
                                continue;
                            }
                            $usedPitchClasses[] = $pc;
                            $distractors[] = $candidate;
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
                        if ($clef !== null) {
                            $q->clef = $clef;
                        }
                        // Plain attribute (not a relation) so the options survive
                        // serializeForSession/reconstructFromSession for both the
                        // Learning Path and Exercise Setup flows.
                        $q->options = $options;
                        $pool[] = $q;
                    }
                }
            }
        }

        return collect($this->shuffleTake($pool, $count));
    }

    /** Return a shuffled copy of an array (shuffle() mutates in place). */
    private function shuffled(array $items): array
    {
        shuffle($items);

        return $items;
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

            // One normal + one reversed variant per valid root — the full
            // transposition space, not one random pick per pair, so questions
            // do not keep repeating the same two notes.
            foreach ($validRoots as $root) {
                $st = $naturalSemitones[$root];

                $q = new IntervalComparisonPractice;
                $q->id = null;
                $q->interval_a = $root.','.$semToNote[$st + $semA];
                $q->interval_b = $root.','.$semToNote[$st + $semB];
                $q->target = $target;
                $q->octave = $octave;
                $q->clef = $clef;
                $pool[] = $q;

                $qRev = new IntervalComparisonPractice;
                $qRev->id = null;
                $qRev->interval_a = $root.','.$semToNote[$st + $semB];
                $qRev->interval_b = $root.','.$semToNote[$st + $semA];
                $qRev->target = $target === 'a' ? 'b' : 'a';
                $qRev->octave = $octave;
                $qRev->clef = $clef;
                $pool[] = $qRev;
            }
        }

        return collect($this->shuffleTake($pool, $count));
    }

    // ── SCALES ───────────────────────────────────────────────────────────────

    private function generateScales(array $cfg, int $count): Collection
    {
        $scaleTypes = array_map([$this, 'canonicalScaleType'], $cfg['allowed_scale_types'] ?? ['Major']);
        $roots = $cfg['allowed_root_notes'] ?? ['C'];
        $direction = $cfg['direction'] ?? 'ascending';
        $distractors = array_map([$this, 'canonicalScaleType'], $cfg['distractor_pool'] ?? ['Natural Minor', 'Dorian']);

        // Studio parity: 'both'/'mixed' mixes ascending and descending
        // questions in one session.
        $directions = in_array($direction, ['both', 'mixed'], true)
            ? ['ascending', 'descending']
            : [$direction];

        // Explicit octave (legacy Learning Path / AI config) wins; otherwise
        // pick an octave that keeps the whole scale (root + octave span)
        // inside the selected clef's playable range (CLEF_RANGES).
        $octaveCfg = isset($cfg['octave']) ? (string) $cfg['octave'] : null;
        $clef = $cfg['clef'] ?? null;

        $pool = [];
        foreach ($scaleTypes as $type) {
            foreach ($roots as $root) {
                foreach ($directions as $dir) {
                    // Classical theory descends the melodic minor as natural
                    // minor, so a descending jazz-form question would be
                    // theoretically ambiguous — in mixed mode the melodic
                    // minor only gets ascending variants.
                    if ($dir === 'descending' && count($directions) > 1 && $type === 'Melodic Minor') {
                        continue;
                    }

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
                    $q->direction = $dir;
                    $q->octave = $octave;
                    if ($clef !== null) {
                        $q->clef = $clef;
                    }
                    // Playback tempo preset (slow|normal|fast) — teacher assignments.
                    if (! empty($cfg['scale_tempo'])) {
                        $q->tempo = $cfg['scale_tempo'];
                    }
                    $q->other_options = $otherOptions;
                    $pool[] = $q;
                }
            }
        }

        return collect($this->shuffleTake($pool, $count));
    }

    /**
     * Map legacy lowercase scale slugs (old LP seed configs) to the canonical
     * ScalePractice::scaleIntervals() keys. Unknown names pass through
     * unchanged. Without this, an unmapped name silently falls back to Major
     * intervals at playback while the answer key keeps the original label —
     * the question sounds wrong.
     */
    private function canonicalScaleType(string $type): string
    {
        return [
            'major' => 'Major',
            'natural-minor' => 'Natural Minor',
            'harmonic-minor' => 'Harmonic Minor',
            'melodic-minor' => 'Melodic Minor',
            'ionian' => 'Ionian',
            'dorian' => 'Dorian',
            'phrygian' => 'Phrygian',
            'lydian' => 'Lydian',
            'mixolydian' => 'Mixolydian',
            'aeolian' => 'Aeolian',
            'locrian' => 'Locrian',
            'pentatonic-major' => 'Major Pentatonic',
            'pentatonic-minor' => 'Minor Pentatonic',
            'blues' => 'Blues Scale',
            'chromatic' => 'Chromatic Scale',
            'whole-tone' => 'Whole Tone Scale',
        ][$type] ?? $type;
    }

    // ── CHORDS ───────────────────────────────────────────────────────────────

    private function generateChords(array $cfg, int $count): Collection
    {
        $chordTypes = array_map(
            fn ($t) => $this->canonicalChordType($t),
            $cfg['allowed_chord_types'] ?? ['Major', 'Minor']
        );
        $roots = $cfg['allowed_root_notes'] ?? ['C', 'D', 'E', 'F', 'G'];
        $voicing = $cfg['voicing'] ?? 'block';
        $distractors = array_map(
            fn ($t) => $this->canonicalChordType($t),
            $cfg['distractor_pool'] ?? []
        );
        // An empty pool (synthesis lessons) means "the lesson's own types are
        // the answer vocabulary"; chordDistractors() tops up from the canonical
        // pool when that still leaves fewer than 3 wrong options.
        if (empty($distractors)) {
            $distractors = $chordTypes;
        }

        // Focused inversion lessons pass explicit inversion_values (e.g. [1]
        // for a first-inversion-only lesson). include_inversions=true keeps
        // the Exercise Setup behavior of mixing root position with both
        // inversions.
        if (! empty($cfg['inversion_values'])) {
            $inversionValues = array_values(array_intersect(
                array_map('intval', (array) $cfg['inversion_values']),
                [0, 1, 2]
            )) ?: [0];
        } else {
            $inversionValues = ($cfg['include_inversions'] ?? false) ? [0, 1, 2] : [0];
        }

        // Explicit octave (Learning Path / AI config) wins; otherwise pick an
        // octave that keeps every chord tone (root + widest interval) inside
        // the selected clef's playable range (CLEF_RANGES).
        $octaveCfg = isset($cfg['octave']) ? (string) $cfg['octave'] : null;
        $clef = $cfg['clef'] ?? null;

        $pool = [];
        foreach ($chordTypes as $type) {
            $chordIntervals = ChordPractice::chordIntervals()[$type] ?? null;
            foreach ($roots as $root) {
                foreach ($inversionValues as $inv) {
                    // Inversions lift the lowest $inv chord tones an octave, so
                    // the top sounding pitch is intervals[$inv-1] + 12 — wider
                    // than max(intervals). Use the true per-inversion span so
                    // the whole voicing stays inside the clef range.
                    if ($chordIntervals === null) {
                        $span = 12 + ($inv > 0 ? 12 : 0);
                    } elseif ($inv > 0 && count($chordIntervals) > $inv) {
                        $span = $chordIntervals[$inv - 1] + 12;
                    } else {
                        $span = max($chordIntervals);
                    }
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

                    $otherOptions = $this->chordDistractors($type, $distractors);

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

    /**
     * Map legacy lowercase chord slugs (old LP seed configs) to the canonical
     * ChordPractice::chordIntervals() keys. Unknown names pass through
     * unchanged. Without this, an unmapped type silently falls back to Major
     * intervals at playback while the answer key keeps the original label —
     * the question sounds wrong.
     */
    private function canonicalChordType(string $type): string
    {
        return [
            'major' => 'Major',
            'minor' => 'Minor',
            'diminished' => 'Diminished',
            'augmented' => 'Augmented',
            'sus2' => 'Sus2',
            'sus4' => 'Sus4',
            'major7' => 'Major 7th',
            'dominant7' => 'Dominant 7th',
            'minor7' => 'Minor 7th',
            'minor-major7' => 'Minor Major 7th',
            'half-diminished7' => 'Half-Diminished 7th',
            'half-diminished' => 'Half-Diminished 7th',
            'diminished7' => 'Diminished 7th',
            'augmented7' => 'Augmented 7th',
            'major6' => 'Major 6th',
            'minor6' => 'Minor 6th',
            'add9' => 'Add9',
            'minor-add9' => 'Minor Add9',
        ][strtolower($type)] ?? $type;
    }

    /**
     * Pick 3 wrong answer options for a chord question. Excludes the correct
     * type and any acoustic twin of it (a pool entry with the identical
     * interval set, e.g. the legacy 'Half Diminished' alias of
     * 'Half-Diminished 7th' — two buttons would both be right). Narrow pools
     * are topped up from the full canonical vocabulary so no question ever
     * renders with fewer than 4 choices.
     */
    private function chordDistractors(string $correct, array $pool): array
    {
        $intervals = ChordPractice::chordIntervals();
        $correctIntervals = $intervals[$correct] ?? null;

        // Dedupe by sound, not just by name: two options that share an
        // interval set (e.g. 'Half Diminished' + 'Half-Diminished 7th') are
        // the same chord twice.
        $filter = function (array $candidates, array $taken) use ($correct, $intervals, $correctIntervals): array {
            $seenSounds = array_map(fn ($n) => $intervals[$n] ?? $n, $taken);
            if ($correctIntervals !== null) {
                $seenSounds[] = $correctIntervals;
            }
            $out = [];
            foreach ($candidates as $name) {
                $sound = $intervals[$name] ?? $name;
                if ($name === $correct || in_array($name, $taken) || in_array($name, $out) || in_array($sound, $seenSounds, true)) {
                    continue;
                }
                $seenSounds[] = $sound;
                $out[] = $name;
            }

            return $out;
        };

        $options = $filter($pool, []);
        shuffle($options);
        $options = array_slice($options, 0, 3);

        if (count($options) < 3) {
            $fallback = $filter(array_keys($intervals), $options);
            shuffle($fallback);
            $options = array_merge($options, array_slice($fallback, 0, 3 - count($options)));
        }

        return $options;
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
        $excludeCells = $cfg['exclude_cells'] ?? [];

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
                    // eighth_rest pairs with an eighth note inside one beat: rest on the
                    // beat (the canonical off-beat figure) or note-then-rest. Two eighth
                    // rests in a row are never generated — that duration is notated as a
                    // quarter rest.
                    if (in_array('eighth_rest', $allowedValues)) {
                        $cells[] = ['len' => 1, 'tokens' => ['eighth_rest', 'eighth']];
                        $cells[] = ['len' => 1, 'tokens' => ['eighth', 'eighth_rest']];
                    }
                }
            }

            // Lesson-scoped cell exclusion: drop cells whose exact token sequence is
            // listed (comma-joined), e.g. the syncopated 'eighth,quarter,eighth' cell
            // in a lesson that teaches dotted figures. A 1-beat non-excluded cell must
            // remain so the meter can still be filled — seeded configs guarantee this.
            if (! empty($excludeCells)) {
                $cells = array_values(array_filter(
                    $cells,
                    fn ($c) => ! in_array(implode(',', $c['tokens']), $excludeCells, true)
                ));
            }

            $seenPatterns = [];
            for ($i = 0; $i < max(80, $count * 8); $i++) {
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

                // Skip patterns already in the pool so narrow configs still
                // produce as many distinct rhythms as the space allows.
                $patternKey = implode(',', $pattern);
                if (isset($seenPatterns[$patternKey])) {
                    continue;
                }
                $seenPatterns[$patternKey] = true;

                // Near-miss distractors via RhythmDistractorService; fall back to random
                // assembly if the service cannot produce enough variants (e.g. all-whole-note bar).
                // Constrain distractors to the lesson vocabulary so focused lessons never
                // surface note values they have not taught yet (e.g. sixteenths in an
                // eighth-note lesson).
                $otherOptions = $this->rhythmDistractor->generate($pattern, $timeSig, $difficulty, $allowedValues);
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
            'whole' => 'whole_rest',
            'half' => 'half_rest',
            'quarter' => 'quarter_rest',
            'eighth' => 'eighth_rest',
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
        $isRest = fn ($c) => ! empty(array_intersect($c['tokens'], ['whole_rest', 'half_rest', 'quarter_rest', 'eighth_rest']));
        $maxDense = max(1, intdiv($beatsPerBar, 2));
        // Allow at most half the bar to be rests (minimum 1 so a quarter_rest can appear
        // in short meters). Budget is checked per-cell so a half_rest (2 beats) is only
        // included when 2 or more rest-beats are still available.
        $maxRestBeats = max(1, intdiv($beatsPerBar, 2));

        for ($b = 0; $b < $bars; $b++) {
            $remaining = $beatsPerBar;
            $denseUsed = 0;
            $restBeatsUsed = 0;
            $barStart = true;

            while ($remaining > 0) {
                $fitting = array_values(array_filter($cells, fn ($c) => $c['len'] <= $remaining));
                if ($denseUsed >= $maxDense) {
                    $calm = array_values(array_filter($fitting, fn ($c) => ! $isDense($c)));
                    if (! empty($calm)) {
                        $fitting = $calm; // a 1-beat calm cell always exists, so the bar still completes
                    }
                }
                // Bars must start on a sounded note — a cell whose FIRST token is a rest
                // cannot open the bar (a note-then-rest cell like [eighth, eighth_rest]
                // may). Rest cells that would exceed the budget are removed per-cell so
                // that smaller rests remain available.
                $restBudget = $maxRestBeats - $restBeatsUsed;
                $fitting = array_values(array_filter($fitting, function ($c) use ($isRest, $barStart, $restBudget) {
                    if (! $isRest($c)) {
                        return true;
                    }
                    if ($barStart && str_contains($c['tokens'][0], '_rest')) {
                        return false;
                    }

                    return $c['len'] <= $restBudget;
                }));
                // Safety net: if all candidates were rest-filtered, fall back to non-rest cells.
                if (empty($fitting)) {
                    $allFit = array_values(array_filter($cells, fn ($c) => $c['len'] <= $remaining));
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
                $barStart = false;
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
        $notePool = $cfg['note_pool'] ?? [];
        $melodyLength = $cfg['melody_length'] ?? 4;
        $clef = $cfg['clef'] ?? 'treble';
        $keySigs = $cfg['key_signatures'] ?? ['C'];
        $tempoRange = $cfg['tempo_range'] ?? [52, 60];
        $includeRhythm = $cfg['include_rhythm'] ?? false;
        $bars = $cfg['bars'] ?? 1;
        $difficulty = $cfg['difficulty'] ?? 'beginner';
        $mode = $cfg['mode'] ?? 'major'; // 'major' or 'minor'
        // Focused Learning Path lessons pin the accidental treatment
        // (none/harmonic/melodic); 'auto' = Studio difficulty-based mix.
        $accidentals = $cfg['accidentals'] ?? $cfg['minor_flavor'] ?? 'auto';

        // Melodies are generated tonally inside the configured key: the
        // TonalMelodyGenerator anchors start/end on tonic-triad degrees and
        // enforces stepwise-dominant, leap-resolved, range-capped motion.
        // A configured note_pool gives a focused lesson register (single-key
        // lessons only — pool notes must be diatonic to the key); without one
        // the Studio clef range (CLEF_RANGES) supplies the pool per key.
        $contexts = [];
        foreach ($keySigs as $keySig) {
            $contexts[$keySig] = ! empty($notePool)
                ? $this->melodyGenerator->contextFromPool($notePool, $keySig, $mode)
                : $this->melodyGenerator->contextForKey($keySig, $mode, $clef);
        }

        // Rhythmic dictation: the melody length follows the generated
        // beat-pattern rhythm (same engine as the Exercise Setup flow), so
        // notes[] and note_values[] are always in sync.
        $timeSig = $cfg['time_signature'] ?? '4/4';
        $allowedNoteValues = array_values(array_filter(
            $cfg['allowed_note_values'] ?? ['quarter', 'eighth'],
            fn ($v) => ! str_ends_with((string) $v, '_rest'),
        ));
        if (empty($allowedNoteValues)) {
            $allowedNoteValues = ['quarter'];
        }

        $unique = [];
        $seen = [];

        // Narrow focused pools (e.g. a 3-note beginner lesson) collide often —
        // allow plenty of attempts so the full question count is still reached.
        for ($i = 0; $i < max($count * 12, 120) && count($unique) < $count * 2; $i++) {
            $keySig = $keySigs[array_rand($keySigs)];

            $noteValues = null;
            $length = $melodyLength;
            if ($includeRhythm) {
                $noteValues = $this->dictationRhythm->generateBeatPatternRhythm($bars, $timeSig, $allowedNoteValues);
                $length = count($noteValues);
            }

            $melody = $this->melodyGenerator->generateMelody($length, $contexts[$keySig], $difficulty);

            // Focused harmonic / melodic minor lessons must actually sound their
            // signature accidental (leading tone, raised 6–7) in every question —
            // force an ascending cadence into the tonic when the generated line
            // lacks one, so applyAccidentals() has something to raise.
            $isFocusedMinor = $mode === 'minor' && in_array($accidentals, ['harmonic', 'melodic'], true);
            if ($isFocusedMinor) {
                $melody = $this->melodyGenerator->ensureMinorCadence(
                    $melody, $keySig, $accidentals, $contexts[$keySig]['pool'], $difficulty
                );
            }

            $melody = $this->melodyGenerator->applyAccidentals($melody, $keySig, $mode, $difficulty, $accidentals);

            // Reject any focused-minor candidate the cadence guarantee couldn't
            // secure, so no question is left without its target accidental.
            if ($isFocusedMinor && ! $this->melodyGenerator->melodyMeetsMinorFlavor($melody, $keySig, $accidentals)) {
                continue;
            }

            $key = $keySig.'|'.implode(',', $melody).'|'.($noteValues ? implode(',', $noteValues) : '');
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
            // Minor lessons notate in the relative major's key signature but
            // are anchored (and labelled) on the relative minor tonic.
            $q->tonic = $mode === 'minor'
                ? $this->melodyGenerator->relativeMinorRoot($keySig)
                : $keySig;
            $q->mode = $mode;
            $q->tempo = rand($tempoRange[0], $tempoRange[1]);
            $q->include_rhythm = $includeRhythm;
            if ($noteValues !== null) {
                $q->note_values = $noteValues;
                $q->time_signature = $timeSig;
            }
            $unique[] = $q;
        }

        return collect($this->shuffleTake($unique, $count));
    }

    // ── SINGLE NOTE ──────────────────────────────────────────────────────────

    private function generateSingleNote(array $cfg, int $count): Collection
    {
        $notes = $cfg['allowed_notes'] ?? ['C', 'D', 'E', 'F', 'G'];
        // Explicit octave_range (Learning Path configs) wins; otherwise the
        // note is placed in every octave inside the clef's playable range.
        $octaveCfg = $cfg['octave_range'] ?? null;
        $clef = $cfg['clef'] ?? null;
        $distractorCount = $cfg['distractor_count'] ?? 3;
        // Per-lesson key labelling ('note-names' | 'keyboard') travels on the
        // question so the practice blade can honour it in the LP flow, where
        // no exercise_settings exist.
        $answerMode = $cfg['answer_mode'] ?? null;

        $pool = [];
        foreach ($notes as $note) {
            if ($octaveCfg !== null) {
                $octaveRange = $octaveCfg;
            } elseif ($clef !== null) {
                $octaveRange = array_map('strval', $this->octavesWithinClefRange($note, 0, $clef));
                if (empty($octaveRange)) {
                    continue;
                }
            } else {
                $octaveRange = ['4'];
            }
            foreach ($octaveRange as $octave) {
                $distractors = $this->music->buildOptions($note, $notes, min($distractorCount, count($notes) - 1));
                $allOptions = array_merge([$note], $distractors);
                shuffle($allOptions);

                $q = new SingleNotePractice;
                $q->id = null;
                $q->target = $note;
                $q->target_type = 'note';
                $q->other_options = implode(',', $allOptions);
                $q->octave = $octave;
                if ($clef !== null) {
                    $q->clef = $clef;
                }
                if ($answerMode !== null) {
                    $q->answer_mode = $answerMode;
                }
                $q->reference_note = $this->pickReferenceNote($note, (int) $octave, $clef);
                $pool[] = $q;
            }
        }

        return collect($this->shuffleTake($pool, $count));
    }

    /**
     * Reference note played before a single-note question: a natural note
     * with a different letter than the target, in the same octave, kept
     * inside the clef's playable range when a clef is known.
     */
    private function pickReferenceNote(string $target, int $octave, ?string $clef): string
    {
        $naturals = ['C', 'D', 'E', 'F', 'G', 'A', 'B'];
        $targetLetter = strtoupper(substr($target, 0, 1));

        if ($clef === null) {
            $candidates = array_values(array_filter($naturals, fn ($n) => $n !== $targetLetter));

            return $candidates[array_rand($candidates)].$octave;
        }

        // Prefer the target's own octave; when the target sits at the edge of
        // the clef range (e.g. C4 in bass, where every other natural of octave
        // 4 is out of range), fall back to a neighbouring octave rather than
        // leaving the clef's playable range.
        [$min, $max] = $this->music->clefRangeMidi($clef);
        foreach ([$octave, $octave - 1, $octave + 1] as $oct) {
            $candidates = [];
            foreach ($naturals as $n) {
                if ($n === $targetLetter) {
                    continue;
                }
                $midi = $this->music->midiNumber($n, $oct);
                if ($midi !== null && $midi >= $min && $midi <= $max) {
                    $candidates[] = $n.$oct;
                }
            }
            if (! empty($candidates)) {
                return $candidates[array_rand($candidates)];
            }
        }

        $candidates = array_values(array_filter($naturals, fn ($n) => $n !== $targetLetter));

        return $candidates[array_rand($candidates)].$octave;
    }

    // ── UTILITIES ────────────────────────────────────────────────────────────

    private function pairSemitones(string $pair): ?int
    {
        return $this->music->intervalPairSemitones($pair);
    }

    /**
     * Pick $count questions from the pool, maximising variety: distinct
     * questions are always exhausted before anything repeats, and when the
     * pool is smaller than $count the repeats are spread out (never the same
     * question twice in a row) instead of naive duplication.
     */
    private function shuffleTake(array $pool, int $count): array
    {
        if (empty($pool)) {
            return [];
        }

        shuffle($pool);

        $unique = [];
        $seen = [];
        foreach ($pool as $q) {
            $key = $this->questionVariantKey($q);
            if (! isset($seen[$key])) {
                $seen[$key] = true;
                $unique[] = $q;
            }
        }

        $result = array_slice($unique, 0, $count);

        while (count($result) < $count) {
            $cycle = $unique;
            shuffle($cycle);
            // Avoid an immediate repeat at the seam between cycles.
            if (count($cycle) > 1
                && $this->questionVariantKey(end($result)) === $this->questionVariantKey($cycle[0])) {
                [$cycle[0], $cycle[1]] = [$cycle[1], $cycle[0]];
            }
            foreach ($cycle as $q) {
                if (count($result) >= $count) {
                    break;
                }
                $result[] = $q;
            }
        }

        return $result;
    }

    /**
     * Identity of a question for variety purposes: the musical content only.
     * Options order, tempo jitter and ids are ignored so two questions that
     * merely differ in shuffled choices still count as the same question.
     */
    private function questionVariantKey(object $q): string
    {
        $attrs = $q->getAttributes();
        unset($attrs['id'], $attrs['options'], $attrs['other_options'], $attrs['tempo']);
        ksort($attrs);

        return md5(json_encode($attrs));
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
