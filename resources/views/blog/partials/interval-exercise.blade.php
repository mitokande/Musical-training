{{--
    In-article interval exercise box.

    Params:
      $exId       unique DOM id fragment
      $mode       'melodic' (notes one after another) | 'harmonic' (together)
      $questions  BlogExerciseBuilder payload — 5 questions, each carrying MIDI
                  numbers for playback and VexFlow keys for the staff

    Everything is client-side on purpose: a blog post is read by logged-out
    visitors, so grading here means no session, no daily-quota burn, and no
    reload between questions. Answering the last question reveals the answer and
    locks the box behind the signup panel, which by design has no dismiss
    control — it stays until the reader reloads the page.

    The staff follows the practice screens exactly: the first note is drawn on
    its own, and the second appears only once the reader has committed to an
    answer, so the notation never gives the ear exercise away.

    Height is capped at 400px so an exercise never breaks the reading rhythm.
--}}
@php
    $exQuestions = array_values($questions ?? []);
    $exTitle = $mode === 'harmonic' ? __('blog.ui.exercise.harmonic_title') : __('blog.ui.exercise.melodic_title');
    $exHint = $mode === 'harmonic' ? __('blog.ui.exercise.harmonic_hint') : __('blog.ui.exercise.melodic_hint');

    // Strings the exercise script builds at runtime, published once for the
    // page. Encoded here rather than with @json inside the script tag: the
    // directive takes a single-line expression and silently truncates a
    // multi-line array literal.
    $exerciseStrings = json_encode([
        'play' => __('blog.ui.exercise.play'),
        'replay' => __('blog.ui.exercise.replay'),
        'playing' => __('blog.ui.exercise.playing'),
        'loading' => __('blog.ui.exercise.loading'),
        'play_prompt' => __('blog.ui.exercise.play_prompt'),
        'answer_prompt' => __('blog.ui.exercise.answer_prompt'),
        'replay_prompt' => __('blog.ui.exercise.replay_prompt'),
        'audio_error' => __('blog.ui.exercise.audio_error'),
        'question' => __('blog.ui.exercise.question'),
        'correct' => __('blog.ui.exercise.correct'),
        'incorrect' => __('blog.ui.exercise.incorrect'),
        'score' => __('blog.ui.exercise.score'),
    ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

    // Canonical interval name -> displayed label. The payload keeps the
    // canonical names (that is what the answer is compared against); only the
    // button text and the feedback line are localised, exactly as
    // music_label() does for chords and scales elsewhere on the site.
    $exerciseLabels = json_encode(
        (array) __('blog.ui.intervals'),
        JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    );
@endphp

@if (! empty($exQuestions))
<div class="reveal my-10"
     x-data="hvIntervalExercise(@js($exQuestions), @js($mode), @js($exId))"
     x-cloak
     id="ex-{{ $exId }}">
    <div class="hv-ex">
        {{-- Header --}}
        <div class="hv-ex-head">
            <span class="hv-ex-badge">
                <i data-lucide="headphones" class="w-3 h-3"></i>
                {{ __('blog.ui.exercise.badge') }}
            </span>
            <div class="hv-ex-titles">
                <p class="hv-ex-title">{{ $exTitle }}</p>
                <p class="hv-ex-hint">{{ $exHint }}</p>
            </div>
            <span class="hv-ex-count" x-text="countLabel"></span>
        </div>

        {{-- Progress --}}
        <div class="hv-ex-dots" aria-hidden="true">
            @foreach ($exQuestions as $i => $q)
                <span class="hv-ex-dot" :class="dotClass({{ $i }})"></span>
            @endforeach
        </div>

        <div x-show="!gated" class="hv-ex-body">
            {{-- Staff --}}
            <div class="hv-ex-stave" id="stave-{{ $exId }}" aria-hidden="true"></div>

            {{-- Transport on the left third, answers on the right two thirds.
                 The play button becomes Next once an answer is in, so the box
                 never shows two competing calls to action. --}}
            <div class="hv-ex-play-answers">
                <div class="hv-ex-transport">
                    <button type="button" class="hv-ex-btn hv-ex-btn-play"
                            x-show="!answered"
                            :disabled="playing"
                            @click="play()">
                        <svg viewBox="0 0 24 24" width="17" height="17" fill="currentColor" aria-hidden="true" x-show="!playing"><path d="M8 5.14v13.72L19 12z"/></svg>
                        <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" x-show="playing" x-cloak><path d="M11 5 6 9H2v6h4l5 4z"/><path d="M15.5 8.5a5 5 0 0 1 0 7"/><path d="M19 5a9 9 0 0 1 0 14"/></svg>
                        <span x-text="playing ? strings.playing : playLabel"></span>
                    </button>
                    <button type="button" class="hv-ex-btn hv-ex-btn-next"
                            x-show="answered && !isLast" x-cloak
                            @click="next()">
                        <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
                        {{ __('blog.ui.exercise.next') }}
                    </button>
                    <p class="hv-ex-status" :class="answered ? (correct ? 'is-correct' : 'is-wrong') : ''" x-text="answered ? feedback : status"></p>
                </div>

                <div class="hv-ex-answers">
                    <template x-for="(option, oi) in current.options" :key="oi">
                        <button type="button"
                                class="hv-ex-answer"
                                :class="answerClass(option)"
                                :disabled="answered"
                                @click="choose(option)"
                                x-text="label(option)"></button>
                    </template>
                </div>
            </div>
        </div>

        {{-- Signup gate: shown once the last question has been answered, and
             deliberately has no way back — it stays until the page reloads. --}}
        <div x-show="gated" x-cloak class="hv-ex-gate">
            <p class="hv-ex-gate-answer" :class="correct ? 'is-correct' : 'is-wrong'" x-text="feedback"></p>
            <p class="hv-ex-gate-score" x-text="scoreLabel"></p>
            <p class="hv-ex-gate-title">{{ __('blog.ui.exercise.gate_title') }}</p>
            <p class="hv-ex-gate-body">{{ __('blog.ui.exercise.gate_body') }}</p>
            <div class="hv-ex-gate-actions">
                <a href="{{ route('register') }}" class="hv-ex-cta">{{ __('blog.ui.exercise.gate_button') }}</a>
                <a href="{{ locale_url('/learn') }}" class="hv-ex-cta-ghost">{{ __('blog.ui.exercise.gate_secondary') }}</a>
            </div>
        </div>
    </div>
</div>

@once
<style>
    /* Exercise box — scoped to the blog article, deliberately its own look
       rather than the practice-page card, and hard-capped at 400px tall. */
    .hv-ex {
        max-height: 400px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 16px;
        border-radius: 20px;
        background: linear-gradient(140deg, #ffffff 0%, #faf5ff 55%, #fff7ed 100%);
        border: 1px solid #e9d5ff;
        box-shadow: 0 12px 34px -22px rgba(88, 28, 135, .8);
    }
    /* Badge and counter hold the edges; the title block is centred between
       them regardless of how wide either one gets in another language. */
    .hv-ex-head { display: grid; grid-template-columns: auto 1fr auto; align-items: center; gap: 10px; }
    .hv-ex-badge {
        display: inline-flex; align-items: center; gap: 4px; flex-shrink: 0;
        padding: 3px 9px; border-radius: 999px;
        background: #9333ea; color: #fff;
        font-size: 10px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase;
    }
    .hv-ex-titles { min-width: 0; text-align: center; }
    .hv-ex-title { font-size: 13px; font-weight: 700; color: #1f2937; line-height: 1.25; }
    .hv-ex-hint { font-size: 9.5px; color: #8b93a1; line-height: 1.35; margin-top: 2px; }
    .hv-ex-count {
        flex-shrink: 0; font-size: 10.5px; font-weight: 800; color: #7c3aed;
        text-transform: uppercase; letter-spacing: .05em; white-space: nowrap;
    }

    .hv-ex-dots { display: flex; gap: 5px; }
    .hv-ex-dot { height: 4px; flex: 1; border-radius: 999px; background: #e9d5ff; transition: background .25s ease; }
    .hv-ex-dot.is-done { background: #22c55e; }
    .hv-ex-dot.is-missed { background: #f87171; }
    .hv-ex-dot.is-current { background: #9333ea; }

    .hv-ex-body { display: flex; flex-direction: column; gap: 10px; }
    /* The stave keeps a fixed box so revealing the second note cannot make the
       article jump under the reader. */
    .hv-ex-stave {
        height: 120px; overflow: hidden;
        display: flex; align-items: center; justify-content: center;
        background: #fff; border: 1px solid #f1e9fd; border-radius: 14px;
    }
    .hv-ex-stave svg { max-width: 100%; height: auto; }

    /* Transport on the left third, answer grid on the right two thirds. */
    .hv-ex-play-answers { display: grid; grid-template-columns: 1fr 2fr; gap: 12px; align-items: start; }
    .hv-ex-transport { display: flex; flex-direction: column; gap: 6px; min-width: 0; }
    .hv-ex-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 7px;
        padding: 18px 14px; border-radius: 12px; border: none; cursor: pointer;
        font-size: 13px; font-weight: 700; line-height: 1.15; text-align: center;
        transition: all .15s ease;
    }
    .hv-ex-btn-play {
        color: #fff;
        background: linear-gradient(135deg, #6d28d9 0%, #7c3aed 100%);
        box-shadow: 0 8px 18px -10px rgba(124, 58, 237, .95);
    }
    .hv-ex-btn-play:hover:not(:disabled) { background: linear-gradient(135deg, #5b21b6 0%, #6d28d9 100%); }
    .hv-ex-btn-play:disabled { cursor: default; opacity: .8; }
    .hv-ex-btn-next { color: #1d4ed8; background: #dbeafe; border: 2px solid #93c5fd; padding: 16px 12px; }
    .hv-ex-btn-next:hover { background: #bfdbfe; border-color: #60a5fa; }
    .hv-ex-status { font-size: 10px; color: #9aa1ad; line-height: 1.35; text-align: center; }
    .hv-ex-status.is-correct { color: #15803d; font-weight: 700; font-size: 11px; }
    .hv-ex-status.is-wrong { color: #b91c1c; font-weight: 700; font-size: 11px; }

    .hv-ex-answers { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 6px; align-content: start; }
    .hv-ex-answer {
        padding: 9px 6px; border-radius: 12px; border: 1px solid #e5e7eb; background: #fff;
        font-size: 12.5px; font-weight: 600; color: #374151; cursor: pointer;
        transition: all .15s ease;
    }
    .hv-ex-answer:hover:not(:disabled) { border-color: #c084fc; background: #faf5ff; }
    .hv-ex-answer:disabled { cursor: default; }
    .hv-ex-answer.is-correct { border-color: #22c55e; background: #f0fdf4; color: #15803d; }
    .hv-ex-answer.is-wrong { border-color: #ef4444; background: #fef2f2; color: #b91c1c; }
    .hv-ex-answer.is-dim { opacity: .45; }

    .hv-ex-gate { text-align: center; display: flex; flex-direction: column; gap: 4px; padding: 6px 0 2px; }
    .hv-ex-gate-answer { font-size: 13px; font-weight: 700; }
    .hv-ex-gate-answer.is-correct { color: #15803d; }
    .hv-ex-gate-answer.is-wrong { color: #b91c1c; }
    .hv-ex-gate-score { font-size: 11.5px; color: #6b7280; }
    .hv-ex-gate-title { font-size: 14px; font-weight: 700; color: #1f2937; margin-top: 4px; }
    .hv-ex-gate-body { font-size: 11px; color: #6b7280; line-height: 1.5; max-width: 460px; margin: 0 auto; }
    .hv-ex-gate-actions { display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; margin-top: 8px; }
    .hv-ex-cta {
        padding: 9px 20px; border-radius: 11px; text-decoration: none;
        background: linear-gradient(135deg, #f97316, #ea580c); color: #fff;
        font-size: 12.5px; font-weight: 700;
    }
    .hv-ex-cta-ghost {
        padding: 9px 20px; border-radius: 11px; text-decoration: none;
        border: 1px solid #d8b4fe; color: #7c3aed; font-size: 12.5px; font-weight: 700;
    }

    /* Below ~460px a one-third column cannot hold "Play interval" on one line,
       so the transport stacks above the answers instead. */
    @media (max-width: 520px) {
        .hv-ex-play-answers { grid-template-columns: 1fr; gap: 8px; }
        .hv-ex-btn { padding: 12px 14px; }
    }
    @media (max-width: 400px) {
        .hv-ex-answer { font-size: 11.5px; padding: 8px 4px; }
        .hv-ex-stave { height: 100px; }
    }
</style>

<script>
/* Runtime strings — the same i18n bridge the practice screens use. */
window.hvExerciseStrings = {!! $exerciseStrings !!};
window.hvIntervalLabels = {!! $exerciseLabels !!};

/**
 * Piano playback for the in-article exercise boxes.
 *
 * A trimmed sibling of window.HarmonivaAudio on the practice pages: same
 * Salamander sampler, but built lazily on the first press so a reader who never
 * touches an exercise downloads no samples at all. Pitches arrive as MIDI
 * numbers, which is what keeps unusual spellings (Cb, B#) sounding right.
 */
window.hvBlogAudio = (function () {
    let sampler = null;
    let ready = false;

    function midiToNote(m) {
        return Tone.Frequency(m, 'midi').toNote();
    }

    async function ensureReady() {
        if (typeof Tone === 'undefined') throw new Error('Tone.js unavailable');
        await Tone.start();
        if (!sampler) {
            sampler = new Tone.Sampler({
                urls: {
                    A1: 'A1.mp3', C2: 'C2.mp3', 'D#2': 'Ds2.mp3', 'F#2': 'Fs2.mp3',
                    A2: 'A2.mp3', C3: 'C3.mp3', 'D#3': 'Ds3.mp3', 'F#3': 'Fs3.mp3',
                    A3: 'A3.mp3', C4: 'C4.mp3', 'D#4': 'Ds4.mp3', 'F#4': 'Fs4.mp3',
                    A4: 'A4.mp3', C5: 'C5.mp3', 'D#5': 'Ds5.mp3', 'F#5': 'Fs5.mp3',
                    A5: 'A5.mp3', C6: 'C6.mp3', 'D#6': 'Ds6.mp3', 'F#6': 'Fs6.mp3',
                },
                release: 1,
                baseUrl: 'https://tonejs.github.io/audio/salamander/',
                onload: () => { ready = true; },
            }).toDestination();
        }
        const deadline = Date.now() + 10000;
        while (!ready && Date.now() < deadline) {
            await new Promise((r) => setTimeout(r, 80));
        }
        if (!ready) throw new Error('Samples not loaded');
    }

    return {
        /** Two pitches one after the other. Resolves with the phrase length. */
        async playMelodic(m1, m2) {
            await ensureReady();
            const now = Tone.now();
            sampler.triggerAttackRelease(midiToNote(m1), 0.9, now);
            sampler.triggerAttackRelease(midiToNote(m2), 1.4, now + 0.75);
            return 2100;
        },
        /** Both pitches together. */
        async playHarmonic(m1, m2) {
            await ensureReady();
            const now = Tone.now();
            sampler.triggerAttackRelease(midiToNote(m1), 1.9, now);
            sampler.triggerAttackRelease(midiToNote(m2), 1.9, now);
            return 2000;
        },
        stop() { if (sampler) sampler.releaseAll(); },
    };
})();

/**
 * Draw the question on a staff, mirroring practice-melodic-interval /
 * practice-harmonic-interval: the first note alone until an answer is in, then
 * both. Melodic intervals are two half notes side by side; harmonic intervals
 * are one chord. A missing or slow VexFlow simply leaves the box empty — the
 * exercise is playable without it.
 */
window.hvDrawStave = function (elId, q, mode, showBoth) {
    if (typeof Vex === 'undefined') return;
    const div = document.getElementById(elId);
    if (!div) return;

    const { Renderer, Stave, StaveNote, Voice, Formatter, Accidental } = Vex.Flow;
    div.innerHTML = '';
    const renderer = new Renderer(div, Renderer.Backends.SVG);
    renderer.resize(420, 114);
    const ctx = renderer.getContext();
    const stave = new Stave(4, 8, 404);
    stave.addClef('treble');
    stave.setNoteStartX(stave.getNoteStartX() + 26);
    stave.setContext(ctx).draw();

    // Stem away from the middle line (B4 = midi 71), as on the practice pages.
    const stemFor = (midi) => (midi >= 71 ? -1 : 1);

    let notes;
    if (mode === 'harmonic') {
        const keys = showBoth ? [q.k1, q.k2] : [q.k1];
        notes = [new StaveNote({
            keys, duration: 'w', clef: 'treble',
            stem_direction: stemFor(showBoth ? q.m2 : q.m1),
        })];
    } else {
        const seq = showBoth ? [[q.k1, q.m1], [q.k2, q.m2]] : [[q.k1, q.m1]];
        notes = seq.map(([k, m]) => new StaveNote({
            keys: [k], duration: showBoth ? 'h' : 'w', clef: 'treble',
            stem_direction: stemFor(m),
        }));
    }

    const beats = (mode !== 'harmonic' && showBoth) ? 2 : 4;
    const beatValue = (mode !== 'harmonic' && showBoth) ? 2 : 4;
    const voice = new Voice({ numBeats: beats, beatValue });
    voice.addTickables(notes);
    Accidental.applyAccidentals([voice], 'C');
    new Formatter().joinVoices([voice]).format([voice], notes.length > 1 ? 150 : 90);
    voice.draw(ctx, stave);
};

window.hvIntervalExercise = function (questions, mode, exId) {
    const t = window.hvExerciseStrings;

    return {
        questions,
        mode,
        strings: t,
        index: 0,
        answered: false,
        chosen: null,
        correct: false,
        playing: false,
        played: false,
        gated: false,
        results: [],
        status: t.play_prompt,

        init() {
            // VexFlow is deferred; draw as soon as it is there, and give up
            // quietly if it never arrives rather than blocking the exercise.
            const draw = () => this.render(false);
            if (typeof Vex !== 'undefined') draw();
            else {
                let tries = 0;
                const timer = setInterval(() => {
                    if (typeof Vex !== 'undefined') { clearInterval(timer); draw(); }
                    else if (++tries > 40) clearInterval(timer);
                }, 150);
            }
        },

        render(showBoth) {
            window.hvDrawStave('stave-' + exId, this.current, this.mode, showBoth);
        },

        get current() { return this.questions[this.index]; },
        get isLast() { return this.index === this.questions.length - 1; },
        get playLabel() { return this.played ? t.replay : t.play; },
        get countLabel() {
            return t.question.replace(':current', this.index + 1).replace(':total', this.questions.length);
        },
        get scoreLabel() {
            const hits = this.results.filter(Boolean).length;
            return t.score.replace(':correct', hits).replace(':total', this.questions.length);
        },
        /** Localised label for a canonical interval name. */
        label(name) {
            var map = window.hvIntervalLabels || {};
            return Object.prototype.hasOwnProperty.call(map, name) ? map[name] : name;
        },

        get feedback() {
            if (!this.answered) return '';
            return this.correct ? t.correct : t.incorrect.replace(':answer', this.label(this.current.answer));
        },

        dotClass(i) {
            if (i < this.results.length) return this.results[i] ? 'is-done' : 'is-missed';
            return i === this.index ? 'is-current' : '';
        },

        answerClass(option) {
            if (!this.answered) return '';
            if (option === this.current.answer) return 'is-correct';
            if (option === this.chosen) return 'is-wrong';
            return 'is-dim';
        },

        async play() {
            if (this.playing) return;
            this.playing = true;
            this.status = t.loading;
            try {
                const holdMs = this.mode === 'harmonic'
                    ? await window.hvBlogAudio.playHarmonic(this.current.m1, this.current.m2)
                    : await window.hvBlogAudio.playMelodic(this.current.m1, this.current.m2);
                this.status = t.playing;
                await new Promise((r) => setTimeout(r, holdMs));
                this.played = true;
                this.status = t.answer_prompt;
            } catch (e) {
                this.status = t.audio_error;
            } finally {
                this.playing = false;
            }
        },

        choose(option) {
            if (this.answered) return;
            this.answered = true;
            this.chosen = option;
            this.correct = option === this.current.answer;
            this.results.push(this.correct);
            this.render(true);

            // The last answer locks the box: the correct answer stays on screen
            // next to the signup panel, with no control that dismisses it.
            if (this.isLast) {
                this.gated = true;
                window.hvBlogAudio.stop();
            }
        },

        next() {
            if (!this.answered || this.isLast) return;
            window.hvBlogAudio.stop();
            this.index += 1;
            this.answered = false;
            this.chosen = null;
            this.correct = false;
            this.played = false;
            this.status = t.play_prompt;
            this.render(false);
        },
    };
};
</script>
@endonce
@endif
