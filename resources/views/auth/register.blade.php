<x-guest-layout>

<div class="form-card rounded-2xl p-8">

    <!-- Header -->
    <div class="mb-7">
        <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ __('app.register.title') }}</h1>
        <p class="text-sm text-gray-500">{{ __('app.register.subtitle') }}</p>
    </div>

    <!-- Google OAuth -->
    <a href="{{ route('auth.google') }}"
       class="google-btn flex items-center justify-center gap-3 w-full px-4 py-2.5 text-sm font-medium text-gray-700 hover:text-gray-900 mb-5">
        <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24">
            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/>
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
        </svg>
        {{ __('app.auth.login_with_google') }}
    </a>

    <!-- Divider -->
    <div class="relative mb-6">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-200"></div>
        </div>
        <div class="relative flex justify-center">
            <span class="px-3 bg-white text-xs text-gray-400 uppercase tracking-widest">{{ __('app.common.or') }}</span>
        </div>
    </div>

    <form method="POST" action="{{ route('register') }}" x-data="{ role: '{{ old('role', 'user') }}' }">
        @csrf

        <!-- Account Type -->
        <div class="mb-5">
            <label class="input-label">{{ __('app.register.account_type') }}</label>
            <div class="grid grid-cols-3 gap-2.5">

                <label @click="role = 'user'"
                       :class="role === 'user' ? 'role-card active' : 'role-card'"
                       class="flex flex-col items-center gap-2 py-3.5 px-2">
                    <input type="radio" name="role" value="user" class="sr-only" :checked="role === 'user'">
                    <div :class="role === 'user' ? 'text-primary-600' : 'text-gray-400'" class="transition-colors">
                        <i data-lucide="graduation-cap" class="w-5 h-5"></i>
                    </div>
                    <span :class="role === 'user' ? 'text-primary-700 font-semibold' : 'text-gray-500 font-medium'"
                          class="text-xs transition-colors">{{ __('app.register.student') }}</span>
                </label>

                <label @click="role = 'teacher'"
                       :class="role === 'teacher' ? 'role-card active' : 'role-card'"
                       class="flex flex-col items-center gap-2 py-3.5 px-2">
                    <input type="radio" name="role" value="teacher" class="sr-only" :checked="role === 'teacher'">
                    <div :class="role === 'teacher' ? 'text-primary-600' : 'text-gray-400'" class="transition-colors">
                        <i data-lucide="user-check" class="w-5 h-5"></i>
                    </div>
                    <span :class="role === 'teacher' ? 'text-primary-700 font-semibold' : 'text-gray-500 font-medium'"
                          class="text-xs transition-colors">{{ __('app.register.teacher') }}</span>
                </label>

                <label @click="role = 'school'"
                       :class="role === 'school' ? 'role-card active' : 'role-card'"
                       class="flex flex-col items-center gap-2 py-3.5 px-2">
                    <input type="radio" name="role" value="school" class="sr-only" :checked="role === 'school'">
                    <div :class="role === 'school' ? 'text-primary-600' : 'text-gray-400'" class="transition-colors">
                        <i data-lucide="building-2" class="w-5 h-5"></i>
                    </div>
                    <span :class="role === 'school' ? 'text-primary-700 font-semibold' : 'text-gray-500 font-medium'"
                          class="text-xs transition-colors">{{ __('app.register.music_school') }}</span>
                </label>

            </div>
            @error('role')
                <p class="error-text">{{ $message }}</p>
            @enderror
        </div>

        <!-- Name + Surname -->
        <div class="grid grid-cols-2 gap-3 mb-4">
            <div>
                <label class="input-label" for="name">{{ __('app.register.first_name') }}</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}"
                       class="input-field" placeholder="{{ __('app.register.first_name_ph') }}" required autofocus autocomplete="given-name">
                @error('name')
                    <p class="error-text">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="input-label" for="surname">{{ __('app.register.last_name') }}</label>
                <input id="surname" type="text" name="surname" value="{{ old('surname') }}"
                       class="input-field" placeholder="{{ __('app.register.last_name_ph') }}" required autocomplete="family-name">
                @error('surname')
                    <p class="error-text">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Email -->
        <div class="mb-4">
            <label class="input-label" for="email">{{ __('app.auth.email') }}</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="input-field" placeholder="{{ __('app.register.email_ph') }}" required autocomplete="email">
            @error('email')
                <p class="error-text">{{ $message }}</p>
            @enderror
        </div>

        <!-- Country -->
        <div class="mb-4">
            <label class="input-label" for="country">{{ __('app.register.country') }}</label>
            <select id="country" name="country" class="input-field">
                <option value="">{{ __('app.register.select_country') }}</option>
                @php
                    $countries = config('countries', []);
                    $detectedCountry = session('detected_country', '');
                @endphp
                @foreach($countries as $code => $name)
                    <option value="{{ $code }}" {{ old('country', $detectedCountry) === $code ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
            @error('country')
                <p class="error-text">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password + Confirm -->
        <div class="grid grid-cols-2 gap-3 mb-6">
            <div>
                <label class="input-label" for="password">{{ __('app.auth.password') }}</label>
                <input id="password" type="password" name="password"
                       class="input-field" placeholder="••••••••" required autocomplete="new-password">
                @error('password')
                    <p class="error-text">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="input-label" for="password_confirmation">{{ __('app.auth.confirm_password') }}</label>
                <input id="password_confirmation" type="password" name="password_confirmation"
                       class="input-field" placeholder="••••••••" required autocomplete="new-password">
            </div>
        </div>

        <!-- Submit -->
        <button type="submit"
                class="btn-primary w-full py-3 rounded-xl text-white font-semibold text-sm flex items-center justify-center gap-2">
            <i data-lucide="user-plus" class="w-4 h-4"></i>
            {{ __('app.register.create_account') }}
        </button>

    </form>

    <!-- Login link -->
    <p class="text-center text-sm text-gray-500 mt-5">
        {{ __('app.register.already_account') }}
        <a href="{{ route('login') }}" class="font-semibold text-primary-600 hover:text-primary-700 transition-colors ml-1">
            {{ __('app.register.login') }}
        </a>
    </p>

</div>

</x-guest-layout>
