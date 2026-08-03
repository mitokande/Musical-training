<?php

namespace Tests\Unit;

use App\Services\AdStudio\AdStudioException;
use App\Services\AdStudio\AdTemplateRegistry;
use App\Services\AdStudio\AdTimingPlanner;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The Ad Studio's timing planner is the one piece of the studio that can silently
 * produce a broken video: everything else either works or throws. A plan that is
 * two centiseconds short leaves a black frame at a cut; one that starts a tone
 * under its own narration muddies the question the whole spot is asking.
 *
 * These tests pin the three rules the planner promises, across scripts that are
 * comfortable, tight, over-long and unusually short.
 */
class AdTimingPlannerTest extends TestCase
{
    private AdTimingPlanner $planner;

    private array $template;

    private array $config;

    protected function setUp(): void
    {
        parent::setUp();

        $registry = new AdTemplateRegistry;
        $this->planner = new AdTimingPlanner($registry);
        $this->template = $registry->get('tiktok-rounds');
        $this->config = $registry->defaultConfig('tiktok-rounds');
    }

    /** The measured takes from the shipped variant C — a script known to work. */
    private function shippedTakes(): array
    {
        return $this->takes([
            'hook' => 2.47, 'round1' => 1.79, 'answer1' => 1.57, 'round2' => 2.02,
            'answer2' => 1.81, 'round3' => 2.55, 'answer3' => 1.80,
            'trainable' => 2.58, 'product' => 4.08, 'cta' => 3.73,
        ]);
    }

    private function takes(array $seconds): array
    {
        return collect($seconds)->map(fn ($s) => ['seconds' => $s])->all();
    }

    /** Scale every take by a factor, to simulate a longer or shorter script. */
    private function scaled(float $factor): array
    {
        return collect($this->shippedTakes())
            ->map(fn ($t) => ['seconds' => round($t['seconds'] * $factor, 3)])
            ->all();
    }

    #[Test]
    public function frame_windows_tile_the_total_exactly(): void
    {
        foreach ([0.8, 0.95, 1.0, 1.05] as $factor) {
            $plan = $this->planner->plan($this->template, $this->config, $this->scaled($factor));

            $sum = round(array_sum(array_column($plan['frames'], 'duration')), 6);

            $this->assertSame(
                round($plan['total'], 6),
                $sum,
                "Frame durations must sum to the planned total (factor $factor)."
            );

            // And they must be contiguous: a gap is a black frame mid-cut.
            $cursor = 0.0;
            foreach ($plan['frames'] as $frame) {
                $this->assertEqualsWithDelta($cursor, $frame['start'], 0.0001, "Frame {$frame['id']} does not start where the previous one ended.");
                $cursor = round($cursor + $frame['duration'], 6);
            }
        }
    }

    #[Test]
    public function every_narration_clip_fits_inside_its_own_frame(): void
    {
        foreach ([0.8, 0.95, 1.0, 1.05] as $factor) {
            $takes = $this->scaled($factor);
            $plan = $this->planner->plan($this->template, $this->config, $takes);
            $frames = collect($plan['frames'])->keyBy('line');

            foreach ($plan['audio'] as $clip) {
                if (! str_starts_with($clip['id'], 'vo-')) {
                    continue;
                }

                $line = substr($clip['id'], 3);
                $frame = $frames[$line];
                $frameEnd = round($frame['start'] + $frame['duration'], 3);

                $this->assertGreaterThanOrEqual(
                    $frame['start'] - 0.0001,
                    $clip['start'],
                    "Narration for [$line] starts before its frame."
                );

                $this->assertLessThanOrEqual(
                    $frameEnd + 0.0001,
                    round($clip['start'] + $clip['duration'], 3),
                    "Narration for [$line] runs past its own hard cut (factor $factor)."
                );
            }
        }
    }

    #[Test]
    public function quiz_tones_always_sound_after_their_rounds_narration(): void
    {
        foreach ([0.8, 1.0, 1.05] as $factor) {
            $takes = $this->scaled($factor);
            $plan = $this->planner->plan($this->template, $this->config, $takes);
            $audio = collect($plan['audio'])->keyBy('id');

            for ($n = 1; $n <= 3; $n++) {
                $vo = $audio["vo-round$n"];
                $tone = $audio["sfx-q$n"];

                $this->assertGreaterThanOrEqual(
                    round($vo['start'] + $vo['duration'], 3) - 0.0001,
                    $tone['start'],
                    "Round $n's tones sound under its own narration (factor $factor). The voice sets the question; the notes are the question."
                );
            }
        }
    }

    #[Test]
    public function the_answer_beat_never_disappears(): void
    {
        // Squeeze hard enough that the reconciler pushes every round to its floor.
        $plan = $this->planner->plan($this->template, $this->config, $this->scaled(1.05));

        foreach ($plan['frames'] as $frame) {
            if ($frame['kind'] !== 'round') {
                continue;
            }

            $this->assertGreaterThanOrEqual(
                0.22 - 0.0001,
                $frame['local']['COUNTDOWN_DUR'],
                "Round {$frame['id']} lost its answer beat — that is the frame where the viewer commits."
            );

            $this->assertLessThanOrEqual(
                round($frame['duration'], 3) + 0.0001,
                round($frame['local']['COUNTDOWN_AT'] + $frame['local']['COUNTDOWN_DUR'], 3),
                "Round {$frame['id']}'s countdown runs past the cut."
            );
        }
    }

    #[Test]
    public function the_countdown_ramp_survives_reconciliation(): void
    {
        // The escalation (0.90 → 0.62 → 0.36) is the arc of the game. A plan may
        // shorten the clock but must never flatten or invert the ramp.
        $plan = $this->planner->plan($this->template, $this->config, $this->shippedTakes());

        $countdowns = collect($plan['frames'])
            ->where('kind', 'round')
            ->pluck('local.COUNTDOWN_DUR')
            ->values()
            ->all();

        $this->assertGreaterThanOrEqual($countdowns[1], $countdowns[0], 'Round 1 must give more time than round 2.');
        $this->assertGreaterThanOrEqual($countdowns[2], $countdowns[1], 'Round 2 must give more time than round 3.');
    }

    #[Test]
    public function a_comfortable_script_lands_on_the_target(): void
    {
        $plan = $this->planner->plan($this->template, $this->config, $this->scaled(0.9));

        $this->assertSame(30.0, $plan['total']);
        $this->assertSame([], $plan['warnings']);
    }

    #[Test]
    public function a_slightly_long_script_runs_long_rather_than_being_crushed(): void
    {
        // The target is a preference, not a law: the feed does not care about a
        // second, and the pacing does.
        $plan = $this->planner->plan($this->template, $this->config, $this->scaled(1.05));

        $this->assertGreaterThan(30.0, $plan['total']);
        $this->assertLessThanOrEqual($this->template['max_duration'], $plan['total']);
        $this->assertNotEmpty($plan['warnings'], 'Running over the target should be reported, not silent.');
    }

    #[Test]
    public function a_script_past_the_ceiling_is_refused_with_an_actionable_message(): void
    {
        $this->expectException(AdStudioException::class);
        $this->expectExceptionMessageMatches('/Trim about [\d.]+s of spoken text/');

        $this->planner->plan($this->template, $this->config, $this->scaled(1.6));
    }

    #[Test]
    public function a_very_short_script_is_padded_on_the_end_card_and_flagged(): void
    {
        $plan = $this->planner->plan($this->template, $this->config, $this->scaled(0.45));

        $this->assertSame(30.0, $plan['total'], 'A short script still fills the target.');
        $this->assertNotEmpty($plan['warnings']);

        // The slack goes where holding is free — the held end card, not the
        // answer beats, which are meant to be the shortest frames in the cut.
        $frames = collect($plan['frames'])->keyBy('id');
        $this->assertGreaterThan(
            $frames['03-answer1']['duration'],
            $frames['10-cta']['duration'],
            'Padding belongs on the end card, not on the answer beats.'
        );
    }

    #[Test]
    public function the_tap_ping_is_omitted_when_the_screen_has_no_measured_target(): void
    {
        $config = $this->config;
        $config['options']['phone_shot'] = 'practice-scale.png';

        $plan = $this->planner->plan($this->template, $config, $this->shippedTakes());

        $this->assertNull(
            collect($plan['audio'])->firstWhere('id', 'sfx-correct'),
            'A ping with no cursor is a sound with nothing behind it.'
        );
    }

    #[Test]
    public function every_audio_clip_has_a_positive_duration(): void
    {
        foreach ([0.5, 0.9, 1.0, 1.05] as $factor) {
            $plan = $this->planner->plan($this->template, $this->config, $this->scaled($factor));

            foreach ($plan['audio'] as $clip) {
                $this->assertGreaterThan(
                    0.0,
                    $clip['duration'],
                    "Clip [{$clip['id']}] has a non-positive duration at factor $factor."
                );
            }
        }
    }
}
