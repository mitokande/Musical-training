@extends('layouts.standalone')

@section('title', __('pages.refund.meta_title'))
@section('description', __('pages.refund.meta_description', ['days' => config('payments.refund_days', 14)]))

@section('content')

@php
    // The advertised window and the trial length are configuration, not copy:
    // checkout, config/payments.php and this page must never disagree.
    $refundDays = (int) config('payments.refund_days', 14);
    $trialDays = (int) config('payments.trial.days', 15);
    $refundVars = ['days' => $refundDays, 'trial' => $trialDays];
    $refundEmail = '<a href="mailto:support@harmoniva.app" class="text-purple-600 hover:text-purple-700 underline transition-colors">support@harmoniva.app</a>';
@endphp

{{-- Hero --}}
<section class="py-16 bg-gray-900 text-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl sm:text-5xl font-extrabold mb-3">{{ __('pages.refund.hero_title') }}</h1>
        <p class="text-gray-400 text-lg">{{ __('pages.refund.updated') }}</p>
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
                <h2 class="text-lg font-bold text-gray-900">{{ __('pages.refund.summary_title') }}</h2>
            </div>
            <ul class="space-y-2.5 text-gray-700">
                @foreach (['summary_li1', 'summary_li2', 'summary_li3', 'summary_li4'] as $item)
                    <li class="flex items-start gap-3">
                        <i data-lucide="check" class="w-4 h-4 text-purple-600 flex-shrink-0 mt-1"></i>
                        <span>{{ __('pages.refund.'.$item, $refundVars) }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</section>

{{-- Content --}}
<section class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-gray-700 leading-relaxed">

        {{-- Commitment --}}
        <div class="mb-12">
            <div class="flex items-start gap-5 bg-green-50 border border-green-200 rounded-2xl p-6 mb-8">
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i data-lucide="shield-check" class="w-6 h-6 text-green-600"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-green-900 mb-2">{{ __('pages.refund.box_title', $refundVars) }}</h2>
                    <p class="text-green-800 leading-relaxed">{{ __('pages.refund.box_text', $refundVars) }}</p>
                </div>
            </div>

            <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.refund.commit_title') }}</h2>
            <p class="mb-4">{{ __('pages.refund.commit_p1') }}</p>
            <p>{{ __('pages.refund.commit_p2') }}</p>
        </div>

        {{-- Free trial --}}
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.refund.trial_title') }}</h2>
            <p class="mb-4">{{ __('pages.refund.trial_p1', $refundVars) }}</p>
            <p>{{ __('pages.refund.trial_p2') }}</p>
        </div>

        {{-- Money-back guarantee --}}
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.refund.g_title', $refundVars) }}</h2>
            <p class="mb-4">{!! __('pages.refund.g_intro', $refundVars) !!}</p>
            <ul class="list-disc list-outside pl-5 space-y-2 mb-4">
                <li>{!! __('pages.refund.g_li1') !!}</li>
                <li>{!! __('pages.refund.g_li2') !!}</li>
                <li>{!! __('pages.refund.g_li3') !!}</li>
                <li>{!! __('pages.refund.g_li4') !!}</li>
            </ul>
            <p class="mb-4">{{ __('pages.refund.g_p1', $refundVars) }}</p>
            <p>{{ __('pages.refund.g_p2') }}</p>
        </div>

        {{-- Statutory rights --}}
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.refund.law_title') }}</h2>
            <p class="mb-4">{{ __('pages.refund.law_p1') }}</p>
            <ul class="list-disc list-outside pl-5 space-y-2 mb-4">
                <li>{!! __('pages.refund.law_li1') !!}</li>
                <li>{!! __('pages.refund.law_li2') !!}</li>
                <li>{!! __('pages.refund.law_li3') !!}</li>
                <li>{!! __('pages.refund.law_li4') !!}</li>
            </ul>
            <p>{{ __('pages.refund.law_p2') }}</p>
        </div>

        {{-- How to Request --}}
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.refund.req_title') }}</h2>
            <p class="mb-5">{{ __('pages.refund.req_intro') }}</p>

            <div class="space-y-4 mb-6">
                <div class="flex items-start gap-4">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0 font-bold text-purple-700 text-sm mt-0.5">1</div>
                    <div>
                        <p class="font-semibold text-gray-900">{{ __('pages.refund.req_s1_title') }}</p>
                        <p class="text-gray-600 text-sm mt-1">{!! __('pages.refund.req_s1_desc', ['email' => $refundEmail]) !!}</p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0 font-bold text-purple-700 text-sm mt-0.5">2</div>
                    <div>
                        <p class="font-semibold text-gray-900">{{ __('pages.refund.req_s2_title') }}</p>
                        <p class="text-gray-600 text-sm mt-1">{{ __('pages.refund.req_s2_desc') }}</p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0 font-bold text-purple-700 text-sm mt-0.5">3</div>
                    <div>
                        <p class="font-semibold text-gray-900">{{ __('pages.refund.req_s3_title') }}</p>
                        <p class="text-gray-600 text-sm mt-1">{{ __('pages.refund.req_s3_desc') }}</p>
                    </div>
                </div>
            </div>

            <p class="text-sm text-gray-500 italic">{{ __('pages.refund.req_note') }}</p>
        </div>

        {{-- Non-Refundable --}}
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.refund.nr_title') }}</h2>
            <p class="mb-4">{{ __('pages.refund.nr_intro') }}</p>
            <ul class="list-disc list-outside pl-5 space-y-2 mb-4">
                <li>{!! __('pages.refund.nr_li1', $refundVars) !!}</li>
                <li>{!! __('pages.refund.nr_li2') !!}</li>
                <li>{!! __('pages.refund.nr_li3') !!}</li>
                <li>{!! __('pages.refund.nr_li4') !!}</li>
                <li>{!! __('pages.refund.nr_li5', $refundVars) !!}</li>
            </ul>
            <p>{{ __('pages.refund.nr_p1') }}</p>
        </div>

        {{-- App Store / Google Play --}}
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.refund.app_title') }}</h2>
            <p class="mb-4">{{ __('pages.refund.app_p1') }}</p>
            <ul class="list-disc list-outside pl-5 space-y-2 mb-4">
                <li>{!! __('pages.refund.app_li1') !!}</li>
                <li>{!! __('pages.refund.app_li2') !!}</li>
                <li>{!! __('pages.refund.app_li3') !!}</li>
            </ul>
            <p class="mb-4">{{ __('pages.refund.app_p2', $refundVars) }}</p>
            <p>{{ __('pages.refund.app_p3') }}</p>
        </div>

        {{-- Timeline --}}
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.refund.tl_title') }}</h2>
            <p class="mb-4">{{ __('pages.refund.tl_intro') }}</p>
            <ul class="list-disc list-outside pl-5 space-y-2 mb-4">
                <li>{{ __('pages.refund.tl_li1') }}</li>
                <li>{{ __('pages.refund.tl_li2') }}</li>
                <li>{{ __('pages.refund.tl_li3') }}</li>
            </ul>
            <div class="bg-[#FAF7F2] rounded-xl p-5 border border-gray-100 mb-4">
                <div class="flex items-center gap-3 mb-2">
                    <i data-lucide="clock" class="w-5 h-5 text-purple-600"></i>
                    <span class="font-semibold text-gray-900">{{ __('pages.refund.tl_box_title') }}</span>
                </div>
                <p class="text-sm text-gray-600">{{ __('pages.refund.tl_box_desc') }}</p>
            </div>
            <p class="text-sm text-gray-500">{{ __('pages.refund.tl_note') }}</p>
        </div>

        {{-- Chargebacks --}}
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.refund.cb_title') }}</h2>
            <p class="mb-4">{{ __('pages.refund.cb_p1') }}</p>
            <p>{{ __('pages.refund.cb_p2') }}</p>
        </div>

        {{-- Teachers, schools, third-party payments --}}
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.refund.tp_title') }}</h2>
            <p class="mb-4">{{ __('pages.refund.tp_p1') }}</p>
            <p class="mb-4">{{ __('pages.refund.tp_p2') }}</p>
            <p>{{ __('pages.refund.tp_p3') }}</p>
        </div>

        {{-- Currency & taxes --}}
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.refund.cur_title') }}</h2>
            <p class="mb-4">{{ __('pages.refund.cur_p1') }}</p>
            <p>{{ __('pages.refund.cur_p2') }}</p>
        </div>

        {{-- Changes --}}
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.refund.ch_title') }}</h2>
            <p>{{ __('pages.refund.ch_p1') }}</p>
        </div>

        {{-- Contact --}}
        <div class="mb-4">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.refund.contact_title') }}</h2>
            <p class="mb-5">{{ __('pages.refund.contact_intro') }}</p>
            <div class="bg-gray-900 rounded-2xl p-6 text-white flex flex-col sm:flex-row items-start sm:items-center gap-5">
                <div class="w-12 h-12 bg-purple-600 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i data-lucide="mail" class="w-6 h-6 text-white"></i>
                </div>
                <div class="flex-1">
                    <p class="font-bold text-white text-lg mb-1">{{ __('pages.refund.contact_box_title') }}</p>
                    <p class="text-gray-400 text-sm">{{ __('pages.refund.contact_box_desc') }}</p>
                </div>
                <a href="mailto:support@harmoniva.app" class="inline-flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold px-5 py-2.5 rounded-xl transition-colors duration-200 whitespace-nowrap">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    support@harmoniva.app
                </a>
            </div>
            <p class="mt-6 text-sm text-gray-500">{{ __('pages.refund.contact_address') }}</p>
            <p class="mt-2 text-sm text-gray-500">{!! __('pages.refund.contact_footer', [
                'terms' => '<a href="'.locale_url('/terms-of-service').'" class="text-purple-600 hover:text-purple-700 transition-colors">'.__('pages.refund.contact_terms_link').'</a>',
                'subscription' => '<a href="'.locale_url('/subscription-terms').'" class="text-purple-600 hover:text-purple-700 transition-colors">'.__('pages.refund.contact_subscription_link').'</a>',
            ]) !!}</p>
        </div>

    </div>
</section>

@endsection
