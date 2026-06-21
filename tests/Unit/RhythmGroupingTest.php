<?php

namespace Tests\Unit;

use App\Services\RhythmGroupingService;
use PHPUnit\Framework\TestCase;

class RhythmGroupingTest extends TestCase
{
    private RhythmGroupingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RhythmGroupingService;
    }

    // ─── validate ──────────────────────────────────────────────────────────────

    public function test_2_4_validates_two_quarters(): void
    {
        $this->assertTrue($this->service->validate(['quarter', 'quarter'], '2/4'));
    }

    public function test_3_4_validates_three_quarters(): void
    {
        $this->assertTrue($this->service->validate(['quarter', 'quarter', 'quarter'], '3/4'));
    }

    public function test_4_4_validates_four_quarters(): void
    {
        $this->assertTrue($this->service->validate(['quarter', 'quarter', 'quarter', 'quarter'], '4/4'));
    }

    public function test_4_4_validates_one_whole(): void
    {
        $this->assertTrue($this->service->validate(['whole'], '4/4'));
    }

    public function test_6_8_validates_six_eighths(): void
    {
        $pattern = array_fill(0, 6, 'eighth');
        $this->assertTrue($this->service->validate($pattern, '6/8'));
    }

    public function test_6_8_validates_two_dotted_quarters(): void
    {
        $this->assertTrue($this->service->validate(['dotted-quarter', 'dotted-quarter'], '6/8'));
    }

    public function test_9_8_validates_nine_eighths(): void
    {
        $this->assertTrue($this->service->validate(array_fill(0, 9, 'eighth'), '9/8'));
    }

    public function test_3_8_validates_three_eighths(): void
    {
        $this->assertTrue($this->service->validate(['eighth', 'eighth', 'eighth'], '3/8'));
    }

    public function test_3_8_validates_dotted_quarter(): void
    {
        $this->assertTrue($this->service->validate(['dotted-quarter'], '3/8'));
    }

    public function test_short_pattern_fails_validation(): void
    {
        $this->assertFalse($this->service->validate(['quarter'], '4/4'));
    }

    public function test_over_full_pattern_fails_validation(): void
    {
        $this->assertFalse($this->service->validate(array_fill(0, 5, 'quarter'), '4/4'));
    }

    // ─── group: 2/4 ────────────────────────────────────────────────────────────

    public function test_2_4_groups_as_two_quarter_beats(): void
    {
        $groups = $this->service->group(['quarter', 'quarter'], '2/4');
        $this->assertCount(2, $groups);
        $this->assertSame(['quarter'], $groups[0]);
        $this->assertSame(['quarter'], $groups[1]);
    }

    public function test_2_4_groups_eighths_within_beat_1(): void
    {
        $groups = $this->service->group(['eighth', 'eighth', 'quarter'], '2/4');
        $this->assertCount(2, $groups);
        $this->assertSame(['eighth', 'eighth'], $groups[0]);
        $this->assertSame(['quarter'], $groups[1]);
    }

    public function test_2_4_groups_note_pattern_with_sixteenths(): void
    {
        // [s+s+e] + [q]  → beat 0 total = 3+3+6 = 12 ✓, beat 1 total = 12 ✓
        $groups = $this->service->group(['sixteenth', 'sixteenth', 'eighth', 'quarter'], '2/4');
        $this->assertCount(2, $groups);
        $this->assertSame(['sixteenth', 'sixteenth', 'eighth'], $groups[0]);
        $this->assertSame(['quarter'], $groups[1]);
    }

    // ─── group: 3/4 ────────────────────────────────────────────────────────────

    public function test_3_4_groups_as_three_quarter_beats(): void
    {
        $groups = $this->service->group(['quarter', 'quarter', 'quarter'], '3/4');
        $this->assertCount(3, $groups);
        $this->assertSame(['quarter'], $groups[0]);
        $this->assertSame(['quarter'], $groups[1]);
        $this->assertSame(['quarter'], $groups[2]);
    }

    public function test_3_4_groups_mixed_beats(): void
    {
        // [e+e] + [q] + [e+s+s]
        $groups = $this->service->group(['eighth', 'eighth', 'quarter', 'eighth', 'sixteenth', 'sixteenth'], '3/4');
        $this->assertCount(3, $groups);
        $this->assertSame(['eighth', 'eighth'], $groups[0]);
        $this->assertSame(['quarter'], $groups[1]);
        $this->assertSame(['eighth', 'sixteenth', 'sixteenth'], $groups[2]);
    }

    // ─── group: 4/4 ────────────────────────────────────────────────────────────

    public function test_4_4_groups_as_four_quarter_beats(): void
    {
        $groups = $this->service->group(['quarter', 'quarter', 'quarter', 'quarter'], '4/4');
        $this->assertCount(4, $groups);
        foreach ($groups as $g) {
            $this->assertSame(['quarter'], $g);
        }
    }

    public function test_4_4_groups_varied_beats(): void
    {
        // [e+e] + [q] + [s+s+s+s] + [q]
        $pattern = ['eighth', 'eighth', 'quarter', 'sixteenth', 'sixteenth', 'sixteenth', 'sixteenth', 'quarter'];
        $groups = $this->service->group($pattern, '4/4');
        $this->assertCount(4, $groups);
        $this->assertSame(['eighth', 'eighth'], $groups[0]);
        $this->assertSame(['quarter'], $groups[1]);
        $this->assertSame(['sixteenth', 'sixteenth', 'sixteenth', 'sixteenth'], $groups[2]);
        $this->assertSame(['quarter'], $groups[3]);
    }

    public function test_4_4_whole_note_goes_to_beat_0(): void
    {
        $groups = $this->service->group(['whole'], '4/4');
        $this->assertCount(4, $groups);
        $this->assertSame(['whole'], $groups[0]);
        $this->assertSame([], $groups[1]);
        $this->assertSame([], $groups[2]);
        $this->assertSame([], $groups[3]);
    }

    public function test_4_4_half_notes_start_on_correct_beats(): void
    {
        $groups = $this->service->group(['half', 'half'], '4/4');
        $this->assertCount(4, $groups);
        $this->assertSame(['half'], $groups[0]);
        $this->assertSame([], $groups[1]);
        $this->assertSame(['half'], $groups[2]);
        $this->assertSame([], $groups[3]);
    }

    // ─── group: 3/8 ────────────────────────────────────────────────────────────

    public function test_3_8_groups_as_single_dotted_quarter_beat(): void
    {
        $groups = $this->service->group(['eighth', 'eighth', 'eighth'], '3/8');
        $this->assertCount(1, $groups);
        $this->assertSame(['eighth', 'eighth', 'eighth'], $groups[0]);
    }

    public function test_3_8_dotted_quarter_is_one_group(): void
    {
        $groups = $this->service->group(['dotted-quarter'], '3/8');
        $this->assertCount(1, $groups);
        $this->assertSame(['dotted-quarter'], $groups[0]);
    }

    public function test_3_8_quarter_plus_eighth_is_one_group(): void
    {
        $groups = $this->service->group(['quarter', 'eighth'], '3/8');
        $this->assertCount(1, $groups);
        $this->assertSame(['quarter', 'eighth'], $groups[0]);
    }

    // ─── group: 6/8 ────────────────────────────────────────────────────────────

    public function test_6_8_groups_as_two_dotted_quarter_beats(): void
    {
        $groups = $this->service->group(array_fill(0, 6, 'eighth'), '6/8');
        $this->assertCount(2, $groups);
        $this->assertSame(['eighth', 'eighth', 'eighth'], $groups[0]);
        $this->assertSame(['eighth', 'eighth', 'eighth'], $groups[1]);
    }

    public function test_6_8_two_dotted_quarters(): void
    {
        $groups = $this->service->group(['dotted-quarter', 'dotted-quarter'], '6/8');
        $this->assertCount(2, $groups);
        $this->assertSame(['dotted-quarter'], $groups[0]);
        $this->assertSame(['dotted-quarter'], $groups[1]);
    }

    public function test_6_8_quarter_eighth_then_dotted_quarter(): void
    {
        // [q+e] fills beat 0 (12+6=18 twelfths); [dq] fills beat 1
        $groups = $this->service->group(['quarter', 'eighth', 'dotted-quarter'], '6/8');
        $this->assertCount(2, $groups);
        $this->assertSame(['quarter', 'eighth'], $groups[0]);
        $this->assertSame(['dotted-quarter'], $groups[1]);
    }

    public function test_6_8_eighth_dotted_eighth_sixteenth_then_dotted_quarter(): void
    {
        // beat 0: e(6) + de(9) + s(3) = 18; beat 1: dq(18)
        $groups = $this->service->group(['eighth', 'dotted-eighth', 'sixteenth', 'dotted-quarter'], '6/8');
        $this->assertCount(2, $groups);
        $this->assertSame(['eighth', 'dotted-eighth', 'sixteenth'], $groups[0]);
        $this->assertSame(['dotted-quarter'], $groups[1]);
    }

    // ─── group: 9/8 ────────────────────────────────────────────────────────────

    public function test_9_8_groups_as_three_dotted_quarter_beats(): void
    {
        $groups = $this->service->group(array_fill(0, 9, 'eighth'), '9/8');
        $this->assertCount(3, $groups);
        foreach ($groups as $g) {
            $this->assertSame(['eighth', 'eighth', 'eighth'], $g);
        }
    }

    public function test_9_8_three_dotted_quarters(): void
    {
        $groups = $this->service->group(['dotted-quarter', 'dotted-quarter', 'dotted-quarter'], '9/8');
        $this->assertCount(3, $groups);
        foreach ($groups as $g) {
            $this->assertSame(['dotted-quarter'], $g);
        }
    }

    public function test_9_8_mixed_beat_patterns(): void
    {
        // [q+e] + [e+e+e] + [e+q]
        $pattern = ['quarter', 'eighth', 'eighth', 'eighth', 'eighth', 'eighth', 'quarter'];
        $groups = $this->service->group($pattern, '9/8');
        $this->assertCount(3, $groups);
        $this->assertSame(['quarter', 'eighth'], $groups[0]);
        $this->assertSame(['eighth', 'eighth', 'eighth'], $groups[1]);
        $this->assertSame(['eighth', 'quarter'], $groups[2]);
    }

    // ─── group: 2/2, 3/2, 4/2 — quarter-note visual groups ───────────────────

    public function test_2_2_validate_two_half_notes(): void
    {
        $this->assertTrue($this->service->validate(['half', 'half'], '2/2'));
    }

    public function test_3_2_validate_three_half_notes(): void
    {
        $this->assertTrue($this->service->validate(['half', 'half', 'half'], '3/2'));
    }

    public function test_4_2_validate_four_half_notes(): void
    {
        $this->assertTrue($this->service->validate(['half', 'half', 'half', 'half'], '4/2'));
    }

    /** 2/2 = 4 quarter-note visual groups */
    public function test_2_2_groups_as_four_quarter_groups(): void
    {
        // half+half fills 2/2; each half note spans two quarter groups
        $groups = $this->service->group(['half', 'half'], '2/2');
        $this->assertCount(4, $groups);
        $this->assertSame(['half'], $groups[0]);
        $this->assertSame([], $groups[1]);
        $this->assertSame(['half'], $groups[2]);
        $this->assertSame([], $groups[3]);
    }

    /** 2/2 complex: four quarter-note groups, not two half-note groups */
    public function test_2_2_complex_pattern_four_groups(): void
    {
        // [e+e] + [e+s+s] + [q] + [s+s+e]  — 4 groups each = 12 twelfths
        $pattern = ['eighth', 'eighth', 'eighth', 'sixteenth', 'sixteenth', 'quarter', 'sixteenth', 'sixteenth', 'eighth'];
        $groups  = $this->service->group($pattern, '2/2');

        $this->assertCount(4, $groups);
        $this->assertSame(['eighth', 'eighth'], $groups[0]);
        $this->assertSame(['eighth', 'sixteenth', 'sixteenth'], $groups[1]);
        $this->assertSame(['quarter'], $groups[2]);
        $this->assertSame(['sixteenth', 'sixteenth', 'eighth'], $groups[3]);
    }

    /** 2/2 dotted: dotted-eighth+sixteenth stays inside one quarter group */
    public function test_2_2_dotted_eighth_sixteenth_in_same_group(): void
    {
        // [de+s] + [q] + [e+e] + [q]
        $pattern = ['dotted-eighth', 'sixteenth', 'quarter', 'eighth', 'eighth', 'quarter'];
        $groups  = $this->service->group($pattern, '2/2');

        $this->assertCount(4, $groups);
        $this->assertSame(['dotted-eighth', 'sixteenth'], $groups[0]);
        $this->assertSame(['quarter'], $groups[1]);
        $this->assertSame(['eighth', 'eighth'], $groups[2]);
        $this->assertSame(['quarter'], $groups[3]);
    }

    /** 3/2 = 6 quarter-note visual groups */
    public function test_3_2_groups_as_six_quarter_groups(): void
    {
        $pattern = ['quarter', 'quarter', 'quarter', 'quarter', 'quarter', 'quarter'];
        $groups  = $this->service->group($pattern, '3/2');

        $this->assertCount(6, $groups);
        foreach ($groups as $g) {
            $this->assertSame(['quarter'], $g);
        }
    }

    /** 3/2 dotted-half spans three groups */
    public function test_3_2_dotted_half_spans_three_groups(): void
    {
        // dotted-half (36 twelfths) + half (24) + quarter (12) = 72 = 3/2
        $groups = $this->service->group(['dotted-half', 'half', 'quarter'], '3/2');

        $this->assertCount(6, $groups);
        $this->assertSame(['dotted-half'], $groups[0]);
        $this->assertSame([], $groups[1]);
        $this->assertSame([], $groups[2]);
        $this->assertSame(['half'], $groups[3]);
        $this->assertSame([], $groups[4]);
        $this->assertSame(['quarter'], $groups[5]);
    }

    /** 4/2 = 8 quarter-note visual groups */
    public function test_4_2_groups_as_eight_quarter_groups(): void
    {
        $pattern = array_fill(0, 8, 'quarter');
        $groups  = $this->service->group($pattern, '4/2');

        $this->assertCount(8, $groups);
        foreach ($groups as $g) {
            $this->assertSame(['quarter'], $g);
        }
    }

    /** 4/2 eighth notes do not beam across a quarter-note boundary */
    public function test_4_2_eighths_stay_within_quarter_groups(): void
    {
        // 4/2 = 96 twelfths; 16 × eighth(6) = 96; yields 8 groups of [e+e]
        $pattern = array_fill(0, 16, 'eighth');
        $groups  = $this->service->group($pattern, '4/2');

        $this->assertCount(8, $groups);
        foreach ($groups as $g) {
            $this->assertSame(['eighth', 'eighth'], $g);
        }
    }

    /** 4/2 whole note spans all 8 groups */
    public function test_4_2_whole_note_goes_to_group_0(): void
    {
        // whole(48) + whole(48) = 96 = 4/2
        $groups = $this->service->group(['whole', 'whole'], '4/2');

        $this->assertCount(8, $groups);
        $this->assertSame(['whole'], $groups[0]);
        for ($i = 1; $i <= 3; $i++) {
            $this->assertSame([], $groups[$i]);
        }
        $this->assertSame(['whole'], $groups[4]);
        for ($i = 5; $i <= 7; $i++) {
            $this->assertSame([], $groups[$i]);
        }
    }

    // ─── visualGroupTwelfths / visualGroupCount ────────────────────────────────

    public function test_visual_group_twelfths_is_12_for_x_2(): void
    {
        $this->assertSame(12, $this->service->visualGroupTwelfths(2));
    }

    public function test_visual_group_twelfths_unchanged_for_x_4(): void
    {
        $this->assertSame(12, $this->service->visualGroupTwelfths(4));
    }

    public function test_visual_group_twelfths_unchanged_for_x_8(): void
    {
        $this->assertSame(18, $this->service->visualGroupTwelfths(8));
    }

    public function test_visual_group_count_2_2_is_4(): void
    {
        $this->assertSame(4, $this->service->visualGroupCount(2, 2));
    }

    public function test_visual_group_count_3_2_is_6(): void
    {
        $this->assertSame(6, $this->service->visualGroupCount(3, 2));
    }

    public function test_visual_group_count_4_2_is_8(): void
    {
        $this->assertSame(8, $this->service->visualGroupCount(4, 2));
    }

    public function test_visual_group_count_4_4_is_4(): void
    {
        $this->assertSame(4, $this->service->visualGroupCount(4, 4));
    }

    public function test_visual_group_count_6_8_is_2(): void
    {
        $this->assertSame(2, $this->service->visualGroupCount(6, 8));
    }

    /** beatTwelfths(2) still returns 24 — metric beat unchanged */
    public function test_beat_twelfths_for_x_2_unchanged(): void
    {
        $this->assertSame(24, $this->service->beatTwelfths(2));
    }

    /** measureTwelfths for x/2 still based on half-note metric beat */
    public function test_measure_twelfths_for_x_2(): void
    {
        $this->assertSame(48,  $this->service->measureTwelfths(2, 2)); // 2/2 = 4 quarter notes = 48
        $this->assertSame(72,  $this->service->measureTwelfths(3, 2)); // 3/2 = 6 quarter notes = 72
        $this->assertSame(96,  $this->service->measureTwelfths(4, 2)); // 4/2 = 8 quarter notes = 96
    }

    // ─── display invariants ────────────────────────────────────────────────────

    public function test_adjacent_rests_are_not_merged(): void
    {
        $groups = $this->service->group(['quarter_rest', 'quarter_rest'], '2/4');
        $this->assertCount(2, $groups);
        $this->assertSame(['quarter_rest'], $groups[0]);
        $this->assertSame(['quarter_rest'], $groups[1]);
    }

    public function test_dotted_quarter_not_converted_in_6_8(): void
    {
        $groups = $this->service->group(['dotted-quarter', 'dotted-quarter'], '6/8');
        $this->assertSame('dotted-quarter', $groups[0][0]);
        $this->assertSame('dotted-quarter', $groups[1][0]);
    }

    public function test_dotted_eighth_not_converted_in_simple_meter(): void
    {
        // de(9) + s(3) = 12 = 1 quarter beat; q = beat 1
        $groups = $this->service->group(['dotted-eighth', 'sixteenth', 'quarter'], '2/4');
        $this->assertSame('dotted-eighth', $groups[0][0]);
        $this->assertSame('sixteenth', $groups[0][1]);
    }

    public function test_answer_options_and_correct_answer_use_same_measure_duration(): void
    {
        // Both the correct answer and alternatives must validate against the same time signature.
        $timeSig = '4/4';
        $correct = ['quarter', 'eighth', 'eighth', 'half'];
        $alt1    = ['quarter', 'quarter', 'half'];
        $alt2    = ['whole'];

        $this->assertTrue($this->service->validate($correct, $timeSig));
        $this->assertTrue($this->service->validate($alt1, $timeSig));
        $this->assertTrue($this->service->validate($alt2, $timeSig));
    }

    // ─── meter helper methods ──────────────────────────────────────────────────

    public function test_beat_twelfths_for_simple_meters(): void
    {
        $this->assertSame(12, $this->service->beatTwelfths(4));
        $this->assertSame(24, $this->service->beatTwelfths(2));
    }

    public function test_beat_twelfths_for_compound_meter(): void
    {
        $this->assertSame(18, $this->service->beatTwelfths(8));
    }

    public function test_measure_twelfths_simple(): void
    {
        $this->assertSame(24, $this->service->measureTwelfths(2, 4)); // 2/4
        $this->assertSame(36, $this->service->measureTwelfths(3, 4)); // 3/4
        $this->assertSame(48, $this->service->measureTwelfths(4, 4)); // 4/4
    }

    public function test_measure_twelfths_compound(): void
    {
        $this->assertSame(18, $this->service->measureTwelfths(3, 8)); // 3/8
        $this->assertSame(36, $this->service->measureTwelfths(6, 8)); // 6/8
        $this->assertSame(54, $this->service->measureTwelfths(9, 8)); // 9/8
    }

    public function test_beat_count_simple(): void
    {
        $this->assertSame(2, $this->service->beatCount(2, 4));
        $this->assertSame(3, $this->service->beatCount(3, 4));
        $this->assertSame(4, $this->service->beatCount(4, 4));
    }

    public function test_beat_count_compound(): void
    {
        $this->assertSame(1, $this->service->beatCount(3, 8));
        $this->assertSame(2, $this->service->beatCount(6, 8));
        $this->assertSame(3, $this->service->beatCount(9, 8));
    }
}
