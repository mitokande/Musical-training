@extends('layouts.standalone')

@section('title', __('pages.terms.meta_title'))
@section('description', __('pages.terms.meta_description'))

@section('content')

@php
    $tosEmail = '<a href="mailto:support@harmoniva.app" class="text-purple-600 hover:text-purple-700 underline transition-colors">support@harmoniva.app</a>';
    $tosPrivacy = '<a href="'.locale_url('/privacy-policy').'" class="text-purple-600 hover:text-purple-700 underline transition-colors">'.__('pages.terms.privacy_link').'</a>';
    $tosChildrens = '<a href="'.locale_url('/privacy-policy').'#childrens-privacy" class="text-purple-600 hover:text-purple-700 underline transition-colors">'.__('pages.terms.childrens_link').'</a>';
    $tosPricing = '<a href="'.locale_url('/pricing').'" class="text-purple-600 hover:text-purple-700 underline transition-colors">'.__('pages.terms.pricing_link').'</a>';
    $tosSubscription = '<a href="'.locale_url('/subscription-terms').'" class="text-purple-600 hover:text-purple-700 underline transition-colors">'.__('pages.terms.subscription_link').'</a>';
    $tosRefund = '<a href="'.locale_url('/refund-policy').'" class="text-purple-600 hover:text-purple-700 underline transition-colors">'.__('pages.terms.refund_link').'</a>';
@endphp

{{-- Hero --}}
<section class="py-16 bg-gray-900 text-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl sm:text-5xl font-extrabold mb-3">{{ __('pages.terms.hero_title') }}</h1>
        <p class="text-gray-400 text-lg">{{ __('pages.terms.updated') }}</p>
    </div>
</section>

{{-- Content --}}
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex gap-12 items-start">

            {{-- Sticky Sidebar TOC --}}
            <aside class="hidden lg:block w-64 flex-shrink-0 sticky top-24">
                <div class="bg-[#FAF7F2] rounded-2xl p-5 border border-gray-100">
                    <h2 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">{{ __('pages.terms.toc_title') }}</h2>
                    <nav class="space-y-1">
                        <a href="#acceptance" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">{{ __('pages.terms.toc_acceptance') }}</a>
                        <a href="#description" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">{{ __('pages.terms.toc_description') }}</a>
                        <a href="#account-registration" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">{{ __('pages.terms.toc_account') }}</a>
                        <a href="#billing" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">{{ __('pages.terms.toc_billing') }}</a>
                        <a href="#free-plan" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">{{ __('pages.terms.toc_free') }}</a>
                        <a href="#prohibited-uses" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">{{ __('pages.terms.toc_prohibited') }}</a>
                        <a href="#intellectual-property" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">{{ __('pages.terms.toc_ip') }}</a>
                        <a href="#user-content" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">{{ __('pages.terms.toc_user_content') }}</a>
                        <a href="#disclaimers" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">{{ __('pages.terms.toc_disclaimers') }}</a>
                        <a href="#termination" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">{{ __('pages.terms.toc_termination') }}</a>
                        <a href="#governing-law" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">{{ __('pages.terms.toc_law') }}</a>
                        <a href="#tos-contact" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">{{ __('pages.terms.toc_contact') }}</a>
                        <div class="border-t border-gray-200 my-3"></div>
                        <a href="#accessibility" class="block text-sm text-purple-600 hover:text-purple-700 font-medium py-1 transition-colors">{{ __('pages.terms.toc_accessibility') }}</a>
                    </nav>
                </div>
            </aside>

            {{-- Main Content --}}
            <main class="flex-1 min-w-0 text-gray-700 leading-relaxed">

                <section id="acceptance" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.terms.acc_title') }}</h2>
                    <p class="mb-4">{!! __('pages.terms.acc_p1') !!}</p>
                    <p class="mb-4">{!! __('pages.terms.acc_p2', ['privacy' => $tosPrivacy]) !!}</p>
                    <p>{{ __('pages.terms.acc_p3') }}</p>
                </section>

                <section id="description" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.terms.desc_title') }}</h2>
                    <p class="mb-4">{{ __('pages.terms.desc_p1') }}</p>
                    <p class="mb-4">{{ __('pages.terms.desc_p2') }}</p>
                    <p>{!! __('pages.terms.desc_p3', ['childrens' => $tosChildrens]) !!}</p>
                </section>

                <section id="account-registration" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.terms.acct_title') }}</h2>
                    <p class="mb-4">{{ __('pages.terms.acct_p1') }}</p>
                    <p class="mb-4">{!! __('pages.terms.acct_p2', ['email' => $tosEmail]) !!}</p>
                    <p class="mb-4">{{ __('pages.terms.acct_p3') }}</p>
                    <p>{{ __('pages.terms.acct_p4') }}</p>
                </section>

                <section id="billing" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.terms.bill_title') }}</h2>
                    <p class="mb-4">{!! __('pages.terms.bill_p1', ['pricing' => $tosPricing]) !!}</p>
                    <p class="mb-4">{{ __('pages.terms.bill_p2') }}</p>
                    <p class="mb-4">{{ __('pages.terms.bill_p3') }}</p>
                    <p>{!! __('pages.terms.bill_p4', ['subscription' => $tosSubscription, 'refund' => $tosRefund]) !!}</p>
                </section>

                <section id="free-plan" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.terms.free_title') }}</h2>
                    <p class="mb-4">{{ __('pages.terms.free_p1') }}</p>
                    <p class="mb-4">{{ __('pages.terms.free_p2') }}</p>
                    <p>{{ __('pages.terms.free_p3') }}</p>
                </section>

                <section id="prohibited-uses" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.terms.prob_title') }}</h2>
                    <p class="mb-4">{{ __('pages.terms.prob_intro') }}</p>
                    <ul class="list-disc list-outside pl-5 space-y-2 mb-4">
                        <li>{{ __('pages.terms.prob_li1') }}</li>
                        <li>{{ __('pages.terms.prob_li2') }}</li>
                        <li>{{ __('pages.terms.prob_li3') }}</li>
                        <li>{{ __('pages.terms.prob_li4') }}</li>
                        <li>{{ __('pages.terms.prob_li5') }}</li>
                        <li>{{ __('pages.terms.prob_li6') }}</li>
                        <li>{{ __('pages.terms.prob_li7') }}</li>
                        <li>{{ __('pages.terms.prob_li8') }}</li>
                        <li>{{ __('pages.terms.prob_li9') }}</li>
                        <li>{{ __('pages.terms.prob_li10') }}</li>
                    </ul>
                    <p>{{ __('pages.terms.prob_footer') }}</p>
                </section>

                <section id="intellectual-property" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.terms.ip_title') }}</h2>
                    <p class="mb-4">{{ __('pages.terms.ip_p1') }}</p>
                    <p class="mb-4">{{ __('pages.terms.ip_p2') }}</p>
                    <p>{{ __('pages.terms.ip_p3') }}</p>
                </section>

                <section id="user-content" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.terms.uc_title') }}</h2>
                    <p class="mb-4">{{ __('pages.terms.uc_p1') }}</p>
                    <p class="mb-4">{{ __('pages.terms.uc_p2') }}</p>
                    <p>{!! __('pages.terms.uc_p3', ['email' => $tosEmail]) !!}</p>
                </section>

                <section id="disclaimers" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.terms.dis_title') }}</h2>

                    <h3 class="text-lg font-semibold text-gray-900 mb-2 mt-4">{{ __('pages.terms.dis_warr_title') }}</h3>
                    <p class="mb-4">{{ __('pages.terms.dis_warr_p') }}</p>

                    <h3 class="text-lg font-semibold text-gray-900 mb-2 mt-4">{{ __('pages.terms.dis_liab_title') }}</h3>
                    <p class="mb-4">{{ __('pages.terms.dis_liab_p1') }}</p>
                    <p>{{ __('pages.terms.dis_liab_p2') }}</p>
                </section>

                <section id="termination" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.terms.term_title') }}</h2>
                    <p class="mb-4">{!! __('pages.terms.term_p1', ['email' => $tosEmail]) !!}</p>
                    <p class="mb-4">{{ __('pages.terms.term_p2') }}</p>
                    <p>{{ __('pages.terms.term_p3') }}</p>
                </section>

                <section id="governing-law" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.terms.law_title') }}</h2>
                    <p class="mb-4">{{ __('pages.terms.law_p1') }}</p>
                    <p class="mb-4">{{ __('pages.terms.law_p2') }}</p>
                    <p>{{ __('pages.terms.law_p3') }}</p>
                </section>

                <section id="tos-contact" class="mb-4">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.terms.contact_title') }}</h2>
                    <p class="mb-4">{{ __('pages.terms.contact_intro') }}</p>
                    <div class="bg-[#FAF7F2] rounded-xl p-5 border border-gray-100">
                        <p class="font-semibold text-gray-900 mb-1">{{ __('pages.terms.contact_company') }}</p>
                        <p class="text-gray-700">{!! __('pages.terms.contact_address') !!}</p>
                        <p class="mt-3"><a href="mailto:support@harmoniva.app" class="text-purple-600 font-medium hover:text-purple-700 transition-colors">support@harmoniva.app</a></p>
                    </div>
                    <p class="mt-4 text-sm text-gray-500">{{ __('pages.terms.contact_footer') }}</p>
                </section>

            </main>
        </div>
    </div>
</section>

{{-- Visual Divider --}}
<div class="bg-[#FAF7F2] py-4">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-4">
            <div class="flex-1 h-px bg-gray-300"></div>
            <div class="flex items-center gap-2 px-4 py-2 rounded-full bg-blue-100 text-blue-700 text-sm font-semibold">
                <i data-lucide="accessibility" class="w-4 h-4"></i>
                {{ __('pages.terms.a11y_badge') }}
            </div>
            <div class="flex-1 h-px bg-gray-300"></div>
        </div>
    </div>
</div>

{{-- Accessibility Statement --}}
<section id="accessibility" class="py-16 bg-[#FAF7F2]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex gap-12 items-start">

            {{-- Sidebar --}}
            <aside class="hidden lg:block w-64 flex-shrink-0 sticky top-24">
                <div class="bg-white rounded-2xl p-5 border border-gray-100">
                    <h2 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">{{ __('pages.terms.a11y_side_title') }}</h2>
                    <nav class="space-y-1">
                        <a href="#a11y-commitment" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">{{ __('pages.terms.a11y_toc_commitment') }}</a>
                        <a href="#a11y-measures" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">{{ __('pages.terms.a11y_toc_measures') }}</a>
                        <a href="#a11y-reporting" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">{{ __('pages.terms.a11y_toc_reporting') }}</a>
                        <a href="#a11y-ongoing" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">{{ __('pages.terms.a11y_toc_ongoing') }}</a>
                    </nav>
                </div>
            </aside>

            {{-- Main Content --}}
            <main class="flex-1 min-w-0 text-gray-700 leading-relaxed">
                <div class="mb-8">
                    <h2 class="text-3xl font-extrabold text-gray-900 mb-2">{{ __('pages.terms.a11y_h') }}</h2>
                    <p class="text-gray-500">{{ __('pages.terms.a11y_sub') }}</p>
                </div>

                <section id="a11y-commitment" class="mb-10">
                    <h3 class="text-xl font-bold text-gray-900 mb-3">{{ __('pages.terms.a11y_commit_title') }}</h3>
                    <p class="mb-4">{{ __('pages.terms.a11y_commit_p1') }}</p>
                    <p>{{ __('pages.terms.a11y_commit_p2') }}</p>
                </section>

                <section id="a11y-measures" class="mb-10">
                    <h3 class="text-xl font-bold text-gray-900 mb-3">{{ __('pages.terms.a11y_measures_title') }}</h3>
                    <ul class="list-disc list-outside pl-5 space-y-2 mb-4">
                        <li>{!! __('pages.terms.a11y_m_li1') !!}</li>
                        <li>{!! __('pages.terms.a11y_m_li2') !!}</li>
                        <li>{!! __('pages.terms.a11y_m_li3') !!}</li>
                        <li>{!! __('pages.terms.a11y_m_li4') !!}</li>
                        <li>{!! __('pages.terms.a11y_m_li5') !!}</li>
                        <li>{!! __('pages.terms.a11y_m_li6') !!}</li>
                        <li>{!! __('pages.terms.a11y_m_li7') !!}</li>
                        <li>{!! __('pages.terms.a11y_m_li8') !!}</li>
                    </ul>
                </section>

                <section id="a11y-reporting" class="mb-10">
                    <h3 class="text-xl font-bold text-gray-900 mb-3">{{ __('pages.terms.a11y_report_title') }}</h3>
                    <p class="mb-4">{{ __('pages.terms.a11y_report_p1') }}</p>
                    <p class="mb-4">{{ __('pages.terms.a11y_report_p2') }}</p>
                    <ul class="list-disc list-outside pl-5 space-y-2 mb-4">
                        <li>{!! __('pages.terms.a11y_report_li1', ['email' => $tosEmail]) !!}</li>
                        <li>{{ __('pages.terms.a11y_report_li2') }}</li>
                        <li>{{ __('pages.terms.a11y_report_li3') }}</li>
                    </ul>
                    <div class="bg-white rounded-xl p-5 border border-gray-100">
                        <p class="font-semibold text-gray-900 mb-1">{{ __('pages.terms.a11y_report_box_title') }}</p>
                        <p class="text-gray-700"><a href="mailto:support@harmoniva.app" class="text-purple-600 font-medium hover:text-purple-700 transition-colors">support@harmoniva.app</a></p>
                        <p class="text-sm text-gray-500 mt-1">{{ __('pages.terms.a11y_report_box_subject') }}</p>
                    </div>
                </section>

                <section id="a11y-ongoing" class="mb-4">
                    <h3 class="text-xl font-bold text-gray-900 mb-3">{{ __('pages.terms.a11y_ongoing_title') }}</h3>
                    <p class="mb-4">{{ __('pages.terms.a11y_ongoing_p') }}</p>
                    <ul class="list-disc list-outside pl-5 space-y-2">
                        <li>{{ __('pages.terms.a11y_ongoing_li1') }}</li>
                        <li>{{ __('pages.terms.a11y_ongoing_li2') }}</li>
                        <li>{{ __('pages.terms.a11y_ongoing_li3') }}</li>
                        <li>{{ __('pages.terms.a11y_ongoing_li4') }}</li>
                        <li>{{ __('pages.terms.a11y_ongoing_li5') }}</li>
                    </ul>
                    <p class="mt-4 text-sm text-gray-500">{{ __('pages.terms.a11y_ongoing_footer') }}</p>
                </section>
            </main>
        </div>
    </div>
</section>

@endsection
