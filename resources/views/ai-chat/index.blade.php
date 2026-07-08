<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('app.ai_chat.title') }} - {{ config('app.name', 'Harmoniva') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@0.460.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked@12/marked.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'] },
                    colors: {
                        primary: { 50:'#faf5ff',100:'#f3e8ff',200:'#e9d5ff',300:'#d8b4fe',400:'#c084fc',500:'#a855f7',600:'#9333ea',700:'#7c3aed',800:'#6b21a8',900:'#581c87' },
                        accent:  { 400:'#fb923c',500:'#f97316',600:'#ea580c' }
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .chat-prose p            { margin-bottom: 0.6rem; line-height: 1.65; }
        .chat-prose p:last-child { margin-bottom: 0; }
        .chat-prose ul           { margin: 0.4rem 0 0.6rem 1.1rem; list-style-type: disc; }
        .chat-prose ol           { margin: 0.4rem 0 0.6rem 1.1rem; list-style-type: decimal; }
        .chat-prose li           { margin-bottom: 0.25rem; line-height: 1.6; }
        .chat-prose strong       { font-weight: 600; color: #374151; }
        .chat-prose code         { background: #f3f4f6; border-radius: 4px; padding: 0.1em 0.35em; font-size: 0.85em; font-family: monospace; }
        .chat-prose h3           { font-size: 0.9rem; font-weight: 600; margin: 0.6rem 0 0.3rem; color: #374151; }
        .chat-prose blockquote   { border-left: 3px solid #d8b4fe; padding-left: 0.75rem; color: #6b7280; margin: 0.4rem 0; }
        .chat-prose hr           { border: none; border-top: 1px solid #e5e7eb; margin: 0.75rem 0; }
    </style>
</head>
<body class="font-sans bg-gray-50 min-h-screen">

@include('partials.navbar', ['active' => 'ai-chat'])

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center shadow-sm">
                <i data-lucide="message-circle" class="w-5 h-5 text-white"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-900">Ask Your AI Music Assistant</h1>
                <p class="text-xs text-gray-500">{{ __('app.ai_chat.subtitle') }}</p>
            </div>
        </div>
        @if(count($history) > 0)
            <form method="POST" action="{{ route('ai-chat.clear') }}" onsubmit="return confirm('{{ __('app.messages.clear_history_confirm') }}')">
                @csrf
                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-500 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:text-red-600 transition">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    {{ __('app.ai_chat.clear') }}
                </button>
            </form>
        @endif
    </div>

    {{-- 2-column layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-[7fr_3fr] gap-6 items-start">

        {{-- Left: Chat --}}
        <div>

            {{-- Error --}}
            @if(session('chat_error'))
                <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 rounded-xl flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 flex-shrink-0"></i>
                    <p class="text-sm text-red-700">{{ session('chat_error') }}</p>
                </div>
            @endif

            {{-- Chat Card --}}
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm flex flex-col" style="min-height: 540px;">

                {{-- Messages --}}
                <div class="flex-1 p-5 space-y-4 overflow-y-auto" style="max-height: 462px;" id="chat-messages">

                    @if(count($history) === 0)
                        <div class="flex flex-col items-center justify-center py-16 text-center">
                            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-primary-100 to-purple-100 flex items-center justify-center mb-4">
                                <i data-lucide="music-2" class="w-10 h-10 text-primary-500"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800 mb-2">{{ __('app.ai_chat.welcome_title') }}</h3>
                            <p class="text-sm text-gray-500 mb-6 max-w-sm">{{ __('app.ai_chat.welcome_text') }}</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 w-full max-w-md">
                                @foreach(__('app.ai_chat.suggestions') as $suggestion)
                                    <button onclick="const el=document.getElementById('message-input'); el.value='{{ $suggestion }}'; el.dispatchEvent(new Event('input',{bubbles:true}));"
                                            class="px-3 py-2.5 text-xs text-left text-gray-600 bg-white border border-gray-200 rounded-xl hover:border-primary-300 hover:text-primary-700 hover:bg-primary-50 transition">
                                        {{ $suggestion }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @else
                        @foreach($history as $msg)
                            @if($msg['role'] === 'user')
                                <div class="flex justify-end">
                                    <div class="max-w-xs sm:max-w-md">
                                        <div class="bg-primary-600 text-white rounded-2xl rounded-br-sm px-4 py-3 text-sm leading-relaxed">
                                            {{ $msg['content'] }}
                                        </div>
                                        <p class="text-xs text-gray-400 text-right mt-1 pr-1">{{ $msg['time'] ?? '' }}</p>
                                    </div>
                                </div>
                            @else
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-primary-500 to-accent-500 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <span class="text-white text-sm leading-none select-none">♪</span>
                                    </div>
                                    <div class="max-w-xs sm:max-w-xl flex-1">
                                        <div class="bg-white border border-gray-100 rounded-2xl rounded-tl-sm px-4 py-3 text-sm text-gray-700 shadow-sm chat-prose"
                                             data-md="{{ $msg['content'] }}"></div>
                                        <p class="text-xs text-gray-400 mt-1 pl-1">{{ $msg['time'] ?? '' }}</p>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>

                {{-- Input --}}
                <div class="border-t border-gray-100 p-4" x-data="{ message: '', sending: false }">
                    <form method="POST" action="{{ route('ai-chat.send') }}" @submit="sending = true" class="flex items-end gap-3">
                        @csrf
                        <div class="flex-1">
                            <textarea
                                id="message-input"
                                name="message"
                                x-model="message"
                                rows="1"
                                maxlength="500"
                                placeholder="{{ __('app.ai_chat.placeholder') }}"
                                class="w-full resize-none px-3 py-2 text-sm text-gray-800 placeholder-gray-400 focus:outline-none bg-transparent"
                                style="max-height: 120px; overflow-y: auto;"
                                @keydown.enter.prevent.stop="if (!$event.shiftKey && message.trim()) { $el.closest('form').submit(); sending = true; }"
                                @input="$el.style.height = ''; $el.style.height = Math.min($el.scrollHeight, 120) + 'px'"
                            ></textarea>
                            <p class="text-xs text-gray-400 text-right mt-1" x-text="message.length + '/500'"></p>
                        </div>
                        <button type="submit"
                                :disabled="!message.trim() || sending"
                                :class="message.trim() && !sending ? 'bg-primary-600 hover:bg-primary-700 text-white' : 'bg-gray-100 text-gray-400 cursor-not-allowed'"
                                class="flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center transition">
                            <template x-if="!sending">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                            </template>
                            <template x-if="sending">
                                <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                            </template>
                        </button>
                    </form>
                </div>

            </div>
        </div>

        {{-- Right: Sidebar (1/3) --}}
        <div class="space-y-5">

            {{-- AI Coach Card --}}
            <div class="rounded-2xl overflow-hidden shadow-sm">
                <div class="bg-gradient-to-br from-purple-600 to-indigo-600 p-5 text-white">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="brain" class="w-6 h-6 text-white"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg leading-tight">AI Coach</h3>
                            <p class="text-white/70 text-xs">Personalized weekly plan</p>
                        </div>
                    </div>
                    <p class="text-white/85 text-sm mb-4 leading-relaxed">AI analyzes your practice history and profile to prepare a personalized weekly study plan.</p>
                    <a href="/ai-coach" class="block text-center bg-white text-purple-700 font-semibold py-2.5 px-4 rounded-xl hover:bg-gray-100 transition-colors text-sm">
                        Open AI Coach →
                    </a>
                </div>
            </div>

            {{-- Your Profile --}}
            @php
                $levelLabels = ['beginner' => 'Beginner', 'intermediate' => 'Intermediate', 'advanced' => 'Advanced'];
            @endphp
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-5">
                <h3 class="font-semibold text-gray-800 mb-3 flex items-center gap-2 text-sm">
                    <i data-lucide="user-check" class="w-4 h-4 text-purple-500"></i>
                    Your Profile
                </h3>
                <div class="space-y-2.5">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Instrument</span>
                        <span class="font-medium text-gray-800">{{ $profile?->primary_instrument ?: '—' }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Level</span>
                        <span class="font-medium text-gray-800">{{ $levelLabels[$profile?->musical_level ?? ''] ?? '—' }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Weekly Practice</span>
                        <span class="font-medium text-gray-800">{{ $profile?->weekly_practice_hours ? $profile->weekly_practice_hours . ' hrs' : '—' }}</span>
                    </div>
                </div>
                @if(!$profile || !$profile->primary_instrument || !$profile->musical_level)
                    <a href="{{ route('profile.edit', ['tab' => 'music']) }}" class="mt-3 block text-center text-xs font-medium text-purple-600 bg-purple-50 hover:bg-purple-100 rounded-lg py-2 transition-colors">
                        Complete Your Profile →
                    </a>
                @endif
            </div>

            {{-- My Progress --}}
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-5">
                <h3 class="font-semibold text-gray-800 mb-1 flex items-center gap-2 text-sm">
                    <i data-lucide="trending-up" class="w-4 h-4 text-green-500"></i>
                    My Progress
                </h3>
                <p class="text-xs text-gray-400 mb-4">Track your ear training journey and see how far you've come</p>
                <div class="grid grid-cols-2 gap-2.5">
                    <div class="bg-orange-50 rounded-xl p-3 text-center">
                        <div class="text-xl font-bold text-gray-900">{{ $streak }}</div>
                        <div class="text-xs text-gray-500 mt-0.5">🔥 Streak</div>
                    </div>
                    <div class="bg-blue-50 rounded-xl p-3 text-center">
                        <div class="text-xl font-bold text-gray-900">{{ $totalSessions }}</div>
                        <div class="text-xs text-gray-500 mt-0.5">Sessions</div>
                    </div>
                    <div class="bg-green-50 rounded-xl p-3 text-center">
                        <div class="text-xl font-bold text-gray-900">{{ $accuracy }}%</div>
                        <div class="text-xs text-gray-500 mt-0.5">Accuracy</div>
                    </div>
                    <div class="bg-purple-50 rounded-xl p-3 text-center">
                        <div class="text-xl font-bold text-gray-900">{{ $totalQuestions }}</div>
                        <div class="text-xs text-gray-500 mt-0.5">Questions</div>
                    </div>
                </div>
                <a href="/progress" class="mt-3 block text-center text-xs font-medium text-purple-600 hover:text-purple-700 transition-colors">
                    View Full Report →
                </a>
            </div>

        </div>
    </div>
</div>

<script>
    lucide.createIcons();

    if (typeof marked !== 'undefined') {
        marked.setOptions({ breaks: true, gfm: true });
        document.querySelectorAll('.chat-prose[data-md]').forEach(el => {
            el.innerHTML = marked.parse(el.dataset.md || '');
        });
    }

    const chatMessages = document.getElementById('chat-messages');
    if (chatMessages) chatMessages.scrollTop = chatMessages.scrollHeight;
</script>
</body>
</html>
