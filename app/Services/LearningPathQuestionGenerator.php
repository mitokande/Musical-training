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
    public function __construct(private MusicTheoryService $music) {}

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

    // ── MELODIC INTERVALS ────────────────────────────────────────────────────

    private function generateMelodicIntervals(array $cfg, int $count): Collection
    {
        $intervals = $cfg['allowed_intervals'] ?? ['Major 2nd'];
        $notes = $cfg['allowed_notes'] ?? ['C', 'D', 'E', 'F', 'G'];
        $octaves = $cfg['octave_range'] ?? ['4'];
        $allIntervals = array_keys(MusicTheoryService::INTERVAL_SEMITONES);

        $distractorCount = count($intervals) <= 2 ? 1 : 3;
        $distractorPool = $distractorCount > 1 && count($intervals) < 4
            ? $allIntervals
            : $intervals;
        $canonicalPool = $this->canonicalIntervalPool();

        $pool = [];
        foreach ($intervals as $interval) {
            foreach ($notes as $note) {
                $octave = (int) $octaves[array_rand($octaves)];
                $result = $this->music->noteAboveByInterval($note, $octave, $interval);
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
                $q->options = $fullOptions;
                $pool[] = $q;
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
        $allIntervals = array_keys(MusicTheoryService::INTERVAL_SEMITONES);
        $canonicalPool = $this->canonicalIntervalPool();

        $pool = [];
        foreach ($intervals as $interval) {
            foreach ($notes as $note) {
                $octave = (int) $octaves[array_rand($octaves)];
                $result = $this->music->noteAboveByInterval($note, $octave, $interval);
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
        $octave = (int) ($cfg['octave'] ?? 4);
        $clef = $cfg['clef'] ?? 'treble';

        $pool = [];
        foreach ($semitones as $st) {
            foreach ($notes as $note) {
                // Ascending variant
                $above = $this->music->noteAboveBySemitones($note, $octave, $st);
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

                // Descending variant
                $below = $this->music->noteBelowBySemitones($note, $octave, $st);
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

        return collect($this->shuffleTake($pool, $count));
    }

    // ── INTERVAL CONSTRUCTION ────────────────────────────────────────────────

    private function generateIntervalConstruction(array $cfg, int $count): Collection
    {
        $intervals = $cfg['allowed_intervals'] ?? ['Major 2nd'];
        $roots = $cfg['allowed_root_notes'] ?? ['C', 'D', 'E', 'F', 'G'];
        $octave = (int) ($cfg['octave'] ?? 4);

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
            fn ($n) => ! preg_match('/(##|bb|x)$/i', $n)
        ));

        $pool = [];
        foreach ($intervals as $interval) {
            foreach ($roots as $root) {
                if ($hasDistractorCfg) {
                    $result = $this->music->preferredNoteAboveByInterval($root, $octave, $interval);
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
                    $result = $this->music->diatonicNoteAboveByInterval($root, $octave, $interval);
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
                $q->setRelation('_options', $options);
                $pool[] = $q;
            }
        }

        return collect($this->shuffleTake($pool, $count));
    }

    // ── INTERVAL COMPARISON ──────────────────────────────────────────────────

    private function generateIntervalComparison(array $cfg, int $count): Collection
    {
        $pairs = $cfg['allowed_interval_pairs'] ?? [['C,D', 'C,E']];
        $octave = $cfg['octave'] ?? '4';
        $clef = $cfg['clef'] ?? 'treble';

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

            $q = new IntervalComparisonPractice;
            $q->id = null;
            $q->interval_a = $a;
            $q->interval_b = $b;
            $q->target = $target;
            $q->octave = $octave;
            $q->clef = $clef;
            $pool[] = $q;

            $qRev = new IntervalComparisonPractice;
            $qRev->id = null;
            $qRev->interval_a = $b;
            $qRev->interval_b = $a;
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

        $octave = (string) ($cfg['octave'] ?? '4');

        $pool = [];
        foreach ($scaleTypes as $type) {
            foreach ($roots as $root) {
                $otherOptions = $this->music->buildOptions($type, $distractors, min(3, count($distractors)));

                $q = new ScalePractice;
                $q->id = null;
                $q->scale_type = $type;
                $q->root_note = $root;
                $q->direction = $direction;
                $q->octave = $octave;
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

        $octave = (string) ($cfg['octave'] ?? '4');

        $pool = [];
        foreach ($chordTypes as $type) {
            foreach ($roots as $root) {
                foreach ($inversionValues as $inv) {
                    $otherOptions = $this->music->buildOptions($type, $distractors, min(3, count($distractors)));

                    $q = new ChordPractice;
                    $q->id = null;
                    $q->chord_type = $type;
                    $q->root_note = $root;
                    $q->voicing = $voicing;
                    $q->inversion = $inv;
                    $q->octave = $octave;
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
            $cells = $this->rhythmCells($family, $difficulty);

            for ($i = 0; $i < max(20, $count * 2); $i++) {
                $pattern = $this->assembleRhythmBars($cells, $beats, $bars);
                if (empty($pattern)) {
                    continue;
                }

                // A few alternative assemblies (unused by the builder UI, kept for parity).
                $otherOptions = [];
                for ($j = 0; $j < 9 && count($otherOptions) < 3; $j++) {
                    $alt = $this->assembleRhythmBars($cells, $beats, $bars);
                    if ($alt !== $pattern && ! in_array($alt, $otherOptions)) {
                        $otherOptions[] = $alt;
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
        $maxDense = max(1, intdiv($beatsPerBar, 2));

        for ($b = 0; $b < $bars; $b++) {
            $remaining = $beatsPerBar;
            $denseUsed = 0;
            while ($remaining > 0) {
                $fitting = array_values(array_filter($cells, fn ($c) => $c['len'] <= $remaining));
                if ($denseUsed >= $maxDense) {
                    $calm = array_values(array_filter($fitting, fn ($c) => ! $isDense($c)));
                    if (! empty($calm)) {
                        $fitting = $calm; // a 1-beat calm cell always exists, so the bar still completes
                    }
                }
                if (empty($fitting)) {
                    return []; // pool can't fill this meter (shouldn't happen)
                }
                $cell = $fitting[array_rand($fitting)];
                array_push($pattern, ...$cell['tokens']);
                if ($isDense($cell)) {
                    $denseUsed++;
                }
                $remaining -= $cell['len'];
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

        $pool = [];

        for ($i = 0; $i < max($count * 3, 30); $i++) {
            shuffle($notePool);
            $melody = array_slice($notePool, 0, $melodyLength);
            for ($k = 1; $k < count($melody); $k++) {
                if ($melody[$k] === $melody[$k - 1]) {
                    $melody[$k] = $notePool[array_rand($notePool)];
                }
            }

            $tempo = rand($tempoRange[0], $tempoRange[1]);
            $keySig = $keySigs[array_rand($keySigs)];

            $q = new MelodicDictationPractice;
            $q->id = null;
            $q->notes = $melody;
            $q->bars = $bars;
            $q->clef = $clef;
            $q->key_signature = $keySig;
            $q->tempo = $tempo;
            $q->include_rhythm = $includeRhythm;
            $pool[] = $q;
        }

        $seen = [];
        $unique = [];
        foreach ($pool as $q) {
            $key = implode(',', $q->notes);
            if (! isset($seen[$key])) {
                $seen[$key] = true;
                $unique[] = $q;
            }
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
