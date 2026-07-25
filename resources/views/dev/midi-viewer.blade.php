<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.google-analytics')
    @include('partials.posthog')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MIDI Viewer (Dev) - {{ config('app.name', 'Harmoniva') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/combine/npm/tone@14.7.58,npm/@magenta/music@1.23.1/es6/core.js,npm/focus-visible@5,npm/html-midi-player@1.5.0"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
        midi-player { width: 100%; }
        midi-visualizer .piano-roll-visualizer {
            background: #fff;
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
            overflow-x: auto;
            padding: 1rem;
        }
        midi-visualizer svg rect.note { fill: #8b5cf6; opacity: 0.85; }
        midi-visualizer svg rect.note.active { fill: #f59e0b; opacity: 1; }

        /* Piano styles (ported from piano-studio.blade.php) */
        .piano-wrapper { overflow-x: auto; padding-bottom: 10px; width: 100%; }
        .piano-container { display: flex; justify-content: center; position: relative; user-select: none; width: 100%; }
        .piano-keys-container { display: flex; position: relative; width: 100%; }
        .piano-key { cursor: pointer; transition: all 0.08s ease; position: relative; }
        .white-key {
            flex: 1;
            min-width: 28px;
            height: 160px;
            background: linear-gradient(180deg, #fefefe 0%, #f5f5f5 100%);
            border: 1px solid #d1d5db;
            border-radius: 0 0 5px 5px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1), inset 0 -2px 4px rgb(0 0 0 / 0.05);
            z-index: 1;
        }
        .white-key:hover { background: linear-gradient(180deg, #f0f0f0 0%, #e8e8e8 100%); }
        .white-key.active {
            background: linear-gradient(180deg, #c084fc 0%, #a855f7 100%);
            transform: translateY(2px);
            box-shadow: 0 2px 4px -1px rgb(0 0 0 / 0.1);
        }
        .black-key {
            height: 96px;
            min-width: 18px;
            background: linear-gradient(180deg, #374151 0%, #1f2937 100%);
            border-radius: 0 0 3px 3px;
            position: absolute;
            z-index: 2;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.3), inset 0 -4px 8px rgb(0 0 0 / 0.3);
        }
        .black-key:hover { background: linear-gradient(180deg, #4b5563 0%, #374151 100%); }
        .black-key.active {
            background: linear-gradient(180deg, #7c3aed 0%, #6b21a8 100%);
            transform: translateY(2px);
            height: 94px;
            box-shadow: 0 2px 4px -1px rgb(0 0 0 / 0.2);
        }
        .key-label {
            position: absolute;
            bottom: 8px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 10px;
            color: #9ca3af;
            font-weight: 500;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen text-gray-800">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        {{-- Header --}}
        <div class="mb-8">
            <div class="inline-flex items-center gap-2 px-3 py-1 mb-3 rounded-full bg-amber-100 text-amber-700 text-xs font-semibold uppercase tracking-wide">
                Dev tool
            </div>
            <h1 class="text-3xl font-bold text-gray-900">MIDI Viewer</h1>
            <p class="mt-2 text-gray-600 max-w-2xl">
                Upload a <span class="font-mono">.mid</span> file to inspect it as a piano roll and play it back.
                Files are stored privately under <span class="font-mono">storage/app/private/dev-midi</span>.
            </p>
        </div>

        {{-- Flash / errors --}}
        @if(session('status'))
            <div class="mb-6 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-medium">
                {{ session('status') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mb-6 px-4 py-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-medium">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Upload --}}
        <section class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Upload a MIDI file</h2>
            <form method="POST" action="{{ route('dev.midi.upload') }}" enctype="multipart/form-data" class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                @csrf
                <input type="file" name="midi" accept=".mid,.midi" required
                       class="block text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-100 file:text-purple-700 hover:file:bg-purple-200 cursor-pointer">
                <button type="submit"
                        class="px-5 py-2 rounded-full bg-purple-600 text-white text-sm font-semibold hover:bg-purple-700 transition">
                    Upload
                </button>
                <span class="text-xs text-gray-400">.mid / .midi, max 1&nbsp;MB</span>
            </form>
        </section>

        {{-- Viewer --}}
        @if($selected)
            <section class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mb-8">
                <div class="flex items-center justify-between gap-4 mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Now viewing</h2>
                    <span class="text-xs text-gray-400 font-mono">{{ $selected }}</span>
                </div>
                <midi-visualizer type="piano-roll" id="midi-vis"
                                 src="{{ route('dev.midi.file', ['filename' => $selected]) }}"></midi-visualizer>
                <div class="mt-4">
                    <midi-player id="midi-player-el" sound-font visualizer="#midi-vis"
                                 src="{{ route('dev.midi.file', ['filename' => $selected]) }}"></midi-player>
                </div>
            </section>
        @endif

        {{-- Realtime note flow --}}
        <section class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mb-8">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-3">
                <h2 class="text-lg font-semibold text-gray-900">Note flow</h2>
                <div class="flex flex-wrap items-center gap-4 text-xs text-gray-400">
                    <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-amber-500 inline-block"></span> file playback</span>
                    <span id="practice-score" class="hidden font-mono text-gray-600"></span>
                    <button id="practice-btn" type="button" disabled
                            class="px-4 py-1.5 rounded-full bg-emerald-600 text-white font-semibold hover:bg-emerald-700 transition disabled:opacity-50 disabled:cursor-not-allowed">Practice</button>
                </div>
            </div>
            <p class="text-xs text-gray-400 mb-3">
                Practice mode: the original plays muffled as a guide &mdash; play the notes yourself on the piano as the bars reach the line.
                Correct notes sparkle; wrong notes flash red; bars that slip past unplayed turn gray.
            </p>
            <canvas id="note-flow" class="w-full h-56 rounded-xl bg-gray-900 block"></canvas>
        </section>

        {{-- Piano (record & save as .mid) --}}
        <section class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 mb-8">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-3">
                <h2 class="text-lg font-semibold text-gray-900">Piano</h2>
                <div class="flex flex-wrap items-center gap-3">
                    <span class="text-xs text-gray-400"><span id="rec-count">0</span> notes recorded</span>
                    <button id="rec-clear" type="button"
                            class="px-4 py-2 rounded-full bg-gray-100 text-gray-600 text-sm font-semibold hover:bg-gray-200 transition">Clear</button>
                    <input id="rec-name" type="text" placeholder="recording name" maxlength="60"
                           class="px-3 py-2 rounded-lg border border-gray-200 text-sm w-44 focus:outline-none focus:ring-2 focus:ring-purple-200">
                    <button id="rec-save" type="button" disabled
                            class="px-5 py-2 rounded-full bg-purple-600 text-white text-sm font-semibold hover:bg-purple-700 transition disabled:opacity-50 disabled:cursor-not-allowed">Save as .mid</button>
                </div>
            </div>
            <p class="text-xs text-gray-400 mb-4">
                Click keys or use your computer keyboard (A&ndash;J / W&ndash;U for octave 3, K&ndash;V / O&ndash;' for octave 4).
                Everything you play is recorded with its real timing; <span class="font-semibold">Save as .mid</span> uploads it to the list below.
                While a file plays above, the matching keys light up.
            </p>
            <div class="piano-wrapper">
                <div class="piano-container" id="piano"></div>
            </div>
        </section>

        {{-- File list --}}
        <section class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900">Uploaded files</h2>
            </div>
            @if($files->isEmpty())
                <p class="px-6 py-8 text-sm text-gray-400 italic">Nothing uploaded yet.</p>
            @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-gray-400 bg-gray-50">
                            <th class="px-6 py-3 font-semibold">File</th>
                            <th class="px-3 py-3 font-semibold text-right">Size</th>
                            <th class="px-3 py-3 font-semibold">Uploaded</th>
                            <th class="px-6 py-3 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($files as $file)
                            <tr class="{{ $file['name'] === $selected ? 'bg-purple-50' : '' }}">
                                <td class="px-6 py-3 font-mono text-xs text-gray-900">{{ $file['name'] }}</td>
                                <td class="px-3 py-3 text-right text-gray-500">{{ number_format($file['size'] / 1024, 1) }} KB</td>
                                <td class="px-3 py-3 text-gray-500">{{ date('Y-m-d H:i', $file['uploaded_at']) }}</td>
                                <td class="px-6 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('dev.midi.index', ['file' => $file['name']]) }}"
                                       class="text-purple-600 hover:text-purple-800 font-semibold mr-3">View</a>
                                    <a href="{{ route('dev.midi.file', ['filename' => $file['name']]) }}" download
                                       class="text-gray-500 hover:text-gray-700 font-semibold mr-3">Download</a>
                                    <form method="POST" action="{{ route('dev.midi.destroy', ['filename' => $file['name']]) }}" class="inline"
                                          onsubmit="return confirm('Delete {{ $file['name'] }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-500 hover:text-rose-700 font-semibold">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>

        <div class="mt-8 text-center">
            <a href="{{ route('dashboard') }}" class="text-sm text-purple-600 hover:text-purple-800 font-medium">&larr; Back to dashboard</a>
        </div>
    </div>

    <script>
    (function () {
        // Same 4-octave layout as piano-studio.blade.php (C2-B5).
        const NOTES = [
            { note: 'C', octave: 2, type: 'white', key: null, midi: 36 },
            { note: 'C#', octave: 2, type: 'black', key: null, midi: 37 },
            { note: 'D', octave: 2, type: 'white', key: null, midi: 38 },
            { note: 'D#', octave: 2, type: 'black', key: null, midi: 39 },
            { note: 'E', octave: 2, type: 'white', key: null, midi: 40 },
            { note: 'F', octave: 2, type: 'white', key: null, midi: 41 },
            { note: 'F#', octave: 2, type: 'black', key: null, midi: 42 },
            { note: 'G', octave: 2, type: 'white', key: null, midi: 43 },
            { note: 'G#', octave: 2, type: 'black', key: null, midi: 44 },
            { note: 'A', octave: 2, type: 'white', key: null, midi: 45 },
            { note: 'A#', octave: 2, type: 'black', key: null, midi: 46 },
            { note: 'B', octave: 2, type: 'white', key: null, midi: 47 },
            { note: 'C', octave: 3, type: 'white', key: 'a', midi: 48 },
            { note: 'C#', octave: 3, type: 'black', key: 'w', midi: 49 },
            { note: 'D', octave: 3, type: 'white', key: 's', midi: 50 },
            { note: 'D#', octave: 3, type: 'black', key: 'e', midi: 51 },
            { note: 'E', octave: 3, type: 'white', key: 'd', midi: 52 },
            { note: 'F', octave: 3, type: 'white', key: 'f', midi: 53 },
            { note: 'F#', octave: 3, type: 'black', key: 't', midi: 54 },
            { note: 'G', octave: 3, type: 'white', key: 'g', midi: 55 },
            { note: 'G#', octave: 3, type: 'black', key: 'y', midi: 56 },
            { note: 'A', octave: 3, type: 'white', key: 'h', midi: 57 },
            { note: 'A#', octave: 3, type: 'black', key: 'u', midi: 58 },
            { note: 'B', octave: 3, type: 'white', key: 'j', midi: 59 },
            { note: 'C', octave: 4, type: 'white', key: 'k', midi: 60 },
            { note: 'C#', octave: 4, type: 'black', key: 'o', midi: 61 },
            { note: 'D', octave: 4, type: 'white', key: 'l', midi: 62 },
            { note: 'D#', octave: 4, type: 'black', key: 'p', midi: 63 },
            { note: 'E', octave: 4, type: 'white', key: ';', midi: 64 },
            { note: 'F', octave: 4, type: 'white', key: 'z', midi: 65 },
            { note: 'F#', octave: 4, type: 'black', key: '.', midi: 66 },
            { note: 'G', octave: 4, type: 'white', key: 'x', midi: 67 },
            { note: 'G#', octave: 4, type: 'black', key: '/', midi: 68 },
            { note: 'A', octave: 4, type: 'white', key: 'c', midi: 69 },
            { note: 'A#', octave: 4, type: 'black', key: "'", midi: 70 },
            { note: 'B', octave: 4, type: 'white', key: 'v', midi: 71 },
            { note: 'C', octave: 5, type: 'white', key: null, midi: 72 },
            { note: 'C#', octave: 5, type: 'black', key: null, midi: 73 },
            { note: 'D', octave: 5, type: 'white', key: null, midi: 74 },
            { note: 'D#', octave: 5, type: 'black', key: null, midi: 75 },
            { note: 'E', octave: 5, type: 'white', key: null, midi: 76 },
            { note: 'F', octave: 5, type: 'white', key: null, midi: 77 },
            { note: 'F#', octave: 5, type: 'black', key: null, midi: 78 },
            { note: 'G', octave: 5, type: 'white', key: null, midi: 79 },
            { note: 'G#', octave: 5, type: 'black', key: null, midi: 80 },
            { note: 'A', octave: 5, type: 'white', key: null, midi: 81 },
            { note: 'A#', octave: 5, type: 'black', key: null, midi: 82 },
            { note: 'B', octave: 5, type: 'white', key: null, midi: 83 },
        ];

        // --- Audio: reuse the global Tone (v14) that the html-midi-player bundle
        // already loads; loading a second Tone copy would conflict with it.
        let sampler = null, samplerReady = false;
        async function ensureSampler() {
            await Tone.start();
            if (!sampler) {
                sampler = new Tone.Sampler({
                    urls: {
                        A1:'A1.mp3', C2:'C2.mp3', 'D#2':'Ds2.mp3', 'F#2':'Fs2.mp3',
                        A2:'A2.mp3', C3:'C3.mp3', 'D#3':'Ds3.mp3', 'F#3':'Fs3.mp3',
                        A3:'A3.mp3', C4:'C4.mp3', 'D#4':'Ds4.mp3', 'F#4':'Fs4.mp3',
                        A4:'A4.mp3', C5:'C5.mp3', 'D#5':'Ds5.mp3', 'F#5':'Fs5.mp3',
                        A5:'A5.mp3', C6:'C6.mp3',
                    },
                    release: 1,
                    baseUrl: 'https://tonejs.github.io/audio/salamander/',
                    onload: () => { samplerReady = true; }
                }).toDestination();
            }
            const deadline = Date.now() + 8000;
            while (!samplerReady && Date.now() < deadline) {
                await new Promise(r => setTimeout(r, 80));
            }
        }
        async function playNote(note, octave) {
            await ensureSampler();
            sampler.triggerAttackRelease(note + octave, 1);
        }

        // --- Realtime note flow, in lanes aligned with the piano keys below.
        // The bottom line is "now": file-playback bars fall from the top and
        // sound exactly when they reach it (drawn ahead of time from the
        // player's noteSequence). Live piano presses are not drawn here.
        const flowCanvas = document.getElementById('note-flow');
        const flowCtx = flowCanvas.getContext('2d');
        const FLOW_SPEED = 120; // px per second
        const FLOW_COLORS = { file: '#f59e0b' };

        function resizeFlowCanvas() {
            const dpr = window.devicePixelRatio || 1;
            const rect = flowCanvas.getBoundingClientRect();
            flowCanvas.width = Math.round(rect.width * dpr);
            flowCanvas.height = Math.round(rect.height * dpr);
            flowCtx.setTransform(dpr, 0, 0, dpr, 0, 0);
        }
        window.addEventListener('resize', resizeFlowCanvas);

        // X position of a key's lane in canvas coordinates. Both elements live
        // in the same width-constrained column, so viewport X maps directly.
        function keyLane(midi) {
            const keyEl = document.getElementById('key-' + midi);
            if (!keyEl) return null;
            const k = keyEl.getBoundingClientRect();
            const c = flowCanvas.getBoundingClientRect();
            return { x: k.left - c.left, w: k.width };
        }

        // midi-player's currentTime only updates in coarse steps, which makes
        // bars snap instead of flow. Extrapolate a smooth clock between
        // readings, easing out drift and snapping only on real seeks.
        let clock = { reported: 0, base: 0, anchor: 0 };
        function playerTime(nowMs) {
            const t = player.currentTime || 0;
            if (!player.playing) {
                clock = { reported: t, base: t, anchor: nowMs };
                return t;
            }
            if (t !== clock.reported) {
                const est = clock.base + (nowMs - clock.anchor) / 1000;
                const base = Math.abs(est - t) > 1 ? t : est + (t - est) * 0.2;
                clock = { reported: t, base, anchor: nowMs };
            }
            return clock.base + (nowMs - clock.anchor) / 1000;
        }

        // --- Practice mode: the file plays muffled as a guide while the user
        // plays the notes themselves; hits/misses are judged at the line.
        const PRACTICE_LEAD_IN = 2.5; // seconds before the first bar sounds
        const HIT_WINDOW = 0.3;       // +/- seconds around a note's start time
        const practiceBtn = document.getElementById('practice-btn');
        const practiceScore = document.getElementById('practice-score');

        let muffled = null, muffledReady = false;
        async function ensureMuffledSampler() {
            await Tone.start();
            if (!muffled) {
                const volume = new Tone.Volume(-14).toDestination();
                const filter = new Tone.Filter(600, 'lowpass').connect(volume);
                muffled = new Tone.Sampler({
                    urls: {
                        A1:'A1.mp3', C2:'C2.mp3', 'D#2':'Ds2.mp3', 'F#2':'Fs2.mp3',
                        A2:'A2.mp3', C3:'C3.mp3', 'D#3':'Ds3.mp3', 'F#3':'Fs3.mp3',
                        A3:'A3.mp3', C4:'C4.mp3', 'D#4':'Ds4.mp3', 'F#4':'Fs4.mp3',
                        A4:'A4.mp3', C5:'C5.mp3', 'D#5':'Ds5.mp3', 'F#5':'Fs5.mp3',
                        A5:'A5.mp3', C6:'C6.mp3',
                    },
                    release: 1,
                    baseUrl: 'https://tonejs.github.io/audio/salamander/',
                    onload: () => { muffledReady = true; }
                }).connect(filter);
            }
            const deadline = Date.now() + 8000;
            while (!muffledReady && Date.now() < deadline) {
                await new Promise(r => setTimeout(r, 80));
            }
        }

        const practice = {
            active: false, startMs: 0, notes: [], schedIdx: 0,
            hits: 0, wrong: 0, missed: 0, endTime: 0,
        };
        function practiceTime(nowMs) {
            return (nowMs - practice.startMs) / 1000 - PRACTICE_LEAD_IN;
        }
        function updatePracticeScore() {
            practiceScore.textContent = '✓ ' + practice.hits + ' · ✗ ' + practice.wrong + ' · missed ' + practice.missed;
        }

        async function startPractice() {
            const seq = player && player.noteSequence;
            if (practice.active || !seq || !seq.notes.length) return;
            practiceBtn.disabled = true;
            practiceBtn.textContent = 'Loading…';
            await ensureMuffledSampler();
            if (player.playing) player.stop();
            practice.notes = seq.notes
                .map(n => ({
                    pitch: n.pitch, start: n.startTime, end: n.endTime,
                    // pitches without a key can't be judged, only heard
                    inRange: !!document.getElementById('key-' + n.pitch),
                    matched: false, missed: false,
                }))
                .sort((a, b) => a.start - b.start);
            practice.schedIdx = 0;
            practice.hits = practice.wrong = practice.missed = 0;
            practice.endTime = Math.max(...practice.notes.map(n => n.end));
            practice.startMs = performance.now();
            practice.active = true;
            practiceBtn.disabled = false;
            practiceBtn.textContent = 'Stop practice';
            practiceScore.classList.remove('hidden');
            updatePracticeScore();
        }

        function stopPractice() {
            if (!practice.active) return;
            practice.active = false;
            if (muffled) muffled.releaseAll();
            practiceBtn.textContent = 'Practice';
        }

        practiceBtn.addEventListener('click', () => practice.active ? stopPractice() : startPractice());

        // Judge a piano key press against the falling notes.
        function practiceJudge(midi) {
            const t = practiceTime(performance.now());
            let hit = null;
            for (const n of practice.notes) {
                if (n.start > t + HIT_WINDOW) break; // sorted by start
                if (!n.matched && !n.missed && n.pitch === midi && Math.abs(n.start - t) <= HIT_WINDOW) {
                    hit = n;
                    break;
                }
            }
            const lane = keyLane(midi);
            if (hit) {
                hit.matched = true;
                practice.hits++;
                if (lane) spawnSparkles(lane);
            } else {
                practice.wrong++;
                if (lane) spawnMissCue(lane);
            }
            updatePracticeScore();
        }

        // --- Feedback particles, rendered inside the drawFlow loop.
        let particles = []; // {x, y, vx, vy, life, ttl, color, size}
        let lineFlashes = []; // {x, w, life, ttl}

        function spawnSparkles(lane) {
            const H = flowCanvas.getBoundingClientRect().height;
            const cx = lane.x + lane.w / 2;
            for (let i = 0; i < 14; i++) {
                particles.push({
                    x: cx + (Math.random() - 0.5) * lane.w,
                    y: H - 2,
                    vx: (Math.random() - 0.5) * 120,
                    vy: -60 - Math.random() * 140,
                    life: 0.7, ttl: 0.7,
                    color: Math.random() < 0.5 ? '#fbbf24' : '#fff7d6',
                    size: 1.5 + Math.random() * 2,
                });
            }
        }

        function spawnMissCue(lane) {
            const H = flowCanvas.getBoundingClientRect().height;
            const cx = lane.x + lane.w / 2;
            for (let i = 0; i < 8; i++) {
                particles.push({
                    x: cx + (Math.random() - 0.5) * lane.w,
                    y: H - 2,
                    vx: (Math.random() - 0.5) * 80,
                    vy: -20 - Math.random() * 50,
                    life: 0.45, ttl: 0.45,
                    color: '#ef4444',
                    size: 1.5 + Math.random() * 1.5,
                });
            }
            lineFlashes.push({ x: lane.x, w: lane.w, life: 0.4, ttl: 0.4 });
        }

        let lastFrameMs = performance.now();

        function drawFlow() {
            const now = performance.now();
            const dt = Math.min((now - lastFrameMs) / 1000, 0.05);
            lastFrameMs = now;
            const rect = flowCanvas.getBoundingClientRect();
            const W = rect.width, H = rect.height;
            flowCtx.clearRect(0, 0, W, H);

            // Octave guides on the C keys.
            flowCtx.fillStyle = 'rgba(255, 255, 255, 0.08)';
            flowCtx.font = '10px sans-serif';
            for (const c of [36, 48, 60, 72]) {
                const lane = keyLane(c);
                if (!lane) continue;
                flowCtx.fillRect(lane.x, 0, 1, H);
                flowCtx.fillText('C' + (c / 12 - 1), lane.x + 4, H - 6);
            }

            // Upcoming file notes fall toward the line and sound on arrival.
            const seq = player && player.noteSequence;
            if (practice.active) {
                const t = practiceTime(now);

                // Trigger the muffled guide notes whose time has come.
                while (practice.schedIdx < practice.notes.length && practice.notes[practice.schedIdx].start <= t) {
                    const n = practice.notes[practice.schedIdx++];
                    if (muffledReady) {
                        muffled.triggerAttackRelease(
                            Tone.Frequency(n.pitch, 'midi').toNote(),
                            Math.max(n.end - n.start, 0.1));
                    }
                }

                for (const n of practice.notes) {
                    if (n.inRange && !n.matched && !n.missed && n.start < t - HIT_WINDOW) {
                        n.missed = true;
                        practice.missed++;
                        updatePracticeScore();
                    }
                    const yStart = H - (n.start - t) * FLOW_SPEED;
                    const yEnd = H - (n.end - t) * FLOW_SPEED;
                    if (yEnd > H || yStart < 0) continue;
                    const lane = keyLane(n.pitch);
                    if (!lane) continue;
                    flowCtx.fillStyle = n.matched ? '#34d399'
                        : n.missed ? 'rgba(156, 163, 175, 0.45)'
                        : FLOW_COLORS.file;
                    flowCtx.beginPath();
                    flowCtx.roundRect(lane.x + 1, yEnd, Math.max(lane.w - 2, 2),
                        Math.max(Math.min(yStart, H) - yEnd, 4), 3);
                    flowCtx.fill();
                }

                if (t > practice.endTime + 2) stopPractice();
            } else if (seq) {
                const t = playerTime(now);
                for (const note of seq.notes) {
                    const yStart = H - (note.startTime - t) * FLOW_SPEED; // note-on edge
                    const yEnd = H - (note.endTime - t) * FLOW_SPEED;     // note-off edge
                    if (yEnd > H || yStart < 0) continue; // already played / not yet visible
                    const lane = keyLane(note.pitch);
                    if (!lane) continue;
                    const sounding = t >= note.startTime && t < note.endTime;
                    flowCtx.fillStyle = sounding ? '#fbbf24' : FLOW_COLORS.file;
                    flowCtx.beginPath();
                    flowCtx.roundRect(lane.x + 1, yEnd, Math.max(lane.w - 2, 2),
                        Math.max(Math.min(yStart, H) - yEnd, 4), 3);
                    flowCtx.fill();
                }
            }

            // The "now" line the bars sound on.
            flowCtx.fillStyle = 'rgba(255, 255, 255, 0.25)';
            flowCtx.fillRect(0, H - 1, W, 1);

            // Hit sparkles / miss shards.
            particles = particles.filter(p => {
                p.life -= dt;
                if (p.life <= 0) return false;
                p.x += p.vx * dt;
                p.y += p.vy * dt;
                p.vy += 300 * dt; // gravity
                flowCtx.globalAlpha = p.life / p.ttl;
                flowCtx.fillStyle = p.color;
                flowCtx.fillRect(p.x - p.size / 2, p.y - p.size / 2, p.size, p.size);
                return true;
            });
            lineFlashes = lineFlashes.filter(f => {
                f.life -= dt;
                if (f.life <= 0) return false;
                flowCtx.globalAlpha = f.life / f.ttl;
                flowCtx.fillStyle = '#ef4444';
                flowCtx.fillRect(f.x, H - 4, f.w, 4);
                return true;
            });
            flowCtx.globalAlpha = 1;

            requestAnimationFrame(drawFlow);
        }

        // --- Recording state: {midi, onMs, offMs|null} in real time.
        let recorded = [];
        const activeKeys = new Set();
        const openNotes = new Map(); // midi -> index into recorded[]

        const recCount = document.getElementById('rec-count');
        const recSave = document.getElementById('rec-save');
        const recClear = document.getElementById('rec-clear');
        const recName = document.getElementById('rec-name');

        function updateRecUi() {
            recCount.textContent = recorded.length;
            recSave.disabled = recorded.length === 0;
        }

        function triggerNote(noteData) {
            if (activeKeys.has(noteData.midi)) return;
            activeKeys.add(noteData.midi);
            const keyEl = document.getElementById('key-' + noteData.midi);
            if (keyEl) keyEl.classList.add('active');
            playNote(noteData.note, noteData.octave);
            if (practice.active) practiceJudge(noteData.midi);
            openNotes.set(noteData.midi, recorded.length);
            recorded.push({ midi: noteData.midi, onMs: performance.now(), offMs: null });
            updateRecUi();
        }

        function releaseNote(noteData) {
            if (!activeKeys.has(noteData.midi)) return;
            activeKeys.delete(noteData.midi);
            const keyEl = document.getElementById('key-' + noteData.midi);
            if (keyEl) keyEl.classList.remove('active');
            const idx = openNotes.get(noteData.midi);
            if (idx !== undefined && recorded[idx]) recorded[idx].offMs = performance.now();
            openNotes.delete(noteData.midi);
        }

        // --- Build keyboard (ported from piano-studio.blade.php).
        function buildPiano() {
            const container = document.getElementById('piano');
            const whiteKeys = NOTES.filter(n => n.type === 'white');
            const blackKeys = NOTES.filter(n => n.type === 'black');
            const keysContainer = document.createElement('div');
            keysContainer.className = 'piano-keys-container';

            const bindPointer = (key, noteData) => {
                key.addEventListener('mousedown', (e) => { e.preventDefault(); triggerNote(noteData); });
                key.addEventListener('mouseup', () => releaseNote(noteData));
                key.addEventListener('mouseleave', () => releaseNote(noteData));
            };

            whiteKeys.forEach((noteData) => {
                const key = document.createElement('div');
                key.className = 'piano-key white-key';
                key.id = 'key-' + noteData.midi;
                if (noteData.key) {
                    const label = document.createElement('span');
                    label.className = 'key-label';
                    label.textContent = noteData.key.toUpperCase();
                    key.appendChild(label);
                }
                bindPointer(key, noteData);
                keysContainer.appendChild(key);
            });

            // White-key index that each black key sits after, per octave: C#,D#,F#,G#,A#.
            const blackKeyPositions = [
                0, 1, 3, 4, 5,
                7, 8, 10, 11, 12,
                14, 15, 17, 18, 19,
                21, 22, 24, 25, 26,
            ];
            const whiteKeyWidthPercent = 100 / whiteKeys.length;
            const blackKeyWidthPercent = whiteKeyWidthPercent * 0.6;

            blackKeys.forEach((noteData, i) => {
                const key = document.createElement('div');
                key.className = 'piano-key black-key';
                key.id = 'key-' + noteData.midi;
                key.style.left = (((blackKeyPositions[i] + 1) * whiteKeyWidthPercent) - blackKeyWidthPercent / 2) + '%';
                key.style.width = blackKeyWidthPercent + '%';
                if (noteData.key) {
                    const label = document.createElement('span');
                    label.className = 'key-label';
                    label.textContent = noteData.key.toUpperCase();
                    key.appendChild(label);
                }
                bindPointer(key, noteData);
                keysContainer.appendChild(key);
            });

            container.appendChild(keysContainer);
        }

        // --- Computer-keyboard shortcuts (same bindings as Piano Studio).
        document.addEventListener('keydown', (e) => {
            if (e.repeat || e.target.matches('input, textarea, select')) return;
            const noteData = NOTES.find(n => n.key === e.key.toLowerCase());
            if (noteData) { e.preventDefault(); triggerNote(noteData); }
        });
        document.addEventListener('keyup', (e) => {
            if (e.target.matches('input, textarea, select')) return;
            const noteData = NOTES.find(n => n.key === e.key.toLowerCase());
            if (noteData) releaseNote(noteData);
        });

        // --- Encode the recording as a format-0 Standard MIDI File at 120 BPM.
        function encodeMidi(notes) {
            const PPQ = 480, MS_PER_BEAT = 500;
            const toTicks = ms => Math.round(ms * PPQ / MS_PER_BEAT);
            const t0 = Math.min(...notes.map(n => n.onMs));

            const events = [];
            notes.forEach(n => {
                const on = toTicks(n.onMs - t0);
                const off = Math.max(on + 1, toTicks((n.offMs ?? n.onMs + 500) - t0));
                events.push({ tick: on, status: 0x90, midi: n.midi, vel: 90 });
                events.push({ tick: off, status: 0x80, midi: n.midi, vel: 0 });
            });
            // Note-offs first at equal ticks so retriggered pitches don't get cut.
            events.sort((a, b) => a.tick - b.tick || a.status - b.status);

            const vlq = (n) => {
                const out = [n & 0x7f];
                while ((n >>= 7) > 0) out.unshift((n & 0x7f) | 0x80);
                return out;
            };
            const u32 = n => [n >>> 24 & 255, n >>> 16 & 255, n >>> 8 & 255, n & 255];
            const u16 = n => [n >>> 8 & 255, n & 255];
            const ascii = s => [...s].map(c => c.charCodeAt(0));

            const track = [0x00, 0xFF, 0x51, 0x03, 0x07, 0xA1, 0x20]; // tempo: 500000 us/beat
            let prev = 0;
            events.forEach(e => {
                track.push(...vlq(e.tick - prev), e.status, e.midi, e.vel);
                prev = e.tick;
            });
            track.push(0x00, 0xFF, 0x2F, 0x00); // end of track

            return new Uint8Array([
                ...ascii('MThd'), ...u32(6), ...u16(0), ...u16(1), ...u16(PPQ),
                ...ascii('MTrk'), ...u32(track.length), ...track,
            ]);
        }

        async function saveRecording() {
            if (recorded.length === 0) return;
            recSave.disabled = true;
            recSave.textContent = 'Saving…';
            try {
                const base = (recName.value.trim() || 'piano-recording').replace(/\.midi?$/i, '');
                const formData = new FormData();
                formData.append('midi', new File([encodeMidi(recorded)], base + '.mid', { type: 'audio/midi' }));
                formData.append('_token', '{{ csrf_token() }}');
                const res = await fetch('{{ route('dev.midi.upload') }}', { method: 'POST', body: formData });
                window.location.href = res.url || '{{ route('dev.midi.index') }}';
            } catch (err) {
                recSave.textContent = 'Save as .mid';
                recSave.disabled = false;
                alert('Saving failed: ' + err.message);
            }
        }

        recSave.addEventListener('click', saveRecording);
        recClear.addEventListener('click', () => {
            recorded = [];
            openNotes.clear();
            updateRecUi();
        });

        // --- Light up piano keys while the selected file plays.
        const player = document.getElementById('midi-player-el');
        if (player) {
            player.addEventListener('note', (e) => {
                const n = e.detail && e.detail.note;
                if (!n) return;
                const ms = Math.max(150, ((n.endTime ?? n.startTime + 0.3) - n.startTime) * 1000);
                const keyEl = document.getElementById('key-' + n.pitch);
                if (!keyEl) return; // pitch outside the C2-B5 keyboard
                keyEl.classList.add('active');
                setTimeout(() => keyEl.classList.remove('active'), Math.min(ms, 2000));
            });
            player.addEventListener('stop', () => {
                document.querySelectorAll('.piano-key.active').forEach(k => k.classList.remove('active'));
            });
            // Practice needs the parsed sequence; the file loads asynchronously.
            player.addEventListener('load', () => { practiceBtn.disabled = false; });
            if (player.noteSequence) practiceBtn.disabled = false;
            // Starting normal playback takes over from practice.
            player.addEventListener('start', stopPractice);
        }

        buildPiano();
        resizeFlowCanvas();
        requestAnimationFrame(drawFlow);
    })();
    </script>
</body>
</html>
