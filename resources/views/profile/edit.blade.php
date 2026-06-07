<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('app.profile.title') }} - {{ config('app.name', 'Harmoniva') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@0.460.0"></script>
    <script defer src="https://unpkg.com/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'] },
                    colors: {
                        primary: { 50:'#faf5ff',100:'#f3e8ff',200:'#e9d5ff',300:'#d8b4fe',400:'#c084fc',500:'#a855f7',600:'#9333ea',700:'#7c3aed',800:'#6b21a8',900:'#581c87' },
                        accent: { 400:'#fb923c',500:'#f97316',600:'#ea580c' }
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .section-anchor { scroll-margin-top: 90px; }
        html { scroll-behavior: smooth; }
        .premium-badge { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; }
        .progress-bar-fill { transition: width 0.8s cubic-bezier(0.4,0,0.2,1); }
        .nav-btn-active { background: linear-gradient(135deg, #f3e8ff 0%, #faf5ff 100%); color: #7c3aed !important; font-weight: 600; }
        .nav-btn-active svg { color: #7c3aed !important; }
    </style>
</head>
<body class="font-sans bg-gray-50 min-h-screen">

@include('partials.navbar', ['active' => 'profile'])

@php $initialMode = in_array($tab, ['questionnaire','settings']) ? $tab : 'main'; @endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
     x-data="{
         mode: '{{ $initialMode }}',
         activeSection: '{{ in_array($tab, ['general','music','progress']) ? $tab : 'general' }}',
         switchMode(m) {
             this.mode = m;
             window.scrollTo({ top: 0, behavior: 'smooth' });
         },
         gotoSection(id) {
             if (this.mode !== 'main') {
                 this.mode = 'main';
                 this.activeSection = id;
                 this.$nextTick(() => {
                     setTimeout(() => { document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' }); }, 80);
                 });
             } else {
                 this.activeSection = id;
                 document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' });
             }
         }
     }">

    {{-- Flash --}}
    @if(session('status'))
        <div class="mb-6 px-4 py-3 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                <i data-lucide="check" class="w-4 h-4 text-green-600"></i>
            </div>
            <p class="text-sm text-green-700">
                @switch(session('status'))
                    @case('profile-updated') {{ __('app.profile.updated') }} @break
                    @case('avatar-updated') {{ __('app.profile.avatar_updated') }} @break
                    @case('questionnaire-saved') {{ __('app.profile.questionnaire_saved') }} @break
                    @case('password-updated') {{ __('app.profile.password_updated') }} @break
                    @case('account-suspended') {{ __('app.profile.account_suspended') }} @break
                    @case('account-activated') {{ __('app.profile.account_reactivated') }} @break
                    @default {{ __('app.profile.action_success') }}
                @endswitch
            </p>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 rounded-xl">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-red-600"></i>
                </div>
                <p class="text-sm font-medium text-red-700">{{ __('app.messages.fix_errors') }}</p>
            </div>
            <ul class="list-disc list-inside text-sm text-red-600 ml-11">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="flex flex-col lg:flex-row gap-8">

        {{-- ===== SOL SIDEBAR ===== --}}
        <div class="lg:w-72 flex-shrink-0">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-24">

                {{-- Avatar --}}
                <div class="flex flex-col items-center mb-5">
                    @if($user->hasAvatar())
                        <img src="{{ $user->avatar }}" alt=""
                             class="w-32 h-32 rounded-full object-cover border-4 border-primary-100 shadow-md mb-4">
                    @else
                        <div class="w-32 h-32 rounded-full bg-gradient-to-br from-primary-500 to-pink-500 flex items-center justify-center text-white text-4xl font-bold shadow-md mb-4">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                    @endif
                    <h3 class="font-bold text-gray-900 text-lg text-center leading-tight">{{ $user->name }}</h3>
                    <div class="flex items-center gap-1.5 mt-2 flex-wrap justify-center">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-700">
                            {{ ucfirst($user->role) }}
                        </span>
                        @if($user->isPremium())
                            <span class="premium-badge inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold shadow-sm">
                                <i data-lucide="star" class="w-3 h-3"></i> Premium
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                Free
                            </span>
                        @endif
                        @if($user->isSuspended())
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-600">
                                <i data-lucide="pause-circle" class="w-3 h-3"></i> {{ __('app.profile.suspended_label') }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Tamamlanma --}}
                @php $completeness = $user->profile_completeness; @endphp
                <div class="mb-5 pb-5 border-b border-gray-100">
                    <div class="flex justify-between items-center mb-1.5">
                        <span class="text-xs font-medium text-gray-600">{{ __('app.profile.completeness') }}</span>
                        <span class="text-xs font-bold {{ $completeness === 100 ? 'text-green-600' : ($completeness >= 60 ? 'text-amber-600' : 'text-red-500') }}">%{{ $completeness }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                        <div class="h-1.5 rounded-full progress-bar-fill {{ $completeness === 100 ? 'bg-green-500' : ($completeness >= 60 ? 'bg-amber-500' : 'bg-red-500') }}" style="width: {{ $completeness }}%"></div>
                    </div>
                    @php $missing = $user->missing_profile_fields; @endphp
                    @if(count($missing) > 0)
                        <p class="text-xs text-red-400 mt-1.5 flex items-center gap-1">
                            <i data-lucide="alert-triangle" class="w-3 h-3"></i>
                            {{ __('app.profile.missing_fields', ['count' => count($missing)]) }}
                        </p>
                    @endif
                </div>

                {{-- Nav --}}
                <nav class="space-y-1">
                    <button @click="gotoSection('general')"
                            :class="mode === 'main' && activeSection === 'general' ? 'nav-btn-active' : 'text-gray-600 hover:bg-gray-50'"
                            class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm transition-all">
                        <i data-lucide="user" class="w-4 h-4 flex-shrink-0"></i>
                        {{ __('app.profile.general_info') }}
                    </button>
                    <button @click="gotoSection('music')"
                            :class="mode === 'main' && activeSection === 'music' ? 'nav-btn-active' : 'text-gray-600 hover:bg-gray-50'"
                            class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm transition-all">
                        <i data-lucide="music" class="w-4 h-4 flex-shrink-0"></i>
                        {{ __('app.profile.music_profile') }}
                    </button>
                    <button @click="gotoSection('progress')"
                            :class="mode === 'main' && activeSection === 'progress' ? 'nav-btn-active' : 'text-gray-600 hover:bg-gray-50'"
                            class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm transition-all">
                        <i data-lucide="bar-chart-2" class="w-4 h-4 flex-shrink-0"></i>
                        Progress
                    </button>
                    <button @click="switchMode('questionnaire')"
                            :class="mode === 'questionnaire' ? 'nav-btn-active' : 'text-gray-600 hover:bg-gray-50'"
                            class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm transition-all">
                        <i data-lucide="clipboard-list" class="w-4 h-4 flex-shrink-0"></i>
                        {{ __('app.profile.questionnaire') }}
                    </button>
                    <button @click="switchMode('settings')"
                            :class="mode === 'settings' ? 'nav-btn-active' : 'text-gray-600 hover:bg-gray-50'"
                            class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm transition-all">
                        <i data-lucide="settings" class="w-4 h-4 flex-shrink-0"></i>
                        {{ __('app.profile.settings') }}
                    </button>
                </nav>
            </div>
        </div>

        {{-- ===== ANA İÇERİK ===== --}}
        <div class="flex-1 min-w-0">

            {{-- ============ MAIN FLOW ============ --}}
            <div x-show="mode === 'main'" class="space-y-6">

                {{-- === ÜST PROGRESS KARTI === --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 md:p-8 flex flex-col md:flex-row gap-6 md:gap-8">

                        {{-- Sol (5/8): başlık + yatay bar grafiği --}}
                        <div class="flex-[5] flex flex-col justify-between">
                            <div>
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary-600 uppercase tracking-wider mb-2">
                                    <i data-lucide="trending-up" class="w-3.5 h-3.5"></i>
                                    {{ __('app.profile.stats') }}
                                </span>
                                <h2 class="text-2xl font-bold text-gray-900 leading-tight">{{ __('app.profile.overall_performance') }}</h2>
                                <p class="text-sm text-gray-400 mt-1">{{ __('app.profile.journey_subtitle') }}</p>
                            </div>

                            <div class="mt-6 space-y-4">
                                {{-- Doğruluk Barı --}}
                                <div>
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm font-medium text-gray-700">{{ __('app.profile.overall_accuracy') }}</span>
                                        <span class="text-xl font-bold text-primary-600">{{ $overallAccuracy }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                                        <div class="progress-bar-fill h-3 rounded-full bg-gradient-to-r from-primary-600 via-primary-500 to-primary-400"
                                             style="width: {{ $overallAccuracy }}%"></div>
                                    </div>
                                </div>

                                {{-- Profil Tamamlanma Barı --}}
                                <div>
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm font-medium text-gray-700">{{ __('app.profile.completeness') }}</span>
                                        <span class="text-sm font-bold {{ $completeness === 100 ? 'text-green-600' : 'text-amber-600' }}">%{{ $completeness }}</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                                        <div class="progress-bar-fill h-3 rounded-full {{ $completeness === 100 ? 'bg-gradient-to-r from-green-500 to-green-400' : 'bg-gradient-to-r from-amber-500 to-amber-400' }}"
                                             style="width: {{ $completeness }}%"></div>
                                    </div>
                                </div>

                                @if($totalQuestions > 0)
                                    <div class="flex items-center gap-4 text-sm pt-1">
                                        <span class="flex items-center gap-1.5 text-green-600 font-medium">
                                            <i data-lucide="check-circle" class="w-4 h-4"></i> {{ $totalCorrect }} {{ __('app.profile.total_correct') }}
                                        </span>
                                        <span class="flex items-center gap-1.5 text-red-400 font-medium">
                                            <i data-lucide="x-circle" class="w-4 h-4"></i> {{ $totalIncorrect }} {{ __('app.profile.total_incorrect') }}
                                        </span>
                                        <span class="text-gray-400">{{ $totalQuestions }} {{ __('app.profile.total_questions') }}</span>
                                    </div>
                                @else
                                    <p class="text-xs text-gray-400 pt-1">{{ __('app.profile.no_sessions') }} <a href="{{ route('learn') }}" class="text-primary-600 hover:underline">Başla →</a></p>
                                @endif
                            </div>
                        </div>

                        {{-- Ayırıcı --}}
                        <div class="hidden md:block w-px bg-gray-100 self-stretch"></div>

                        {{-- Sağ (3/8): 2x2 stat kutuları --}}
                        <div class="flex-[3] grid grid-cols-2 gap-3 content-start">
                            <div class="bg-gray-100 rounded-xl p-4 text-center hover:bg-gray-150 transition">
                                <div class="w-8 h-8 bg-primary-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                                    <i data-lucide="play-circle" class="w-4 h-4 text-primary-600"></i>
                                </div>
                                <div class="text-2xl font-bold text-gray-900">{{ $totalSessions }}</div>
                                <p class="text-xs text-gray-500 mt-0.5 font-medium">{{ __('app.profile.sessions') }}</p>
                            </div>
                            <div class="bg-gray-100 rounded-xl p-4 text-center hover:bg-gray-150 transition">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                                    <i data-lucide="help-circle" class="w-4 h-4 text-blue-600"></i>
                                </div>
                                <div class="text-2xl font-bold text-gray-900">{{ $totalQuestions }}</div>
                                <p class="text-xs text-gray-500 mt-0.5 font-medium">{{ __('app.profile.stat_questions') }}</p>
                            </div>
                            <div class="bg-gray-100 rounded-xl p-4 text-center hover:bg-gray-150 transition">
                                <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                                    <i data-lucide="clock" class="w-4 h-4 text-orange-500"></i>
                                </div>
                                <div class="text-2xl font-bold text-gray-900">{{ $formattedTime ?: '0s' }}</div>
                                <p class="text-xs text-gray-500 mt-0.5 font-medium">{{ __('app.profile.stat_duration') }}</p>
                            </div>
                            <div class="bg-gray-100 rounded-xl p-4 text-center hover:bg-gray-150 transition">
                                <div class="w-8 h-8 bg-red-50 rounded-lg flex items-center justify-center mx-auto mb-2 text-lg leading-none">🔥</div>
                                <div class="text-2xl font-bold text-gray-900">{{ $streak }}</div>
                                <p class="text-xs text-gray-500 mt-0.5 font-medium">{{ __('app.dashboard.streak') }}</p>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ===== BÖLÜM 1: GENEL BİLGİLER ===== --}}
                <div id="general" class="section-anchor space-y-4">

                    {{-- Genel bilgiler kartı --}}
                    <div x-data="{ editing: {{ $errors->any() ? 'true' : 'false' }} }" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                <i data-lucide="user" class="w-5 h-5 text-primary-600"></i>
                                {{ __('app.profile.general_info') }}
                            </h2>
                            <button @click="editing = !editing"
                                    :class="editing ? 'bg-gray-100 text-gray-600' : 'bg-primary-50 text-primary-600 hover:bg-primary-100'"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition">
                                <i :data-lucide="editing ? 'x' : 'pencil'" class="w-4 h-4"></i>
                                <span x-text="editing ? '{{ __('app.profile.cancel') }}' : '{{ __('app.profile.edit') }}'"></span>
                            </button>
                        </div>

                        <div x-show="!editing">
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="p-3 bg-gray-50 rounded-xl">
                                    <dt class="text-xs font-medium text-gray-500 mb-0.5">{{ __('app.profile.name') }}</dt>
                                    <dd class="text-sm font-semibold text-gray-900">{{ $user->name ?: '—' }}</dd>
                                </div>
                                <div class="p-3 bg-gray-50 rounded-xl">
                                    <dt class="text-xs font-medium text-gray-500 mb-0.5">{{ __('app.profile.email') }}</dt>
                                    <dd class="text-sm font-semibold text-gray-900">{{ $user->email ?: '—' }}</dd>
                                </div>
                                <div class="p-3 rounded-xl {{ empty($user->phone) ? 'bg-red-50 border border-red-100' : 'bg-gray-50' }}">
                                    <dt class="text-xs font-medium {{ empty($user->phone) ? 'text-red-500' : 'text-gray-500' }} mb-0.5">{{ __('app.profile.phone') }} {{ empty($user->phone) ? '· ' . __('app.common.missing') : '' }}</dt>
                                    <dd class="text-sm font-semibold {{ empty($user->phone) ? 'text-red-400 italic' : 'text-gray-900' }}">{{ $user->phone ?: __('app.common.not_specified') }}</dd>
                                </div>
                                <div class="p-3 rounded-xl {{ empty($user->country) ? 'bg-red-50 border border-red-100' : 'bg-gray-50' }}">
                                    <dt class="text-xs font-medium {{ empty($user->country) ? 'text-red-500' : 'text-gray-500' }} mb-0.5">{{ __('app.profile.country') }} {{ empty($user->country) ? '· ' . __('app.common.missing') : '' }}</dt>
                                    <dd class="text-sm font-semibold {{ empty($user->country) ? 'text-red-400 italic' : 'text-gray-900' }}">{{ $user->country ?: __('app.common.not_specified') }}</dd>
                                </div>
                                <div class="p-3 rounded-xl {{ empty($user->city) ? 'bg-red-50 border border-red-100' : 'bg-gray-50' }}">
                                    <dt class="text-xs font-medium {{ empty($user->city) ? 'text-red-500' : 'text-gray-500' }} mb-0.5">{{ __('app.profile.city') }} {{ empty($user->city) ? '· ' . __('app.common.missing') : '' }}</dt>
                                    <dd class="text-sm font-semibold {{ empty($user->city) ? 'text-red-400 italic' : 'text-gray-900' }}">{{ $user->city ?: __('app.common.not_specified') }}</dd>
                                </div>
                                <div class="p-3 bg-gray-50 rounded-xl">
                                    <dt class="text-xs font-medium text-gray-500 mb-0.5">{{ __('app.profile.dob') }}</dt>
                                    <dd class="text-sm font-semibold text-gray-900">{{ $user->date_of_birth?->format('d.m.Y') ?: __('app.common.not_specified') }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div x-show="editing" x-cloak>
                            <form method="POST" action="{{ route('profile.update') }}">
                                @csrf
                                @method('PATCH')
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.profile.name') }}</label>
                                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.profile.email') }}</label>
                                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium {{ empty($user->phone) ? 'text-red-600' : 'text-gray-700' }} mb-1">{{ __('app.profile.phone') }} {{ empty($user->phone) ? '(' . __('app.common.missing') . ')' : '' }}</label>
                                        <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+90 5xx xxx xx xx" class="w-full px-4 py-2.5 border {{ empty($user->phone) ? 'border-red-300 ring-1 ring-red-200' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium {{ empty($user->country) ? 'text-red-600' : 'text-gray-700' }} mb-1">{{ __('app.profile.country') }} {{ empty($user->country) ? '(' . __('app.common.missing') . ')' : '' }}</label>
                                        <input type="text" name="country" value="{{ old('country', $user->country) }}" placeholder="{{ __('app.profile.country_placeholder') }}" class="w-full px-4 py-2.5 border {{ empty($user->country) ? 'border-red-300 ring-1 ring-red-200' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium {{ empty($user->city) ? 'text-red-600' : 'text-gray-700' }} mb-1">{{ __('app.profile.city') }} {{ empty($user->city) ? '(' . __('app.common.missing') . ')' : '' }}</label>
                                        <input type="text" name="city" value="{{ old('city', $user->city) }}" placeholder="{{ __('app.profile.city_placeholder') }}" class="w-full px-4 py-2.5 border {{ empty($user->city) ? 'border-red-300 ring-1 ring-red-200' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.profile.dob') }}</label>
                                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                                    </div>
                                </div>
                                <div class="mt-6 flex items-center gap-3 justify-end">
                                    <button type="button" @click="editing = false" class="px-4 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition">{{ __('app.profile.cancel') }}</button>
                                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition text-sm font-medium">
                                        <i data-lucide="save" class="w-4 h-4"></i> {{ __('app.common.save') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- ===== BÖLÜM 2: MÜZİK PROFİLİ ===== --}}
                <div id="music" class="section-anchor">
                    @php
                        $allInterests = __('app.profile.interests_options');
                        $selectedInterests = old('interests', $profile->interests ?? []);
                        $levelLabels = ['beginner' => __('app.profile.level_options.beginner'), 'intermediate' => __('app.profile.level_options.intermediate'), 'advanced' => __('app.profile.level_options.advanced')];
                        $eduLabels = ['self_taught' => __('app.profile.education_options.self_taught'), 'private_lessons' => __('app.profile.education_options.private_lessons'), 'music_school' => __('app.profile.education_options.music_school'), 'professional' => __('app.profile.education_options.professional')];
                        $musicErrors = $errors->has('primary_instrument') || $errors->has('musical_level') || $errors->has('education_status') || $errors->has('bio') || $errors->has('weekly_practice_hours') || $errors->has('interests');
                    @endphp

                    <div x-data="{ editing: {{ $musicErrors ? 'true' : 'false' }} }" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                <i data-lucide="music" class="w-5 h-5 text-primary-600"></i>
                                {{ __('app.profile.music_profile') }}
                            </h2>
                            <button @click="editing = !editing"
                                    :class="editing ? 'bg-gray-100 text-gray-600' : 'bg-primary-50 text-primary-600 hover:bg-primary-100'"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition">
                                <i :data-lucide="editing ? 'x' : 'pencil'" class="w-4 h-4"></i>
                                <span x-text="editing ? '{{ __('app.profile.cancel') }}' : '{{ __('app.profile.edit') }}'"></span>
                            </button>
                        </div>

                        <div x-show="!editing">
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                                <div class="p-3 rounded-xl {{ empty($profile->primary_instrument) ? 'bg-red-50 border border-red-100' : 'bg-gray-50' }}">
                                    <dt class="text-xs font-medium {{ empty($profile->primary_instrument) ? 'text-red-500' : 'text-gray-500' }} mb-0.5">{{ __('app.profile.instrument') }} {{ empty($profile->primary_instrument) ? '· ' . __('app.common.missing') : '' }}</dt>
                                    <dd class="text-sm font-semibold {{ empty($profile->primary_instrument) ? 'text-red-400 italic' : 'text-gray-900' }}">{{ $profile->primary_instrument ?: __('app.common.not_specified') }}</dd>
                                </div>
                                <div class="p-3 rounded-xl {{ empty($profile->musical_level) ? 'bg-red-50 border border-red-100' : 'bg-gray-50' }}">
                                    <dt class="text-xs font-medium {{ empty($profile->musical_level) ? 'text-red-500' : 'text-gray-500' }} mb-0.5">{{ __('app.profile.level') }} {{ empty($profile->musical_level) ? '· ' . __('app.common.missing') : '' }}</dt>
                                    <dd class="text-sm font-semibold {{ empty($profile->musical_level) ? 'text-red-400 italic' : 'text-gray-900' }}">{{ $levelLabels[$profile->musical_level] ?? __('app.common.not_specified') }}</dd>
                                </div>
                                <div class="p-3 rounded-xl {{ empty($profile->education_status) ? 'bg-red-50 border border-red-100' : 'bg-gray-50' }}">
                                    <dt class="text-xs font-medium {{ empty($profile->education_status) ? 'text-red-500' : 'text-gray-500' }} mb-0.5">{{ __('app.profile.education') }} {{ empty($profile->education_status) ? '· ' . __('app.common.missing') : '' }}</dt>
                                    <dd class="text-sm font-semibold {{ empty($profile->education_status) ? 'text-red-400 italic' : 'text-gray-900' }}">{{ $eduLabels[$profile->education_status] ?? __('app.common.not_specified') }}</dd>
                                </div>
                                <div class="p-3 bg-gray-50 rounded-xl">
                                    <dt class="text-xs font-medium text-gray-500 mb-0.5">{{ __('app.profile.weekly_hours') }}</dt>
                                    <dd class="text-sm font-semibold text-gray-900">{{ $profile->weekly_practice_hours !== null ? $profile->weekly_practice_hours . ' ' . __('app.profile.hours_suffix') : __('app.common.not_specified') }}</dd>
                                </div>
                            </dl>
                            <div class="p-3 rounded-xl mb-6 {{ empty($profile->bio) ? 'bg-red-50 border border-red-100' : 'bg-gray-50' }}">
                                <dt class="text-xs font-medium {{ empty($profile->bio) ? 'text-red-500' : 'text-gray-500' }} mb-1">{{ __('app.profile.bio') }} {{ empty($profile->bio) ? '· Eksik' : '' }}</dt>
                                <dd class="text-sm {{ empty($profile->bio) ? 'text-red-400 italic' : 'text-gray-700' }} leading-relaxed">{{ $profile->bio ?: __('app.profile.bio_empty') }}</dd>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 mb-2">{{ __('app.profile.interests') }}</p>
                                @if(!empty($selectedInterests))
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($selectedInterests as $interest)
                                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm bg-primary-50 border border-primary-200 text-primary-700">{{ $interest }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-gray-400 italic">{{ __('app.profile.interests_empty') }}</p>
                                @endif
                            </div>
                        </div>

                        <div x-show="editing" x-cloak
                             x-data="{
                                 interests: {{ json_encode($selectedInterests) }},
                                 toggleInterest(val) {
                                     let idx = this.interests.indexOf(val);
                                     if (idx > -1) { this.interests.splice(idx, 1); } else { this.interests.push(val); }
                                 }
                             }">
                            <form method="POST" action="{{ route('profile.extended.update') }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="phone" value="{{ $user->phone }}">
                                <input type="hidden" name="country" value="{{ $user->country }}">
                                <input type="hidden" name="city" value="{{ $user->city }}">
                                <input type="hidden" name="date_of_birth" value="{{ $user->date_of_birth?->format('Y-m-d') }}">

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium {{ empty($profile->primary_instrument) ? 'text-red-600' : 'text-gray-700' }} mb-1">{{ __('app.profile.instrument') }} {{ empty($profile->primary_instrument) ? '(' . __('app.common.missing') . ')' : '' }}</label>
                                        <select name="primary_instrument" class="w-full px-4 py-2.5 border {{ empty($profile->primary_instrument) ? 'border-red-300' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                                            <option value="">{{ __('app.common.select_prompt') }}</option>
                                            @foreach(__('app.profile.instrument_options') as $inst)
                                                <option value="{{ $inst }}" {{ old('primary_instrument', $profile->primary_instrument) === $inst ? 'selected' : '' }}>{{ $inst }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium {{ empty($profile->musical_level) ? 'text-red-600' : 'text-gray-700' }} mb-1">{{ __('app.profile.level') }} {{ empty($profile->musical_level) ? '(' . __('app.common.missing') . ')' : '' }}</label>
                                        <select name="musical_level" class="w-full px-4 py-2.5 border {{ empty($profile->musical_level) ? 'border-red-300' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                                            <option value="">{{ __('app.common.select_prompt') }}</option>
                                            <option value="beginner" {{ old('musical_level', $profile->musical_level) === 'beginner' ? 'selected' : '' }}>{{ __('app.profile.level_options.beginner') }}</option>
                                            <option value="intermediate" {{ old('musical_level', $profile->musical_level) === 'intermediate' ? 'selected' : '' }}>{{ __('app.profile.level_options.intermediate') }}</option>
                                            <option value="advanced" {{ old('musical_level', $profile->musical_level) === 'advanced' ? 'selected' : '' }}>{{ __('app.profile.level_options.advanced') }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium {{ empty($profile->education_status) ? 'text-red-600' : 'text-gray-700' }} mb-1">{{ __('app.profile.education') }} {{ empty($profile->education_status) ? '(' . __('app.common.missing') . ')' : '' }}</label>
                                        <select name="education_status" class="w-full px-4 py-2.5 border {{ empty($profile->education_status) ? 'border-red-300' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                                            <option value="">{{ __('app.common.select_prompt') }}</option>
                                            <option value="self_taught" {{ old('education_status', $profile->education_status) === 'self_taught' ? 'selected' : '' }}>{{ __('app.profile.education_options.self_taught') }}</option>
                                            <option value="private_lessons" {{ old('education_status', $profile->education_status) === 'private_lessons' ? 'selected' : '' }}>{{ __('app.profile.education_options.private_lessons') }}</option>
                                            <option value="music_school" {{ old('education_status', $profile->education_status) === 'music_school' ? 'selected' : '' }}>{{ __('app.profile.education_options.music_school') }}</option>
                                            <option value="professional" {{ old('education_status', $profile->education_status) === 'professional' ? 'selected' : '' }}>{{ __('app.profile.education_options.professional') }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.profile.weekly_hours') }}</label>
                                        <input type="number" name="weekly_practice_hours" value="{{ old('weekly_practice_hours', $profile->weekly_practice_hours) }}" min="0" max="168" placeholder="Örneğin: 10" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                                    </div>
                                </div>
                                <div class="mt-6">
                                    <label class="block text-sm font-medium {{ empty($profile->bio) ? 'text-red-600' : 'text-gray-700' }} mb-1">{{ __('app.profile.bio') }} {{ empty($profile->bio) ? '(' . __('app.common.missing') . ')' : '' }}</label>
                                    <textarea name="bio" rows="4" maxlength="1000" placeholder="{{ __('app.profile.bio_placeholder') }}" class="w-full px-4 py-2.5 border {{ empty($profile->bio) ? 'border-red-300' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">{{ old('bio', $profile->bio) }}</textarea>
                                </div>
                                <div class="mt-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.profile.interests') }}</label>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($allInterests as $interest)
                                            <label @click.prevent="toggleInterest('{{ addslashes($interest) }}')"
                                                   :class="interests.includes('{{ addslashes($interest) }}') ? 'bg-primary-50 border-primary-300 text-primary-700' : 'bg-white border-gray-200 text-gray-600 hover:border-primary-200'"
                                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border cursor-pointer text-sm transition select-none">
                                                <input type="checkbox" name="interests[]" value="{{ $interest }}"
                                                       :checked="interests.includes('{{ addslashes($interest) }}')" class="hidden">
                                                {{ $interest }}
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="mt-6 flex items-center gap-3 justify-end">
                                    <button type="button" @click="editing = false" class="px-4 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition">{{ __('app.profile.cancel') }}</button>
                                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition text-sm font-medium">
                                        <i data-lucide="save" class="w-4 h-4"></i> {{ __('app.common.save') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- ===== BÖLÜM 3: PROGRESS ===== --}}
                <div id="progress" class="section-anchor space-y-4">

                    {{-- Practice Breakdown --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                <i data-lucide="layers" class="w-5 h-5 text-primary-600"></i>
                                {{ __('app.profile.practice_breakdown') }}
                            </h2>
                            <a href="{{ route('progress') }}" class="inline-flex items-center gap-1.5 text-sm text-primary-600 hover:text-primary-700 font-medium transition">
                                {{ __('app.profile.view_all') }} <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                        </div>

                        @php
                            $activeBreakdown = collect($practiceBreakdown)->filter(fn($p) => $p['sessions'] > 0)->values();
                            $breakdownColors = [
                                0 => ['bg' => 'bg-primary-100', 'text' => 'text-primary-600', 'bar' => 'from-primary-600 to-primary-400'],
                                1 => ['bg' => 'bg-orange-100',  'text' => 'text-orange-600',  'bar' => 'from-orange-500 to-amber-400'],
                                2 => ['bg' => 'bg-blue-100',    'text' => 'text-blue-600',    'bar' => 'from-blue-500 to-blue-400'],
                            ];
                        @endphp

                        @if($activeBreakdown->count() > 0)
                            <div class="space-y-4">
                                @foreach($activeBreakdown->take(3) as $idx => $practice)
                                    @php $bc = $breakdownColors[$idx % 3]; @endphp
                                    <div class="p-4 bg-gray-50 rounded-xl">
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-lg {{ $bc['bg'] }} flex items-center justify-center flex-shrink-0">
                                                    <i data-lucide="music" class="w-4 h-4 {{ $bc['text'] }}"></i>
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-gray-900 text-sm">{{ $practice['name'] }}</p>
                                                    <p class="text-xs text-gray-500">{{ $practice['sessions'] }} {{ __('app.profile.sessions') }}</p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-xl font-bold {{ $bc['text'] }}">{{ $practice['accuracy'] }}%</div>
                                                <p class="text-xs text-gray-400">{{ __('app.profile.overall_accuracy') }}</p>
                                            </div>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-1.5 overflow-hidden">
                                            <div class="progress-bar-fill h-1.5 rounded-full bg-gradient-to-r {{ $bc['bar'] }}"
                                                 style="width: {{ $practice['accuracy'] }}%"></div>
                                        </div>
                                        <div class="mt-2 flex items-center gap-4 text-xs text-gray-500">
                                            <span>{{ $practice['total_questions'] }} {{ __('app.profile.total_questions') }}</span>
                                            <span class="text-green-600">{{ $practice['correct_answers'] }} {{ __('app.profile.total_correct') }}</span>
                                            <span>{{ __('app.common.avg') }} {{ $practice['avg_time'] }}s</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-10">
                                <i data-lucide="bar-chart-2" class="w-10 h-10 mx-auto text-gray-300 mb-3"></i>
                                <p class="text-sm text-gray-500 mb-4">{{ __('app.profile.no_sessions') }}</p>
                                <a href="{{ route('learn') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 transition">
                                    <i data-lucide="play" class="w-4 h-4"></i> {{ __('app.profile.practice_start') }}
                                </a>
                            </div>
                        @endif
                    </div>

                    {{-- Son oturumlar --}}
                    @if($recentActivity->count() > 0)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h2 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <i data-lucide="clock" class="w-5 h-5 text-primary-600"></i>
                            {{ __('app.profile.recent_sessions') }}
                        </h2>
                        <div class="overflow-x-auto -mx-2">
                            <table class="w-full text-sm min-w-[400px]">
                                <thead>
                                    <tr class="text-xs text-gray-400 border-b border-gray-100">
                                        <th class="pb-2 text-left pl-2">{{ __('app.profile.date_col') }}</th>
                                        <th class="pb-2 text-left">{{ __('app.profile.type_col') }}</th>
                                        <th class="pb-2 text-center">{{ __('app.profile.question_col') }}</th>
                                        <th class="pb-2 text-center">{{ __('app.profile.overall_accuracy') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @foreach($recentActivity as $activity)
                                        @php
                                            $acc = $activity->total_questions > 0
                                                ? round(($activity->correct_answers / $activity->total_questions) * 100) : 0;
                                            $accColor = $acc >= 80 ? 'text-green-600' : ($acc >= 60 ? 'text-amber-600' : 'text-red-500');
                                        @endphp
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="py-2.5 pl-2 text-gray-400 text-xs">{{ \Carbon\Carbon::parse($activity->created_at)->format('d.m.y') }}</td>
                                            <td class="py-2.5">
                                                <span class="px-2 py-0.5 bg-primary-50 text-primary-700 rounded-full text-xs font-medium">{{ $activity->practice?->name ?? '—' }}</span>
                                            </td>
                                            <td class="py-2.5 text-center font-medium text-gray-700">{{ $activity->total_questions }}</td>
                                            <td class="py-2.5 text-center font-bold {{ $accColor }}">{{ $acc }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-100 text-center">
                            <a href="{{ route('progress') }}" class="inline-flex items-center gap-2 text-sm text-primary-600 hover:text-primary-700 font-medium transition">
                                <i data-lucide="external-link" class="w-4 h-4"></i> {{ __('app.profile.all_stats_link') }}
                            </a>
                        </div>
                    </div>
                    @endif

                </div>

            </div>
            {{-- ============ END MAIN FLOW ============ --}}

            {{-- ============ AI COACH PANELİ ============ --}}
            <div x-show="mode === 'questionnaire'" x-cloak class="space-y-4">
                <div class="flex items-center gap-3 mb-2">
                    <button @click="switchMode('main')"
                            class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 transition font-medium">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i> {{ __('app.common.back') }}
                    </button>
                    <span class="text-gray-300">/</span>
                    <span class="text-sm font-semibold text-gray-700">{{ __('app.profile.questionnaire') }}</span>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-base font-bold text-gray-900 mb-1 flex items-center gap-2">
                        <i data-lucide="clipboard-list" class="w-5 h-5 text-primary-600"></i>
                        {{ __('app.profile.questionnaire') }}
                    </h2>
                    <p class="text-sm text-gray-500 mb-6">{{ __('app.profile.questionnaire_desc') }}</p>

                    @if($questions->count() > 0)
                        <form method="POST" action="{{ route('profile.questionnaire.store') }}">
                            @csrf
                            <div class="space-y-5">
                                @foreach($questions as $question)
                                    <div class="p-4 rounded-xl bg-gray-50 border border-gray-100">
                                        <label class="block text-sm font-medium text-gray-800 mb-2">
                                            {{ $question->getLocalizedText() }}
                                            @if($question->is_required)<span class="text-red-500">*</span>@endif
                                        </label>
                                        @if($question->question_type === 'text')
                                            <input type="text" name="answers[{{ $question->id }}]" value="{{ $responses[$question->id] ?? '' }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm bg-white">
                                        @elseif($question->question_type === 'number')
                                            <input type="number" name="answers[{{ $question->id }}]" value="{{ $responses[$question->id] ?? '' }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm bg-white">
                                        @elseif($question->question_type === 'single_choice')
                                            @php $localizedOpts = $question->getLocalizedOptions(); @endphp
                                            <div class="space-y-2">
                                                @foreach($question->options as $idx => $optionKey)
                                                    <label class="flex items-center gap-3 p-2.5 rounded-lg hover:bg-white cursor-pointer transition">
                                                        <input type="radio" name="answers[{{ $question->id }}]" value="{{ $optionKey }}" {{ ($responses[$question->id] ?? '') === $optionKey ? 'checked' : '' }} class="text-primary-600 focus:ring-primary-500">
                                                        <span class="text-sm text-gray-700">{{ $localizedOpts[$idx] ?? $optionKey }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @elseif($question->question_type === 'multi_choice')
                                            @php
                                                $selectedAnswers = isset($responses[$question->id]) ? json_decode($responses[$question->id], true) ?? [] : [];
                                                $localizedOpts = $question->getLocalizedOptions();
                                            @endphp
                                            <div class="space-y-2">
                                                @foreach($question->options as $idx => $optionKey)
                                                    <label class="flex items-center gap-3 p-2.5 rounded-lg hover:bg-white cursor-pointer transition">
                                                        <input type="checkbox" name="answers[{{ $question->id }}][]" value="{{ $optionKey }}" {{ in_array($optionKey, $selectedAnswers) ? 'checked' : '' }} class="text-primary-600 rounded focus:ring-primary-500">
                                                        <span class="text-sm text-gray-700">{{ $localizedOpts[$idx] ?? $optionKey }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @elseif($question->question_type === 'scale')
                                            <div class="flex items-center gap-3 mt-1 flex-wrap">
                                                @for($i = 1; $i <= 10; $i++)
                                                    <label class="flex flex-col items-center cursor-pointer">
                                                        <input type="radio" name="answers[{{ $question->id }}]" value="{{ $i }}" {{ ($responses[$question->id] ?? '') == $i ? 'checked' : '' }} class="text-primary-600 focus:ring-primary-500">
                                                        <span class="text-xs text-gray-500 mt-1">{{ $i }}</span>
                                                    </label>
                                                @endfor
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-6 flex items-center gap-3 justify-between">
                                <button type="button" @click="switchMode('main')" class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                                    <i data-lucide="arrow-left" class="w-4 h-4"></i> {{ __('app.common.back') }}
                                </button>
                                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition text-sm font-medium">
                                    <i data-lucide="save" class="w-4 h-4"></i> {{ __('app.profile.save_changes') }}
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="text-center py-12 text-gray-500">
                            <i data-lucide="clipboard" class="w-12 h-12 mx-auto text-gray-300 mb-3"></i>
                            <p>{{ __('app.profile.no_questions') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ============ AYARLAR PANELİ ============ --}}
            <div x-show="mode === 'settings'" x-cloak class="space-y-4">
                <div class="flex items-center gap-3 mb-2">
                    <button @click="switchMode('main')"
                            class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 transition font-medium">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i> {{ __('app.common.back') }}
                    </button>
                    <span class="text-gray-300">/</span>
                    <span class="text-sm font-semibold text-gray-700">{{ __('app.profile.settings') }}</span>
                </div>

                {{-- Dil Ayarı --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i data-lucide="globe" class="w-5 h-5 text-primary-600"></i>
                        {{ __('app.profile.language') }}
                    </h2>
                    <p class="text-sm text-gray-500 mb-4">{{ __('app.profile.change_language') }}</p>
                    <form method="POST" action="{{ route('language.switch') }}">
                        @csrf
                        <div class="flex items-center gap-3">
                            <select name="locale" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                                @php
                                    $languages = [
                                        'en' => ['name' => 'English', 'flag' => '🇬🇧'],
                                        'es' => ['name' => 'Español', 'flag' => '🇪🇸'],
                                        'de' => ['name' => 'Deutsch', 'flag' => '🇩🇪'],
                                        'fr' => ['name' => 'Français', 'flag' => '🇫🇷'],
                                        'pt' => ['name' => 'Português', 'flag' => '🇧🇷'],
                                        'tr' => ['name' => 'Türkçe', 'flag' => '🇹🇷'],
                                        'it' => ['name' => 'Italiano', 'flag' => '🇮🇹'],
                                    ];
                                @endphp
                                @foreach($languages as $code => $info)
                                    <option value="{{ $code }}" {{ app()->getLocale() === $code ? 'selected' : '' }}>
                                        {{ $info['flag'] }} {{ $info['name'] }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition text-sm font-medium">
                                <i data-lucide="check" class="w-4 h-4"></i>
                                {{ __('app.common.save') }}
                            </button>
                        </div>
                    </form>
                    @if(session('locale_changed'))
                        <p class="text-sm text-green-600 mt-3 flex items-center gap-1.5">
                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                            {{ __('app.profile.language_saved') }}
                        </p>
                    @endif
                </div>

                {{-- Profil Fotoğrafı --}}
                <div x-data="{ editingAvatar: false }" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                            <i data-lucide="camera" class="w-5 h-5 text-primary-600"></i>
                            {{ __('app.profile.avatar') }}
                        </h2>
                        <button @click="editingAvatar = !editingAvatar"
                                :class="editingAvatar ? 'bg-gray-100 text-gray-600' : 'bg-primary-50 text-primary-600 hover:bg-primary-100'"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition">
                            <i :data-lucide="editingAvatar ? 'x' : 'pencil'" class="w-4 h-4"></i>
                            <span x-text="editingAvatar ? '{{ __('app.profile.cancel') }}' : '{{ __('app.profile.change_password') }}'"></span>
                        </button>
                    </div>
                    <div x-show="!editingAvatar" class="flex items-center gap-5">
                        @if($user->hasAvatar())
                            <img src="{{ $user->avatar }}" alt="" class="w-20 h-20 rounded-full object-cover border-4 border-primary-100 shadow-sm">
                        @else
                            <div class="w-20 h-20 rounded-full bg-gradient-to-br from-primary-500 to-pink-500 flex items-center justify-center text-white text-2xl font-bold shadow-sm">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                        @endif
                        <div>
                            <p class="font-semibold text-gray-900">{{ $user->name }}</p>
                            <p class="text-sm text-gray-500">{{ $user->email }}</p>
                            @if(!$user->hasAvatar())
                                <span class="inline-flex items-center gap-1 text-xs text-red-500 mt-1">
                                    <i data-lucide="alert-circle" class="w-3 h-3"></i> {{ __('app.profile.photo_not_added') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div x-show="editingAvatar" x-cloak>
                        <form method="POST" action="{{ route('profile.avatar.update') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="flex items-center gap-6">
                                @if($user->hasAvatar())
                                    <img src="{{ $user->avatar }}" alt="" class="w-20 h-20 rounded-full object-cover border-4 border-primary-100">
                                @else
                                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-primary-500 to-pink-500 flex items-center justify-center text-white text-2xl font-bold">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <label for="avatar-input" class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition text-sm font-medium">
                                        <i data-lucide="upload" class="w-4 h-4"></i> {{ __('app.profile.avatar') }}
                                    </label>
                                    <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" id="avatar-input" class="hidden" onchange="this.form.submit()">
                                    <p class="text-xs text-gray-500 mt-2">{{ __('app.profile.avatar_format_hint') }}</p>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Şifre --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-base font-bold text-gray-900 mb-6 flex items-center gap-2">
                        <i data-lucide="lock" class="w-5 h-5 text-primary-600"></i>
                        {{ __('app.profile.change_password') }}
                    </h2>
                    @if($user->hasPassword())
                        <form method="POST" action="{{ route('password.update') }}">
                            @csrf
                            @method('PUT')
                            <div class="space-y-4 max-w-lg">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.profile.current_password') }}</label>
                                    <input type="password" name="current_password" required autocomplete="current-password" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.profile.new_password') }}</label>
                                    <input type="password" name="password" required autocomplete="new-password" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.profile.confirm_new_password') }}</label>
                                    <input type="password" name="password_confirmation" required autocomplete="new-password" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                                </div>
                            </div>
                            <div class="mt-6">
                                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition text-sm font-medium">
                                    <i data-lucide="save" class="w-4 h-4"></i> {{ __('app.profile.update_password') }}
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="p-4 rounded-xl bg-blue-50 border border-blue-200">
                            <p class="text-sm text-blue-700 flex items-center gap-2">
                                <i data-lucide="info" class="w-4 h-4 flex-shrink-0"></i>
                                {{ __('app.profile.google_no_password') }}
                            </p>
                        </div>
                    @endif
                </div>

                {{-- Hesabı Dondur / Aktifleştir --}}
                <div class="bg-white rounded-2xl shadow-sm border border-amber-100 p-6"
                     x-data="{ showModal: false }">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-base font-bold text-gray-900 mb-1 flex items-center gap-2">
                                <i data-lucide="pause-circle" class="w-5 h-5 text-amber-500"></i>
                                {{ $user->isSuspended() ? __('app.profile.account_reactivated') : __('app.profile.account_suspended') }}
                            </h2>
                            @if($user->isSuspended())
                                <p class="text-sm text-amber-600 font-medium mb-1">{{ __('app.profile.account_suspended_desc') }}</p>
                                <p class="text-sm text-gray-500">{{ __('app.profile.reactivate_desc') }}</p>
                            @else
                                <p class="text-sm text-gray-500">{{ __('app.profile.suspend_desc') }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="mt-5">
                        <button @click="showModal = true"
                                class="inline-flex items-center gap-2 px-4 py-2 {{ $user->isSuspended() ? 'bg-green-600 hover:bg-green-700' : 'bg-amber-500 hover:bg-amber-600' }} text-white rounded-lg transition text-sm font-medium">
                            <i data-lucide="{{ $user->isSuspended() ? 'play-circle' : 'pause-circle' }}" class="w-4 h-4"></i>
                            {{ $user->isSuspended() ? __('app.profile.account_reactivated') : __('app.profile.account_suspended') }}
                        </button>
                    </div>

                    <div x-show="showModal" x-cloak
                         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
                         @click.self="showModal = false">
                        <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md mx-4" @click.stop>
                            @if($user->isSuspended())
                                <h3 class="text-lg font-bold text-gray-900 mb-2">{{ __('app.profile.reactivate_confirm_title') }}</h3>
                                <p class="text-sm text-gray-600 mb-6">{{ __('app.profile.reactivate_confirm_desc') }}</p>
                            @else
                                <h3 class="text-lg font-bold text-gray-900 mb-2">{{ __('app.profile.suspend_confirm_title') }}</h3>
                                <p class="text-sm text-gray-600 mb-6">{{ __('app.profile.suspend_confirm_desc') }}</p>
                            @endif
                            <form method="POST" action="{{ route('profile.suspend') }}">
                                @csrf
                                <div class="flex justify-end gap-3">
                                    <button type="button" @click="showModal = false"
                                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                                        {{ __('app.common.cancel') }}
                                    </button>
                                    <button type="submit"
                                            class="px-4 py-2 text-sm font-medium text-white {{ $user->isSuspended() ? 'bg-green-600 hover:bg-green-700' : 'bg-amber-500 hover:bg-amber-600' }} rounded-lg transition">
                                        {{ $user->isSuspended() ? __('app.profile.confirm_yes_activate') : __('app.profile.confirm_yes_suspend') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
            {{-- ============ END AYARLAR ============ --}}

        </div>
    </div>
</div>

<script>
    lucide.createIcons();

    // IntersectionObserver for active section in main flow
    const sections = ['general', 'music', 'progress'];

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const comp = document.querySelector('[x-data]')?._x_dataStack?.[0];
                if (comp && comp.mode === 'main') {
                    comp.activeSection = entry.target.id;
                }
            }
        });
    }, { rootMargin: '-20% 0px -65% 0px', threshold: 0 });

    sections.forEach(id => {
        const el = document.getElementById(id);
        if (el) observer.observe(el);
    });
</script>

@include('partials.footer')
</body>
</html>
