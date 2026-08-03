<?php

namespace App\Services\Practice;

use App\Services\LearningPathQuestionGenerator;
use App\Services\MusicTheoryService;

/**
 * The single grading rule for every practice flow (web sessions, teacher
 * assignments and the mobile API). Previously this block was copy-pasted into
 * four branches of PracticeController.
 */
class PracticeAnswerGrader
{
    /**
     * Practice types whose answer is a note name. The piano answer keyboard
     * emits sharp spellings while lessons may teach flats, so enharmonic
     * equivalents are accepted.
     */
    private const NOTE_ANSWER_TYPES = [
        'single-note-practice',
        'interval-construction-practice',
    ];

    public function __construct(
        private readonly LearningPathQuestionGenerator $generator,
        private readonly MusicTheoryService $music,
    ) {}

    /**
     * The canonical correct answer for a serialized question.
     */
    public function correctAnswerFor(array $questionData, string $practiceType): string
    {
        return $this->generator->getAnswerFromSessionQuestion($questionData, $practiceType);
    }

    /**
     * Case- and whitespace-insensitive comparison, with enharmonic tolerance
     * for note-answer types.
     */
    public function isCorrect(string $answer, string $correct, string $practiceType): bool
    {
        $answer = trim($answer);
        $correct = trim($correct);

        $isCorrect = $this->normalize($answer) === $this->normalize($correct);

        if (! $isCorrect && in_array($practiceType, self::NOTE_ANSWER_TYPES, true)) {
            $isCorrect = $this->music->notesAreEnharmonic($answer, $correct);
        }

        return $isCorrect;
    }

    /**
     * Grade in one call: resolve the correct answer, then compare.
     *
     * @return array{correct: string, is_correct: bool}
     */
    public function grade(array $questionData, string $practiceType, string $answer): array
    {
        $correct = $this->correctAnswerFor($questionData, $practiceType);

        return [
            'correct' => $correct,
            'is_correct' => $this->isCorrect($answer, $correct, $practiceType),
        ];
    }

    private function normalize(string $value): string
    {
        return strtolower(preg_replace('/\s+/', '', $value));
    }
}
