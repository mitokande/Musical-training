<?php

namespace App\Services\Practice;

use App\Livewire\PracticeIntervalComparison;
use App\Livewire\PracticeIntervalDirection;
use App\Livewire\PracticeMelodicInterval;
use App\Models\ChordPractice;
use App\Models\ScalePractice;

/**
 * Maps the mobile client's practice config onto the generator's config_json.
 *
 * The equivalent mapping lives inline in each App\Livewire\Practice*::mount().
 * Rather than duplicate the abbreviation tables, this reuses the constants and
 * helpers those components already expose (INTERVAL_POOL_MAP,
 * POOL_TO_SEMITONES, buildPairsFromPool).
 */
class StudioConfigMapper
{
    private const DEFAULT_ROOTS = ['C', 'D', 'E', 'F', 'G', 'A', 'B'];

    /**
     * The single-note keyboard the web setup screen actually draws: twelve
     * keys, sharps included (exercise-setup.blade.php).
     */
    private const SINGLE_NOTE_KEYS = ['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'];

    /** "Play notes in groups of" — the web ladder starts at two. */
    private const GROUP_SIZES = [2, 3, 4, 5, 6, 7, 8, 9];

    /** Note values the rhythm generator understands as cell tokens. */
    private const DEFAULT_RHYTHM_VALUES = ['quarter', 'eighth', 'half'];

    /**
     * Meters the rhythm generator supports, in the order the web setup screen
     * lists them. x/8 is read as compound (num/3 dotted-quarter beats) and x/2
     * as alla breve; 5/8 and 7/8 are deliberately absent, since intdiv($num, 3)
     * would give them the wrong beats per bar.
     */
    private const TIME_SIGNATURES = ['2/4', '3/4', '4/4', '6/8', '9/8', '2/2', '3/2', '4/2'];

    /**
     * Note values a rhythm question can be built from. The five plain values
     * plus the dotted ones and the triplet eighth — all of which already have
     * generator cells and already survive `rhythmValues()`.
     */
    private const RHYTHM_NOTE_VALUES = [
        'whole', 'half', 'quarter', 'eighth', 'sixteenth',
        'dotted-half', 'dotted-quarter', 'dotted-eighth',
        'triplet-eighth',
    ];

    /**
     * @return array<string,mixed> config_json for LearningPathQuestionGenerator
     */
    public function map(string $practiceType, array $config): array
    {
        $clef = $config['clef'] ?? 'treble';

        $base = ['practice_type' => $practiceType, 'clef' => $clef];

        return match ($practiceType) {
            'melodic-interval-practice', 'harmonic-interval-practice' => $base + [
                'allowed_intervals' => $this->intervalNames($config),
                'allowed_notes' => self::DEFAULT_ROOTS,
            ],

            'interval-direction-practice' => $base + [
                'allowed_intervals_semitones' => $this->intervalSemitones($config),
                'allowed_notes' => self::DEFAULT_ROOTS,
            ],

            'interval-construction-practice' => $base + [
                'allowed_intervals' => $this->intervalNames($config),
                'allowed_root_notes' => self::DEFAULT_ROOTS,
                'direction' => $config['direction'] ?? 'ascending',
            ],

            'interval-comparison-practice' => $base + [
                'allowed_interval_pairs' => $this->intervalPairs($config),
            ],

            'single-note-practice' => $base + [
                'allowed_notes' => $this->allowedNotes($config),
                'distractor_count' => 3,
                'answer_mode' => $config['answer_mode'] ?? 'note-names',
                // How many notes one question sounds. Read back out of
                // config_json by PracticeSessionService, which is where the
                // grouping happens — the generator itself is per-note.
                'group_size' => $this->groupSize($config),
            ],

            'chord-practice' => $base + [
                'allowed_chord_types' => $this->chordTypes($config),
                'allowed_root_notes' => self::DEFAULT_ROOTS,
                'voicing' => $config['voicing'] ?? 'block',
                'include_inversions' => (bool) ($config['include_inversions'] ?? false),
                'distractor_pool' => $this->chordTypes($config),
            ],

            'scale-practice' => $base + [
                'allowed_scale_types' => $this->scaleTypes($config),
                'allowed_root_notes' => self::DEFAULT_ROOTS,
                'direction' => $config['scale_direction'] ?? $config['direction'] ?? 'ascending',
                'distractor_pool' => $this->scaleTypes($config),
                'scale_tempo' => $config['scale_tempo'] ?? 'normal',
            ],

            'rhythm-practice' => [
                'practice_type' => $practiceType,
                'time_signatures' => [$timeSignature = (string) ($config['time_signature'] ?? '4/4')],
                'allowed_note_values' => $this->rhythmValues($config, $timeSignature),
                'tempo_range' => $this->tempoRange($config, 80, 40, 160),
                // Clamped to what the schema publishes. Unclamped, bars = 0 asks
                // the generator for a pattern of nothing and gets a 422 back,
                // and bars = 12 quietly builds a twelve-bar question no setup
                // screen offers.
                'bars' => max(1, min(2, (int) ($config['bars'] ?? 1))),
                'include_rests' => (bool) ($config['include_rests'] ?? false),
                'rhythm_difficulty' => $config['rhythm_difficulty'] ?? 'medium',
            ],

            'melodic-dictation' => $base + [
                'key_signatures' => [$config['key_signature'] ?? 'C'],
                'mode' => $config['mode'] ?? 'major',
                'difficulty' => $config['difficulty'] ?? 'intermediate',
                'accidentals' => $config['accidentals'] ?? 'auto',
                'include_rhythm' => true,
                'time_signature' => $config['time_signature'] ?? '4/4',
                'allowed_note_values' => $this->dictationValues($config),
                'bars' => (int) ($config['bars'] ?? 2),
                'tempo_range' => $this->tempoRange($config, 60),
            ],

            default => $base,
        };
    }

    /**
     * The option contract the mobile setup screen renders from, so the app
     * never hardcodes vocabularies that live in PHP.
     */
    public function configSchema(string $practiceType): array
    {
        $clefs = ['treble', 'alto', 'bass'];
        $intervals = array_keys(PracticeMelodicInterval::INTERVAL_POOL_MAP);

        $common = [
            'question_count' => ['type' => 'int', 'min' => 5, 'max' => 20, 'default' => 10],
        ];

        return match ($practiceType) {
            'melodic-interval-practice', 'harmonic-interval-practice' => $common + [
                'clef' => ['type' => 'enum', 'values' => $clefs, 'default' => 'treble'],
                'interval_pool' => ['type' => 'multi', 'values' => $intervals, 'default' => ['M2', 'M3', 'P5']],
            ],
            'interval-direction-practice', 'interval-comparison-practice' => $common + [
                'clef' => ['type' => 'enum', 'values' => $clefs, 'default' => 'treble'],
                'interval_pool' => ['type' => 'multi', 'values' => $intervals, 'default' => ['M2', 'M3', 'P5']],
            ],
            'interval-construction-practice' => $common + [
                'clef' => ['type' => 'enum', 'values' => $clefs, 'default' => 'treble'],
                'interval_pool' => ['type' => 'multi', 'values' => $intervals, 'default' => ['M3', 'P5']],
                'direction' => ['type' => 'enum', 'values' => ['ascending', 'descending', 'mixed'], 'default' => 'ascending'],
            ],
            // Transcribed from the web's own setup screen: twelve keys opening
            // on C alone, a group ladder of 2-9, and an unlabelled keyboard by
            // default. This schema used to publish seven naturals, no group
            // size and 'note-names', which left the mobile app widening it
            // client-side and offering a group size the API then ignored.
            'single-note-practice' => $common + [
                'clef' => ['type' => 'enum', 'values' => $clefs, 'default' => 'treble'],
                'allowed_notes' => ['type' => 'multi', 'values' => self::SINGLE_NOTE_KEYS, 'default' => ['C']],
                'group_size' => [
                    'type' => 'int',
                    'values' => array_map('strval', self::GROUP_SIZES),
                    'min' => self::GROUP_SIZES[0],
                    'max' => self::GROUP_SIZES[count(self::GROUP_SIZES) - 1],
                    'default' => self::GROUP_SIZES[0],
                ],
                'answer_mode' => ['type' => 'enum', 'values' => ['keyboard', 'note-names'], 'default' => 'keyboard'],
            ],
            'chord-practice' => $common + [
                'clef' => ['type' => 'enum', 'values' => $clefs, 'default' => 'treble'],
                'chord_types' => [
                    'type' => 'multi',
                    'values' => array_keys(ChordPractice::chordIntervals()),
                    'default' => ['Major', 'Minor', 'Diminished', 'Augmented'],
                ],
                'voicing' => ['type' => 'enum', 'values' => ['block', 'arpeggiated'], 'default' => 'block'],
                'include_inversions' => ['type' => 'bool', 'default' => false],
            ],
            'scale-practice' => $common + [
                'clef' => ['type' => 'enum', 'values' => $clefs, 'default' => 'treble'],
                'scale_types' => [
                    'type' => 'multi',
                    'values' => array_keys(ScalePractice::scaleIntervals()),
                    'default' => ['Major', 'Natural Minor', 'Harmonic Minor'],
                ],
                'scale_direction' => ['type' => 'enum', 'values' => ['ascending', 'descending', 'both'], 'default' => 'ascending'],
                'scale_tempo' => ['type' => 'enum', 'values' => ['slow', 'normal', 'fast'], 'default' => 'normal'],
            ],
            'rhythm-practice' => $common + [
                // Every meter the Exercise Setup Studio offers, and every one
                // the generator handles: den 8 is compound, den 2 is alla breve
                // (num * 2 quarter-units), everything else is simple. The four
                // published before were a subset of what already worked, which
                // left the apps either short of the website or carrying their
                // own list to make up the difference.
                'time_signature' => [
                    'type' => 'enum',
                    'values' => self::TIME_SIGNATURES,
                    'default' => '4/4',
                ],
                // The tokens `rhythmValues()` lets through and the cell pools
                // are built from. Rests are absent on purpose: they are not cell
                // tokens, they arrive through `include_rests`.
                'note_values' => [
                    'type' => 'multi',
                    'values' => self::RHYTHM_NOTE_VALUES,
                    'default' => self::DEFAULT_RHYTHM_VALUES,
                ],
                'tempo' => ['type' => 'int', 'min' => 40, 'max' => 160, 'default' => 80],
                'bars' => ['type' => 'int', 'min' => 1, 'max' => 2, 'default' => 1],
                'include_rests' => ['type' => 'bool', 'default' => false],
                // Playback, not generation: `map()` never forwards it and the
                // generator never sees it. It is published because it is part
                // of the exercise as a learner sets it up — the website has the
                // switch, so a client that renders this schema gets it too.
                'metronome' => ['type' => 'bool', 'default' => true],
            ],
            'melodic-dictation' => $common + [
                'clef' => ['type' => 'enum', 'values' => $clefs, 'default' => 'treble'],
                'key_signature' => [
                    'type' => 'enum',
                    'values' => ['C', 'G', 'D', 'A', 'F', 'Bb', 'Eb'],
                    'default' => 'C',
                ],
                'mode' => ['type' => 'enum', 'values' => ['major', 'minor'], 'default' => 'major'],
                'difficulty' => ['type' => 'enum', 'values' => ['beginner', 'intermediate', 'advanced'], 'default' => 'beginner'],
                'time_signature' => ['type' => 'enum', 'values' => ['2/4', '3/4', '4/4'], 'default' => '4/4'],
                'note_values' => [
                    'type' => 'multi',
                    'values' => ['half', 'quarter', 'eighth'],
                    'default' => ['quarter', 'eighth'],
                ],
                'bars' => ['type' => 'int', 'min' => 1, 'max' => 4, 'default' => 2],
                'tempo' => ['type' => 'int', 'min' => 40, 'max' => 120, 'default' => 60],
            ],
            default => $common,
        };
    }

    /**
     * Interval abbreviations (m2, M3, TT…) are not understood by the generator
     * and must be expanded to full names.
     */
    private function intervalNames(array $config): array
    {
        $pool = $config['interval_pool'] ?? [];

        $names = array_values(array_filter(array_map(
            fn ($a) => PracticeMelodicInterval::INTERVAL_POOL_MAP[$a] ?? null,
            (array) $pool,
        )));

        return $names ?: ['Major 2nd', 'Major 3rd', 'Perfect 5th'];
    }

    private function intervalSemitones(array $config): array
    {
        $pool = $config['interval_pool'] ?? [];

        $semitones = array_values(array_filter(array_map(
            fn ($a) => PracticeIntervalDirection::POOL_TO_SEMITONES[$a] ?? null,
            (array) $pool,
        )));

        return $semitones ?: range(1, 12);
    }

    private function intervalPairs(array $config): array
    {
        $pool = (array) ($config['interval_pool'] ?? []);

        if ($pool !== []) {
            $pairs = PracticeIntervalComparison::buildPairsFromPool($pool);
            if ($pairs !== []) {
                return $pairs;
            }
        }

        return PracticeIntervalComparison::buildPairsFromPool(['m2', 'M3', 'P5', '8ve']);
    }

    private function allowedNotes(array $config): array
    {
        $notes = array_values(array_filter(
            (array) ($config['allowed_notes'] ?? []),
            fn ($n) => in_array($n, self::SINGLE_NOTE_KEYS, true),
        ));

        return $notes ?: self::DEFAULT_ROOTS;
    }

    /**
     * Notes per question. Absent — an older client, or a lesson — means one,
     * which is the shape every caller had before groups existed.
     */
    private function groupSize(array $config): int
    {
        $size = (int) ($config['group_size'] ?? 1);

        return max(1, min(self::GROUP_SIZES[count(self::GROUP_SIZES) - 1], $size));
    }

    private function chordTypes(array $config): array
    {
        $types = array_values(array_filter((array) ($config['chord_types'] ?? [])));

        return $types ?: ['Major', 'Minor', 'Diminished', 'Augmented'];
    }

    private function scaleTypes(array $config): array
    {
        $types = array_values(array_filter((array) ($config['scale_types'] ?? [])));

        return $types ?: ['Major', 'Natural Minor', 'Harmonic Minor'];
    }

    /**
     * Rests and triplet-quarter are not cell tokens; rests are injected after
     * assembly via include_rests instead (mirrors PracticeRhythm::mount()).
     *
     * The selection also has to leave the bar fillable. A pool of nothing but
     * long values has no cell that fits a short bar — {whole} in 3/4, say — and
     * the generator answers that by returning nothing at all, which surfaces to
     * the learner as a 422 rather than as an exercise. Worse, when the filter
     * empties the pool the generator falls back to the ENTIRE unfiltered pool,
     * so a compound selection without an eighth silently practises tokens
     * nobody asked for. One added token keeps the choice honest in both cases:
     * the beat unit of the meter, which every bar can be filled with.
     */
    private function rhythmValues(array $config, string $timeSignature = '4/4'): array
    {
        $values = array_values(array_filter(
            (array) ($config['note_values'] ?? []),
            fn ($v) => ! str_contains((string) $v, '_rest') && $v !== 'triplet-quarter',
        ));

        $values = $values ?: self::DEFAULT_RHYTHM_VALUES;

        // Compound meters count in dotted-quarter beats and every one of their
        // cells is built from eighths; simple meters need something no longer
        // than a quarter.
        $compound = (int) (explode('/', $timeSignature)[1] ?? 4) === 8;
        $fits = $compound ? ['eighth'] : ['quarter', 'eighth', 'sixteenth'];

        if (array_intersect($values, $fits) === []) {
            $values[] = $compound ? 'eighth' : 'quarter';
        }

        return $values;
    }

    private function dictationValues(array $config): array
    {
        $values = array_values(array_filter(
            (array) ($config['note_values'] ?? []),
            fn ($v) => ! str_contains((string) $v, '_rest'),
        ));

        return $values ?: ['quarter', 'eighth'];
    }

    /**
     * A single tempo, held as the degenerate range the generator expects and
     * clamped to the range the schema publishes — the value is written straight
     * onto every question, and nothing downstream would question a 9999.
     */
    private function tempoRange(array $config, int $default, int $min = 20, int $max = 240): array
    {
        $tempo = (int) ($config['tempo'] ?? $default);
        $tempo = max($min, min($max, $tempo));

        return [$tempo, $tempo];
    }
}
