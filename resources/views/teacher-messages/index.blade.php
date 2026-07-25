<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.google-analytics')
    @include('partials.posthog')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('teacher.messaging.title') }} - {{ config('app.name', 'Harmoniva') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@0.460.0"></script>
    <script>
        tailwind.config = { theme: { extend: {
            fontFamily: { sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'] },
            colors: { primary: { 50:'#faf5ff',100:'#f3e8ff',600:'#9333ea',700:'#7c3aed' } }
        } } }
    </script>
    <style>.card { background: white; border-radius: 12px; border: 1px solid #e5e7eb; }</style>
</head>
<body class="font-sans bg-gray-50 min-h-screen">
@include('partials.navbar')

<main class="max-w-3xl mx-auto px-4 sm:px-6 py-8">
    <h1 class="text-2xl font-bold text-gray-900">{{ __('teacher.messaging.title') }}</h1>
    <p class="text-gray-500 text-sm mt-1 mb-6">{{ __('teacher.messaging.student_subtitle') }}</p>

    @if ($errors->any())
        <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 rounded-xl">
            @foreach ($errors->all() as $error)<p class="text-sm text-red-600">{{ $error }}</p>@endforeach
        </div>
    @endif

    @if($newRecipients->isNotEmpty())
        <div class="card p-4 mb-6 flex flex-wrap items-center gap-3">
            <span class="text-sm font-semibold text-gray-600">{{ __('teacher.messaging.new_conversation') }}:</span>
            @foreach($newRecipients as $teacher)
                <form method="POST" action="{{ route('teacher-messages.start', $teacher) }}">
                    @csrf
                    <button class="px-3 py-1.5 text-xs font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 rounded-lg transition">
                        {{ $teacher->name }} {{ $teacher->surname }}
                    </button>
                </form>
            @endforeach
        </div>
    @endif

    <div class="card overflow-hidden">
        @if($conversations->isEmpty())
            <div class="p-10 text-center text-gray-500">
                <i data-lucide="message-circle" class="w-10 h-10 mx-auto mb-3 text-gray-300"></i>
                <p class="text-sm">{{ __('teacher.messaging.no_conversations') }}</p>
            </div>
        @else
            <ul class="divide-y divide-gray-100">
                @foreach($conversations as $conversation)
                    @php $unread = $conversation->unreadCountFor(auth()->user()); @endphp
                    <li>
                        <a href="{{ route('teacher-messages.show', $conversation) }}" class="flex items-center gap-4 px-5 py-4 hover:bg-gray-50 transition">
                            @if($conversation->teacher->hasAvatar())
                                <img src="{{ $conversation->teacher->avatar }}" class="w-11 h-11 rounded-full object-cover" alt="">
                            @else
                                <div class="w-11 h-11 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-bold">
                                    {{ strtoupper(substr($conversation->teacher->name, 0, 1)) }}
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-900 truncate">{{ $conversation->teacher->name }} {{ $conversation->teacher->surname }}</p>
                                <p class="text-xs text-gray-400">{{ $conversation->last_message_at?->diffForHumans() }}</p>
                            </div>
                            @if($unread > 0)
                                <span class="px-2 py-0.5 rounded-full bg-primary-600 text-white text-[11px] font-bold">{{ $unread }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</main>

@include('partials.footer')
<script>lucide.createIcons();</script>
</body>
</html>
