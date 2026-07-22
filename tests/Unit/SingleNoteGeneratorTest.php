<?php

namespace Tests\Unit;

use App\Models\LearningPathExercise;
use App\Services\DictationRhythmService;
use App\Services\LearningPathQuestionGenerator;
use App\Services\MusicTheoryService;
use App\Services\RhythmDistractorService;
use App\Services\RhythmGroupingService;
use App\Services\TonalMelodyGenerator;
use PHPUnit\Framework\TestCase;

/**
 * Rules for Learning Path / Exercise Setup single-note questions: clef-driven
 * octave placement keeps every target inside CLEF_RANGES (treble G3–G5, bass
 * C2–C4, alto C3–C5), the reference note is a natural with a different letter
 * than the target inside the same clef range, other_options always contains
 * the target itself, answer spellings follow the lesson config (Bb stays Bb —
 * the answer flow accepts enharmonic equivalents), and the per-lesson
 * answer_mode rides on each question. Mirrors the curriculum seeded by
 * LearningPathExerciseSeeder (single-note lessons 1–15).
 */
class SingleNoteGeneratorTest extends TestCase
{
    private LearningPathQuestionGenerator $generator;

    private MusicTheoryService $music;

    protected function setUp(): void
    {
        parent::setUp();
        $this->music = new MusicTheoryService;
        $this->generator = new LearningPathQuestionGenerator(
            $this->music,
            new TonalMelodyGenerator,
            new RhythmDistractorService(new RhythmGroupingService),
            new DictationRhythmService,
        );
    }

    private function generate(array $config, int $count = 30)
    {
        $exercise = new LearningPathExercise(['config_json' => array_merge([
            'practice_type' => 'single-note-practice',
            'target_type' => 'note',
            'distractor_count' => 3,
        ], $config)]);

        return $this->generator->generate($exercise, $count);
    }

    /** Seeded lesson configs, keyed by a readable label. */
    private function lessonConfigs(): array
    {
        $naturals = ['C', 'D', 'E', 'F', 'G', 'A', 'B'];
        $allTwelve = ['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'];

        return [
            'L1 anchors C G' => ['allowed_notes' => ['C', 'G'], 'clef' => 'treble', 'answer_mode' => 'note-names'],
            'L2 pentachord' => ['allowed_notes' => ['C', 'D', 'E', 'F', 'G'], 'clef' => 'treble', 'answer_mode' => 'note-names'],
            'L3 upper tetrachord' => ['allowed_notes' => ['G', 'A', 'B', 'C'], 'clef' => 'treble', 'answer_mode' => 'note-names'],
            'L4 c major' => ['allowed_notes' => $naturals, 'clef' => 'treble', 'answer_mode' => 'note-names'],
            'L5 g major' => ['allowed_notes' => ['G', 'A', 'B', 'C', 'D', 'E', 'F#'], 'clef' => 'treble', 'answer_mode' => 'note-names'],
            'L6 f major' => ['allowed_notes' => ['F', 'G', 'A', 'Bb', 'C', 'D', 'E'], 'clef' => 'treble', 'answer_mode' => 'note-names'],
            'L7 d major' => ['allowed_notes' => ['D', 'E', 'F#', 'G', 'A', 'B', 'C#'], 'clef' => 'treble', 'answer_mode' => 'note-names'],
            'L8 bb major' => ['allowed_notes' => ['Bb', 'C', 'D', 'Eb', 'F', 'G', 'A'], 'clef' => 'treble', 'answer_mode' => 'note-names'],
            'L9 chromatic lower' => ['allowed_notes' => ['C', 'C#', 'D', 'D#', 'E', 'F'], 'clef' => 'treble', 'answer_mode' => 'note-names'],
            'L10 chromatic upper' => ['allowed_notes' => ['F#', 'G', 'G#', 'A', 'A#', 'B'], 'clef' => 'treble', 'answer_mode' => 'note-names'],
            'L11 all 12 treble' => ['allowed_notes' => $allTwelve, 'clef' => 'treble', 'answer_mode' => 'keyboard'],
            'L12 bass naturals' => ['allowed_notes' => $naturals, 'clef' => 'bass', 'answer_mode' => 'keyboard'],
            'L13 bass all 12' => ['allowed_notes' => $allTwelve, 'clef' => 'bass', 'answer_mode' => 'keyboard'],
            'L14 alto naturals' => ['allowed_notes' => $naturals, 'clef' => 'alto', 'answer_mode' => 'keyboard'],
            'L15 alto all 12' => ['allowed_notes' => $allTwelve, 'clef' => 'alto', 'answer_mode' => 'keyboard'],
        ];
    }

    public function test_every_lesson_stays_inside_its_clef_range(): void
    {
        foreach ($this->lessonConfigs() as $label => $config) {
            $questions = $this->generate($config);
            $this->assertNotEmpty($questions, "$label produced no questions");
            [$min, $max] = $this->music->clefRangeMidi($config['clef']);

            foreach ($questions as $q) {
                $midi = $this->music->midiNumber($q->target, (int) $q->octave);
                $this->assertNotNull($midi, "$label: unparseable target {$q->target}{$q->octave}");
                $this->assertGreaterThanOrEqual($min, $midi,
                    "$label: {$q->target}{$q->octave} below {$config['clef']} range");
                $this->assertLessThanOrEqual($max, $midi,
                    "$label: {$q->target}{$q->octave} above {$config['clef']} range");
                $this->assertSame($config['clef'], $q->clef, "$label: question missing its clef");
            }
        }
    }

    public function test_targets_only_come_from_the_lesson_pool_with_spelling_preserved(): void
    {
        foreach ($this->lessonConfigs() as $label => $config) {
            foreach ($this->generate($config) as $q) {
                $this->assertContains($q->target, $config['allowed_notes'],
                    "$label: target {$q->target} not in lesson pool (spelling must be preserved)");
            }
        }
    }

    public function test_options_contain_the_target_and_respect_distractor_count(): void
    {
        foreach ($this->lessonConfigs() as $label => $config) {
            foreach ($this->generate($config) as $q) {
                $options = explode(',', $q->other_options);
                $this->assertContains($q->target, $options,
                    "$label: other_options must include the correct answer");
                $this->assertLessThanOrEqual(4, count($options),
                    "$label: more than distractor_count+1 options");
                $this->assertGreaterThanOrEqual(2, count($options),
                    "$label: a question needs at least one distractor");
                foreach ($options as $opt) {
                    $this->assertContains($opt, $config['allowed_notes'],
                        "$label: option $opt not in lesson pool");
                }
                $this->assertSame(count($options), count(array_unique($options)),
                    "$label: duplicate options");
            }
        }
    }

    public function test_reference_note_is_a_natural_with_a_different_letter_inside_the_clef(): void
    {
        foreach ($this->lessonConfigs() as $label => $config) {
            [$min, $max] = $this->music->clefRangeMidi($config['clef']);

            foreach ($this->generate($config) as $q) {
                $this->assertMatchesRegularExpression('/^[A-G]\d$/', $q->reference_note,
                    "$label: reference {$q->reference_note} is not a plain natural");
                $this->assertNotSame($q->target[0], $q->reference_note[0],
                    "$label: reference shares the target's letter");

                $midi = $this->music->midiNumber(substr($q->reference_note, 0, -1), (int) substr($q->reference_note, -1));
                $this->assertGreaterThanOrEqual($min, $midi,
                    "$label: reference {$q->reference_note} below {$config['clef']} range");
                $this->assertLessThanOrEqual($max, $midi,
                    "$label: reference {$q->reference_note} above {$config['clef']} range");
            }
        }
    }

    public function test_answer_mode_rides_on_every_question_and_survives_serialization(): void
    {
        foreach ($this->lessonConfigs() as $label => $config) {
            $questions = $this->generate($config, 10);
            $serialized = $this->generator->serializeForSession($questions);

            foreach ($serialized as $data) {
                $this->assertSame($config['answer_mode'], $data['answer_mode'] ?? null,
                    "$label: answer_mode missing after serialization");
            }
        }
    }

    public function test_flat_spelled_targets_are_enharmonically_matchable_from_the_sharp_keyboard(): void
    {
        // The piano answer keys emit sharp names; flat-spelled lesson targets
        // must be accepted through enharmonic equivalence.
        $this->assertTrue($this->music->notesAreEnharmonic('A#', 'Bb'));
        $this->assertTrue($this->music->notesAreEnharmonic('D#', 'Eb'));
        $this->assertFalse($this->music->notesAreEnharmonic('A', 'Bb'));
    }

    public function test_clef_mixes_multiple_octaves_for_the_same_note(): void
    {
        // Treble keeps C at C4 and C5; a clef-driven lesson must use both.
        $questions = $this->generate(['allowed_notes' => ['C', 'G'], 'clef' => 'treble', 'answer_mode' => 'note-names'], 60);
        $cOctaves = $questions->filter(fn ($q) => $q->target === 'C')->pluck('octave')->unique()->sort()->values()->all();

        $this->assertSame(['4', '5'], $cOctaves, 'clef-driven placement should cover every octave of C inside G3–G5');
    }
}
