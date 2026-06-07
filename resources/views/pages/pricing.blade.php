@extends('layouts.standalone')

@section('title', 'Pricing — Choose Your Plan')
@section('description', 'Simple, transparent pricing for musicians, students, teachers, and music schools. Start free — no credit card required.')

@section('body-attrs', 'x-data="{ billingYearly: false }"')

@section('content')

{{-- Hero --}}
<section class="py-20 sm:py-28 relative overflow-hidden" style="background: linear-gradient(135deg, #faf5ff 0%, #FAF7F2 60%, #fef3c7 100%);">
    <div class="absolute -top-32 -right-32 w-[500px] h-[500px] rounded-full bg-purple-100/50 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-20 -left-20 w-[300px] h-[300px] rounded-full bg-orange-50/60 blur-2xl pointer-events-none"></div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary-100 text-primary-700 text-sm font-semibold mb-6 hero-badge">
            <i data-lucide="sparkles" class="w-4 h-4"></i>
            Simple, Transparent Pricing
        </div>

        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 leading-tight mb-5">
            Start free, upgrade<br>
            <span class="font-serif italic font-normal gradient-text">when you're ready</span>
        </h1>

        <p class="text-gray-500 text-lg max-w-xl mx-auto mb-10">
            No credit card required to get started. Train your ear with 3 exercises per type every day — forever free. Upgrade for unlimited access, AI features, and more.
        </p>

        {{-- Billing Toggle --}}
        <div class="inline-flex items-center gap-1 mb-12 p-1.5 bg-white rounded-2xl shadow-sm border border-gray-100">
            <button @click="billingYearly = false"
                    :class="!billingYearly ? 'bg-gray-900 text-white shadow' : 'text-gray-500 hover:text-gray-700'"
                    class="px-6 py-2.5 rounded-xl text-sm font-semibold transition-all">
                Monthly
            </button>
            <button @click="billingYearly = true"
                    :class="billingYearly ? 'text-white shadow' : 'text-gray-500 hover:text-gray-700'"
                    class="px-6 py-2.5 rounded-xl text-sm font-semibold transition-all relative"
                    :style="billingYearly ? 'background: linear-gradient(135deg,#9333ea,#7c3aed)' : ''">
                Yearly
                <span class="ml-2 text-xs font-bold px-2 py-0.5 rounded-full"
                      :class="billingYearly ? 'bg-white/20 text-white' : 'bg-primary-100 text-primary-700'">
                    Save 28%
                </span>
            </button>
        </div>
    </div>
</section>

{{-- Plan Cards --}}
<section class="pb-20 -mt-6 relative z-10">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">

            {{-- Free Card --}}
            <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-8 reveal">
                <div class="mb-6">
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-gray-100 text-gray-500 mb-4">Free Trial</span>
                    <div class="flex items-end gap-1 mb-1">
                        <span class="text-5xl font-extrabold text-gray-900">$0</span>
                        <span class="text-gray-400 text-base mb-2">/ forever</span>
                    </div>
                    <p class="text-gray-400 text-sm">No credit card required</p>
                </div>

                <ul class="space-y-3.5 mb-8">
                    @php $freeFeatures = [
                        ['icon' => 'music-2', 'text' => '3 exercises per day, per type'],
                        ['icon' => 'gamepad-2', 'text' => '3 music games per day'],
                        ['icon' => 'layers', 'text' => 'All 10+ exercise types'],
                        ['icon' => 'save', 'text' => 'Up to 3 saved templates'],
                        ['icon' => 'bar-chart-2', 'text' => 'Basic progress tracking'],
                        ['icon' => 'x', 'text' => 'AI mode (Premium only)', 'disabled' => true],
                        ['icon' => 'x', 'text' => 'Unlimited exercises (Premium only)', 'disabled' => true],
                    ]; @endphp
                    @foreach ($freeFeatures as $f)
                    <li class="flex items-start gap-3 text-sm {{ ($f['disabled'] ?? false) ? 'opacity-40' : '' }}">
                        <div class="w-5 h-5 rounded-full {{ ($f['disabled'] ?? false) ? 'bg-gray-100' : 'bg-green-100' }} flex items-center justify-center shrink-0 mt-0.5">
                            <i data-lucide="{{ ($f['disabled'] ?? false) ? 'x' : 'check' }}" class="w-3 h-3 {{ ($f['disabled'] ?? false) ? 'text-gray-400' : 'text-green-600' }}"></i>
                        </div>
                        <span class="{{ ($f['disabled'] ?? false) ? 'text-gray-400 line-through' : 'text-gray-700' }}">{{ $f['text'] }}</span>
                    </li>
                    @endforeach
                </ul>

                @auth
                <a href="{{ url('/dashboard') }}" class="block w-full py-3.5 text-center text-sm font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                    Go to Dashboard
                </a>
                @else
                <a href="{{ route('register') }}" class="block w-full py-3.5 text-center text-sm font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                    Start Free — No Card Needed
                </a>
                @endauth
            </div>

            {{-- Premium Card --}}
            <div class="bg-gray-900 rounded-3xl shadow-2xl p-8 reveal relative overflow-hidden" style="transition-delay:0.1s">
                <div class="absolute -top-16 -right-16 w-48 h-48 rounded-full blur-3xl" style="background:rgba(147,51,234,0.25);"></div>
                <div class="absolute -bottom-10 -left-10 w-36 h-36 rounded-full blur-2xl" style="background:rgba(249,115,22,0.2);"></div>

                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider text-white" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">Premium</span>
                        <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-orange-500/20 text-orange-300 border border-orange-500/30">Most Popular</span>
                    </div>

                    <div x-show="!billingYearly">
                        <div class="flex items-end gap-1 mb-1">
                            <span class="text-5xl font-extrabold text-white">$6.90</span>
                            <span class="text-gray-400 text-base mb-2">/month</span>
                        </div>
                        <p class="text-gray-400 text-sm mb-6">Billed monthly</p>
                    </div>
                    <div x-show="billingYearly">
                        <div class="flex items-end gap-1 mb-1">
                            <span class="text-5xl font-extrabold text-white">$4.99</span>
                            <span class="text-gray-400 text-base mb-2">/month</span>
                        </div>
                        <p class="text-gray-400 text-sm mb-1">$59.88 billed annually</p>
                        <span class="inline-block mb-6 px-3 py-1 rounded-full text-xs font-bold" style="background:rgba(249,115,22,0.2);color:#fb923c;border:1px solid rgba(249,115,22,0.3);">Save $23/year</span>
                    </div>

                    <ul class="space-y-3.5 mb-8">
                        @php $premiumFeatures = [
                            ['icon' => 'infinity', 'text' => 'Unlimited exercises — every type'],
                            ['icon' => 'infinity', 'text' => 'Unlimited music games'],
                            ['icon' => 'sparkles', 'text' => 'AI mode — personalized feedback'],
                            ['icon' => 'brain', 'text' => 'AI Learning Path generator'],
                            ['icon' => 'message-circle', 'text' => 'AI Music Assistant chat'],
                            ['icon' => 'save', 'text' => 'Unlimited saved templates'],
                            ['icon' => 'trending-up', 'text' => 'Advanced progress analytics'],
                            ['icon' => 'layers', 'text' => 'All 10+ exercise types'],
                        ]; @endphp
                        @foreach ($premiumFeatures as $f)
                        <li class="flex items-start gap-3 text-sm">
                            <div class="w-5 h-5 rounded-full bg-purple-500/20 border border-purple-500/30 flex items-center justify-center shrink-0 mt-0.5">
                                <i data-lucide="check" class="w-3 h-3 text-purple-300"></i>
                            </div>
                            <span class="text-gray-300">{{ $f['text'] }}</span>
                        </li>
                        @endforeach
                    </ul>

                    @auth
                    <a href="{{ url('/dashboard') }}" class="block w-full py-3.5 text-center text-sm font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-lg hover:-translate-y-0.5" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
                        Go to Dashboard
                    </a>
                    @else
                    <a href="{{ route('register') }}" class="block w-full py-3.5 text-center text-sm font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-lg hover:-translate-y-0.5" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
                        Start Now — Get Premium
                    </a>
                    @endauth
                </div>
            </div>
        </div>

        {{-- Links below cards --}}
        <div class="flex flex-wrap items-center justify-center gap-4 mt-8 text-sm reveal" style="transition-delay:0.2s">
            <a href="/pricing/teachers-and-schools" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-200 rounded-xl text-gray-700 font-semibold hover:border-accent-400 hover:text-accent-600 transition-all shadow-sm">
                <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                Teachers &amp; Schools →
            </a>
            <a href="#compare" class="inline-flex items-center gap-2 px-5 py-2.5 text-gray-500 hover:text-gray-800 transition-colors">
                Compare all plans <i data-lucide="arrow-down" class="w-4 h-4"></i>
            </a>
        </div>
    </div>
</section>

{{-- Trust Signals --}}
<section class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10 reveal">
            <h2 class="text-2xl font-extrabold text-gray-900 mb-2">Why Harmoniva?</h2>
            <p class="text-gray-400 text-sm">Everything you need, nothing you don't.</p>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 reveal">
            @php $trusts = [
                ['icon' => 'credit-card', 'title' => 'No credit card', 'desc' => 'Start free with no payment info required.'],
                ['icon' => 'x-circle', 'title' => 'Cancel anytime', 'desc' => 'No lock-in, no cancellation fees.'],
                ['icon' => 'shield-check', 'title' => 'Secure & private', 'desc' => 'Your data is encrypted and never sold.'],
                ['icon' => 'headphones', 'title' => 'Fast support', 'desc' => 'Real humans, quick responses.'],
            ]; @endphp
            @foreach ($trusts as $t)
            <div class="text-center p-5 bg-gray-50 rounded-2xl border border-gray-100">
                <div class="w-10 h-10 mx-auto rounded-xl bg-primary-100 flex items-center justify-center mb-3">
                    <i data-lucide="{{ $t['icon'] }}" class="w-5 h-5 text-primary-600"></i>
                </div>
                <p class="font-bold text-gray-900 text-sm mb-1">{{ $t['title'] }}</p>
                <p class="text-gray-400 text-xs leading-relaxed">{{ $t['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Full Comparison Table --}}
<section id="compare" class="py-20" style="background:#FAF7F2;">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 reveal">
            <span class="text-xs font-bold uppercase tracking-[0.2em] text-primary-600 mb-3 block">Full Breakdown</span>
            <h2 class="text-3xl font-extrabold text-gray-900 mb-3">Compare all plans</h2>
            <p class="text-gray-400 text-sm">See exactly what's included in each tier.</p>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden reveal">
            <div class="grid grid-cols-4 gap-0 border-b border-gray-100 bg-gray-50">
                <div class="px-5 py-4 text-xs font-bold uppercase tracking-wider text-gray-400">Feature</div>
                <div class="px-3 py-4 text-center">
                    <div class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Free</div>
                    <div class="text-lg font-extrabold text-gray-900">$0</div>
                </div>
                <div class="px-3 py-4 text-center" style="background:rgba(147,51,234,0.04);">
                    <div class="text-xs font-bold uppercase tracking-wider text-primary-600 mb-1">Premium</div>
                    <div class="text-lg font-extrabold text-gray-900">$6.90<span class="text-xs text-gray-400 font-normal">/mo</span></div>
                </div>
                <div class="px-3 py-4 text-center" style="background:rgba(234,88,12,0.04);">
                    <div class="text-xs font-bold uppercase tracking-wider text-accent-600 mb-1">Teachers</div>
                    <div class="text-lg font-extrabold text-gray-900">$16.90<span class="text-xs text-gray-400 font-normal">/mo</span></div>
                </div>
            </div>

            @php
            $rows = [
                ['Exercises per day', '3 / type', 'Unlimited', 'Unlimited'],
                ['Music games', '3 / game', 'Unlimited', 'Unlimited'],
                ['Exercise types (10+)', true, true, true],
                ['AI Learning Path', false, true, true],
                ['AI Music Assistant', false, true, true],
                ['AI mode / feedback', false, true, true],
                ['Saved templates', '3 max', 'Unlimited', 'Unlimited'],
                ['Progress analytics', 'Basic', 'Advanced', 'Advanced'],
                ['Student management', false, false, true],
                ['Assign to students/classes', false, false, true],
                ['Class-wide analytics', false, false, true],
                ['Multiple teacher accounts', false, false, true],
                ['Custom branding', false, false, true],
                ['Priority support', false, false, true],
            ];
            @endphp

            @foreach ($rows as $ri => $row)
            <div class="grid grid-cols-4 gap-0 border-b border-gray-50 last:border-0 {{ $ri % 2 === 1 ? 'bg-gray-50/40' : '' }}">
                <div class="px-5 py-3.5 text-sm text-gray-700 font-medium">{{ $row[0] }}</div>
                @foreach ([1, 2, 3] as $ci)
                <div class="px-3 py-3.5 text-center text-sm {{ $ci === 2 ? '' : ($ci === 3 ? '' : '') }}" @if($ci === 2) style="background:rgba(147,51,234,0.02);" @endif @if($ci === 3) style="background:rgba(234,88,12,0.02);" @endif>
                    @if ($row[$ci] === false)
                        <i data-lucide="minus" class="w-4 h-4 text-gray-300 mx-auto"></i>
                    @elseif ($row[$ci] === true)
                        <i data-lucide="check" class="w-4 h-4 text-green-500 mx-auto"></i>
                    @else
                        <span class="{{ $ci === 2 ? 'text-primary-700' : ($ci === 3 ? 'text-accent-600' : 'text-gray-500') }} font-semibold text-xs">{{ $row[$ci] }}</span>
                    @endif
                </div>
                @endforeach
            </div>
            @endforeach
        </div>

        <div class="flex flex-wrap items-center justify-center gap-4 mt-8 text-sm reveal">
            @auth
            <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-2 px-6 py-3 text-white font-bold rounded-xl hover:opacity-90 transition-all shadow-lg" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
                Go to Dashboard
            </a>
            @else
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-6 py-3 text-white font-bold rounded-xl hover:opacity-90 transition-all shadow-lg" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
                Get Started Free
            </a>
            @endauth
            <a href="/pricing/teachers-and-schools" class="inline-flex items-center gap-2 px-6 py-3 bg-white border border-gray-200 rounded-xl text-gray-700 font-semibold hover:border-accent-400 hover:text-accent-600 transition-all shadow-sm">
                Teachers &amp; Schools →
            </a>
        </div>
    </div>
</section>

{{-- FAQ --}}
<section class="py-20 bg-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 reveal">
            <h2 class="text-3xl font-extrabold text-gray-900 mb-3">Frequently asked questions</h2>
            <p class="text-gray-400">Got questions? We've got answers.</p>
        </div>

        @php
        $faqs = [
            ['q' => 'Is Harmoniva really free to start?',
             'a' => 'Yes, completely. Create your account with just an email — no credit card required. You get 3 exercises per day in every category, forever. Upgrade to Premium when you want unlimited access and AI features.'],
            ['q' => 'What\'s the difference between Monthly and Yearly billing?',
             'a' => 'Monthly billing is $6.90/month and can be cancelled at any time. Yearly billing locks in a lower rate of $4.99/month (billed as $59.88/year), saving you about 28%. Both plans include identical features.'],
            ['q' => 'Can I cancel my Premium subscription at any time?',
             'a' => 'Absolutely. You can cancel from your account settings at any moment. You\'ll keep Premium access until the end of your current billing period, and you won\'t be charged again.'],
            ['q' => 'What AI features are included in Premium?',
             'a' => 'Premium includes our AI Learning Path generator (creates a personalized curriculum based on your skill gaps), the AI Music Assistant (an always-available chat for music theory help), and AI-powered exercise feedback that explains your mistakes.'],
            ['q' => 'Do you offer a plan for teachers and music schools?',
             'a' => 'Yes! Our Teachers & Schools plan starts at $16.90/month and includes student roster management, exercise assignment, class-wide analytics, multiple teacher accounts, and more. Visit the Teachers & Schools pricing page for full details.'],
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

{{-- Final CTA --}}
<section class="py-24 relative overflow-hidden" style="background: linear-gradient(135deg, #faf5ff 0%, #FAF7F2 50%, #f3e8ff 100%);">
    <div class="absolute -top-20 -right-20 w-[400px] h-[400px] rounded-full bg-purple-100/50 blur-3xl pointer-events-none"></div>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 text-center relative reveal">
        <div class="w-16 h-16 mx-auto rounded-2xl flex items-center justify-center mb-8 shadow-xl hero-badge" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
            <i data-lucide="music-2" class="w-8 h-8 text-white"></i>
        </div>
        <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-5">
            Your musical ear is waiting.<br>
            <span class="font-serif italic font-normal gradient-text">Start training today.</span>
        </h2>
        <p class="text-gray-500 text-lg mb-10 max-w-xl mx-auto">
            Join thousands of musicians who train smarter with Harmoniva. Free to start, powerful when you're ready to level up.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            @auth
            <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-xl hover:-translate-y-0.5" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
                <i data-lucide="rocket" class="w-5 h-5"></i>
                Go to Dashboard
            </a>
            @else
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-xl hover:-translate-y-0.5" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
                <i data-lucide="rocket" class="w-5 h-5"></i>
                Start Free — No Card Needed
            </a>
            @endauth
            <a href="/pricing/teachers-and-schools" class="inline-flex items-center gap-2 px-6 py-4 text-base font-medium text-gray-500 hover:text-gray-800 transition-colors">
                Teachers &amp; Schools <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>
        <div class="flex flex-wrap items-center justify-center gap-6 text-sm text-gray-400 mt-8">
            <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>No credit card required</span>
            <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>Cancel anytime</span>
            <span class="flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-green-500"></i>10,000+ musicians training</span>
        </div>
    </div>
</section>

@endsection
