<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Piano Game - {{ config('app.name', 'Ear Training Studio') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- VexFlow for music notation -->
    <script src="https://cdn.jsdelivr.net/npm/vexflow@4.2.2/build/cjs/vexflow.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#faf5ff',
                            100: '#f3e8ff',
                            200: '#e9d5ff',
                            300: '#d8b4fe',
                            400: '#c084fc',
                            500: '#a855f7',
                            600: '#9333ea',
                            700: '#7c3aed',
                            800: '#6b21a8',
                            900: '#581c87',
                        },
                        accent: {
                            400: '#fb923c',
                            500: '#f97316',
                            600: '#ea580c',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        .hero-gradient {
            background: linear-gradient(135deg, #9333ea 0%, #c084fc 35%, #f97316 100%);
        }
        .card {
            background: white;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #9333ea 0%, #7c3aed 100%);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #7c3aed 0%, #6b21a8 100%);
        }
        .nav-item {
            transition: all 0.2s ease;
        }
        .nav-item:hover {
            background: #f3f4f6;
        }
        .nav-item.active {
            background: #f3f4f6;
            font-weight: 600;
        }

        /* Piano Styles */
        .piano-container {
            display: flex;
            justify-content: center;
            position: relative;
            user-select: none;
        }
        
        .piano-key {
            cursor: pointer;
            transition: all 0.08s ease;
            position: relative;
        }
        
        .white-key {
            width: 48px;
            height: 180px;
            background: linear-gradient(180deg, #fefefe 0%, #f5f5f5 100%);
            border: 1px solid #d1d5db;
            border-radius: 0 0 6px 6px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1), inset 0 -2px 4px rgb(0 0 0 / 0.05);
            z-index: 1;
        }
        
        .white-key:hover {
            background: linear-gradient(180deg, #f0f0f0 0%, #e8e8e8 100%);
        }
        
        .white-key.active {
            background: linear-gradient(180deg, #c084fc 0%, #a855f7 100%);
            transform: translateY(2px);
            box-shadow: 0 2px 4px -1px rgb(0 0 0 / 0.1);
        }
        
        .black-key {
            width: 32px;
            height: 110px;
            background: linear-gradient(180deg, #374151 0%, #1f2937 100%);
            border-radius: 0 0 4px 4px;
            position: absolute;
            z-index: 2;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.3), inset 0 -4px 8px rgb(0 0 0 / 0.3);
        }
        
        .black-key:hover {
            background: linear-gradient(180deg, #4b5563 0%, #374151 100%);
        }
        
        .black-key.active {
            background: linear-gradient(180deg, #7c3aed 0%, #6b21a8 100%);
            transform: translateY(2px);
            height: 108px;
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

        .black-key .key-label {
            color: #9ca3af;
        }

        /* Notation container */
        #notation-container {
            background: white;
            border-radius: 12px;
            padding: 20px;
            min-height: 200px;
            overflow-x: auto;
            scroll-behavior: smooth;
        }

        #notation-container svg {
            display: block;
            margin: 0 auto;
        }

        /* Playback animation */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .playing {
            animation: pulse 0.5s ease-in-out infinite;
        }
    </style>
</head>
<body class="font-sans bg-gray-50 min-h-screen">
    <!-- Header/Navigation -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-purple-600 to-orange-500 flex items-center justify-center">
                        <i data-lucide="music" class="w-5 h-5 text-white"></i>
                    </div>
                    <span class="font-bold text-lg text-gray-900">Ear Training<br class="sm:hidden"><span class="text-purple-600"> Studio</span></span>
                </div>

                <!-- Navigation -->
                <nav class="hidden lg:flex items-center gap-1">
                    <a href="/dashboard" class="nav-item flex items-center gap-2 px-4 py-2 rounded-lg text-sm text-gray-600">
                        <i data-lucide="home" class="w-4 h-4"></i>
                        Home
                    </a>
                    <a href="/learn" class="nav-item flex items-center gap-2 px-4 py-2 rounded-lg text-sm text-gray-600">
                        <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                        Learn Path
                    </a>
                    <a href="/ai-exercises" class="nav-item flex items-center gap-2 px-4 py-2 rounded-lg text-sm text-gray-600">
                        <i data-lucide="sparkles" class="w-4 h-4"></i>
                        AI Exercises
                    </a>
                    <a href="/piano-game" class="nav-item active flex items-center gap-2 px-4 py-2 rounded-lg text-sm text-gray-700">
                        <i data-lucide="piano" class="w-4 h-4"></i>
                        Piano Game
                    </a>
                    <a href="/progress" class="nav-item flex items-center gap-2 px-4 py-2 rounded-lg text-sm text-gray-600">
                        <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
                        My Progress
                    </a>
                </nav>

                <!-- User Menu -->
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-semibold text-sm">
                            {{ substr(Auth::user()->name ?? 'M', 0, 1) }}
                        </div>
                        <span class="hidden sm:block text-sm font-medium text-gray-700">{{ Auth::user()->name ?? 'Guest' }}</span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                            <i data-lucide="log-out" class="w-5 h-5"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-600 to-orange-500 flex items-center justify-center">
                    <i data-lucide="music-2" class="w-6 h-6 text-white"></i>
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Piano Game</h1>
            </div>
            <p class="text-gray-600">Play the piano and see your notes rendered in real-time on the staff.</p>
        </div>

        <!-- Controls -->
        <div class="card p-4 mb-6">
            <div class="flex flex-wrap items-center gap-4">
                <button id="playbackBtn" class="inline-flex items-center gap-2 px-4 py-2 btn-primary text-white font-semibold rounded-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    <i data-lucide="play" class="w-4 h-4"></i>
                    <span>Playback</span>
                </button>
                <button id="clearBtn" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-all">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                    Clear
                </button>
                <div class="flex-1"></div>
                <div class="text-sm text-gray-500">
                    <span id="noteCount">0</span> notes recorded
                </div>
            </div>
        </div>

        <!-- Keyboard Shortcuts Guide -->
        <div class="card p-4 mb-6">
            <div class="flex items-center gap-2 mb-3">
                <i data-lucide="keyboard" class="w-5 h-5 text-purple-600"></i>
                <h3 class="font-semibold text-gray-900">Keyboard Shortcuts</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                <div>
                    <p class="font-medium text-gray-700 mb-1">Lower Octave (C3-B3)</p>
                    <p>White keys: <kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-xs">A</kbd> <kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-xs">S</kbd> <kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-xs">D</kbd> <kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-xs">F</kbd> <kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-xs">G</kbd> <kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-xs">H</kbd> <kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-xs">J</kbd></p>
                    <p>Black keys: <kbd class="px-1.5 py-0.5 bg-gray-800 text-white rounded text-xs">W</kbd> <kbd class="px-1.5 py-0.5 bg-gray-800 text-white rounded text-xs">E</kbd> <kbd class="px-1.5 py-0.5 bg-gray-800 text-white rounded text-xs">T</kbd> <kbd class="px-1.5 py-0.5 bg-gray-800 text-white rounded text-xs">Y</kbd> <kbd class="px-1.5 py-0.5 bg-gray-800 text-white rounded text-xs">U</kbd></p>
                </div>
                <div>
                    <p class="font-medium text-gray-700 mb-1">Upper Octave (C4-B4)</p>
                    <p>White keys: <kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-xs">K</kbd> <kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-xs">L</kbd> <kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-xs">;</kbd> <kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-xs">Z</kbd> <kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-xs">X</kbd> <kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-xs">C</kbd> <kbd class="px-1.5 py-0.5 bg-gray-100 rounded text-xs">V</kbd></p>
                    <p>Black keys: <kbd class="px-1.5 py-0.5 bg-gray-800 text-white rounded text-xs">O</kbd> <kbd class="px-1.5 py-0.5 bg-gray-800 text-white rounded text-xs">P</kbd> <kbd class="px-1.5 py-0.5 bg-gray-800 text-white rounded text-xs">.</kbd> <kbd class="px-1.5 py-0.5 bg-gray-800 text-white rounded text-xs">/</kbd> <kbd class="px-1.5 py-0.5 bg-gray-800 text-white rounded text-xs">'</kbd></p>
                </div>
            </div>
        </div>

        <!-- Music Notation Display -->
        <div class="card mb-6">
            <div class="p-4 border-b border-gray-200">
                <div class="flex items-center gap-2">
                    <i data-lucide="music-2" class="w-5 h-5 text-purple-600"></i>
                    <h3 class="font-semibold text-gray-900">Music Notation</h3>
                </div>
            </div>
            <div id="notation-container">
                <div id="notation-output"></div>
                <p id="notation-placeholder" class="text-center text-gray-400 py-12">Play some notes to see them appear here...</p>
            </div>
        </div>

        <!-- Piano Keyboard -->
        <div class="card p-6">
            <div class="piano-container" id="piano">
                <!-- Piano keys will be generated by JavaScript -->
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-8">
                <!-- Brand -->
                <div class="col-span-2 md:col-span-4 lg:col-span-1">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-purple-600 to-orange-500 flex items-center justify-center">
                            <i data-lucide="music" class="w-5 h-5 text-white"></i>
                        </div>
                        <span class="font-bold text-lg text-white">Ear Training Studio</span>
                    </div>
                    <p class="text-sm mb-4">
                        An AI-powered ear training platform for musicians, students, and educators. Master your musical ear with personalized exercises.
                    </p>
                </div>

                <!-- Platform -->
                <div>
                    <h4 class="font-semibold text-white mb-4">Platform</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="/dashboard" class="hover:text-white transition-colors">Home</a></li>
                        <li><a href="/learn" class="hover:text-white transition-colors">Learning Path</a></li>
                        <li><a href="/piano-game" class="hover:text-white transition-colors">Piano Game</a></li>
                    </ul>
                </div>

                <!-- Resources -->
                <div>
                    <h4 class="font-semibold text-white mb-4">Resources</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-white transition-colors">All Resources</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Articles</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">FAQ & Help Center</a></li>
                    </ul>
                </div>

                <!-- Company -->
                <div>
                    <h4 class="font-semibold text-white mb-4">Company</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-white transition-colors">About Us</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Contact Support</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Privacy & Terms</a></li>
                    </ul>
                </div>
            </div>

            <!-- Copyright -->
            <div class="border-t border-gray-800 mt-8 pt-8">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p class="text-sm text-gray-500">© {{ date('Y') }} Ear Training Studio. All rights reserved.</p>
                    <div class="flex items-center gap-6 text-sm">
                        <a href="#" class="hover:text-white transition-colors">Privacy Policy</a>
                        <a href="#" class="hover:text-white transition-colors">Terms of Service</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Initialize Lucide Icons -->
    <script>
        lucide.createIcons();
    </script>

    <!-- Piano Game Logic -->
    <script>
        // Piano configuration - 2 octaves from C3 to B4
        const NOTES = [
            // Lower octave (C3-B3)
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
            // Upper octave (C4-B4)
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
        ];

        // State
        let recordedNotes = [];
        let isPlaying = false;
        let activeKeys = new Set();

        // DOM Elements
        const pianoContainer = document.getElementById('piano');
        const playbackBtn = document.getElementById('playbackBtn');
        const clearBtn = document.getElementById('clearBtn');
        const noteCountEl = document.getElementById('noteCount');
        const notationOutput = document.getElementById('notation-output');
        const notationPlaceholder = document.getElementById('notation-placeholder');

        // Play a note using cached audio files (falls back to API if not cached)
        function playNote(note, octave, duration = 1) {
            // Format note name for cached file (e.g., "C#" -> "Cs")
            const noteName = note.replace('#', 's');
            const cachedUrl = `/audio/piano/${noteName}${octave}_d${duration}.mp3`;
            
            const audio = new Audio(cachedUrl);
            
            // Try cached file first, fall back to API if it fails
            audio.play().catch(err => {
                console.log('Cached audio not found, falling back to API...');
                const apiNoteName = note.replace('#', '%23');
                const apiUrl = `https://mithatck.com/music/api/note.php?note=${apiNoteName}${octave}&duration=${duration}`;
                const apiAudio = new Audio(apiUrl);
                apiAudio.play().catch(apiErr => {
                    console.log('Audio playback failed:', apiErr);
                });
            });
            
            return audio;
        }

        // Build piano keyboard
        function buildPiano() {
            const whiteKeys = NOTES.filter(n => n.type === 'white');
            const blackKeys = NOTES.filter(n => n.type === 'black');
            
            // Create white keys container
            const whiteKeysContainer = document.createElement('div');
            whiteKeysContainer.style.display = 'flex';
            whiteKeysContainer.style.position = 'relative';
            
            whiteKeys.forEach((noteData, index) => {
                const key = document.createElement('div');
                key.className = 'piano-key white-key';
                key.dataset.note = noteData.note;
                key.dataset.octave = noteData.octave;
                key.dataset.midi = noteData.midi;
                key.id = `key-${noteData.midi}`;
                
                const label = document.createElement('span');
                label.className = 'key-label';
                label.textContent = noteData.key.toUpperCase();
                key.appendChild(label);
                
                key.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    triggerNote(noteData);
                });
                key.addEventListener('mouseup', () => releaseNote(noteData));
                key.addEventListener('mouseleave', () => releaseNote(noteData));
                
                whiteKeysContainer.appendChild(key);
            });
            
            // Add black keys
            const blackKeyPositions = [0, 1, 3, 4, 5, 7, 8, 10, 11, 12]; // Positions after white keys
            let blackKeyIndex = 0;
            
            blackKeys.forEach((noteData) => {
                const key = document.createElement('div');
                key.className = 'piano-key black-key';
                key.dataset.note = noteData.note;
                key.dataset.octave = noteData.octave;
                key.dataset.midi = noteData.midi;
                key.id = `key-${noteData.midi}`;
                
                // Position black key
                const whiteKeyWidth = 48;
                const blackKeyWidth = 32;
                const position = blackKeyPositions[blackKeyIndex];
                key.style.left = `${(position + 1) * whiteKeyWidth - blackKeyWidth / 2}px`;
                
                const label = document.createElement('span');
                label.className = 'key-label';
                label.textContent = noteData.key.toUpperCase();
                key.appendChild(label);
                
                key.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    triggerNote(noteData);
                });
                key.addEventListener('mouseup', () => releaseNote(noteData));
                key.addEventListener('mouseleave', () => releaseNote(noteData));
                
                whiteKeysContainer.appendChild(key);
                blackKeyIndex++;
            });
            
            pianoContainer.appendChild(whiteKeysContainer);
        }

        // Trigger a note (play sound, visual feedback, record)
        function triggerNote(noteData) {
            if (activeKeys.has(noteData.midi)) return;
            
            activeKeys.add(noteData.midi);
            
            const keyEl = document.getElementById(`key-${noteData.midi}`);
            if (keyEl) keyEl.classList.add('active');
            
            playNote(noteData.note, noteData.octave, 1);
            
            // Record the note
            recordedNotes.push({
                ...noteData,
                timestamp: Date.now()
            });
            
            updateNoteCount();
            renderNotation();
        }

        // Release a note (visual feedback)
        function releaseNote(noteData) {
            activeKeys.delete(noteData.midi);
            
            const keyEl = document.getElementById(`key-${noteData.midi}`);
            if (keyEl) keyEl.classList.remove('active');
        }

        // Update note count display
        function updateNoteCount() {
            noteCountEl.textContent = recordedNotes.length;
            playbackBtn.disabled = recordedNotes.length === 0;
        }

        // Render notation using VexFlow
        function renderNotation() {
            if (recordedNotes.length === 0) {
                notationOutput.innerHTML = '';
                notationPlaceholder.style.display = 'block';
                return;
            }
            
            notationPlaceholder.style.display = 'none';
            notationOutput.innerHTML = '';
            
            const VF = Vex.Flow;
            
            // Calculate width based on number of notes
            const notesPerMeasure = 4;
            const measureWidth = 250;
            const numMeasures = Math.ceil(recordedNotes.length / notesPerMeasure);
            const totalWidth = Math.max(measureWidth * numMeasures, 600);
            
            const renderer = new VF.Renderer(notationOutput, VF.Renderer.Backends.SVG);
            renderer.resize(totalWidth, 150);
            const context = renderer.getContext();
            context.setFont('Arial', 10);
            
            // Create stave notes from recorded notes
            const staveNotes = recordedNotes.map(noteData => {
                const noteName = noteData.note.replace('#', '#');
                const vexNote = `${noteName.toLowerCase()}/${noteData.octave}`;
                
                const staveNote = new VF.StaveNote({
                    keys: [vexNote],
                    duration: 'q',
                    clef: 'treble'
                });
                
                // Add accidental if needed
                if (noteData.note.includes('#')) {
                    staveNote.addModifier(new VF.Accidental('#'), 0);
                }
                
                return staveNote;
            });
            
            // Split into measures
            const measures = [];
            for (let i = 0; i < staveNotes.length; i += notesPerMeasure) {
                measures.push(staveNotes.slice(i, i + notesPerMeasure));
            }
            
            // Render each measure
            let xPos = 10;
            measures.forEach((measureNotes, measureIndex) => {
                const stave = new VF.Stave(xPos, 20, measureWidth);
                
                if (measureIndex === 0) {
                    stave.addClef('treble');
                }
                
                stave.setContext(context).draw();
                
                // Pad with rests if needed to fill measure
                while (measureNotes.length < notesPerMeasure) {
                    measureNotes.push(new VF.StaveNote({
                        keys: ['b/4'],
                        duration: 'qr'
                    }));
                }
                
                const voice = new VF.Voice({ num_beats: 4, beat_value: 4 });
                voice.addTickables(measureNotes);
                
                new VF.Formatter().joinVoices([voice]).format([voice], measureWidth - 50);
                voice.draw(context, stave);
                
                xPos += measureWidth;
            });
            
            // Auto-scroll to the right to show the latest notes
            const notationContainer = document.getElementById('notation-container');
            notationContainer.scrollLeft = notationContainer.scrollWidth;
        }

        // Playback recorded notes
        async function playback() {
            if (recordedNotes.length === 0 || isPlaying) return;
            
            isPlaying = true;
            playbackBtn.classList.add('playing');
            playbackBtn.innerHTML = '<i data-lucide="pause" class="w-4 h-4"></i><span>Playing...</span>';
            lucide.createIcons();
            
            for (let i = 0; i < recordedNotes.length; i++) {
                if (!isPlaying) break;
                
                const noteData = recordedNotes[i];
                const nextNote = recordedNotes[i + 1];
                
                // Visual feedback
                const keyEl = document.getElementById(`key-${noteData.midi}`);
                if (keyEl) keyEl.classList.add('active');
                
                playNote(noteData.note, noteData.octave, 1);
                
                // Calculate delay until next note
                let delay = 500; // Default delay
                if (nextNote) {
                    delay = Math.min(Math.max(nextNote.timestamp - noteData.timestamp, 200), 1500);
                }
                
                await new Promise(resolve => setTimeout(resolve, delay));
                
                if (keyEl) keyEl.classList.remove('active');
            }
            
            isPlaying = false;
            playbackBtn.classList.remove('playing');
            playbackBtn.innerHTML = '<i data-lucide="play" class="w-4 h-4"></i><span>Playback</span>';
            lucide.createIcons();
        }

        // Clear recorded notes
        function clearNotes() {
            recordedNotes = [];
            updateNoteCount();
            renderNotation();
        }

        // Keyboard event handlers
        function handleKeyDown(e) {
            if (e.repeat) return;
            
            const key = e.key.toLowerCase();
            const noteData = NOTES.find(n => n.key === key);
            
            if (noteData) {
                e.preventDefault();
                triggerNote(noteData);
            }
        }

        function handleKeyUp(e) {
            const key = e.key.toLowerCase();
            const noteData = NOTES.find(n => n.key === key);
            
            if (noteData) {
                releaseNote(noteData);
            }
        }

        // Event listeners
        document.addEventListener('keydown', handleKeyDown);
        document.addEventListener('keyup', handleKeyUp);
        playbackBtn.addEventListener('click', playback);
        clearBtn.addEventListener('click', clearNotes);

        // Initialize piano on page load
        document.addEventListener('DOMContentLoaded', () => {
            buildPiano();
        });
    </script>
</body>
</html>
