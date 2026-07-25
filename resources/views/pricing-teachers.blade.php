<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.google-analytics')
    @include('partials.posthog')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Teachers &amp; Schools Plan — Harmoniva</title>
    <meta name="description" content="Harmoniva for educators and institutions. Manage students, assign exercises, track progress school-wide — and use Harmoniva completely free when you bring enough Premium students.">
    <link rel="canonical" href="{{ route('pricing.teachers') }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Harmoniva">
    <meta property="og:title" content="Teachers &amp; Schools Plan — Harmoniva">
    <meta property="og:description" content="Harmoniva for educators and institutions. Manage students, assign exercises, track progress school-wide with AI-powered ear training.">
    <meta property="og:url" content="{{ route('pricing.teachers') }}">
    <meta property="og:image" content="{{ asset('images/og-image.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Teachers &amp; Schools Plan — Harmoniva">
    <meta name="twitter:description" content="Harmoniva for educators and institutions. Manage students, assign exercises, track progress school-wide with AI-powered ear training.">
    <meta name="twitter:image" content="{{ asset('images/og-image.png') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&family=instrument-serif:400,400i" rel="stylesheet" />

    @vite('resources/css/marketing.css')
    <script src="https://unpkg.com/lucide@0.460.0"></script>
    <script defer src="https://unpkg.com/alpinejs@3.14.8/dist/cdn.min.js"></script>


    <style>
        [x-cloak] { display: none !important; }
        body { background: #FAF7F2; overflow-x: hidden; }

        .gradient-text {
            background: linear-gradient(135deg, #ea580c 0%, #f97316 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .feature-row {
            border-bottom: 1px solid rgba(0,0,0,0.06);
            transition: background 0.2s ease;
        }
        .feature-row:hover { background: rgba(234,88,12,0.03); }
        .feature-row:last-child { border-bottom: none; }

        .reveal {
            opacity: 0;
            transform: translateY(24px);
            transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .reveal.visible { opacity: 1; transform: translateY(0); }

        .hero-badge { animation: pulse-soft 3s ease-in-out infinite; }
        @keyframes pulse-soft {
            0%, 100% { box-shadow: 0 0 0 0 rgba(234,88,12,0.3); }
            50% { box-shadow: 0 0 0 12px rgba(234,88,12,0); }
        }
    </style>
</head>

<body class="font-sans text-gray-700 antialiased" x-data="{ billingYearly: true }">

    {{-- Navbar --}}
    <nav class="fixed top-0 left-0 right-0 z-50 border-b border-black/10 backdrop-blur-xl bg-white/80">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="/" class="flex items-center group shrink-0">
                    <img src="{{ asset('images/logo-full.png') }}" alt="Harmoniva" width="1374" height="340" class="h-[43px] sm:h-[47px] w-auto">
                </a>
                <div class="flex items-center gap-3">
                    <a href="{{ route('pricing.index') }}" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-900 transition-colors">
                        ← All Plans
                    </a>
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-5 py-2 text-sm font-semibold text-white bg-gradient-to-r from-accent-600 to-accent-500 rounded-lg hover:opacity-90 transition-all shadow-lg">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="px-5 py-2 text-sm font-semibold text-white bg-gradient-to-r from-accent-600 to-accent-500 rounded-lg hover:opacity-90 transition-all shadow-lg">
                            Start Now
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <div class="h-16"></div>

    {{-- Hero --}}
    <section class="py-20 sm:py-24 relative overflow-hidden" style="background: linear-gradient(135deg, #fff7ed 0%, #FAF7F2 60%, #fef3c7 100%);">
        <div class="absolute -top-32 -right-32 w-[500px] h-[500px] rounded-full bg-orange-100/60 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-20 -left-20 w-[300px] h-[300px] rounded-full bg-amber-50/80 blur-2xl pointer-events-none"></div>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-orange-100 text-orange-700 text-sm font-semibold mb-6 hero-badge">
                <i data-lucide="users" class="w-4 h-4"></i>
                For Teachers &amp; Music Schools
            </div>

            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 leading-tight mb-6">
                Teach smarter — and<br>
                <span class="font-serif italic font-normal gradient-text">earn it for free</span>
            </h1>

            <p class="text-gray-500 text-lg max-w-2xl mx-auto mb-8">
                Manage classrooms, assign exercises, and track every student's progress with AI-powered ear training. Bring enough Premium students and your own subscription is <strong class="text-gray-700">completely free</strong>.
            </p>

            {{-- Billing Toggle --}}
            <div class="inline-flex items-center gap-1 p-1.5 bg-white rounded-2xl shadow-sm border border-gray-100">
                <button @click="billingYearly = false"
                        :class="!billingYearly ? 'bg-gray-900 text-white shadow' : 'text-gray-500 hover:text-gray-700'"
                        class="px-6 py-2.5 rounded-xl text-sm font-semibold transition-all">
                    Monthly
                </button>
                <button @click="billingYearly = true"
                        :class="billingYearly ? 'text-white shadow' : 'text-gray-500 hover:text-gray-700'"
                        class="px-6 py-2.5 rounded-xl text-sm font-semibold transition-all relative"
                        :style="billingYearly ? 'background: linear-gradient(135deg,#ea580c,#f97316)' : ''">
                    Yearly
                    <span class="ml-2 text-xs font-bold px-2 py-0.5 rounded-full"
                          :class="billingYearly ? 'bg-white/20 text-white' : 'bg-orange-100 text-orange-700'">
                        Save up to 53%
                    </span>
                </button>
            </div>
            <p class="text-xs text-gray-400 mt-3" x-show="billingYearly" x-cloak>Two months free vs. monthly billing — best value.</p>
        </div>
    </section>


    {{-- Pricing Cards: Teacher + School --}}
    <section class="pb-8 -mt-8 relative z-10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">

                {{-- Teacher Card --}}
                <div class="bg-white rounded-3xl border-2 border-orange-100 shadow-lg p-8 reveal flex flex-col relative overflow-hidden">
                    <div class="absolute -top-16 -right-16 w-40 h-40 rounded-full blur-3xl" style="background:rgba(249,115,22,0.10);"></div>
                    <div class="relative flex flex-col h-full">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-11 h-11 rounded-2xl bg-orange-100 flex items-center justify-center">
                                <i data-lucide="graduation-cap" class="w-6 h-6 text-orange-600"></i>
                            </div>
                            <div>
                                <div class="text-lg font-extrabold text-gray-900">Teachers</div>
                                <div class="text-xs text-gray-400">Solo &amp; private instructors</div>
                            </div>
                        </div>

                        <div x-show="!billingYearly">
                            <div class="flex items-end gap-1 mb-1">
                                <span class="text-5xl font-extrabold text-gray-900">$16.90</span>
                                <span class="text-gray-400 text-base mb-2">/month</span>
                            </div>
                            <p class="text-gray-400 text-sm mb-6">Billed monthly</p>
                        </div>
                        <div x-show="billingYearly" x-cloak>
                            <div class="flex items-end gap-1 mb-1">
                                <span class="text-5xl font-extrabold text-gray-900">$6.67</span>
                                <span class="text-gray-400 text-base mb-2">/month</span>
                            </div>
                            <p class="text-gray-400 text-sm mb-1">$80 billed annually</p>
                            <span class="inline-block mb-6 px-3 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-700">Save 61% · $123/year off</span>
                        </div>

                        <ul class="space-y-3 mb-8">
                            @php $teacherFeatures = [
                                'Unlimited student roster &amp; class groups',
                                'Assign exercises &amp; learning paths',
                                'Per-student progress reports',
                                'AI learning-path generation',
                                'Unlimited exercises, games &amp; templates',
                                'Reusable assignment templates',
                            ]; @endphp
                            @foreach ($teacherFeatures as $f)
                            <li class="flex items-start gap-3 text-sm">
                                <div class="w-5 h-5 rounded-full bg-orange-100 flex items-center justify-center shrink-0 mt-0.5">
                                    <i data-lucide="check" class="w-3 h-3 text-orange-600"></i>
                                </div>
                                <span class="text-gray-700">{!! $f !!}</span>
                            </li>
                            @endforeach
                        </ul>

                        <div class="mt-auto">
                            <div class="flex items-start gap-2 mb-4 p-3 rounded-xl bg-green-50 border border-green-100">
                                <i data-lucide="gift" class="w-4 h-4 text-green-600 shrink-0 mt-0.5"></i>
                                <span class="text-xs text-green-800 font-medium">Bring <strong>10+ Premium students</strong> and this plan is 100% free.</span>
                            </div>
                            @if (auth()->check() && auth()->user()->role === 'teacher')
                            {{-- Teacher account: checkout resolves the Teacher plan from the role. --}}
                            <a href="{{ route('checkout.show') }}" class="block w-full py-3.5 text-center text-sm font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-lg" style="background: linear-gradient(135deg,#ea580c,#f97316);">
                                Upgrade to Teacher Premium
                            </a>
                            @else
                            {{-- Guests (and non-teacher accounts) create a Teacher account first
                                 so checkout can bill the Teacher plan. --}}
                            <a href="{{ route('register', ['role' => 'teacher']) }}" class="block w-full py-3.5 text-center text-sm font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-lg" style="background: linear-gradient(135deg,#ea580c,#f97316);">
                                Start as a Teacher
                            </a>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- School Card --}}
                <div class="bg-gray-900 rounded-3xl shadow-2xl p-8 reveal flex flex-col relative overflow-hidden" style="transition-delay:0.1s">
                    <div class="absolute -top-16 -right-16 w-48 h-48 rounded-full blur-3xl" style="background:rgba(249,115,22,0.25);"></div>
                    <div class="absolute -bottom-10 -left-10 w-36 h-36 rounded-full blur-2xl" style="background:rgba(147,51,234,0.18);"></div>
                    <div class="relative flex flex-col h-full">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-2xl bg-orange-500/20 flex items-center justify-center">
                                    <i data-lucide="building-2" class="w-6 h-6 text-orange-300"></i>
                                </div>
                                <div>
                                    <div class="text-lg font-extrabold text-white">Music Schools</div>
                                    <div class="text-xs text-gray-400">Multi-teacher institutions</div>
                                </div>
                            </div>
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-orange-500/20 text-orange-300 border border-orange-500/30">Best for teams</span>
                        </div>

                        <div x-show="!billingYearly">
                            <div class="flex items-end gap-1 mb-1">
                                <span class="text-5xl font-extrabold text-white">$29.90</span>
                                <span class="text-gray-400 text-base mb-2">/month</span>
                            </div>
                            <p class="text-gray-400 text-sm mb-6">Billed monthly</p>
                        </div>
                        <div x-show="billingYearly" x-cloak>
                            <div class="flex items-end gap-1 mb-1">
                                <span class="text-5xl font-extrabold text-white">$14.08</span>
                                <span class="text-gray-400 text-base mb-2">/month</span>
                            </div>
                            <p class="text-gray-400 text-sm mb-1">$169 billed annually</p>
                            <span class="inline-block mb-6 px-3 py-1 rounded-full text-xs font-bold" style="background:rgba(249,115,22,0.2);color:#fb923c;border:1px solid rgba(249,115,22,0.3);">Save over 50% · $189/year off</span>
                        </div>

                        <ul class="space-y-3 mb-8">
                            @php $schoolFeatures = [
                                'Everything in Teachers, plus:',
                                'Multiple teacher &amp; assistant accounts',
                                'Multi-department / campus setup',
                                'School-wide analytics dashboard',
                                'Centralized billing &amp; invoices',
                                'Custom branding &amp; priority support',
                            ]; @endphp
                            @foreach ($schoolFeatures as $i => $f)
                            <li class="flex items-start gap-3 text-sm">
                                <div class="w-5 h-5 rounded-full {{ $i === 0 ? 'bg-white/10' : 'bg-orange-500/20 border border-orange-500/30' }} flex items-center justify-center shrink-0 mt-0.5">
                                    <i data-lucide="{{ $i === 0 ? 'plus' : 'check' }}" class="w-3 h-3 {{ $i === 0 ? 'text-gray-300' : 'text-orange-300' }}"></i>
                                </div>
                                <span class="{{ $i === 0 ? 'text-gray-400 font-semibold' : 'text-gray-300' }}">{!! $f !!}</span>
                            </li>
                            @endforeach
                        </ul>

                        <div class="mt-auto">
                            <div class="flex items-start gap-2 mb-4 p-3 rounded-xl bg-green-500/10 border border-green-500/20">
                                <i data-lucide="gift" class="w-4 h-4 text-green-400 shrink-0 mt-0.5"></i>
                                <span class="text-xs text-green-300 font-medium">Register <strong>20+ Premium students</strong> and your school uses Harmoniva 100% free.</span>
                            </div>
                            @if (auth()->check() && auth()->user()->role === 'school')
                            {{-- School account: checkout resolves the School plan from the role. --}}
                            <a href="{{ route('checkout.show') }}" class="block w-full py-3.5 text-center text-sm font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-lg" style="background: linear-gradient(135deg,#ea580c,#f97316);">
                                Upgrade to School Premium
                            </a>
                            @else
                            {{-- Guests (and non-school accounts) create a School account first
                                 so checkout can bill the School plan. --}}
                            <a href="{{ route('register', ['role' => 'school']) }}" class="block w-full py-3.5 text-center text-sm font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-lg" style="background: linear-gradient(135deg,#ea580c,#f97316);">
                                Start a School Account
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <p class="text-center text-xs text-gray-400 mt-6 reveal">All plans include a free tier to explore first — no credit card required to get started.</p>
        </div>
    </section>


    {{-- Bring Premium Students, Use Free --}}
    <section class="py-20" style="background: linear-gradient(135deg, #f0fdf4 0%, #FAF7F2 55%, #ecfdf5 100%);">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 reveal">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-green-100 text-green-700 text-sm font-semibold mb-5">
                    <i data-lucide="gift" class="w-4 h-4"></i>
                    Bring Premium Students, Use Harmoniva Free
                </div>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-4">
                    Your students grow.<br><span class="font-serif italic font-normal" style="color:#16a34a;">Your subscription is on us.</span>
                </h2>
                <p class="text-gray-500 max-w-2xl mx-auto">
                    When enough of your students go Premium, you've already brought more than enough value to Harmoniva — so your own account becomes completely free, with every feature unlocked.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 reveal">
                {{-- Teacher tier --}}
                <div class="bg-white rounded-3xl border border-green-100 shadow-sm p-8">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-12 h-12 rounded-2xl bg-orange-100 flex items-center justify-center">
                            <i data-lucide="graduation-cap" class="w-6 h-6 text-orange-600"></i>
                        </div>
                        <div>
                            <div class="font-extrabold text-gray-900">Teachers</div>
                            <div class="text-xs text-gray-400">Individual instructors</div>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2 mb-3">
                        <span class="text-5xl font-extrabold" style="color:#16a34a;">10+</span>
                        <span class="text-gray-500 font-medium">Premium students</span>
                    </div>
                    <p class="text-sm text-gray-500 mb-5 leading-relaxed">
                        Teachers with <strong>10 or more Premium students</strong> use Harmoniva completely free — the full Teacher plan, every feature, automatically applied.
                    </p>
                    <ul class="space-y-2.5">
                        @foreach (['Applied automatically — no request needed', 'All Teacher features unlocked', 'Stays free as long as you qualify'] as $li)
                        <li class="flex items-center gap-2.5 text-sm text-gray-700">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-green-500 shrink-0"></i> {{ $li }}
                        </li>
                        @endforeach
                    </ul>
                </div>

                {{-- School tier --}}
                <div class="bg-white rounded-3xl border border-green-100 shadow-sm p-8">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-12 h-12 rounded-2xl bg-orange-100 flex items-center justify-center">
                            <i data-lucide="building-2" class="w-6 h-6 text-orange-600"></i>
                        </div>
                        <div>
                            <div class="font-extrabold text-gray-900">Music Schools</div>
                            <div class="text-xs text-gray-400">Institutions &amp; academies</div>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2 mb-3">
                        <span class="text-5xl font-extrabold" style="color:#16a34a;">20+</span>
                        <span class="text-gray-500 font-medium">Premium students</span>
                    </div>
                    <p class="text-sm text-gray-500 mb-5 leading-relaxed">
                        Schools that reach <strong>20 or more Premium students</strong> — or register that many into their account — unlock the entire platform for free after a quick admin approval.
                    </p>
                    <ul class="space-y-2.5">
                        @foreach (['Reviewed &amp; approved by the Harmoniva team', 'Every school feature unlocked for free', 'Tracked transparently in your dashboard'] as $li)
                        <li class="flex items-center gap-2.5 text-sm text-gray-700">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-green-500 shrink-0"></i> {!! $li !!}
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="text-center mt-8 reveal">
                <p class="text-sm text-gray-500">
                    Progress toward free access is tracked live on your dashboard — you'll always know how many more Premium students you need.
                </p>
            </div>
        </div>
    </section>


    {{-- What's Included --}}
    <section class="py-20 bg-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14 reveal">
                <span class="text-xs font-bold uppercase tracking-[0.2em] text-accent-600 mb-3 block">Everything Included</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-4">
                    Built for educators,<br><span class="font-serif italic font-normal gradient-text">at every scale</span>
                </h2>
                <p class="text-gray-500 max-w-xl mx-auto">From solo music teachers to full conservatories — Harmoniva scales with you.</p>
            </div>

            @php
            $featureGroups = [
                [
                    'icon' => 'layout-dashboard',
                    'color' => 'text-orange-600',
                    'bg' => 'bg-orange-100',
                    'title' => 'Classroom Management',
                    'features' => [
                        ['icon' => 'users', 'title' => 'Student roster management', 'desc' => 'Add, organize, and manage all your students from a single dashboard. Create class groups and sub-groups by level or instrument.'],
                        ['icon' => 'folder-plus', 'title' => 'Class groups & cohorts', 'desc' => 'Organize students into groups — by grade, instrument, skill level, or custom criteria. Assign exercises to entire groups at once.'],
                        ['icon' => 'user-check', 'title' => 'Multiple teacher accounts', 'desc' => 'Invite co-teachers and assistants. Assign different roles and permissions so each teacher sees only their own students. (Schools)'],
                        ['icon' => 'building-2', 'title' => 'Multi-department school setup', 'desc' => 'Manage multiple departments, year levels, or campuses from a single school account with centralized billing. (Schools)'],
                    ]
                ],
                [
                    'icon' => 'clipboard-list',
                    'color' => 'text-primary-600',
                    'bg' => 'bg-primary-100',
                    'title' => 'Exercise Assignment',
                    'features' => [
                        ['icon' => 'send', 'title' => 'Assign exercises to students', 'desc' => 'Push specific exercises or full learning paths directly to your students\' dashboards. Set due dates and minimum completion targets.'],
                        ['icon' => 'sliders-horizontal', 'title' => 'Custom difficulty settings', 'desc' => 'Fine-tune exercise parameters — allowed notes, interval types, BPM ranges, distractor counts — for precise pedagogical targeting.'],
                        ['icon' => 'repeat', 'title' => 'Reusable exercise templates', 'desc' => 'Save your most-used exercise configurations as named templates and reuse them across classes, saving hours of setup time.'],
                        ['icon' => 'calendar-check', 'title' => 'Homework & practice tracking', 'desc' => 'See which students completed assigned work and when. Automatically remind students who fall behind on their practice goals.'],
                    ]
                ],
                [
                    'icon' => 'bar-chart-3',
                    'color' => 'text-blue-600',
                    'bg' => 'bg-blue-100',
                    'title' => 'Analytics & Reporting',
                    'features' => [
                        ['icon' => 'trending-up', 'title' => 'Per-student progress reports', 'desc' => 'Detailed accuracy charts, session history, streak tracking, and skill-by-skill breakdowns for every student over any time period.'],
                        ['icon' => 'layers', 'title' => 'Class-wide analytics dashboard', 'desc' => 'Compare performance across your entire class with aggregated charts showing which exercises need more attention.'],
                        ['icon' => 'file-text', 'title' => 'Exportable PDF reports', 'desc' => 'Generate and download professional progress reports to share with students, parents, or administrators at any time.'],
                        ['icon' => 'bell', 'title' => 'Smart alerts & notifications', 'desc' => 'Get notified when a student\'s accuracy drops significantly, completes a milestone, or hasn\'t practiced in several days.'],
                    ]
                ],
                [
                    'icon' => 'bot',
                    'color' => 'text-cyan-600',
                    'bg' => 'bg-cyan-100',
                    'title' => 'AI-Powered Tools',
                    'features' => [
                        ['icon' => 'sparkles', 'title' => 'AI learning path generation', 'desc' => 'Our AI analyzes each student\'s performance and generates personalized learning paths targeting their specific weaknesses.'],
                        ['icon' => 'message-circle', 'title' => 'AI music assistant for students', 'desc' => 'Every student gets access to the Harmoniva AI chat — 24/7 support for music theory questions, exercise explanations, and practice tips.'],
                        ['icon' => 'brain', 'title' => 'Adaptive difficulty engine', 'desc' => 'Exercises automatically scale in difficulty as students improve, keeping them in the optimal learning zone at all times.'],
                        ['icon' => 'lightbulb', 'title' => 'AI-generated exercise recommendations', 'desc' => 'Teachers receive weekly AI-curated suggestions on which exercises to assign based on class-wide performance patterns.'],
                    ]
                ],
                [
                    'icon' => 'shield-check',
                    'color' => 'text-green-600',
                    'bg' => 'bg-green-100',
                    'title' => 'Institution & Support',
                    'features' => [
                        ['icon' => 'credit-card', 'title' => 'Centralized billing & invoices', 'desc' => 'One invoice for your whole institution. Upgrade, downgrade, or add seats without hassle. VAT invoices available.'],
                        ['icon' => 'palette', 'title' => 'Custom branding & white-label', 'desc' => 'Add your school logo and brand colors to the student-facing interface. Create a seamless experience that matches your institution.'],
                        ['icon' => 'headphones', 'title' => 'Priority customer support', 'desc' => 'Dedicated support channel with guaranteed response times. Onboarding assistance and training sessions for your team.'],
                        ['icon' => 'lock', 'title' => 'Data privacy & GDPR compliance', 'desc' => 'Student data is stored securely and in compliance with GDPR. Parent consent flows included for under-13 students.'],
                    ]
                ],
            ];
            @endphp

            <div class="space-y-16">
                @foreach ($featureGroups as $gi => $group)
                <div class="reveal" style="transition-delay:{{ $gi * 0.08 }}s">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 rounded-2xl {{ $group['bg'] }} flex items-center justify-center">
                            <i data-lucide="{{ $group['icon'] }}" class="w-6 h-6 {{ $group['color'] }}"></i>
                        </div>
                        <h3 class="text-xl font-extrabold text-gray-900">{{ $group['title'] }}</h3>
                    </div>
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        @foreach ($group['features'] as $fi => $feat)
                        <div class="feature-row flex items-start gap-5 px-6 py-5">
                            <div class="w-10 h-10 rounded-xl {{ $group['bg'] }} flex items-center justify-center shrink-0 mt-0.5">
                                <i data-lucide="{{ $feat['icon'] }}" class="w-5 h-5 {{ $group['color'] }}"></i>
                            </div>
                            <div>
                                <h4 class="text-gray-900 font-bold text-sm mb-1">{{ $feat['title'] }}</h4>
                                <p class="text-gray-500 text-sm leading-relaxed">{{ $feat['desc'] }}</p>
                            </div>
                            <div class="shrink-0 mt-1">
                                <i data-lucide="check" class="w-5 h-5 text-green-500"></i>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>


    {{-- Comparison Table --}}
    <section class="py-20" style="background: #FAF7F2;">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 reveal">
                <h2 class="text-3xl font-extrabold text-gray-900 mb-3">Plan Comparison</h2>
                <p class="text-gray-500">Free to explore · Teacher &amp; School unlock the full toolkit.</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden reveal">
                <div class="overflow-x-auto"><div class="min-w-[640px]">
                {{-- Header --}}
                <div class="grid grid-cols-4 gap-0 border-b border-gray-100">
                    <div class="px-5 py-4 col-span-1"></div>
                    <div class="px-3 py-4 text-center">
                        <div class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Free</div>
                        <div class="text-lg font-extrabold text-gray-900">$0</div>
                    </div>
                    <div class="px-3 py-4 text-center" style="background: rgba(234,88,12,0.05);">
                        <div class="text-xs font-bold uppercase tracking-wider text-accent-600 mb-1">Teacher</div>
                        <div class="text-lg font-extrabold text-gray-900" x-show="!billingYearly">$16.90<span class="text-xs text-gray-400 font-normal">/mo</span></div>
                        <div class="text-lg font-extrabold text-gray-900" x-show="billingYearly" x-cloak>$6.67<span class="text-xs text-gray-400 font-normal">/mo</span></div>
                    </div>
                    <div class="px-3 py-4 text-center" style="background: rgba(147,51,234,0.05);">
                        <div class="text-xs font-bold uppercase tracking-wider text-primary-600 mb-1">School</div>
                        <div class="text-lg font-extrabold text-gray-900" x-show="!billingYearly">$29.90<span class="text-xs text-gray-400 font-normal">/mo</span></div>
                        <div class="text-lg font-extrabold text-gray-900" x-show="billingYearly" x-cloak>$14.08<span class="text-xs text-gray-400 font-normal">/mo</span></div>
                    </div>
                </div>

                @php
                $compRows = [
                    ['label' => 'Exercises & games per day',   'free' => '5 / day',   'teacher' => 'Unlimited', 'school' => 'Unlimited'],
                    ['label' => 'AI Learning Path & Assistant', 'free' => false,       'teacher' => true,        'school' => true],
                    ['label' => 'Exercise templates',          'free' => 'Up to 3',   'teacher' => 'Unlimited', 'school' => 'Unlimited'],
                    ['label' => 'Progress analytics',          'free' => 'Basic',     'teacher' => 'Advanced',  'school' => 'Advanced'],
                    ['label' => 'Student roster & management',  'free' => false,       'teacher' => true,        'school' => true],
                    ['label' => 'Assign exercises to class',   'free' => false,       'teacher' => true,        'school' => true],
                    ['label' => 'Active assignments',          'free' => '2',         'teacher' => 'Unlimited', 'school' => 'Unlimited'],
                    ['label' => 'Multiple teacher accounts',   'free' => false,       'teacher' => false,       'school' => true],
                    ['label' => 'Multi-department setup',      'free' => false,       'teacher' => false,       'school' => true],
                    ['label' => 'School-wide analytics',       'free' => false,       'teacher' => false,       'school' => true],
                    ['label' => 'Custom branding',             'free' => false,       'teacher' => false,       'school' => true],
                    ['label' => 'Priority support',            'free' => false,       'teacher' => true,        'school' => true],
                    ['label' => 'Free when you bring Premium students', 'free' => false, 'teacher' => '10+ students', 'school' => '20+ students'],
                ];
                @endphp

                @foreach ($compRows as $ri => $row)
                <div class="grid grid-cols-4 gap-0 border-b border-gray-50 last:border-0 {{ $ri % 2 === 0 ? '' : 'bg-gray-50/50' }}">
                    <div class="px-5 py-3.5 text-sm text-gray-700 font-medium">{{ $row['label'] }}</div>
                    <div class="px-3 py-3.5 text-center text-sm">
                        @if ($row['free'] === false)
                            <i data-lucide="minus" class="w-4 h-4 text-gray-300 mx-auto"></i>
                        @elseif ($row['free'] === true)
                            <i data-lucide="check" class="w-4 h-4 text-green-500 mx-auto"></i>
                        @else
                            <span class="text-gray-500">{{ $row['free'] }}</span>
                        @endif
                    </div>
                    <div class="px-3 py-3.5 text-center text-sm" style="background: rgba(234,88,12,0.02);">
                        @if ($row['teacher'] === false)
                            <i data-lucide="minus" class="w-4 h-4 text-gray-300 mx-auto"></i>
                        @elseif ($row['teacher'] === true)
                            <i data-lucide="check" class="w-4 h-4 text-green-500 mx-auto"></i>
                        @else
                            <span class="text-accent-600 font-semibold text-xs">{{ $row['teacher'] }}</span>
                        @endif
                    </div>
                    <div class="px-3 py-3.5 text-center text-sm" style="background: rgba(147,51,234,0.02);">
                        @if ($row['school'] === false)
                            <i data-lucide="minus" class="w-4 h-4 text-gray-300 mx-auto"></i>
                        @elseif ($row['school'] === true)
                            <i data-lucide="check" class="w-4 h-4 text-green-500 mx-auto"></i>
                        @else
                            <span class="text-primary-700 font-semibold text-xs">{{ $row['school'] }}</span>
                        @endif
                    </div>
                </div>
                @endforeach
                </div></div>
            </div>
        </div>
    </section>


    {{-- FAQ --}}
    <section class="py-20 bg-white">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 reveal">
                <h2 class="text-3xl font-extrabold text-gray-900 mb-3">Frequently Asked Questions</h2>
            </div>

            @php
            $faqs = [
                ['q' => 'How do I use Harmoniva for free as a teacher?',
                 'a' => 'Teachers with 10 or more active Premium students get the full Teacher plan completely free — it\'s applied automatically, no request needed. Your dashboard shows your live progress toward the threshold, and free access continues for as long as you keep enough Premium students.'],
                ['q' => 'How does free access work for music schools?',
                 'a' => 'Schools that reach 20 or more Premium students — or register that many into their school account — qualify for 100% free access to the entire platform. School grants go through a quick approval by the Harmoniva team, after which every feature unlocks at no cost.'],
                ['q' => 'What\'s the difference between Monthly and Yearly billing?',
                 'a' => 'Yearly billing gives you the equivalent of several months free. Teachers pay $80/year (about $6.67/month, 61% off) and schools pay $169/year (about $14.08/month, over 50% off the monthly rate). You can switch billing cycles anytime.'],
                ['q' => 'How many students can I manage?',
                 'a' => 'Both the Teacher and School plans support unlimited students. Whether you\'re a solo teacher with 10 students or a school with hundreds, there are no per-seat charges for students.'],
                ['q' => 'Can multiple teachers share one account?',
                 'a' => 'That\'s what the School plan is for. Schools can invite co-teachers and assistants, each with their own login and permissions, all under one centralized account and invoice. The Teacher plan is designed for a single instructor.'],
                ['q' => 'Is student data private and secure?',
                 'a' => 'Yes. Student data is encrypted at rest and in transit. We are fully GDPR-compliant and never share or sell data to third parties. Parental consent workflows are available for minors.'],
                ['q' => 'Do you offer discounts for large schools or non-profits?',
                 'a' => 'We offer custom pricing for large institutions and special rates for registered non-profit music education programs. Contact our team to discuss your needs — and remember, at 20+ Premium students your school can use Harmoniva entirely free.'],
            ];
            @endphp

            <div class="space-y-3" x-data="{ open: null }">
                @foreach ($faqs as $fi => $faq)
                <div class="bg-gray-50 rounded-2xl border border-gray-100 overflow-hidden reveal" style="transition-delay:{{ $fi * 0.05 }}s">
                    <button @click="open === {{ $fi }} ? open = null : open = {{ $fi }}"
                            class="w-full flex items-center justify-between px-6 py-5 text-left gap-4">
                        <span class="font-bold text-gray-900 text-sm">{{ $faq['q'] }}</span>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 shrink-0 transition-transform" :class="open === {{ $fi }} ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open === {{ $fi }}" x-collapse>
                        <div class="px-6 pb-5 text-sm text-gray-500 leading-relaxed border-t border-gray-100 pt-4">
                            {{ $faq['a'] }}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>


    {{-- CTA --}}
    <section class="py-24 relative overflow-hidden" style="background: linear-gradient(135deg, #fff7ed 0%, #FAF7F2 50%, #fef3c7 100%);">
        <div class="absolute -top-20 -right-20 w-[400px] h-[400px] rounded-full bg-orange-100/50 blur-3xl pointer-events-none"></div>

        <div class="max-w-3xl mx-auto px-4 sm:px-6 text-center relative reveal">
            <div class="w-16 h-16 mx-auto rounded-2xl flex items-center justify-center mb-8 shadow-xl hero-badge" style="background: linear-gradient(135deg,#ea580c,#f97316);">
                <i data-lucide="graduation-cap" class="w-8 h-8 text-white"></i>
            </div>

            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900 mb-5">
                Ready to transform your<br><span class="font-serif italic font-normal gradient-text">music classroom?</span>
            </h2>
            <p class="text-gray-500 text-lg mb-10 max-w-xl mx-auto">
                Join Harmoniva today. Set up takes less than 5 minutes — and the more your students grow, the closer you get to using it all for free.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                @auth
                <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-xl hover:-translate-y-0.5" style="background: linear-gradient(135deg,#ea580c,#f97316);">
                    <i data-lucide="rocket" class="w-5 h-5"></i>
                    Go to Dashboard
                </a>
                @else
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-xl hover:-translate-y-0.5" style="background: linear-gradient(135deg,#ea580c,#f97316);">
                    <i data-lucide="rocket" class="w-5 h-5"></i>
                    Start Free — No Card Needed
                </a>
                @endauth
                <a href="{{ route('pricing.index') }}" class="inline-flex items-center gap-2 px-6 py-4 text-base font-medium text-gray-500 hover:text-gray-800 transition-colors">
                    Student plans <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-6 text-sm text-gray-400 mt-8">
                <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>Free to get started</span>
                <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>Cancel anytime</span>
                <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>GDPR compliant</span>
                <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>Free at 10+/20+ Premium students</span>
            </div>
        </div>
    </section>

    @include('partials.footer')

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();

            const reveals = document.querySelectorAll('.reveal');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
            }, { threshold: 0.1, rootMargin: '0px 0px -30px 0px' });
            reveals.forEach(el => observer.observe(el));
        });
    </script>
</body>
</html>
