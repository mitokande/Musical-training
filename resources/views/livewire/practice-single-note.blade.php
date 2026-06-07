    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

        <!-- Practice Card -->
        <div class="card overflow-hidden mb-6">
            <!-- Header -->
            <div class="hero-gradient p-6">
                <div class="relative flex items-center justify-center">
                    <a href="/learn" class="absolute left-0 top-1/2 -translate-y-1/2 w-12 h-12 flex items-center justify-center rounded-xl bg-white/10 hover:bg-white/20 text-white transition-all hover:scale-105 active:scale-95">
                        <i data-lucide="arrow-left" class="w-6 h-6"></i>
                    </a>
                    <div class="text-center">
                        <h1 class="text-xl font-bold text-white">Single Note Practice</h1>
                        <p class="text-white/80 text-sm">Listening Exercise</p>
                    </div>
                    <div class="absolute right-0 top-1/2 -translate-y-1/2">
                        <span class="inline-flex items-center gap-1.5 rounded-xl bg-white/10 px-3 py-2 text-sm font-semibold text-white/90 ring-1 ring-white/20 backdrop-blur">
                            <span>{{ $currentQuestionNumber }}</span>
                            <span class="font-medium">/</span>
                            <span>{{ $totalQuestions }}</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="p-8">

                <!-- Staff -->
                <div id="staffContainer"
                     class="rounded-xl mb-6 border-4 border-transparent transition-all duration-300 overflow-hidden"
                     style="background:#fafafa;">
                    <div id="output" style="width:100%;height:145px;"
                         data-group-size="{{ $groupSize }}"
                         data-answer-mode="{{ $answerMode }}"
                         data-clef="{{ $clef }}"
                         data-group-targets="{{ json_encode(array_map(fn($n) => strtolower($n['target']).'/'.$n['octave'], $currentGroupNotes)) }}"
                         data-allowed-notes="{{ json_encode(array_values($allowedNotes)) }}">
                    </div>
                </div>

                <!-- Play / Next -->
                <div class="flex flex-col items-center mb-6">
                    <div class="flex gap-3">
                        <button id="playButton"
                                class="btn-primary text-white font-semibold py-3 px-8 rounded-lg flex items-center gap-2 hover:shadow-lg transition-all">
                            <i data-lucide="play" class="w-5 h-5"></i> Play
                        </button>
                        @if(!$isLastQuestion)
                        <button id="nextPracticeBtn" wire:click="getNextPractice"
                                class="hidden font-semibold py-3 px-8 rounded-lg flex items-center gap-2 hover:shadow-lg
                                       bg-blue-100 text-blue-700 border-2 border-blue-300 hover:bg-blue-200 hover:border-blue-400">
                            <i data-lucide="arrow-right" class="w-5 h-5"></i> Next
                        </button>
                        @else
                        <a id="nextPracticeBtn" href="/learn"
                           class="hidden font-semibold py-3 px-8 rounded-lg flex items-center gap-2 hover:shadow-lg
                                  bg-blue-100 text-blue-700 border-2 border-blue-300 hover:bg-blue-200 hover:border-blue-400">
                            <i data-lucide="check" class="w-5 h-5"></i> Finish
                        </a>
                        @endif
                    </div>
                    <p id="playStatus" class="text-sm text-gray-500 mt-3">Press Play to hear the note(s), then answer below</p>
                </div>

                <!-- Piano Keyboard Answer -->
                <div id="pianoAnswerArea">
                    @if($groupSize > 1)
                    <p class="text-xs text-gray-400 text-center mb-3">
                        Click <strong>{{ $groupSize }}</strong> keys in order to answer
                    </p>
                    @else
                    <p class="text-xs text-gray-400 text-center mb-3">Click the key that matches the note you heard</p>
                    @endif

                    <!-- Keyboard -->
                    <div class="relative mx-auto select-none" style="max-width:480px;height:116px;">
                        <!-- White keys -->
                        <div class="flex h-full" style="gap:2px;">
                            @foreach(['C','D','E','F','G','A','B'] as $note)
                            <button class="piano-answer-key flex-1 rounded-b-xl border-2 border-gray-300 bg-white
                                           transition-all shadow-md hover:shadow-lg hover:bg-indigo-50 hover:border-indigo-400
                                           flex flex-col items-center justify-end pb-2"
                                    data-note="{{ $note }}">
                                @if($answerMode === 'note-names')
                                <span class="text-xs font-bold text-gray-600 pointer-events-none">{{ $note }}</span>
                                @endif
                            </button>
                            @endforeach
                        </div>

                        <!-- Black keys (clickable) -->
                        <div class="absolute inset-0 pointer-events-none">
                            @php
                            $practiceBlackKeys = [
                                ['left' => '10.7%', 'note' => 'C#', 'sharp' => 'C#', 'flat' => 'Db'],
                                ['left' => '24.9%', 'note' => 'D#', 'sharp' => 'D#', 'flat' => 'Eb'],
                                ['left' => '53.3%', 'note' => 'F#', 'sharp' => 'F#', 'flat' => 'Gb'],
                                ['left' => '67.5%', 'note' => 'G#', 'sharp' => 'G#', 'flat' => 'Ab'],
                                ['left' => '81.7%', 'note' => 'A#', 'sharp' => 'A#', 'flat' => 'Bb'],
                            ];
                            @endphp
                            @foreach($practiceBlackKeys as $bk)
                            <button class="piano-answer-key absolute top-0 rounded-b-md pointer-events-auto transition-all
                                           flex flex-col items-center justify-center"
                                    data-note="{{ $bk['note'] }}"
                                    style="left:{{ $bk['left'] }};width:8%;height:60%;
                                           background:linear-gradient(180deg,#374151 0%,#111827 100%);
                                           border:1px solid #1f2937;
                                           box-shadow:2px 4px 6px rgba(0,0,0,.4);
                                           z-index:10;">
                                @if($answerMode === 'note-names')
                                <div class="pointer-events-none flex flex-col items-center justify-center" style="gap:1px;line-height:1.1;">
                                    <span class="font-bold text-white text-center" style="font-size:11px;">{{ $bk['sharp'] }}</span>
                                    <span class="font-semibold text-center" style="font-size:10px;color:rgba(255,255,255,0.7);">{{ $bk['flat'] }}</span>
                                </div>
                                @endif
                            </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Progress dots -->
                    @if($groupSize > 1)
                    <div class="flex justify-center gap-2 mt-4">
                        @for($i = 0; $i < $groupSize; $i++)
                        <div class="w-3 h-3 rounded-full bg-gray-200 transition-all" id="dot-{{ $i }}"></div>
                        @endfor
                    </div>
                    @endif
                </div>

                <!-- Feedback -->
                <div id="feedbackMessage" class="mt-4 p-4 rounded-lg text-center font-medium hidden"></div>

            </div>
        </div>

        <!-- Score -->
        <div class="flex items-center justify-center gap-4 text-sm text-gray-500">
            <span class="flex items-center gap-1">
                <i data-lucide="sparkles" class="w-4 h-4 text-yellow-500"></i>
                +<span id="xpEarned">0</span> XP
            </span>
            <span>•</span>
            <span><span id="scoreCorrect">0</span> / <span id="scoreTotal">0</span> Correct</span>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/vexflow@4.2.2/build/cjs/vexflow.js"></script>
        <script>
        window.initPracticeSingleNote = function () {
            window._practiceGen = (window._practiceGen || 0) + 1;
            const myGen = window._practiceGen;

            const outputDiv      = document.getElementById('output');
            const staffContainer = document.getElementById('staffContainer');
            const playButton     = document.getElementById('playButton');
            const playStatus     = document.getElementById('playStatus');
            const nextButton     = document.getElementById('nextPracticeBtn');
            const feedbackMsg    = document.getElementById('feedbackMessage');
            const pianoKeys      = document.querySelectorAll('.piano-answer-key');

            if (!outputDiv || !playButton) return;

            const groupSize    = parseInt(outputDiv.dataset.groupSize) || 1;
            const clef         = outputDiv.dataset.clef || 'treble';
            const groupTargets = JSON.parse(outputDiv.dataset.groupTargets || '[]');

            // Clef-specific display octave and reference note.
            // clefOctave: the octave used when rendering the user's answer on the staff.
            // refVF / refTone: the anchor reference note played first.
            //
            // Sol  (Treble): staff E4–F5, ref = C4 (1 ledger below, middle C)
            // Fa   (Bass)  : staff G2–A3, ref = C3 (2nd space, clearly on staff)
            // Do   (Alto)  : staff F3–G4, C4 = middle line, ref = C4
            const clefConfig = {
                treble: { octave: '4', refVF: 'c/4', refTone: 'C4' },
                bass:   { octave: '2', refVF: 'c/3', refTone: 'C3' },
                alto:   { octave: '4', refVF: 'c/4', refTone: 'C4' },
            };
            const cfg        = clefConfig[clef] || clefConfig.treble;
            const clefOctave = cfg.octave;
            const refNoteVF  = cfg.refVF;
            const refNoteTone= cfg.refTone;

            // 40 BPM — 1 beat = 1500ms
            const BPM_MS = 1500;

            // ── VexFlow helpers ───────────────────────────────────────────
            const VF = Vex.Flow;
            const { Renderer, Stave, StaveNote, Voice, Formatter, Accidental } = VF;

            /**
             * mkNote — always passes `clef` to StaveNote so VexFlow positions
             * the notehead on the correct staff line for the active clef.
             * Without this, VexFlow defaults to treble positioning regardless of
             * which clef symbol is shown on the stave.
             */
            function mkNote(key, color) {
                const sn = new StaveNote({ keys: [key], duration: '1', clef: clef });
                if (color) sn.setStyle({ fillStyle: color, strokeStyle: color });
                return sn;
            }

            /** Base staff builder — returns {renderer, ctx, stave} */
            function buildStave(height, staveY) {
                outputDiv.innerHTML = '';
                const w = outputDiv.offsetWidth || 540;
                outputDiv.style.height = height + 'px';
                const renderer = new Renderer(outputDiv, Renderer.Backends.SVG);
                renderer.resize(w, height);
                const ctx = renderer.getContext();
                const stave = new Stave(10, staveY, w - 20);
                stave.addClef(clef);
                stave.setNoteStartX(stave.getNoteStartX() + 20);
                stave.setContext(ctx).draw();
                return { ctx, stave, w };
            }

            /**
             * renderPreview — reference note (indigo) + user answers so far (slate).
             * All whole notes. Called while user is clicking keys.
             */
            function renderPreview(answers) {
                const { ctx, stave, w } = buildStave(145, 25);

                const items = [{ key: refNoteVF, color: '#6366f1' }];
                answers.forEach(ans => {
                    items.push({ key: ans.toLowerCase() + '/' + clefOctave, color: '#475569' });
                });

                const notes = items.map(({ key, color }) => mkNote(key, color));
                const voice = new Voice({ numBeats: 4, beatValue: 4 });
                voice.setStrict(false);
                voice.addTickables(notes);
                Accidental.applyAccidentals([voice], 'C');
                new Formatter().joinVoices([voice]).format([voice], w - 140);
                voice.draw(ctx, stave);
            }

            /**
             * renderFinal — two-Voice approach with manual SVG labels.
             *
             * Voice A: refNote | ans[0]        | ans[1]        | …
             * Voice B: ghost   | ghost/hint[0] | ghost/hint[1] | …
             *
             * At each wrong-answer beat: Voice A has a red note, Voice B has a teal hint note.
             * VexFlow places two real notes at the same beat side-by-side (~10px apart).
             * Labels are drawn as plain SVG <text> at the hint note's absolute X position
             * and a fixed Y above the staff — this gives perfectly consistent positioning.
             */
            function renderFinal(answers) {
                // Taller stave: space above (y=42) for labels, space below for ledger lines
                const { ctx, stave, w } = buildStave(175, 42);

                const GhostNote = VF.GhostNote;
                // GhostNote is invisible — it holds a beat without rendering.
                // Fallback: a transparent mkNote (clef already included via mkNote).
                const makeGhost = () => GhostNote
                    ? new GhostNote({ duration: '1' })
                    : mkNote(refNoteVF, 'rgba(0,0,0,0)');

                const hintNotes = []; // {note: StaveNote, label: string}

                // ── Voice A: reference + user answers ─────────────────────
                const vA = [];
                vA.push(mkNote(refNoteVF, '#6366f1'));

                answers.forEach((ans, i) => {
                    const tgtVF   = groupTargets[i] || (ans.toLowerCase() + '/' + clefOctave);
                    const tgtName = tgtVF.split('/')[0];
                    const isRight = ans.toLowerCase() === tgtName.toLowerCase();
                    vA.push(mkNote(ans.toLowerCase() + '/' + clefOctave,
                                   isRight ? '#16a34a' : '#dc2626'));
                });

                // ── Voice B: ghost at correct slots, teal hint at wrong ────
                const vB = [];
                vB.push(makeGhost()); // ghost aligned with ref note

                answers.forEach((ans, i) => {
                    const tgtVF   = groupTargets[i] || (ans.toLowerCase() + '/' + clefOctave);
                    const tgtName = tgtVF.split('/')[0];
                    const isRight = ans.toLowerCase() === tgtName.toLowerCase();

                    if (isRight) {
                        vB.push(makeGhost());
                    } else {
                        // Teal hint — visually distinct from both green and red
                        const hint = mkNote(tgtVF, '#0d9488');
                        vB.push(hint);
                        hintNotes.push({ note: hint, label: tgtName.toUpperCase() });
                    }
                });

                // ── Format both voices together ────────────────────────────
                const voice1 = new Voice({ numBeats: 4, beatValue: 4 });
                voice1.setStrict(false);
                voice1.addTickables(vA);

                const voice2 = new Voice({ numBeats: 4, beatValue: 4 });
                voice2.setStrict(false);
                voice2.addTickables(vB);

                Accidental.applyAccidentals([voice1], 'C');
                Accidental.applyAccidentals([voice2], 'C');

                new Formatter().joinVoices([voice1, voice2]).format([voice1, voice2], w - 110);
                voice1.draw(ctx, stave);
                voice2.draw(ctx, stave);

                // ── Draw note-name labels 1-2 px above each hint notehead ───
                // getAbsoluteX() + getYs()[0] give the exact notehead centre in SVG coords.
                // SVG text y = baseline. With font-size 10 the ascent is ~8px, so
                // setting y = noteY - 6 puts the text bottom ~1px above the notehead top
                // (notehead radius ≈ 5px → top at noteY-5, text bottom at noteY-6 = 1px gap).
                const svg = outputDiv.querySelector('svg');
                if (svg && hintNotes.length) {
                    hintNotes.forEach(({ note, label }) => {
                        try {
                            const x  = note.getAbsoluteX();
                            const ny = note.getYs()[0];       // Y centre of the notehead
                            const t  = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                            t.setAttribute('x', String(x));
                            t.setAttribute('y', String(ny - 6)); // baseline just above notehead
                            t.setAttribute('text-anchor', 'middle');
                            t.setAttribute('font-family', 'Arial, sans-serif');
                            t.setAttribute('font-size', '10');
                            t.setAttribute('font-weight', 'bold');
                            t.setAttribute('fill', '#000000');
                            t.textContent = label;
                            svg.appendChild(t);
                        } catch (_) {}
                    });
                }
            }

            // ── State ────────────────────────────────────────────────────
            let isAnswered = false;
            let playedOnce = false;
            let answerCount = 0;
            let userAnswers = [];

            function dotSet(i, state) {
                const dot = document.getElementById('dot-' + i);
                if (!dot) return;
                const c = { empty:'bg-gray-200', pending:'bg-indigo-400', correct:'bg-green-500', wrong:'bg-red-500' };
                dot.className = 'w-3 h-3 rounded-full transition-all ' + (c[state] || 'bg-gray-200');
            }

            function resetKeyStyles() {
                pianoKeys.forEach(k => {
                    k.classList.remove('bg-indigo-100', 'border-indigo-500');
                    if (k.dataset.note && k.dataset.note.includes('#')) {
                        k.style.background = 'linear-gradient(180deg,#374151 0%,#111827 100%)';
                        k.style.borderColor = '#1f2937';
                    }
                });
            }

            function resetAll() {
                answerCount = 0;
                userAnswers = [];
                staffContainer.classList.remove('border-green-500', 'border-red-500');
                staffContainer.classList.add('border-transparent');
                feedbackMsg.classList.add('hidden');
                outputDiv.style.height = '145px';
                renderPreview([]);
                for (let i = 0; i < groupSize; i++) dotSet(i, 'empty');
                resetKeyStyles();
            }

            function evaluate() {
                isAnswered = true;
                let allCorrect = true;
                userAnswers.forEach((ans, i) => {
                    const tgt = (groupTargets[i] || '').split('/')[0];
                    const ok = ans.toLowerCase() === tgt.toLowerCase();
                    if (!ok) allCorrect = false;
                    dotSet(i, ok ? 'correct' : 'wrong');
                });

                renderFinal(userAnswers);

                staffContainer.classList.remove('border-transparent');
                staffContainer.classList.add(allCorrect ? 'border-green-500' : 'border-red-500');

                if (allCorrect) {
                    feedbackMsg.textContent = '✓ Correct! Well done!';
                    feedbackMsg.className = 'mt-4 p-4 rounded-lg text-center font-medium bg-green-100 text-green-700';
                } else {
                    const correctList = groupTargets.map(t => t.split('/')[0].toUpperCase()).join(', ');
                    feedbackMsg.textContent = '✗ Incorrect. Correct: ' + correctList;
                    feedbackMsg.className = 'mt-4 p-4 rounded-lg text-center font-medium bg-red-100 text-red-700';
                }
                feedbackMsg.classList.remove('hidden');

                playButton.classList.add('hidden');
                if (playStatus) playStatus.classList.add('hidden');
                if (nextButton) nextButton.classList.remove('hidden');
                if (typeof lucide !== 'undefined') lucide.createIcons();

                // Server record
                fetch('/api/practice/check-answer', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify({ practice_id: 1, question_id: 1, answer: userAnswers[0] || '' }),
                }).then(r => r.json()).then(d => {
                    if (d.xp !== undefined) { const el = document.getElementById('xpEarned'); if (el) el.textContent = d.xp; }
                    if (d.correctCount !== undefined) { const el = document.getElementById('scoreCorrect'); if (el) el.textContent = d.correctCount; }
                    if (d.totalCount !== undefined) { const el = document.getElementById('scoreTotal'); if (el) el.textContent = d.totalCount; }
                }).catch(() => {});
            }

            // Initial render (reference note only)
            renderPreview([]);

            // ── Piano key clicks ─────────────────────────────────────────
            pianoKeys.forEach(key => {
                key.addEventListener('click', function (e) {
                    e.stopPropagation();
                    if (isAnswered || !playedOnce || answerCount >= groupSize) return;

                    const note = this.dataset.note;
                    userAnswers.push(note);
                    dotSet(answerCount, 'pending');
                    answerCount++;

                    // Flash
                    const isBlack = note.includes('#');
                    if (isBlack) {
                        this.style.background = 'linear-gradient(180deg,#4f46e5,#3730a3)';
                        this.style.borderColor = '#818cf8';
                        setTimeout(() => {
                            if (!isAnswered) { this.style.background = 'linear-gradient(180deg,#374151 0%,#111827 100%)'; this.style.borderColor = '#1f2937'; }
                        }, 350);
                    } else {
                        this.classList.add('bg-indigo-100', 'border-indigo-500');
                        setTimeout(() => { if (!isAnswered) this.classList.remove('bg-indigo-100', 'border-indigo-500'); }, 350);
                    }

                    renderPreview(userAnswers);

                    if (answerCount >= groupSize) setTimeout(evaluate, 150);
                });
            });

            // ── Play button — 40 BPM ──────────────────────────────────────
            playButton.addEventListener('click', async function () {
                if (window._practiceGen !== myGen || isAnswered) return;
                await Tone.start();

                playButton.disabled = true;
                playButton.innerHTML = '<i data-lucide="volume-2" class="w-5 h-5"></i> Playing…';
                if (typeof lucide !== 'undefined') lucide.createIcons();
                if (playStatus) playStatus.textContent = 'Playing…';

                resetAll();

                // 1. Reference note (40 BPM = 1.5s)
                await window.HarmonivaAudio.playNote(refNoteTone, BPM_MS / 1000);
                await new Promise(r => setTimeout(r, BPM_MS + 200));
                if (window._practiceGen !== myGen) return;

                // 2. Question notes at 40 BPM
                // VF "c#/4" → Tone "C#4"
                const toneNotes = groupTargets.map(vf => {
                    const [n, o] = vf.split('/');
                    const name = n.charAt(0).toUpperCase() + n.slice(1); // 'c#' → 'C#', 'c' → 'C'
                    return name + (o || '4');
                });

                if (toneNotes.length === 1) {
                    await window.HarmonivaAudio.playNote(toneNotes[0], BPM_MS / 1000);
                    await new Promise(r => setTimeout(r, BPM_MS + 400));
                } else {
                    window.HarmonivaAudio.playSequential(toneNotes, BPM_MS, BPM_MS / 1000);
                    await new Promise(r => setTimeout(r, window.HarmonivaAudio.totalMs(toneNotes, BPM_MS)));
                }
                if (window._practiceGen !== myGen) return;

                playedOnce = true;
                playButton.disabled = false;
                playButton.innerHTML = '<i data-lucide="play" class="w-5 h-5"></i> Play Again';
                if (typeof lucide !== 'undefined') lucide.createIcons();
                if (playStatus) playStatus.textContent = 'Now click the piano keys to answer';
            });

            if (typeof lucide !== 'undefined') lucide.createIcons();
        };

        document.addEventListener('livewire:init', function () {
            window.initPracticeSingleNote();
            Livewire.on('practice-updated', () => setTimeout(window.initPracticeSingleNote, 80));
        });
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Livewire === 'undefined') window.initPracticeSingleNote();
        });
        </script>

    </main>
