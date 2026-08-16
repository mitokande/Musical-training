<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.google-analytics')
    @include('partials.posthog')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('app.checkout.meta_title', ['app' => config('app.name', 'Harmoniva')]) }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@0.460.0"></script>
    <script defer src="https://unpkg.com/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: { extend: {
                fontFamily: { sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'] },
                colors: {
                    primary: { 50:'#faf5ff',100:'#f3e8ff',200:'#e9d5ff',300:'#d8b4fe',400:'#c084fc',500:'#a855f7',600:'#9333ea',700:'#7c3aed',800:'#6b21a8',900:'#581c87' },
                }
            } }
        }
    </script>
</head>
<body class="font-sans text-gray-700 antialiased">

@include('partials.navbar', ['active' => 'billing'])

@php
    $sym = $currencySymbol;
    $roleLabels = ['user' => __('app.checkout.role_student'), 'teacher' => __('app.checkout.role_teacher'), 'school' => __('app.checkout.role_school')];
    $roleLabel = $roleLabels[$plan->role] ?? ucfirst($plan->role);
    $yearlySave = round($monthly['total'] * 12 - $yearly['total'], 2);
    $yearlyPct = $monthly['total'] > 0 ? (int) round((1 - $yearly['total'] / ($monthly['total'] * 12)) * 100) : 0;
    $highlights = [
        __('app.checkout.hl_unlimited'),
        __('app.checkout.hl_ai'),
        __('app.checkout.hl_progress'),
        __('app.checkout.hl_games'),
    ];

    // While the payment provider cannot charge a real card, this page offers the
    // free trial in place of the payment form. It is the only place in the app
    // that advertises the trial.
    $trialUser = auth()->user();
    $trialState = match (true) {
        $trialUser->onTrial() => 'active',
        $trialUser->canStartTrial() => 'eligible',
        default => 'unavailable',
    };
@endphp

<div x-data="{
        yearly: {{ $cycle === 'yearly' ? 'true' : 'false' }},
        get price() { return this.yearly ? {{ $yearly['total'] }} : {{ $monthly['total'] }} },
        get sub() { return this.yearly ? {{ $yearly['amount'] }} : {{ $monthly['amount'] }} },
        get tax() { return this.yearly ? {{ $yearly['tax'] }} : {{ $monthly['tax'] }} },
     }"
     style="background: linear-gradient(135deg, #faf5ff 0%, #FAF7F2 60%, #fef3c7 100%);">

<section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-20">

    <div class="mb-8">
        <a href="{{ locale_url('/pricing') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 transition-colors">
            <i data-lucide="arrow-left" style="width:16px;height:16px;"></i> {{ __('app.checkout.back_to_pricing') }}
        </a>
        @if($checkoutEnabled)
            <h1 class="mt-4 text-3xl sm:text-4xl font-extrabold text-gray-900">{{ __('app.checkout.h_upgrade') }}</h1>
            <p class="mt-2 text-gray-500">{!! __('app.checkout.p_upgrade', ['role' => $roleLabel]) !!}</p>
        @elseif($trialState === 'eligible')
            <h1 class="mt-4 text-3xl sm:text-4xl font-extrabold text-gray-900">{{ __('app.checkout.h_trial_eligible', ['days' => $trialDays]) }}</h1>
            <p class="mt-2 text-gray-500">{!! __('app.checkout.p_trial_eligible', ['role' => $roleLabel]) !!}</p>
        @elseif($trialState === 'active')
            <h1 class="mt-4 text-3xl sm:text-4xl font-extrabold text-gray-900">{{ __('app.checkout.h_trial_active') }}</h1>
            <p class="mt-2 text-gray-500">{!! __('app.checkout.p_trial_active', ['role' => $roleLabel]) !!}</p>
        @else
            <h1 class="mt-4 text-3xl sm:text-4xl font-extrabold text-gray-900">{{ __('app.checkout.h_soon') }}</h1>
            <p class="mt-2 text-gray-500">{{ __('app.checkout.p_soon') }}</p>
        @endif
    </div>

    <div class="grid lg:grid-cols-5 gap-6">

        {{-- Order summary --}}
        <div class="lg:col-span-3 space-y-6">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
                <div class="flex items-center gap-2 mb-5">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold text-white" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
                        <i data-lucide="crown" style="width:14px;height:14px;"></i> Premium
                    </span>
                    <span class="text-sm text-gray-400">{{ __('app.checkout.role_plan', ['role' => $roleLabel]) }}</span>
                </div>

                {{-- Billing cycle toggle --}}
                <div class="inline-flex p-1 rounded-xl bg-gray-100 mb-6">
                    <button type="button" @click="yearly = false"
                            :class="!yearly ? 'bg-white shadow text-gray-900' : 'text-gray-500'"
                            class="px-4 py-2 rounded-lg text-sm font-semibold transition-all">{{ __('app.checkout.cycle_monthly') }}</button>
                    <button type="button" @click="yearly = true"
                            :class="yearly ? 'bg-white shadow text-gray-900' : 'text-gray-500'"
                            class="px-4 py-2 rounded-lg text-sm font-semibold transition-all inline-flex items-center gap-1.5">
                        {{ __('app.checkout.cycle_yearly') }}
                        @if($yearlyPct > 0)
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-700">{{ __('app.checkout.save_pct', ['pct' => $yearlyPct]) }}</span>
                        @endif
                    </button>
                </div>

                {{-- Price line --}}
                <div class="flex items-end gap-2 mb-1">
                    <span class="text-4xl font-extrabold text-gray-900">{{ $sym }}<span x-text="price.toFixed(2)"></span></span>
                    <span class="text-gray-400 mb-1.5" x-text="yearly ? '{{ __('app.checkout.per_year') }}' : '{{ __('app.checkout.per_month') }}'"></span>
                </div>
                <p class="text-sm text-gray-400 mb-6" x-show="yearly" x-cloak>
                    {{ __('app.checkout.yearly_breakdown', ['permonth' => $sym.number_format($yearly['total'] / 12, 2), 'save' => $sym.number_format($yearlySave, 2)]) }}
                </p>

                <div class="border-t border-gray-100 pt-5">
                    <h3 class="text-sm font-bold text-gray-900 mb-3">{{ __('app.checkout.included_title') }}</h3>
                    <ul class="space-y-2.5">
                        @foreach($highlights as $h)
                            <li class="flex items-start gap-2.5 text-sm text-gray-600">
                                <i data-lucide="check" style="width:16px;height:16px;color:#16a34a;margin-top:2px;flex-shrink:0;"></i>
                                <span>{{ $h }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            {{-- Trust row --}}
            <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-xs text-gray-500 px-2">
                @if($checkoutEnabled)
                    <span class="inline-flex items-center gap-1.5"><i data-lucide="shield-check" style="width:15px;height:15px;color:#7c3aed;"></i> {{ __('app.checkout.trust_secure') }}</span>
                    <span class="inline-flex items-center gap-1.5"><i data-lucide="rotate-ccw" style="width:15px;height:15px;color:#7c3aed;"></i> {{ __('app.checkout.trust_guarantee', ['days' => $refundDays]) }}</span>
                    <span class="inline-flex items-center gap-1.5"><i data-lucide="x-circle" style="width:15px;height:15px;color:#7c3aed;"></i> {{ __('app.checkout.trust_cancel') }}</span>
                @else
                    <span class="inline-flex items-center gap-1.5"><i data-lucide="credit-card" style="width:15px;height:15px;color:#7c3aed;"></i> {{ __('app.checkout.trust_no_card') }}</span>
                    <span class="inline-flex items-center gap-1.5"><i data-lucide="shield-check" style="width:15px;height:15px;color:#7c3aed;"></i> {{ __('app.checkout.trust_nothing_charged') }}</span>
                    <span class="inline-flex items-center gap-1.5"><i data-lucide="x-circle" style="width:15px;height:15px;color:#7c3aed;"></i> {{ __('app.checkout.trust_nothing_cancel') }}</span>
                @endif
            </div>
        </div>

        {{-- Payment / confirm box --}}
        <div class="lg:col-span-2">
            @if(! $checkoutEnabled)
            {{-- Payments are closed: the free trial stands in for the order form. --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-lg p-6 sm:p-7 lg:sticky lg:top-24">
                @if($trialState === 'eligible')
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold text-white mb-4" style="background:linear-gradient(135deg,#7c3aed,#c026d3);">
                        <i data-lucide="sparkles" style="width:14px;height:14px;"></i> {{ __('app.checkout.trial_badge') }}
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">{{ __('app.checkout.trial_box_title', ['days' => $trialDays]) }}</h3>
                    <p class="text-sm text-gray-500 mb-5">{{ __('app.checkout.trial_box_desc', ['days' => $trialDays]) }}</p>

                    <div class="space-y-3 text-sm mb-6">
                        <div class="flex justify-between text-gray-600">
                            <span>{{ __('app.checkout.trial_line', ['role' => $roleLabel, 'days' => $trialDays]) }}</span>
                            <span>{{ $sym }}0.00</span>
                        </div>
                        <div class="border-t border-gray-100 pt-3 flex justify-between items-baseline">
                            <span class="font-bold text-gray-900">{{ __('app.checkout.total_today') }}</span>
                            <span class="font-extrabold text-lg text-gray-900">{{ $sym }}0.00</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('trial.store') }}">
                        @csrf
                        <button type="submit"
                                class="w-full py-3.5 text-center text-sm font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-lg"
                                style="background:linear-gradient(135deg,#7c3aed,#c026d3);">
                            <span class="inline-flex items-center justify-center gap-2">
                                <i data-lucide="sparkles" style="width:16px;height:16px;"></i>
                                {{ __('app.checkout.trial_cta', ['days' => $trialDays]) }}
                            </span>
                        </button>
                    </form>

                    <p class="mt-3 text-center text-[11px] text-gray-400 leading-relaxed">
                        {!! __('app.checkout.trial_fineprint') !!}
                    </p>

                @elseif($trialState === 'active')
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold text-white mb-4" style="background:linear-gradient(135deg,#7c3aed,#c026d3);">
                        <i data-lucide="crown" style="width:14px;height:14px;"></i> {{ trans_choice('app.trial.days_left', auth()->user()->trialDaysLeft(), ['count' => auth()->user()->trialDaysLeft()]) }}
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">{{ __('app.checkout.active_title') }}</h3>
                    <p class="text-sm text-gray-500 mb-5">
                        {!! __('app.checkout.active_desc', ['date' => auth()->user()->trial_ends_at->translatedFormat('j M Y')]) !!}
                    </p>
                    <a href="{{ route('billing.index') }}" class="block w-full py-3.5 text-center text-sm font-bold text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition-all">
                        {{ __('app.checkout.active_cta') }}
                    </a>
                    <p class="mt-3 text-center text-[11px] text-gray-400 leading-relaxed">
                        {!! __('app.checkout.active_fineprint') !!}
                    </p>

                @else
                    <h3 class="text-lg font-bold text-gray-900 mb-1">{{ __('app.checkout.closed_title') }}</h3>
                    <p class="text-sm text-gray-500 mb-5">
                        {{ __('app.checkout.closed_desc') }}
                    </p>
                    <a href="{{ route('dashboard') }}" class="block w-full py-3.5 text-center text-sm font-bold text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition-all">
                        {{ __('app.checkout.closed_cta') }}
                    </a>
                @endif
            </div>
            @else
            <div class="bg-white rounded-2xl border border-gray-100 shadow-lg p-6 sm:p-7 lg:sticky lg:top-24">
                <h3 class="text-lg font-bold text-gray-900 mb-5">{{ __('app.checkout.summary_title') }}</h3>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>Premium ({{ $roleLabel }}) <span x-text="yearly ? '{{ __('app.checkout.summary_yearly') }}' : '{{ __('app.checkout.summary_monthly') }}'"></span></span>
                        <span>{{ $sym }}<span x-text="sub.toFixed(2)"></span></span>
                    </div>
                    @if($taxRate > 0)
                        <div class="flex justify-between text-gray-600">
                            <span>{{ __('app.checkout.tax_line', ['pct' => (int) round($taxRate * 100)]) }}</span>
                            <span>{{ $sym }}<span x-text="tax.toFixed(2)"></span></span>
                        </div>
                    @endif
                    <div class="border-t border-gray-100 pt-3 flex justify-between items-baseline">
                        <span class="font-bold text-gray-900">{{ __('app.checkout.total_today') }}</span>
                        <span class="font-extrabold text-lg text-gray-900">{{ $sym }}<span x-text="price.toFixed(2)"></span></span>
                    </div>
                </div>

                @if($errors->any())
                    <div class="mt-4 text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('checkout.store') }}" class="mt-6">
                    @csrf
                    <input type="hidden" name="cycle" :value="yearly ? 'yearly' : 'monthly'">

                    <label class="flex items-start gap-2.5 mb-5 cursor-pointer">
                        <input type="checkbox" name="terms" value="1" required
                               class="mt-0.5 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <span class="text-xs text-gray-500 leading-relaxed">
                            {!! __('app.checkout.agree', [
                                'subscription' => '<a href="'.locale_url('/subscription-terms').'" target="_blank" class="text-purple-600 underline">'.__('app.checkout.agree_subscription').'</a>',
                                'terms' => '<a href="'.locale_url('/terms-of-service').'" target="_blank" class="text-purple-600 underline">'.__('app.checkout.agree_terms').'</a>',
                                'refund' => '<a href="'.locale_url('/refund-policy').'" target="_blank" class="text-purple-600 underline">'.__('app.checkout.agree_refund').'</a>',
                            ]) !!}
                        </span>
                    </label>

                    <button type="submit"
                            class="w-full py-3.5 text-center text-sm font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-lg"
                            style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
                        <span class="inline-flex items-center justify-center gap-2">
                            <i data-lucide="lock" style="width:16px;height:16px;"></i>
                            {{ __('app.checkout.pay_cta') }}
                        </span>
                    </button>

                    <p class="mt-3 text-center text-[11px] text-gray-400 leading-relaxed">
                        {!! __('app.checkout.pay_fineprint') !!}
                    </p>

                    <div class="mt-4 flex items-center justify-center gap-2 text-[11px] text-gray-400">
                        <i data-lucide="shield-check" style="width:13px;height:13px;color:#7c3aed;"></i>
                        <span>{{ __('app.checkout.pay_ssl') }}</span>
                    </div>
                </form>
            </div>
            @endif
        </div>
    </div>
</section>
</div>

@includeIf('partials.footer')
<script>lucide.createIcons();</script>
</body>
</html>
