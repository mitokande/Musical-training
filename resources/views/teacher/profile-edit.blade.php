<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('app.teacher.profile_page_title') }} - {{ config('app.name', 'Harmoniva') }}</title>
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
</head>
<body class="font-sans bg-gray-50 min-h-screen">

@include('partials.navbar', ['active' => 'teacher'])

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ __('app.teacher.profile_page_title') }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ __('app.teacher.profile_subtitle') }}</p>
        </div>
        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-primary-600 transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            {{ __('app.teacher.back_to_dashboard') }}
        </a>
    </div>

    @if(session('status') === 'profile-updated')
        <div class="mb-6 px-4 py-3 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3">
            <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
            <p class="text-sm text-green-700">{{ __('app.teacher.profile_updated') }}</p>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 rounded-xl">
            <ul class="list-disc list-inside text-sm text-red-600">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('teacher.profile.update') }}">
        @csrf
        @method('PUT')

        {{-- Temel Bilgiler --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <h2 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                <i data-lucide="user" class="w-5 h-5 text-primary-600"></i>
                {{ __('app.teacher.basic_info') }}
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.teacher.title_label') }}</label>
                    <input type="text" name="title" value="{{ old('title', $profile->title) }}" placeholder="{{ __('app.teacher.title_placeholder') }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.teacher.location') }}</label>
                    <input type="text" name="location" value="{{ old('location', $profile->location) }}" placeholder="{{ __('app.teacher.location_placeholder') }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                </div>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.teacher.short_bio') }}</label>
                <textarea name="short_bio" rows="2" maxlength="500" placeholder="Kisa bir tanitim..." class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">{{ old('short_bio', $profile->short_bio) }}</textarea>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.teacher.detailed_bio') }}</label>
                <textarea name="long_bio" rows="5" maxlength="5000" placeholder="Detayli biyografiniz..." class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">{{ old('long_bio', $profile->long_bio) }}</textarea>
            </div>
        </div>

        {{-- Uzmanlik Alanlari --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <h2 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                <i data-lucide="award" class="w-5 h-5 text-primary-600"></i>
                {{ __('app.teacher.expertise') }}
            </h2>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.teacher.specializations') }}</label>
                @php
                    $allSpecs = __('app.teacher.specialization_options');
                    $selectedSpecs = old('specializations', $profile->specializations ?? []);
                @endphp
                <div x-data="{ selected: {{ json_encode($selectedSpecs) }} }" class="flex flex-wrap gap-2">
                    @foreach($allSpecs as $spec)
                        <label @click.prevent="selected.includes('{{ addslashes($spec) }}') ? selected.splice(selected.indexOf('{{ addslashes($spec) }}'), 1) : selected.push('{{ addslashes($spec) }}')"
                               :class="selected.includes('{{ addslashes($spec) }}') ? 'bg-primary-50 border-primary-300 text-primary-700' : 'bg-white border-gray-200 text-gray-600 hover:border-primary-200'"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border cursor-pointer text-sm transition select-none">
                            <input type="checkbox" name="specializations[]" value="{{ $spec }}"
                                   :checked="selected.includes('{{ addslashes($spec) }}')" class="hidden">
                            {{ $spec }}
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.teacher.education_history') }}</label>
                    <textarea name="education_background" rows="2" maxlength="500" placeholder="Universite, sertifikalar..." class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">{{ old('education_background', $profile->education_background) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.teacher.experience_years') }}</label>
                    <input type="number" name="experience_years" value="{{ old('experience_years', $profile->experience_years) }}" min="0" max="80" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                </div>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('app.teacher.spoken_languages') }}</label>
                @php
                    $allLangs = __('app.teacher.language_options');
                    $selectedLangs = old('languages', $profile->languages ?? []);
                @endphp
                <div x-data="{ selected: {{ json_encode($selectedLangs) }} }" class="flex flex-wrap gap-2">
                    @foreach($allLangs as $lang)
                        <label @click.prevent="selected.includes('{{ addslashes($lang) }}') ? selected.splice(selected.indexOf('{{ addslashes($lang) }}'), 1) : selected.push('{{ addslashes($lang) }}')"
                               :class="selected.includes('{{ addslashes($lang) }}') ? 'bg-primary-50 border-primary-300 text-primary-700' : 'bg-white border-gray-200 text-gray-600 hover:border-primary-200'"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border cursor-pointer text-sm transition select-none">
                            <input type="checkbox" name="languages[]" value="{{ $lang }}"
                                   :checked="selected.includes('{{ addslashes($lang) }}')" class="hidden">
                            {{ $lang }}
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Ders Bilgileri --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <h2 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                <i data-lucide="book-open" class="w-5 h-5 text-primary-600"></i>
                {{ __('app.teacher.lesson_info') }}
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.teacher.lesson_format') }}</label>
                    <select name="lesson_format" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                        <option value="">{{ __('app.teacher.select_format') }}</option>
                        <option value="online" {{ old('lesson_format', $profile->lesson_format) === 'online' ? 'selected' : '' }}>{{ __('app.teacher.online') }}</option>
                        <option value="in_person" {{ old('lesson_format', $profile->lesson_format) === 'in_person' ? 'selected' : '' }}>{{ __('app.teacher.in_person') }}</option>
                        <option value="hybrid" {{ old('lesson_format', $profile->lesson_format) === 'hybrid' ? 'selected' : '' }}>{{ __('app.teacher.hybrid') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.teacher.hourly_rate') }}</label>
                    <input type="number" name="hourly_rate" value="{{ old('hourly_rate', $profile->hourly_rate) }}" min="0" step="0.01" placeholder="250.00" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.teacher.currency') }}</label>
                    <select name="currency" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                        <option value="TRY" {{ old('currency', $profile->currency ?? 'TRY') === 'TRY' ? 'selected' : '' }}>TRY</option>
                        <option value="USD" {{ old('currency', $profile->currency) === 'USD' ? 'selected' : '' }}>USD</option>
                        <option value="EUR" {{ old('currency', $profile->currency) === 'EUR' ? 'selected' : '' }}>EUR</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                <div class="flex items-center gap-3">
                    <input type="hidden" name="accepts_students" value="0">
                    <input type="checkbox" name="accepts_students" value="1" id="accepts_students" {{ old('accepts_students', $profile->accepts_students) ? 'checked' : '' }} class="w-4 h-4 text-primary-600 rounded focus:ring-primary-500">
                    <label for="accepts_students" class="text-sm font-medium text-gray-700">{{ __('app.teacher.accepting_students') }}</label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.teacher.max_students') }}</label>
                    <input type="number" name="max_students" value="{{ old('max_students', $profile->max_students) }}" min="0" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                </div>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.teacher.payment_link') }}</label>
                <input type="url" name="payment_link" value="{{ old('payment_link', $profile->payment_link) }}" placeholder="https://..." class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
            </div>
        </div>

        {{-- Sosyal Medya --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <h2 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                <i data-lucide="share-2" class="w-5 h-5 text-primary-600"></i>
                {{ __('app.teacher.social_media') }}
            </h2>
            @php $social = old('social_links', $profile->social_links ?? []); @endphp
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-pink-50 flex items-center justify-center flex-shrink-0"><i data-lucide="instagram" class="w-5 h-5 text-pink-600"></i></div>
                    <input type="text" name="social_links[instagram]" value="{{ $social['instagram'] ?? '' }}" placeholder="{{ __('app.teacher.instagram_ph') }}" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center flex-shrink-0"><i data-lucide="youtube" class="w-5 h-5 text-red-600"></i></div>
                    <input type="text" name="social_links[youtube]" value="{{ $social['youtube'] ?? '' }}" placeholder="{{ __('app.teacher.youtube_ph') }}" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-gray-50 flex items-center justify-center flex-shrink-0">
                        <svg viewBox="0 0 24 24" class="w-5 h-5 text-gray-700 fill-current"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.747l7.73-8.835L1.254 2.25H8.08l4.253 5.622zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    </div>
                    <input type="text" name="social_links[twitter]" value="{{ $social['twitter'] ?? '' }}" placeholder="{{ __('app.teacher.twitter_ph') }}" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0"><i data-lucide="linkedin" class="w-5 h-5 text-blue-600"></i></div>
                    <input type="text" name="social_links[linkedin]" value="{{ $social['linkedin'] ?? '' }}" placeholder="{{ __('app.teacher.linkedin_ph') }}" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0"><i data-lucide="facebook" class="w-5 h-5 text-blue-700"></i></div>
                    <input type="text" name="social_links[facebook]" value="{{ $social['facebook'] ?? '' }}" placeholder="{{ __('app.teacher.facebook_ph') }}" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-gray-900 flex items-center justify-center flex-shrink-0"><i data-lucide="music-2" class="w-5 h-5 text-white"></i></div>
                    <input type="text" name="social_links[tiktok]" value="{{ $social['tiktok'] ?? '' }}" placeholder="{{ __('app.teacher.tiktok_ph') }}" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                </div>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.teacher.website') }}</label>
                <input type="url" name="website_url" value="{{ old('website_url', $profile->website_url) }}" placeholder="https://www.example.com" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
            </div>
        </div>

        {{-- Gorunurluk --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <div class="flex items-center gap-3">
                <input type="hidden" name="public_profile" value="0">
                <input type="checkbox" name="public_profile" value="1" id="public_profile" {{ old('public_profile', $profile->public_profile) ? 'checked' : '' }} class="w-4 h-4 text-primary-600 rounded focus:ring-primary-500">
                <label for="public_profile" class="text-sm font-medium text-gray-700">{{ __('app.teacher.public_profile') }}</label>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition text-sm font-medium">
                <i data-lucide="save" class="w-4 h-4"></i>
                {{ __('app.common.save') }}
            </button>
        </div>
    </form>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
