<?php

namespace App\Services\AdStudio;

/**
 * Turns measured narration durations into a cut.
 *
 * This is the studio's actual engineering content. Everything else moves files
 * around; this decides when each frame opens, how long its answer beat runs, and
 * where every note, ping and music cue lands — from the real length of the takes
 * Gemini returned, so a rewritten script still produces a cut that holds
 * together instead of one with dead air in it.
 *
 * Three rules the plan never breaks:
 *
 *   1. A line's audio always fits inside its own frame's window. Narration
 *      spilling across a hard cut is the one artefact that reads as broken.
 *   2. The quiz tones always sound AFTER their round's narration. The voice sets
 *      the question; the notes are the question.
 *   3. Frame windows sum to the target duration exactly. Platforms cut hard at
 *      their ceiling and a 30.2s upload loses its last beat.
 */
class AdTimingPlanner
{
    /** How long after a frame opens its narration starts. */
    private const LEAD = 0.06;

    /** Minimum breath between the end of narration and the next hard cut. */
    private const TAIL = 0.08;

    /** The two quiz tones in every q-*.wav are this far apart (scripts/tones.mjs). */
    private const MELODIC_GAP = 0.85;

    /** Float slop, so an exact fit is not rejected by a rounding hair. */
    private const EPSILON = 0.002;

    /** Track layout, carried from the hand-authored variants. */
    private const TRACK_VO = 10;

    private const TRACK_QUESTION = 11;

    private const TRACK_BGM = 12;

    private const TRACK_ANSWER = 13;

    private const TRACK_STING = 14;

    public function __construct(private readonly AdTemplateRegistry $registry) {}

    /**
     * @param  array<string, array{seconds: float}>  $vo  measured takes, keyed by line key
     * @return array{total: float, natural: float, frames: list<array>, audio: list<array>, warnings: list<string>}
     */
    public function plan(array $template, array $config, array $vo): array
    {
        $target = (float) $template['target_duration'];
        $frames = $this->naturalFrames($template, $config, $vo);

        $ceiling = (float) $template['max_duration'];
        $natural = round(array_sum(array_column($frames, 'duration')), 3);
        $warnings = [];

        if ($natural > $target) {
            // Reclaim what the frames genuinely have — never more. Most of a
            // round frame is its tones and its answer beat, not slack, so the
            // real capacity is small and squeezing past it would clip the voice.
            $capacity = round($this->capacity($frames), 3);
            $reclaim = min($natural - $target, $capacity);
            $total = round($natural - $reclaim, 2);

            if ($total > $ceiling + self::EPSILON) {
                throw new AdStudioException($this->tooLongMessage($total, $ceiling, $capacity));
            }

            if ($total > $target + 0.05) {
                $warnings[] = sprintf(
                    'This script needs more room than the %.0fs target, so the cut runs %.1fs. That is within the %.0fs ceiling and will upload fine — trim a few words if you want it landing on %.0fs exactly.',
                    $target, $total, $ceiling, $target
                );
            }

            $frames = $this->shrink($frames, $reclaim);
        } else {
            $total = $target;
            $delta = round($target - $natural, 3);

            if ($delta > $target * 0.18) {
                $warnings[] = sprintf(
                    'The script only fills %.1fs of a %.0fs target, so %.1fs was added to held frames — mostly the end card. It will cut cleanly but will feel slower than the shipped variant.',
                    $natural, $target, $delta
                );
            }

            $frames = $this->grow($frames, $delta);
        }

        $frames = $this->assignStarts($frames, $total);

        return [
            'total' => $total,
            'natural' => $natural,
            'frames' => array_map(fn ($f) => $this->withLocals($f, $config), $frames),
            'audio' => $this->audio($template, $config, $vo, $frames, $total),
            'warnings' => $warnings,
        ];
    }

    /**
     * Every frame at its natural length: the length its own content wants, before
     * any reconciliation against the target.
     *
     * `min` is how far it can be squeezed before it stops working — narration
     * would clip, or an answer beat would vanish. The reconciler never goes below
     * it, which is why an over-long script fails loudly instead of silently
     * producing a cut with a truncated voiceover.
     */
    private function naturalFrames(array $template, array $config, array $vo): array
    {
        $frames = [];
        $seconds = fn (string $key) => (float) ($vo[$key]['seconds'] ?? 0.0);

        // --- 1 · Hook ------------------------------------------------------
        $hook = $seconds('hook');
        $frames[] = [
            'id' => '01-hook', 'kind' => 'hook', 'stub' => 'hook', 'line' => 'hook', 'prefix' => 'hk',
            'vo' => $hook,
            'duration' => max(self::LEAD + $hook + self::TAIL, 2.2),
            'min' => self::LEAD + $hook + 0.04,
            'grow_weight' => 0.5,
        ];

        // --- 2/4/6 · Rounds and 3/5/7 · Answers ----------------------------
        for ($r = 0; $r < $template['rounds']; $r++) {
            $n = $r + 1;
            $roundVo = $seconds("round$n");
            $countdown = (float) $template['countdowns'][$r];

            // Rule 2: the notes sound after the narration, never under it. The
            // 1.5s floor is the staff draw plus the chip deal — the options must
            // be on screen before the viewer is asked to choose between them.
            $note1 = max(self::LEAD + $roundVo + 0.06, 1.5);
            $note2 = $note1 + self::MELODIC_GAP;
            $countdownAt = $note2 + 0.02;

            $frames[] = [
                'id' => sprintf('%02d-round%d', $n * 2, $n), 'kind' => 'round', 'stub' => 'round',
                'line' => "round$n", 'prefix' => "r$n", 'round' => $r,
                'vo' => $roundVo,
                'note1' => $note1, 'note2' => $note2, 'countdown_at' => $countdownAt, 'countdown' => $countdown,
                'duration' => $countdownAt + $countdown + 0.12,
                // The answer beat may compress to a fifth of a second, but it may
                // never disappear: it is the frame where the viewer commits.
                'min' => $countdownAt + 0.22 + 0.08,
                'grow_weight' => 0.8,
            ];

            $answerVo = $seconds("answer$n");
            $isFinal = $n === $template['rounds'];
            $aside = trim((string) data_get($config, "options.answer_aside_$n", ''));

            $tickAt = 0.52;
            $asideAt = 0.92;

            $frames[] = [
                'id' => sprintf('%02d-answer%d', $n * 2 + 1, $n), 'kind' => 'answer',
                'stub' => $isFinal ? 'answer-final' : 'answer',
                'line' => "answer$n", 'prefix' => "a$n", 'round' => $r, 'final' => $isFinal,
                'vo' => $answerVo,
                'tick_at' => $tickAt, 'aside_at' => $asideAt, 'has_aside' => $aside !== '',
                'duration' => max(
                    0.02 + $answerVo + 0.06,
                    $tickAt + 0.62,
                    $aside !== '' ? $asideAt + 0.50 : 0.0,
                ),
                'min' => max(0.02 + $answerVo + 0.04, $tickAt + 0.46),
                // Answer frames are the shortest in the cut on purpose — the
                // answer is never the point, the counter is. They grow last.
                'grow_weight' => 0.2,
            ];
        }

        // --- 8 · The turn --------------------------------------------------
        $turn = $seconds('trainable');
        $slamAt = $this->clamp(self::LEAD + $turn * 0.55, 0.66, max(0.7, self::LEAD + $turn - 0.45));
        $frames[] = [
            'id' => '08-trainable', 'kind' => 'trainable', 'stub' => 'trainable', 'line' => 'trainable', 'prefix' => 'tr',
            'vo' => $turn, 'slam_at' => $slamAt,
            'duration' => max(self::LEAD + $turn + self::TAIL, $slamAt + 1.30),
            'min' => max(self::LEAD + $turn + 0.04, $slamAt + 0.95),
            'grow_weight' => 0.7,
        ];

        // --- 9 · Product ---------------------------------------------------
        $product = $seconds('product');
        $cats = max(1, count($this->categories($config)));
        // The list occupies roughly the back half of the line, so the categories
        // light there rather than under the brand name at the front.
        $catsAt = self::LEAD + $product * 0.42;
        $catsStep = ($product * 0.50) / $cats;
        $catsEnd = $catsAt + ($cats - 1) * $catsStep + 0.32;
        $tapAt = $catsAt + 0.35;
        $stripAt = max($catsEnd - 0.60, $tapAt + 0.40, 2.10);

        $frames[] = [
            'id' => '09-product', 'kind' => 'product', 'stub' => 'product', 'line' => 'product', 'prefix' => 'pr',
            'vo' => $product,
            'cats_at' => $catsAt, 'cats_step' => $catsStep, 'tap_at' => $tapAt, 'strip_at' => $stripAt,
            'duration' => max(self::LEAD + $product + self::TAIL, $stripAt + 0.95, 3.4),
            'min' => max(self::LEAD + $product + 0.04, $stripAt + 0.70),
            'grow_weight' => 0.6,
        ];

        // --- 10 · CTA ------------------------------------------------------
        $cta = $seconds('cta');
        $domainAt = $this->clamp(self::LEAD + $cta * 0.55, 1.45, max(1.5, self::LEAD + $cta - 0.55));
        $termsAt = $domainAt + 0.53;
        $holdAt = $termsAt + 0.40;

        $frames[] = [
            'id' => '10-cta', 'kind' => 'cta', 'stub' => 'cta', 'line' => 'cta', 'prefix' => 'ct',
            'vo' => $cta, 'domain_at' => $domainAt, 'terms_at' => $termsAt, 'hold_at' => $holdAt,
            'duration' => max(self::LEAD + $cta + 0.14, $holdAt + 1.00, 3.2),
            'min' => max(self::LEAD + $cta + 0.10, $holdAt + 0.50),
            // The end card is the natural home for slack: it is held anyway, and
            // a longer hold means a paused viewer sits on the domain longer.
            'grow_weight' => 2.5,
        ];

        return $frames;
    }

    /** Distribute surplus time, weighted so it lands where holding is free. */
    private function grow(array $frames, float $delta): array
    {
        if ($delta <= 0.0005) {
            return $frames;
        }

        $totalWeight = array_sum(array_column($frames, 'grow_weight'));

        foreach ($frames as $i => $frame) {
            $frames[$i]['duration'] += $delta * ($frame['grow_weight'] / $totalWeight);
        }

        return $frames;
    }

    /**
     * Reclaim time proportionally to each frame's *available* slack, so the
     * squeeze comes out of frames that have room rather than evenly out of all
     * of them. Never crosses a frame's `min`.
     */
    private function shrink(array $frames, float $delta): array
    {
        $slack = array_map(fn ($f) => max(0.0, $f['duration'] - $f['min']), $frames);
        $available = array_sum($slack);

        if ($available <= 0.0) {
            return $frames;
        }

        // Never reclaim more than exists; plan() has already refused the cases
        // where that would matter, so this is a floor, not a policy.
        $delta = min($delta, $available);

        foreach ($frames as $i => $frame) {
            $frames[$i]['duration'] -= $delta * ($slack[$i] / $available);
        }

        return $frames;
    }

    /**
     * Round to whole centiseconds and lay the frames end to end.
     *
     * Rounding is applied to the running edge rather than to each duration, so
     * accumulated rounding error cannot push the total off the target — the last
     * frame absorbs whatever is left and the sum is exact by construction.
     */
    private function assignStarts(array $frames, float $target): array
    {
        $cursor = 0.0;
        $last = count($frames) - 1;

        foreach ($frames as $i => $frame) {
            $start = round($cursor, 2);
            $end = $i === $last ? $target : round($cursor + $frame['duration'], 2);

            $frames[$i]['start'] = $start;
            $frames[$i]['duration'] = round($end - $start, 2);

            $cursor = $end;
        }

        return $frames;
    }

    /**
     * Expand a frame's final duration into the local motion times its stub needs.
     *
     * Everything here is derived, never authored twice: the stub holds the
     * choreography, this holds when it happens.
     */
    private function withLocals(array $frame, array $config): array
    {
        $d = $frame['duration'];

        $frame['local'] = match ($frame['kind']) {
            'hook' => $this->hookLocals($frame, $d),
            'round' => $this->roundLocals($frame, $d),
            'answer' => $this->answerLocals($frame, $d),
            'trainable' => $this->trainableLocals($frame, $d, $config),
            'product' => $this->productLocals($frame, $d, $config),
            'cta' => $this->ctaLocals($frame, $d),
            default => [],
        };

        return $frame;
    }

    private function hookLocals(array $f, float $d): array
    {
        $slamAt = $this->clamp(self::LEAD + $f['vo'] * 0.5, 0.9, max(0.9, self::LEAD + $f['vo'] - 0.45));
        $handoffAt = max($slamAt + 0.5, $d - 0.40);
        $breatheAt = min($slamAt + 0.31, max($slamAt + 0.26, $handoffAt - 0.5));

        return [
            'HOOK_SLAM_AT' => $slamAt,
            'HOOK_PIP_AT' => 0.08,
            // Three pulses, evenly spread across the run-up to the punch.
            'HOOK_PIP_STEP' => $this->clamp(($slamAt - 0.30) / 3, 0.18, 0.42),
            'HOOK_RULE_AT' => 0.20,
            'HOOK_RULE_DUR' => max(0.35, $slamAt - 0.25),
            'HOOK_BREATHE_AT' => $breatheAt,
            'HOOK_BREATHE_DUR' => $this->clamp(($handoffAt - $breatheAt) / 2, 0.15, 0.40),
            'HOOK_HANDOFF_AT' => $handoffAt,
        ];
    }

    private function roundLocals(array $f, float $d): array
    {
        $r = $f['round'];
        $note1 = $f['note1'];
        $countdownAt = $f['countdown_at'];

        // The clock keeps the template's shape (0.90 → 0.62 → 0.36) unless the
        // window genuinely cannot hold it. Flattening the ramp would flatten the
        // escalation, which is the whole arc of the game.
        $countdownDur = $this->clamp($d - 0.12 - $countdownAt, 0.22, $f['countdown']);

        // Setup beats tighten with each round: the game speeds up and never says so.
        $staffAt = 0.22 - $r * 0.03;
        $staffDur = 0.34 - $r * 0.04;
        $staffStep = 0.05 - $r * 0.005;
        $chipsAt = $staffAt + $staffDur + 4 * $staffStep - 0.10;
        $chipsStep = 0.07 - $r * 0.007;
        $chipsDur = 0.40 - $r * 0.03;
        $chipsEnd = $chipsAt + 3 * $chipsStep + $chipsDur;

        return [
            'LABEL_DUR' => 0.26 - $r * 0.03,
            'STAFF_AT' => $staffAt,
            'STAFF_DUR' => $staffDur,
            'STAFF_STEP' => $staffStep,
            'CHIPS_AT' => $chipsAt,
            'CHIPS_STEP' => $chipsStep,
            'CHIPS_DUR' => $chipsDur,
            // The listen cue: after the options are on screen, before the tone.
            'BLOOM_AT' => $this->clamp($note1 - 0.45, $chipsEnd + 0.05, max($chipsEnd + 0.05, $note1 - 0.1)),
            'NOTE1_AT' => $note1,
            'NOTE2_AT' => $f['note2'],
            'COUNTDOWN_LABEL_AT' => max(0.0, $countdownAt - 0.02),
            'COUNTDOWN_AT' => $countdownAt,
            'COUNTDOWN_DUR' => $countdownDur,
            'TIGHTEN_AT' => max($countdownAt, $d - 0.16),
            'TIGHTEN_DUR' => min(0.16, max(0.08, $d - max($countdownAt, $d - 0.16))),
        ];
    }

    private function answerLocals(array $f, float $d): array
    {
        $outAt = max($f['tick_at'] + 0.46, $d - 0.35);

        return [
            'WORD_AT' => 0.10,
            'PITCH_AT' => 0.38,
            'TICK_AT' => $f['tick_at'],
            'ASIDE_AT' => $f['aside_at'],
            'OUT_AT' => $outAt,
            'OUT_DUR' => max(0.12, $d - $outAt),
        ];
    }

    private function trainableLocals(array $f, float $d, array $config): array
    {
        $slamAt = $f['slam_at'];
        $strikeAt = max(0.30, $slamAt - 0.34);
        $words = max(1, count($this->words((string) data_get($config, 'options.turn_struck', ''))));
        $breatheAt = min($slamAt + 0.92, max($slamAt + 0.55, $d - 0.5));

        return [
            'TR_WORDS_AT' => 0.06,
            'TR_WORDS_STEP' => $this->clamp(($strikeAt - 0.20) / max(1, $words), 0.06, 0.16),
            'TR_STRIKE_AT' => $strikeAt,
            'TR_SLAM_AT' => $slamAt,
            'TR_BREATHE_AT' => $breatheAt,
            'TR_BREATHE_DUR' => $this->clamp(($d - $breatheAt) / 2, 0.18, 0.55),
        ];
    }

    private function productLocals(array $f, float $d, array $config): array
    {
        $catsAt = $f['cats_at'];

        return [
            // Visible-but-dim from before the first hit, so the row never
            // changes width mid-frame.
            'PR_CATS_VISIBLE_AT' => max(0.0, $catsAt - 0.35),
            'PR_CATS_AT' => $catsAt,
            'PR_CATS_STEP' => $f['cats_step'],
            'PR_TAP_AT' => $f['tap_at'],
            'PR_CURSOR_AT' => max(0.0, $f['tap_at'] - 0.68),
            'PR_STRIP_AT' => min($f['strip_at'], max(0.0, $d - 0.80)),
            'PR_PUSH_AT' => max(0.5, $d - 0.35),
        ];
    }

    private function ctaLocals(array $f, float $d): array
    {
        $domainAt = min($f['domain_at'], max(1.0, $d - 1.35));
        $termsAt = $domainAt + 0.53;
        $holdAt = min($termsAt + 0.40, max($termsAt + 0.2, $d - 0.5));

        return [
            'CTA_LINE1_AT' => 0.18,
            'CTA_LINE2_AT' => 0.52,
            'CTA_PIPS_AT' => min(0.90, max(0.86, $domainAt - 0.62)),
            'CTA_NOTE_AT' => min(1.32, max(1.24, $domainAt - 0.20)),
            'CTA_DOMAIN_AT' => $domainAt,
            'CTA_TERMS_AT' => $termsAt,
            'CTA_HOLD_AT' => $holdAt,
            'CTA_HOLD_DUR' => $this->clamp(($d - $holdAt) / 2, 0.20, 0.75),
        ];
    }

    /**
     * Every audio cue at its global time.
     *
     * Rule 1 is enforced here rather than trusted: each clip's duration is capped
     * so it cannot outlive the frame that owns it, with a small decay allowance
     * for the tone beds that are *meant* to ring under the next cut.
     */
    private function audio(array $template, array $config, array $vo, array $frames, float $total): array
    {
        $mix = $template['mix'];
        $byId = collect($frames)->keyBy('id');
        $clips = [];

        foreach ($frames as $frame) {
            $line = $frame['line'];

            if (! isset($vo[$line])) {
                continue;
            }

            $lead = $frame['kind'] === 'answer' ? 0.02 : self::LEAD;

            $clips[] = [
                'id' => 'vo-'.$line,
                'src' => "assets/audio/vo-$line.wav",
                'start' => round($frame['start'] + $lead, 2),
                'duration' => round(min($vo[$line]['seconds'], $frame['duration'] - $lead), 2),
                'track' => self::TRACK_VO,
                'volume' => $mix['vo'],
                'label' => 'Narration — '.$line,
            ];
        }

        for ($r = 0; $r < $template['rounds']; $r++) {
            $n = $r + 1;
            $round = $byId[sprintf('%02d-round%d', $n * 2, $n)];
            $answer = $byId[sprintf('%02d-answer%d', $n * 2 + 1, $n)];
            $slug = $this->toneSlug((string) data_get($config, "options.round{$n}_interval"));

            // The question. Allowed to bleed a little past the cut — that tail is
            // the tone decaying, and the answer frame's dyad rings under it.
            $qStart = $round['start'] + $round['note1'];
            $clips[] = [
                'id' => "sfx-q$n",
                'src' => "assets/audio/q-$slug.wav",
                'start' => round($qStart, 2),
                'duration' => round(min(2.6, ($round['start'] + $round['duration']) - $qStart + 0.35), 2),
                'track' => self::TRACK_QUESTION,
                'volume' => $mix['question'],
                'label' => "Round $n question tones",
            ];

            // The answer, as one colour. Own track, because it deliberately
            // overlaps the question's decay.
            $clips[] = [
                'id' => "sfx-a$n",
                'src' => "assets/audio/r-$slug.wav",
                'start' => round($answer['start'] + 0.02, 2),
                'duration' => round(min(2.2, $answer['duration']), 2),
                'track' => self::TRACK_ANSWER,
                'volume' => $mix['answer'],
                'label' => "Round $n answer dyad",
            ];

            // The final verdict gets the low sting under it.
            if (! empty($answer['final'])) {
                $clips[] = [
                    'id' => 'sfx-sting',
                    'src' => 'assets/audio/sting.wav',
                    'start' => round($answer['start'] + 0.02, 2),
                    'duration' => round(min(1.8, $answer['duration']), 2),
                    'track' => self::TRACK_STING,
                    'volume' => $mix['sting'],
                    'label' => 'Final verdict sting',
                ];
            }
        }

        // The tap ping only exists when the chosen screen has a measured press
        // target — no target means no cursor, so a ping would sound at nothing.
        $product = $byId['09-product'];
        if ($this->hasPressTarget($config)) {
            $clips[] = [
                'id' => 'sfx-correct',
                'src' => 'assets/audio/correct.wav',
                'start' => round($product['start'] + $product['tap_at'], 2),
                'duration' => round(min(1.3, $product['duration'] - $product['tap_at']), 2),
                'track' => self::TRACK_QUESTION,
                'volume' => $mix['ping'],
                'label' => 'Tap ping',
            ];
        }

        $clips[] = [
            'id' => 'bgm',
            'src' => 'assets/audio/bgm-bed.wav',
            'start' => 0.0,
            'duration' => $total,
            'track' => self::TRACK_BGM,
            'volume' => $mix['bgm'],
            'label' => 'Music bed',
        ];

        return $clips;
    }

    private function hasPressTarget(array $config): bool
    {
        $shot = (string) data_get($config, 'options.phone_shot');

        return isset(AdTemplateRegistry::SHOTS[$shot])
            && AdTemplateRegistry::SHOTS[$shot]['press_target'] !== null;
    }

    /** scripts/tones.mjs names its files by the interval key. */
    private function toneSlug(string $intervalKey): string
    {
        return $this->registry->interval($intervalKey)['key'];
    }

    /** @return list<string> */
    public function categories(array $config): array
    {
        $raw = data_get($config, 'options.categories', '');
        $items = is_array($raw) ? $raw : explode(',', (string) $raw);

        return array_values(array_filter(array_map('trim', $items), fn ($v) => $v !== ''));
    }

    /** @return list<string> */
    public function words(string $text): array
    {
        return array_values(array_filter(preg_split('/\s+/', trim($text)) ?: []));
    }

    private function clamp(float $value, float $min, float $max): float
    {
        if ($max < $min) {
            return $min;
        }

        return max($min, min($max, $value));
    }

    /**
     * Total reclaimable time across the cut.
     *
     * Most of a round frame is not slack: the two tones are a fixed 0.85s apart
     * and the answer beat cannot vanish, so roughly 3.6s of a three-round cut is
     * structural. That is why the narration budget is well under the target
     * duration, and why an over-long script has to be trimmed rather than
     * squeezed.
     */
    public function capacity(array $frames): float
    {
        return array_sum(array_map(fn ($f) => max(0.0, $f['duration'] - $f['min']), $frames));
    }

    /**
     * Roughly how much narration this template can carry, for the editor's
     * pre-flight estimate. Derived, not guessed: the target minus the beats the
     * template structurally spends outside the voice.
     */
    public function narrationBudget(array $template): float
    {
        $perRound = self::MELODIC_GAP + 0.06 + 0.12;
        $structural = $template['rounds'] * ($perRound + array_sum($template['countdowns']) / max(1, count($template['countdowns'])))
            + count($template['lines']) * (self::LEAD + self::TAIL);

        return round($template['target_duration'] - $structural, 1);
    }

    private function tooLongMessage(float $total, float $ceiling, float $capacity): string
    {
        return sprintf(
            'This script runs too long even after squeezing: the cut would be %.1fs against a %.0fs ceiling. Only %.1fs can be reclaimed without clipping the voice or losing an answer beat — the quiz tones and countdowns are fixed. Trim about %.1fs of spoken text; the answer lines are usually the cheapest to shorten.',
            $total, $ceiling, $capacity, $total - $ceiling
        );
    }
}
