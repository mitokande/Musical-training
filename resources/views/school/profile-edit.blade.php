<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('app.school.profile_page_title') }} - {{ config('app.name', 'Harmoniva') }}</title>
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

@include('partials.navbar', ['active' => 'school'])

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ __('app.school.profile_page_title') }}</h1>
            <p class="text-sm text-gray-500 mt-1">Okul bilgilerinizi duzenleyin.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-primary-600 transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            {{ __('app.teacher.back_to_dashboard') }}
        </a>
    </div>

    @if(session('status'))
        <div class="mb-6 px-4 py-3 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3">
            <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
            <p class="text-sm text-green-700">
                {{ session('status') === 'logo-updated' ? 'Okul logosu guncellendi.' : 'Okul profili guncellendi.' }}
            </p>
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

    {{-- Logo Upload --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
            <i data-lucide="image" class="w-5 h-5 text-primary-600"></i>
            Okul Logosu
        </h2>
        <form method="POST" action="{{ route('school.logo.update') }}" enctype="multipart/form-data">
            @csrf
            <div class="flex items-center gap-6">
                @if($school->logo_url)
                    <img src="{{ asset('storage/' . $school->logo_url) }}" alt="" class="w-20 h-20 rounded-xl object-cover border-2 border-gray-100">
                @else
                    <div class="w-20 h-20 rounded-xl bg-gray-100 flex items-center justify-center">
                        <i data-lucide="building-2" class="w-8 h-8 text-gray-400"></i>
                    </div>
                @endif
                <div>
                    <label for="logo-input" class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition text-sm font-medium">
                        <i data-lucide="upload" class="w-4 h-4"></i>
                        Logo Yukle
                    </label>
                    <input type="file" name="logo" accept="image/jpeg,image/png,image/webp" id="logo-input" class="hidden" onchange="this.form.submit()">
                    <p class="text-xs text-gray-500 mt-2">JPG, PNG veya WebP. Maks. 2MB.</p>
                </div>
            </div>
        </form>
    </div>

    <form method="POST" action="{{ route('school.profile.update') }}">
        @csrf
        @method('PUT')

        {{-- Okul Bilgileri --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <h2 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                <i data-lucide="building-2" class="w-5 h-5 text-primary-600"></i>
                Okul Bilgileri
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Okul Adi *</label>
                    <input type="text" name="name" value="{{ old('name', $school->name) }}" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kurulus Yili</label>
                    <input type="number" name="founded_year" value="{{ old('founded_year', $school->founded_year) }}" min="1900" max="{{ date('Y') }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                </div>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Kisa Aciklama</label>
                <textarea name="description" rows="2" maxlength="1000" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">{{ old('description', $school->description) }}</textarea>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Detayli Aciklama</label>
                <textarea name="long_description" rows="5" maxlength="5000" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">{{ old('long_description', $school->long_description) }}</textarea>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Ogrenci Kapasitesi</label>
                <input type="number" name="student_capacity" value="{{ old('student_capacity', $school->student_capacity) }}" min="0" class="w-full max-w-xs px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
            </div>
        </div>

        {{-- Iletisim --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <h2 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                <i data-lucide="map-pin" class="w-5 h-5 text-primary-600"></i>
                Iletisim Bilgileri
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Adres</label>
                    <textarea name="address" rows="2" maxlength="500" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">{{ old('address', $school->address) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.profile.city') }}</label>
                    <input type="text" name="city" value="{{ old('city', $school->city) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.profile.country') }}</label>
                    <input type="text" name="country" value="{{ old('country', $school->country) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.profile.phone') }}</label>
                    <input type="tel" name="phone" value="{{ old('phone', $school->phone) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.profile.email') }}</label>
                    <input type="email" name="email" value="{{ old('email', $school->email) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.teacher.website') }}</label>
                    <input type="url" name="website_url" value="{{ old('website_url', $school->website_url) }}" placeholder="https://..." class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.teacher.payment_link') }}</label>
                    <input type="url" name="payment_link" value="{{ old('payment_link', $school->payment_link) }}" placeholder="https://..." class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                </div>
            </div>
        </div>

        {{-- Sosyal Medya --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <h2 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                <i data-lucide="share-2" class="w-5 h-5 text-primary-600"></i>
                {{ __('app.teacher.social_media') }}
            </h2>
            @php $social = old('social_links', $school->social_links ?? []); @endphp
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-pink-50 flex items-center justify-center flex-shrink-0"><i data-lucide="instagram" class="w-5 h-5 text-pink-600"></i></div>
                    <input type="text" name="social_links[instagram]" value="{{ $social['instagram'] ?? '' }}" placeholder="Instagram" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center flex-shrink-0"><i data-lucide="youtube" class="w-5 h-5 text-red-600"></i></div>
                    <input type="text" name="social_links[youtube]" value="{{ $social['youtube'] ?? '' }}" placeholder="YouTube" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-gray-50 flex items-center justify-center flex-shrink-0">
                        <svg viewBox="0 0 24 24" class="w-5 h-5 text-gray-700 fill-current"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.747l7.73-8.835L1.254 2.25H8.08l4.253 5.622zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    </div>
                    <input type="text" name="social_links[twitter]" value="{{ $social['twitter'] ?? '' }}" placeholder="X (Twitter) kullanıcı adı" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0"><i data-lucide="facebook" class="w-5 h-5 text-blue-700"></i></div>
                    <input type="text" name="social_links[facebook]" value="{{ $social['facebook'] ?? '' }}" placeholder="Facebook" class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                </div>
            </div>
        </div>

        {{-- Programlar --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                <i data-lucide="graduation-cap" class="w-5 h-5 text-primary-600"></i>
                Programlar
            </h2>
            <div class="flex flex-wrap gap-2">
                @php
                    $allPrograms = ['Piyano', 'Gitar', 'Keman', 'Vokal', 'Davul', 'Bas Gitar', 'Muzik Teorisi', 'Solfej', 'Armoni', 'Kompozisyon', 'Kulak Egitimi', 'Orkestra', 'Oda Muzigi'];
                    $selectedPrograms = old('programs', $school->programs ?? []);
                @endphp
                @foreach($allPrograms as $program)
                    <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border cursor-pointer text-sm transition {{ in_array($program, $selectedPrograms) ? 'bg-primary-50 border-primary-300 text-primary-700' : 'bg-white border-gray-200 text-gray-600 hover:border-primary-200' }}">
                        <input type="checkbox" name="programs[]" value="{{ $program }}" class="hidden" {{ in_array($program, $selectedPrograms) ? 'checked' : '' }}>
                        {{ $program }}
                    </label>
                @endforeach
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
