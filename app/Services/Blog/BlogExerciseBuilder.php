<?php

namespace App\Services\Blog;

use App\Models\LearningPathExercise;
use App\Services\LearningPathQuestionGenerator;
use App\Services\MusicTheoryService;

/**
 * Question payloads for the interval exercise boxes embedded in blog posts.
 *
 * These reuse the site's own generator (same intervals, same distractor rules
 * as the AI interval exercises) so a reader hears exactly what the product
 * teaches. What is deliberately different is the delivery: the questions are
 * baked into the page and graded in the browser, with no call to
 * /api/practice/check-answer. A blog post is read by logged-out visitors, and
 * a demo box must not burn a guest's daily exercise quota, need a session, or
 * reload the article to score an answer.
 *
 * Pitches travel as MIDI numbers next to their display spelling: Tone.js is
 * given the number, so unusual spellings the generator can produce (Cb, B#,
 * double accidentals) always sound the right note.
 */
class BlogExerciseBuilder
{
    public function __construct(
        private LearningPathQuestionGenerator $generator,
        private MusicTheoryService $music,
    ) {}

    /**
     * Interval pools mirroring AIController's medium difficulty for the
     * melodic/harmonic interval types — the recognisable core set, no
     * enharmonic aliases that would put two same-sounding options side by side.
     */
    private const MEDIUM_INTERVALS = [
        'Minor 2nd', 'Major 2nd', 'Minor 3rd', 'Major 3rd',
        'Perfect 4th', 'Perfect 5th', 'Major 6th', 'Perfect Octave',
    ];

    private const EASY_INTERVALS = ['Major 2nd', 'Major 3rd', 'Perfect 5th', 'Perfect Octave'];

    /**
     * @param  'easy'|'medium'  $difficulty
     * @return array<int, array{n1: string, n2: string, m1: int, m2: int, answer: string, options: array<int, string>, direction: string}>
     */
    public function intervals(string $type, string $difficulty = 'medium', string $direction = 'ascending', int $count = 5): array
    {
        $exercise = new LearningPathExercise(['config_json' => [
            'practice_type' => $type,
            'allowed_intervals' => $difficulty === 'easy' ? self::EASY_INTERVALS : self::MEDIUM_INTERVALS,
            'allowed_notes' => ['C', 'D', 'E', 'F', 'G', 'A', 'B'],
            'clef' => 'treble',
            'direction' => $direction,
        ]]);

        return $this->generator->generate($exercise, $count)
            ->map(fn ($q) => $this->toPayload($q))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array{n1: string, n2: string, m1: int, m2: int, answer: string, options: array<int, string>, direction: string}|null
     */
    private function toPayload(object $question): ?array
    {
        $midi1 = $this->music->midiNumber($question->note1, (int) $question->octave);
        $midi2 = $this->music->midiNumber($question->note2, (int) $question->note2_octave);

        // A pitch the theory service cannot place has no sound to play; drop the
        // question rather than ship a silent play button.
        if ($midi1 === null || $midi2 === null) {
            return null;
        }

        $options = is_array($question->options) ? $question->options : [];
        if (! in_array($question->interval, $options, true)) {
            $options[] = $question->interval;
            shuffle($options);
        }

        return [
            'n1' => MusicTheoryService::toDisplaySymbol($question->note1).$question->octave,
            'n2' => MusicTheoryService::toDisplaySymbol($question->note2).$question->note2_octave,
            // VexFlow keys ("c/4", "f#/4") — raw ASCII spelling, never the ♯/♭
            // display symbols, which VexFlow's key parser does not understand.
            'k1' => strtolower($question->note1).'/'.$question->octave,
            'k2' => strtolower($question->note2).'/'.$question->note2_octave,
            'm1' => $midi1,
            'm2' => $midi2,
            'answer' => $question->interval,
            'options' => array_values($options),
            'direction' => $question->direction ?? 'ascending',
        ];
    }
}
