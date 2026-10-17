<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.google-analytics')
    @include('partials.posthog')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $lesson->title }} - {{ config('app.name', 'Harmoniva') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    <link rel="preconnect" href="{{ $derslikOrigin }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@0.460.0"></script>
    <script defer src="https://unpkg.com/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <script>
        tailwind.config = { theme: { extend: {
            fontFamily: { sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'] },
            colors: { primary: { 50:'#faf5ff',100:'#f3e8ff',600:'#9333ea',700:'#7c3aed' } }
        } } }
    </script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
{{--
    A Derslik (meeting.harmoniva.app) lesson embedded on a Harmoniva page.
    Teacher: Derslik's teacher URL (carries the teacher key) + share panel + end button.
    Student: Derslik's student URL; Derslik's waiting room admits them.
    Derslik posts { source: 'derslik', type } events (ready, waiting, joined, left, ended);
    on ready/joined we tell it to show this page's link in its own share button.
--}}
@php $ll = 'teacher.live_lessons'; @endphp
<body class="font-sans bg-gray-950 text-gray-100 h-[100dvh] flex flex-col overflow-hidden">

<header class="shrink-0 flex items-center gap-3 px-3 sm:px-5 h-14 border-b border-gray-800 bg-gray-900"
        @if($isTeacher) x-data="{ open: false, copied: false, canShare: typeof navigator.share === 'function' }" @endif>
    <a href="{{ $links['back'] }}" class="p-2 -ml-1 text-gray-400 hover:text-white rounded-lg" title="{{ __($ll.'.back') }}" aria-label="{{ __($ll.'.back') }}">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
    </a>
    <img src="{{ asset('favicon.svg') }}" alt="" class="w-6 h-6 hidden sm:block">
    <div class="min-w-0 flex-1">
        <h1 class="text-sm font-bold truncate flex items-center gap-2">
            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-red-500/15 text-red-300 text-[10px] font-bold uppercase tracking-wide shrink-0">
                <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>{{ __($ll.'.live_badge') }}
            </span>
            <span class="truncate">{{ $lesson->title }}</span>
        </h1>
        @unless($isTeacher)
            <p class="text-xs text-gray-400 truncate">{{ __($ll.'.with_teacher', ['name' => $lesson->teacher->fullName()]) }}</p>
        @endunless
    </div>

    @if($isTeacher)
        {{-- Share the student link (the harmoniva.app page, never Derslik's own URL) --}}
        <div class="relative">
            <button type="button" @click="open = !open" :aria-expanded="open"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition">
                <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                <span class="hidden sm:inline">{{ __($ll.'.student_link') }}</span>
            </button>
            <div x-show="open" x-cloak @click.outside="open = false" @keydown.escape.window="open = false"
                 class="absolute right-0 top-full mt-2 w-[min(22rem,calc(100vw-1.5rem))] p-4 bg-gray-900 border border-gray-700 rounded-xl shadow-2xl z-20">
                <p class="text-xs text-gray-400 mb-2">{{ __($ll.'.student_link_hint') }}</p>
                <input type="text" readonly value="{{ $shareUrl }}" @focus="$el.select()" x-ref="link" aria-label="{{ __($ll.'.student_link') }}"
                       class="w-full px-2.5 py-2 text-xs text-gray-200 bg-gray-950 border border-gray-700 rounded-lg">
                <div class="mt-2 grid grid-cols-2 gap-2">
                    <button type="button"
                            @click="navigator.clipboard.writeText(@js($shareUrl)).then(() => { copied = true; setTimeout(() => copied = false, 2000) }).catch(() => { $refs.link.select(); window.prompt(@js(__($ll.'.copy_failed')), @js($shareUrl)) })"
                            class="inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg">
                        <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                        <span x-show="!copied">{{ __($ll.'.copy') }}</span>
                        <span x-show="copied" x-cloak>{{ __($ll.'.copied') }}</span>
                    </button>
                    <a href="https://wa.me/?text={{ rawurlencode(__($ll.'.share_text', ['url' => $shareUrl])) }}" target="_blank" rel="noopener"
                       class="inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold text-green-300 bg-green-500/10 hover:bg-green-500/20 rounded-lg">
                        <i data-lucide="message-circle" class="w-3.5 h-3.5"></i> {{ __($ll.'.share_whatsapp') }}
                    </a>
                    <button type="button" x-show="canShare" x-cloak
                            @click="navigator.share({ title: @js($lesson->title), url: @js($shareUrl) }).catch(() => {})"
                            class="col-span-2 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold text-gray-200 bg-gray-800 hover:bg-gray-700 rounded-lg">
                        <i data-lucide="share-2" class="w-3.5 h-3.5"></i> {{ __($ll.'.share') }}
                    </button>
                </div>
            </div>
        </div>
        <form method="POST" action="{{ $links['end'] }}" onsubmit="return confirm(@js(__($ll.'.end_confirm')))">
            @csrf
            <button class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-red-300 hover:text-white hover:bg-red-600 border border-red-500/40 rounded-lg transition">
                <i data-lucide="circle-stop" class="w-3.5 h-3.5"></i>
                <span class="hidden sm:inline">{{ __($ll.'.end') }}</span>
            </button>
        </form>
    @endif
</header>

<main class="flex-1 min-h-0 bg-black">
    <iframe id="derslik" src="{{ $iframeSrc }}" title="{{ $lesson->title }}"
            allow="camera; microphone; display-capture; fullscreen; autoplay; screen-wake-lock; clipboard-write; web-share"
            allowfullscreen referrerpolicy="no-referrer"
            class="w-full h-full border-0 block"></iframe>
</main>

<script>
    (() => {
        const origin = @json($derslikOrigin);
        const shareUrl = @json($shareUrl);
        const frame = document.getElementById('derslik');
        window.addEventListener('message', (e) => {
            if (e.origin !== origin || e.source !== frame.contentWindow || e.data?.source !== 'derslik') return;
            if (e.data.type === 'ready' || e.data.type === 'joined') {
                frame.contentWindow.postMessage({ type: 'share-url', url: shareUrl }, origin);
            }
        });
    })();
    lucide.createIcons();
</script>
</body>
</html>
