<?php

namespace Tests\Unit;

use App\Services\MusicTheoryService;
use Tests\TestCase;

class MusicTheoryServiceTest extends TestCase
{
    private MusicTheoryService $music;

    protected function setUp(): void
    {
        parent::setUp();
        $this->music = new MusicTheoryService();
    }

    // ── Direction tests (from acceptance criteria) ────────────────────────────

    public function test_C4_to_E4_is_ascending(): void
    {
        $this->assertSame('ascending', $this->music->getDirection('C', 4, 'E', 4));
    }

    public function test_E4_to_C4_is_descending(): void
    {
        $this->assertSame('descending', $this->music->getDirection('E', 4, 'C', 4));
    }

    public function test_C5_to_E4_is_descending(): void
    {
        $this->assertSame('descending', $this->music->getDirection('C', 5, 'E', 4));
    }

    public function test_A3_to_C4_is_ascending(): void
    {
        $this->assertSame('ascending', $this->music->getDirection('A', 3, 'C', 4));
    }

    public function test_G4_to_G4_is_unison(): void
    {
        $this->assertSame('unison', $this->music->getDirection('G', 4, 'G', 4));
    }

    // ── Interval name calculation ─────────────────────────────────────────────

    public function test_C4_to_E4_is_Major_3rd(): void
    {
        $semitones = abs($this->music->semitonesBetween('C', 4, 'E', 4));
        $this->assertSame(4, $semitones);
        $this->assertSame('Major 3rd', $this->music->intervalNameFromSemitones($semitones));
    }

    public function test_A3_to_C4_is_Minor_3rd(): void
    {
        $semitones = abs($this->music->semitonesBetween('A', 3, 'C', 4));
        $this->assertSame(3, $semitones);
        $this->assertSame('Minor 3rd', $this->music->intervalNameFromSemitones($semitones));
    }

    public function test_C4_to_C5_is_Perfect_Octave(): void
    {
        $semitones = abs($this->music->semitonesBetween('C', 4, 'C', 5));
        $this->assertSame(12, $semitones);
        $this->assertSame('Perfect Octave', $this->music->intervalNameFromSemitones($semitones));
    }

    // ── noteAboveByInterval — cross-octave correctness ────────────────────────

    public function test_B4_plus_M3_ascending_lands_in_octave5(): void
    {
        $result = $this->music->noteAboveByInterval('B', 4, 'Major 3rd');
        $this->assertNotNull($result);
        $this->assertSame('D#', $result['note']);
        $this->assertSame(5, $result['octave']);
        // Verify the direction is actually ascending
        $this->assertSame('ascending', $this->music->getDirection('B', 4, $result['note'], $result['octave']));
    }

    public function test_A4_plus_P5_ascending_lands_in_octave5(): void
    {
        $result = $this->music->noteAboveByInterval('A', 4, 'Perfect 5th');
        $this->assertNotNull($result);
        $this->assertSame('E', $result['note']);
        $this->assertSame(5, $result['octave']);
        $this->assertSame('ascending', $this->music->getDirection('A', 4, $result['note'], $result['octave']));
    }

    public function test_C4_plus_P8_ascending_lands_in_octave5(): void
    {
        $result = $this->music->noteAboveByInterval('C', 4, 'Perfect Octave');
        $this->assertNotNull($result);
        $this->assertSame('C', $result['note']);
        $this->assertSame(5, $result['octave']);
    }

    // ── MIDI number arithmetic ────────────────────────────────────────────────

    public function test_midi_number_C4_is_60(): void
    {
        $this->assertSame(60, $this->music->midiNumber('C', 4));
    }

    public function test_note_from_midi_60_is_C4(): void
    {
        $result = $this->music->noteFromMidi(60);
        $this->assertSame('C', $result['note']);
        $this->assertSame(4, $result['octave']);
    }

    public function test_signed_semitones_C4_to_E4_is_positive_4(): void
    {
        $this->assertSame(4, $this->music->semitonesBetween('C', 4, 'E', 4));
    }

    public function test_signed_semitones_E4_to_C4_is_negative_4(): void
    {
        $this->assertSame(-4, $this->music->semitonesBetween('E', 4, 'C', 4));
    }

    // ── Answer option consistency ─────────────────────────────────────────────

    public function test_correct_answer_appears_exactly_once_in_options(): void
    {
        $correct = 'Major 3rd';
        $pool    = ['Minor 2nd', 'Major 2nd', 'Minor 3rd', 'Major 3rd', 'Perfect 4th'];
        $distractors = $this->music->buildOptions($correct, $pool, 1);

        // Build final options array as components do (correct + distractors)
        $options = array_merge([$correct], $distractors);

        $count = array_count_values($options)[$correct] ?? 0;
        $this->assertSame(1, $count, 'Correct answer must appear exactly once');
    }

    public function test_wrong_options_do_not_include_correct_answer(): void
    {
        $correct = 'Perfect 5th';
        $pool    = ['Minor 2nd', 'Major 2nd', 'Perfect 4th', 'Perfect 5th', 'Minor 6th'];

        $distractors = $this->music->buildOptions($correct, $pool, 3);

        $this->assertNotContains($correct, $distractors, 'Distractors must not include the correct answer');
    }

    public function test_tritone_aliases_are_normalized_consistently(): void
    {
        $this->assertSame('Tritone', $this->music->normalizeIntervalName('Augmented 4th'));
        $this->assertSame('Tritone', $this->music->normalizeIntervalName('Diminished 5th'));
        $this->assertSame('Tritone', $this->music->normalizeIntervalName('Tritone'));
    }

    // ── Validation ────────────────────────────────────────────────────────────

    public function test_validates_correct_direction_question(): void
    {
        $result = $this->music->validateQuestionConsistency([
            'note1' => 'C', 'octave' => 4, 'note2' => 'E', 'note2_octave' => 4,
            'direction' => 'ascending',
        ], 'interval-direction-practice');

        $this->assertTrue($result['valid']);
        $this->assertSame('valid', $result['status']);
        $this->assertEmpty($result['issues']);
    }

    public function test_detects_direction_mismatch(): void
    {
        // C4 → E4 is ascending but stored as descending
        $result = $this->music->validateQuestionConsistency([
            'note1' => 'C', 'octave' => 4, 'note2' => 'E', 'note2_octave' => 4,
            'direction' => 'descending',
        ], 'interval-direction-practice');

        $this->assertFalse($result['valid']);
        $this->assertContains('direction_mismatch', $result['issues']);
    }

    public function test_validates_correct_melodic_interval_question(): void
    {
        $result = $this->music->validateQuestionConsistency([
            'note1' => 'C', 'octave' => 4, 'note2' => 'E', 'note2_octave' => 4,
            'interval' => 'Major 3rd',
        ], 'melodic-interval-practice');

        $this->assertTrue($result['valid']);
    }

    public function test_detects_interval_answer_mismatch(): void
    {
        // C4 → E4 is Major 3rd, not Perfect 5th
        $result = $this->music->validateQuestionConsistency([
            'note1' => 'C', 'octave' => 4, 'note2' => 'E', 'note2_octave' => 4,
            'interval' => 'Perfect 5th',
        ], 'melodic-interval-practice');

        $this->assertFalse($result['valid']);
        $this->assertContains('answer_mismatch', $result['issues']);
    }

    public function test_cross_octave_interval_direction_is_derived_from_midi_pitch(): void
    {
        // B4 ascending Major 3rd = D#5 (NOT D#4 which would be descending)
        $result = $this->music->noteAboveByInterval('B', 4, 'Major 3rd');
        $this->assertNotNull($result);

        // If we stored both at octave 4 (the old bug), getDirection would say descending
        $wrongDirection  = $this->music->getDirection('B', 4, $result['note'], 4);
        $rightDirection  = $this->music->getDirection('B', 4, $result['note'], $result['octave']);

        $this->assertSame('descending', $wrongDirection, 'Same-octave assumption gives wrong result');
        $this->assertSame('ascending', $rightDirection, 'Correct octave-aware direction');
    }

    // ── resolveNote2OctaveFromDirection (AI post-processing) ──────────────────

    public function test_resolve_ascending_same_octave_when_note2_index_higher(): void
    {
        // C4 → E ascending: E (idx 4) > C (idx 0) → same octave 4
        $octave2 = $this->music->resolveNote2OctaveFromDirection('C', 4, 'E', 'ascending');
        $this->assertSame(4, $octave2);
        $this->assertSame('ascending', $this->music->getDirection('C', 4, 'E', $octave2));
    }

    public function test_resolve_ascending_bumps_octave_for_cross_octave_case(): void
    {
        // B4 → D# ascending: D# (idx 3) <= B (idx 11) → octave 5
        $octave2 = $this->music->resolveNote2OctaveFromDirection('B', 4, 'D#', 'ascending');
        $this->assertSame(5, $octave2);
        $this->assertSame('ascending', $this->music->getDirection('B', 4, 'D#', $octave2));
    }

    public function test_resolve_descending_same_octave_when_note2_index_lower(): void
    {
        // E4 → C descending: C (idx 0) < E (idx 4) → same octave 4
        $octave2 = $this->music->resolveNote2OctaveFromDirection('E', 4, 'C', 'descending');
        $this->assertSame(4, $octave2);
        $this->assertSame('descending', $this->music->getDirection('E', 4, 'C', $octave2));
    }

    public function test_resolve_descending_drops_octave_for_cross_octave_case(): void
    {
        // C4 → B descending: B (idx 11) >= C (idx 0) → octave 3
        $octave2 = $this->music->resolveNote2OctaveFromDirection('C', 4, 'B', 'descending');
        $this->assertSame(3, $octave2);
        $this->assertSame('descending', $this->music->getDirection('C', 4, 'B', $octave2));
    }

    // ── Interval comparison sizing (same-octave gap) ──────────────────────────

    public function test_interval_pair_semitones_uses_same_octave_gap(): void
    {
        // Both notes share one octave, so the span is the absolute index gap.
        $this->assertSame(4, $this->music->intervalPairSemitones('C,E'));   // major 3rd
        $this->assertSame(9, $this->music->intervalPairSemitones('A,C'));   // A4..C4 → major 6th
        $this->assertSame(9, $this->music->intervalPairSemitones('B,D'));   // B4..D4
        $this->assertSame(0, $this->music->intervalPairSemitones('C,C'));
    }

    public function test_interval_pair_semitones_returns_null_for_invalid_pair(): void
    {
        $this->assertNull($this->music->intervalPairSemitones('C'));
        $this->assertNull($this->music->intervalPairSemitones('C,H'));
        $this->assertNull($this->music->intervalPairSemitones('C,E,G'));
    }

    public function test_larger_interval_pair_matches_what_is_rendered(): void
    {
        // The bug case: A,C (9) vs C,E (4) — A is larger, not B.
        $this->assertSame('a', $this->music->largerIntervalPair('A,C', 'C,E'));
        $this->assertSame('a', $this->music->largerIntervalPair('B,D', 'C,E'));
        $this->assertSame('b', $this->music->largerIntervalPair('C,E', 'C,G'));
    }

    public function test_larger_interval_pair_returns_null_for_equal_or_invalid(): void
    {
        // Equal size (both major 3rds) → unanswerable.
        $this->assertNull($this->music->largerIntervalPair('C,E', 'F,A'));
        $this->assertNull($this->music->largerIntervalPair('C,E', 'C,H'));
    }

    public function test_resolve_direction_matches_getDirection_for_all_chromatic_pairs(): void
    {
        // For every chromatic pair, resolveNote2OctaveFromDirection then getDirection
        // must round-trip back to the stated direction.
        $notes = MusicTheoryService::CHROMATIC_NOTES;
        $directions = ['ascending', 'descending'];

        foreach ($notes as $n1) {
            foreach ($notes as $n2) {
                if ($n1 === $n2) continue; // unison is ambiguous, skip
                foreach ($directions as $dir) {
                    $oct2 = $this->music->resolveNote2OctaveFromDirection($n1, 4, $n2, $dir);
                    $derived = $this->music->getDirection($n1, 4, $n2, $oct2);
                    $this->assertSame(
                        $dir, $derived,
                        "resolveNote2OctaveFromDirection('$n1', 4, '$n2', '$dir') → octave $oct2, but getDirection says '$derived'"
                    );
                }
            }
        }
    }
}
