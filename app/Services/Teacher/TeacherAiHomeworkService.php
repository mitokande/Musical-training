<?php

namespace App\Services\Teacher;

use App\Models\SystemSetting;
use App\Services\AiUsageLogger;
use App\Services\MusicTheoryService;
use InvalidArgumentException;
use OpenAI;

/**
 * Basic AI Homework Builder (Phase 1): maps a teacher's free-text prompt to
 * structured, validated exercise parameters. The AI never generates question
 * content — it only selects parameters; questions always come from the
 * canonical LearningPathQuestionGenerator via TeacherAssignmentConfigFactory.
 */
class TeacherAiHomeworkService
{
    public function __construct(private TeacherAssignmentConfigFactory $configFactory) {}

    /**
     * @return array{practice_type: string, difficulty: string, question_count: int, overrides: array, title: string, explanation: string}
     *
     * @throws InvalidArgumentException when the request is unsupported
     */
    public function interpretPrompt(string $prompt): array
    {
        $apiKey = config('services.openai.key');

        if (! $apiKey) {
            throw new InvalidArgumentException(__('teacher.assignments.ai_unavailable'));
        }

        $client = OpenAI::client($apiKey);
        $model = SystemSetting::get('ai_model', 'gpt-4.1-mini');
        $start = microtime(true);

        try {
            $response = $client->chat()->create([
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $this->systemPrompt()],
                    ['role' => 'user', 'content' => mb_substr($prompt, 0, 2000)],
                ],
                'response_format' => [
                    'type' => 'json_schema',
                    'json_schema' => $this->schema(),
                ],
                'temperature' => 0.2,
            ]);
        } catch (\Throwable $e) {
            AiUsageLogger::logError('teacher_homework_interpret', $model, $e->getMessage(), auth()->id(), [], (int) ((microtime(true) - $start) * 1000));
            throw $e;
        }

        AiUsageLogger::logSuccess('teacher_homework_interpret', $model, $response->usage, auth()->id(), [], (int) ((microtime(true) - $start) * 1000));

        $data = json_decode($response->choices[0]->message->content ?? '', true);

        if (! is_array($data)) {
            throw new InvalidArgumentException(__('teacher.assignments.ai_failed'));
        }

        if (! empty($data['unsupported_reason'])) {
            throw new InvalidArgumentException(
                __('teacher.assignments.ai_unsupported').' '.$data['unsupported_reason']
            );
        }

        $overrides = array_filter([
            'allowed_intervals' => $data['allowed_intervals'] ?? [],
            'allowed_notes' => $data['allowed_notes'] ?? [],
            'allowed_chord_types' => $data['allowed_chord_types'] ?? [],
            'allowed_scale_types' => $data['allowed_scale_types'] ?? [],
            'allowed_note_values' => $data['allowed_note_values'] ?? [],
            'time_signatures' => $data['time_signatures'] ?? [],
            'clef' => $data['clef'] ?? '',
        ]);

        // Validate by actually building the config — throws with a clear
        // message when the AI proposed anything outside the whitelists.
        $this->configFactory->build($data['practice_type'], $data['difficulty'], $overrides);

        return [
            'practice_type' => $data['practice_type'],
            'difficulty' => $data['difficulty'],
            'question_count' => max(3, min(30, (int) $data['question_count'])),
            'overrides' => $overrides,
            'title' => $data['title'] ?: 'Homework',
            'explanation' => $data['explanation'] ?? '',
        ];
    }

    private function systemPrompt(): string
    {
        $types = implode(', ', TeacherAssignmentConfigFactory::PRACTICE_TYPES);
        $intervals = implode(', ', array_keys(MusicTheoryService::INTERVAL_SEMITONES));
        $chords = implode(', ', TeacherAssignmentConfigFactory::CHORD_TYPES);
        $scales = implode(', ', TeacherAssignmentConfigFactory::SCALE_TYPES);
        $noteValues = implode(', ', TeacherAssignmentConfigFactory::NOTE_VALUES);

        return <<<PROMPT
You translate a music teacher's homework description into structured parameters for the Harmoniva exercise generator. You must only use supported values.

Supported practice types: {$types}
Supported difficulties: beginner, intermediate, advanced
Supported interval names: {$intervals}
Supported chord types: {$chords}
Supported scale types: {$scales}
Supported rhythm note values: {$noteValues}
Supported time signatures: 4/4, 3/4, 2/4, 6/8
Supported clefs: treble, bass

Rules:
- Pick exactly one practice type that best matches the request.
- Only fill the constraint fields relevant to that practice type; leave others as empty arrays or empty string.
- allowed_intervals applies to melodic-interval-practice, harmonic-interval-practice, interval-construction-practice only.
- allowed_notes applies to single-note-practice, melodic-interval-practice, harmonic-interval-practice, interval-direction-practice only (note names like C, D, F#, Bb).
- allowed_chord_types applies to chord-practice only. allowed_scale_types applies to scale-practice only.
- allowed_note_values and time_signatures apply to rhythm-practice only.
- question_count between 3 and 30 (default 10 when unspecified).
- If the request cannot be expressed with the supported options (e.g. an unsupported exercise kind, instrument-specific repertoire, compound intervals beyond an octave, unsupported meters), set unsupported_reason to a short clear explanation and suggest the nearest supported alternative in it. Never invent unsupported values.
- title: a short homework title in the same language as the teacher's prompt.
- explanation: one sentence describing what you configured, in the same language as the teacher's prompt.
PROMPT;
    }

    private function schema(): array
    {
        $str = fn (string $desc) => ['type' => 'string', 'description' => $desc];
        $strArr = fn (string $desc) => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => $desc];

        return [
            'name' => 'TeacherHomeworkConfig',
            'strict' => true,
            'schema' => [
                'type' => 'object',
                'properties' => [
                    'practice_type' => $str('One of the supported practice type slugs'),
                    'difficulty' => $str('beginner | intermediate | advanced'),
                    'question_count' => ['type' => 'integer'],
                    'allowed_intervals' => $strArr('Interval full names, or empty'),
                    'allowed_notes' => $strArr('Note names, or empty'),
                    'allowed_chord_types' => $strArr('Chord types, or empty'),
                    'allowed_scale_types' => $strArr('Scale types, or empty'),
                    'allowed_note_values' => $strArr('Rhythm note values, or empty'),
                    'time_signatures' => $strArr('Time signatures, or empty'),
                    'clef' => $str('treble | bass | empty string'),
                    'title' => $str('Short homework title'),
                    'explanation' => $str('One-sentence summary of the configuration'),
                    'unsupported_reason' => $str('Empty string when supported; otherwise why the request is unsupported plus the nearest supported alternative'),
                ],
                'required' => ['practice_type', 'difficulty', 'question_count', 'allowed_intervals', 'allowed_notes', 'allowed_chord_types', 'allowed_scale_types', 'allowed_note_values', 'time_signatures', 'clef', 'title', 'explanation', 'unsupported_reason'],
                'additionalProperties' => false,
            ],
        ];
    }
}
