<?php

namespace Tests\Unit;

use App\Models\ChordPractice;
use App\Models\ScalePractice;
use App\Services\MusicTheoryService;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Chords and scales are spelled, not just sounded.
 *
 * A chord is a stack of thirds, so it takes one letter per third and whatever
 * accidental that letter needs — including a double one. Spelling an augmented
 * triad B-D#-G instead of B-D#-F## puts a G where the fifth belongs, which is
 * how the staff came to show intervals that do not exist.
 *
 * These lock the rule in both directions: the written note, and the pitch it
 * actually sounds at, which is what the staff and the sampler each need.
 */
class ChordSpellingTest extends TestCase
{
    private MusicTheoryService $music;

    protected function setUp(): void
    {
        parent::setUp();
        $this->music = new MusicTheoryService;
    }

    private function chord(string $root, string $type, int $octave = 4): array
    {
        $chord = new ChordPractice;
        $chord->root_note = $root;
        $chord->chord_type = $type;
        $chord->octave = $octave;
        $chord->inversion = 0;

        return $chord->note_array;
    }

    public static function chords(): array
    {
        return [
            'a plain triad is untouched' => ['C', 'Major', ['C4', 'E4', 'G4']],
            'a flat triad keeps its flats' => ['Eb', 'Major', ['Eb4', 'G4', 'Bb4']],
            'an augmented fifth from B is a double sharp' => ['B', 'Augmented', ['B4', 'D#5', 'F##5']],
            'a diminished fifth from F is C flat' => ['F', 'Diminished', ['F4', 'Ab4', 'Cb5']],
            'a diminished seventh from C is a double flat' => ['C', 'Diminished 7th', ['C4', 'Eb4', 'Gb4', 'Bbb4']],
            'a diminished seventh from B needs no double' => ['B', 'Diminished 7th', ['B4', 'D5', 'F5', 'Ab5']],
            'a seventh chord still stacks by letter' => ['G', 'Dominant 7th', ['G4', 'B4', 'D5', 'F5']],
        ];
    }

    #[DataProvider('chords')]
    public function test_a_chord_is_spelled_as_it_is_built(string $root, string $type, array $expected): void
    {
        $this->assertSame($expected, $this->chord($root, $type));
    }

    public function test_a_written_spelling_still_sounds_the_right_pitch(): void
    {
        // Cb5 is written an octave above B4 and sounds as it; F##5 sounds as G5.
        $this->assertSame(71, $this->music->midiNumber('Cb', 5));
        $this->assertSame(72, $this->music->midiNumber('B#', 4));
        $this->assertSame(79, $this->music->midiNumber('F##', 5));
        $this->assertSame(69, $this->music->midiNumber('Bbb', 4));

        // Every note of a doubly-accidental chord is playable, and in order.
        $midis = array_map(function (string $pitch) {
            preg_match('/^([A-G](?:#{1,2}|b{1,2})?)(\d+)$/', $pitch, $m);

            return $this->music->midiNumber($m[1], (int) $m[2]);
        }, $this->chord('B', 'Augmented'));

        $this->assertSame([71, 75, 79], $midis);
    }

    public function test_a_scale_takes_one_letter_per_degree(): void
    {
        $scale = new ScalePractice;
        $scale->root_note = 'G#';
        $scale->scale_type = 'Harmonic Minor';
        $scale->octave = 3;
        $scale->direction = 'ascending';

        // The raised seventh of G# minor is F##, not G.
        $this->assertContains('F##4', $scale->note_array);

        $letters = array_map(fn ($n) => $n[0], $scale->note_array);
        $this->assertSame(['G', 'A', 'B', 'C', 'D', 'E', 'F', 'G'], $letters);
    }

    public function test_a_typed_answer_keeps_the_readable_spelling(): void
    {
        // The piano answer keyboard only offers the twelve everyday spellings,
        // so anything the learner has to type back stays simplifiable.
        $spelled = $this->music->spellNote('B', 4, 4, 8);
        $this->assertSame('G', $spelled['note']);

        $honest = $this->music->spellNote('B', 4, 4, 8, simplify: false);
        $this->assertSame('F##', $honest['note']);
    }
}
