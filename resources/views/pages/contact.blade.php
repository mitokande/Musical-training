@extends('layouts.standalone')

@section('title', __('pages.contact.meta_title'))
@section('description', __('pages.contact.meta_description'))

@section('content')

{{-- Hero --}}
<section class="bg-gradient-to-br from-purple-700 via-purple-600 to-purple-800 text-white py-20 px-4">
    <div class="max-w-3xl mx-auto text-center reveal">
        <div class="inline-flex items-center gap-2 bg-white/10 text-white text-sm font-medium px-4 py-2 rounded-full mb-6">
            <i data-lucide="message-circle" class="w-4 h-4"></i>
            {{ __('pages.contact.hero_badge') }}
        </div>
        <h1 class="text-4xl md:text-5xl font-bold mb-4">{{ __('pages.contact.hero_title') }}</h1>
        <p class="text-purple-200 text-lg">{{ __('pages.contact.hero_subtitle') }}</p>
    </div>
</section>

{{-- Support Options --}}
<section class="bg-[#FAF7F2] py-12 px-4 border-b border-gray-100">
    <div class="max-w-5xl mx-auto">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center gap-4 reveal">
                <div class="w-11 h-11 rounded-xl bg-purple-100 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="mail" class="w-5 h-5 text-purple-600"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-900 text-sm">{{ __('pages.contact.opt_email_title') }}</p>
                    <p class="text-gray-500 text-xs mt-0.5">support@harmoniva.app</p>
                </div>
            </div>
            <a href="{{ locale_url('/help') }}" class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center gap-4 hover:border-purple-200 hover:shadow-md transition-all group reveal">
                <div class="w-11 h-11 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="book-open" class="w-5 h-5 text-blue-600"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-900 text-sm group-hover:text-purple-600 transition-colors">{{ __('pages.contact.opt_help_title') }}</p>
                    <p class="text-gray-500 text-xs mt-0.5">{{ __('pages.contact.opt_help_desc') }}</p>
                </div>
            </a>
            <a href="{{ locale_url('/faq') }}" class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center gap-4 hover:border-purple-200 hover:shadow-md transition-all group reveal">
                <div class="w-11 h-11 rounded-xl bg-green-100 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="help-circle" class="w-5 h-5 text-green-600"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-900 text-sm group-hover:text-purple-600 transition-colors">{{ __('pages.contact.opt_faq_title') }}</p>
                    <p class="text-gray-500 text-xs mt-0.5">{{ __('pages.contact.opt_faq_desc') }}</p>
                </div>
            </a>
        </div>
    </div>
</section>

{{-- Main Two-Column Layout --}}
<section class="bg-[#FAF7F2] py-16 px-4">
    <div class="max-w-5xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-10">

            {{-- Contact Form (wider) --}}
            <div class="lg:col-span-3 reveal">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ __('pages.contact.form_title') }}</h2>
                    <p class="text-gray-500 text-sm mb-8">{{ __('pages.contact.form_subtitle') }}</p>

                    @if (session('contact_status') === 'sent')
                        <div class="mb-6 flex items-start gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3.5">
                            <i data-lucide="check-circle" class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5"></i>
                            <p class="text-sm text-green-800">{{ __('pages.contact.success') }}</p>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-6 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3.5">
                            <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5"></i>
                            <ul class="text-sm text-red-800 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('contact.submit') }}" method="POST" class="space-y-5">
                        @csrf

                        {{-- Honeypot: hidden from humans, filled by bots. --}}
                        <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true" />

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label for="contact_name" class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('pages.contact.label_name') }}</label>
                                <input
                                    type="text"
                                    id="contact_name"
                                    name="name"
                                    value="{{ old('name', auth()->user()?->name) }}"
                                    placeholder="{{ __('pages.contact.ph_name') }}"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-3 text-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent transition-shadow"
                                    required
                                />
                            </div>
                            <div>
                                <label for="contact_email" class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('pages.contact.label_email') }}</label>
                                <input
                                    type="email"
                                    id="contact_email"
                                    name="email"
                                    value="{{ old('email', auth()->user()?->email) }}"
                                    placeholder="{{ __('pages.contact.ph_email') }}"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-3 text-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent transition-shadow"
                                    required
                                />
                            </div>
                        </div>

                        <div>
                            <label for="contact_subject" class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('pages.contact.label_subject') }}</label>
                            <div class="relative">
                                <select
                                    id="contact_subject"
                                    name="subject"
                                    class="w-full appearance-none border border-gray-200 rounded-xl px-4 py-3 text-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent bg-white transition-shadow pr-10"
                                    required
                                >
                                    <option value="" disabled @selected(! old('subject'))>{{ __('pages.contact.subject_select') }}</option>
                                    @foreach (['general', 'billing', 'technical', 'schools', 'other'] as $topic)
                                        <option value="{{ $topic }}" @selected(old('subject') === $topic)>{{ __('pages.contact.subject_'.$topic) }}</option>
                                    @endforeach
                                </select>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                            </div>
                        </div>

                        <div>
                            <label for="contact_message" class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('pages.contact.label_message') }}</label>
                            <textarea
                                id="contact_message"
                                name="message"
                                rows="6"
                                placeholder="{{ __('pages.contact.ph_message') }}"
                                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400 focus:border-transparent transition-shadow resize-none"
                                required
                            >{{ old('message') }}</textarea>
                        </div>

                        <button
                            type="submit"
                            class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3.5 rounded-xl transition-colors shadow-lg hover:shadow-xl flex items-center justify-center gap-2 text-base"
                        >
                            <i data-lucide="send" class="w-5 h-5"></i>
                            {{ __('pages.contact.submit') }}
                        </button>

                        <p class="text-xs text-gray-400 text-center">{!! __('pages.contact.privacy_note', ['privacy_link' => '<a href="'.locale_url('/privacy-policy').'" class="underline hover:text-gray-600">'.__('pages.contact.privacy_link_text').'</a>']) !!}</p>
                    </form>
                </div>
            </div>

            {{-- Contact Info (narrower) --}}
            <div class="lg:col-span-2 space-y-6 reveal">

                {{-- Response Info --}}
                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i data-lucide="clock" class="w-5 h-5 text-purple-600"></i>
                        {{ __('pages.contact.response_title') }}
                    </h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">{{ __('pages.contact.resp_typical_label') }}</span>
                            <span class="text-sm font-semibold text-gray-900">{{ __('pages.contact.resp_typical_value') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">{{ __('pages.contact.resp_billing_label') }}</span>
                            <span class="text-sm font-semibold text-gray-900">{{ __('pages.contact.resp_billing_value') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">{{ __('pages.contact.resp_school_label') }}</span>
                            <span class="text-sm font-semibold text-gray-900">{{ __('pages.contact.resp_school_value') }}</span>
                        </div>
                    </div>
                    <div class="mt-5 pt-5 border-t border-gray-100">
                        <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider mb-2">{{ __('pages.contact.hours_label') }}</p>
                        <p class="text-sm text-gray-700">{{ __('pages.contact.hours_days') }}</p>
                        <p class="text-sm text-gray-700">{{ __('pages.contact.hours_time') }}</p>
                    </div>
                </div>

                {{-- Direct Email --}}
                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i data-lucide="mail" class="w-5 h-5 text-purple-600"></i>
                        {{ __('pages.contact.email_title') }}
                    </h3>
                    <a href="mailto:support@harmoniva.app" class="text-purple-600 hover:text-purple-700 font-medium text-sm break-all">
                        support@harmoniva.app
                    </a>
                    <p class="text-gray-500 text-xs mt-2">{{ __('pages.contact.email_desc') }}</p>
                </div>

                {{-- Address --}}
                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i data-lucide="map-pin" class="w-5 h-5 text-purple-600"></i>
                        {{ __('pages.contact.address_title') }}
                    </h3>
                    <address class="not-italic text-sm text-gray-700 leading-relaxed">
                        <strong class="text-gray-900">Harmoniva — Softchain Solutions</strong><br>
                        8 The Green STE B<br>
                        Dover, DE 19901<br>
                        {{ __('pages.contact.address_country') }}
                    </address>
                    <div class="mt-4 bg-gray-50 rounded-xl p-3 flex items-center justify-center">
                        <div class="text-center">
                            <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center mx-auto mb-2">
                                <i data-lucide="building-2" class="w-5 h-5 text-purple-600"></i>
                            </div>
                            <p class="text-xs text-gray-500">{{ __('pages.contact.address_registered') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Social / Community (bonus) --}}
                <div class="bg-gradient-to-br from-purple-50 to-orange-50 rounded-2xl p-6 border border-purple-100">
                    <h3 class="font-bold text-gray-900 mb-3">{{ __('pages.contact.not_urgent_title') }}</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">{!! __('pages.contact.not_urgent_desc', ['faq_link' => '<a href="'.locale_url('/faq').'" class="text-purple-600 hover:underline font-medium">'.__('pages.contact.faq_link_text').'</a>', 'help_link' => '<a href="'.locale_url('/help').'" class="text-purple-600 hover:underline font-medium">'.__('pages.contact.help_link_text').'</a>']) !!}</p>
                </div>

            </div>

        </div>
    </div>
</section>

@endsection
