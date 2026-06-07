<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Teachers &amp; Schools Plan — Harmoniva</title>
    <meta name="description" content="Harmoniva for educators and institutions. Manage students, assign exercises, track progress school-wide with AI-powered ear training.">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&family=instrument-serif:400,400i" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@0.460.0"></script>
    <script defer src="https://unpkg.com/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'],
                        serif: ['Instrument Serif', 'Georgia', 'serif'],
                    },
                    colors: {
                        primary: {
                            50: '#faf5ff', 100: '#f3e8ff', 200: '#e9d5ff',
                            300: '#d8b4fe', 400: '#c084fc', 500: '#a855f7',
                            600: '#9333ea', 700: '#7c3aed', 800: '#6b21a8', 900: '#581c87',
                        },
                        accent: { 400: '#fb923c', 500: '#f97316', 600: '#ea580c' }
                    }
                }
            }
        }
    </script>

    <style>
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
                <a href="/" class="flex items-center gap-2.5 group">
                    <div class="w-9 h-9 rounded-lg bg-gray-900 flex items-center justify-center shadow-lg shrink-0">
                        <svg viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5">
                            <defs>
                                <linearGradient id="wl-g2" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#9333ea"/>
                                    <stop offset="100%" stop-color="#fb923c"/>
                                </linearGradient>
                            </defs>
                            <rect x="2" y="3" width="5.5" height="22" rx="2" fill="url(#wl-g2)"/>
                            <rect x="20.5" y="3" width="5.5" height="22" rx="2" fill="url(#wl-g2)"/>
                            <path d="M7.5 14 Q11 9 14 14 Q17 19 20.5 14" stroke="url(#wl-g2)" stroke-width="3.5" fill="none" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <span class="text-2xl font-bold tracking-tight leading-none">
                        <span style="background: linear-gradient(135deg,#9333ea,#fb923c); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;">H</span><span class="text-gray-900">armoniva</span>
                    </span>
                </a>
                <div class="flex items-center gap-3">
                    <a href="/#pricing" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-900 transition-colors">
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
    <section class="py-20 sm:py-28 relative overflow-hidden" style="background: linear-gradient(135deg, #fff7ed 0%, #FAF7F2 60%, #fef3c7 100%);">
        <div class="absolute -top-32 -right-32 w-[500px] h-[500px] rounded-full bg-orange-100/60 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-20 -left-20 w-[300px] h-[300px] rounded-full bg-amber-50/80 blur-2xl pointer-events-none"></div>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-orange-100 text-orange-700 text-sm font-semibold mb-6 hero-badge">
                <i data-lucide="users" class="w-4 h-4"></i>
                For Teachers &amp; Music Schools
            </div>

            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 leading-tight mb-6">
                Empower every student's<br>
                <span class="font-serif italic font-normal gradient-text">musical journey</span>
            </h1>

            <p class="text-gray-500 text-lg max-w-2xl mx-auto mb-10">
                Give your students the most advanced AI-powered ear training platform. Manage classrooms, track individual progress, assign exercises — all from one intuitive dashboard.
            </p>

            {{-- Billing Toggle --}}
            <div class="inline-flex items-center gap-4 mb-10 p-1.5 bg-white rounded-2xl shadow-sm border border-gray-100">
                <button @click="billingYearly = false"
                        :class="!billingYearly ? 'bg-gray-900 text-white shadow' : 'text-gray-500 hover:text-gray-700'"
                        class="px-5 py-2 rounded-xl text-sm font-semibold transition-all">
                    Monthly
                </button>
                <button @click="billingYearly = true"
                        :class="billingYearly ? 'text-white shadow' : 'text-gray-500 hover:text-gray-700'"
                        class="px-5 py-2 rounded-xl text-sm font-semibold transition-all relative"
                        :style="billingYearly ? 'background: linear-gradient(135deg,#ea580c,#f97316)' : ''">
                    Yearly
                    <span class="ml-2 text-xs font-bold px-2 py-0.5 rounded-full"
                          :class="billingYearly ? 'bg-white/20 text-white' : 'bg-orange-100 text-orange-700'">
                        50%+ off
                    </span>
                </button>
            </div>

            {{-- Price Card --}}
            <div class="inline-block bg-white rounded-3xl shadow-xl border border-orange-100 p-8 mb-6 min-w-[280px]">
                <div x-show="!billingYearly">
                    <div class="flex items-end justify-center gap-1 mb-1">
                        <span class="text-5xl font-extrabold text-gray-900">$16.90</span>
                        <span class="text-gray-400 text-base mb-2">/month</span>
                    </div>
                    <p class="text-gray-400 text-sm">Billed monthly</p>
                </div>
                <div x-show="billingYearly">
                    <div class="flex items-end justify-center gap-1 mb-1">
                        <span class="text-5xl font-extrabold text-gray-900">$8.25</span>
                        <span class="text-gray-400 text-base mb-2">/month</span>
                    </div>
                    <p class="text-gray-400 text-sm">$99 billed annually</p>
                    <span class="inline-block mt-1 px-3 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-700">Save over $100/year</span>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                @auth
                <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-xl hover:-translate-y-0.5" style="background: linear-gradient(135deg,#ea580c,#f97316);">
                    <i data-lucide="rocket" class="w-5 h-5"></i>
                    Start Now
                </a>
                @else
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-xl hover:-translate-y-0.5" style="background: linear-gradient(135deg,#ea580c,#f97316);">
                    <i data-lucide="rocket" class="w-5 h-5"></i>
                    Start Now — Free Trial
                </a>
                @endauth
                <a href="/#pricing" class="inline-flex items-center gap-2 px-6 py-4 text-base font-medium text-gray-500 hover:text-gray-800 transition-colors">
                    Compare all plans <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
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
                        ['icon' => 'user-check', 'title' => 'Multiple teacher accounts', 'desc' => 'Invite co-teachers and assistants. Assign different roles and permissions so each teacher sees only their own students.'],
                        ['icon' => 'building-2', 'title' => 'Multi-department school setup', 'desc' => 'Manage multiple departments, year levels, or campuses from a single school account with centralized billing.'],
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
                <p class="text-gray-500">See exactly what's included in each plan.</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden reveal">
                {{-- Header --}}
                <div class="grid grid-cols-4 gap-0 border-b border-gray-100">
                    <div class="px-5 py-4 col-span-1"></div>
                    <div class="px-3 py-4 text-center">
                        <div class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Free</div>
                        <div class="text-lg font-extrabold text-gray-900">$0</div>
                    </div>
                    <div class="px-3 py-4 text-center" style="background: rgba(147,51,234,0.04);">
                        <div class="text-xs font-bold uppercase tracking-wider text-primary-600 mb-1">Premium</div>
                        <div class="text-lg font-extrabold text-gray-900">$6.90<span class="text-xs text-gray-400 font-normal">/mo</span></div>
                    </div>
                    <div class="px-3 py-4 text-center" style="background: rgba(234,88,12,0.04);">
                        <div class="text-xs font-bold uppercase tracking-wider text-accent-600 mb-1">Teachers</div>
                        <div class="text-lg font-extrabold text-gray-900">$16.90<span class="text-xs text-gray-400 font-normal">/mo</span></div>
                    </div>
                </div>

                @php
                $compRows = [
                    ['label' => 'Exercises per day',         'free' => '3 / type',  'premium' => 'Unlimited', 'teachers' => 'Unlimited'],
                    ['label' => 'Music games',               'free' => '3 / game',  'premium' => 'Unlimited', 'teachers' => 'Unlimited'],
                    ['label' => 'AI Learning Path',          'free' => false,        'premium' => true,         'teachers' => true],
                    ['label' => 'AI Music Assistant',        'free' => false,        'premium' => true,         'teachers' => true],
                    ['label' => 'Exercise templates',        'free' => 'Up to 3',   'premium' => 'Unlimited', 'teachers' => 'Unlimited'],
                    ['label' => 'Progress analytics',        'free' => 'Basic',     'premium' => 'Advanced',  'teachers' => 'Advanced'],
                    ['label' => 'Student management',        'free' => false,        'premium' => false,        'teachers' => true],
                    ['label' => 'Assign exercises to class', 'free' => false,        'premium' => false,        'teachers' => true],
                    ['label' => 'Multiple teacher accounts', 'free' => false,        'premium' => false,        'teachers' => true],
                    ['label' => 'School-wide analytics',     'free' => false,        'premium' => false,        'teachers' => true],
                    ['label' => 'Custom branding',           'free' => false,        'premium' => false,        'teachers' => true],
                    ['label' => 'Priority support',          'free' => false,        'premium' => false,        'teachers' => true],
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
                    <div class="px-3 py-3.5 text-center text-sm" style="background: rgba(147,51,234,0.02);">
                        @if ($row['premium'] === false)
                            <i data-lucide="minus" class="w-4 h-4 text-gray-300 mx-auto"></i>
                        @elseif ($row['premium'] === true)
                            <i data-lucide="check" class="w-4 h-4 text-green-500 mx-auto"></i>
                        @else
                            <span class="text-primary-700 font-semibold text-xs">{{ $row['premium'] }}</span>
                        @endif
                    </div>
                    <div class="px-3 py-3.5 text-center text-sm" style="background: rgba(234,88,12,0.02);">
                        @if ($row['teachers'] === false)
                            <i data-lucide="minus" class="w-4 h-4 text-gray-300 mx-auto"></i>
                        @elseif ($row['teachers'] === true)
                            <i data-lucide="check" class="w-4 h-4 text-green-500 mx-auto"></i>
                        @else
                            <span class="text-accent-600 font-semibold text-xs">{{ $row['teachers'] }}</span>
                        @endif
                    </div>
                </div>
                @endforeach
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
                ['q' => 'How many students can I manage?',
                 'a' => 'The Teachers & Schools plan supports unlimited students. Whether you\'re a solo teacher with 10 students or a school with hundreds, the plan scales without extra per-seat charges.'],
                ['q' => 'Can I try it before committing to a paid plan?',
                 'a' => 'Absolutely. Create a free account and explore all features with a free trial. Your dashboard will show exactly what unlocks with an upgrade.'],
                ['q' => 'Can multiple teachers share one account?',
                 'a' => 'Yes. The Teachers & Schools plan allows you to invite co-teachers and assistants, each with their own login. Administrators can set permissions to control what each teacher can access.'],
                ['q' => 'Is student data private and secure?',
                 'a' => 'Yes. Student data is encrypted at rest and in transit. We are fully GDPR-compliant and do not share or sell data to third parties. Parental consent workflows are available for minors.'],
                ['q' => 'Can I switch from monthly to yearly billing?',
                 'a' => 'Yes, you can switch billing cycles at any time from your account settings. When switching to yearly, the remaining value of your current month is prorated toward the annual fee.'],
                ['q' => 'Do you offer discounts for large schools or non-profits?',
                 'a' => 'We offer custom enterprise pricing for large institutions and special rates for registered non-profit music education programs. Contact our team to discuss your specific needs.'],
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
                Join Harmoniva today and give your students the most powerful ear training platform available. Set up takes less than 5 minutes.
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
                <a href="/#pricing" class="inline-flex items-center gap-2 px-6 py-4 text-base font-medium text-gray-500 hover:text-gray-800 transition-colors">
                    Compare all plans <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-6 text-sm text-gray-400 mt-8">
                <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>Free to get started</span>
                <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>Cancel anytime</span>
                <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>GDPR compliant</span>
                <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>Priority support</span>
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
