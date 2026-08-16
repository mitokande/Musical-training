@extends('layouts.standalone')

@section('title', __('pages.how_it_works.meta_title'))
@section('description', __('pages.how_it_works.meta_description'))

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
            'headline' => __('pages.how_it_works.jsonld_headline'),
            'description' => __('pages.how_it_works.jsonld_description'),
            'about' => __('pages.how_it_works.jsonld_about'),
            'inLanguage' => str_replace('_', '-', app()->getLocale()),
            'image' => asset('images/og-image.png'),
            'author' => ['@id' => url('/').'#organization'],
            'publisher' => ['@id' => url('/').'#organization'],
            'mainEntityOfPage' => url()->current(),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $howItWorksBreadcrumbJsonLd = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => __('pages.how_it_works.jsonld_home'), 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => __('pages.how_it_works.jsonld_breadcrumb'), 'item' => url()->current()],
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
            {{ __('pages.how_it_works.hero_badge') }}
        </div>
        <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold mb-5">{{ __('pages.how_it_works.hero_title') }}</h1>
        <p class="text-purple-200 text-lg sm:text-xl max-w-2xl mx-auto leading-relaxed">{{ __('pages.how_it_works.hero_subtitle') }}</p>
        <div class="flex flex-wrap justify-center gap-4 mt-8 text-sm text-purple-200">
            <span class="flex items-center gap-1.5"><i data-lucide="clock" class="w-4 h-4"></i> {{ __('pages.how_it_works.hero_stat_1') }}</span>
            <span class="flex items-center gap-1.5"><i data-lucide="layout-grid" class="w-4 h-4"></i> {{ __('pages.how_it_works.hero_stat_2') }}</span>
            <span class="flex items-center gap-1.5"><i data-lucide="graduation-cap" class="w-4 h-4"></i> {{ __('pages.how_it_works.hero_stat_3') }}</span>
        </div>
    </div>
</section>

{{-- ============ TABLE OF CONTENTS ============ --}}
<section class="bg-white border-b border-gray-100 py-8 px-4">
    <div class="max-w-4xl mx-auto reveal">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-4">{{ __('pages.how_it_works.toc_label') }}</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @php
            $toc = [
                ['n'=>1,'t'=>__('pages.how_it_works.toc_1'),'ic'=>'rocket'],
                ['n'=>2,'t'=>__('pages.how_it_works.toc_2'),'ic'=>'layout-dashboard'],
                ['n'=>3,'t'=>__('pages.how_it_works.toc_3'),'ic'=>'route'],
                ['n'=>4,'t'=>__('pages.how_it_works.toc_4'),'ic'=>'music'],
                ['n'=>5,'t'=>__('pages.how_it_works.toc_5'),'ic'=>'sliders-horizontal'],
                ['n'=>6,'t'=>__('pages.how_it_works.toc_6'),'ic'=>'sparkles'],
                ['n'=>7,'t'=>__('pages.how_it_works.toc_7'),'ic'=>'piano'],
                ['n'=>8,'t'=>__('pages.how_it_works.toc_8'),'ic'=>'gamepad-2'],
                ['n'=>9,'t'=>__('pages.how_it_works.toc_9'),'ic'=>'trending-up'],
                ['n'=>10,'t'=>__('pages.how_it_works.toc_10'),'ic'=>'users'],
                ['n'=>11,'t'=>__('pages.how_it_works.toc_11'),'ic'=>'rss'],
                ['n'=>12,'t'=>__('pages.how_it_works.toc_12'),'ic'=>'clipboard-list'],
                ['n'=>13,'t'=>__('pages.how_it_works.toc_13'),'ic'=>'briefcase'],
                ['n'=>14,'t'=>__('pages.how_it_works.toc_14'),'ic'=>'building-2'],
                ['n'=>15,'t'=>__('pages.how_it_works.toc_15'),'ic'=>'tag'],
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
        $guestLp = config('plans.guest.learning_path_daily_sessions');
        $guestStudio = config('plans.guest.studio_daily_sessions');
        // Pre-rendered badges reused across sections.
        $bFree = guideBadge(__('pages.how_it_works.badge_free'));
        $bPremium = guideBadge(__('pages.how_it_works.badge_premium'), 'premium');
        $bGuest = guideBadge(__('pages.how_it_works.badge_guest'), 'guest');
        $bTeacher = guideBadge(__('pages.how_it_works.badge_teacher'), 'teacher');
        $bSchool = guideBadge(__('pages.how_it_works.badge_school'), 'school');
        $bStrong = fn ($t) => '<strong>'.$t.'</strong>';
        // Each section header links straight to the feature it describes.
        $sectionLinks = [
            1 => route('register'),
            2 => route('dashboard'),
            3 => locale_url('/learn'),
            4 => route('exercise-setup.index'),
            5 => route('exercise-setup.index'),
            6 => route('ai.exercises'),
            7 => locale_url('/piano-studio'),
            8 => route('games.index'),
            9 => route('progress'),
            10 => locale_url('/find-teachers'),
            11 => route('feed'),
            12 => route('assignments.index'),
            13 => locale_url('/teachers'),
            14 => locale_url('/schools'),
            15 => locale_url('/pricing'),
        ];
        $hk = fn ($k, $r = []) => __('pages.how_it_works.'.$k, $r);
        @endphp

        {{-- ═══════════ 1. GETTING STARTED ═══════════ --}}
        <article id="section-1" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[1] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">1</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">{{ $hk('s1_title') }}</h2>
                    <p class="text-gray-500 text-sm">{{ $hk('s1_sub') }}</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-4">{{ $hk('s1_p1') }}</p>

            <p class="text-gray-600 leading-relaxed mb-6">{{ $hk('s1_p2') }}</p>

            {{-- account type cards --}}
            <div class="grid sm:grid-cols-3 gap-3 mb-6">
                <div class="light-card rounded-2xl p-4">
                    <div class="w-9 h-9 rounded-lg bg-green-100 text-green-600 flex items-center justify-center mb-2"><i data-lucide="user" class="w-4 h-4"></i></div>
                    <p class="font-bold text-gray-900 text-sm">{{ $hk('acct_student_title') }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $hk('acct_student_desc') }}</p>
                </div>
                <div class="light-card rounded-2xl p-4">
                    <div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center mb-2"><i data-lucide="briefcase" class="w-4 h-4"></i></div>
                    <p class="font-bold text-gray-900 text-sm">{{ $hk('acct_teacher_title') }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $hk('acct_teacher_desc') }}</p>
                </div>
                <div class="light-card rounded-2xl p-4">
                    <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center mb-2"><i data-lucide="building-2" class="w-4 h-4"></i></div>
                    <p class="font-bold text-gray-900 text-sm">{{ $hk('acct_school_title') }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $hk('acct_school_desc') }}</p>
                </div>
            </div>

            <div class="bg-purple-50/60 border border-purple-100 rounded-xl p-5 mb-6">
                <p class="text-xs font-bold text-purple-700 uppercase tracking-wider mb-3">{{ $hk('s1_first5_label') }}</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">1.</span><span>{{ $hk('s1_first5_1') }}</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">2.</span><span>{!! $hk('s1_first5_2', ['lp' => $bStrong($hk('s1_first5_2_lp'))]) !!}</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">3.</span><span>{{ $hk('s1_first5_3') }}</span></li>
                </ul>
            </div>

            <p class="text-gray-600 text-sm leading-relaxed mb-6">{!! $bGuest !!} {{ $hk('s1_guest_note', ['lp' => $guestLp, 'studio' => $guestStudio]) }}</p>

            {!! guideBtn(route('register'), $hk('s1_btn'), 'user-plus') !!}

            @include('pages.partials.guide-faq', ['label' => $hk('faq_label'), 'faqs' => [
                ['q'=>$hk('s1_faq_q1'),'a'=>$hk('s1_faq_a1')],
                ['q'=>$hk('s1_faq_q2'),'a'=>$hk('s1_faq_a2')],
                ['q'=>$hk('s1_faq_q3'),'a'=>$hk('s1_faq_a3')],
            ]])
        </article>

        {{-- ═══════════ 2. DASHBOARD ═══════════ --}}
        <article id="section-2" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[2] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">2</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">{{ $hk('s2_title') }}</h2>
                    <p class="text-gray-500 text-sm">{{ $hk('s2_sub') }}</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-6">{!! $hk('s2_p1', ['skill' => $bStrong($hk('s2_p1_skill')), 'quick' => $bStrong($hk('s2_p1_quick'))]) !!}</p>

            {{-- visual mockup --}}
            <div class="light-card rounded-2xl p-5 mb-6">
                <div class="flex flex-wrap items-center justify-between gap-y-2 mb-4">
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider">{{ $hk('s2_mock_welcome') }}</p>
                        <p class="font-bold text-gray-900">{{ $hk('s2_mock_ready') }}</p>
                    </div>
                    <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-orange-50 text-orange-600 text-sm font-bold">
                        <i data-lucide="flame" class="w-4 h-4"></i> {{ $hk('s2_mock_streak') }}
                    </div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach([[$hk('s2_mock_learn'),'route','purple'],[$hk('s2_mock_studio'),'sliders-horizontal','purple'],[$hk('s2_mock_ai'),'sparkles','orange'],[$hk('s2_mock_piano'),'piano','purple']] as $q)
                    <div class="rounded-xl bg-gray-50 p-3 text-center">
                        <div class="w-9 h-9 mx-auto rounded-lg bg-{{ $q[2] }}-100 text-{{ $q[2] }}-600 flex items-center justify-center mb-2"><i data-lucide="{{ $q[1] }}" class="w-4 h-4"></i></div>
                        <p class="text-xs font-semibold text-gray-700">{{ $q[0] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-purple-50/60 border border-purple-100 rounded-xl p-5 mb-6">
                <p class="text-xs font-bold text-purple-700 uppercase tracking-wider mb-3">{{ $hk('how_to_use') }}</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">1.</span><span>{{ $hk('s2_use_1') }}</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">2.</span><span>{{ $hk('s2_use_2') }}</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">3.</span><span>{{ $hk('s2_use_3') }}</span></li>
                </ul>
            </div>

            {!! guideBtn(route('dashboard'), $hk('s2_btn')) !!}

            @include('pages.partials.guide-faq', ['label' => $hk('faq_label'), 'faqs' => [
                ['q'=>$hk('s2_faq_q1'),'a'=>$hk('s2_faq_a1')],
                ['q'=>$hk('s2_faq_q2'),'a'=>$hk('s2_faq_a2')],
                ['q'=>$hk('s2_faq_q3'),'a'=>$hk('s2_faq_a3')],
            ]])
        </article>

        {{-- ═══════════ 3. STRUCTURED LEARNING PATH ═══════════ --}}
        <article id="section-3" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[3] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">3</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">{{ $hk('s3_title') }}</h2>
                    <p class="text-gray-500 text-sm">{{ $hk('s3_sub') }} {!! $bFree !!}</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-4">{{ $hk('s3_p1') }}</p>

            <p class="text-gray-600 leading-relaxed mb-6">{!! $hk('s3_p2', ['link' => '<a href="#section-6" class="text-purple-600 font-semibold hover:underline">'.$hk('s3_p2_link').'</a>']) !!}</p>

            {{-- visual: lesson track --}}
            <div class="light-card rounded-2xl p-5 mb-6">
                <div class="space-y-3">
                    @foreach([[$hk('s3_lesson_1'),'done'],[$hk('s3_lesson_2'),'done'],[$hk('s3_lesson_3'),'active'],[$hk('s3_lesson_4'),'locked']] as $l)
                    <div class="flex items-center gap-3 p-3 rounded-xl {{ $l[1]==='active' ? 'bg-purple-50 border border-purple-200' : 'bg-gray-50' }}">
                        <span class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 {{ $l[1]==='done' ? 'bg-green-100 text-green-600' : ($l[1]==='active' ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-400') }}">
                            <i data-lucide="{{ $l[1]==='done' ? 'check' : ($l[1]==='active' ? 'play' : 'lock') }}" class="w-4 h-4"></i>
                        </span>
                        <span class="text-sm font-medium {{ $l[1]==='locked' ? 'text-gray-400' : 'text-gray-800' }}">{{ $l[0] }}</span>
                        @if($l[1]==='active')<span class="ml-auto text-xs font-bold text-purple-600">{{ $hk('s3_continue') }}</span>@endif
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-purple-50/60 border border-purple-100 rounded-xl p-5 mb-6">
                <p class="text-xs font-bold text-purple-700 uppercase tracking-wider mb-3">{{ $hk('how_to_use') }}</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">1.</span><span>{!! $hk('s3_use_1', ['learn' => $bStrong($hk('s3_use_1_learn'))]) !!}</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">2.</span><span>{!! $hk('s3_use_2', ['start' => $bStrong($hk('s3_use_2_start'))]) !!}</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">3.</span><span>{{ $hk('s3_use_3') }}</span></li>
                </ul>
            </div>

            {!! guideBtn(locale_url('/learn'), $hk('s3_btn')) !!}

            @include('pages.partials.guide-faq', ['label' => $hk('faq_label'), 'faqs' => [
                ['q'=>$hk('s3_faq_q1'),'a'=>$hk('s3_faq_a1')],
                ['q'=>$hk('s3_faq_q2'),'a'=>$hk('s3_faq_a2')],
                ['q'=>$hk('s3_faq_q3'),'a'=>$hk('s3_faq_a3', ['free_lp'=>$freePlan['learning_path_daily_sessions'],'free_q'=>$freePlan['session_question_cap'],'guest_lp'=>$guestLp])],
                ['q'=>$hk('s3_faq_q4'),'a'=>$hk('s3_faq_a4')],
            ]])
        </article>

        {{-- ═══════════ 4. PRACTICE EXERCISES ═══════════ --}}
        <article id="section-4" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[4] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">4</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">{{ $hk('s4_title') }}</h2>
                    <p class="text-gray-500 text-sm">{{ $hk('s4_sub') }}</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-6">{{ $hk('s4_p1') }}</p>

            {{-- exercise cards: what it is + what it trains + direct link --}}
            <div class="grid sm:grid-cols-2 gap-3 mb-6">
                @foreach([
                    [$hk('ex_single_name'),'music-2','single-note-practice',$hk('ex_single_desc')],
                    [$hk('ex_melodic_name'),'trending-up','melodic-interval-practice',$hk('ex_melodic_desc')],
                    [$hk('ex_harmonic_name'),'layers','harmonic-interval-practice',$hk('ex_harmonic_desc')],
                    [$hk('ex_direction_name'),'arrow-up-down','interval-direction-practice',$hk('ex_direction_desc')],
                    [$hk('ex_comparison_name'),'git-compare','interval-comparison-practice',$hk('ex_comparison_desc')],
                    [$hk('ex_construction_name'),'wrench','interval-construction-practice',$hk('ex_construction_desc')],
                    [$hk('ex_chords_name'),'grid-3x3','chord-practice',$hk('ex_chords_desc')],
                    [$hk('ex_scales_name'),'list-music','scale-practice',$hk('ex_scales_desc')],
                    [$hk('ex_rhythm_name'),'activity','rhythm-practice',$hk('ex_rhythm_desc')],
                    [$hk('ex_dictation_name'),'pen-line','melodic-dictation',$hk('ex_dictation_desc')],
                ] as $ex)
                <a href="{{ route('practice', $ex[2]) }}" class="light-card rounded-2xl p-4 group hover:border-purple-300 hover:shadow-md hover:-translate-y-0.5 transition-all flex flex-col">
                    <div class="flex items-center gap-2.5 mb-2">
                        <span class="w-8 h-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center flex-shrink-0"><i data-lucide="{{ $ex[1] }}" class="w-4 h-4"></i></span>
                        <span class="text-sm font-bold text-gray-900 group-hover:text-purple-700 transition-colors">{{ $ex[0] }}</span>
                    </div>
                    <p class="text-xs text-gray-500 leading-relaxed flex-1">{{ $ex[3] }}</p>
                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-purple-600 mt-3">{{ $hk('try', ['name' => $ex[0]]) }} <i data-lucide="arrow-right" class="w-3 h-3 transition-transform group-hover:translate-x-0.5"></i></span>
                </a>
                @endforeach
            </div>

            <p class="text-gray-600 text-sm leading-relaxed mb-6">{!! $bFree !!} {{ $hk('s4_free_note', ['free_lp'=>$freePlan['learning_path_daily_sessions'],'free_studio'=>$freePlan['studio_daily_sessions'],'free_q'=>$freePlan['session_question_cap']]) }} {!! $bPremium !!} {{ $hk('s4_premium_note') }}</p>

            @include('pages.partials.guide-faq', ['label' => $hk('faq_label'), 'faqs' => [
                ['q'=>$hk('s4_faq_q1'),'a'=>$hk('s4_faq_a1')],
                ['q'=>$hk('s4_faq_q2'),'a'=>$hk('s4_faq_a2')],
                ['q'=>$hk('s4_faq_q3'),'a'=>$hk('s4_faq_a3', ['free_lp'=>$freePlan['learning_path_daily_sessions'],'free_studio'=>$freePlan['studio_daily_sessions'],'free_q'=>$freePlan['session_question_cap']])],
                ['q'=>$hk('s4_faq_q4'),'a'=>$hk('s4_faq_a4')],
            ]])
        </article>

        {{-- ═══════════ 5. EXERCISE SETUP STUDIO ═══════════ --}}
        <article id="section-5" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[5] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">5</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">{{ $hk('s5_title') }}</h2>
                    <p class="text-gray-500 text-sm">{{ $hk('s5_sub') }} {!! $bFree !!}</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-6">{{ $hk('s5_p1') }}</p>

            {{-- category options --}}
            <div class="grid sm:grid-cols-2 gap-3 mb-6">
                @foreach([
                    [$hk('cat_intervals_name'),'trending-up',$hk('cat_intervals_desc')],
                    [$hk('cat_single_name'),'music-2',$hk('cat_single_desc')],
                    [$hk('cat_chords_name'),'grid-3x3',$hk('cat_chords_desc')],
                    [$hk('cat_scales_name'),'list-music',$hk('cat_scales_desc')],
                    [$hk('cat_rhythm_name'),'activity',$hk('cat_rhythm_desc')],
                    [$hk('cat_dictation_name'),'pen-line',$hk('cat_dictation_desc')],
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
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-4">{{ $hk('s5_mock_label') }}</p>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-gray-500 mb-1.5">{{ $hk('s5_mock_intervals') }}</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach(['m2','M2','m3','M3','P4','P5'] as $i => $chip)
                            <span class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ $i < 4 ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-500' }}">{{ $chip }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div><p class="text-xs text-gray-500 mb-1.5">{{ $hk('s5_mock_clef') }}</p><div class="px-3 py-2 rounded-lg bg-gray-50 text-sm text-gray-700 font-medium">{{ $hk('s5_mock_clef_val') }}</div></div>
                        <div><p class="text-xs text-gray-500 mb-1.5">{{ $hk('s5_mock_questions') }}</p><div class="px-3 py-2 rounded-lg bg-gray-50 text-sm text-gray-700 font-medium">20</div></div>
                    </div>
                </div>
            </div>

            <div class="bg-purple-50/60 border border-purple-100 rounded-xl p-5 mb-6">
                <p class="text-xs font-bold text-purple-700 uppercase tracking-wider mb-3">{{ $hk('how_to_use') }}</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">1.</span><span>{{ $hk('s5_use_1') }}</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">2.</span><span>{{ $hk('s5_use_2') }}</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">3.</span><span>{!! $hk('s5_use_3', ['launch' => $bStrong($hk('s5_use_3_launch'))]) !!}</span></li>
                </ul>
            </div>

            {!! guideBtn(route('exercise-setup.index'), $hk('s5_btn')) !!}

            @include('pages.partials.guide-faq', ['label' => $hk('faq_label'), 'faqs' => [
                ['q'=>$hk('s5_faq_q1'),'a'=>$hk('s5_faq_a1')],
                ['q'=>$hk('s5_faq_q2'),'a'=>$hk('s5_faq_a2', ['templates'=>$freePlan['saved_plans_limit']])],
                ['q'=>$hk('s5_faq_q3'),'a'=>$hk('s5_faq_a3', ['templates'=>$freePlan['saved_plans_limit']])],
            ]])
        </article>

        {{-- ═══════════ 6. AI-POWERED TRAINING ═══════════ --}}
        <article id="section-6" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[6] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0" style="background:linear-gradient(135deg,#9333ea,#f97316);">6</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">{{ $hk('s6_title') }}</h2>
                    <p class="text-gray-500 text-sm">{{ $hk('s6_sub') }}</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <div class="space-y-4 mb-6">
                <div class="light-card rounded-2xl p-5">
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="w-8 h-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center"><i data-lucide="sparkles" class="w-4 h-4"></i></span>
                        <span class="font-bold text-gray-900">{{ $hk('s6_ai_ex_name') }}</span>
                        {!! $bPremium !!}
                    </div>
                    <p class="text-sm text-gray-600 leading-relaxed">{{ $hk('s6_ai_ex_desc') }}</p>
                </div>
                <div class="light-card rounded-2xl p-5">
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="w-8 h-8 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center"><i data-lucide="bot" class="w-4 h-4"></i></span>
                        <span class="font-bold text-gray-900">{{ $hk('s6_ai_coach_name') }}</span>
                        {!! guideBadge($hk('badge_free_limited')) !!}
                        {!! guideBadge($hk('badge_premium_full'), 'premium') !!}
                    </div>
                    <p class="text-sm text-gray-600 leading-relaxed">{!! $hk('s6_ai_coach_desc', ['plan' => $bStrong($hk('s6_ai_coach_plan'))]) !!}</p>
                </div>
                <div class="light-card rounded-2xl p-5">
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center"><i data-lucide="message-circle" class="w-4 h-4"></i></span>
                        <span class="font-bold text-gray-900">{{ $hk('s6_ai_assistant_name') }}</span>
                        {!! guideBadge($hk('badge_free_account')) !!}
                    </div>
                    <p class="text-sm text-gray-600 leading-relaxed">{{ $hk('s6_ai_assistant_desc') }}</p>
                </div>
            </div>

            {{-- visual: AI insight card --}}
            <div class="rounded-2xl p-5 mb-6 text-white relative overflow-hidden" style="background:linear-gradient(135deg,#7c3aed,#9333ea);">
                <div class="absolute -right-6 -top-6 w-28 h-28 rounded-full bg-orange-400/20"></div>
                <div class="flex items-center gap-2 mb-3 relative">
                    <i data-lucide="sparkles" class="w-4 h-4 text-orange-300"></i>
                    <span class="text-xs font-bold uppercase tracking-wider text-purple-200">{{ $hk('s6_mock_label') }}</span>
                </div>
                <p class="text-sm leading-relaxed relative">{{ $hk('s6_mock_quote') }}</p>
                <div class="mt-4 flex gap-2 relative">
                    <span class="px-3 py-1.5 rounded-lg bg-white/15 text-xs font-semibold backdrop-blur-sm">{{ $hk('s6_mock_focus') }}</span>
                    <span class="px-3 py-1.5 rounded-lg bg-orange-400 text-xs font-semibold text-white">{{ $hk('s6_mock_weekly') }}</span>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                {!! guideBtn(route('ai.exercises'), $hk('s6_btn_ex'), 'sparkles') !!}
                <a href="{{ route('ai-coach.index') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl font-semibold text-sm w-full sm:w-auto border-2 border-purple-200 text-purple-700 bg-white hover:bg-purple-50 transition-all">{{ $hk('s6_btn_coach') }} <i data-lucide="bot" class="w-4 h-4"></i></a>
                <a href="{{ route('ai-chat.index') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl font-semibold text-sm w-full sm:w-auto border-2 border-purple-200 text-purple-700 bg-white hover:bg-purple-50 transition-all">{{ $hk('s6_btn_assistant') }} <i data-lucide="message-circle" class="w-4 h-4"></i></a>
            </div>

            <div class="mt-6">
            @include('pages.partials.guide-faq', ['label' => $hk('faq_label'), 'faqs' => [
                ['q'=>$hk('s6_faq_q1'),'a'=>$hk('s6_faq_a1')],
                ['q'=>$hk('s6_faq_q2'),'a'=>$hk('s6_faq_a2')],
                ['q'=>$hk('s6_faq_q3'),'a'=>$hk('s6_faq_a3')],
            ]])
            </div>
        </article>

        {{-- ═══════════ 7. PIANO STUDIO ═══════════ --}}
        <article id="section-7" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[7] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">7</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">{{ $hk('s7_title') }}</h2>
                    <p class="text-gray-500 text-sm">{{ $hk('s7_sub') }} {!! guideBadge($hk('badge_free_no_account')) !!}</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-6">{{ $hk('s7_p1') }}</p>

            {{-- visual: mini piano --}}
            <div class="light-card rounded-2xl p-5 mb-6">
                <div class="rounded-xl bg-gray-900 p-4">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-xs text-gray-400 uppercase tracking-widest flex items-center gap-1.5"><i data-lucide="piano" class="w-3.5 h-3.5"></i> {{ $hk('s7_mock_label') }}</span>
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
                <p class="text-xs font-bold text-purple-700 uppercase tracking-wider mb-3">{{ $hk('how_to_use') }}</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">1.</span><span>{{ $hk('s7_use_1') }}</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">2.</span><span>{{ $hk('s7_use_2') }}</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">3.</span><span>{{ $hk('s7_use_3') }}</span></li>
                </ul>
            </div>

            {!! guideBtn(locale_url('/piano-studio'), $hk('s7_btn'), 'piano') !!}

            @include('pages.partials.guide-faq', ['label' => $hk('faq_label'), 'faqs' => [
                ['q'=>$hk('s7_faq_q1'),'a'=>$hk('s7_faq_a1')],
                ['q'=>$hk('s7_faq_q2'),'a'=>$hk('s7_faq_a2')],
                ['q'=>$hk('s7_faq_q3'),'a'=>$hk('s7_faq_a3')],
            ]])
        </article>

        {{-- ═══════════ 8. MUSIC GAMES ═══════════ --}}
        <article id="section-8" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[8] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">8</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">{{ $hk('s8_title') }}</h2>
                    <p class="text-gray-500 text-sm">{{ $hk('s8_sub') }}</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-6">{{ $hk('s8_p1') }}</p>

            {{-- game cards --}}
            <div class="grid sm:grid-cols-2 gap-3 mb-6">
                @foreach([
                    [$hk('game_notefall_name'),'arrow-down-to-line',$hk('game_notefall_tag'),$hk('game_notefall_desc')],
                    [$hk('game_noterush_name'),'zap',$hk('game_noterush_tag'),$hk('game_noterush_desc')],
                    [$hk('game_melody_name'),'music',$hk('game_melody_tag'),$hk('game_melody_desc')],
                    [$hk('game_blitz_name'),'timer',$hk('game_blitz_tag'),$hk('game_blitz_desc')],
                    [$hk('game_catcher_name'),'move-horizontal',$hk('game_catcher_tag'),$hk('game_catcher_desc')],
                    [$hk('game_clash_name'),'layers',$hk('game_clash_tag'),$hk('game_clash_desc')],
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
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">{{ $hk('s8_limits_label') }}</p>
                <div class="space-y-2 text-sm text-gray-700">
                    <div class="flex flex-wrap items-center gap-2">{!! $bGuest !!}<span>{{ $hk('s8_limit_guest', ['guest_per'=>$guestPlaysPerGame,'guest_total'=>$guestPlaysTotal]) }}</span></div>
                    <div class="flex flex-wrap items-center gap-2">{!! $bFree !!}<span>{{ $hk('s8_limit_free', ['free_per'=>$freePlan['games_daily_plays_per_type'],'free_total'=>$freePlan['games_daily_plays_total']]) }}</span></div>
                    <div class="flex flex-wrap items-center gap-2">{!! $bPremium !!}<span>{{ $hk('s8_limit_premium') }}</span></div>
                </div>
            </div>

            {!! guideBtn(route('games.index'), $hk('s8_btn'), 'gamepad-2') !!}

            @include('pages.partials.guide-faq', ['label' => $hk('faq_label'), 'faqs' => [
                ['q'=>$hk('s8_faq_q1'),'a'=>$hk('s8_faq_a1')],
                ['q'=>$hk('s8_faq_q2'),'a'=>$hk('s8_faq_a2', ['guest_per'=>$guestPlaysPerGame,'guest_total'=>$guestPlaysTotal])],
                ['q'=>$hk('s8_faq_q3'),'a'=>$hk('s8_faq_a3')],
            ]])
        </article>

        {{-- ═══════════ 9. PROGRESS & ACHIEVEMENTS ═══════════ --}}
        <article id="section-9" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[9] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">9</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">{{ $hk('s9_title') }}</h2>
                    <p class="text-gray-500 text-sm">{{ $hk('s9_sub') }}</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-6">{!! $hk('s9_p1', ['free' => $bFree, 'premium' => $bPremium]) !!}</p>

            {{-- visual: progress bars --}}
            <div class="light-card rounded-2xl p-5 mb-6">
                <div class="grid grid-cols-3 gap-3 mb-4">
                    @foreach([[$hk('s9_stat_accuracy'),'87%','green'],[$hk('s9_stat_streak'),'7d','orange'],[$hk('s9_stat_sessions'),'142','purple']] as $s)
                    <div class="text-center rounded-xl bg-gray-50 py-3">
                        <p class="text-lg font-extrabold text-{{ $s[2] }}-600">{{ $s[1] }}</p>
                        <p class="text-xs text-gray-400">{{ $s[0] }}</p>
                    </div>
                    @endforeach
                </div>
                <div class="space-y-2.5">
                    @foreach([[$hk('s9_bar_intervals'),92],[$hk('s9_bar_chords'),78],[$hk('s9_bar_scales'),64],[$hk('s9_bar_rhythm'),85]] as $b)
                    <div>
                        <div class="flex justify-between text-xs mb-1"><span class="text-gray-600 font-medium">{{ $b[0] }}</span><span class="text-gray-400">{{ $b[1] }}%</span></div>
                        <div class="h-2 rounded-full bg-gray-100 overflow-hidden"><div class="h-full rounded-full bg-purple-500" style="width:{{ $b[1] }}%"></div></div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-purple-50/60 border border-purple-100 rounded-xl p-5 mb-6">
                <p class="text-xs font-bold text-purple-700 uppercase tracking-wider mb-3">{{ $hk('how_to_use') }}</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">1.</span><span>{!! $hk('s9_use_1', ['progress' => $bStrong($hk('s9_use_1_progress'))]) !!}</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">2.</span><span>{{ $hk('s9_use_2') }}</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">3.</span><span>{{ $hk('s9_use_3') }}</span></li>
                </ul>
            </div>

            {!! guideBtn(route('progress'), $hk('s9_btn'), 'trending-up') !!}

            @include('pages.partials.guide-faq', ['label' => $hk('faq_label'), 'faqs' => [
                ['q'=>$hk('s9_faq_q1'),'a'=>$hk('s9_faq_a1')],
                ['q'=>$hk('s9_faq_q2'),'a'=>$hk('s9_faq_a2')],
                ['q'=>$hk('s9_faq_q3'),'a'=>$hk('s9_faq_a3')],
            ]])
        </article>

        {{-- ═══════════ 10. FIND TEACHERS ═══════════ --}}
        <article id="section-10" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[10] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">10</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">{{ $hk('s10_title') }}</h2>
                    <p class="text-gray-500 text-sm">{{ $hk('s10_sub') }}</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-4">{{ $hk('s10_p1') }}</p>

            <p class="text-gray-600 leading-relaxed mb-6">{{ $hk('s10_p2') }}</p>

            {{-- visual: teacher card --}}
            <div class="light-card rounded-2xl p-4 mb-6">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-11 h-11 rounded-full bg-gradient-to-br from-purple-400 to-orange-400 flex items-center justify-center text-white font-bold">A</div>
                    <div><p class="font-bold text-gray-900 text-sm leading-tight">Ayla K.</p><p class="text-xs text-gray-400">{{ $hk('s10_mock_role') }}</p></div>
                    <span class="ml-auto inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-green-100 text-green-700 text-[11px] font-bold"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> {{ $hk('s10_mock_accepting') }}</span>
                </div>
                <div class="flex flex-wrap gap-1.5">
                    <span class="px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 text-[11px] font-semibold">{{ $hk('s10_mock_tag1') }}</span>
                    <span class="px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 text-[11px] font-semibold">{{ $hk('s10_mock_tag2') }}</span>
                    <span class="px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 text-[11px] font-semibold">{{ $hk('s10_mock_tag3') }}</span>
                </div>
            </div>

            <div class="bg-purple-50/60 border border-purple-100 rounded-xl p-5 mb-6">
                <p class="text-xs font-bold text-purple-700 uppercase tracking-wider mb-3">{{ $hk('how_to_use') }}</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">1.</span><span>{{ $hk('s10_use_1') }}</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">2.</span><span>{{ $hk('s10_use_2') }}</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">3.</span><span>{{ $hk('s10_use_3') }}</span></li>
                </ul>
            </div>

            {!! guideBtn(locale_url('/find-teachers'), $hk('s10_btn'), 'users') !!}

            @include('pages.partials.guide-faq', ['label' => $hk('faq_label'), 'faqs' => [
                ['q'=>$hk('s10_faq_q1'),'a'=>$hk('s10_faq_a1')],
                ['q'=>$hk('s10_faq_q2'),'a'=>$hk('s10_faq_a2')],
                ['q'=>$hk('s10_faq_q3'),'a'=>$hk('s10_faq_a3')],
            ]])
        </article>

        {{-- ═══════════ 11. COMMUNITY FEED ═══════════ --}}
        <article id="section-11" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[11] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">11</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">{{ $hk('s11_title') }}</h2>
                    <p class="text-gray-500 text-sm">{{ $hk('s11_sub') }} {!! guideBadge($hk('badge_free_account')) !!}</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-6">{{ $hk('s11_p1') }}</p>

            {{-- visual: feed --}}
            <div class="light-card rounded-2xl p-4 mb-6">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">{{ $hk('s11_mock_label') }}</p>
                <div class="space-y-2 text-xs text-gray-600">
                    <div class="flex items-center gap-2"><span class="w-6 h-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center"><i data-lucide="award" class="w-3 h-3"></i></span> {{ $hk('s11_mock_1') }}</div>
                    <div class="flex items-center gap-2"><span class="w-6 h-6 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center"><i data-lucide="check" class="w-3 h-3"></i></span> {{ $hk('s11_mock_2') }}</div>
                    <div class="flex items-center gap-2"><span class="w-6 h-6 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center"><i data-lucide="star" class="w-3 h-3"></i></span> {{ $hk('s11_mock_3') }}</div>
                </div>
            </div>

            <div class="bg-purple-50/60 border border-purple-100 rounded-xl p-5 mb-6">
                <p class="text-xs font-bold text-purple-700 uppercase tracking-wider mb-3">{{ $hk('how_to_use') }}</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">1.</span><span>{!! $hk('s11_use_1', ['feed' => $bStrong($hk('s11_use_1_feed'))]) !!}</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">2.</span><span>{{ $hk('s11_use_2') }}</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">3.</span><span>{{ $hk('s11_use_3') }}</span></li>
                </ul>
            </div>

            {!! guideBtn(route('feed'), $hk('s11_btn'), 'rss') !!}

            @include('pages.partials.guide-faq', ['label' => $hk('faq_label'), 'faqs' => [
                ['q'=>$hk('s11_faq_q1'),'a'=>$hk('s11_faq_a1')],
                ['q'=>$hk('s11_faq_q2'),'a'=>$hk('s11_faq_a2')],
                ['q'=>$hk('s11_faq_q3'),'a'=>$hk('s11_faq_a3')],
            ]])
        </article>

        {{-- ═══════════ 12. ASSIGNMENTS, MESSAGES & LESSONS ═══════════ --}}
        <article id="section-12" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[12] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">12</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">{{ $hk('s12_title') }}</h2>
                    <p class="text-gray-500 text-sm">{{ $hk('s12_sub') }}</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-6">{{ $hk('s12_p1') }}</p>

            {{-- visual: assignment + appointment --}}
            <div class="grid sm:grid-cols-2 gap-3 mb-6">
                <div class="light-card rounded-2xl p-4">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">{{ $hk('s12_mock_assign_label') }}</p>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2.5 p-2.5 rounded-lg bg-purple-50 border border-purple-100">
                            <span class="w-7 h-7 rounded-lg bg-purple-600 text-white flex items-center justify-center flex-shrink-0"><i data-lucide="clipboard-list" class="w-3.5 h-3.5"></i></span>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-gray-800 leading-tight">{{ $hk('s12_mock_assign_1_title') }}</p>
                                <p class="text-[11px] text-gray-400">{{ $hk('s12_mock_assign_1_meta') }}</p>
                            </div>
                            <span class="ml-auto text-[11px] font-bold text-purple-600 flex-shrink-0">{{ $hk('s12_mock_start') }}</span>
                        </div>
                        <div class="flex items-center gap-2.5 p-2.5 rounded-lg bg-gray-50">
                            <span class="w-7 h-7 rounded-lg bg-green-100 text-green-600 flex items-center justify-center flex-shrink-0"><i data-lucide="check" class="w-3.5 h-3.5"></i></span>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-gray-800 leading-tight">{{ $hk('s12_mock_assign_2_title') }}</p>
                                <p class="text-[11px] text-gray-400">{{ $hk('s12_mock_assign_2_meta') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="light-card rounded-2xl p-4">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">{{ $hk('s12_mock_next_label') }}</p>
                    <div class="rounded-xl bg-gray-50 p-3 mb-2.5">
                        <div class="flex items-center gap-2.5">
                            <span class="w-9 h-9 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center flex-shrink-0"><i data-lucide="calendar-check" class="w-4 h-4"></i></span>
                            <div>
                                <p class="text-xs font-bold text-gray-800">{{ $hk('s12_mock_lesson_title') }}</p>
                                <p class="text-[11px] text-gray-400">{{ $hk('s12_mock_lesson_meta') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <span class="w-6 h-6 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center flex-shrink-0"><i data-lucide="message-circle" class="w-3 h-3"></i></span>
                        {{ $hk('s12_mock_lesson_note') }}
                    </div>
                </div>
            </div>

            <div class="bg-purple-50/60 border border-purple-100 rounded-xl p-5 mb-6">
                <p class="text-xs font-bold text-purple-700 uppercase tracking-wider mb-3">{{ $hk('how_to_use') }}</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">1.</span><span>{!! $hk('s12_use_1', ['assignments' => $bStrong($hk('s12_use_1_assignments'))]) !!}</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">2.</span><span>{!! $hk('s12_use_2', ['messages' => $bStrong($hk('s12_use_2_messages'))]) !!}</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">3.</span><span>{!! $hk('s12_use_3', ['appointments' => $bStrong($hk('s12_use_3_appointments'))]) !!}</span></li>
                </ul>
            </div>

            <div class="flex flex-wrap gap-3">
                {!! guideBtn(route('assignments.index'), $hk('s12_btn_assign'), 'clipboard-list') !!}
                <a href="{{ route('messages') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl font-semibold text-sm w-full sm:w-auto border-2 border-purple-200 text-purple-700 bg-white hover:bg-purple-50 transition-all">{{ $hk('s12_btn_messages') }} <i data-lucide="mail" class="w-4 h-4"></i></a>
                <a href="{{ route('my-appointments.index') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl font-semibold text-sm w-full sm:w-auto border-2 border-purple-200 text-purple-700 bg-white hover:bg-purple-50 transition-all">{{ $hk('s12_btn_appointments') }} <i data-lucide="calendar" class="w-4 h-4"></i></a>
            </div>

            <div class="mt-6">
            @include('pages.partials.guide-faq', ['label' => $hk('faq_label'), 'faqs' => [
                ['q'=>$hk('s12_faq_q1'),'a'=>$hk('s12_faq_a1')],
                ['q'=>$hk('s12_faq_q2'),'a'=>$hk('s12_faq_a2')],
                ['q'=>$hk('s12_faq_q3'),'a'=>$hk('s12_faq_a3')],
                ['q'=>$hk('s12_faq_q4'),'a'=>$hk('s12_faq_a4')],
            ]])
            </div>
        </article>

        {{-- ═══════════ 13. FOR TEACHERS ═══════════ --}}
        <article id="section-13" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[13] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">13</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">{{ $hk('s13_title') }}</h2>
                    <p class="text-gray-500 text-sm">{{ $hk('s13_sub') }} {!! $bTeacher !!}</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-4">{{ $hk('s13_p1') }}</p>

            <p class="text-gray-600 leading-relaxed mb-6">{!! $bTeacher !!} {!! $hk('s13_p2', ['teacher_students'=>$teacherFree['max_students'], 'premium'=>guideBadge($hk('badge_teacher_premium'),'premium')]) !!}</p>

            <div class="bg-purple-50/60 border border-purple-100 rounded-xl p-5 mb-6">
                <p class="text-xs font-bold text-purple-700 uppercase tracking-wider mb-3">{{ $hk('how_it_works_label') }}</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">1.</span><span>{{ $hk('s13_use_1') }}</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">2.</span><span>{{ $hk('s13_use_2') }}</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">3.</span><span>{{ $hk('s13_use_3') }}</span></li>
                </ul>
            </div>

            <div class="flex flex-wrap gap-3">
                {!! guideBtn(locale_url('/teachers'), $hk('s13_btn'), 'briefcase') !!}
                <a href="{{ locale_url('/pricing/teachers-and-schools') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl font-semibold text-sm w-full sm:w-auto border-2 border-purple-200 text-purple-700 bg-white hover:bg-purple-50 transition-all">{{ $hk('s13_btn_pricing') }} <i data-lucide="tag" class="w-4 h-4"></i></a>
            </div>

            <div class="mt-6">
            @include('pages.partials.guide-faq', ['label' => $hk('faq_label'), 'faqs' => [
                ['q'=>$hk('s13_faq_q1'),'a'=>$hk('s13_faq_a1')],
                ['q'=>$hk('s13_faq_q2'),'a'=>$hk('s13_faq_a2', ['teacher_students'=>$teacherFree['max_students']])],
                ['q'=>$hk('s13_faq_q3'),'a'=>$hk('s13_faq_a3')],
            ]])
            </div>
        </article>

        {{-- ═══════════ 14. FOR MUSIC SCHOOLS ═══════════ --}}
        <article id="section-14" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[14] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">14</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">{{ $hk('s14_title') }}</h2>
                    <p class="text-gray-500 text-sm">{{ $hk('s14_sub') }} {!! $bSchool !!}</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-4">{{ $hk('s14_p1') }}</p>

            <p class="text-gray-600 leading-relaxed mb-6">{!! $bSchool !!} {!! $hk('s14_p2', ['school_teachers'=>$schoolFree['max_teachers'], 'school_students'=>$schoolFree['max_students'], 'premium'=>guideBadge($hk('badge_school_premium'),'premium')]) !!}</p>

            {{-- visual: school strip --}}
            <div class="light-card rounded-2xl p-4 mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-orange-400 to-purple-500 flex items-center justify-center text-white flex-shrink-0"><i data-lucide="building-2" class="w-5 h-5"></i></div>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-gray-900 text-sm leading-tight">{{ $hk('s14_mock_name') }}</p>
                        <p class="text-xs text-gray-400">{{ $hk('s14_mock_meta') }}</p>
                    </div>
                    <span class="hidden sm:inline-block px-3 py-1.5 rounded-lg bg-purple-50 text-purple-700 text-xs font-semibold flex-shrink-0">{{ $hk('s14_mock_profile') }}</span>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                {!! guideBtn(locale_url('/schools'), $hk('s14_btn'), 'building-2') !!}
                <a href="{{ locale_url('/request-demo') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl font-semibold text-sm w-full sm:w-auto border-2 border-purple-200 text-purple-700 bg-white hover:bg-purple-50 transition-all">{{ $hk('s14_btn_demo') }} <i data-lucide="calendar" class="w-4 h-4"></i></a>
            </div>

            <div class="mt-6">
            @include('pages.partials.guide-faq', ['label' => $hk('faq_label'), 'faqs' => [
                ['q'=>$hk('s14_faq_q1'),'a'=>$hk('s14_faq_a1')],
                ['q'=>$hk('s14_faq_q2'),'a'=>$hk('s14_faq_a2')],
                ['q'=>$hk('s14_faq_q3'),'a'=>$hk('s14_faq_a3')],
            ]])
            </div>
        </article>

        {{-- ═══════════ 15. PLANS & ACCESS ═══════════ --}}
        <article id="section-15" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[15] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">15</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">{{ $hk('s15_title') }}</h2>
                    <p class="text-gray-500 text-sm">{{ $hk('s15_sub') }}</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-6">{{ $hk('s15_p1') }}</p>

            <div class="light-card rounded-2xl p-5 mb-6">
                <div class="space-y-3 text-sm text-gray-700">
                    <div class="flex flex-wrap items-start gap-2 pb-3 border-b border-gray-100">
                        {!! $bGuest !!}
                        <span class="flex-1 min-w-[200px]">{{ $hk('s15_guest', ['guest_per'=>$guestPlaysPerGame,'guest_total'=>$guestPlaysTotal]) }}</span>
                    </div>
                    <div class="flex flex-wrap items-start gap-2 pb-3 border-b border-gray-100">
                        {!! $bFree !!}
                        <span class="flex-1 min-w-[200px]">{{ $hk('s15_free', ['free_lp'=>$freePlan['learning_path_daily_sessions'],'free_q'=>$freePlan['session_question_cap'],'free_studio'=>$freePlan['studio_daily_sessions'],'templates'=>$freePlan['saved_plans_limit'],'games_per'=>$freePlan['games_daily_plays_per_type'],'games_total'=>$freePlan['games_daily_plays_total'],'ask_ai'=>$freePlan['ask_ai_daily']]) }}</span>
                    </div>
                    <div class="flex flex-wrap items-start gap-2 pb-3 border-b border-gray-100">
                        {!! $bPremium !!}
                        <span class="flex-1 min-w-[200px]">{{ $hk('s15_premium') }}</span>
                    </div>
                    <div class="flex flex-wrap items-start gap-2 pb-3 border-b border-gray-100">
                        {!! $bTeacher !!}
                        <span class="flex-1 min-w-[200px]">{{ $hk('s15_teacher', ['teacher_students'=>$teacherFree['max_students']]) }}</span>
                    </div>
                    <div class="flex flex-wrap items-start gap-2">
                        {!! $bSchool !!}
                        <span class="flex-1 min-w-[200px]">{{ $hk('s15_school', ['school_teachers'=>$schoolFree['max_teachers'],'school_students'=>$schoolFree['max_students']]) }}</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                {!! guideBtn(locale_url('/pricing'), $hk('s15_btn'), 'tag') !!}
                <a href="{{ locale_url('/pricing/teachers-and-schools') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl font-semibold text-sm w-full sm:w-auto border-2 border-purple-200 text-purple-700 bg-white hover:bg-purple-50 transition-all">{{ $hk('s15_btn_teachers') }} <i data-lucide="briefcase" class="w-4 h-4"></i></a>
            </div>

            <div class="mt-6">
            @include('pages.partials.guide-faq', ['label' => $hk('faq_label'), 'faqs' => [
                ['q'=>$hk('s15_faq_q1'),'a'=>$hk('s15_faq_a1')],
                ['q'=>$hk('s15_faq_q2'),'a'=>$hk('s15_faq_a2')],
                ['q'=>$hk('s15_faq_q3'),'a'=>$hk('s15_faq_a3')],
            ]])
            </div>
        </article>

    </div>
</div>

{{-- ============ BOTTOM CTA ============ --}}
<section class="bg-white py-20 px-4">
    <div class="max-w-2xl mx-auto text-center reveal">
        <span class="text-xs font-bold uppercase tracking-[0.2em] text-purple-600 mb-3 block">{{ __('pages.how_it_works.cta_eyebrow') }}</span>
        <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-4">{{ __('pages.how_it_works.cta_title') }}</h2>
        <p class="text-gray-500 text-lg mb-8">{{ __('pages.how_it_works.cta_subtitle') }}</p>
        <div class="flex flex-wrap justify-center gap-3">
            @auth
                <a href="{{ url('/dashboard') }}" class="inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-xl text-white font-semibold shadow-lg shadow-purple-500/25 hover:-translate-y-0.5 transition-all w-full sm:w-auto" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">{{ __('pages.how_it_works.cta_dashboard') }} <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
            @else
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-xl text-white font-semibold shadow-lg shadow-purple-500/25 hover:-translate-y-0.5 transition-all w-full sm:w-auto" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">{{ __('pages.how_it_works.cta_start') }} <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-xl font-semibold border-2 border-gray-200 text-gray-700 hover:bg-gray-50 transition-all w-full sm:w-auto">{{ __('pages.how_it_works.cta_signin') }}</a>
            @endauth
        </div>
    </div>
</section>

@endsection
