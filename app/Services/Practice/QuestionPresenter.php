<?php

namespace App\Services\Practice;

use App\Models\ChordPractice;
use App\Models\ScalePractice;
use App\Services\MusicTheoryService;

/**
 * Normalizes generator output into one client-facing question shape, and keeps
 * the correct answer on the server.
 *
 * The generator's option fields are inconsistent by type — `other_options` is a
 * comma string for single-note, an array of strings for chord/scale, an array
 * of arrays for rhythm, `options` for intervals, and absent for dictation. This
 * class resolves all of that once, at session-creation time, so the stored
 * question carries a final, already-shuffled option list (`_options`) that is
 * stable across resume.
 *
 * Answer visibility: for every type except rhythm and melodic dictation the
 * answer field is removed from the payload. Those two are unavoidable — the
 * client has to receive the rhythm/melody in order to play it, exactly as the
 * web client does today.
 */
class QuestionPresenter
{
    /** Answer widget the client should render, by practice type. */
    private const ANSWER_MODES = [
        'single-note-practice' => 'note_name',
        'interval-construction-practice' => 'note_name',
        'melodic-dictation' => 'sequence',
        'rhythm-practice' => 'rhythm',
        'melodic-interval-practice' => 'choice',
        'harmonic-interval-practice' => 'choice',
        'interval-direction-practice' => 'choice',
        'interval-comparison-practice' => 'choice',
        'chord-practice' => 'choice',
        'scale-practice' => 'choice',
    ];

    /**
     * Fields removed before the question reaches the client. Rhythm and
     * dictation are deliberately absent — see the class docblock.
     */
    private const STRIPPED_FIELDS = [
        'single-note-practice' => ['target'],
        'melodic-interval-practice' => ['interval'],
        'harmonic-interval-practice' => ['interval'],
        'interval-direction-practice' => ['direction'],
        'interval-construction-practice' => ['note2', 'note2_octave'],
        'interval-comparison-practice' => ['target'],
        'chord-practice' => ['chord_type'],
        'scale-practice' => ['scale_type'],
    ];

    /** Types whose staff shows real content before the user answers. */
    private const NOTATION_MODES = [
        'single-note-practice' => 'user_entry',
        'interval-construction-practice' => 'given_note',
        'melodic-dictation' => 'user_entry',
        'rhythm-practice' => 'option_patterns',
    ];

    public function __construct(
        private readonly MusicTheoryService $music,
        private readonly QuestionAudioResolver $audio,
        private readonly PracticeCatalog $catalog,
    ) {}

    /** Which answer widget a practice type needs. */
    public function answerMode(string $practiceType): string
    {
        return self::ANSWER_MODES[$practiceType] ?? 'choice';
    }

    /**
     * One-time normalization run when a session is created. Resolves the final
     * option list and freezes its order into the stored question.
     */
    public function prepare(array $q, string $practiceType): array
    {
        $q['_options'] = $this->buildOptions($q, $practiceType);

        return $q;
    }

    /**
     * The client-facing DTO. Never returns anything that identifies the answer
     * (rhythm/dictation excepted — see the class docblock).
     */
    public function present(array $q, string $practiceType, int $index): array
    {
        $options = $q['_options'] ?? $this->buildOptions($q, $practiceType);

        return [
            'index' => $index,
            'practice_type' => $practiceType,
            'answer' => [
                'mode' => self::ANSWER_MODES[$practiceType] ?? 'choice',
                'options' => array_map(
                    fn ($o) => ['value' => $o, 'label' => $this->label($o, $practiceType)],
                    $options,
                ),
                'allowed_notes' => $this->allowedNotes($q, $practiceType),
                'sequence_length' => $this->sequenceLength($q, $practiceType),
            ],
            'notation' => $this->notation($q, $practiceType),
            'audio' => $this->audio->resolve($practiceType, $q),
            'meta' => $this->meta($q, $practiceType),
        ];
    }

    /**
     * Final answer options, always including the correct answer and shuffled.
     * Returns an empty list for constructed-answer types.
     */
    public function buildOptions(array $q, string $practiceType): array
    {
        $correct = $this->music->getAnswerFromQuestion($q, $practiceType);

        return match ($practiceType) {
            // Already a complete, shuffled, comma-joined list.
            'single-note-practice' => $this->splitCsv($q['other_options'] ?? ''),

            // The generator stores the full option list on `options`.
            'melodic-interval-practice',
            'harmonic-interval-practice',
            'interval-construction-practice' => $this->normalizeList($q['options'] ?? []),

            // Fixed two-way choices.
            'interval-direction-practice' => ['ascending', 'descending'],
            'interval-comparison-practice' => ['a', 'b'],

            // Distractors only — merge the correct answer in, then shuffle.
            'chord-practice',
            'scale-practice' => $this->shuffleWithCorrect(
                $this->normalizeList($q['other_options'] ?? []),
                $correct,
            ),

            // Distractor patterns are arrays of note-value tokens.
            'rhythm-practice' => $this->shuffleWithCorrect(
                array_map(
                    fn ($p) => is_array($p) ? implode(',', $p) : (string) $p,
                    $this->normalizeList($q['other_options'] ?? []),
                ),
                $correct,
            ),

            // Constructed answer, no options.
            default => [],
        };
    }

    /**
     * Notation payload. Only types whose staff legitimately shows content
     * before answering get real notes.
     */
    private function notation(array $q, string $practiceType): array
    {
        $mode = self::NOTATION_MODES[$practiceType] ?? 'none';
        $clef = $q['clef'] ?? 'treble';

        $payload = [
            'mode' => $mode,
            'visible' => $mode !== 'none',
            'clef' => $clef,
            'key_signature' => $q['key_signature'] ?? null,
            'time_signature' => $q['time_signature'] ?? null,
            'bars' => isset($q['bars']) ? (int) $q['bars'] : null,
            'notes' => [],
            'range' => $this->music::CLEF_RANGES[$clef] ?? null,
        ];

        if ($mode === 'given_note') {
            $octave = (int) ($q['octave'] ?? 4);
            $note = $q['note1'] ?? null;
            if ($note !== null) {
                $payload['notes'] = [$this->noteDto($note, $octave)];
            }
        }

        return $payload;
    }

    /**
     * Non-answer-bearing context the client needs to render prompts and UI.
     */
    private function meta(array $q, string $practiceType): array
    {
        $meta = [
            'octave' => isset($q['octave']) ? (int) $q['octave'] : null,
            'root_note' => $q['root_note'] ?? null,
            'voicing' => $q['voicing'] ?? null,
            'inversion' => isset($q['inversion']) ? (int) $q['inversion'] : null,
            'answer_mode' => $q['answer_mode'] ?? null,
            'tonic' => $q['tonic'] ?? null,
            'mode' => $q['mode'] ?? null,
            'include_rhythm' => isset($q['include_rhythm']) ? (bool) $q['include_rhythm'] : null,
            'tempo_preset' => is_string($q['tempo'] ?? null) ? $q['tempo'] : null,
            // How many notes this question sounds, and therefore how many
            // answers it wants. Only single note groups; everything else is
            // one note and leaves this out.
            'group_size' => is_array($q['group'] ?? null) ? count($q['group']) : null,
        ];

        // Interval construction tells the user which interval to build, so the
        // interval name is the prompt rather than the answer.
        if ($practiceType === 'interval-construction-practice') {
            $meta['interval'] = $q['interval'] ?? null;
            $meta['direction'] = $q['direction'] ?? null;
            $meta['given_note'] = $q['note1'] ?? null;
        }

        return array_filter($meta, fn ($v) => $v !== null);
    }

    private function allowedNotes(array $q, string $practiceType): ?array
    {
        if (self::ANSWER_MODES[$practiceType] !== 'note_name') {
            return null;
        }

        if ($practiceType === 'single-note-practice') {
            // The answer keys are the option list itself.
            return $this->splitCsv($q['other_options'] ?? '') ?: null;
        }

        return null;
    }

    private function sequenceLength(array $q, string $practiceType): ?int
    {
        if ($practiceType !== 'melodic-dictation') {
            return null;
        }

        $notes = $q['notes'] ?? null;
        if (is_array($notes)) {
            return count($notes);
        }

        return null;
    }

    /**
     * Human label for an option value. Rhythm option values are token
     * sequences, which the client renders as notation rather than text.
     */
    private function label(string $value, string $practiceType): string
    {
        return match ($practiceType) {
            'interval-direction-practice' => ucfirst($value),
            'interval-comparison-practice' => strtoupper($value),
            'rhythm-practice' => '',
            default => $value,
        };
    }

    private function noteDto(string $note, int $octave): array
    {
        return [
            'name' => $note,
            'octave' => $octave,
            'midi' => $this->music->midiNumber($note, $octave),
        ];
    }

    private function shuffleWithCorrect(array $distractors, string $correct): array
    {
        $options = $distractors;
        if (! in_array($correct, $options, true)) {
            $options[] = $correct;
        }
        $options = array_values(array_unique($options));
        shuffle($options);

        return $options;
    }

    private function normalizeList(mixed $value): array
    {
        if (is_array($value)) {
            return array_values($value);
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return array_values($decoded);
            }

            return $this->splitCsv($value);
        }

        return [];
    }

    private function splitCsv(mixed $value): array
    {
        if (is_array($value)) {
            return array_values($value);
        }

        if (! is_string($value) || $value === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $value)), fn ($v) => $v !== ''));
    }

    /**
     * Remove the answer-bearing attributes from a stored question. Used by the
     * session resource when echoing raw question context.
     */
    public function strip(array $q, string $practiceType): array
    {
        foreach (self::STRIPPED_FIELDS[$practiceType] ?? [] as $field) {
            unset($q[$field]);
        }

        unset($q['_options'], $q['other_options'], $q['options']);

        return $q;
    }

    /** Chord/scale vocabularies, for the Studio config schema. */
    public function vocabularies(): array
    {
        return [
            'chord_types' => array_keys(ChordPractice::chordIntervals()),
            'scale_types' => array_keys(ScalePractice::scaleIntervals()),
            'clefs' => array_keys($this->music::CLEF_RANGES),
        ];
    }
}
