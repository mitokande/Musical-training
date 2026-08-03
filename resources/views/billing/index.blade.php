<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.google-analytics')
    @include('partials.posthog')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('app.billing.title') }} - {{ config('app.name', 'Harmoniva') }}</title>

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
    <style>
        .card { background:white; border-radius:16px; border:1px solid #ede9fe; box-shadow:0 2px 8px 0 rgb(109 40 217/0.06),0 1px 2px -1px rgb(0 0 0/0.06); }
    </style>
</head>
<body class="font-sans bg-gray-50 min-h-screen">

    @include('partials.navbar', ['active' => 'billing'])

    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <h1 class="text-2xl font-extrabold text-gray-900 mb-1">{{ __('app.billing.heading') }}</h1>
        <p class="text-gray-500 text-sm mb-6">{{ __('app.billing.subtitle') }}</p>

        @if(session('success'))
            <div class="mb-5 rounded-xl bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3">{{ session('success') }}</div>
        @endif
        @if(session('info'))
            <div class="mb-5 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-sm px-4 py-3">{{ session('info') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-5 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3">{{ session('error') }}</div>
        @endif

        {{-- Current plan --}}
        <div class="card p-6 mb-6">
            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div>
                    <div class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">{{ __('app.billing.current_plan') }}</div>
                    @php $onTrial = $user->onTrial(); @endphp
                    <div class="flex items-center gap-2">
                        <span class="text-2xl font-extrabold text-gray-900">
                            {{ $onTrial ? __('app.trial.plan_label') : ($user->isEffectivelyPremium() ? __('app.billing.plan_premium') : __('app.billing.plan_free')) }}
                        </span>
                        @if($onTrial)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold text-white" style="background:linear-gradient(135deg,#7c3aed,#c026d3);">
                                {{ trans_choice('app.trial.days_left', $user->trialDaysLeft(), ['count' => $user->trialDaysLeft()]) }}
                            </span>
                        @elseif($user->isEffectivelyPremium())
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold text-white" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
                                <i data-lucide="crown" style="width:12px;height:12px;"></i> {{ __('app.billing.active') }}
                            </span>
                        @endif
                    </div>

                    @if($onTrial)
                        {{-- A trial takes no payment, so there is no cycle, amount or renewal to show. --}}
                        <p class="text-sm text-gray-500 mt-2">
                            {{ __('app.trial.cta_no_card') }} · {{ __('app.billing.ends_on') }} <strong>{{ $user->trial_ends_at->format('M j, Y') }}</strong>
                        </p>
                    @elseif($subscription)
                        @php $cycleLabel = \Illuminate\Support\Facades\Lang::has('app.billing.cycle_'.$subscription->billing_cycle) ? __('app.billing.cycle_'.$subscription->billing_cycle) : ucfirst($subscription->billing_cycle); @endphp
                        <p class="text-sm text-gray-500 mt-2">
                            {{ $cycleLabel }} · {{ $subscription->currency }} {{ number_format((float)$subscription->amount, 2) }}
                            @if($subscription->ends_at)
                                · {{ $subscription->auto_renew ? __('app.billing.renews_on') : __('app.billing.ends_on2') }} <strong>{{ $subscription->ends_at->format('M j, Y') }}</strong>
                            @endif
                        </p>
                        @unless($subscription->auto_renew)
                            <p class="text-xs text-amber-600 mt-1">{{ __('app.billing.autorenew_off') }}</p>
                        @endunless
                    @elseif($isEffectivelyPremium)
                        <p class="text-sm text-gray-500 mt-2">{{ __('app.billing.premium_via_benefit') }}</p>
                    @else
                        <p class="text-sm text-gray-500 mt-2">{{ __('app.billing.upgrade_hint') }}</p>
                    @endif
                </div>

                <div class="flex flex-col gap-2">
                    @if($onTrial)
                        {{-- Nothing to cancel on a trial: no card was taken and it
                             lapses on its own. Only offer early conversion. --}}
                        @if(config('payments.checkout_enabled'))
                            <a href="{{ route('checkout.show') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-bold text-white rounded-xl hover:opacity-90 transition-all shadow" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
                                <i data-lucide="crown" style="width:15px;height:15px;"></i> {{ __('app.trial.cta_subscribe') }}
                            </a>
                        @else
                            <span class="text-xs font-semibold text-gray-400">{{ __('app.trial.payments_soon') }}</span>
                        @endif
                    @elseif(! $user->isEffectivelyPremium())
                        <a href="{{ route('checkout.show') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-bold text-white rounded-xl hover:opacity-90 transition-all shadow" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
                            <i data-lucide="crown" style="width:15px;height:15px;"></i> {{ __('app.billing.upgrade_btn') }}
                        </a>
                    @elseif($subscription && $subscription->auto_renew)
                        <form method="POST" action="{{ route('billing.cancel', $subscription) }}"
                              onsubmit="return confirm(@js(__('app.billing.cancel_confirm')));">
                            @csrf
                            <button type="submit" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl hover:border-red-300 hover:text-red-600 transition-all">
                                {{ __('app.billing.cancel_btn') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- Invoices --}}
        <div class="card p-6">
            <h2 class="text-sm font-bold text-gray-900 mb-4">{{ __('app.billing.invoice_history') }}</h2>
            @if($invoices->isEmpty())
                <p class="text-sm text-gray-400 py-6 text-center">{{ __('app.billing.no_invoices') }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wider text-gray-400 border-b border-gray-100">
                                <th class="py-2.5 pr-4 font-semibold">{{ __('app.billing.col_invoice') }}</th>
                                <th class="py-2.5 pr-4 font-semibold">{{ __('app.billing.col_date') }}</th>
                                <th class="py-2.5 pr-4 font-semibold">{{ __('app.billing.col_amount') }}</th>
                                <th class="py-2.5 font-semibold">{{ __('app.billing.col_status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices as $inv)
                                @php
                                    $badge = [
                                        'paid' => 'bg-green-100 text-green-700',
                                        'pending' => 'bg-amber-100 text-amber-700',
                                        'refunded' => 'bg-gray-100 text-gray-600',
                                        'cancelled' => 'bg-red-100 text-red-700',
                                    ][$inv->status] ?? 'bg-gray-100 text-gray-600';
                                    $statusLabel = \Illuminate\Support\Facades\Lang::has('app.billing.status_'.$inv->status) ? __('app.billing.status_'.$inv->status) : ucfirst($inv->status);
                                @endphp
                                <tr class="border-b border-gray-50">
                                    <td class="py-3 pr-4 font-medium text-gray-800">{{ $inv->invoice_number }}</td>
                                    <td class="py-3 pr-4 text-gray-500">{{ $inv->created_at->format('M j, Y') }}</td>
                                    <td class="py-3 pr-4 text-gray-800">{{ $inv->currency }} {{ number_format((float)$inv->total_amount, 2) }}</td>
                                    <td class="py-3"><span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $badge }}">{{ $statusLabel }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </main>

    @includeIf('partials.footer')

    <script>lucide.createIcons();</script>
</body>
</html>
