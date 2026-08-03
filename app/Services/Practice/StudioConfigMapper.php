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

    /** Note values the rhythm generator understands as cell tokens. */
    private const DEFAULT_RHYTHM_VALUES = ['quarter', 'eighth', 'half'];

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
                'time_signatures' => [$config['time_signature'] ?? '4/4'],
                'allowed_note_values' => $this->rhythmValues($config),
                'tempo_range' => $this->tempoRange($config, 80),
                'bars' => (int) ($config['bars'] ?? 1),
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
            'single-note-practice' => $common + [
                'clef' => ['type' => 'enum', 'values' => $clefs, 'default' => 'treble'],
                'allowed_notes' => ['type' => 'multi', 'values' => self::DEFAULT_ROOTS, 'default' => self::DEFAULT_ROOTS],
                'answer_mode' => ['type' => 'enum', 'values' => ['note-names', 'keyboard'], 'default' => 'note-names'],
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
                'time_signature' => ['type' => 'enum', 'values' => ['2/4', '3/4', '4/4', '6/8'], 'default' => '4/4'],
                'note_values' => [
                    'type' => 'multi',
                    'values' => ['whole', 'half', 'quarter', 'eighth', 'sixteenth'],
                    'default' => self::DEFAULT_RHYTHM_VALUES,
                ],
                'tempo' => ['type' => 'int', 'min' => 40, 'max' => 160, 'default' => 80],
                'bars' => ['type' => 'int', 'min' => 1, 'max' => 2, 'default' => 1],
                'include_rests' => ['type' => 'bool', 'default' => false],
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
        $notes = array_values(array_filter((array) ($config['allowed_notes'] ?? [])));

        return $notes ?: self::DEFAULT_ROOTS;
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
     */
    private function rhythmValues(array $config): array
    {
        $values = array_values(array_filter(
            (array) ($config['note_values'] ?? []),
            fn ($v) => ! str_contains((string) $v, '_rest') && $v !== 'triplet-quarter',
        ));

        return $values ?: self::DEFAULT_RHYTHM_VALUES;
    }

    private function dictationValues(array $config): array
    {
        $values = array_values(array_filter(
            (array) ($config['note_values'] ?? []),
            fn ($v) => ! str_contains((string) $v, '_rest'),
        ));

        return $values ?: ['quarter', 'eighth'];
    }

    private function tempoRange(array $config, int $default): array
    {
        $tempo = (int) ($config['tempo'] ?? $default);

        return [$tempo, $tempo];
    }
}
