<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex">
    <title>{{ config('app.name') }} — {{ __('app.email_prefs.title') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@0.460.0"></script>
    <script defer src="https://unpkg.com/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: { extend: {
                fontFamily: { sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'] },
                colors: { primary: { 50:'#faf5ff',100:'#f3e8ff',200:'#e9d5ff',300:'#d8b4fe',400:'#c084fc',500:'#a855f7',600:'#9333ea',700:'#7c3aed',800:'#6b21a8',900:'#581c87' } }
            } }
        }
    </script>
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="font-sans min-h-screen bg-gradient-to-b from-primary-50 via-white to-white text-gray-800">

@php
    $freq = old('frequency', $preference->frequency ?: 'all');
    $actionUrl = $token
        ? url()->signedRoute('email.preferences.update', ['token' => $token])
        : route('email-preferences.update');
@endphp

<div class="max-w-xl mx-auto px-4 py-10 sm:py-14">

    {{-- Header --}}
    <div class="text-center mb-8">
        <a href="{{ config('app.url') }}" class="inline-flex items-center gap-2 text-primary-700 font-extrabold text-xl no-underline">
            <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-white" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">🎵</span>
            {{ config('app.name') }}
        </a>
        <h1 class="mt-5 text-2xl font-extrabold text-gray-900">{{ __('app.email_prefs.title') }}</h1>
        <p class="mt-1.5 text-sm text-gray-500">{{ __('app.email_prefs.subtitle') }}</p>
        <p class="mt-1 text-xs text-gray-400">{{ __('app.email_prefs.for_email') }} <span class="font-semibold text-gray-600">{{ $user->email }}</span></p>
    </div>

    @if($saved || session('status') === 'email-prefs-updated')
        <div class="mb-6 flex items-center gap-3 px-4 py-3 rounded-xl bg-green-50 border border-green-200">
            <i data-lucide="check-circle" class="w-5 h-5 text-green-600 flex-shrink-0"></i>
            <p class="text-sm text-green-700 font-medium">{{ __('app.email_prefs.saved') }}</p>
        </div>
    @endif

    <form method="POST" action="{{ $actionUrl }}"
          x-data="{ freq: '{{ $freq }}', unsub: {{ old('unsubscribe_all', $unsubscribedAll) ? 'true' : 'false' }} }">
        @csrf
        @unless($token) @method('PUT') @endunless

        {{-- Frequency --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6 mb-5">
            <h2 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                <i data-lucide="clock" class="w-4 h-4 text-primary-600"></i>
                {{ __('app.email_prefs.frequency_heading') }}
            </h2>
            <div class="space-y-2.5">
                @foreach(['all' => '📬', 'weekly' => '🗓️', 'important_only' => '🔔'] as $value => $emoji)
                    <label class="flex items-start gap-3 p-3.5 rounded-xl border cursor-pointer transition"
                           :class="freq === '{{ $value }}' ? 'border-primary-300 bg-primary-50/60 ring-1 ring-primary-200' : 'border-gray-200 hover:border-primary-200'">
                        <input type="radio" name="frequency" value="{{ $value }}" x-model="freq"
                               class="mt-1 text-primary-600 focus:ring-primary-500">
                        <span>
                            <span class="block text-sm font-semibold text-gray-900">{{ $emoji }} {{ __('app.email_prefs.freq_'.$value) }}</span>
                            <span class="block text-xs text-gray-500 mt-0.5">{{ __('app.email_prefs.freq_'.$value.'_desc') }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Topics --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6 mb-5"
             :class="(freq !== 'all' || unsub) ? 'opacity-50' : ''">
            <h2 class="text-sm font-bold text-gray-900 mb-1 flex items-center gap-2">
                <i data-lucide="list-checks" class="w-4 h-4 text-primary-600"></i>
                {{ __('app.email_prefs.topics_heading') }}
            </h2>
            <p class="text-xs text-gray-400 mb-4">{{ __('app.email_prefs.topics_note') }}</p>
            <div class="space-y-2">
                @php
                    $topics = ['tips' => '💡', 'progress' => '📈', 'offers' => '⭐', 'product' => '✨'];
                    // Teaching-activity topic is only relevant to teacher/school audiences.
                    if (in_array($audience, ['teacher', 'school'])) {
                        $topics['teaching'] = '🎓';
                    }
                @endphp
                @foreach($topics as $topic => $emoji)
                    <label class="flex items-center justify-between gap-3 p-3 rounded-xl hover:bg-gray-50 cursor-pointer transition">
                        <span class="flex items-start gap-3">
                            <span class="text-lg leading-none mt-0.5">{{ $emoji }}</span>
                            <span>
                                <span class="block text-sm font-medium text-gray-800">{{ __('app.email_prefs.topic_'.$topic) }}</span>
                                <span class="block text-xs text-gray-500">{{ __('app.email_prefs.topic_'.$topic.'_desc') }}</span>
                            </span>
                        </span>
                        <input type="checkbox" name="topic_{{ $topic }}" value="1"
                               {{ old('topic_'.$topic, $preference->{'topic_'.$topic} ? '1' : '') ? 'checked' : '' }}
                               class="w-5 h-5 rounded text-primary-600 focus:ring-primary-500 flex-shrink-0">
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Unsubscribe all --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6 mb-5">
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" name="unsubscribe_all" value="1" x-model="unsub"
                       class="mt-0.5 w-5 h-5 rounded text-red-600 focus:ring-red-500 flex-shrink-0">
                <span>
                    <span class="block text-sm font-semibold text-gray-900">{{ __('app.email_prefs.unsubscribe_all') }}</span>
                    <span class="block text-xs text-gray-500 mt-0.5">{{ __('app.email_prefs.unsubscribe_all_desc') }}</span>
                </span>
            </label>
        </div>

        {{-- Always-sent note --}}
        <div class="flex items-start gap-2.5 px-4 py-3 rounded-xl bg-blue-50 border border-blue-100 mb-6">
            <i data-lucide="shield-check" class="w-4 h-4 text-blue-500 flex-shrink-0 mt-0.5"></i>
            <p class="text-xs text-blue-700 leading-relaxed">{{ __('app.email_prefs.always_sent_note') }}</p>
        </div>

        <button type="submit"
                class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl text-white font-semibold text-sm shadow-md hover:opacity-95 transition"
                style="background:linear-gradient(135deg,#9333ea,#7c3aed);">
            <i data-lucide="save" class="w-4 h-4"></i> {{ __('app.email_prefs.save') }}
        </button>
    </form>

    @if($authed)
        <div class="text-center mt-6">
            <a href="{{ $settingsUrl }}" class="inline-flex items-center gap-1.5 text-sm text-primary-600 hover:text-primary-700 font-medium">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> {{ __('app.email_prefs.back_to_settings') }}
            </a>
        </div>
    @endif

</div>

<script>
    document.addEventListener('DOMContentLoaded', () => window.lucide?.createIcons());
    document.addEventListener('alpine:initialized', () => window.lucide?.createIcons());
</script>
</body>
</html>
