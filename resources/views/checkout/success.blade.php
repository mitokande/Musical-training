<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.google-analytics')
    @include('partials.posthog')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Welcome to Premium — {{ config('app.name', 'Harmoniva') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@0.460.0"></script>
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'] } } } }</script>
</head>
<body class="font-sans text-gray-700 antialiased">
@include('partials.navbar', ['active' => 'billing'])
<div class="flex items-center justify-center px-4 py-16"
     style="background: linear-gradient(135deg, #faf5ff 0%, #FAF7F2 60%, #fef3c7 100%); min-height: calc(100vh - 64px);">
    <div class="max-w-lg w-full bg-white rounded-2xl border border-gray-100 shadow-lg p-8 sm:p-10 text-center">
        <div class="mx-auto mb-6 w-16 h-16 rounded-full flex items-center justify-center" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
            <i data-lucide="check" style="width:32px;height:32px;color:#fff;"></i>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mb-3">You're Premium! 🎉</h1>
        <p class="text-gray-500 mb-2">
            Your <strong>{{ $subscription->plan->name ?? 'Premium' }}</strong> subscription is active
            @if($subscription->ends_at)
                until {{ $subscription->ends_at->format('M j, Y') }}.
            @else.
            @endif
        </p>
        <p class="text-sm text-gray-400 mb-8">Invoice #{{ optional($subscription->invoices()->latest()->first())->invoice_number }} · Available in your billing page.</p>

        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('dashboard') }}"
               class="inline-flex items-center justify-center gap-2 px-6 py-3 text-sm font-bold text-white rounded-xl hover:opacity-90 transition-all shadow-lg"
               style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
                <i data-lucide="layout-dashboard" style="width:16px;height:16px;"></i> Go to Dashboard
            </a>
            <a href="{{ route('billing.index') }}"
               class="inline-flex items-center justify-center gap-2 px-6 py-3 text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl hover:border-purple-300 transition-all">
                View billing
            </a>
        </div>
    </div>
</div>
<script>lucide.createIcons();</script>
</body>
</html>
