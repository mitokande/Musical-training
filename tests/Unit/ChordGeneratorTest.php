<?php

namespace Tests\Unit;

use App\Models\ChordPractice;
use App\Models\LearningPathExercise;
use App\Services\DictationRhythmService;
use App\Services\LearningPathQuestionGenerator;
use App\Services\MusicTheoryService;
use App\Services\RhythmDistractorService;
use App\Services\RhythmGroupingService;
use App\Services\TonalMelodyGenerator;
use PHPUnit\Framework\TestCase;

/**
 * Rules for Learning Path / Exercise Setup chord questions: every chord type
 * (target and options) is a canonical ChordPractice::chordIntervals() key —
 * legacy lowercase slugs are normalized instead of silently playing as Major —
 * clef-driven octave placement keeps every voicing (including inversions)
 * inside CLEF_RANGES, focused lessons can pin exact inversion values via
 * inversion_values, and no answer set ever pairs two chords with identical
 * interval content (e.g. the legacy 'Half Diminished' alias). Mirrors the
 * curriculum seeded by LearningPathExerciseSeeder (chords lessons 1–15).
 */
class ChordGeneratorTest extends TestCase
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
            'practice_type' => 'chord-practice',
        ], $config)]);

        return $this->generator->generate($exercise, $count);
    }

    /** Seeded lesson configs, keyed by a readable label. */
    private function lessonConfigs(): array
    {
        $fourTriads = ['Major', 'Minor', 'Diminished', 'Augmented'];
        $fourSevenths = ['Dominant 7th', 'Major 7th', 'Minor 7th', 'Half-Diminished 7th'];
        $masterTypes = ['Major', 'Minor', 'Diminished', 'Augmented', 'Sus2', 'Sus4',
            'Dominant 7th', 'Major 7th', 'Minor 7th', 'Half-Diminished 7th',
            'Diminished 7th', 'Major 6th', 'Minor 6th'];

        return [
            'L1 major vs minor' => [
                'allowed_chord_types' => ['Major', 'Minor'],
                'allowed_root_notes' => ['C', 'D', 'E', 'F', 'G', 'A'],
                'voicing' => 'block', 'include_inversions' => false, 'clef' => 'treble',
                'distractor_pool' => ['Major', 'Minor', 'Augmented', 'Diminished'],
            ],
            'L2 diminished & augmented' => [
                'allowed_chord_types' => ['Diminished', 'Augmented'],
                'allowed_root_notes' => ['C', 'D', 'E', 'F', 'G', 'A', 'B'],
                'voicing' => 'block', 'include_inversions' => false, 'clef' => 'treble',
                'distractor_pool' => ['Major', 'Minor', 'Diminished', 'Augmented'],
            ],
            'L4 arpeggiated triads' => [
                'allowed_chord_types' => $fourTriads,
                'allowed_root_notes' => ['C', 'D', 'E', 'G', 'A'],
                'voicing' => 'arpeggiated', 'include_inversions' => false, 'clef' => 'treble',
                'distractor_pool' => $fourTriads,
            ],
            'L5 sus chords' => [
                'allowed_chord_types' => ['Sus2', 'Sus4'],
                'allowed_root_notes' => ['C', 'D', 'F', 'G', 'A'],
                'voicing' => 'block', 'include_inversions' => false, 'clef' => 'treble',
                'distractor_pool' => ['Sus2', 'Sus4', 'Major', 'Minor'],
            ],
            'L6 add9 color chords' => [
                'allowed_chord_types' => ['Add9', 'Minor Add9'],
                'allowed_root_notes' => ['C', 'D', 'F', 'G'],
                'voicing' => 'block', 'include_inversions' => false, 'clef' => 'treble',
                'distractor_pool' => ['Add9', 'Minor Add9', 'Major', 'Sus2'],
            ],
            'L7 dominant 7th & major 7th' => [
                'allowed_chord_types' => ['Dominant 7th', 'Major 7th'],
                'allowed_root_notes' => ['C', 'F', 'G', 'D', 'A'],
                'voicing' => 'block', 'include_inversions' => false, 'clef' => 'treble',
                'distractor_pool' => ['Dominant 7th', 'Major 7th', 'Minor 7th', 'Major'],
            ],
            'L8 minor 7th & half-diminished 7th' => [
                'allowed_chord_types' => ['Minor 7th', 'Half-Diminished 7th'],
                'allowed_root_notes' => ['C', 'D', 'F', 'G', 'A'],
                'voicing' => 'block', 'include_inversions' => false, 'clef' => 'treble',
                'distractor_pool' => ['Minor 7th', 'Half-Diminished 7th', 'Diminished 7th', 'Minor'],
            ],
            'L9 four sevenths' => [
                'allowed_chord_types' => $fourSevenths,
                'allowed_root_notes' => ['C', 'D', 'G', 'A'],
                'voicing' => 'block', 'include_inversions' => false, 'clef' => 'treble',
                'distractor_pool' => $fourSevenths,
            ],
            'L10 arpeggiated sevenths' => [
                'allowed_chord_types' => $fourSevenths,
                'allowed_root_notes' => ['C', 'D', 'F', 'G', 'A'],
                'voicing' => 'arpeggiated', 'include_inversions' => false, 'clef' => 'treble',
                'distractor_pool' => $fourSevenths,
            ],
            'L11 diminished family' => [
                'allowed_chord_types' => ['Diminished', 'Half-Diminished 7th', 'Diminished 7th'],
                'allowed_root_notes' => ['B', 'C', 'D', 'E'],
                'voicing' => 'block', 'include_inversions' => false, 'clef' => 'treble',
                'distractor_pool' => ['Diminished', 'Half-Diminished 7th', 'Diminished 7th', 'Minor 7th'],
            ],
            'L12 sixth chords' => [
                'allowed_chord_types' => ['Major 6th', 'Minor 6th'],
                'allowed_root_notes' => ['C', 'D', 'F', 'G'],
                'voicing' => 'block', 'include_inversions' => false, 'clef' => 'treble',
                'distractor_pool' => ['Major 6th', 'Minor 6th', 'Major 7th', 'Dominant 7th', 'Minor 7th'],
            ],
            'L13 first inversion' => [
                'allowed_chord_types' => ['Major', 'Minor'],
                'allowed_root_notes' => ['C', 'D', 'E', 'F', 'G'],
                'voicing' => 'block', 'include_inversions' => true, 'inversion_values' => [1], 'clef' => 'treble',
                'distractor_pool' => ['Major', 'Minor', 'Augmented', 'Diminished'],
            ],
            'L14 second inversion' => [
                'allowed_chord_types' => ['Major', 'Minor'],
                'allowed_root_notes' => ['C', 'D', 'E', 'F', 'G'],
                'voicing' => 'block', 'include_inversions' => true, 'inversion_values' => [2], 'clef' => 'treble',
                'distractor_pool' => ['Major', 'Minor', 'Augmented', 'Diminished'],
            ],
            'L15 bass master' => [
                'allowed_chord_types' => $masterTypes,
                'allowed_root_notes' => ['C', 'C#', 'D', 'Eb', 'E', 'F', 'F#', 'G', 'A', 'Bb'],
                'voicing' => 'block', 'include_inversions' => true, 'inversion_values' => [0, 1, 2], 'clef' => 'bass',
                'distractor_pool' => [],
            ],
        ];
    }

    public function test_every_lesson_config_generates_the_full_question_count(): void
    {
        foreach ($this->lessonConfigs() as $label => $config) {
            foreach ([5, 10, 15] as $count) {
                $questions = $this->generate($config, $count);
                $this->assertCount($count, $questions, "$label produced too few questions for count $count");
            }
        }
    }

    public function test_all_chord_types_and_options_are_canonical_interval_keys(): void
    {
        $canonical = ChordPractice::chordIntervals();
        foreach ($this->lessonConfigs() as $label => $config) {
            foreach ($this->generate($config) as $q) {
                foreach (array_merge([$q->chord_type], $q->other_options) as $type) {
                    $this->assertArrayHasKey(
                        $type,
                        $canonical,
                        "$label: '$type' is not a canonical chordIntervals() key — it would play as Major"
                    );
                }
            }
        }
    }

    public function test_legacy_lowercase_slugs_are_normalized_to_canonical_names(): void
    {
        // Pre-overhaul seed configs carried slugs; they must map to real
        // chordIntervals() keys instead of silently playing Major.
        $questions = $this->generate([
            'allowed_chord_types' => ['minor', 'dominant7', 'half-diminished7', 'augmented'],
            'allowed_root_notes' => ['C', 'G'],
            'voicing' => 'block', 'include_inversions' => false, 'clef' => 'treble',
            'distractor_pool' => ['major', 'minor7', 'diminished7', 'major7'],
        ], 20);

        $types = $questions->pluck('chord_type')->unique()->sort()->values()->all();
        $this->assertSame(
            ['Augmented', 'Dominant 7th', 'Half-Diminished 7th', 'Minor'],
            $types
        );
        foreach ($questions as $q) {
            foreach ($q->other_options as $opt) {
                $this->assertContains($opt, ['Major', 'Minor 7th', 'Diminished 7th', 'Major 7th']);
            }
        }
    }

    public function test_every_chord_tone_spells_and_stays_inside_the_clef_range(): void
    {
        foreach ($this->lessonConfigs() as $label => $config) {
            [$min, $max] = $this->music->clefRangeMidi($config['clef']);
            foreach ($this->generate($config) as $q) {
                $this->assertSame($config['clef'], $q->clef, "$label: question lost its clef");

                $expected = count(ChordPractice::chordIntervals()[$q->chord_type]);
                $notes = $q->note_array;
                $this->assertCount(
                    $expected,
                    $notes,
                    "$label: {$q->root_note} {$q->chord_type} dropped a chord tone while spelling"
                );

                foreach ($notes as $spelled) {
                    // Double accidentals are part of a correct spelling — Cdim7 reaches
                    // Bbb, B augmented reaches F## — so the pitch is parsed, not
                    // restricted, and midiNumber below is what proves it playable.
                    $this->assertSame(1, preg_match('/^([A-G](?:#{1,2}|b{1,2})?)(\d)$/', $spelled, $m), "$label: unparsable pitch '$spelled'");
                    $midi = $this->music->midiNumber($m[1], (int) $m[2]);
                    $this->assertNotNull($midi, "$label: unplayable pitch $spelled");
                    $this->assertGreaterThanOrEqual($min, $midi, "$label: $spelled below {$config['clef']} range ({$q->root_note} {$q->chord_type} inv {$q->inversion})");
                    $this->assertLessThanOrEqual($max, $midi, "$label: $spelled above {$config['clef']} range ({$q->root_note} {$q->chord_type} inv {$q->inversion})");
                }
            }
        }
    }

    public function test_every_question_has_three_distinct_wrong_options(): void
    {
        $intervals = ChordPractice::chordIntervals();
        foreach ($this->lessonConfigs() as $label => $config) {
            foreach ($this->generate($config) as $q) {
                $this->assertCount(3, $q->other_options, "$label: expected exactly 3 distractors");
                $this->assertCount(3, array_unique($q->other_options), "$label: duplicate distractor");

                $seenSounds = [$intervals[$q->chord_type]];
                foreach ($q->other_options as $opt) {
                    $this->assertNotSame($q->chord_type, $opt, "$label: correct answer offered as distractor");
                    $this->assertNotContains(
                        $intervals[$opt],
                        $seenSounds,
                        "$label: '$opt' sounds identical to another option for a {$q->chord_type} question"
                    );
                    $seenSounds[] = $intervals[$opt];
                }
            }
        }
    }

    public function test_inversion_values_pins_focused_lessons_to_that_inversion(): void
    {
        $inversions = $this->generate($this->lessonConfigs()['L13 first inversion'], 25)
            ->pluck('inversion')->unique()->all();
        $this->assertSame([1], $inversions, 'first-inversion lesson generated other inversions');

        $secondInversions = $this->generate($this->lessonConfigs()['L14 second inversion'], 25)
            ->pluck('inversion')->unique()->all();
        $this->assertSame([2], $secondInversions, 'second-inversion lesson generated other inversions');

        $masterInversions = $this->generate($this->lessonConfigs()['L15 bass master'], 60)
            ->pluck('inversion')->unique()->sort()->values()->all();
        $this->assertSame([0, 1, 2], $masterInversions, 'master lesson should mix all inversions');
    }

    public function test_questions_survive_the_session_round_trip(): void
    {
        $questions = $this->generate($this->lessonConfigs()['L9 four sevenths'], 10)
            ->values()->map(function ($q, $i) {
                $q->id = $i + 1;

                return $q;
            });

        $serialized = $this->generator->serializeForSession($questions);
        $rebuilt = $this->generator->reconstructFromSession($serialized, 'chord-practice');

        $this->assertCount(10, $rebuilt);
        foreach ($rebuilt as $i => $q) {
            $original = $questions[$i];
            $this->assertSame($original->chord_type, $q->chord_type);
            $this->assertSame($original->root_note, $q->root_note);
            $this->assertSame($original->clef, $q->clef);
            $this->assertEquals($original->other_options, $q->other_options);
            $this->assertSame(
                $original->chord_type,
                $this->generator->getAnswerFromSessionQuestion($serialized[$i], 'chord-practice')
            );
        }
    }
}
