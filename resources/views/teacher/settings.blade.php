@extends('teacher.layouts.crm')

@section('title', __('teacher.settings.title'))

@section('content')
<div class="max-w-3xl">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('teacher.settings.title') }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ __('teacher.settings.subtitle') }}</p>
    </div>

    @if(session('status') === 'avatar-updated')
        <div class="mb-6 px-4 py-3 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3">
            <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
            <p class="text-sm text-green-700">{{ __('teacher.settings.avatar_updated') }}</p>
        </div>
    @elseif(session('status') === 'password-updated')
        <div class="mb-6 px-4 py-3 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3">
            <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
            <p class="text-sm text-green-700">{{ __('teacher.settings.password_updated') }}</p>
        </div>
    @elseif(session('locale_changed'))
        <div class="mb-6 px-4 py-3 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3">
            <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
            <p class="text-sm text-green-700">{{ __('teacher.settings.language_saved') }}</p>
        </div>
    @endif

    <div class="space-y-6">
        {{-- Profile photo --}}
        <div class="card p-6">
            <h2 class="font-semibold text-gray-900 mb-1 flex items-center gap-2">
                <i data-lucide="image" class="w-4 h-4 text-primary-500"></i> {{ __('teacher.settings.photo') }}
            </h2>
            <p class="text-xs text-gray-500 mb-4">{{ __('teacher.settings.photo_hint') }}</p>
            <div class="flex items-center gap-5">
                @if($user->hasAvatar())
                    <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="w-20 h-20 rounded-full object-cover ring-2 ring-primary-100">
                @else
                    <div class="w-20 h-20 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-2xl font-bold">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
                <form method="POST" action="{{ route('profile.avatar.update') }}" enctype="multipart/form-data" class="flex flex-wrap items-center gap-3">
                    @csrf
                    <input type="file" name="avatar" accept="image/*" required class="text-sm text-gray-600">
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition">
                        {{ __('teacher.settings.upload_photo') }}
                    </button>
                </form>
            </div>
            @error('avatar')<p class="text-sm text-red-600 mt-2">{{ $message }}</p>@enderror
        </div>

        {{-- Account info --}}
        <div class="card p-6">
            <h2 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                <i data-lucide="user" class="w-4 h-4 text-primary-500"></i> {{ __('teacher.settings.account') }}
            </h2>
            <div class="text-sm space-y-1.5">
                <p class="flex items-center gap-2 text-gray-700"><i data-lucide="user" class="w-4 h-4 text-gray-300"></i> {{ $user->name }} {{ $user->surname }}</p>
                <p class="flex items-center gap-2 text-gray-700"><i data-lucide="mail" class="w-4 h-4 text-gray-300"></i> {{ $user->email }}</p>
                @if($user->username)
                    <p class="flex items-center gap-2 text-gray-700"><i data-lucide="at-sign" class="w-4 h-4 text-gray-300"></i> {{ $user->username }}</p>
                @endif
            </div>
        </div>

        {{-- Language --}}
        <div class="card p-6">
            <h2 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <i data-lucide="globe" class="w-4 h-4 text-primary-500"></i> {{ __('teacher.settings.language') }}
            </h2>
            <form method="POST" action="{{ route('language.switch') }}" class="flex items-center gap-3">
                @csrf
                <select name="locale" class="flex-1 rounded-lg border-gray-300 text-sm focus:ring-primary-500 focus:border-primary-500">
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
                        <option value="{{ $code }}" @selected(app()->getLocale() === $code)>{{ $info['flag'] }} {{ $info['name'] }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition">
                    {{ __('teacher.settings.save') }}
                </button>
            </form>
        </div>

        {{-- Password --}}
        @if($user->hasPassword())
            <div class="card p-6">
                <h2 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <i data-lucide="lock" class="w-4 h-4 text-primary-500"></i> {{ __('teacher.settings.password') }}
                </h2>
                <form method="POST" action="{{ route('password.update') }}" class="space-y-4 max-w-md">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('teacher.settings.current_password') }}</label>
                        <input type="password" name="current_password" autocomplete="current-password" required
                               class="w-full rounded-lg border-gray-300 text-sm focus:ring-primary-500 focus:border-primary-500">
                        @error('current_password', 'updatePassword')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('teacher.settings.new_password') }}</label>
                        <input type="password" name="password" autocomplete="new-password" required
                               class="w-full rounded-lg border-gray-300 text-sm focus:ring-primary-500 focus:border-primary-500">
                        @error('password', 'updatePassword')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('teacher.settings.confirm_password') }}</label>
                        <input type="password" name="password_confirmation" autocomplete="new-password" required
                               class="w-full rounded-lg border-gray-300 text-sm focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-lg transition">
                        {{ __('teacher.settings.update_password') }}
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
