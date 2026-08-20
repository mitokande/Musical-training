@extends('layouts.standalone')

@section('title', __('pages.delete_account.meta_title'))
@section('description', __('pages.delete_account.meta_description'))

@section('content')

@php
    // Public on purpose: Google Play and the App Store require the account
    // deletion route to be reachable without installing the app or signing in.
    $daUser = auth()->user();
    $daPrivacy = '<a href="'.locale_url('/privacy-policy').'" class="text-purple-600 hover:text-purple-700 underline transition-colors">'.__('pages.delete_account.privacy_link').'</a>';
    $daRefund = '<a href="'.locale_url('/refund-policy').'" class="text-purple-600 hover:text-purple-700 underline transition-colors">'.__('pages.delete_account.refund_link').'</a>';
@endphp

{{-- Hero --}}
<section class="py-16 bg-gray-900 text-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl sm:text-5xl font-extrabold mb-3">{{ __('pages.delete_account.hero_title') }}</h1>
        <p class="text-gray-300 text-lg mb-2">{{ __('pages.delete_account.hero_sub') }}</p>
        <p class="text-gray-500 text-sm">{{ __('pages.delete_account.updated') }}</p>
    </div>
</section>

{{-- Summary --}}
<section class="py-12 bg-[#FAF7F2] border-b border-gray-200">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i data-lucide="info" class="w-5 h-5 text-purple-600"></i>
                </div>
                <h2 class="text-lg font-bold text-gray-900">{{ __('pages.delete_account.summary_title') }}</h2>
            </div>
            <ul class="space-y-2.5 text-gray-700">
                @foreach (['summary_li1', 'summary_li2', 'summary_li3', 'summary_li4'] as $item)
                    <li class="flex items-start gap-3">
                        <i data-lucide="check" class="w-4 h-4 text-purple-600 flex-shrink-0 mt-1"></i>
                        <span>{{ __('pages.delete_account.'.$item) }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</section>

<section class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-gray-700 leading-relaxed">

        {{-- Route 1: delete right here --}}
        <section id="delete-on-the-web" class="mb-12" x-data="{ showModal: {{ $errors->userDeletion->isNotEmpty() ? 'true' : 'false' }} }">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.delete_account.web_title') }}</h2>

            @if ($daUser)
                <div class="bg-white rounded-2xl border border-red-200 p-6">
                    <p class="mb-5">{{ __('pages.delete_account.web_signed_in_text', ['email' => $daUser->email]) }}</p>
                    <button @click="showModal = true"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg transition text-sm font-semibold">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                        {{ __('app.profile.delete_account') }}
                    </button>
                </div>

                {{-- Same confirmation contract as the profile Settings tab:
                     password, or the account's own address for Google sign-ins. --}}
                <div x-show="showModal" x-cloak
                     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
                     @click.self="showModal = false">
                    <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md mx-4" @click.stop>
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                                <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900">{{ __('app.profile.delete_confirm_title') }}</h3>
                        </div>
                        <p class="text-sm text-gray-600 mb-5">{{ __('app.profile.delete_confirm_desc') }}</p>

                        <form method="POST" action="{{ route('profile.destroy') }}">
                            @csrf
                            @method('DELETE')

                            <div class="space-y-4">
                                @if ($daUser->hasPassword())
                                    <div>
                                        <label for="da_password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.profile.delete_password_label') }}</label>
                                        <input type="password" name="password" id="da_password" required autocomplete="current-password"
                                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm">
                                        @error('password', 'userDeletion')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @else
                                    <div>
                                        <label for="da_confirm_email" class="block text-sm font-medium text-gray-700 mb-1">
                                            {{ __('app.profile.delete_email_label', ['email' => $daUser->email]) }}
                                        </label>
                                        <input type="text" name="confirm_email" id="da_confirm_email" required autocomplete="off"
                                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm">
                                        @error('confirm_email', 'userDeletion')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @endif

                                <div>
                                    <label for="da_reason" class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.profile.delete_reason_label') }}</label>
                                    <textarea name="reason" id="da_reason" rows="2" maxlength="500"
                                              placeholder="{{ __('app.profile.delete_reason_placeholder') }}"
                                              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm"></textarea>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end gap-3">
                                <button type="button" @click="showModal = false"
                                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                                    {{ __('app.common.cancel') }}
                                </button>
                                <button type="submit"
                                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition">
                                    {{ __('app.profile.delete_confirm_button') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <p class="mb-5">{{ __('pages.delete_account.web_signed_out_text') }}</p>
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-900 hover:bg-gray-800 text-white rounded-lg transition text-sm font-semibold">
                        <i data-lucide="log-in" class="w-4 h-4"></i>
                        {{ __('pages.delete_account.web_sign_in') }}
                    </a>
                </div>
            @endif
        </section>

        {{-- Route 2: in the mobile app --}}
        <section id="delete-in-the-app" class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.delete_account.app_title') }}</h2>
            <ol class="space-y-3 mb-4">
                @foreach (['app_step1', 'app_step2', 'app_step3'] as $i => $step)
                    <li class="flex items-start gap-3">
                        <span class="w-6 h-6 rounded-full bg-purple-100 text-purple-700 text-xs font-bold flex items-center justify-center flex-shrink-0 mt-0.5">{{ $i + 1 }}</span>
                        <span>{{ __('pages.delete_account.'.$step) }}</span>
                    </li>
                @endforeach
            </ol>
            <p class="text-sm text-gray-500">{{ __('pages.delete_account.app_note') }}</p>
        </section>

        {{-- Route 3: no access left --}}
        <section id="contact-support" class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.delete_account.support_title') }}</h2>
            <p class="mb-4">{{ __('pages.delete_account.support_text') }}</p>
            <a href="mailto:support@harmoniva.app?subject=Account%20deletion%20request"
               class="inline-flex items-center gap-2 px-5 py-2.5 border border-gray-300 hover:border-purple-400 hover:text-purple-700 rounded-lg transition text-sm font-semibold text-gray-800">
                <i data-lucide="mail" class="w-4 h-4"></i>
                {{ __('pages.delete_account.support_cta') }}
            </a>
        </section>

        {{-- Data disclosure: what goes, what stays, for how long --}}
        <section id="what-is-deleted" class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.delete_account.data_title') }}</h2>
            <p class="mb-4">{{ __('pages.delete_account.data_intro') }}</p>
            <ul class="space-y-2.5">
                @foreach (['data_li1', 'data_li2', 'data_li3', 'data_li4', 'data_li5'] as $item)
                    <li class="flex items-start gap-3">
                        <i data-lucide="x-circle" class="w-4 h-4 text-red-500 flex-shrink-0 mt-1"></i>
                        <span>{{ __('pages.delete_account.'.$item) }}</span>
                    </li>
                @endforeach
            </ul>
        </section>

        <section id="what-we-keep" class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.delete_account.kept_title') }}</h2>
            <p class="mb-4">{{ __('pages.delete_account.kept_intro') }}</p>
            <ul class="space-y-2.5 mb-4">
                @foreach (['kept_li1', 'kept_li2', 'kept_li3'] as $item)
                    <li class="flex items-start gap-3">
                        <i data-lucide="archive" class="w-4 h-4 text-gray-400 flex-shrink-0 mt-1"></i>
                        <span>{{ __('pages.delete_account.'.$item) }}</span>
                    </li>
                @endforeach
            </ul>
            <p>{!! __('pages.delete_account.kept_outro') !!}</p>
        </section>

        <section id="subscriptions" class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.delete_account.sub_title') }}</h2>
            <p>{!! __('pages.delete_account.sub_text', ['refund' => $daRefund]) !!}</p>
        </section>

        <p class="text-sm text-gray-500 pt-6 border-t border-gray-100">
            {!! __('pages.delete_account.legal_note', ['privacy' => $daPrivacy]) !!}
        </p>

    </div>
</section>

@endsection
