<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $profileUser->name }} – {{ config('app.name', 'Harmoniva') }}</title>

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
                    accent: { 400:'#fb923c',500:'#f97316',600:'#ea580c' }
                }
            } }
        }
    </script>
    <style>
        .hero-gradient { background: linear-gradient(135deg, #6d28d9 0%, #8b5cf6 50%, #a78bfa 100%); }
        .card { background:white; border-radius:16px; border:1px solid #ede9fe; box-shadow:0 2px 8px 0 rgb(109 40 217/0.06),0 1px 2px -1px rgb(0 0 0/0.06); }
        .btn-primary { background:linear-gradient(135deg,#6d28d9 0%,#8b5cf6 100%); transition:all 0.2s; }
        .btn-primary:hover { background:linear-gradient(135deg,#5b21b6 0%,#7c3aed 100%); transform:translateY(-1px); box-shadow:0 8px 20px -4px rgb(109 40 217/0.4); }
    </style>
    @livewireStyles
</head>
<body class="font-sans bg-gray-50 min-h-screen">

    @include('partials.navbar', ['active' => ''])

    <div class="hero-gradient h-32"></div>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 -mt-16 pb-10">
        <div class="card p-6">
            <div class="flex flex-col sm:flex-row sm:items-end gap-4">
                {{-- Avatar --}}
                @if($profileUser->hasAvatar())
                    <img src="{{ $profileUser->avatar }}" alt="" class="w-24 h-24 rounded-2xl object-cover border-4 border-white shadow -mt-12 sm:-mt-16 bg-white">
                @else
                    <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white text-3xl font-bold border-4 border-white shadow -mt-12 sm:-mt-16">
                        {{ substr($profileUser->name ?? 'U', 0, 1) }}
                    </div>
                @endif

                <div class="flex-1">
                    <h1 class="text-2xl font-bold text-gray-900">{{ $profileUser->name }} {{ $profileUser->surname }}</h1>
                    <p class="text-gray-400 text-sm">&#64;{{ $profileUser->username }}</p>
                </div>

                {{-- Actions --}}
                @auth
                    @if(auth()->id() !== $profileUser->id)
                        <div class="flex items-center gap-2">
                            @livewire('follow-button', ['user' => $profileUser])
                            <a href="{{ url('/messages?to='.$profileUser->username) }}"
                               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 transition-all">
                                <i data-lucide="message-circle" class="w-4 h-4"></i>
                                {{ __('app.social.message') }}
                            </a>
                        </div>
                    @endif
                @endauth
            </div>

            {{-- Bio --}}
            @if($profileUser->profile?->bio)
                <p class="mt-4 text-sm text-gray-600 whitespace-pre-line">{{ $profileUser->profile->bio }}</p>
            @endif

            {{-- Meta chips --}}
            <div class="mt-4 flex flex-wrap gap-2 text-xs">
                @if($profileUser->profile?->primary_instrument)
                    <span class="inline-flex items-center gap-1 bg-purple-50 text-purple-700 rounded-full px-3 py-1">
                        <i data-lucide="music-2" class="w-3.5 h-3.5"></i> {{ $profileUser->profile->primary_instrument }}
                    </span>
                @endif
                @if($profileUser->profile?->musical_level)
                    <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 rounded-full px-3 py-1">
                        <i data-lucide="bar-chart-2" class="w-3.5 h-3.5"></i> {{ ucfirst($profileUser->profile->musical_level) }}
                    </span>
                @endif
                @if($profileUser->city || $profileUser->country)
                    <span class="inline-flex items-center gap-1 bg-gray-50 text-gray-600 rounded-full px-3 py-1">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5"></i> {{ collect([$profileUser->city, $profileUser->country])->filter()->join(', ') }}
                    </span>
                @endif
            </div>

            {{-- Follow / stat counts --}}
            <div class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach([
                    ['label' => __('app.social.followers'), 'value' => $followersCount, 'icon' => 'users'],
                    ['label' => __('app.social.following'), 'value' => $followingCount, 'icon' => 'user-check'],
                    ['label' => __('app.social.sessions'), 'value' => $totalSessions, 'icon' => 'activity'],
                    ['label' => __('app.social.accuracy'), 'value' => $overallAccuracy.'%', 'icon' => 'target'],
                ] as $stat)
                    <div class="bg-gray-50 rounded-xl p-3 text-center">
                        <div class="text-lg font-bold text-gray-900">{{ $stat['value'] }}</div>
                        <div class="text-xs text-gray-400">{{ $stat['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @livewireScripts
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.hook('morph.updated', () => window.lucide && lucide.createIcons());
        });
        document.addEventListener('DOMContentLoaded', () => window.lucide && lucide.createIcons());
        document.addEventListener('livewire:navigated', () => window.lucide && lucide.createIcons());
    </script>
</body>
</html>
