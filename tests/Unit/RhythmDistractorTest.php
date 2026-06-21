<?php

namespace Tests\Unit;

use App\Services\RhythmDistractorService;
use App\Services\RhythmGroupingService;
use Tests\TestCase;

class RhythmDistractorTest extends TestCase
{
    private RhythmDistractorService $svc;
    private RhythmGroupingService $groupingSvc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->groupingSvc = new RhythmGroupingService;
        $this->svc         = new RhythmDistractorService($this->groupingSvc);
    }

    // ── generate() ──────────────────────────────────────────────────────────────

    /** 4/4: three distractors are returned */
    public function test_generates_three_distractors_4_4(): void
    {
        $correct     = ['quarter', 'quarter', 'quarter', 'quarter'];
        $distractors = $this->svc->generate($correct, '4/4', 'medium');

        $this->assertCount(3, $distractors);
    }

    /** 6/8: three distractors are returned */
    public function test_generates_three_distractors_6_8(): void
    {
        $correct     = ['eighth', 'eighth', 'eighth', 'eighth', 'eighth', 'eighth'];
        $distractors = $this->svc->generate($correct, '6/8', 'medium');

        $this->assertCount(3, $distractors);
    }

    /** No distractor equals the correct answer */
    public function test_no_distractor_equals_correct(): void
    {
        $correct     = ['quarter', 'eighth', 'eighth', 'half'];
        $distractors = $this->svc->generate($correct, '4/4', 'medium');

        foreach ($distractors as $d) {
            $this->assertNotEquals($correct, $d, 'A distractor must not equal the correct answer');
        }
    }

    /** All distractors are distinct */
    public function test_distractors_are_distinct(): void
    {
        $correct     = ['dotted-quarter', 'eighth', 'dotted-quarter', 'eighth'];
        $distractors = $this->svc->generate($correct, '4/4', 'medium');

        $unique = array_unique(array_map(fn ($d) => implode(',', $d), $distractors));
        $this->assertCount(count($distractors), $unique, 'All distractors must be distinct');
    }

    /** Every distractor fills exactly the same measure duration */
    public function test_distractors_have_same_total_duration_4_4(): void
    {
        $correct     = ['eighth', 'eighth', 'quarter', 'half'];
        $totalT      = $this->measureTwelfths($correct);
        $distractors = $this->svc->generate($correct, '4/4', 'medium');

        foreach ($distractors as $d) {
            $this->assertSame($totalT, $this->measureTwelfths($d), 'Distractor duration must match correct');
        }
    }

    /** 6/8 distractors also preserve the total measure duration */
    public function test_distractors_have_same_total_duration_6_8(): void
    {
        $correct     = ['quarter', 'eighth', 'dotted-quarter'];
        $totalT      = $this->measureTwelfths($correct);
        $distractors = $this->svc->generate($correct, '6/8', 'medium');

        foreach ($distractors as $d) {
            $this->assertSame($totalT, $this->measureTwelfths($d), 'Distractor duration must match correct for 6/8');
        }
    }

    /** Dotted rhythms produce distractors (not empty) */
    public function test_dotted_rhythms_produce_distractors(): void
    {
        $correct     = ['dotted-quarter', 'eighth', 'dotted-quarter', 'eighth'];
        $distractors = $this->svc->generate($correct, '4/4', 'medium');

        $this->assertNotEmpty($distractors);
    }

    /** Easy difficulty still produces at least 1 distractor */
    public function test_easy_difficulty_produces_distractors(): void
    {
        $correct     = ['quarter', 'quarter', 'half'];
        $distractors = $this->svc->generate($correct, '4/4', 'easy');

        $this->assertNotEmpty($distractors);
    }

    /** 4/2: three distractors with correct total duration (quarter-note grouping) */
    public function test_generates_three_distractors_4_2(): void
    {
        // 4/2 = 8 quarter-note groups; each pair of quarters = one group
        $correct     = ['quarter', 'quarter', 'quarter', 'quarter', 'quarter', 'quarter', 'quarter', 'quarter'];
        $distractors = $this->svc->generate($correct, '4/2', 'medium');

        $this->assertNotEmpty($distractors);

        $totalT = $this->measureTwelfths($correct);
        foreach ($distractors as $d) {
            $this->assertSame($totalT, $this->measureTwelfths($d), '4/2 distractor must match correct duration');
        }
    }

    /** 2/2: distractors preserve total duration */
    public function test_generates_distractors_2_2(): void
    {
        $correct     = ['eighth', 'eighth', 'quarter', 'eighth', 'eighth', 'quarter'];
        $distractors = $this->svc->generate($correct, '2/2', 'medium');

        $this->assertNotEmpty($distractors);
        $totalT = $this->measureTwelfths($correct);
        foreach ($distractors as $d) {
            $this->assertSame($totalT, $this->measureTwelfths($d));
        }
    }

    /** Hard difficulty produces exactly 3 distractors */
    public function test_hard_difficulty_produces_three_distractors(): void
    {
        $correct     = ['eighth', 'eighth', 'eighth', 'eighth', 'quarter', 'quarter'];
        $distractors = $this->svc->generate($correct, '4/4', 'hard');

        $this->assertCount(3, $distractors);
    }

    // ── accumulateGroups() ───────────────────────────────────────────────────────

    /** 4/4: four groups, one beat each */
    public function test_accumulate_groups_4_4_all_quarters(): void
    {
        $tokens = ['quarter', 'quarter', 'quarter', 'quarter'];
        $groups = $this->svc->accumulateGroups($tokens, 12);

        $this->assertCount(4, $groups);
        foreach ($groups as $g) {
            $this->assertSame(12, $g['twelfths']);
        }
    }

    /** Half note spans two beats and forms one super-group of 24 twelfths */
    public function test_accumulate_groups_half_note_spans_two_beats(): void
    {
        $tokens = ['half', 'quarter', 'quarter'];
        $groups = $this->svc->accumulateGroups($tokens, 12);

        $this->assertCount(3, $groups);
        $this->assertSame(24, $groups[0]['twelfths']);
        $this->assertSame(['half'], $groups[0]['tokens']);
    }

    /** 6/8: each 18-twelfth beat group detected correctly */
    public function test_accumulate_groups_6_8(): void
    {
        $tokens = ['dotted-quarter', 'eighth', 'eighth', 'eighth'];
        $groups = $this->svc->accumulateGroups($tokens, 18);

        $this->assertCount(2, $groups);
        $this->assertSame(18, $groups[0]['twelfths']);
        $this->assertSame(18, $groups[1]['twelfths']);
    }

    // ── uniquePermutations() ──────────────────────────────────────────────────────

    /** 2 distinct tokens → 2 permutations */
    public function test_unique_permutations_2_distinct(): void
    {
        $perms = $this->svc->uniquePermutations(['quarter', 'eighth']);
        $this->assertCount(2, $perms);
    }

    /** 2 identical tokens → only 1 permutation */
    public function test_unique_permutations_2_identical(): void
    {
        $perms = $this->svc->uniquePermutations(['eighth', 'eighth']);
        $this->assertCount(1, $perms);
    }

    /** 3 distinct tokens → 6 permutations */
    public function test_unique_permutations_3_distinct(): void
    {
        $perms = $this->svc->uniquePermutations(['a', 'b', 'c']);
        $this->assertCount(6, $perms);
    }

    // ── helpers ───────────────────────────────────────────────────────────────────

    private function measureTwelfths(array $tokens): int
    {
        return (int) array_sum(array_map(
            [$this->groupingSvc, 'noteTwelfths'],
            $tokens
        ));
    }
}
