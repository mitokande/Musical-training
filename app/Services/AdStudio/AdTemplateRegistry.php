<?php

namespace App\Services\AdStudio;

/**
 * What the Ad Studio can build, and what an operator may change inside it.
 *
 * A template is a hand-authored, shipped creative that has been tokenized into
 * resources/ad-templates/<key>/. The registry describes only the *editable
 * surface*: the script lines, the option vocabulary, and the hard facts the
 * builder needs (interval spellings, measured press targets). The frame
 * vocabulary, motion and palette discipline live in the stubs and are not
 * negotiable from the panel — that is deliberate, and it is why variants stay
 * on-brand without an operator having to know the design system.
 */
class AdTemplateRegistry
{
    /**
     * Every interval the three-round game can ask, rooted on A4.
     *
     * The root never moves, because scripts/tones.mjs roots every generated tone
     * on A4: keeping it fixed is what makes a difficulty ramp read as the
     * INTERVAL changing rather than the register moving. `upper` is the real
     * spelling, so the staff renderer draws the correct accidental.
     */
    public const INTERVALS = [
        'minor-2nd' => ['label' => 'Minor 2nd', 'semitones' => 1, 'upper' => 'A#4', 'difficulty' => 'hard'],
        'major-2nd' => ['label' => 'Major 2nd', 'semitones' => 2, 'upper' => 'B4', 'difficulty' => 'medium'],
        'minor-3rd' => ['label' => 'Minor 3rd', 'semitones' => 3, 'upper' => 'C5', 'difficulty' => 'medium'],
        'major-3rd' => ['label' => 'Major 3rd', 'semitones' => 4, 'upper' => 'C#5', 'difficulty' => 'medium'],
        'perfect-4th' => ['label' => 'Perfect 4th', 'semitones' => 5, 'upper' => 'D5', 'difficulty' => 'medium'],
        'tritone' => ['label' => 'Tritone', 'semitones' => 6, 'upper' => 'D#5', 'difficulty' => 'hard'],
        'perfect-5th' => ['label' => 'Perfect 5th', 'semitones' => 7, 'upper' => 'E5', 'difficulty' => 'easy'],
        'minor-6th' => ['label' => 'Minor 6th', 'semitones' => 8, 'upper' => 'F5', 'difficulty' => 'hard'],
        'major-6th' => ['label' => 'Major 6th', 'semitones' => 9, 'upper' => 'F#5', 'difficulty' => 'medium'],
        'minor-7th' => ['label' => 'Minor 7th', 'semitones' => 10, 'upper' => 'G5', 'difficulty' => 'medium'],
        'major-7th' => ['label' => 'Major 7th', 'semitones' => 11, 'upper' => 'G#5', 'difficulty' => 'hard'],
        'octave' => ['label' => 'Octave', 'semitones' => 12, 'upper' => 'A5', 'difficulty' => 'easy'],
    ];

    /**
     * The captured product screens available to the studio, and — where one has
     * been MEASURED — the press target inside the phone frame.
     *
     * `press_target` is in the phone's padding-box coordinates (604x792 outer,
     * 8px border, so 588x776 inner) and was measured off a zoom snapshot of the
     * capture, not guessed. A screen without one renders with no cursor at all:
     * the studio will not point at a control it cannot locate.
     */
    public const SHOTS = [
        'practice-melodic-interval.png' => [
            'label' => 'Melodic interval practice',
            'alt' => 'Melodic interval practice screen',
            // The screen's real "Play Interval" button.
            'press_target' => ['x' => 185, 'bottom' => 10, 'w' => 218, 'h' => 71],
        ],
        'practice-chord.png' => ['label' => 'Chord practice', 'alt' => 'Chord practice screen', 'press_target' => null],
        'practice-scale.png' => ['label' => 'Scale practice', 'alt' => 'Scale practice screen', 'press_target' => null],
        'practice-rhythm.png' => ['label' => 'Rhythm practice', 'alt' => 'Rhythm practice screen', 'press_target' => null],
        'practice-single-note.png' => ['label' => 'Single note practice', 'alt' => 'Single note practice screen', 'press_target' => null],
        'practice-dictation.png' => ['label' => 'Melodic dictation', 'alt' => 'Melodic dictation practice screen', 'press_target' => null],
    ];

    /** @return array<string, array> keyed by template key */
    public function all(): array
    {
        return [
            'tiktok-rounds' => $this->tiktokRounds(),
        ];
    }

    public function get(string $key): array
    {
        $all = $this->all();

        if (! isset($all[$key])) {
            throw new AdStudioException("Unknown ad template [$key].");
        }

        return $all[$key];
    }

    public function exists(string $key): bool
    {
        return isset($this->all()[$key]);
    }

    /** Template keys => human labels, for a select box. */
    public function options(): array
    {
        return array_map(fn ($t) => $t['label'], $this->all());
    }

    /**
     * Variant C — the three-round escalating quiz.
     *
     * Shipped as hyperframes/videos/harmoniva-tiktok-rounds; that project is the
     * asset donor (fonts, captured screens, music bed, favicon) so a generated
     * creative inherits the brand without re-crawling the site.
     */
    private function tiktokRounds(): array
    {
        return [
            'key' => 'tiktok-rounds',
            'label' => 'Three-round quiz (variant C)',
            'blurb' => 'A game the viewer plays: three escalating interval rounds with a live scorecard, a locked final score, and a comment-bait close that loops back into the hook.',
            'aspect' => '9:16',
            'width' => 1080,
            'height' => 1920,
            // The target is what the planner aims for; the ceiling is what the
            // feed will actually take. A script needing 31s gets a 31s cut
            // rather than one squeezed until its answer beats vanish.
            'target_duration' => 30.0,
            'max_duration' => 34.0,
            'rounds' => 3,

            // Asset donor. Copied, never referenced in place, so a creative keeps
            // rendering even if the donor project is edited later.
            'source_project' => 'hyperframes/videos/harmoniva-tiktok-rounds',
            'assets' => [
                'dirs' => ['assets/fonts', 'assets/shots'],
                'files' => ['assets/favicon.svg', 'assets/audio/bgm-bed.wav'],
            ],

            /*
             * The script. `frame` ties a line to the frame whose window is cut to
             * its measured duration; `max` is a soft ceiling the editor enforces
             * so a single line cannot eat the whole 30s.
             */
            'lines' => [
                ['key' => 'hook', 'label' => 'Hook', 'frame' => '01-hook', 'max' => 70,
                    'hint' => 'Must be answerable by anyone. Never open on a line that filters the audience — that is what cost variant B 78% of viewers by 0:02.',
                    'default' => 'Three rounds. Most people only get one.'],
                ['key' => 'round1', 'label' => 'Round 1', 'frame' => '02-round1', 'max' => 55,
                    'hint' => 'Brisk. The two notes carry the rest of the frame.',
                    'default' => "Round one. This one's easy."],
                ['key' => 'answer1', 'label' => 'Answer 1', 'frame' => '03-answer1', 'max' => 50,
                    'hint' => 'Flat and fast — a dealer paying out.',
                    'default' => "Octave. That's your one."],
                ['key' => 'round2', 'label' => 'Round 2', 'frame' => '04-round2', 'max' => 55,
                    'hint' => 'Pure function. Get out of the way.',
                    'default' => 'Round two. Listen.'],
                ['key' => 'answer2', 'label' => 'Answer 2', 'frame' => '05-answer2', 'max' => 50,
                    'hint' => 'Slight lift on the question — genuine, not taunting.',
                    'default' => 'Minor third. Still with me?'],
                ['key' => 'round3', 'label' => 'Round 3', 'frame' => '06-round3', 'max' => 60,
                    'hint' => 'Drop half a step in pitch. The warning is real.',
                    'default' => 'Round three. Nobody gets this one.'],
                ['key' => 'answer3', 'label' => 'Answer 3', 'frame' => '07-answer3', 'max' => 50,
                    'hint' => 'Two flat facts and a shrug. Do not gloat.',
                    'default' => 'Tritone. Told you.'],
                ['key' => 'trainable', 'label' => 'The turn', 'frame' => '08-trainable', 'max' => 60,
                    'hint' => 'Dismiss the excuse, then lift on the promise. The first good news the viewer has had.',
                    'default' => "That's not talent. It's trainable."],
                ['key' => 'product', 'label' => 'Product', 'frame' => '09-product', 'max' => 90,
                    'hint' => 'Brisk and concrete. The category list is a rhythm — even beats, no drag on the last.',
                    'default' => 'Harmoniva drills it. Intervals, chords, scales, rhythm.'],
                ['key' => 'cta', 'label' => 'CTA', 'frame' => '10-cta', 'max' => 90,
                    'hint' => 'Warm at last. Ask for the comment first, then land the domain cleanly.',
                    'default' => 'Comment your score. Then train it free at harmoniva dot app.'],
            ],

            /*
             * Everything else the operator can set. `type` drives the editor's
             * control; `group` drives its layout.
             */
            'options' => [
                'kicker' => ['type' => 'text', 'group' => 'Copy', 'label' => 'Kicker', 'default' => 'ear training', 'max' => 24,
                    'hint' => 'Top-left on the hook AND the CTA — the two rails must match for the loop seam to be invisible.'],
                'score_label' => ['type' => 'text', 'group' => 'Copy', 'label' => 'Counter label', 'default' => 'score', 'max' => 10],
                'final_label' => ['type' => 'text', 'group' => 'Copy', 'label' => 'Locked counter label', 'default' => 'final', 'max' => 10],

                'hook_line1' => ['type' => 'text', 'group' => 'Hook', 'label' => 'Hook line 1', 'default' => '3 rounds.', 'max' => 22],
                'hook_line2' => ['type' => 'text', 'group' => 'Hook', 'label' => 'Hook line 2', 'default' => 'most people get', 'max' => 26],
                'hook_slam' => ['type' => 'text', 'group' => 'Hook', 'label' => 'Hook ember word', 'default' => 'one.', 'max' => 12,
                    'hint' => 'Goes in the ember block — the one thing a thumb should catch at feed scale. Keep it to one or two words.'],

                'round1_interval' => ['type' => 'interval', 'group' => 'Rounds', 'label' => 'Round 1 interval', 'default' => 'octave',
                    'hint' => 'Should be winnable by anyone — round 1 buys the point that rounds 2 and 3 spend.'],
                'round2_interval' => ['type' => 'interval', 'group' => 'Rounds', 'label' => 'Round 2 interval', 'default' => 'minor-3rd'],
                'round3_interval' => ['type' => 'interval', 'group' => 'Rounds', 'label' => 'Round 3 interval', 'default' => 'tritone',
                    'hint' => 'The one almost nobody gets cold. If your audience is more trained than expected, this is the knob to turn.'],
                'countdown_label' => ['type' => 'text', 'group' => 'Rounds', 'label' => 'Countdown label', 'default' => 'answer in your head', 'max' => 28],
                'countdown_label_final' => ['type' => 'text', 'group' => 'Rounds', 'label' => 'Final countdown label', 'default' => 'quick', 'max' => 28],

                'answer_aside_2' => ['type' => 'text', 'group' => 'Answers', 'label' => 'Answer 2 aside', 'default' => 'still with me?', 'max' => 24,
                    'hint' => 'Serif counter-voice. Leave blank to hide it entirely.'],
                'answer_aside_3' => ['type' => 'text', 'group' => 'Answers', 'label' => 'Answer 3 aside', 'default' => 'told you.', 'max' => 24],

                'turn_eyebrow' => ['type' => 'text', 'group' => 'The turn', 'label' => 'Eyebrow', 'default' => 'the actual news', 'max' => 24],
                'turn_struck' => ['type' => 'text', 'group' => 'The turn', 'label' => 'Struck-through claim', 'default' => "that's not talent.", 'max' => 34],
                'turn_slam' => ['type' => 'text', 'group' => 'The turn', 'label' => 'Ember payoff', 'default' => "it's trainable.", 'max' => 26],

                'brand' => ['type' => 'text', 'group' => 'Product', 'label' => 'Brand line', 'default' => 'harmoniva', 'max' => 20],
                'categories' => ['type' => 'csv', 'group' => 'Product', 'label' => 'Categories', 'default' => 'intervals, chords, scales, rhythm', 'max_items' => 5,
                    'hint' => 'One per beat of the product VO line. Keep the count equal to the number of things the voice actually lists.'],
                'phone_shot' => ['type' => 'shot', 'group' => 'Product', 'label' => 'Phone screen', 'default' => 'practice-melodic-interval.png',
                    'hint' => 'Only the melodic-interval screen has a measured tap target; others render without a cursor.'],
                'strip_shots' => ['type' => 'shots', 'group' => 'Product', 'label' => 'Strip screens', 'max_items' => 3,
                    'default' => ['practice-chord.png', 'practice-rhythm.png', 'practice-dictation.png']],

                'cta_line1' => ['type' => 'text', 'group' => 'CTA', 'label' => 'CTA line 1', 'default' => 'comment', 'max' => 18],
                'cta_line2' => ['type' => 'text', 'group' => 'CTA', 'label' => 'CTA line 2', 'default' => 'your score.', 'max' => 20],
                'cta_note' => ['type' => 'text', 'group' => 'CTA', 'label' => 'CTA aside', 'default' => 'beat me?', 'max' => 20],
                'cta_domain' => ['type' => 'text', 'group' => 'CTA', 'label' => 'Domain', 'default' => 'harmoniva.app', 'max' => 26],
                'cta_terms' => ['type' => 'text', 'group' => 'CTA', 'label' => 'Terms line', 'default' => 'start free — no card · 15-day premium trial', 'max' => 60,
                    'hint' => 'Must stay factually accurate — this is the offer, not a headline.'],

                'accent' => ['type' => 'color', 'group' => 'Palette', 'label' => 'Accent', 'default' => '#9333EA'],
                'ember' => ['type' => 'color', 'group' => 'Palette', 'label' => 'Ember (ignition)', 'default' => '#F97316',
                    'hint' => 'Used on exactly two words plus the countdown rules. Widening its use breaks the system.'],

                'voice' => ['type' => 'voice', 'group' => 'Voice', 'label' => 'Gemini voice', 'default' => 'Kore'],
                'voice_direction' => ['type' => 'textarea', 'group' => 'Voice', 'label' => 'Delivery direction', 'max' => 600,
                    'default' => 'Read in a bright, confident female voice at a fast, clipped pace — short sentences land as separate hits, punch the period, no flowing paragraph. Game-show host energy held at conversational volume: she is running a quiz, keeping score, a little smug about it — never sneering, never an announcer, never salesy.'],
            ],

            /*
             * Per-round countdown lengths. The escalation is felt as pressure
             * rather than stated, and it is the template's, not the operator's —
             * a flat clock would flatten the whole arc.
             */
            'countdowns' => [0.90, 0.62, 0.36],

            // Music bed volume and the SFX mix, carried from the shipped variant.
            'mix' => [
                'vo' => 1.0,
                'question' => 0.85,
                'answer' => 0.30,
                'sting' => 0.42,
                'ping' => 0.5,
                'bgm' => 0.72,
            ],
        ];
    }

    /** Interval keys => labels, for a select box. */
    public function intervalOptions(): array
    {
        return array_map(fn ($i) => $i['label'], self::INTERVALS);
    }

    public function interval(string $key): array
    {
        if (! isset(self::INTERVALS[$key])) {
            throw new AdStudioException("Unknown interval [$key].");
        }

        return self::INTERVALS[$key] + ['key' => $key];
    }

    /** Shot filenames => labels, for a select box. */
    public function shotOptions(): array
    {
        return array_map(fn ($s) => $s['label'], self::SHOTS);
    }

    public function shot(string $file): array
    {
        if (! isset(self::SHOTS[$file])) {
            throw new AdStudioException("Unknown product screen [$file].");
        }

        return self::SHOTS[$file] + ['file' => $file];
    }

    /**
     * The default config for a fresh creative: every line at its shipped copy and
     * every option at its default. This is exactly the shipped variant C, so a
     * new draft renders the known-good cut before anyone edits a word.
     */
    public function defaultConfig(string $templateKey): array
    {
        $template = $this->get($templateKey);

        return [
            'lines' => collect($template['lines'])->mapWithKeys(fn ($l) => [$l['key'] => $l['default']])->all(),
            'options' => collect($template['options'])->mapWithKeys(fn ($o, $k) => [$k => $o['default']])->all(),
        ];
    }
}
