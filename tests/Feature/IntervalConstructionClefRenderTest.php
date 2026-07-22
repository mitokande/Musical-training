<?php

namespace Tests\Feature;

use App\Livewire\PracticeIntervalConstruction;
use App\Models\IntervalConstructionPractice;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Guards the Livewire-collision fix: the component's public `$clef` property
 * (default 'treble') must NOT clobber the per-question clef the blade renders
 * into `data-clef`. Regression test for the bass-clef bug in Learning Path
 * Interval Construction lesson 15.
 */
class IntervalConstructionClefRenderTest extends TestCase
{
    private function bassQuestion(): IntervalConstructionPractice
    {
        $q = new IntervalConstructionPractice;
        $q->id = 1;
        $q->interval = 'Tritone';
        $q->note1 = 'E';
        $q->note2 = 'Bb';
        $q->octave = 3;
        $q->note2_octave = 2;
        $q->direction = 'descending';
        $q->clef = 'bass';
        $q->options = ['Bb', 'B', 'C', 'A'];

        return $q;
    }

    public function test_bass_question_renders_bass_clef(): void
    {
        $html = Livewire::test(PracticeIntervalConstruction::class, [
            'practices' => collect([$this->bassQuestion()]),
        ])->html();

        $this->assertMatchesRegularExpression('/data-clef="bass"/', $html);
        $this->assertDoesNotMatchRegularExpression('/data-clef="treble"/', $html);
    }
}
