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
 * Rules for Learning Path melodic dictation questions: every seeded LP
 * dictation lesson must fill its full premium question count (15) with
 * distinct melodies; every rhythm fills its meter exactly (bars × measure
 * sixteenths) with note counts always in sync; rhythm tokens never leave the
 * lesson vocabulary; melodies stay inside the lesson's note pool (or the
 * treble clef range for pool-less lessons) and diatonic to the key except for
 * the accidentals the lesson explicitly teaches (harmonic minor leading tone,
 * melodic minor 6–7, advanced chromatic approach tones); beginner lessons
 * move by step or repeated note only and end on the tonic; minor lessons
 * notate in the relative major's key signature with the minor tonic and mode
 * stored per question. Mirrors the curriculum seeded by
 * LearningPathExerciseSeeder (melodic-dictation lessons 1–16).
 */
class MelodicDictationGeneratorTest extends TestCase
{
    private LearningPathQuestionGenerator $generator;

    // Note-value durations in 16th-note units (matches the practice blades)
    private const BEAT16 = [
        'whole' => 16, 'half' => 8, 'dotted-half' => 12,
        'quarter' => 4, 'dotted-quarter' => 6,
        'eighth' => 2, 'dotted-eighth' => 3, 'sixteenth' => 1,
    ];

    private const DIATONIC = [
        'C' => ['C', 'D', 'E', 'F', 'G', 'A', 'B'],
        'G' => ['G', 'A', 'B', 'C', 'D', 'E', 'F#'],
        'F' => ['F', 'G', 'A', 'Bb', 'C', 'D', 'E'],
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $grouping = new RhythmGroupingService;
        $this->generator = new LearningPathQuestionGenerator(
            new MusicTheoryService,
            new TonalMelodyGenerator,
            new RhythmDistractorService($grouping),
            new DictationRhythmService,
        );
    }

    private function generate(array $config, int $count = 15)
    {
        $exercise = new LearningPathExercise(['config_json' => array_merge([
            'practice_type' => 'melodic-dictation',
            'clef' => 'treble',
            'include_rhythm' => true,
            'time_signature' => '4/4',
            'bars' => 2,
        ], $config)]);

        return $this->generator->generate($exercise, $count);
    }

    /** Seeded LP melodic dictation lesson configs, keyed by a readable label. */
    private function lessonConfigs(): array
    {
        $octaveC = ['C4', 'D4', 'E4', 'F4', 'G4', 'A4', 'B4', 'C5'];
        $minorA = ['A3', 'B3', 'C4', 'D4', 'E4', 'F4', 'G4', 'A4'];

        return [
            'L1 steps do-re-mi' => ['note_pool' => ['C4', 'D4', 'E4'], 'difficulty' => 'beginner', 'mode' => 'major', 'accidentals' => 'none', 'key_signatures' => ['C'], 'allowed_note_values' => ['quarter'], 'tempo_range' => [58, 64]],
            'L2 steps pentachord' => ['note_pool' => ['C4', 'D4', 'E4', 'F4', 'G4'], 'difficulty' => 'beginner', 'mode' => 'major', 'accidentals' => 'none', 'key_signatures' => ['C'], 'allowed_note_values' => ['quarter'], 'tempo_range' => [56, 62]],
            'L3 tonic triad' => ['note_pool' => ['C4', 'E4', 'G4', 'C5'], 'difficulty' => 'intermediate', 'mode' => 'major', 'accidentals' => 'none', 'key_signatures' => ['C'], 'allowed_note_values' => ['quarter'], 'tempo_range' => [56, 62]],
            'L4 scale lines' => ['note_pool' => $octaveC, 'difficulty' => 'beginner', 'mode' => 'major', 'accidentals' => 'none', 'key_signatures' => ['C'], 'allowed_note_values' => ['quarter'], 'tempo_range' => [55, 61]],
            'L5 steps meet skips' => ['note_pool' => $octaveC, 'difficulty' => 'intermediate', 'mode' => 'major', 'accidentals' => 'none', 'key_signatures' => ['C'], 'allowed_note_values' => ['quarter'], 'tempo_range' => [55, 61]],
            'L6 quarters and halves' => ['note_pool' => $octaveC, 'difficulty' => 'intermediate', 'mode' => 'major', 'accidentals' => 'none', 'key_signatures' => ['C'], 'allowed_note_values' => ['quarter', 'half'], 'tempo_range' => [54, 60]],
            'L7 eighth pairs' => ['note_pool' => $octaveC, 'difficulty' => 'intermediate', 'mode' => 'major', 'accidentals' => 'none', 'key_signatures' => ['C'], 'allowed_note_values' => ['quarter', 'eighth'], 'tempo_range' => [52, 58]],
            'L8 sol-do pull' => ['note_pool' => ['G3', 'A3', 'B3', 'C4', 'D4', 'E4', 'F4', 'G4', 'A4', 'B4', 'C5'], 'difficulty' => 'intermediate', 'mode' => 'major', 'accidentals' => 'none', 'key_signatures' => ['C'], 'allowed_note_values' => ['quarter', 'half'], 'tempo_range' => [52, 58]],
            'L9 natural minor' => ['note_pool' => $minorA, 'difficulty' => 'intermediate', 'mode' => 'minor', 'accidentals' => 'none', 'key_signatures' => ['C'], 'allowed_note_values' => ['quarter', 'half'], 'tempo_range' => [52, 58]],
            'L10 G major' => ['note_pool' => ['G3', 'A3', 'B3', 'C4', 'D4', 'E4', 'F#4', 'G4', 'A4', 'B4', 'C5', 'D5'], 'difficulty' => 'intermediate', 'mode' => 'major', 'accidentals' => 'none', 'key_signatures' => ['G'], 'allowed_note_values' => ['quarter', 'eighth', 'half'], 'tempo_range' => [52, 58]],
            'L11 F major' => ['note_pool' => ['C4', 'D4', 'E4', 'F4', 'G4', 'A4', 'Bb4', 'C5', 'D5', 'E5', 'F5'], 'difficulty' => 'intermediate', 'mode' => 'major', 'accidentals' => 'none', 'key_signatures' => ['F'], 'allowed_note_values' => ['quarter', 'eighth', 'half'], 'tempo_range' => [52, 58]],
            'L12 triple metre' => ['note_pool' => $octaveC, 'difficulty' => 'intermediate', 'mode' => 'major', 'accidentals' => 'none', 'key_signatures' => ['C'], 'time_signature' => '3/4', 'allowed_note_values' => ['quarter', 'half', 'dotted-half', 'eighth'], 'tempo_range' => [50, 56]],
            'L13 harmonic minor' => ['note_pool' => $minorA, 'difficulty' => 'intermediate', 'mode' => 'minor', 'accidentals' => 'harmonic', 'key_signatures' => ['C'], 'allowed_note_values' => ['quarter', 'eighth'], 'tempo_range' => [50, 56]],
            'L14 melodic minor' => ['note_pool' => $minorA, 'difficulty' => 'intermediate', 'mode' => 'minor', 'accidentals' => 'melodic', 'key_signatures' => ['C'], 'allowed_note_values' => ['quarter', 'eighth'], 'tempo_range' => [50, 56]],
            'L15 chromatic approach' => ['difficulty' => 'advanced', 'mode' => 'major', 'key_signatures' => ['C'], 'allowed_note_values' => ['quarter', 'eighth', 'half'], 'tempo_range' => [50, 56]],
            'L16 master four-bar' => ['difficulty' => 'advanced', 'mode' => 'major', 'key_signatures' => ['C', 'G', 'F'], 'bars' => 4, 'allowed_note_values' => ['quarter', 'eighth', 'half', 'dotted-quarter', 'dotted-half'], 'tempo_range' => [48, 54]],
        ];
    }

    private function noteName(string $noteWithOctave): string
    {
        return substr($noteWithOctave, 0, -1);
    }

    private function midi(string $noteWithOctave): int
    {
        return (new TonalMelodyGenerator)->noteToMidi($noteWithOctave);
    }

    private function measure16(string $sig): int
    {
        [$num, $den] = array_map('intval', explode('/', $sig));

        return (int) ($num * (16 / $den));
    }

    // ── Full-curriculum invariants ───────────────────────────────────────────

    public function test_every_lesson_fills_the_premium_question_count_with_distinct_melodies(): void
    {
        foreach ($this->lessonConfigs() as $label => $config) {
            $questions = $this->generate($config);

            $this->assertCount(15, $questions, "$label: expected 15 questions");

            $keys = $questions->map(fn ($q) => $q->key_signature.'|'.implode(',', $q->notes).'|'.implode(',', $q->note_values));
            $this->assertCount(15, $keys->unique(), "$label: duplicate questions in set");
        }
    }

    public function test_every_rhythm_fills_its_meter_exactly_and_matches_the_note_count(): void
    {
        foreach ($this->lessonConfigs() as $label => $config) {
            $timeSig = $config['time_signature'] ?? '4/4';
            $bars = $config['bars'] ?? 2;

            foreach ($this->generate($config) as $q) {
                $this->assertSame($timeSig, $q->time_signature, "$label: time signature mismatch");
                $this->assertSame($bars, $q->bars, "$label: bars attribute mismatch");
                $this->assertCount(count($q->note_values), $q->notes, "$label: notes/note_values out of sync");

                $total = array_sum(array_map(fn ($v) => self::BEAT16[$v] ?? -1000, $q->note_values));
                $this->assertSame(
                    $this->measure16($timeSig) * $bars,
                    $total,
                    "$label: rhythm [".implode(',', $q->note_values)."] does not fill $bars bar(s) of $timeSig"
                );

                foreach ($q->note_values as $v) {
                    $this->assertContains($v, $config['allowed_note_values'], "$label: note value $v outside lesson vocabulary");
                }
            }
        }
    }

    public function test_melodies_stay_inside_the_lesson_note_pool(): void
    {
        foreach ($this->lessonConfigs() as $label => $config) {
            if (empty($config['note_pool'])) {
                continue;
            }
            // Accidental lessons raise pool notes (G4→G#4 etc.) — compare by MIDI reach instead.
            $allowRaised = in_array($config['accidentals'] ?? '', ['harmonic', 'melodic'], true);

            foreach ($this->generate($config) as $q) {
                foreach ($q->notes as $note) {
                    if ($allowRaised) {
                        continue;
                    }
                    $this->assertContains($note, $config['note_pool'], "$label: note $note outside lesson pool");
                }
            }
        }
    }

    public function test_poolless_lessons_stay_inside_the_treble_clef_range(): void
    {
        foreach (['L15 chromatic approach', 'L16 master four-bar'] as $label) {
            $config = $this->lessonConfigs()[$label];
            [$min, $max] = [$this->midi('G3'), $this->midi('G5')];

            foreach ($this->generate($config) as $q) {
                foreach ($q->notes as $note) {
                    $m = $this->midi($note);
                    $this->assertGreaterThanOrEqual($min, $m, "$label: $note below treble range");
                    $this->assertLessThanOrEqual($max, $m, "$label: $note above treble range");
                }
            }
        }
    }

    public function test_beginner_lessons_move_by_step_or_repeat_and_end_on_the_tonic(): void
    {
        foreach (['L1 steps do-re-mi', 'L2 steps pentachord', 'L4 scale lines'] as $label) {
            $config = $this->lessonConfigs()[$label];

            foreach ($this->generate($config) as $q) {
                $notes = $q->notes;
                for ($i = 1; $i < count($notes); $i++) {
                    $dist = abs($this->midi($notes[$i]) - $this->midi($notes[$i - 1]));
                    $this->assertLessThanOrEqual(2, $dist,
                        "$label: move {$notes[$i - 1]}→{$notes[$i]} is not a step or repeat");
                }
                $this->assertSame('C', $this->noteName(end($notes)), "$label: beginner melody must end on the tonic");
            }
        }
    }

    public function test_diatonic_lessons_never_leave_the_key(): void
    {
        $strictlyDiatonic = [
            'L1 steps do-re-mi', 'L2 steps pentachord', 'L3 tonic triad', 'L4 scale lines',
            'L5 steps meet skips', 'L6 quarters and halves', 'L7 eighth pairs', 'L8 sol-do pull',
            'L9 natural minor', 'L10 G major', 'L11 F major', 'L12 triple metre',
        ];

        foreach ($strictlyDiatonic as $label) {
            $config = $this->lessonConfigs()[$label];

            foreach ($this->generate($config) as $q) {
                $allowed = self::DIATONIC[$q->key_signature];
                foreach ($q->notes as $note) {
                    $this->assertContains($this->noteName($note), $allowed,
                        "$label: chromatic note $note in a strictly diatonic lesson");
                }
            }
        }
    }

    public function test_harmonic_minor_lesson_raises_only_the_leading_tone_into_the_tonic(): void
    {
        $config = $this->lessonConfigs()['L13 harmonic minor'];

        foreach ($this->generate($config) as $q) {
            $notes = $q->notes;
            $sawLeadingTone = false;
            foreach ($notes as $i => $note) {
                $name = $this->noteName($note);
                if (! in_array($name, self::DIATONIC['C'], true)) {
                    $this->assertSame('G#', $name, "L13: unexpected accidental $note");
                    $sawLeadingTone = true;
                    $this->assertArrayHasKey($i + 1, $notes, "L13: leading tone $note must not end the melody");
                    $this->assertSame('A', $this->noteName($notes[$i + 1]), "L13: $note must resolve to A");
                    $this->assertGreaterThan($this->midi($note), $this->midi($notes[$i + 1]),
                        "L13: leading tone $note must resolve upward");
                }
            }

            // Every question must sound the leading tone at least once — that
            // signature ascent into the tonic is the whole point of the lesson.
            $this->assertTrue($sawLeadingTone,
                'L13: a question sounded no leading tone: '.implode(',', $notes));
        }
    }

    public function test_melodic_minor_lesson_uses_only_raised_sixth_and_seventh(): void
    {
        $config = $this->lessonConfigs()['L14 melodic minor'];

        foreach ($this->generate($config) as $q) {
            $sawRaisedSixth = false;
            foreach ($q->notes as $note) {
                $name = $this->noteName($note);
                if (! in_array($name, self::DIATONIC['C'], true)) {
                    $this->assertContains($name, ['F#', 'G#'], "L14: unexpected accidental $note");
                    if ($name === 'F#') {
                        $sawRaisedSixth = true;
                    }
                }
            }

            // Every question must sound the melodic-minor climb (raised 6th, and
            // therefore the 7th too), not merely a harmonic-minor leading tone.
            $this->assertTrue($sawRaisedSixth,
                'L14: a question sounded no raised 6th: '.implode(',', $q->notes));
        }
    }

    public function test_natural_minor_lesson_never_contains_accidentals(): void
    {
        $config = $this->lessonConfigs()['L9 natural minor'];

        foreach ($this->generate($config, 15) as $q) {
            foreach ($q->notes as $note) {
                $this->assertContains($this->noteName($note), self::DIATONIC['C'],
                    "L9: natural minor must stay accidental-free, got $note");
            }
        }
    }

    public function test_chromatic_lesson_adds_at_most_two_approach_tones(): void
    {
        $config = $this->lessonConfigs()['L15 chromatic approach'];

        foreach ($this->generate($config) as $q) {
            $extras = array_filter($q->notes, fn ($n) => ! in_array($this->noteName($n), self::DIATONIC['C'], true));
            $this->assertLessThanOrEqual(2, count(array_unique(array_map(fn ($n) => $this->noteName($n), $extras))),
                'L15: more than two distinct chromatic approach tones');
        }
    }

    public function test_minor_lessons_carry_relative_minor_tonic_and_mode(): void
    {
        foreach (['L9 natural minor', 'L13 harmonic minor', 'L14 melodic minor'] as $label) {
            foreach ($this->generate($this->lessonConfigs()[$label]) as $q) {
                $this->assertSame('C', $q->key_signature, "$label: minor lessons notate in the relative major");
                $this->assertSame('A', $q->tonic, "$label: tonic must be the relative minor root");
                $this->assertSame('minor', $q->mode, "$label: mode attribute missing");
            }
        }
    }

    public function test_major_lessons_carry_matching_tonic_and_mode(): void
    {
        foreach ($this->generate($this->lessonConfigs()['L16 master four-bar']) as $q) {
            $this->assertContains($q->key_signature, ['C', 'G', 'F'], 'L16: key outside the lesson set');
            $this->assertSame($q->key_signature, $q->tonic, 'L16: major tonic must equal the key root');
            $this->assertSame('major', $q->mode, 'L16: mode attribute missing');
        }
    }

    public function test_questions_survive_session_serialization(): void
    {
        $questions = $this->generate($this->lessonConfigs()['L13 harmonic minor'], 5);
        $serialized = $this->generator->serializeForSession($questions);
        $rebuilt = $this->generator->reconstructFromSession($serialized, 'melodic-dictation');

        $this->assertCount(5, $rebuilt);
        foreach ($rebuilt as $i => $q) {
            $this->assertSame($questions[$i]->notes, is_array($q->notes) ? $q->notes : json_decode($q->notes, true));
            $this->assertSame('minor', $q->mode);
            $this->assertSame('A', $q->tonic);
            $this->assertNotEmpty($q->note_values);
        }
    }

    public function test_tempo_stays_inside_the_lesson_range(): void
    {
        foreach ($this->lessonConfigs() as $label => $config) {
            foreach ($this->generate($config) as $q) {
                $this->assertGreaterThanOrEqual($config['tempo_range'][0], $q->tempo, "$label: tempo below range");
                $this->assertLessThanOrEqual($config['tempo_range'][1], $q->tempo, "$label: tempo above range");
            }
        }
    }
}
