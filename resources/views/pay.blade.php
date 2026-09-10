<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.google-analytics')
    @include('partials.posthog')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('app.pay.meta_title', ['app' => config('app.name', 'Harmoniva')]) }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'] } } } }</script>
    <script src="https://cdn.paddle.com/paddle/v2/paddle.js"></script>
</head>
<body class="font-sans bg-gray-50 min-h-screen flex items-center justify-center p-6">

    <div class="max-w-md w-full text-center">
        @if ($hasTransaction)
            {{-- Paddle.js opens the overlay by itself for the ?_ptxn in the URL.
                 This is only what shows behind it while that happens. --}}
            <div id="pay-loading">
                <div class="w-10 h-10 mx-auto mb-5 border-2 border-gray-200 border-t-gray-900 rounded-full animate-spin"></div>
                <h1 class="text-lg font-semibold text-gray-900">{{ __('app.pay.opening') }}</h1>
                <p class="mt-2 text-sm text-gray-500">{{ __('app.pay.opening_hint') }}</p>
            </div>

            {{-- Shown only if Paddle.js fails to initialise, so the visitor is
                 never left staring at a spinner that will not resolve. --}}
            <div id="pay-error" class="hidden">
                <h1 class="text-lg font-semibold text-gray-900">{{ __('app.pay.error_title') }}</h1>
                <p class="mt-2 text-sm text-gray-500">{{ __('app.pay.error_body') }}</p>
                <a href="{{ route('billing.index') }}" class="inline-block mt-5 px-5 py-2.5 rounded-lg bg-gray-900 text-white text-sm font-semibold">
                    {{ __('app.pay.go_billing') }}
                </a>
            </div>
        @else
            <h1 class="text-lg font-semibold text-gray-900">{{ __('app.pay.bare_title') }}</h1>
            <p class="mt-2 text-sm text-gray-500">{{ __('app.pay.bare_body') }}</p>
            <div class="mt-5 flex items-center justify-center gap-3">
                <a href="{{ route('billing.index') }}" class="px-5 py-2.5 rounded-lg bg-gray-900 text-white text-sm font-semibold">
                    {{ __('app.pay.go_billing') }}
                </a>
                <a href="{{ route('pricing.index') }}" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 text-sm font-semibold">
                    {{ __('app.pay.go_pricing') }}
                </a>
            </div>
        @endif
    </div>

    <script>
        (function () {
            var token = @json($clientToken);
            var isSandbox = @json($sandbox);
            var hasTransaction = @json($hasTransaction);

            if (!hasTransaction) return;

            function fail() {
                var l = document.getElementById('pay-loading');
                var e = document.getElementById('pay-error');
                if (l) l.classList.add('hidden');
                if (e) e.classList.remove('hidden');
            }

            if (!token || typeof Paddle === 'undefined') { fail(); return; }

            try {
                if (isSandbox) Paddle.Environment.set('sandbox');

                Paddle.Initialize({
                    token: token,
                    checkout: {
                        settings: {
                            displayMode: 'overlay',
                            theme: 'light',
                            locale: @json($locale)
                        }
                    },
                    eventCallback: function (event) {
                        if (event.name !== 'checkout.completed') return;

                        // The local subscription id we attached when the
                        // transaction was created. Paddle hands custom_data back
                        // untouched, which is what lets a page with no session
                        // send the buyer to the right confirmation.
                        var custom = (event.data && event.data.custom_data) || {};
                        var id = custom.local_subscription_id;

                        window.location = id
                            ? '/checkout/success/' + encodeURIComponent(id)
                            : @json(route('billing.index'));
                    }
                });
            } catch (err) {
                fail();
            }
        })();
    </script>

</body>
</html>
