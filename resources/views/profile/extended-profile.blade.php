<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('app.profile.music_profile') }} - {{ config('app.name', 'Harmoniva') }}</title>
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

@include('partials.navbar', ['active' => 'profile'])

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    @if (session('status') === 'profile-updated')
        <div class="mb-4 p-4 bg-green-100 border border-green-300 rounded-lg text-green-800 text-sm">
            {{ __('app.profile.updated') }}
        </div>
    @endif

    <form method="POST" action="{{ route('profile.extended.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Personal Info -->
        <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg space-y-4">
            <h3 class="text-lg font-medium text-gray-900">{{ __('app.profile.personal_info') }}</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">{{ __('app.profile.phone') }}</label>
                    <input id="phone" name="phone" type="text" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" value="{{ old('phone', $user->phone) }}">
                    @error('phone') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="date_of_birth" class="block text-sm font-medium text-gray-700">{{ __('app.profile.dob') }}</label>
                    <input id="date_of_birth" name="date_of_birth" type="date" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}">
                    @error('date_of_birth') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="country" class="block text-sm font-medium text-gray-700">{{ __('app.profile.country') }}</label>
                    <input id="country" name="country" type="text" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" value="{{ old('country', $user->country) }}">
                    @error('country') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="city" class="block text-sm font-medium text-gray-700">{{ __('app.profile.city') }}</label>
                    <input id="city" name="city" type="text" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" value="{{ old('city', $user->city) }}">
                    @error('city') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- Musical Background -->
        <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg space-y-4">
            <h3 class="text-lg font-medium text-gray-900">{{ __('app.profile.music_history') }}</h3>

            <div>
                <label for="primary_instrument" class="block text-sm font-medium text-gray-700">{{ __('app.profile.instrument') }}</label>
                <input id="primary_instrument" name="primary_instrument" type="text" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="{{ __('app.profile.instrument_placeholder') }}" value="{{ old('primary_instrument', $profile->primary_instrument) }}">
                @error('primary_instrument') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">{{ __('app.profile.level') }}</label>
                <div class="mt-2 flex flex-wrap gap-3">
                    @foreach ([
                        'beginner'     => __('app.profile.level_options.beginner'),
                        'intermediate' => __('app.profile.level_options.intermediate'),
                        'advanced'     => __('app.profile.level_options.advanced'),
                    ] as $value => $label)
                        <label class="inline-flex items-center">
                            <input type="radio" name="musical_level" value="{{ $value }}" class="text-indigo-600 focus:ring-indigo-500"
                                {{ old('musical_level', $profile->musical_level) === $value ? 'checked' : '' }}>
                            <span class="ms-2 text-sm text-gray-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('musical_level') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">{{ __('app.profile.education') }}</label>
                <div class="mt-2 flex flex-wrap gap-3">
                    @foreach ([
                        'self_taught'     => __('app.profile.education_options.self_taught'),
                        'private_lessons' => __('app.profile.education_options.private_lessons'),
                        'music_school'    => __('app.profile.education_options.music_school'),
                        'professional'    => __('app.profile.education_options.professional'),
                    ] as $value => $label)
                        <label class="inline-flex items-center">
                            <input type="radio" name="education_status" value="{{ $value }}" class="text-indigo-600 focus:ring-indigo-500"
                                {{ old('education_status', $profile->education_status) === $value ? 'checked' : '' }}>
                            <span class="ms-2 text-sm text-gray-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('education_status') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="weekly_practice_hours" class="block text-sm font-medium text-gray-700">{{ __('app.profile.weekly_hours') }}</label>
                <input id="weekly_practice_hours" name="weekly_practice_hours" type="number" min="0" max="168" class="mt-1 block w-full sm:w-32 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" value="{{ old('weekly_practice_hours', $profile->weekly_practice_hours) }}">
                @error('weekly_practice_hours') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <!-- Interests -->
        <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg space-y-4">
            <h3 class="text-lg font-medium text-gray-900">{{ __('app.profile.interests') }}</h3>

            @php
                $interestOptions = ['Ear Training', 'Piano', 'Chords', 'Intervals', 'Rhythm', 'Sight-reading', 'Melodic Dictation', 'Music Theory'];
                $currentInterests = old('interests', $profile->interests ?? []);
            @endphp

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach ($interestOptions as $interest)
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="interests[]" value="{{ $interest }}" class="rounded text-indigo-600 focus:ring-indigo-500"
                            {{ in_array($interest, $currentInterests) ? 'checked' : '' }}>
                        <span class="ms-2 text-sm text-gray-700">{{ $interest }}</span>
                    </label>
                @endforeach
            </div>
            @error('interests') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <!-- Bio -->
        <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg space-y-4">
            <h3 class="text-lg font-medium text-gray-900">{{ __('app.profile.bio') }}</h3>

            <div>
                <textarea id="bio" name="bio" rows="4" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('bio', $profile->bio) }}</textarea>
                @error('bio') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg transition-colors">
                {{ __('app.common.save') }}
            </button>
            <a href="{{ route('profile.edit') }}" class="text-sm text-gray-600 hover:text-gray-900">{{ __('app.common.back') }}</a>
        </div>
    </form>

</div>

</body>
</html>
