<?php

namespace Tests\Unit;

use App\Models\LearningPathExercise;
use App\Services\LearningPathQuestionGenerator;
use App\Services\Practice\StudioConfigMapper;
use Tests\TestCase;

/**
 * The published rhythm schema is a promise: everything it offers has to make a
 * question, and nothing it offers may quietly practise something else.
 */
class RhythmStudioSchemaTest extends TestCase
{
    private StudioConfigMapper $mapper;

    private LearningPathQuestionGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mapper = app(StudioConfigMapper::class);
        $this->generator = app(LearningPathQuestionGenerator::class);
    }

    private function schema(): array
    {
        return $this->mapper->configSchema('rhythm-practice');
    }

    private function generate(array $config, int $count = 5)
    {
        return $this->generator->generate(
            new LearningPathExercise(['config_json' => $this->mapper->map('rhythm-practice', $config)]),
            $count,
        );
    }

    public function test_every_published_meter_generates_a_full_bar(): void
    {
        foreach ($this->schema()['time_signature']['values'] as $signature) {
            $questions = $this->generate(['time_signature' => $signature]);

            $this->assertGreaterThan(0, $questions->count(), "{$signature} generated nothing");

            foreach ($questions as $question) {
                $this->assertSame($signature, $question->time_signature);
            }
        }
    }

    public function test_every_published_note_value_survives_the_mapper(): void
    {
        $values = $this->schema()['note_values']['values'];

        $this->assertContains('dotted-quarter', $values);
        $this->assertContains('triplet-eighth', $values);
        // Rests are not cell tokens; include_rests is how a rest is asked for.
        $this->assertSame([], array_filter($values, fn ($v) => str_contains($v, '_rest')));

        $mapped = $this->mapper->map('rhythm-practice', ['note_values' => $values])['allowed_note_values'];

        // Publishing a value the mapper strips would be advertising a choice
        // that silently does nothing.
        foreach ($values as $value) {
            $this->assertContains($value, $mapped, "{$value} is published but stripped");
        }
    }

    public function test_a_selection_that_cannot_fill_the_bar_is_widened_rather_than_failing(): void
    {
        // A whole note cannot be placed in 3/4. Before, this returned no
        // questions at all and the session died with a 422.
        $mapped = $this->mapper->map('rhythm-practice', [
            'time_signature' => '3/4',
            'note_values' => ['whole'],
        ]);

        $this->assertSame(['whole', 'quarter'], $mapped['allowed_note_values']);
        $this->assertGreaterThan(0, $this->generate([
            'time_signature' => '3/4',
            'note_values' => ['whole'],
        ])->count());
    }

    public function test_a_compound_selection_keeps_its_eighth_so_the_pool_is_not_replaced(): void
    {
        // With no eighth, the generator's own filter empties and it falls back
        // to the whole unfiltered compound pool — practising tokens the learner
        // never picked. Adding the beat unit keeps the choice honest.
        $mapped = $this->mapper->map('rhythm-practice', [
            'time_signature' => '6/8',
            'note_values' => ['quarter'],
        ]);

        $this->assertSame(['quarter', 'eighth'], $mapped['allowed_note_values']);
    }

    public function test_bars_and_tempo_are_held_to_what_the_schema_publishes(): void
    {
        $schema = $this->schema();

        $low = $this->mapper->map('rhythm-practice', ['bars' => 0, 'tempo' => 5]);
        $high = $this->mapper->map('rhythm-practice', ['bars' => 12, 'tempo' => 9999]);

        $this->assertSame($schema['bars']['min'], $low['bars']);
        $this->assertSame($schema['bars']['max'], $high['bars']);
        $this->assertSame([$schema['tempo']['min'], $schema['tempo']['min']], $low['tempo_range']);
        $this->assertSame([$schema['tempo']['max'], $schema['tempo']['max']], $high['tempo_range']);

        // bars = 0 used to reach the generator and produce nothing at all.
        $this->assertGreaterThan(0, $this->generate(['bars' => 0])->count());
    }

    public function test_the_metronome_is_published_but_never_reaches_the_generator(): void
    {
        $this->assertSame(['type' => 'bool', 'default' => true], $this->schema()['metronome']);
        $this->assertArrayNotHasKey('metronome', $this->mapper->map('rhythm-practice', ['metronome' => false]));
    }
}
