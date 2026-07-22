<?php

namespace App\Services\Teacher;

use App\Services\MusicTheoryService;
use InvalidArgumentException;

/**
 * Builds canonical LearningPathQuestionGenerator config_json for teacher
 * assignments from a (practice type, difficulty, overrides) triple.
 *
 * The vocabulary here mirrors the Learning Path seed configs exactly — those
 * are the values the generator, blades, and audio playback are proven
 * against. Overrides are validated against whitelists so a teacher (or the
 * AI builder) can never push an unsupported value into the pipeline.
 */
class TeacherAssignmentConfigFactory
{
    public function __construct(private MusicTheoryService $music) {}

    public const PRACTICE_TYPES = [
        'single-note-practice',
        'melodic-interval-practice',
        'harmonic-interval-practice',
        'interval-direction-practice',
        'interval-comparison-practice',
        'interval-construction-practice',
        'chord-practice',
        'scale-practice',
        'rhythm-practice',
        'melodic-dictation',
    ];

    public const DIFFICULTIES = ['beginner', 'intermediate', 'advanced'];

    public const NATURAL_NOTES = ['C', 'D', 'E', 'F', 'G', 'A', 'B'];

    // Canonical model vocabulary (ChordPractice::chordIntervals /
    // ScalePractice::scaleIntervals keys) — the same values Exercise Setup
    // sends. Lowercase slugs do NOT resolve to intervals and must never be
    // stored in configs.
    public const CHORD_TYPES = [
        'Major', 'Minor', 'Augmented', 'Diminished', 'Sus2', 'Sus4',
        'Dominant 7th', 'Major 7th', 'Minor 7th', 'Half-Diminished 7th', 'Diminished 7th',
    ];

    public const SCALE_TYPES = [
        'Major', 'Natural Minor', 'Harmonic Minor', 'Melodic Minor',
        'Dorian', 'Phrygian', 'Lydian', 'Mixolydian', 'Aeolian', 'Locrian',
        'Major Pentatonic', 'Minor Pentatonic', 'Blues Scale', 'Chromatic Scale', 'Whole Tone Scale',
    ];

    public const NOTE_VALUES = [
        'whole', 'half', 'dotted-half', 'quarter', 'dotted-quarter',
        'eighth', 'sixteenth', 'quarter_rest', 'eighth_rest',
    ];

    public const TIME_SIGNATURES = ['4/4', '3/4', '2/4', '6/8'];

    public const CLEFS = ['treble', 'bass', 'alto'];

    public const SCALE_TEMPOS = ['slow', 'normal', 'fast'];

    public function supportedTypes(): array
    {
        return self::PRACTICE_TYPES;
    }

    /**
     * @param  array  $overrides  optional teacher/AI constraints, validated here
     * @return array canonical generator config (includes 'practice_type')
     */
    public function build(string $practiceType, string $difficulty, array $overrides = []): array
    {
        if (! in_array($practiceType, self::PRACTICE_TYPES, true)) {
            throw new InvalidArgumentException("Unsupported practice type: {$practiceType}");
        }

        if (! in_array($difficulty, self::DIFFICULTIES, true)) {
            throw new InvalidArgumentException("Unsupported difficulty: {$difficulty}");
        }

        $config = $this->preset($practiceType, $difficulty);
        $config = $this->applyOverrides($practiceType, $config, $overrides);

        // Dictation melodies come from an explicit note pool, not from clef
        // math — fit the pool to the selected clef's playable range so a bass
        // (or alto) override never yields out-of-range pitches.
        if ($practiceType === 'melodic-dictation') {
            $config['note_pool'] = $this->fitNotePoolToClef(
                $config['note_pool'] ?? [],
                $config['clef'] ?? 'treble',
            );
        }

        $config['practice_type'] = $practiceType;

        return $config;
    }

    /**
     * Fit a note pool ('C4'-style entries) to a clef's playable range
     * (CLEF_RANGES). Bass pools are first shifted down an octave since the
     * presets are written in the treble register; anything still outside the
     * range is dropped. Falls back to the original pool if nothing survives.
     */
    private function fitNotePoolToClef(array $pool, string $clef): array
    {
        [$min, $max] = $this->music->clefRangeMidi($clef);
        $shift = $clef === 'bass' ? -1 : 0;

        $fitted = [];
        foreach ($pool as $entry) {
            if (! preg_match('/^([A-G][#b]?)(\d)$/', (string) $entry, $m)) {
                continue;
            }
            $octave = (int) $m[2] + $shift;
            $midi = $this->music->midiNumber($m[1], $octave);
            if ($midi !== null && $midi >= $min && $midi <= $max) {
                $fitted[] = $m[1].$octave;
            }
        }

        return $fitted !== [] ? $fitted : $pool;
    }

    private function preset(string $type, string $difficulty): array
    {
        // Pitched types follow the Exercise Setup Studio rules exactly: the
        // config carries a clef and NO hardcoded octave — the generator places
        // every note inside the clef's playable range (CLEF_RANGES: treble
        // G3–G5, bass C2–C4, alto C3–C5). Difficulty only widens the
        // note/interval vocabulary, never the pitch range.
        return match ($type) {
            'single-note-practice' => match ($difficulty) {
                'beginner' => ['target_type' => 'note', 'allowed_notes' => ['C', 'D', 'E', 'F', 'G'], 'clef' => 'treble', 'distractor_count' => 3],
                'intermediate' => ['target_type' => 'note', 'allowed_notes' => self::NATURAL_NOTES, 'clef' => 'treble', 'distractor_count' => 3],
                'advanced' => ['target_type' => 'note', 'allowed_notes' => array_merge(self::NATURAL_NOTES, ['C#', 'F#', 'Bb', 'Eb']), 'clef' => 'treble', 'distractor_count' => 3],
            },
            'melodic-interval-practice', 'harmonic-interval-practice' => array_merge(
                match ($difficulty) {
                    'beginner' => ['allowed_intervals' => ['Major 2nd', 'Minor 3rd', 'Major 3rd', 'Perfect 4th', 'Perfect 5th', 'Perfect Octave'], 'allowed_notes' => self::NATURAL_NOTES, 'clef' => 'treble'],
                    'intermediate' => ['allowed_intervals' => ['Minor 2nd', 'Major 2nd', 'Minor 3rd', 'Major 3rd', 'Perfect 4th', 'Perfect 5th', 'Minor 6th', 'Major 6th', 'Perfect Octave'], 'allowed_notes' => array_merge(self::NATURAL_NOTES, ['F#', 'Bb']), 'clef' => 'treble'],
                    'advanced' => ['allowed_intervals' => array_keys(MusicTheoryService::INTERVAL_SEMITONES), 'allowed_notes' => array_merge(self::NATURAL_NOTES, ['C#', 'F#', 'G#', 'Bb', 'Eb']), 'clef' => 'treble'],
                },
                // Melodic intervals mix ascending/descending questions from
                // intermediate up; harmonic intervals sound simultaneously,
                // so direction does not apply there.
                $type === 'melodic-interval-practice' && $difficulty !== 'beginner'
                    ? ['direction' => 'mixed']
                    : [],
            ),
            'interval-direction-practice' => match ($difficulty) {
                'beginner' => ['allowed_intervals_semitones' => [4, 7, 12], 'allowed_notes' => ['C', 'D', 'E', 'F', 'G'], 'clef' => 'treble'],
                'intermediate' => ['allowed_intervals_semitones' => [2, 3, 4, 5, 7], 'allowed_notes' => self::NATURAL_NOTES, 'clef' => 'treble'],
                'advanced' => ['allowed_intervals_semitones' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12], 'allowed_notes' => self::NATURAL_NOTES, 'clef' => 'treble'],
            },
            'interval-comparison-practice' => match ($difficulty) {
                'beginner' => ['allowed_interval_pairs' => [['C,D', 'C,G'], ['D,E', 'D,A'], ['C,E', 'C,A'], ['E,F#', 'E,B']], 'clef' => 'treble'],
                'intermediate' => ['allowed_interval_pairs' => [['C,D', 'C,E'], ['D,E', 'D,F#'], ['E,F#', 'E,G#'], ['C,E', 'C,G'], ['F,G', 'F,A']], 'clef' => 'treble'],
                'advanced' => ['allowed_interval_pairs' => [['C,E', 'C,F'], ['D,F#', 'D,G'], ['C,G', 'C,A'], ['E,G#', 'E,A'], ['G,B', 'G,C']], 'clef' => 'treble'],
            },
            'interval-construction-practice' => match ($difficulty) {
                'beginner' => ['allowed_intervals' => ['Major 2nd', 'Major 3rd', 'Perfect 4th', 'Perfect 5th'], 'allowed_root_notes' => ['C', 'D', 'E', 'F', 'G'], 'clef' => 'treble'],
                'intermediate' => ['allowed_intervals' => ['Minor 2nd', 'Major 2nd', 'Minor 3rd', 'Major 3rd', 'Perfect 4th', 'Perfect 5th', 'Major 6th'], 'allowed_root_notes' => self::NATURAL_NOTES, 'clef' => 'treble', 'direction' => 'mixed'],
                'advanced' => ['allowed_intervals' => ['Minor 2nd', 'Major 2nd', 'Minor 3rd', 'Major 3rd', 'Perfect 4th', 'Tritone', 'Perfect 5th', 'Minor 6th', 'Major 6th', 'Minor 7th', 'Major 7th', 'Perfect Octave'], 'allowed_root_notes' => array_merge(self::NATURAL_NOTES, ['F#', 'Bb', 'Eb']), 'clef' => 'treble', 'direction' => 'mixed'],
            },
            'chord-practice' => match ($difficulty) {
                'beginner' => ['allowed_chord_types' => ['Major', 'Minor', 'Diminished'], 'allowed_root_notes' => ['C', 'F', 'G'], 'voicing' => 'block', 'include_inversions' => false, 'clef' => 'treble', 'distractor_pool' => ['Augmented', 'Diminished', 'Minor', 'Major']],
                'intermediate' => ['allowed_chord_types' => ['Major', 'Minor', 'Diminished', 'Augmented'], 'allowed_root_notes' => self::NATURAL_NOTES, 'voicing' => 'block', 'include_inversions' => false, 'clef' => 'treble', 'distractor_pool' => ['Major', 'Minor', 'Augmented', 'Diminished', 'Dominant 7th']],
                'advanced' => ['allowed_chord_types' => ['Major', 'Minor', 'Diminished', 'Augmented', 'Dominant 7th', 'Major 7th', 'Minor 7th'], 'allowed_root_notes' => self::NATURAL_NOTES, 'voicing' => 'block', 'include_inversions' => true, 'clef' => 'treble', 'distractor_pool' => ['Major', 'Minor', 'Augmented', 'Diminished', 'Dominant 7th', 'Major 7th', 'Minor 7th', 'Half-Diminished 7th']],
            },
            'scale-practice' => match ($difficulty) {
                'beginner' => ['allowed_scale_types' => ['Major', 'Natural Minor', 'Harmonic Minor'], 'allowed_root_notes' => ['C', 'G', 'F'], 'direction' => 'ascending', 'clef' => 'treble', 'distractor_pool' => ['Major', 'Natural Minor', 'Harmonic Minor', 'Major Pentatonic']],
                'intermediate' => ['allowed_scale_types' => ['Major', 'Natural Minor', 'Harmonic Minor', 'Melodic Minor'], 'allowed_root_notes' => ['C', 'D', 'E', 'F', 'G', 'A'], 'direction' => 'ascending', 'clef' => 'treble', 'distractor_pool' => ['Major', 'Natural Minor', 'Harmonic Minor', 'Melodic Minor', 'Dorian']],
                'advanced' => ['allowed_scale_types' => ['Major', 'Natural Minor', 'Harmonic Minor', 'Melodic Minor', 'Dorian', 'Phrygian', 'Lydian', 'Mixolydian'], 'allowed_root_notes' => self::NATURAL_NOTES, 'direction' => 'ascending', 'clef' => 'treble', 'distractor_pool' => ['Dorian', 'Phrygian', 'Lydian', 'Mixolydian', 'Aeolian', 'Locrian', 'Major Pentatonic', 'Minor Pentatonic']],
            },
            'rhythm-practice' => match ($difficulty) {
                'beginner' => ['time_signatures' => ['4/4'], 'allowed_note_values' => ['quarter', 'half', 'whole', 'eighth'], 'tempo_range' => [72, 84], 'bars' => 1, 'rhythm_difficulty' => 'easy'],
                'intermediate' => ['time_signatures' => ['4/4', '3/4'], 'allowed_note_values' => ['quarter', 'half', 'eighth', 'dotted-half'], 'tempo_range' => [76, 92], 'bars' => 1, 'rhythm_difficulty' => 'medium'],
                'advanced' => ['time_signatures' => ['4/4', '3/4', '6/8'], 'allowed_note_values' => ['quarter', 'half', 'eighth', 'sixteenth', 'dotted-quarter', 'quarter_rest', 'eighth_rest'], 'tempo_range' => [84, 108], 'bars' => 2, 'rhythm_difficulty' => 'hard'],
            },
            'melodic-dictation' => match ($difficulty) {
                'beginner' => ['note_pool' => ['C4', 'D4', 'E4', 'F4', 'G4'], 'melody_length' => 4, 'clef' => 'treble', 'key_signatures' => ['C', 'G'], 'tempo_range' => [57, 63], 'include_rhythm' => false, 'bars' => 1, 'difficulty' => 'beginner'],
                'intermediate' => ['note_pool' => ['C4', 'D4', 'E4', 'F4', 'G4', 'A4', 'B4', 'C5'], 'melody_length' => 6, 'clef' => 'treble', 'key_signatures' => ['C', 'G', 'F'], 'tempo_range' => [63, 76], 'include_rhythm' => true, 'time_signature' => '4/4', 'allowed_note_values' => ['quarter', 'eighth', 'half'], 'bars' => 1, 'difficulty' => 'intermediate'],
                'advanced' => ['note_pool' => ['G3', 'A3', 'B3', 'C4', 'D4', 'E4', 'F4', 'G4', 'A4', 'B4', 'C5', 'D5'], 'melody_length' => 8, 'clef' => 'treble', 'key_signatures' => ['C', 'G', 'D', 'F', 'Bb'], 'tempo_range' => [69, 84], 'include_rhythm' => true, 'time_signature' => '4/4', 'allowed_note_values' => ['quarter', 'eighth', 'half', 'dotted-quarter'], 'bars' => 2, 'difficulty' => 'advanced'],
            },
        };
    }

    /**
     * Merge validated overrides into the preset. Unknown keys and values
     * outside the whitelists throw so unsupported requests fail loudly
     * instead of producing invalid questions.
     */
    private function applyOverrides(string $type, array $config, array $overrides): array
    {
        foreach ($overrides as $key => $value) {
            if ($value === null || $value === [] || $value === '') {
                continue;
            }

            $config = match ($key) {
                'allowed_intervals' => $this->overrideList($config, $key, $value, array_keys(MusicTheoryService::INTERVAL_SEMITONES), $type, ['melodic-interval-practice', 'harmonic-interval-practice', 'interval-construction-practice']),
                'allowed_intervals_semitones' => $this->overrideList($config, $key, array_map('intval', (array) $value), range(1, 12), $type, ['interval-direction-practice']),
                'allowed_notes' => $this->overrideList($config, $key, $value, array_keys(MusicTheoryService::NOTE_SEMITONES), $type, ['single-note-practice', 'melodic-interval-practice', 'harmonic-interval-practice', 'interval-direction-practice']),
                'allowed_root_notes' => $this->overrideList($config, $key, $value, array_keys(MusicTheoryService::NOTE_SEMITONES), $type, ['interval-construction-practice', 'chord-practice', 'scale-practice']),
                'allowed_chord_types' => $this->overrideList($config, $key, $value, self::CHORD_TYPES, $type, ['chord-practice']),
                'allowed_scale_types' => $this->overrideList($config, $key, $value, self::SCALE_TYPES, $type, ['scale-practice']),
                'allowed_note_values' => $this->overrideList($config, $key, $value, self::NOTE_VALUES, $type, ['rhythm-practice', 'melodic-dictation']),
                'time_signatures' => $this->overrideList($config, $key, $value, self::TIME_SIGNATURES, $type, ['rhythm-practice']),
                'time_signature' => $this->overrideScalar($config, $key, $value, self::TIME_SIGNATURES, $type, ['melodic-dictation']),
                'clef' => $this->overrideScalar($config, $key, $value, self::CLEFS, $type, ['single-note-practice', 'melodic-interval-practice', 'harmonic-interval-practice', 'interval-construction-practice', 'interval-direction-practice', 'interval-comparison-practice', 'melodic-dictation', 'chord-practice', 'scale-practice']),
                'key_signatures' => $this->overrideList($config, $key, $value, ['C', 'G', 'D', 'A', 'E', 'F', 'Bb', 'Eb', 'Ab'], $type, ['melodic-dictation']),
                'tempo_range' => $this->overrideTempo($config, $value, $type),
                'scale_tempo' => $this->overrideScalar($config, $key, $value, self::SCALE_TEMPOS, $type, ['scale-practice']),
                'bars' => $this->overrideInt($config, $key, $value, 1, 4, $type, ['rhythm-practice', 'melodic-dictation']),
                'melody_length' => $this->overrideInt($config, $key, $value, 2, 12, $type, ['melodic-dictation']),
                'include_rests' => $this->overrideBool($config, $key, $value, $type, ['rhythm-practice']),
                'include_inversions' => $this->overrideBool($config, $key, $value, $type, ['chord-practice']),
                'include_rhythm' => $this->overrideBool($config, $key, $value, $type, ['melodic-dictation']),
                // Scales are played strictly up or down; interval types also
                // support a per-question ascending/descending blend.
                'direction' => $type === 'scale-practice'
                    ? $this->overrideScalar($config, $key, $value, ['ascending', 'descending'], $type, ['scale-practice'])
                    : $this->overrideScalar($config, $key, $value, ['ascending', 'descending', 'mixed'], $type, ['melodic-interval-practice', 'interval-construction-practice']),
                default => throw new InvalidArgumentException("Unsupported configuration option: {$key}"),
            };
        }

        return $config;
    }

    private function overrideList(array $config, string $key, array|string $value, array $whitelist, string $type, array $allowedTypes): array
    {
        $this->assertTypeSupports($key, $type, $allowedTypes);

        $values = array_values((array) $value);
        $invalid = array_diff($values, $whitelist);

        if ($invalid !== []) {
            throw new InvalidArgumentException("Unsupported value for {$key}: ".implode(', ', array_map('strval', $invalid)));
        }

        $config[$key] = $values;

        return $config;
    }

    private function overrideScalar(array $config, string $key, string $value, array $whitelist, string $type, array $allowedTypes): array
    {
        $this->assertTypeSupports($key, $type, $allowedTypes);

        if (! in_array($value, $whitelist, true)) {
            throw new InvalidArgumentException("Unsupported value for {$key}: {$value}");
        }

        $config[$key] = $value;

        return $config;
    }

    private function overrideInt(array $config, string $key, mixed $value, int $min, int $max, string $type, array $allowedTypes): array
    {
        $this->assertTypeSupports($key, $type, $allowedTypes);

        $int = (int) $value;
        if ($int < $min || $int > $max) {
            throw new InvalidArgumentException("{$key} must be between {$min} and {$max}");
        }

        $config[$key] = $int;

        return $config;
    }

    private function overrideBool(array $config, string $key, mixed $value, string $type, array $allowedTypes): array
    {
        $this->assertTypeSupports($key, $type, $allowedTypes);

        $config[$key] = (bool) $value;

        return $config;
    }

    private function overrideTempo(array $config, mixed $value, string $type): array
    {
        $this->assertTypeSupports('tempo_range', $type, ['rhythm-practice', 'melodic-dictation']);

        $range = array_values(array_map('intval', (array) $value));
        if (count($range) !== 2 || $range[0] < 40 || $range[1] > 208 || $range[0] > $range[1]) {
            throw new InvalidArgumentException('tempo_range must be [min, max] between 40 and 208');
        }

        $config['tempo_range'] = $range;

        return $config;
    }

    private function assertTypeSupports(string $key, string $type, array $allowedTypes): void
    {
        if (! in_array($type, $allowedTypes, true)) {
            throw new InvalidArgumentException("{$key} is not supported for {$type}");
        }
    }
}
