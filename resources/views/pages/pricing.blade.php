@extends('layouts.standalone')

@section('title', 'Pricing — Choose Your Plan')
@section('description', 'Simple, transparent pricing for musicians, students, teachers, and music schools. Start free — no credit card required.')

@section('content')

@php
    // Single source of truth for Premium pricing so every figure on this page
    // (hero badge, cards, comparison, FAQ) stays consistent and can never drift.
    $monthlyPrice    = 6.90;
    $yearlyTotal     = 40;                                   // annual total, billed once
    $yearlyMonthly   = round($yearlyTotal / 12, 2);         // 3.33 effective /month
    $annualIfMonthly = $monthlyPrice * 12;                  // 82.80
    $saveAmount      = round($annualIfMonthly - $yearlyTotal);              // ~43
    $savePercent     = (int) round((1 - $yearlyTotal / $annualIfMonthly) * 100); // 52
    $monthsFree      = (int) round(12 - $yearlyTotal / $monthlyPrice);      // ~6
@endphp

{{-- Alpine scope for the Monthly/Yearly toggle. Defined here (not via body-attrs)
     because @yield escapes the attribute, which breaks x-data on the body tag. --}}
<div x-data="{ billingYearly: false }">

{{-- Hero --}}
<section class="py-20 sm:py-28 relative overflow-hidden" style="background: linear-gradient(135deg, #faf5ff 0%, #FAF7F2 60%, #fef3c7 100%); padding-bottom:3rem;">
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
            No credit card required to get started. Train your ear with 5 free practice sessions every day — forever free. Upgrade for unlimited access, AI features, and more.
        </p>

        {{-- Billing Toggle --}}
        <div class="inline-flex items-center gap-1 mb-6 p-1.5 bg-white rounded-2xl shadow-sm border border-gray-100">
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
                    Save {{ $savePercent }}%
                </span>
            </button>
        </div>
        <p class="text-sm text-gray-400 -mt-2 mb-6" x-show="billingYearly" x-cloak style="margin-top:-0.75rem;">
            <i data-lucide="gift" class="w-4 h-4 inline text-accent-500 -mt-0.5"></i>
            Yearly is our best value — get <strong class="text-gray-600">~{{ $monthsFree }} months free</strong> vs. paying monthly.
        </p>
    </div>
</section>

{{-- Plan Cards --}}
<section class="pb-20 -mt-6 relative z-10">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">

            {{-- Free Card --}}
            <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-8 reveal flex flex-col">
                <div class="mb-6">
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-gray-100 text-gray-500 mb-4">Free</span>
                    <div class="flex items-end gap-1 mb-1">
                        <span class="text-5xl font-extrabold text-gray-900">$0</span>
                        <span class="text-gray-400 text-base mb-2">/ forever</span>
                    </div>
                    <p class="text-gray-400 text-sm">No credit card required</p>
                </div>

                <ul class="space-y-4 mb-8">
                    @php $freeFeatures = [
                        ['icon' => 'music-2', 'text' => '5 Learning Path sessions per day (5 questions each)'],
                        ['icon' => 'settings-2', 'text' => '5 Exercise Studio sessions per day (5 questions each)'],
                        ['icon' => 'gamepad-2', 'text' => '2 plays per game per day (6 total)'],
                        ['icon' => 'piano', 'text' => 'Unlimited Piano Studio'],
                        ['icon' => 'message-circle', 'text' => 'Ask AI — 1 question per day'],
                        ['icon' => 'layers', 'text' => 'All 10+ exercise types'],
                        ['icon' => 'save', 'text' => 'Up to 3 saved templates'],
                        ['icon' => 'bar-chart-2', 'text' => 'Basic progress tracking'],
                        ['icon' => 'x', 'text' => 'AI Exercises & AI Coach (Premium only)', 'disabled' => true],
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
                <a href="{{ url('/dashboard') }}" class="mt-auto block w-full py-3.5 text-center text-sm font-bold text-white bg-gray-900 hover:bg-gray-800 rounded-xl transition-colors shadow-lg hover:-translate-y-0.5">
                    Go to Dashboard
                </a>
                @else
                <a href="{{ route('register') }}" class="mt-auto block w-full py-3.5 text-center text-sm font-bold text-white bg-gray-900 hover:bg-gray-800 rounded-xl transition-colors shadow-lg hover:-translate-y-0.5">
                    Start Free — No Card Needed
                </a>
                @endauth
            </div>

            {{-- Premium Card --}}
            <div class="bg-gray-900 rounded-3xl shadow-2xl reveal relative overflow-hidden flex flex-col" style="transition-delay:0.1s">

                {{-- Price banner — solid purple, no pattern; price switches with the billing toggle --}}
                <div style="background:#7c3aed; padding:28px 32px 30px;">
                    <div class="flex items-center justify-between" style="margin-bottom:20px;">
                        <span class="inline-flex items-center font-bold uppercase text-white" style="gap:6px; padding:5px 12px; border-radius:9999px; font-size:11px; letter-spacing:0.05em; background:rgba(255,255,255,0.18); border:1px solid rgba(255,255,255,0.32);">
                            <i data-lucide="crown" style="width:14px; height:14px;"></i> Premium
                        </span>
                        <span class="inline-block font-bold" style="padding:5px 12px; border-radius:9999px; font-size:11px; color:#fff2e2; background:rgba(249,115,22,0.38); border:1px solid rgba(251,146,60,0.6);">Most Popular</span>
                    </div>

                    {{-- Monthly / Yearly switch — full-width segmented control, changes the price below --}}
                    <div style="display:flex; width:100%; gap:5px; padding:5px; border-radius:13px; background:rgba(0,0,0,0.18); box-shadow:inset 0 1px 2px rgba(0,0,0,0.15); margin-bottom:18px;">
                        <button type="button" @click="billingYearly = false"
                                style="flex:1; display:flex; align-items:center; justify-content:center; padding:11px 12px; border-radius:9px; font-size:13.5px; font-weight:700; letter-spacing:0.01em; border:0; cursor:pointer; transition:all .2s;"
                                :style="!billingYearly ? 'background:#ffffff; color:#6d28d9; box-shadow:0 1px 2px rgba(0,0,0,0.2),0 3px 10px rgba(0,0,0,0.14);' : 'background:transparent; color:rgba(255,255,255,0.85);'">
                            Monthly
                        </button>
                        <button type="button" @click="billingYearly = true"
                                style="flex:1; display:flex; align-items:center; justify-content:center; gap:8px; padding:11px 12px; border-radius:9px; font-size:13.5px; font-weight:700; letter-spacing:0.01em; border:0; cursor:pointer; transition:all .2s;"
                                :style="billingYearly ? 'background:#ffffff; color:#6d28d9; box-shadow:0 1px 2px rgba(0,0,0,0.2),0 3px 10px rgba(0,0,0,0.14);' : 'background:transparent; color:rgba(255,255,255,0.85);'">
                            Yearly
                            <span style="display:inline-flex; align-items:center; font-size:10px; font-weight:800; letter-spacing:0.04em; padding:3px 8px; border-radius:9999px; background:#f97316; color:#fff; line-height:1;">SAVE {{ $savePercent }}%</span>
                        </button>
                    </div>

                    {{-- Monthly price (visible when toggle is on Monthly) --}}
                    <div x-show="!billingYearly">
                        <div class="flex items-end" style="gap:6px;">
                            <span class="font-extrabold text-white" style="font-size:56px; line-height:1; letter-spacing:-0.02em;">${{ number_format($monthlyPrice, 2) }}</span>
                            <span class="font-medium" style="color:rgba(255,255,255,0.82); font-size:16px; margin-bottom:6px;">/month</span>
                        </div>
                        <p style="color:rgba(255,255,255,0.82); font-size:13px; margin-top:8px;">Billed monthly · cancel anytime</p>
                    </div>
                    {{-- Yearly price (visible when toggle is on Yearly) --}}
                    <div x-show="billingYearly" x-cloak>
                        <div class="flex items-end" style="gap:6px;">
                            <span class="font-extrabold text-white" style="font-size:56px; line-height:1; letter-spacing:-0.02em;">${{ number_format($yearlyMonthly, 2) }}</span>
                            <span class="font-medium" style="color:rgba(255,255,255,0.82); font-size:16px; margin-bottom:6px;">/month</span>
                        </div>
                        <p style="color:rgba(255,255,255,0.92); font-size:13px; margin-top:8px;">
                            ${{ $yearlyTotal }} billed annually ·
                            <span class="font-bold" style="color:#ffd9a8;">Save {{ $savePercent }}% (${{ $saveAmount }})</span>
                        </p>
                    </div>
                </div>

                {{-- Features + CTA --}}
                <div class="relative px-8 pt-6 pb-8 flex flex-col flex-1">
                    <ul class="space-y-4 mb-8">
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
                            <div class="w-5 h-5 rounded-full flex items-center justify-center shrink-0" style="margin-top:2px; background:rgba(168,85,247,0.2); border:1px solid rgba(168,85,247,0.4);">
                                <i data-lucide="check" class="w-3 h-3 text-purple-300"></i>
                            </div>
                            <span class="text-gray-300">{{ $f['text'] }}</span>
                        </li>
                        @endforeach
                    </ul>

                    @auth
                    <a href="{{ route('checkout.show') }}" class="mt-auto block w-full py-3.5 text-center text-sm font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-lg hover:-translate-y-0.5" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
                        Get Premium Now
                    </a>
                    @else
                    <a href="{{ route('register') }}" class="mt-auto block w-full py-3.5 text-center text-sm font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-lg hover:-translate-y-0.5" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
                        Start Now — Get Premium
                    </a>
                    @endauth
                    <p style="font-size:11px;color:#9ca3af;text-align:center;margin-top:10px;display:flex;align-items:center;justify-content:center;gap:6px;flex-wrap:wrap;">
                        <span style="display:inline-flex;align-items:center;gap:4px;"><i data-lucide="shield-check" style="width:12px;height:12px;"></i> 14-day money-back guarantee</span>
                        <span>·</span>
                        <span>Cancel anytime</span>
                    </p>
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

{{-- Teacher & School free-access teaser --}}
<section class="pb-16">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="rounded-3xl p-8 sm:p-10 relative overflow-hidden reveal" style="background: linear-gradient(135deg,#0f172a 0%,#1e1b4b 100%);">
            <div class="absolute -top-16 -right-16 w-56 h-56 rounded-full blur-3xl" style="background:rgba(34,197,94,0.18);"></div>
            <div class="absolute -bottom-12 -left-12 w-40 h-40 rounded-full blur-2xl" style="background:rgba(249,115,22,0.15);"></div>
            <div class="relative flex flex-col md:flex-row items-center gap-8">
                <div class="flex-1 text-center md:text-left">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-green-500/20 text-green-300 text-xs font-bold mb-4">
                        <i data-lucide="gift" class="w-3.5 h-3.5"></i>
                        For Teachers &amp; Music Schools
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-white mb-3">
                        Bring Premium students, <span class="font-serif italic font-normal" style="color:#4ade80;">use Harmoniva free</span>
                    </h2>
                    <p class="text-gray-300 text-sm max-w-xl leading-relaxed">
                        Teachers with <strong class="text-white">10+ Premium students</strong> and schools with <strong class="text-white">20+ Premium students</strong> unlock the entire platform — every feature, completely free. The more your students grow, the less you pay.
                    </p>
                </div>
                <div class="shrink-0 flex flex-col sm:flex-row md:flex-col gap-3">
                    <div class="flex items-center gap-3 px-5 py-3 rounded-2xl bg-white/5 border border-white/10">
                        <span class="text-3xl font-extrabold" style="color:#4ade80;">10+</span>
                        <span class="text-xs text-gray-300 leading-tight">Premium students<br>= free for teachers</span>
                    </div>
                    <div class="flex items-center gap-3 px-5 py-3 rounded-2xl bg-white/5 border border-white/10">
                        <span class="text-3xl font-extrabold" style="color:#4ade80;">20+</span>
                        <span class="text-xs text-gray-300 leading-tight">Premium students<br>= free for schools</span>
                    </div>
                </div>
            </div>
            <div class="relative mt-8 text-center md:text-left">
                <a href="{{ route('pricing.teachers') }}" class="inline-flex items-center gap-2 px-6 py-3 text-sm font-bold text-gray-900 bg-white rounded-xl hover:bg-gray-100 transition-all shadow-lg">
                    <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                    See Teacher &amp; School plans
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
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
            <div class="overflow-x-auto"><div class="min-w-[560px]">
            <div class="grid grid-cols-4 gap-0 border-b border-gray-100 bg-gray-50">
                <div class="px-5 py-4 text-xs font-bold uppercase tracking-wider text-gray-400">Feature</div>
                <div class="px-3 py-4 text-center">
                    <div class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Free</div>
                    <div class="text-lg font-extrabold text-gray-900">$0</div>
                </div>
                <div class="px-3 py-4 text-center" style="background:rgba(147,51,234,0.04);">
                    <div class="text-xs font-bold uppercase tracking-wider text-primary-600 mb-1">Premium</div>
                    <div class="text-lg font-extrabold text-gray-900" x-show="!billingYearly">${{ number_format($monthlyPrice, 2) }}<span class="text-xs text-gray-400 font-normal">/mo</span></div>
                    <div class="text-lg font-extrabold text-gray-900" x-show="billingYearly" x-cloak>${{ number_format($yearlyMonthly, 2) }}<span class="text-xs text-gray-400 font-normal">/mo</span></div>
                </div>
                <div class="px-3 py-4 text-center" style="background:rgba(234,88,12,0.04);">
                    <div class="text-xs font-bold uppercase tracking-wider text-accent-600 mb-1">Teachers</div>
                    <div class="text-lg font-extrabold text-gray-900">$16.90<span class="text-xs text-gray-400 font-normal">/mo</span></div>
                </div>
            </div>

            @php
            $rows = [
                ['Learning Path sessions', '5 / day (5 questions)', 'Unlimited', 'Unlimited'],
                ['Exercise Studio sessions', '5 / day (5 questions)', 'Unlimited', 'Unlimited'],
                ['Music games', '2 / game / day', 'Unlimited', 'Unlimited'],
                ['Piano Studio', 'Unlimited', 'Unlimited', 'Unlimited'],
                ['Exercise types (10+)', true, true, true],
                ['Ask AI (Music Assistant)', '1 question / day', '10 / day', '10 / day'],
                ['AI Exercises', false, true, true],
                ['AI Coach', false, true, true],
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
            </div></div>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-4 mt-8 text-sm reveal">
            @auth
            <a href="{{ route('checkout.show') }}" class="inline-flex items-center gap-2 px-6 py-3 text-white font-bold rounded-xl hover:opacity-90 transition-all shadow-lg" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
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
             'a' => 'Yes, completely. Create your account with just an email — no credit card required. You get 5 practice sessions per day in the Learning Path and the Exercise Studio, unlimited Piano Studio, and daily game plays — forever. Upgrade to Premium when you want unlimited access and AI features.'],
            ['q' => 'What\'s the difference between Monthly and Yearly billing?',
             'a' => 'Monthly billing is $'.number_format($monthlyPrice, 2).'/month and can be cancelled at any time. Yearly billing locks in a lower rate of $'.number_format($yearlyMonthly, 2).'/month (billed as $'.$yearlyTotal.'/year), saving you about '.$savePercent.'%. Both plans include identical features.'],
            ['q' => 'Can I cancel my Premium subscription at any time?',
             'a' => 'Absolutely. You can cancel from your account settings at any moment. You\'ll keep Premium access until the end of your current billing period, and you won\'t be charged again.'],
            ['q' => 'What AI features are included in Premium?',
             'a' => 'Premium includes our AI Learning Path generator (creates a personalized curriculum based on your skill gaps), the AI Music Assistant (an always-available chat for music theory help), and AI-powered exercise feedback that explains your mistakes.'],
            ['q' => 'Do you offer a plan for teachers and music schools?',
             'a' => 'Yes! Teacher plans start at $16.90/month and School plans at $29.90/month, both with bigger savings on yearly billing — and they include student roster management, exercise assignment, class-wide analytics, and more. Even better: teachers with 10+ Premium students, and schools with 20+ Premium students, use Harmoniva completely free. Visit the Teachers & Schools pricing page for full details.'],
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
            <a href="{{ route('checkout.show') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-xl hover:-translate-y-0.5" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
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

</div>{{-- /x-data billing toggle scope --}}

@endsection
