<x-guest-layout>

<div class="form-card rounded-2xl p-8">

    <!-- Header -->
    <div class="mb-7">
        <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ __('app.auth.login_title') }}</h1>
        <p class="text-sm text-gray-500">{{ __('app.auth.login_subtitle') }}</p>
    </div>

    @if (session('status'))
        <div class="mb-4 p-3 rounded-xl text-sm font-medium bg-green-50 border border-green-200 text-green-700">
            {{ session('status') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 p-3 rounded-xl text-sm font-medium bg-red-50 border border-red-200 text-red-700">
            {{ session('error') }}
        </div>
    @endif

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

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email -->
        <div class="mb-4">
            <label class="input-label" for="email">{{ __('app.auth.email') }}</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="input-field" placeholder="{{ __('app.auth.email_placeholder') }}" required autofocus autocomplete="email">
            @error('email')
                <p class="error-text">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-4">
            <div class="flex items-center justify-between mb-1.5">
                <label class="input-label mb-0" for="password">{{ __('app.auth.password') }}</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                       class="text-xs font-medium text-primary-600 hover:text-primary-700 transition-colors">
                        {{ __('app.auth.forgot_password') }}
                    </a>
                @endif
            </div>
            <input id="password" type="password" name="password"
                   class="input-field" placeholder="••••••••" required autocomplete="current-password">
            @error('password')
                <p class="error-text">{{ $message }}</p>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="flex items-center gap-2 mb-6">
            <input id="remember_me" type="checkbox" name="remember"
                   class="w-4 h-4 rounded border-gray-300 text-primary-600"
                   style="accent-color: #9333ea;">
            <label for="remember_me" class="text-sm text-gray-500 cursor-pointer select-none">
                {{ __('app.auth.remember_me') }}
            </label>
        </div>

        <!-- Submit -->
        <button type="submit"
                class="btn-primary w-full py-3 rounded-xl text-white font-semibold text-sm flex items-center justify-center gap-2">
            <i data-lucide="log-in" class="w-4 h-4"></i>
            {{ __('app.auth.login') }}
        </button>

    </form>

    <!-- Register link -->
    <p class="text-center text-sm text-gray-500 mt-5">
        {{ __('app.auth.no_account') }}
        <a href="{{ route('register') }}" class="font-semibold text-primary-600 hover:text-primary-700 transition-colors ml-1">
            {{ __('app.auth.register') }}
        </a>
    </p>

</div>

</x-guest-layout>
