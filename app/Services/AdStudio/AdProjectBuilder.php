<?php

namespace App\Services\AdStudio;

use App\Models\AdCreative;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

/**
 * Materializes a creative into a real, self-contained HyperFrames project.
 *
 * "Self-contained" is the important word. Assets are COPIED from the donor
 * variant rather than referenced in place, so a generated creative keeps
 * rendering byte-identically even if the hand-authored project it descended from
 * is later edited — which it will be, because that is where the next variant
 * gets designed.
 *
 * The output is an ordinary project: an operator can open it in the HyperFrames
 * CLI, snapshot it, and hand-edit it. Nothing about it is locked to this panel.
 */
class AdProjectBuilder
{
    public function __construct(
        private readonly AdTemplateRegistry $registry,
        private readonly AdTimingPlanner $planner,
        private readonly AdStaffRenderer $staff,
    ) {}

    /**
     * Build (or rebuild) the project for a creative.
     *
     * @param  array<string, array>  $vo  the measured take manifest
     * @return array the plan, for storing on the row
     */
    public function build(AdCreative $creative, array $vo): array
    {
        $template = $this->registry->get($creative->template);
        $config = $creative->config;

        $plan = $this->planner->plan($template, $config, $vo);

        $dir = $this->projectDir($creative);
        $this->resetProject($dir);

        $this->copyDonorAssets($template, $dir);
        $this->writeVoiceover($dir, $vo);
        $this->writeTones($dir, $template, $config);
        $this->writeFrames($dir, $template, $config, $plan);
        $this->writeIndex($dir, $creative, $plan);
        $this->writeProjectFiles($dir, $creative, $template, $config, $plan, $vo);

        return $plan;
    }

    public function projectDir(AdCreative $creative): string
    {
        return base_path(config('ad_studio.projects_root').'/ad-'.$creative->slug);
    }

    /** Path relative to base_path(), which is what the row stores. */
    public function relativeProjectDir(AdCreative $creative): string
    {
        return config('ad_studio.projects_root').'/ad-'.$creative->slug;
    }

    /**
     * A rebuild is a clean rebuild: stale frames from a previous config are worse
     * than no frames, because `hyperframes check` would happily pass a project
     * whose index no longer references them while the operator wonders why an
     * edit did nothing. `renders/` survives so an earlier MP4 stays downloadable
     * until the new one replaces it.
     */
    private function resetProject(string $dir): void
    {
        foreach (['compositions', 'assets/audio', 'scripts'] as $sub) {
            File::deleteDirectory("$dir/$sub");
        }

        File::ensureDirectoryExists("$dir/compositions/frames");
        File::ensureDirectoryExists("$dir/assets/audio");
        File::ensureDirectoryExists("$dir/scripts");
        File::ensureDirectoryExists("$dir/renders");
    }

    private function copyDonorAssets(array $template, string $dir): void
    {
        $source = base_path($template['source_project']);

        if (! is_dir($source)) {
            throw new AdStudioException(
                "The asset donor project [{$template['source_project']}] is missing. The studio copies fonts, captured screens and the music bed from it, so it cannot build without it."
            );
        }

        foreach ($template['assets']['dirs'] as $sub) {
            if (! is_dir("$source/$sub")) {
                throw new AdStudioException("The donor project has no [$sub] directory.");
            }

            File::ensureDirectoryExists("$dir/$sub");
            File::copyDirectory("$source/$sub", "$dir/$sub");
        }

        foreach ($template['assets']['files'] as $file) {
            if (! is_file("$source/$file")) {
                throw new AdStudioException("The donor project is missing [$file].");
            }

            File::ensureDirectoryExists(dirname("$dir/$file"));
            File::copy("$source/$file", "$dir/$file");
        }
    }

    /**
     * Copy the cached takes in under stable names and leave the measurements
     * beside them. vo.json is not read by the render — it is there so a human
     * opening the project can see exactly which numbers the windows were cut to.
     */
    private function writeVoiceover(string $dir, array $vo): void
    {
        $manifest = [];

        foreach ($vo as $key => $take) {
            $dest = "$dir/assets/audio/vo-$key.wav";

            if (! is_file($take['path'])) {
                throw new AdStudioException("The narration take for [$key] is missing from the cache. Regenerate the voiceover.");
            }

            File::copy($take['path'], $dest);

            $manifest[] = [
                'line' => $key,
                'text' => $take['text'],
                'voice' => $take['voice'],
                'file' => "assets/audio/vo-$key.wav",
                'seconds' => $take['seconds'],
            ];
        }

        File::put("$dir/assets/audio/vo.json", json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n");
    }

    /**
     * Synthesize the interval tones for whichever intervals this creative asks.
     *
     * This runs the same deterministic additive-synth generator the hand-authored
     * variants use, rather than a PHP re-implementation, so there is exactly one
     * definition of what the quiz sounds like.
     */
    private function writeTones(string $dir, array $template, array $config): void
    {
        $rounds = [];

        for ($n = 1; $n <= $template['rounds']; $n++) {
            $interval = $this->registry->interval((string) data_get($config, "options.round{$n}_interval"));
            $rounds[] = ['slug' => $interval['key'], 'semitones' => $interval['semitones'], 'label' => $interval['label']];
        }

        File::put("$dir/assets/audio/tones.json", json_encode(['rounds' => $rounds], JSON_PRETTY_PRINT)."\n");
        File::copy($this->templatePath($template, 'scripts/tones.mjs'), "$dir/scripts/tones.mjs");

        $result = Process::path($dir)->timeout(180)->run(['node', 'scripts/tones.mjs']);

        if (! $result->successful()) {
            throw new AdStudioException('Could not synthesize the interval tones: '.Str::limit(trim($result->errorOutput() ?: $result->output()), 400));
        }
    }

    private function writeFrames(string $dir, array $template, array $config, array $plan): void
    {
        foreach ($plan['frames'] as $frame) {
            $stub = File::get($this->templatePath($template, "frames/{$frame['stub']}.html"));
            $tokens = $this->tokens($template, $config, $plan, $frame);

            File::put("$dir/compositions/frames/{$frame['id']}.html", $this->substitute($stub, $tokens, $frame['stub']));
        }
    }

    private function writeIndex(string $dir, AdCreative $creative, array $plan): void
    {
        $template = $this->registry->get($creative->template);
        $stub = File::get($this->templatePath($template, 'index.html'));

        $frameClips = collect($plan['frames'])->map(fn ($f) => sprintf(
            <<<'HTML'
      <div
        id="el-%s"
        data-composition-id="%s"
        data-composition-src="compositions/frames/%s.html"
        data-start="%s"
        data-duration="%s"
        data-track-index="1"
        data-width="%d"
        data-height="%d"
      ></div>
HTML,
            $f['prefix'],
            $f['id'],
            $f['id'],
            $this->num($f['start']),
            $this->num($f['duration']),
            $template['width'],
            $template['height'],
        ))->implode("\n\n");

        $audio = collect($plan['audio']);
        $clip = fn (array $a) => sprintf(
            '      <audio id="%s" src="%s" data-start="%s" data-duration="%s" data-track-index="%d" data-volume="%s"></audio>',
            $a['id'], $a['src'], $this->num($a['start']), $this->num($a['duration']), $a['track'], $a['volume']
        );

        File::put("$dir/index.html", $this->substitute($stub, [
            'TITLE' => e($creative->name).' — '.$template['label'],
            'TOTAL' => $this->num($plan['total']),
            'FRAME_CLIPS' => $frameClips,
            'VO_CLIPS' => $audio->filter(fn ($a) => Str::startsWith($a['id'], 'vo-'))->map($clip)->implode("\n"),
            'SFX_CLIPS' => $audio->filter(fn ($a) => Str::startsWith($a['id'], 'sfx-'))->map($clip)->implode("\n"),
            'BGM_CLIP' => $audio->filter(fn ($a) => $a['id'] === 'bgm')->map($clip)->implode("\n"),
        ], 'index.html'));
    }

    /**
     * Token values for one frame: the shared palette and copy, plus whatever that
     * frame kind needs.
     */
    private function tokens(array $template, array $config, array $plan, array $frame): array
    {
        $opt = fn (string $key, $default = '') => data_get($config, "options.$key", $default);

        $accent = (string) $opt('accent', '#9333EA');
        $ember = (string) $opt('ember', '#F97316');

        $tokens = [
            'FRAME_ID' => $frame['id'],
            'P' => $frame['prefix'],
            'F_DUR' => $this->num($frame['duration']),
            'C_ACCENT' => $accent,
            'C_ACCENT_RGB' => $this->rgb($accent),
            'C_EMBER' => $ember,
            'C_EMBER_RGB' => $this->rgb($ember),
            'SCORE_LABEL' => e((string) $opt('score_label', 'score')),
            'FINAL_LABEL' => e((string) $opt('final_label', 'final')),
            'HOOK_KICKER' => e((string) $opt('kicker', '')),
            'PR_BRAND' => e((string) $opt('brand', '')),
        ];

        foreach ($frame['local'] as $key => $value) {
            $tokens[$key] = $this->num($value);
        }

        return $tokens + match ($frame['kind']) {
            'hook' => $this->hookTokens($template, $config),
            'round' => $this->roundTokens($template, $config, $frame),
            'answer' => $this->answerTokens($template, $config, $frame),
            'trainable' => $this->trainableTokens($config),
            'product' => $this->productTokens($config, $frame),
            'cta' => $this->ctaTokens($template, $config),
            default => [],
        };
    }

    private function hookTokens(array $template, array $config): array
    {
        $opt = fn (string $k, $d = '') => (string) data_get($config, "options.$k", $d);

        $line1 = $opt('hook_line1');
        $line2 = $opt('hook_line2');
        $slam = $opt('hook_slam');

        $pips = collect(range(1, $template['rounds']))
            ->map(fn ($i) => '        <div class="hk-pip" id="hk-pip-'.$i.'">'.$i.'</div>')
            ->implode("\n");

        // The rule only reads as "round 1, then the rest" with three or more
        // pips; with fewer it sits after the first anyway and looks deliberate.
        $pips = Str::replaceFirst(
            '</div>',
            "</div>\n        <div id=\"hk-pip-rule\"></div>",
            $pips
        );

        return [
            'HOOK_LINE1' => e($line1),
            'HOOK_LINE2' => e($line2),
            'HOOK_SLAM' => e($slam),
            'SCORE_START' => '0/'.$template['rounds'],
            'HOOK_PIPS' => $pips,
            // Fit-to-measure: size from the actual copy so a longer line steps
            // down instead of running off the frame.
            'HOOK_LINE_SIZE' => (string) $this->fit(max(strlen($line1), strlen($line2)), 152, 92, 15),
            'HOOK_SLAM_SIZE' => (string) $this->fit(strlen($slam), 196, 96, 6),
        ];
    }

    private function roundTokens(array $template, array $config, array $frame): array
    {
        $n = $frame['round'] + 1;
        $interval = $this->registry->interval((string) data_get($config, "options.round{$n}_interval"));
        $isFinal = $n === $template['rounds'];

        $chips = $this->chipLabels($template, $config, $n);

        return [
            'ROUND_LABEL' => e(sprintf('round %02d', $n)),
            'SCORE' => ($n - 1).'/'.$template['rounds'],
            'CLEF_LABEL' => 'treble',
            'STAFF_ARIA' => e($this->staff->aria('A4', $interval['upper'])),
            'NOTE1_SVG' => $this->staff->note("{$frame['prefix']}-note1", 'A4', 320),
            'NOTE2_SVG' => $this->staff->note("{$frame['prefix']}-note2", $interval['upper'], 600),
            'CHIPS' => collect($chips)
                ->map(fn ($c) => '        <div class="chip">'.e($c).'</div>')
                ->implode("\n"),
            'CHIP_SIZE' => (string) $this->fit(max(array_map('strlen', $chips)), 52, 36, 11),
            'COUNTDOWN_LABEL' => e((string) data_get(
                $config,
                $isFinal ? 'options.countdown_label_final' : 'options.countdown_label',
                'answer in your head'
            )),
        ];
    }

    /**
     * The four options for a round: the right answer plus three plausible
     * neighbours.
     *
     * Distractors are drawn from the intervals nearest in semitones, because a
     * quiz whose wrong answers are obviously wrong is not a quiz. The set is
     * shuffled deterministically by round number so a re-render is identical but
     * the correct answer is not always in the same corner.
     */
    private function chipLabels(array $template, array $config, int $n): array
    {
        $correct = $this->registry->interval((string) data_get($config, "options.round{$n}_interval"));

        $neighbours = collect(AdTemplateRegistry::INTERVALS)
            ->map(fn ($i, $key) => $i + ['key' => $key])
            ->reject(fn ($i) => $i['key'] === $correct['key'])
            ->sortBy(fn ($i) => abs($i['semitones'] - $correct['semitones']))
            ->take(3)
            ->pluck('label')
            ->all();

        $options = array_merge([$correct['label']], $neighbours);

        // Deterministic placement: seeded by the round, so the answer moves
        // around the grid across rounds but never between renders.
        $slot = ($correct['semitones'] + $n) % 4;
        $answer = array_shift($options);
        array_splice($options, $slot, 0, [$answer]);

        return $options;
    }

    private function answerTokens(array $template, array $config, array $frame): array
    {
        $n = $frame['round'] + 1;
        $interval = $this->registry->interval((string) data_get($config, "options.round{$n}_interval"));
        $aside = trim((string) data_get($config, "options.answer_aside_$n", ''));
        $word = Str::lower($interval['label']).'.';

        $score = [
            'SCORE_OLD' => ($n - 1).'/'.$template['rounds'],
            'SCORE_NEW' => $n.'/'.$template['rounds'],
            // The final counter LOCKS rather than ticking: the viewer's score is
            // whatever they had going into the last round.
            'SCORE_FINAL' => ($n - 1).'/'.$template['rounds'],
        ];

        return $score + [
            'ROUND_LABEL' => e(sprintf('round %02d — answer', $n)),
            'ANSWER_WORD' => e($word),
            'ANSWER_PITCHES' => e(sprintf('A4 → %s · %d semitones', $interval['upper'], $interval['semitones'])),
            'ANSWER_CHIP' => e($interval['label']),
            'ANSWER_ASIDE' => e($aside),
            'ASIDE_DISPLAY' => $aside === '' ? 'none' : 'block',
            'WORD_SIZE' => (string) $this->fit(strlen($word), 178, 104, 8),
        ];
    }

    private function trainableTokens(array $config): array
    {
        $opt = fn (string $k) => (string) data_get($config, "options.$k", '');

        $struck = $opt('turn_struck');
        $slam = $opt('turn_slam');
        $words = $this->planner->words($struck);

        return [
            'TR_EYEBROW' => e($opt('turn_eyebrow')),
            'TR_WORDS' => collect($words)
                ->map(fn ($w) => '          <span class="tr-word">'.e($w).'</span>')
                ->implode("\n"),
            'TR_SLAM' => e($slam),
            'TR_STRUCK_SIZE' => (string) $this->fit(strlen($struck), 112, 74, 18),
            'TR_SLAM_SIZE' => (string) $this->fit(strlen($slam), 178, 104, 15),
        ];
    }

    private function productTokens(array $config, array $frame): array
    {
        $cats = $this->planner->categories($config);
        $phone = $this->registry->shot((string) data_get($config, 'options.phone_shot'));
        $strip = (array) data_get($config, 'options.strip_shots', []);

        $target = $phone['press_target'];
        $hasTap = $target !== null;

        return [
            'PR_CATS' => collect($cats)
                ->map(fn ($c) => '        <div class="pr-cat">'.e($c).'</div>')
                ->implode("\n"),
            'PR_CAT_SIZE' => (string) $this->fit(array_sum(array_map('strlen', $cats)) + count($cats) * 3, 30, 19, 34),
            'PR_PHONE_SHOT' => $phone['file'],
            'PR_PHONE_ALT' => e($phone['alt']),
            'PR_THUMBS' => collect($strip)->map(function ($file) {
                $shot = $this->registry->shot($file);

                return '        <div class="pr-thumb"><img src="assets/shots/'.$shot['file'].'" alt="'.e($shot['alt']).'" data-layout-allow-overflow /></div>';
            })->implode("\n"),

            // With no measured target the whole tap apparatus is display:none and
            // its timeline is omitted — no cursor pointing at nothing.
            'PR_TAP_DISPLAY' => $hasTap ? 'block' : 'none',
            'PR_HIT_X' => (string) ($target['x'] ?? 0),
            'PR_HIT_BOTTOM' => (string) ($target['bottom'] ?? 0),
            'PR_HIT_W' => (string) ($target['w'] ?? 0),
            'PR_HIT_H' => (string) ($target['h'] ?? 0),
            'PR_HIT_CX' => (string) (int) (($target['x'] ?? 0) + ($target['w'] ?? 0) / 2),
            // The phone's padding box is 776px tall (792 outer - 2x8px border).
            'PR_HIT_CY' => (string) (int) (776 - ($target['bottom'] ?? 0) - ($target['h'] ?? 0) / 2),
            'PR_TAP_TL' => $hasTap ? $this->tapTimeline($frame, $target) : '      // No measured press target on this screen — the tap is omitted.',
        ];
    }

    /**
     * The cursor travel and press, written only when there is a real control to
     * press. The offsets put the arrow's TIP (about 6,3 inside its 46x46 box) on
     * the button centre.
     */
    private function tapTimeline(array $frame, array $target): string
    {
        $cx = (int) ($target['x'] + $target['w'] / 2);
        $cy = (int) (776 - $target['bottom'] - $target['h'] / 2);
        $dx = $cx - 420 - 6;
        $dy = $cy - 300 - 3;

        $at = $this->num($frame['local']['PR_TAP_AT']);
        $cursorAt = $this->num($frame['local']['PR_CURSOR_AT']);
        $travel = $this->num(max(0.2, $frame['local']['PR_TAP_AT'] - $frame['local']['PR_CURSOR_AT']));

        return <<<JS
      // cursor-click-ripple. The pointer travels to a REAL control measured off
      // the capture and presses on the ping, so the sound reads as the app
      // responding rather than as a generic UI blip.
      tl.set("#pr-cursor", { opacity: 1 }, $cursorAt);
      tl.fromTo("#pr-cursor", { x: 0, y: 0 },
        { x: $dx, y: $dy, duration: $travel, ease: "power2.inOut" }, $cursorAt);
      tl.to("#pr-cursor", { scale: 0.82, duration: 0.08, ease: "power2.in", transformOrigin: "left top" }, $at);
      tl.to("#pr-cursor", { scale: 1, duration: 0.18, ease: "back.out(3)", transformOrigin: "left top" }, $at + 0.08);

      // The control lights under the press, then releases. Cream, because the
      // button underneath is already brand-coloured.
      tl.to("#pr-hit", { backgroundColor: "rgba(250,247,242,0.3)", borderColor: "rgba(250,247,242,1)", duration: 0.1, ease: "none" }, $at);
      tl.to("#pr-hit", { backgroundColor: "rgba(250,247,242,0.08)", duration: 0.5, ease: "power2.out" }, $at + 0.14);

      // The ripple expands with an attack-decay envelope.
      tl.set("#pr-ripple", { opacity: 0.85, scale: 0.4 }, $at);
      tl.to("#pr-ripple", { scale: 3.2, opacity: 0, duration: 0.62, ease: "power2.out", transformOrigin: "center center" }, $at);
JS;
    }

    private function ctaTokens(array $template, array $config): array
    {
        $opt = fn (string $k, $d = '') => (string) data_get($config, "options.$k", $d);

        $line1 = $opt('cta_line1');
        $line2 = $opt('cta_line2');
        $note = trim($opt('cta_note'));
        $domain = $opt('cta_domain');

        $check = '<svg class="ct-mark-svg" viewBox="0 0 46 46" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Round %d correct">'
            .'<path d="M9 25 L19 35 L37 11" stroke="#FAF7F2" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" /></svg>';
        $cross = '<svg class="ct-mark-svg" viewBox="0 0 46 46" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Round %d missed">'
            .'<path d="M13 13 L33 33 M33 13 L13 33" stroke="#0C0A10" stroke-width="6" stroke-linecap="round" /></svg>';

        // Marks are DRAWN, not typed: the brand mono subset carries no U+2713 or
        // U+2715 and a missing glyph renders as a tofu box in the final MP4.
        $pips = collect(range(1, $template['rounds']))->map(function ($i) use ($template, $check, $cross) {
            $missed = $i === $template['rounds'];

            return '          <div class="ct-pip'.($missed ? ' ct-pip-miss' : '').'" id="ct-pip-'.$i.'">'."\n"
                .'            '.sprintf($missed ? $cross : $check, $i)."\n"
                .'          </div>';
        })->implode("\n");

        return [
            'CTA_LINE1' => e($line1),
            'CTA_LINE2' => e($line2),
            'CTA_LINE_SIZE' => (string) $this->fit(max(strlen($line1), strlen($line2)), 138, 88, 12),
            'CTA_PIPS' => $pips,
            'CTA_NOTE' => e($note),
            'CTA_NOTE_DISPLAY' => $note === '' ? 'none' : 'block',
            'CTA_DOMAIN' => e($domain),
            'CTA_DOMAIN_SIZE' => (string) $this->fit(strlen($domain), 66, 40, 14),
            'CTA_TERMS' => e($opt('cta_terms')),
            'SCORE_FINAL' => ($template['rounds'] - 1).'/'.$template['rounds'],
        ];
    }

    /** BRIEF / SCRIPT / project metadata — the paper trail a HyperFrames project carries. */
    private function writeProjectFiles(string $dir, AdCreative $creative, array $template, array $config, array $plan, array $vo): void
    {
        $version = config('ad_studio.cli.version');

        File::put("$dir/package.json", json_encode([
            'name' => 'ad-'.$creative->slug,
            'private' => true,
            'type' => 'module',
            'scripts' => [
                'dev' => "npx --yes hyperframes@$version preview",
                'check' => "npx --yes hyperframes@$version check",
                'render' => "npx --yes hyperframes@$version render",
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");

        File::put("$dir/meta.json", json_encode([
            'id' => 'ad-'.$creative->slug,
            'name' => $creative->name,
            'createdAt' => $creative->created_at?->toIso8601String() ?? now()->toIso8601String(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");

        File::put("$dir/hyperframes.json", json_encode([
            '$schema' => 'https://hyperframes.heygen.com/schema/hyperframes.json',
            'registry' => 'https://raw.githubusercontent.com/heygen-com/hyperframes/main/registry',
            'paths' => ['blocks' => 'compositions', 'components' => 'compositions/components', 'assets' => 'assets'],
            'media' => ['autoProxy' => true],
            'authoringSkill' => 'product-launch-video',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");

        File::put("$dir/BRIEF.md", $this->brief($creative, $template, $config, $plan));
        File::put("$dir/SCRIPT.md", $this->script($creative, $template, $config, $vo, $plan));
    }

    private function brief(AdCreative $creative, array $template, array $config, array $plan): string
    {
        $opt = fn (string $k, $d = '') => (string) data_get($config, "options.$k", $d);

        $rounds = collect(range(1, $template['rounds']))->map(function ($n) use ($config) {
            $i = $this->registry->interval((string) data_get($config, "options.round{$n}_interval"));

            return sprintf('- Round %d — **%s** (A4 → %s, %d semitones)', $n, $i['label'], $i['upper'], $i['semitones']);
        })->implode("\n");

        $warnings = $plan['warnings']
            ? "\n## Planner notes\n\n".collect($plan['warnings'])->map(fn ($w) => "- $w")->implode("\n")."\n"
            : '';

        return <<<MD
        ---
        workflow: product-launch-video
        flow: automation
        storyboard: no
        template: {$creative->template}
        destination: tiktok
        aspect: {$template['aspect']}
        length: {$this->num($plan['total'])}s
        language: en
        voice: gemini/{$opt('voice', 'Kore')}
        generated_by: admin Ad Studio
        ---

        # {$creative->name}

        **GENERATED FILE.** This project was built by the admin Ad Studio from the
        `{$creative->template}` template. Hand edits here are lost on the next rebuild —
        change the creative in the panel instead, or copy this directory out of
        `{$this->relativeProjectDir($creative)}` to fork it by hand.

        ## The cut

        {$template['blurb']}

        $rounds

        Frame windows are cut to the **measured** Gemini TTS durations in
        `assets/audio/vo.json`. The script's natural length was {$this->num($plan['natural'])}s
        against a {$this->num($template['target_duration'])}s target, and the planner
        reconciled the difference across the frames that had slack.

        ## Inherited constraints

        These come from variant B's measured Reels curve (100% → 22% by 0:02, flat
        behind it) and are baked into the template rather than left to the operator:

        - Frame 1 is fully composed at t=0 — it is the poster frame the feed shows.
        - Frame 1 sits on a saturated ground; near-black reads as "not loaded yet".
        - The opening line must be answerable by anyone.
        - No caption track: every spoken line is already on screen at poster scale.
        $warnings
        MD;
    }

    private function script(AdCreative $creative, array $template, array $config, array $vo, array $plan): string
    {
        $voice = (string) data_get($config, 'options.voice', 'Kore');
        $direction = (string) data_get($config, 'options.voice_direction', '');
        $starts = collect($plan['frames'])->keyBy('line');

        $lines = collect($template['lines'])->map(function ($line, $i) use ($config, $vo, $starts) {
            $key = $line['key'];
            $text = (string) data_get($config, "lines.$key", '');
            $seconds = $vo[$key]['seconds'] ?? 0;
            $frame = $starts[$key] ?? null;
            $window = $frame
                ? sprintf('%ss – %ss', $this->num($frame['start']), $this->num($frame['start'] + $frame['duration']))
                : 'n/a';

            return sprintf(
                "## Line %d — %s (%s)\n\n**Window:** %s · **measured take:** %.2fs\n**Delivery:** %s\n\n    %s\n",
                $i + 1, $line['label'], $line['frame'], $window, $seconds, $line['hint'], $text
            );
        })->implode("\n");

        return <<<MD
        # SCRIPT — {$creative->name}

        **GENERATED FILE** — edit the creative in the admin Ad Studio, not here.

        **Voice:** {$voice} (Google Gemini TTS, `{$this->cliSafe(config('ad_studio.tts.model'))}`)
        **Voice direction:** {$direction}

        Durations below are measured from the actual takes, silence-trimmed. They are
        the numbers the frame windows were cut to.

        ---

        {$lines}
        MD;
    }

    /**
     * Replace {{TOKEN}} placeholders and fail loudly on any that survive.
     *
     * An unreplaced token would ship a literal "{{FOO}}" into a rendered frame,
     * where it is a cosmetic bug nobody notices until it is on TikTok. Better to
     * refuse to build.
     */
    private function substitute(string $stub, array $tokens, string $label): string
    {
        $out = str_replace(
            array_map(fn ($k) => '{{'.$k.'}}', array_keys($tokens)),
            array_values($tokens),
            $stub
        );

        if (preg_match_all('/\{\{([A-Z0-9_]+)\}\}/', $out, $m)) {
            $missing = implode(', ', array_unique($m[1]));

            throw new AdStudioException("The [$label] template has unfilled placeholders: $missing. This is a template bug, not a content problem.");
        }

        return $out;
    }

    /**
     * Fit-to-measure. Display type is sized from the copy's length so a longer
     * headline steps down instead of running off the frame — the preset's rule,
     * applied automatically because an operator should not have to know it.
     */
    private function fit(int $length, int $max, int $min, int $comfortable): int
    {
        if ($length <= $comfortable) {
            return $max;
        }

        return (int) max($min, round($max * $comfortable / $length));
    }

    private function templatePath(array $template, string $file): string
    {
        $path = base_path(config('ad_studio.templates_root')."/{$template['key']}/$file");

        if (! is_file($path)) {
            throw new AdStudioException("Missing template stub [{$template['key']}/$file].");
        }

        return $path;
    }

    /** "#9333EA" => "147,51,234", for the rgba() glows that must track the accent. */
    private function rgb(string $hex): string
    {
        $hex = ltrim(trim($hex), '#');

        if (! preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
            throw new AdStudioException("[$hex] is not a 6-digit hex colour.");
        }

        return implode(',', [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))]);
    }

    /** Times are written into JS and HTML attributes; keep them locale-proof. */
    private function num(float|int $value): string
    {
        return rtrim(rtrim(number_format((float) $value, 3, '.', ''), '0'), '.') ?: '0';
    }

    private function cliSafe(?string $value): string
    {
        return (string) $value;
    }
}
