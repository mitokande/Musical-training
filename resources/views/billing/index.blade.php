<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.google-analytics')
    @include('partials.posthog')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Billing - {{ config('app.name', 'Harmoniva') }}</title>

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

        <h1 class="text-2xl font-extrabold text-gray-900 mb-1">Billing & Subscription</h1>
        <p class="text-gray-500 text-sm mb-6">Manage your Harmoniva plan and view your invoices.</p>

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
                    <div class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Current plan</div>
                    <div class="flex items-center gap-2">
                        <span class="text-2xl font-extrabold text-gray-900">
                            {{ $user->isEffectivelyPremium() ? 'Premium' : 'Free' }}
                        </span>
                        @if($user->isEffectivelyPremium())
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold text-white" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
                                <i data-lucide="crown" style="width:12px;height:12px;"></i> Active
                            </span>
                        @endif
                    </div>

                    @if($subscription)
                        <p class="text-sm text-gray-500 mt-2">
                            {{ ucfirst($subscription->billing_cycle) }} · {{ $subscription->currency }} {{ number_format((float)$subscription->amount, 2) }}
                            @if($subscription->ends_at)
                                · {{ $subscription->auto_renew ? 'Renews' : 'Ends' }} on <strong>{{ $subscription->ends_at->format('M j, Y') }}</strong>
                            @endif
                        </p>
                        @unless($subscription->auto_renew)
                            <p class="text-xs text-amber-600 mt-1">Auto-renew is off — you keep Premium until the date above.</p>
                        @endunless
                    @elseif($isEffectivelyPremium)
                        <p class="text-sm text-gray-500 mt-2">You have Premium access through a teacher/school benefit.</p>
                    @else
                        <p class="text-sm text-gray-500 mt-2">Upgrade to unlock unlimited exercises, AI features and more.</p>
                    @endif
                </div>

                <div class="flex flex-col gap-2">
                    @if(!$user->isEffectivelyPremium())
                        <a href="{{ route('checkout.show') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-bold text-white rounded-xl hover:opacity-90 transition-all shadow" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
                            <i data-lucide="crown" style="width:15px;height:15px;"></i> Upgrade to Premium
                        </a>
                    @elseif($subscription && $subscription->auto_renew)
                        <form method="POST" action="{{ route('billing.cancel', $subscription) }}"
                              onsubmit="return confirm('Cancel auto-renewal? You will keep Premium until the end of the current period.');">
                            @csrf
                            <button type="submit" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl hover:border-red-300 hover:text-red-600 transition-all">
                                Cancel subscription
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- Invoices --}}
        <div class="card p-6">
            <h2 class="text-sm font-bold text-gray-900 mb-4">Invoice history</h2>
            @if($invoices->isEmpty())
                <p class="text-sm text-gray-400 py-6 text-center">No invoices yet.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wider text-gray-400 border-b border-gray-100">
                                <th class="py-2.5 pr-4 font-semibold">Invoice</th>
                                <th class="py-2.5 pr-4 font-semibold">Date</th>
                                <th class="py-2.5 pr-4 font-semibold">Amount</th>
                                <th class="py-2.5 font-semibold">Status</th>
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
                                @endphp
                                <tr class="border-b border-gray-50">
                                    <td class="py-3 pr-4 font-medium text-gray-800">{{ $inv->invoice_number }}</td>
                                    <td class="py-3 pr-4 text-gray-500">{{ $inv->created_at->format('M j, Y') }}</td>
                                    <td class="py-3 pr-4 text-gray-800">{{ $inv->currency }} {{ number_format((float)$inv->total_amount, 2) }}</td>
                                    <td class="py-3"><span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $badge }}">{{ ucfirst($inv->status) }}</span></td>
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
