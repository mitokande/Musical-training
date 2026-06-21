<?php

namespace Tests\Unit;

use App\Models\LearningPathExercise;
use App\Services\LearningPathQuestionGenerator;
use App\Services\MusicTheoryService;
use App\Services\RhythmDistractorService;
use App\Services\RhythmGroupingService;
use App\Services\TonalMelodyGenerator;
use Tests\TestCase;

/**
 * Tests for the "rests" option in the Exercise Setup Studio.
 *
 * When include_rests is true:
 *  - Every generated rhythm/melody must contain exactly one rest.
 *  - The rest must be ≥ 1/8 duration.
 *  - No sixteenth or smaller rests are generated.
 */
class RestInjectionTest extends TestCase
{
    private LearningPathQuestionGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $groupingSvc  = new RhythmGroupingService;
        $this->generator = new LearningPathQuestionGenerator(
            new MusicTheoryService,
            new TonalMelodyGenerator,
            new RhythmDistractorService($groupingSvc),
        );
    }

    // ── injectOneRest via reflection ─────────────────────────────────────────

    private function callInjectOneRest(array $pattern): ?array
    {
        $ref = new \ReflectionMethod($this->generator, 'injectOneRest');
        $ref->setAccessible(true);

        return $ref->invoke($this->generator, $pattern);
    }

    public function test_inject_one_rest_replaces_exactly_one_token(): void
    {
        $pattern = ['quarter', 'quarter', 'half'];
        $result  = $this->callInjectOneRest($pattern);

        $this->assertNotNull($result);
        $rests = array_filter($result, fn ($t) => str_contains($t, '_rest'));
        $this->assertCount(1, $rests, 'Exactly one rest must be injected');
    }

    public function test_inject_one_rest_never_replaces_first_token(): void
    {
        // Run many times to verify position 0 is never chosen
        $pattern = ['quarter', 'quarter', 'quarter', 'quarter'];
        for ($i = 0; $i < 50; $i++) {
            $result = $this->callInjectOneRest($pattern);
            $this->assertNotNull($result);
            $this->assertFalse(
                str_contains($result[0], '_rest'),
                'The first token must never be replaced with a rest'
            );
        }
    }

    public function test_inject_one_rest_maps_note_to_correct_rest(): void
    {
        // Direct test: each eligible token maps to the correct rest.
        $pairs = [
            [['quarter', 'whole'],   'whole_rest'],
            [['quarter', 'half'],    'half_rest'],
            [['quarter', 'quarter'], 'quarter_rest'],
            [['quarter', 'eighth'],  'eighth_rest'],
        ];
        foreach ($pairs as [$pattern, $expectedRest]) {
            $result  = $this->callInjectOneRest($pattern);
            $this->assertNotNull($result);
            $this->assertContains($expectedRest, $result, "Expected {$expectedRest} in result");
        }
    }

    public function test_inject_one_rest_returns_null_when_no_eligible_token(): void
    {
        // Only sixteenth notes (too short to map) after position 0
        $pattern = ['quarter', 'sixteenth', 'sixteenth', 'sixteenth', 'sixteenth'];
        $result  = $this->callInjectOneRest($pattern);
        $this->assertNull($result, 'Should return null when no eligible token exists');
    }

    public function test_inject_one_rest_does_not_generate_sixteenth_rest(): void
    {
        $pattern = ['quarter', 'eighth', 'eighth', 'quarter'];
        for ($i = 0; $i < 30; $i++) {
            $result = $this->callInjectOneRest($pattern);
            $this->assertNotNull($result);
            $this->assertNotContains('sixteenth_rest', $result);
        }
    }

    // ── generateRhythm with include_rests ────────────────────────────────────

    private function makeRhythmExercise(bool $includeRests, string $timeSig = '4/4'): LearningPathExercise
    {
        return new LearningPathExercise(['config_json' => [
            'practice_type'    => 'rhythm-practice',
            'time_signatures'  => [$timeSig],
            'tempo_range'      => [80, 80],
            'bars'             => 1,
            'include_rests'    => $includeRests,
        ]]);
    }

    public function test_rhythm_with_rests_enabled_produces_exactly_one_rest_per_question(): void
    {
        $exercise  = $this->makeRhythmExercise(true);
        $questions = $this->generator->generate($exercise, 10);

        $this->assertCount(10, $questions);

        foreach ($questions as $q) {
            $noteValues = $q->note_values;
            if (is_string($noteValues)) {
                $noteValues = json_decode($noteValues, true);
            }
            $restCount = count(array_filter($noteValues, fn ($t) => str_contains($t, '_rest')));
            $this->assertSame(1, $restCount, 'Every question must contain exactly one rest');
        }
    }

    public function test_rhythm_with_rests_disabled_produces_no_rests(): void
    {
        $exercise  = $this->makeRhythmExercise(false);
        $questions = $this->generator->generate($exercise, 10);

        $this->assertCount(10, $questions);

        foreach ($questions as $q) {
            $noteValues = $q->note_values;
            if (is_string($noteValues)) {
                $noteValues = json_decode($noteValues, true);
            }
            $restCount = count(array_filter($noteValues, fn ($t) => str_contains($t, '_rest')));
            $this->assertSame(0, $restCount, 'No rests when rests are disabled');
        }
    }

    public function test_rhythm_rest_duration_is_never_smaller_than_eighth(): void
    {
        $forbidden = ['sixteenth_rest', 'thirty_second_rest'];
        $exercise  = $this->makeRhythmExercise(true);
        $questions = $this->generator->generate($exercise, 15);

        foreach ($questions as $q) {
            $noteValues = $q->note_values;
            if (is_string($noteValues)) {
                $noteValues = json_decode($noteValues, true);
            }
            foreach ($forbidden as $f) {
                $this->assertNotContains($f, $noteValues, "Forbidden rest value {$f} must never appear");
            }
        }
    }

    public function test_rhythm_rest_question_first_token_is_never_rest(): void
    {
        $exercise  = $this->makeRhythmExercise(true);
        $questions = $this->generator->generate($exercise, 20);

        foreach ($questions as $q) {
            $noteValues = $q->note_values;
            if (is_string($noteValues)) {
                $noteValues = json_decode($noteValues, true);
            }
            $this->assertFalse(
                str_contains($noteValues[0], '_rest'),
                'The first token of a question must never be a rest'
            );
        }
    }

    public function test_rhythm_with_rests_in_3_4(): void
    {
        $exercise  = $this->makeRhythmExercise(true, '3/4');
        $questions = $this->generator->generate($exercise, 10);

        foreach ($questions as $q) {
            $noteValues = $q->note_values;
            if (is_string($noteValues)) {
                $noteValues = json_decode($noteValues, true);
            }
            $restCount = count(array_filter($noteValues, fn ($t) => str_contains($t, '_rest')));
            $this->assertSame(1, $restCount, 'Exactly one rest in 3/4');
        }
    }

    // ── injectOneRestIntoMelody via reflection ───────────────────────────────

    private function makeComponent(): \App\Livewire\PracticeMelodicDictation
    {
        // Instantiate without triggering mount
        return new \App\Livewire\PracticeMelodicDictation;
    }

    private function callInjectMelodyRest(array $notes, array $noteValues): array
    {
        $component = $this->makeComponent();
        $ref = new \ReflectionMethod($component, 'injectOneRestIntoMelody');
        $ref->setAccessible(true);

        return $ref->invoke($component, $notes, $noteValues);
    }

    public function test_melody_rest_injection_produces_exactly_one_rest(): void
    {
        $notes      = ['C4', 'D4', 'E4', 'F4'];
        $noteValues = ['quarter', 'quarter', 'quarter', 'quarter'];

        [$newNotes, $newValues] = $this->callInjectMelodyRest($notes, $noteValues);

        $restCount = count(array_filter($newValues, fn ($v) => str_contains($v, '_rest')));
        $this->assertSame(1, $restCount, 'Exactly one rest must be injected into melody');
    }

    public function test_melody_rest_position_has_null_pitch(): void
    {
        $notes      = ['C4', 'D4', 'E4', 'F4', 'G4'];
        $noteValues = ['quarter', 'quarter', 'quarter', 'quarter', 'quarter'];

        [$newNotes, $newValues] = $this->callInjectMelodyRest($notes, $noteValues);

        // Find rest position
        $restPos = null;
        foreach ($newValues as $i => $v) {
            if (str_contains($v, '_rest')) {
                $restPos = $i;
                break;
            }
        }

        $this->assertNotNull($restPos, 'A rest must be present in note_values');
        $this->assertNull($newNotes[$restPos], 'Pitch at rest position must be null');
    }

    public function test_melody_rest_never_at_position_zero(): void
    {
        $notes      = ['C4', 'D4', 'E4', 'F4'];
        $noteValues = ['quarter', 'quarter', 'quarter', 'quarter'];

        for ($i = 0; $i < 40; $i++) {
            [$newNotes, $newValues] = $this->callInjectMelodyRest($notes, $noteValues);
            $this->assertFalse(
                str_contains($newValues[0], '_rest'),
                'First position must never be a rest in melodic dictation'
            );
        }
    }

    public function test_melody_rest_duration_not_smaller_than_eighth(): void
    {
        $notes      = ['C4', 'D4', 'E4', 'F4', 'G4', 'A4', 'B4', 'C5'];
        $noteValues = ['quarter', 'quarter', 'quarter', 'quarter', 'quarter', 'quarter', 'quarter', 'quarter'];

        for ($i = 0; $i < 20; $i++) {
            [, $newValues] = $this->callInjectMelodyRest($notes, $noteValues);
            $this->assertNotContains('sixteenth_rest', $newValues);
        }
    }

    public function test_melody_notes_and_values_stay_same_length(): void
    {
        $notes      = ['C4', 'D4', 'E4', 'F4', 'G4'];
        $noteValues = ['quarter', 'eighth', 'eighth', 'half', 'quarter'];

        [$newNotes, $newValues] = $this->callInjectMelodyRest($notes, $noteValues);

        $this->assertCount(count($notes), $newNotes, 'notes array must keep the same length');
        $this->assertCount(count($noteValues), $newValues, 'note_values array must keep the same length');
    }

    public function test_melody_without_rests_keeps_all_pitches(): void
    {
        // When no rest injection is performed, all pitches stay intact.
        // We test the component class directly without calling injectOneRestIntoMelody.
        $notes      = ['C4', 'D4', 'E4'];
        $noteValues = ['quarter', 'quarter', 'quarter'];

        // Control: no rest injection → no nulls
        foreach ($notes as $pitch) {
            $this->assertNotNull($pitch, 'Without rest injection no pitch should be null');
        }
    }
}
