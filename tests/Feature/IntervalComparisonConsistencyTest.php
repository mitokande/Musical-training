<?php

namespace Tests\Feature;

use App\Http\Controllers\AIController;
use App\Services\MusicTheoryService;
use ReflectionMethod;
use Tests\TestCase;

class IntervalComparisonConsistencyTest extends TestCase
{
    private MusicTheoryService $music;

    protected function setUp(): void
    {
        parent::setUp();
        $this->music = new MusicTheoryService();
    }

    private function sanitize(array $questions): array
    {
        $method = new ReflectionMethod(AIController::class, 'sanitizeAIQuestions');
        $method->setAccessible(true);
        return $method->invoke(app(AIController::class), $questions);
    }

    public function test_sanitized_interval_comparison_target_matches_rendered_size(): void
    {
        // Mix of pairs whose second note's letter sits below the first (the bug
        // case), normal pairs, and one equal-size pair (should be dropped).
        $input = [
            ['type' => 'interval-comparison', 'interval_a' => 'A,C', 'interval_b' => 'C,E', 'target' => 'b', 'octave' => '4'],
            ['type' => 'interval-comparison', 'interval_a' => 'B,D', 'interval_b' => 'C,E', 'target' => 'b', 'octave' => '4'],
            ['type' => 'interval-comparison', 'interval_a' => 'C,E', 'interval_b' => 'C,G', 'target' => 'a', 'octave' => '4'],
            ['type' => 'interval-comparison', 'interval_a' => 'C,E', 'interval_b' => 'F,A', 'target' => 'a', 'octave' => '4'], // tie
        ];

        $out = $this->sanitize($input);

        // The equal-size pair is unanswerable and must be dropped.
        $this->assertCount(3, $out, 'Equal-size interval-comparison question should be dropped');

        foreach ($out as $q) {
            $expected = $this->music->largerIntervalPair($q['interval_a'], $q['interval_b']);
            $this->assertNotNull($expected);
            $this->assertSame(
                $expected,
                $q['target'],
                "target '{$q['target']}' disagrees with same-octave size for {$q['interval_a']} vs {$q['interval_b']}"
            );
        }

        // The reported bug: A,C (6th) vs C,E (3rd) → A is larger.
        $this->assertSame('a', $out[0]['target']);
        $this->assertSame('a', $out[1]['target']);
    }
}
