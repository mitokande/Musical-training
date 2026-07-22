@extends('layouts.standalone')

@section('title', 'How Harmoniva Works — The Complete Guide')
@section('description', 'A complete, section-by-section guide to Harmoniva — getting started, the Structured Learning Path, all 10 practice exercises, the Exercise Setup Studio, AI tools, Piano Studio, music games, progress tracking, teachers, schools, and plans.')

@section('head')
<style>[x-cloak]{display:none!important}</style>
@endsection

@section('structured-data')
    @php
        // Built inside @php so Blade does not compile the "@context"/"@type"
        // literal keys as directives and corrupt the JSON.
        $howItWorksJsonLd = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => 'How Harmoniva Works — The Complete Guide',
            'description' => 'A complete, section-by-section guide to Harmoniva — the Learning Path, all 10 practice exercises, the Exercise Setup Studio, AI tools, Piano Studio, games, teachers, schools, and plans.',
            'about' => 'Harmoniva ear training platform',
            'inLanguage' => str_replace('_', '-', app()->getLocale()),
            'image' => asset('images/og-image.png'),
            'author' => ['@id' => url('/').'#organization'],
            'publisher' => ['@id' => url('/').'#organization'],
            'mainEntityOfPage' => route('page.how-it-works'),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $howItWorksBreadcrumbJsonLd = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'How It Works', 'item' => route('page.how-it-works')],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    @endphp
    <script type="application/ld+json">{!! $howItWorksJsonLd !!}</script>
    <script type="application/ld+json">{!! $howItWorksBreadcrumbJsonLd !!}</script>
@endsection

@section('content')

{{-- ============ HERO ============ --}}
<section class="bg-gradient-to-br from-purple-700 via-purple-600 to-purple-800 text-white py-20 px-4 relative overflow-hidden">
    <div class="absolute inset-0 opacity-10" style="background-image:radial-gradient(circle at 20% 30%, #fff 0, transparent 40%), radial-gradient(circle at 80% 70%, #f97316 0, transparent 40%);"></div>
    <div class="max-w-3xl mx-auto text-center reveal relative">
        <div class="hero-badge inline-flex items-center gap-2 bg-white/10 text-white text-sm font-medium px-4 py-2 rounded-full mb-6">
            <i data-lucide="compass" class="w-4 h-4"></i>
            How it Works
        </div>
        <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold mb-5">The Complete Guide to Harmoniva</h1>
        <p class="text-purple-200 text-lg sm:text-xl max-w-2xl mx-auto leading-relaxed">Every section of the app, explained simply — what it does, how to use it, and where to find it. Find your way around in minutes.</p>
        <div class="flex flex-wrap justify-center gap-4 mt-8 text-sm text-purple-200">
            <span class="flex items-center gap-1.5"><i data-lucide="clock" class="w-4 h-4"></i> 12 min read</span>
            <span class="flex items-center gap-1.5"><i data-lucide="layout-grid" class="w-4 h-4"></i> 15 sections</span>
            <span class="flex items-center gap-1.5"><i data-lucide="graduation-cap" class="w-4 h-4"></i> All levels</span>
        </div>
    </div>
</section>

{{-- ============ TABLE OF CONTENTS ============ --}}
<section class="bg-white border-b border-gray-100 py-8 px-4">
    <div class="max-w-4xl mx-auto reveal">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-4">On this page</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @php
            $toc = [
                ['n'=>1,'t'=>'Getting Started','ic'=>'rocket'],
                ['n'=>2,'t'=>'Your Dashboard','ic'=>'layout-dashboard'],
                ['n'=>3,'t'=>'Structured Learning Path','ic'=>'route'],
                ['n'=>4,'t'=>'Practice Exercises','ic'=>'music'],
                ['n'=>5,'t'=>'Exercise Setup Studio','ic'=>'sliders-horizontal'],
                ['n'=>6,'t'=>'AI-Powered Training','ic'=>'sparkles'],
                ['n'=>7,'t'=>'Virtual Piano Studio','ic'=>'piano'],
                ['n'=>8,'t'=>'Music Games','ic'=>'gamepad-2'],
                ['n'=>9,'t'=>'Progress & Achievements','ic'=>'trending-up'],
                ['n'=>10,'t'=>'Find Teachers','ic'=>'users'],
                ['n'=>11,'t'=>'Community Feed','ic'=>'rss'],
                ['n'=>12,'t'=>'Assignments, Messages & Lessons','ic'=>'clipboard-list'],
                ['n'=>13,'t'=>'Harmoniva for Teachers','ic'=>'briefcase'],
                ['n'=>14,'t'=>'Harmoniva for Music Schools','ic'=>'building-2'],
                ['n'=>15,'t'=>'Plans & Access','ic'=>'tag'],
            ];
            @endphp
            @foreach($toc as $item)
            <a href="#section-{{ $item['n'] }}" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-gray-50 hover:bg-purple-50 hover:text-purple-700 transition-colors group">
                <span class="w-8 h-8 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="{{ $item['ic'] }}" class="w-4 h-4"></i>
                </span>
                <span class="text-sm font-medium text-gray-700 group-hover:text-purple-700 leading-tight">{{ $item['n'] }}. {{ $item['t'] }}</span>
            </a>
            @endforeach
        </div>
    </div>
</section>

{{-- ============ SECTIONS ============ --}}
<div class="bg-[#FAF7F2] py-16 px-4">
    <div class="max-w-3xl mx-auto space-y-16 sm:space-y-20">

        @php
        if (!function_exists('guideBtn')) {
        function guideBtn($url, $label, $icon = 'arrow-right') {
            return '<a href="'.$url.'" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl text-white font-semibold text-sm shadow-lg shadow-purple-500/25 hover:-translate-y-0.5 transition-all w-full sm:w-auto" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">'.$label.' <i data-lucide="'.$icon.'" class="w-4 h-4"></i></a>';
        }
        }
        if (!function_exists('guideBadge')) {
        function guideBadge($label, $tone = 'free') {
            $tones = [
                'free' => 'bg-green-100 text-green-700',
                'premium' => 'bg-purple-100 text-purple-700',
                'teacher' => 'bg-blue-100 text-blue-700',
                'school' => 'bg-amber-100 text-amber-700',
                'guest' => 'bg-gray-100 text-gray-600',
            ];
            return '<span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider '.($tones[$tone] ?? $tones['free']).'">'.$label.'</span>';
        }
        }
        // Access numbers come from the central plan config so the guide never drifts from the real limits.
        $freePlan   = config('plans.user.free');
        $teacherFree = config('plans.teacher.free');
        $schoolFree  = config('plans.school.free');
        $guestPlaysPerGame = (int) config('plans.guest.games_daily_plays_per_type', 1);
        $guestPlaysTotal   = $guestPlaysPerGame * count(\App\Http\Controllers\GameController::GAMES);
        // Each section header links straight to the feature it describes.
        $sectionLinks = [
            1 => route('register'),
            2 => route('dashboard'),
            3 => route('learn'),
            4 => route('exercise-setup.index'),
            5 => route('exercise-setup.index'),
            6 => route('ai.exercises'),
            7 => route('piano.studio'),
            8 => route('games.index'),
            9 => route('progress'),
            10 => route('teachers.directory'),
            11 => route('feed'),
            12 => route('assignments.index'),
            13 => route('page.teachers-solution'),
            14 => route('page.schools'),
            15 => route('pricing.index'),
        ];
        @endphp

        {{-- ═══════════ 1. GETTING STARTED ═══════════ --}}
        <article id="section-1" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[1] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">1</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">Getting Started</h2>
                    <p class="text-gray-500 text-sm">What Harmoniva is, the account types, and your first five minutes.</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-4">Harmoniva is an ear-training platform for musicians of every level — students practising on their own, teachers running a studio, and music schools managing whole classes. You listen, you answer, and you get instant feedback, with real staff notation on screen so your eyes and ears learn together.</p>

            <p class="text-gray-600 leading-relaxed mb-6">When you sign up (with email or Google), you choose one of three account types — these match what you'll see on the registration page:</p>

            {{-- account type cards --}}
            <div class="grid sm:grid-cols-3 gap-3 mb-6">
                <div class="light-card rounded-2xl p-4">
                    <div class="w-9 h-9 rounded-lg bg-green-100 text-green-600 flex items-center justify-center mb-2"><i data-lucide="user" class="w-4 h-4"></i></div>
                    <p class="font-bold text-gray-900 text-sm">Student Portal</p>
                    <p class="text-xs text-gray-500 mt-1">Personal learning dashboard — practise, track progress, and optionally connect with teachers.</p>
                </div>
                <div class="light-card rounded-2xl p-4">
                    <div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center mb-2"><i data-lucide="briefcase" class="w-4 h-4"></i></div>
                    <p class="font-bold text-gray-900 text-sm">Teacher Portal</p>
                    <p class="text-xs text-gray-500 mt-1">Complete teaching CRM — students, assignments, and a public teacher profile.</p>
                </div>
                <div class="light-card rounded-2xl p-4">
                    <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center mb-2"><i data-lucide="building-2" class="w-4 h-4"></i></div>
                    <p class="font-bold text-gray-900 text-sm">Music School Portal</p>
                    <p class="text-xs text-gray-500 mt-1">Multi-teacher institution hub — manage teachers, students, and school-wide activity.</p>
                </div>
            </div>

            <div class="bg-purple-50/60 border border-purple-100 rounded-xl p-5 mb-6">
                <p class="text-xs font-bold text-purple-700 uppercase tracking-wider mb-3">Your first five minutes</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">1.</span><span>Create a free account and verify your email — no credit card needed.</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">2.</span><span>Open the <strong>Learning Path</strong> for a guided start, or jump straight into any practice exercise.</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">3.</span><span>Press the play button to hear each question — you can replay it as often as you like. Headphones are recommended, especially for harmonic exercises.</span></li>
                </ul>
            </div>

            <p class="text-gray-600 text-sm leading-relaxed mb-6">{!! guideBadge('Guest','guest') !!} You can explore everything without an account: {{ config('plans.guest.learning_path_daily_sessions') }} Learning Path sessions and {{ config('plans.guest.studio_daily_sessions') }} Exercise Studio sessions per day (5 questions each), each game once per day (level 1), and the Piano Studio without limits. Guest limits renew daily. You'll need a free account to save scores, unlock higher levels, build streaks, and track progress.</p>

            {!! guideBtn(route('register'), 'Create a Free Account', 'user-plus') !!}

            @include('pages.partials.guide-faq', ['faqs' => [
                ['q'=>'Do I need an account to try Harmoniva?','a'=>'No — you can open practice exercises, play the virtual piano, and try each game once as a guest. An account (free) is what saves your progress, scores, and streaks.'],
                ['q'=>'Which account type should I choose?','a'=>'Most people start with the Student Portal. Choose Teacher or Music School if you teach — and if you start as a student, you can still open a teacher account later from your existing account.'],
                ['q'=>'Is it really free to start?','a'=>'Yes. The free plan includes the Structured Learning Path, all practice exercise types (with daily limits), the Exercise Setup Studio, games, and basic progress tracking. No credit card required.'],
            ]])
        </article>

        {{-- ═══════════ 2. DASHBOARD ═══════════ --}}
        <article id="section-2" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[2] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">2</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">Your Dashboard</h2>
                    <p class="text-gray-500 text-sm">Your home base — a snapshot of everything at a glance.</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-6">The moment you sign in, the Dashboard shows your day streak, today's practice goal, minutes logged, experience earned, and achievements. A <strong>Skill Mastery</strong> panel summarises how you're doing across skills, and <strong>Quick Actions</strong> take you to the Learning Path, Exercise Setup, AI Exercises, Music Assistant, and Piano Studio in one click — plus a shortcut to continue where you left off.</p>

            {{-- visual mockup --}}
            <div class="light-card rounded-2xl p-5 mb-6">
                <div class="flex flex-wrap items-center justify-between gap-y-2 mb-4">
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider">Welcome back</p>
                        <p class="font-bold text-gray-900">Ready to train your ears?</p>
                    </div>
                    <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-orange-50 text-orange-600 text-sm font-bold">
                        <i data-lucide="flame" class="w-4 h-4"></i> 7-day streak
                    </div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach([['Learn','route','purple'],['Studio','sliders-horizontal','purple'],['AI Mode','sparkles','orange'],['Piano','piano','purple']] as $q)
                    <div class="rounded-xl bg-gray-50 p-3 text-center">
                        <div class="w-9 h-9 mx-auto rounded-lg bg-{{ $q[2] }}-100 text-{{ $q[2] }}-600 flex items-center justify-center mb-2"><i data-lucide="{{ $q[1] }}" class="w-4 h-4"></i></div>
                        <p class="text-xs font-semibold text-gray-700">{{ $q[0] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-purple-50/60 border border-purple-100 rounded-xl p-5 mb-6">
                <p class="text-xs font-bold text-purple-700 uppercase tracking-wider mb-3">How to use it</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">1.</span><span>Check your streak and today's practice goal to stay consistent.</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">2.</span><span>Use the Quick Actions tiles to open any part of the app in one click.</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">3.</span><span>Glance at Skill Mastery to see which skills deserve attention next.</span></li>
                </ul>
            </div>

            {!! guideBtn(route('dashboard'), 'Open Dashboard') !!}

            @include('pages.partials.guide-faq', ['faqs' => [
                ['q'=>'Do I need an account to see the Dashboard?','a'=>'Yes — the Dashboard is personal, so you\'ll need a free account. Sign-up takes under a minute.'],
                ['q'=>'What is the streak counter?','a'=>'It tracks how many days in a row you\'ve practised. Completing at least one exercise a day keeps it alive — a simple, proven way to build a habit.'],
                ['q'=>'What is the daily practice goal?','a'=>'A daily minutes target shown on the Dashboard. As you practise, the bar fills up so you can see exactly how close you are to today\'s goal.'],
            ]])
        </article>

        {{-- ═══════════ 3. STRUCTURED LEARNING PATH ═══════════ --}}
        <article id="section-3" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[3] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">3</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">Structured Learning Path</h2>
                    <p class="text-gray-500 text-sm">A guided, step-by-step curriculum from beginner to advanced. {!! guideBadge('Free') !!}</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-4">If you don't know where to start, start here. The Learning Path is a pre-built curriculum of short, focused lessons for each skill area — intervals, chords, scales, rhythm, dictation and more. Skills are introduced progressively: each lesson builds on the concepts you've already learned, and completing a lesson unlocks the next one in the sequence.</p>

            <p class="text-gray-600 leading-relaxed mb-6"><strong>Note:</strong> this structured curriculum is different from the AI-personalised training described in <a href="#section-6" class="text-purple-600 font-semibold hover:underline">section 6</a>. The Learning Path is the same fixed, expert-designed sequence for everyone; the AI tools build personalised sessions and weekly plans around <em>your</em> results.</p>

            {{-- visual: lesson track --}}
            <div class="light-card rounded-2xl p-5 mb-6">
                <div class="space-y-3">
                    @foreach([['Perfect intervals','done'],['Major & minor 2nds','done'],['Major & minor 3rds','active'],['Tritone & beyond','locked']] as $l)
                    <div class="flex items-center gap-3 p-3 rounded-xl {{ $l[1]==='active' ? 'bg-purple-50 border border-purple-200' : 'bg-gray-50' }}">
                        <span class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 {{ $l[1]==='done' ? 'bg-green-100 text-green-600' : ($l[1]==='active' ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-400') }}">
                            <i data-lucide="{{ $l[1]==='done' ? 'check' : ($l[1]==='active' ? 'play' : 'lock') }}" class="w-4 h-4"></i>
                        </span>
                        <span class="text-sm font-medium {{ $l[1]==='locked' ? 'text-gray-400' : 'text-gray-800' }}">{{ $l[0] }}</span>
                        @if($l[1]==='active')<span class="ml-auto text-xs font-bold text-purple-600">Continue →</span>@endif
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-purple-50/60 border border-purple-100 rounded-xl p-5 mb-6">
                <p class="text-xs font-bold text-purple-700 uppercase tracking-wider mb-3">How to use it</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">1.</span><span>Open <strong>Learn</strong> and pick the next unlocked lesson in the skill area you want to develop.</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">2.</span><span>Read the short intro, then press <strong>Start</strong> to begin the questions.</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">3.</span><span>Complete the lesson to unlock the next step — retry as many times as you need.</span></li>
                </ul>
            </div>

            {!! guideBtn(route('learn'), 'Open the Learning Path') !!}

            @include('pages.partials.guide-faq', ['faqs' => [
                ['q'=>'Do lessons have to be done in order?','a'=>'Within each skill area, yes — each lesson unlocks the next, so difficulty rises smoothly instead of throwing advanced material at you too early. You can work on several skill areas in parallel.'],
                ['q'=>'What happens if I don\'t pass a lesson?','a'=>'Nothing bad — you simply retry. The path is designed for practice, not punishment, so you can attempt any lesson as many times as you need.'],
                ['q'=>'Is the Learning Path free?','a'=>'Yes. Learning Path lessons are part of the free plan: up to '.$freePlan['learning_path_daily_sessions'].' sessions per day, '.$freePlan['session_question_cap'].' questions each. Guests can also try '.config('plans.guest.learning_path_daily_sessions').' sessions per day. Premium removes the daily limits.'],
                ['q'=>'How is this different from the AI Learning Path?','a'=>'The Structured Learning Path is the same expert-designed sequence for every user. The AI tools (a Premium feature) analyse your own results to generate personalised sessions and a weekly practice plan — see the AI-Powered Training section.'],
            ]])
        </article>

        {{-- ═══════════ 4. PRACTICE EXERCISES ═══════════ --}}
        <article id="section-4" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[4] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">4</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">Practice Exercises</h2>
                    <p class="text-gray-500 text-sm">The ten core ear-training drills — what each one is and what it trains.</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-6">These are the heart of Harmoniva. Every exercise plays a sound, shows real staff notation, and gives instant feedback on your answer — green for correct, red with the right answer revealed. You answer with labelled buttons or an on-screen piano keyboard, so you don't need to read music to begin. Each card below opens that exercise directly:</p>

            {{-- exercise cards: what it is + what it trains + direct link --}}
            <div class="grid sm:grid-cols-2 gap-3 mb-6">
                @foreach([
                    ['Single Note','music-2','single-note-practice','Hear one note and identify it. Trains pitch recognition and pitch memory — the foundation every other skill builds on.'],
                    ['Melodic Interval','trending-up','melodic-interval-practice','Two notes play one after another; name the interval between them. Trains relative pitch, the core skill for learning melodies by ear.'],
                    ['Harmonic Interval','layers','harmonic-interval-practice','Two notes play at the same time; name the interval. Trains harmonic hearing — headphones strongly recommended.'],
                    ['Interval Direction','arrow-up-down','interval-direction-practice','Two notes play in sequence, starting from the first note. Decide whether the second note is higher (ascending), lower (descending), or the same pitch. Trains melodic contour perception.'],
                    ['Interval Comparison','git-compare','interval-comparison-practice','Hear two intervals and decide which one is wider. Trains fine discrimination of interval size.'],
                    ['Interval Construction','wrench','interval-construction-practice','Given a starting note and a target interval, build the correct second note. Turns passive recognition into active interval knowledge.'],
                    ['Chords','grid-3x3','chord-practice','Hear a chord and identify its type — from major and minor triads to seventh chords and inversions. Trains harmony recognition.'],
                    ['Scales','list-music','scale-practice','Hear a scale and identify it — major, minor variants, the modes, pentatonic and more. Trains tonal and modal hearing.'],
                    ['Rhythm','activity','rhythm-practice','Listen to a rhythm and match it to the correct notation across different meters and note values. Trains rhythmic reading and inner pulse.'],
                    ['Melodic Dictation','pen-line','melodic-dictation','Hear a short melody and reconstruct it — pitches and rhythm. The most complete ear-training workout, combining every other skill.'],
                ] as $ex)
                <a href="{{ route('practice', $ex[2]) }}" class="light-card rounded-2xl p-4 group hover:border-purple-300 hover:shadow-md hover:-translate-y-0.5 transition-all flex flex-col">
                    <div class="flex items-center gap-2.5 mb-2">
                        <span class="w-8 h-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center flex-shrink-0"><i data-lucide="{{ $ex[1] }}" class="w-4 h-4"></i></span>
                        <span class="text-sm font-bold text-gray-900 group-hover:text-purple-700 transition-colors">{{ $ex[0] }}</span>
                    </div>
                    <p class="text-xs text-gray-500 leading-relaxed flex-1">{{ $ex[3] }}</p>
                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-purple-600 mt-3">Try {{ $ex[0] }} <i data-lucide="arrow-right" class="w-3 h-3 transition-transform group-hover:translate-x-0.5"></i></span>
                </a>
                @endforeach
            </div>

            <p class="text-gray-600 text-sm leading-relaxed mb-6">{!! guideBadge('Free') !!} All ten exercise types are on the free plan, with up to {{ $freePlan['learning_path_daily_sessions'] }} Learning Path and {{ $freePlan['studio_daily_sessions'] }} Studio sessions per day ({{ $freePlan['session_question_cap'] }} questions each). {!! guideBadge('Premium','premium') !!} Premium removes the daily limits.</p>

            @include('pages.partials.guide-faq', ['faqs' => [
                ['q'=>'Can I replay the sound?','a'=>'Absolutely. Press play as many times as you like before answering — there\'s no penalty for re-listening. Repetition is how your ear learns.'],
                ['q'=>'What do harmonic vs. melodic mean?','a'=>'Melodic means the notes play one after another; harmonic means they play at the same time. Harmonic exercises are trickier, so headphones are recommended.'],
                ['q'=>'Is there a daily limit?','a'=>'Free accounts get '.$freePlan['learning_path_daily_sessions'].' Learning Path sessions and '.$freePlan['studio_daily_sessions'].' Studio sessions per day ('.$freePlan['session_question_cap'].' questions each). Premium unlocks unlimited practice across every type.'],
                ['q'=>'Do I need to read music?','a'=>'No. You can answer with labelled buttons or a piano keyboard, so you can train purely by ear while gradually picking up notation from the on-screen staff.'],
            ]])
        </article>

        {{-- ═══════════ 5. EXERCISE SETUP STUDIO ═══════════ --}}
        <article id="section-5" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[5] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">5</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">Exercise Setup Studio</h2>
                    <p class="text-gray-500 text-sm">Build a custom drill for any exercise type, exactly how you want it. {!! guideBadge('Free') !!}</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-6">The Studio covers <strong>every</strong> practice type — intervals (melodic, harmonic, direction, construction, comparison), single notes, chords, scales, rhythm, melodic dictation, and even a piano practice mode. For each type you control exactly what appears in your session:</p>

            {{-- category options --}}
            <div class="grid sm:grid-cols-2 gap-3 mb-6">
                @foreach([
                    ['Intervals','trending-up','Choose the interval pool, direction (ascending/descending/mixed), clef and register — separately for melodic, harmonic, direction, construction and comparison drills.'],
                    ['Single Notes','music-2','Pick the exact notes, pitch range and clef, and how you answer — labelled note-name buttons or an unlabelled keyboard.'],
                    ['Chords','grid-3x3','Major, minor, diminished and augmented triads; suspended chords; seventh chords (dominant, major 7, minor 7, half-diminished, diminished); inversions and voicing.'],
                    ['Scales','list-music','Major, natural/harmonic/melodic minor, all seven modes, major & minor pentatonic, blues, chromatic and whole-tone — plus direction.'],
                    ['Rhythm','activity','Time signatures, tempo (BPM), note values, rests, dotted notes, triplets, and a metronome count-in.'],
                    ['Melodic Dictation','pen-line','Key, major/minor mode, clef, time signature and rhythm values — from pitch-only lines to full melodies with rhythm.'],
                ] as $cat)
                <div class="light-card rounded-2xl p-4">
                    <div class="flex items-center gap-2.5 mb-2">
                        <span class="w-8 h-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center flex-shrink-0"><i data-lucide="{{ $cat[1] }}" class="w-4 h-4"></i></span>
                        <span class="text-sm font-bold text-gray-900">{{ $cat[0] }}</span>
                    </div>
                    <p class="text-xs text-gray-500 leading-relaxed">{{ $cat[2] }}</p>
                </div>
                @endforeach
            </div>

            {{-- visual: config panel --}}
            <div class="light-card rounded-2xl p-5 mb-6">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-4">Configure your session</p>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-gray-500 mb-1.5">Intervals to include</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach(['m2','M2','m3','M3','P4','P5'] as $i => $chip)
                            <span class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ $i < 4 ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-500' }}">{{ $chip }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div><p class="text-xs text-gray-500 mb-1.5">Clef</p><div class="px-3 py-2 rounded-lg bg-gray-50 text-sm text-gray-700 font-medium">Treble (G3–G5)</div></div>
                        <div><p class="text-xs text-gray-500 mb-1.5">Questions</p><div class="px-3 py-2 rounded-lg bg-gray-50 text-sm text-gray-700 font-medium">20</div></div>
                    </div>
                </div>
            </div>

            <div class="bg-purple-50/60 border border-purple-100 rounded-xl p-5 mb-6">
                <p class="text-xs font-bold text-purple-700 uppercase tracking-wider mb-3">How to use it</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">1.</span><span>Choose an exercise type, then toggle the specific sounds you want to hear.</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">2.</span><span>Set the clef, range, question count and difficulty to match your goal.</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">3.</span><span>Press <strong>Launch</strong> to practise — or save the configuration as a template for next time.</span></li>
                </ul>
            </div>

            {!! guideBtn(route('exercise-setup.index'), 'Open Exercise Setup') !!}

            @include('pages.partials.guide-faq', ['faqs' => [
                ['q'=>'What can I customise?','a'=>'The exact intervals, chords, scales or rhythms that appear; the clef and register; question count; direction; and how tricky the wrong answers are — per exercise type.'],
                ['q'=>'Can I save my settings?','a'=>'Yes — save any configuration as a named template and relaunch it instantly. Free accounts can keep up to '.$freePlan['saved_plans_limit'].' templates; Premium is unlimited.'],
                ['q'=>'Is the Studio on the free plan?','a'=>'Yes, the Studio itself is available to everyone. The free plan\'s daily practice limits and the '.$freePlan['saved_plans_limit'].'-template cap apply; Premium removes both.'],
            ]])
        </article>

        {{-- ═══════════ 6. AI-POWERED TRAINING ═══════════ --}}
        <article id="section-6" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[6] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0" style="background:linear-gradient(135deg,#9333ea,#f97316);">6</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">AI-Powered Training</h2>
                    <p class="text-gray-500 text-sm">Three separate AI tools — exercises, coaching, and a music assistant.</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <div class="space-y-4 mb-6">
                <div class="light-card rounded-2xl p-5">
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="w-8 h-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center"><i data-lucide="sparkles" class="w-4 h-4"></i></span>
                        <span class="font-bold text-gray-900">AI Exercises</span>
                        {!! guideBadge('Premium','premium') !!}
                    </div>
                    <p class="text-sm text-gray-600 leading-relaxed">AI analyses your practice history to generate a personalised training session. Pick the skills you want covered, and the generated question set leans into the sounds you've been missing most — no manual configuration needed.</p>
                </div>
                <div class="light-card rounded-2xl p-5">
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="w-8 h-8 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center"><i data-lucide="bot" class="w-4 h-4"></i></span>
                        <span class="font-bold text-gray-900">AI Coach</span>
                        {!! guideBadge('Free · limited') !!}
                        {!! guideBadge('Premium · full','premium') !!}
                    </div>
                    <p class="text-sm text-gray-600 leading-relaxed">The AI Coach turns your profile, survey answers, and recent practice history into a personalised <strong>7-day weekly practice plan</strong>, with focus areas and practical tips. This is the personalised counterpart to the fixed Structured Learning Path.</p>
                </div>
                <div class="light-card rounded-2xl p-5">
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center"><i data-lucide="message-circle" class="w-4 h-4"></i></span>
                        <span class="font-bold text-gray-900">Music Assistant</span>
                        {!! guideBadge('Free account') !!}
                    </div>
                    <p class="text-sm text-gray-600 leading-relaxed">A chat assistant for music questions. Get instant explanations, practice suggestions, and help with music theory, ear training and notation whenever you need it — it adapts explanations to your level.</p>
                </div>
            </div>

            {{-- visual: AI insight card --}}
            <div class="rounded-2xl p-5 mb-6 text-white relative overflow-hidden" style="background:linear-gradient(135deg,#7c3aed,#9333ea);">
                <div class="absolute -right-6 -top-6 w-28 h-28 rounded-full bg-orange-400/20"></div>
                <div class="flex items-center gap-2 mb-3 relative">
                    <i data-lucide="sparkles" class="w-4 h-4 text-orange-300"></i>
                    <span class="text-xs font-bold uppercase tracking-wider text-purple-200">Example AI Coach recommendation</span>
                </div>
                <p class="text-sm leading-relaxed relative">"You're strong on perfect intervals, but minor 6ths trip you up often. This week's plan includes short daily sessions focused on 6ths — 10 minutes a day should move the needle."</p>
                <div class="mt-4 flex gap-2 relative">
                    <span class="px-3 py-1.5 rounded-lg bg-white/15 text-xs font-semibold backdrop-blur-sm">Focus: Minor 6th</span>
                    <span class="px-3 py-1.5 rounded-lg bg-orange-400 text-xs font-semibold text-white">Weekly plan →</span>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                {!! guideBtn(route('ai.exercises'), 'Open AI Exercises', 'sparkles') !!}
                <a href="{{ route('ai-coach.index') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl font-semibold text-sm w-full sm:w-auto border-2 border-purple-200 text-purple-700 bg-white hover:bg-purple-50 transition-all">Open AI Coach <i data-lucide="bot" class="w-4 h-4"></i></a>
                <a href="{{ route('ai-chat.index') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl font-semibold text-sm w-full sm:w-auto border-2 border-purple-200 text-purple-700 bg-white hover:bg-purple-50 transition-all">Open Music Assistant <i data-lucide="message-circle" class="w-4 h-4"></i></a>
            </div>

            <div class="mt-6">
            @include('pages.partials.guide-faq', ['faqs' => [
                ['q'=>'How does the AI know my weak spots?','a'=>'It analyses your answer history to find which sounds you confuse most often, then builds sessions and plans that spend more time on exactly those.'],
                ['q'=>'Which AI features are free?','a'=>'The Music Assistant is available to all signed-in users, and the AI Coach offers limited access on the free plan. AI Exercises and full AI Coach access are Premium features.'],
                ['q'=>'Does the AI replace a teacher?','a'=>'No — it complements one. The AI is great at spotting patterns in your practice data and answering theory questions; a human teacher brings feedback on technique, musicality and goals. Harmoniva supports both.'],
            ]])
            </div>
        </article>

        {{-- ═══════════ 7. PIANO STUDIO ═══════════ --}}
        <article id="section-7" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[7] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">7</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">Virtual Piano Studio</h2>
                    <p class="text-gray-500 text-sm">A full playable keyboard for exploring sounds freely. {!! guideBadge('Free · no account needed') !!}</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-6">Sometimes you just want to <em>hear</em> a note, an interval or a chord. The Piano Studio is a responsive on-screen keyboard with realistic sound — the note name lights up as you play. Use it to check yourself, build chords by ear, or as a reference alongside other exercises. There's also a piano practice mode available from the Exercise Setup Studio.</p>

            {{-- visual: mini piano --}}
            <div class="light-card rounded-2xl p-5 mb-6">
                <div class="rounded-xl bg-gray-900 p-4">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-xs text-gray-400 uppercase tracking-widest flex items-center gap-1.5"><i data-lucide="piano" class="w-3.5 h-3.5"></i> Piano Studio</span>
                        <span class="text-xs font-semibold text-purple-400">C4 · E4 · G4</span>
                    </div>
                    <div class="relative h-24 flex rounded-lg overflow-hidden">
                        @for($i=0;$i<8;$i++)
                        <div class="flex-1 bg-gradient-to-b {{ in_array($i,[0,2,4]) ? 'from-purple-100 to-purple-200' : 'from-gray-50 to-white' }} border-r border-gray-300 last:border-r-0"></div>
                        @endfor
                        @foreach([12,25,50,62,75] as $left)
                        <div class="absolute top-0 h-14 w-[9%] bg-gray-800 rounded-b" style="left:{{ $left }}%"></div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="bg-purple-50/60 border border-purple-100 rounded-xl p-5 mb-6">
                <p class="text-xs font-bold text-purple-700 uppercase tracking-wider mb-3">How to use it</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">1.</span><span>Click or tap keys to play them — the note name lights up as you go.</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">2.</span><span>Play two or more keys together to hear intervals and chords.</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">3.</span><span>On mobile, scroll the keyboard sideways to reach higher or lower notes.</span></li>
                </ul>
            </div>

            {!! guideBtn(route('piano.studio'), 'Open Piano Studio', 'piano') !!}

            @include('pages.partials.guide-faq', ['faqs' => [
                ['q'=>'Does it work on a phone?','a'=>'Yes. The keyboard is touch-friendly and scrolls horizontally so you can reach the full range on any screen size.'],
                ['q'=>'Do I need headphones?','a'=>'Not required, but recommended — headphones make it much easier to hear the difference between close notes and the individual voices in a chord.'],
                ['q'=>'Do I need an account?','a'=>'No — the Piano Studio is open to everyone, including guests.'],
            ]])
        </article>

        {{-- ═══════════ 8. MUSIC GAMES ═══════════ --}}
        <article id="section-8" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[8] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">8</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">Music Games</h2>
                    <p class="text-gray-500 text-sm">Six arcade-style games that train real ear skills.</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-6">Practice doesn't have to feel like homework. Each game uses the same audio engine and skills as the practice exercises — you're genuinely training, just with more adrenaline. Your personal best is saved for every game, and Premium unlocks the global leaderboard (the Hall of Fame).</p>

            {{-- game cards --}}
            <div class="grid sm:grid-cols-2 gap-3 mb-6">
                @foreach([
                    ['Note Fall','arrow-down-to-line','Note reading & reflexes','Notes fall from the top of the screen — press the matching piano key to catch them. 5 levels add a wider range, accidentals, and melodic sequences.'],
                    ['Note Rush','zap','Fast note recognition','A note plays — identify it as fast as you can. Build streaks for score multipliers, with 60 seconds on the clock.'],
                    ['Melody Memory','music','Melodic memory','Listen to a melody, then repeat it on the piano keyboard. The melody grows each round — one wrong note ends the game.'],
                    ['Interval Blitz','timer','Interval recognition','Name the interval before the timer runs out. Level 1: melodic · Level 2: harmonic · Level 3: mixed. Complete 20 questions per level to advance; 3 lives, and a 5-streak earns a bonus life.'],
                    ['Note Catcher','move-horizontal','Staff-to-key mapping','Steer the falling note left and right to land it on the correct piano key — arrow keys work too.'],
                    ['Chord Clash','layers','Chord quality recognition','A chord plays — identify its quality, choosing between two chord types. 5 levels take you from basic triads to seventh chords.'],
                ] as $g)
                <div class="light-card rounded-2xl p-4">
                    <div class="flex items-center gap-2.5 mb-2">
                        <span class="w-8 h-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center flex-shrink-0"><i data-lucide="{{ $g[1] }}" class="w-4 h-4"></i></span>
                        <div>
                            <p class="text-sm font-bold text-gray-900 leading-tight">{{ $g[0] }}</p>
                            <p class="text-[11px] text-gray-400">{{ $g[2] }}</p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 leading-relaxed">{{ $g[3] }}</p>
                </div>
                @endforeach
            </div>

            {{-- play limits --}}
            <div class="light-card rounded-2xl p-5 mb-6">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">Daily play limits</p>
                <div class="space-y-2 text-sm text-gray-700">
                    <div class="flex flex-wrap items-center gap-2">{!! guideBadge('Guest','guest') !!}<span>{{ $guestPlaysPerGame }} play per game, {{ $guestPlaysTotal }} plays total (try before signing up)</span></div>
                    <div class="flex flex-wrap items-center gap-2">{!! guideBadge('Free') !!}<span>{{ $freePlan['games_daily_plays_per_type'] }} plays per game, {{ $freePlan['games_daily_plays_total'] }} plays total per day</span></div>
                    <div class="flex flex-wrap items-center gap-2">{!! guideBadge('Premium','premium') !!}<span>Unlimited plays + global leaderboard access</span></div>
                </div>
            </div>

            {!! guideBtn(route('games.index'), 'Browse Music Games', 'gamepad-2') !!}

            @include('pages.partials.guide-faq', ['faqs' => [
                ['q'=>'Do games count as real practice?','a'=>'Yes — every game uses the same audio engine and skills as the standard exercises. You\'re genuinely training, just with more adrenaline.'],
                ['q'=>'Can I play without an account?','a'=>'Yes — guests get '.$guestPlaysPerGame.' play per game ('.$guestPlaysTotal.' total) to try them out. Sign in to save scores and get the free plan\'s daily plays.'],
                ['q'=>'Is there a leaderboard?','a'=>'Yes. Personal bests are saved to your profile on any plan; the global leaderboard (Hall of Fame) is a Premium feature.'],
            ]])
        </article>

        {{-- ═══════════ 9. PROGRESS & ACHIEVEMENTS ═══════════ --}}
        <article id="section-9" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[9] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">9</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">Progress, Analytics &amp; Achievements</h2>
                    <p class="text-gray-500 text-sm">See exactly how far you've come — and what to work on next.</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-6">Every answer you give is logged and turned into clear feedback: accuracy per exercise type, sessions completed, practice time, your streak, experience points and achievements. {!! guideBadge('Free') !!} covers this core tracking; {!! guideBadge('Premium','premium') !!} adds detailed charts and deeper skill breakdowns so you can see trends over time.</p>

            {{-- visual: progress bars --}}
            <div class="light-card rounded-2xl p-5 mb-6">
                <div class="grid grid-cols-3 gap-3 mb-4">
                    @foreach([['Accuracy','87%','green'],['Streak','7d','orange'],['Sessions','142','purple']] as $s)
                    <div class="text-center rounded-xl bg-gray-50 py-3">
                        <p class="text-lg font-extrabold text-{{ $s[2] }}-600">{{ $s[1] }}</p>
                        <p class="text-xs text-gray-400">{{ $s[0] }}</p>
                    </div>
                    @endforeach
                </div>
                <div class="space-y-2.5">
                    @foreach([['Intervals',92],['Chords',78],['Scales',64],['Rhythm',85]] as $b)
                    <div>
                        <div class="flex justify-between text-xs mb-1"><span class="text-gray-600 font-medium">{{ $b[0] }}</span><span class="text-gray-400">{{ $b[1] }}%</span></div>
                        <div class="h-2 rounded-full bg-gray-100 overflow-hidden"><div class="h-full rounded-full bg-purple-500" style="width:{{ $b[1] }}%"></div></div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-purple-50/60 border border-purple-100 rounded-xl p-5 mb-6">
                <p class="text-xs font-bold text-purple-700 uppercase tracking-wider mb-3">How to use it</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">1.</span><span>Open <strong>Progress</strong> to see accuracy and activity across every skill.</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">2.</span><span>Spot the lowest bars — those are your best opportunities to improve.</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">3.</span><span>Jump into a targeted drill via the Exercise Setup Studio, or let the AI tools build one around your results.</span></li>
                </ul>
            </div>

            {!! guideBtn(route('progress'), 'View My Progress', 'trending-up') !!}

            @include('pages.partials.guide-faq', ['faqs' => [
                ['q'=>'What exactly is tracked?','a'=>'Accuracy per exercise type, sessions completed, time practised, your streak, experience points, achievements, and which specific sounds you miss most often.'],
                ['q'=>'What does Premium add?','a'=>'Detailed charts and deeper skill breakdowns, so you can follow trends over time instead of just seeing today\'s snapshot.'],
                ['q'=>'Is my data private?','a'=>'Yes. Your practice data is yours and is never sold. You can request a copy of your data, or request deletion of your account and personal data, at any time — see the Privacy Policy for details.'],
            ]])
        </article>

        {{-- ═══════════ 10. FIND TEACHERS ═══════════ --}}
        <article id="section-10" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[10] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">10</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">Find Teachers</h2>
                    <p class="text-gray-500 text-sm">Browse verified teachers and music schools, and connect with one.</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-4">The teacher directory lists every approved teacher and music school on Harmoniva. Each public profile shows their background, instruments, services and lesson formats, photos and videos, languages, and reviews from real students. From a profile you can send a connection request, message the teacher, and — where the teacher has online booking enabled — book a lesson slot directly.</p>

            <p class="text-gray-600 leading-relaxed mb-6"><strong>Profiles are reviewed before going live:</strong> a teacher creates their account, completes their profile, submits it for review, and it becomes publicly visible after approval by the Harmoniva team. So every profile you see in the directory has been checked.</p>

            {{-- visual: teacher card --}}
            <div class="light-card rounded-2xl p-4 mb-6">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-11 h-11 rounded-full bg-gradient-to-br from-purple-400 to-orange-400 flex items-center justify-center text-white font-bold">A</div>
                    <div><p class="font-bold text-gray-900 text-sm leading-tight">Ayla K.</p><p class="text-xs text-gray-400">Piano & ear-training teacher · ★ 4.9</p></div>
                    <span class="ml-auto inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-green-100 text-green-700 text-[11px] font-bold"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Accepting students</span>
                </div>
                <div class="flex flex-wrap gap-1.5">
                    <span class="px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 text-[11px] font-semibold">Online lessons</span>
                    <span class="px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 text-[11px] font-semibold">Classical</span>
                    <span class="px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 text-[11px] font-semibold">English · Turkish</span>
                </div>
            </div>

            <div class="bg-purple-50/60 border border-purple-100 rounded-xl p-5 mb-6">
                <p class="text-xs font-bold text-purple-700 uppercase tracking-wider mb-3">How to use it</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">1.</span><span>Browse the directory and open profiles that match your instrument and goals.</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">2.</span><span>Check their specialisation, services, languages and student reviews.</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">3.</span><span>Send a connection request, message them, or book an available lesson slot.</span></li>
                </ul>
            </div>

            {!! guideBtn(route('teachers.directory'), 'Find Teachers & Schools', 'users') !!}

            @include('pages.partials.guide-faq', ['faqs' => [
                ['q'=>'Are these profiles verified?','a'=>'Yes — every teacher and school profile is reviewed by the Harmoniva team before it becomes publicly visible in the directory.'],
                ['q'=>'Does connecting with a teacher cost anything?','a'=>'Connecting on Harmoniva is free. Lesson pricing is set by each teacher or school — you\'ll find their rates and services on their profile.'],
                ['q'=>'Can every teacher be booked online?','a'=>'Online slot booking appears on profiles where the teacher has enabled it. Otherwise you can still connect and message the teacher to arrange lessons.'],
            ]])
        </article>

        {{-- ═══════════ 11. COMMUNITY FEED ═══════════ --}}
        <article id="section-11" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[11] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">11</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">Community Feed</h2>
                    <p class="text-gray-500 text-sm">Follow other learners and share the journey. {!! guideBadge('Free account') !!}</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-6">Learning is easier with company. On the community feed you can follow other students, teachers and schools, and see their milestones — completed lessons, streaks, achievements and new high scores — alongside your own. It's a lightweight way to stay motivated and discover people to learn with.</p>

            {{-- visual: feed --}}
            <div class="light-card rounded-2xl p-4 mb-6">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">Community feed</p>
                <div class="space-y-2 text-xs text-gray-600">
                    <div class="flex items-center gap-2"><span class="w-6 h-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center"><i data-lucide="award" class="w-3 h-3"></i></span> Mateo hit a 30-day streak 🎉</div>
                    <div class="flex items-center gap-2"><span class="w-6 h-6 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center"><i data-lucide="check" class="w-3 h-3"></i></span> Sara finished "Chords · Lesson 12"</div>
                    <div class="flex items-center gap-2"><span class="w-6 h-6 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center"><i data-lucide="star" class="w-3 h-3"></i></span> New high score in Interval Blitz</div>
                </div>
            </div>

            <div class="bg-purple-50/60 border border-purple-100 rounded-xl p-5 mb-6">
                <p class="text-xs font-bold text-purple-700 uppercase tracking-wider mb-3">How to use it</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">1.</span><span>Open the <strong>Feed</strong> from the main menu (sign-in required).</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">2.</span><span>Follow students, teachers and schools you want to keep up with.</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">3.</span><span>Your own milestones — streaks, completed lessons, high scores — appear in the feed too.</span></li>
                </ul>
            </div>

            {!! guideBtn(route('feed'), 'Open the Feed', 'rss') !!}

            @include('pages.partials.guide-faq', ['faqs' => [
                ['q'=>'Who can see the feed?','a'=>'The feed is for signed-in users. Guests are asked to create a free account first.'],
                ['q'=>'Can I follow my teacher?','a'=>'Yes — you can follow teachers and schools as well as other students, and their public activity appears in your feed.'],
                ['q'=>'What shows up in the feed?','a'=>'Activity and milestones such as completed lessons, practice streaks, achievements and game high scores — from you and the people you follow.'],
            ]])
        </article>

        {{-- ═══════════ 12. ASSIGNMENTS, MESSAGES & LESSONS ═══════════ --}}
        <article id="section-12" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[12] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">12</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">Assignments, Messages &amp; Lessons</h2>
                    <p class="text-gray-500 text-sm">Everything that connects you with your teacher, in one flow.</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-6">Once you're connected with a teacher or school, three tools keep the collaboration running: <strong>Assignments</strong> collects every exercise set built for you, with your results reported back automatically; <strong>Messages</strong> is your private inbox with each teacher, including shared documents, articles and videos; and <strong>My Appointments</strong> lists your booked lessons, where you can cancel or request a reschedule. Notifications tie it all together so you never miss a new assignment, a reply, or an upcoming lesson.</p>

            {{-- visual: assignment + appointment --}}
            <div class="grid sm:grid-cols-2 gap-3 mb-6">
                <div class="light-card rounded-2xl p-4">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">Your assignments</p>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2.5 p-2.5 rounded-lg bg-purple-50 border border-purple-100">
                            <span class="w-7 h-7 rounded-lg bg-purple-600 text-white flex items-center justify-center flex-shrink-0"><i data-lucide="clipboard-list" class="w-3.5 h-3.5"></i></span>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-gray-800 leading-tight">Bass-clef triads · 15 questions</p>
                                <p class="text-[11px] text-gray-400">From Ayla K. · due Friday</p>
                            </div>
                            <span class="ml-auto text-[11px] font-bold text-purple-600 flex-shrink-0">Start →</span>
                        </div>
                        <div class="flex items-center gap-2.5 p-2.5 rounded-lg bg-gray-50">
                            <span class="w-7 h-7 rounded-lg bg-green-100 text-green-600 flex items-center justify-center flex-shrink-0"><i data-lucide="check" class="w-3.5 h-3.5"></i></span>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-gray-800 leading-tight">Melodic dictation · C major</p>
                                <p class="text-[11px] text-gray-400">Completed · 92%</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="light-card rounded-2xl p-4">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">Next lesson</p>
                    <div class="rounded-xl bg-gray-50 p-3 mb-2.5">
                        <div class="flex items-center gap-2.5">
                            <span class="w-9 h-9 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center flex-shrink-0"><i data-lucide="calendar-check" class="w-4 h-4"></i></span>
                            <div>
                                <p class="text-xs font-bold text-gray-800">Piano lesson with Ayla K.</p>
                                <p class="text-[11px] text-gray-400">Tuesday · 16:00 – 16:45 · Online</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <span class="w-6 h-6 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center flex-shrink-0"><i data-lucide="message-circle" class="w-3 h-3"></i></span>
                        "Great progress on 6ths — see you Tuesday!"
                    </div>
                </div>
            </div>

            <div class="bg-purple-50/60 border border-purple-100 rounded-xl p-5 mb-6">
                <p class="text-xs font-bold text-purple-700 uppercase tracking-wider mb-3">How to use it</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">1.</span><span>Open <strong>Assignments</strong> to see every exercise set your teacher or school has sent — press Start and your score reports back automatically.</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">2.</span><span>Use <strong>Messages</strong> to ask questions and receive shared materials — documents, articles, and videos from your teacher.</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">3.</span><span>Book lessons from your teacher's profile calendar and manage them under <strong>My Appointments</strong>.</span></li>
                </ul>
            </div>

            <div class="flex flex-wrap gap-3">
                {!! guideBtn(route('assignments.index'), 'My Assignments', 'clipboard-list') !!}
                <a href="{{ route('messages') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl font-semibold text-sm w-full sm:w-auto border-2 border-purple-200 text-purple-700 bg-white hover:bg-purple-50 transition-all">Messages <i data-lucide="mail" class="w-4 h-4"></i></a>
                <a href="{{ route('my-appointments.index') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl font-semibold text-sm w-full sm:w-auto border-2 border-purple-200 text-purple-700 bg-white hover:bg-purple-50 transition-all">My Appointments <i data-lucide="calendar" class="w-4 h-4"></i></a>
            </div>

            <div class="mt-6">
            @include('pages.partials.guide-faq', ['faqs' => [
                ['q'=>'Where do I find exercises my teacher sent me?','a'=>'Under Assignments. Each one shows who sent it, its due date, and your score once completed. Results sync back to your teacher automatically.'],
                ['q'=>'Can I message my teacher directly?','a'=>'Yes — every connected teacher or school gets a private conversation thread in your Messages inbox, including any documents, articles or videos they share with you.'],
                ['q'=>'How does lesson booking work?','a'=>'Teachers with booking enabled show their available slots on their public profile. Pick a time, confirm, and manage everything — cancellations and reschedule requests included — from My Appointments.'],
                ['q'=>'Will I be notified about new assignments?','a'=>'Yes. New assignments, messages, appointment updates, and connection requests all land in your notification center, so nothing slips by.'],
            ]])
            </div>
        </article>

        {{-- ═══════════ 13. FOR TEACHERS ═══════════ --}}
        <article id="section-13" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[13] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">13</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">Harmoniva for Teachers</h2>
                    <p class="text-gray-500 text-sm">A complete teaching toolkit built into the platform. {!! guideBadge('Teacher','teacher') !!}</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-4">Any registered user can open a teacher account — you don't need to re-register. The Teacher Portal gives you a student roster with invitations, a custom assignment builder (the same engine as the Exercise Setup Studio), automatic result tracking for everything you assign, private messaging with each student, shared teaching materials, and a public teacher profile that goes live after review.</p>

            <p class="text-gray-600 leading-relaxed mb-6">{!! guideBadge('Teacher','teacher') !!} The base teacher account supports up to {{ $teacherFree['max_students'] }} students with assignments and result tracking. {!! guideBadge('Teacher Premium','premium') !!} unlocks unlimited students, the full CRM, calendar and lesson scheduling, online booking on your public profile, content publishing, and detailed reports.</p>

            <div class="bg-purple-50/60 border border-purple-100 rounded-xl p-5 mb-6">
                <p class="text-xs font-bold text-purple-700 uppercase tracking-wider mb-3">How it works</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">1.</span><span>Enable teacher tools on your account and invite your students (or accept their requests).</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">2.</span><span>Build assignments with the exercise builder — pick the type, sounds, clef and question count per student.</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">3.</span><span>Watch results come back automatically, message students, and complete your public profile to appear in the teacher directory after approval.</span></li>
                </ul>
            </div>

            <div class="flex flex-wrap gap-3">
                {!! guideBtn(route('page.teachers-solution'), 'Harmoniva for Teachers', 'briefcase') !!}
                <a href="{{ route('pricing.teachers') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl font-semibold text-sm w-full sm:w-auto border-2 border-purple-200 text-purple-700 bg-white hover:bg-purple-50 transition-all">Teacher &amp; School Pricing <i data-lucide="tag" class="w-4 h-4"></i></a>
            </div>

            <div class="mt-6">
            @include('pages.partials.guide-faq', ['faqs' => [
                ['q'=>'How do I become a teacher on Harmoniva?','a'=>'Choose the Teacher Portal at registration, or enable teacher tools later from your existing account — no separate registration needed.'],
                ['q'=>'What\'s the difference between the base and Premium teacher plans?','a'=>'The base account covers up to '.config('plans.teacher.free.max_students').' students with assignments and result tracking. Premium adds unlimited students, the full CRM, calendar and scheduling, online booking, content publishing, and detailed reports.'],
                ['q'=>'Is my teacher profile public immediately?','a'=>'No — you complete your profile and submit it for review; it becomes publicly visible in the directory after approval by the Harmoniva team.'],
            ]])
            </div>
        </article>

        {{-- ═══════════ 14. FOR MUSIC SCHOOLS ═══════════ --}}
        <article id="section-14" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[14] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">14</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">Harmoniva for Music Schools</h2>
                    <p class="text-gray-500 text-sm">The same teaching engine, at institution scale. {!! guideBadge('Music School','school') !!}</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-4">The Music School Portal runs on the same engine as the Teacher Portal, extended for institutions: manage a roster of teachers, invite and enroll students, assign exercises, and follow everyone's progress from one panel. Schools also get a public school profile in the directory — reviewed and approved just like teacher profiles — and students can belong to a school, a private teacher, or both.</p>

            <p class="text-gray-600 leading-relaxed mb-6">{!! guideBadge('Music School','school') !!} The base school account supports up to {{ $schoolFree['max_teachers'] }} teachers and {{ $schoolFree['max_students'] }} students. {!! guideBadge('School Premium','premium') !!} unlocks unlimited teachers and students, the full CRM, calendar, content publishing, and advanced reports.</p>

            {{-- visual: school strip --}}
            <div class="light-card rounded-2xl p-4 mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-orange-400 to-purple-500 flex items-center justify-center text-white flex-shrink-0"><i data-lucide="building-2" class="w-5 h-5"></i></div>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-gray-900 text-sm leading-tight">Aria Music School</p>
                        <p class="text-xs text-gray-400">Teachers · students · class assignments &amp; progress reports in one panel</p>
                    </div>
                    <span class="hidden sm:inline-block px-3 py-1.5 rounded-lg bg-purple-50 text-purple-700 text-xs font-semibold flex-shrink-0">School profile →</span>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                {!! guideBtn(route('page.schools'), 'Harmoniva for Schools', 'building-2') !!}
                <a href="{{ route('page.request-demo') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl font-semibold text-sm w-full sm:w-auto border-2 border-purple-200 text-purple-700 bg-white hover:bg-purple-50 transition-all">Request a Demo <i data-lucide="calendar" class="w-4 h-4"></i></a>
            </div>

            <div class="mt-6">
            @include('pages.partials.guide-faq', ['faqs' => [
                ['q'=>'What\'s the difference between a teacher and a school account?','a'=>'A teacher account is for an individual tutor; a school account manages multiple teachers and their students under one institution, with school-wide oversight.'],
                ['q'=>'How do students join our school?','a'=>'Through invitations — the school (or its teachers) invites students, who accept and are then enrolled. Their assignment results and progress become visible to the school.'],
                ['q'=>'Where can I see school pricing?','a'=>'The Teachers & Schools pricing page covers both teacher and school plans, including what the Premium tiers add.'],
            ]])
            </div>
        </article>

        {{-- ═══════════ 15. PLANS & ACCESS ═══════════ --}}
        <article id="section-15" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[15] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">15</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">Plans &amp; Access</h2>
                    <p class="text-gray-500 text-sm">What's included where — the quick reference.</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-6">Throughout this guide you've seen small access badges. Here's the summary in one place — these numbers come straight from the live plan configuration:</p>

            <div class="light-card rounded-2xl p-5 mb-6">
                <div class="space-y-3 text-sm text-gray-700">
                    <div class="flex flex-wrap items-start gap-2 pb-3 border-b border-gray-100">
                        {!! guideBadge('Guest','guest') !!}
                        <span class="flex-1 min-w-[200px]">Try practice exercises, the Piano Studio, and {{ $guestPlaysPerGame }} play per game ({{ $guestPlaysTotal }} total) — nothing is saved.</span>
                    </div>
                    <div class="flex flex-wrap items-start gap-2 pb-3 border-b border-gray-100">
                        {!! guideBadge('Free') !!}
                        <span class="flex-1 min-w-[200px]">All 10 exercise types · Structured Learning Path ({{ $freePlan['learning_path_daily_sessions'] }} sessions/day, {{ $freePlan['session_question_cap'] }} questions each) · Exercise Setup Studio ({{ $freePlan['studio_daily_sessions'] }} sessions/day, {{ $freePlan['saved_plans_limit'] }} saved templates) · unlimited Piano Studio · games ({{ $freePlan['games_daily_plays_per_type'] }}/game, {{ $freePlan['games_daily_plays_total'] }}/day) · Ask AI ({{ $freePlan['ask_ai_daily'] }}/day) · core progress tracking · community feed.</span>
                    </div>
                    <div class="flex flex-wrap items-start gap-2 pb-3 border-b border-gray-100">
                        {!! guideBadge('Premium','premium') !!}
                        <span class="flex-1 min-w-[200px]">Everything in Free without daily limits · unlimited templates · AI Exercises · full AI Coach · detailed charts · unlimited game plays + global leaderboard.</span>
                    </div>
                    <div class="flex flex-wrap items-start gap-2 pb-3 border-b border-gray-100">
                        {!! guideBadge('Teacher','teacher') !!}
                        <span class="flex-1 min-w-[200px]">Up to {{ $teacherFree['max_students'] }} students, assignments &amp; result tracking; Teacher Premium adds unlimited students, CRM, calendar, booking, content publishing and reports.</span>
                    </div>
                    <div class="flex flex-wrap items-start gap-2">
                        {!! guideBadge('Music School','school') !!}
                        <span class="flex-1 min-w-[200px]">Up to {{ $schoolFree['max_teachers'] }} teachers and {{ $schoolFree['max_students'] }} students; School Premium adds unlimited rosters, CRM, calendar and advanced reports.</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                {!! guideBtn(route('pricing.index'), 'See Full Pricing', 'tag') !!}
                <a href="{{ route('pricing.teachers') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl font-semibold text-sm w-full sm:w-auto border-2 border-purple-200 text-purple-700 bg-white hover:bg-purple-50 transition-all">Teachers &amp; Schools Pricing <i data-lucide="briefcase" class="w-4 h-4"></i></a>
            </div>

            <div class="mt-6">
            @include('pages.partials.guide-faq', ['faqs' => [
                ['q'=>'How do I upgrade to Premium?','a'=>'Open the Pricing page, pick a plan, and check out — your account upgrades immediately and every limit in this guide lifts accordingly.'],
                ['q'=>'Do teachers and schools have separate pricing?','a'=>'Yes — the Teachers & Schools pricing page covers those plans, separate from the personal Free/Premium plans.'],
                ['q'=>'Where can I get help?','a'=>'The Help Center and FAQ cover common questions, and you can always reach the team via the Contact page.'],
            ]])
            </div>
        </article>

    </div>
</div>

{{-- ============ BOTTOM CTA ============ --}}
<section class="bg-white py-20 px-4">
    <div class="max-w-2xl mx-auto text-center reveal">
        <span class="text-xs font-bold uppercase tracking-[0.2em] text-purple-600 mb-3 block">Ready to begin?</span>
        <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-4">Start training your ears today</h2>
        <p class="text-gray-500 text-lg mb-8">Create a free account and put this guide into practice. No credit card, no commitment.</p>
        <div class="flex flex-wrap justify-center gap-3">
            @auth
                <a href="{{ url('/dashboard') }}" class="inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-xl text-white font-semibold shadow-lg shadow-purple-500/25 hover:-translate-y-0.5 transition-all w-full sm:w-auto" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">Go to Dashboard <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
            @else
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-xl text-white font-semibold shadow-lg shadow-purple-500/25 hover:-translate-y-0.5 transition-all w-full sm:w-auto" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">Start Free <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-xl font-semibold border-2 border-gray-200 text-gray-700 hover:bg-gray-50 transition-all w-full sm:w-auto">Sign In</a>
            @endauth
        </div>
    </div>
</section>

@endsection
