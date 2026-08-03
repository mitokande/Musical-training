@extends('layouts.standalone')

@section('title', __('pages.subscription.meta_title'))
@section('description', __('pages.subscription.meta_description'))

@section('content')

@php
    $subEmail = '<a href="mailto:support@harmoniva.app" class="text-purple-600 hover:text-purple-700 underline transition-colors">support@harmoniva.app</a>';
@endphp

{{-- Hero --}}
<section class="py-16 bg-gray-900 text-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl sm:text-5xl font-extrabold mb-3">{{ __('pages.subscription.hero_title') }}</h1>
        <p class="text-gray-400 text-lg">{{ __('pages.subscription.updated') }}</p>
    </div>
</section>

{{-- Intro --}}
<section class="py-12 bg-[#FAF7F2] border-b border-gray-200">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-start gap-4 bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <i data-lucide="info" class="w-5 h-5 text-purple-600"></i>
            </div>
            <div>
                <p class="text-gray-700 leading-relaxed">{!! __('pages.subscription.intro', [
                    'terms' => '<a href="'.locale_url('/terms-of-service').'" class="text-purple-600 hover:text-purple-700 underline transition-colors">'.__('pages.subscription.terms_link').'</a>',
                    'email' => $subEmail,
                ]) !!}</p>
            </div>
        </div>
    </div>
</section>

{{-- Content --}}
<section class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-gray-700 leading-relaxed">

        {{-- Subscription Plans --}}
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-5">{{ __('pages.subscription.plans_title') }}</h2>
            <p class="mb-6">{{ __('pages.subscription.plans_intro') }}</p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6">
                <div class="bg-[#FAF7F2] rounded-2xl p-5 border border-gray-100">
                    <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center mb-3">
                        <i data-lucide="music" class="w-5 h-5 text-gray-600"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-2">{{ __('pages.subscription.plan_free_title') }}</h3>
                    <p class="text-sm text-gray-600">{{ __('pages.subscription.plan_free_desc') }}</p>
                </div>

                <div class="bg-purple-50 rounded-2xl p-5 border border-purple-200">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mb-3">
                        <i data-lucide="star" class="w-5 h-5 text-purple-600"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-2">{{ __('pages.subscription.plan_premium_title') }}</h3>
                    <p class="text-sm text-gray-600">{{ __('pages.subscription.plan_premium_desc') }}</p>
                </div>

                <div class="bg-orange-50 rounded-2xl p-5 border border-orange-200">
                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center mb-3">
                        <i data-lucide="users" class="w-5 h-5 text-orange-600"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-2">{{ __('pages.subscription.plan_ts_title') }}</h3>
                    <p class="text-sm text-gray-600">{{ __('pages.subscription.plan_ts_desc') }}</p>
                </div>
            </div>

            <p>{!! __('pages.subscription.plans_footer', ['pricing' => '<a href="'.locale_url('/pricing').'" class="text-purple-600 hover:text-purple-700 underline transition-colors">'.__('pages.subscription.pricing_link').'</a>']) !!}</p>
        </div>

        {{-- Billing Cycles --}}
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.subscription.cycles_title') }}</h2>
            <p class="mb-4">{{ __('pages.subscription.cycles_intro') }}</p>
            <ul class="list-disc list-outside pl-5 space-y-2 mb-4">
                <li>{!! __('pages.subscription.cycles_li1') !!}</li>
                <li>{!! __('pages.subscription.cycles_li2') !!}</li>
            </ul>
            <p>{{ __('pages.subscription.cycles_p1') }}</p>
        </div>

        {{-- Auto-Renewal --}}
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.subscription.renewal_title') }}</h2>
            <p class="mb-4">{{ __('pages.subscription.renewal_p1') }}</p>
            <p class="mb-4">{{ __('pages.subscription.renewal_p2') }}</p>
            <p>{{ __('pages.subscription.renewal_p3') }}</p>
        </div>

        {{-- Price Changes --}}
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.subscription.price_title') }}</h2>
            <p class="mb-4">{{ __('pages.subscription.price_p1') }}</p>
            <p>{{ __('pages.subscription.price_p2') }}</p>
        </div>

        {{-- Cancellation --}}
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.subscription.cancel_title') }}</h2>
            <p class="mb-4">{{ __('pages.subscription.cancel_intro') }}</p>
            <ol class="list-decimal list-outside pl-5 space-y-2 mb-4">
                <li>{{ __('pages.subscription.cancel_s1') }}</li>
                <li>{!! __('pages.subscription.cancel_s2') !!}</li>
                <li>{{ __('pages.subscription.cancel_s3') }}</li>
            </ol>
            <p class="mb-4">{!! __('pages.subscription.cancel_p1', ['email' => $subEmail]) !!}</p>
            <p class="mb-4">{{ __('pages.subscription.cancel_p2') }}</p>
            <p>{!! __('pages.subscription.cancel_p3', ['refund' => '<a href="'.locale_url('/refund-policy').'" class="text-purple-600 hover:text-purple-700 underline transition-colors">'.__('pages.subscription.refund_link').'</a>']) !!}</p>
        </div>

        {{-- Refunds --}}
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.subscription.refunds_title') }}</h2>
            <p class="mb-4">{!! __('pages.subscription.refunds_intro', ['refund' => '<a href="'.locale_url('/refund-policy').'" class="text-purple-600 hover:text-purple-700 underline transition-colors">'.__('pages.subscription.refund_link').'</a>']) !!}</p>
            <ul class="list-disc list-outside pl-5 space-y-2">
                <li>{{ __('pages.subscription.refunds_li1') }}</li>
                <li>{{ __('pages.subscription.refunds_li2') }}</li>
                <li>{!! __('pages.subscription.refunds_li3', ['email' => $subEmail]) !!}</li>
            </ul>
        </div>

        {{-- Upgrades & Downgrades --}}
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.subscription.ud_title') }}</h2>

            <h3 class="text-lg font-semibold text-gray-900 mb-2 mt-4">{{ __('pages.subscription.ud_up_title') }}</h3>
            <p class="mb-4">{{ __('pages.subscription.ud_up_p') }}</p>

            <h3 class="text-lg font-semibold text-gray-900 mb-2 mt-4">{{ __('pages.subscription.ud_down_title') }}</h3>
            <p class="mb-4">{{ __('pages.subscription.ud_down_p1') }}</p>
            <p>{{ __('pages.subscription.ud_down_p2') }}</p>
        </div>

        {{-- Free Trial --}}
        <div class="mb-4">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.subscription.trial_title') }}</h2>
            <p class="mb-4">{!! __('pages.subscription.trial_p1', ['days' => (int) config('payments.trial.days', 15)]) !!}</p>
            <p class="mb-4">{!! __('pages.subscription.trial_p2') !!}</p>
            <p class="mb-4">{{ __('pages.subscription.trial_p3') }}</p>
            <div class="bg-[#FAF7F2] rounded-xl p-5 border border-gray-100 mt-6">
                <p class="text-sm text-gray-600">{!! __('pages.subscription.trial_box', ['email' => '<a href="mailto:support@harmoniva.app" class="text-purple-600 font-medium hover:text-purple-700 transition-colors">support@harmoniva.app</a>']) !!}</p>
            </div>
        </div>

    </div>
</section>

@endsection
