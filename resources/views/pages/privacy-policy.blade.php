@extends('layouts.standalone')

@section('title', __('pages.privacy.meta_title'))
@section('description', __('pages.privacy.meta_description'))

@section('content')

@php
    $ppEmail = '<a href="mailto:support@harmoniva.app" class="text-purple-600 hover:text-purple-700 underline transition-colors">support@harmoniva.app</a>';
    $ppCookie = '<a href="'.locale_url('/cookie-policy').'" class="text-purple-600 hover:text-purple-700 underline transition-colors">'.__('pages.privacy.cookie_link').'</a>';
    $ppSection = '<a href="#childrens-privacy" class="text-purple-600 hover:text-purple-700 underline transition-colors">'.__('pages.privacy.cm_section_link').'</a>';
@endphp

{{-- Hero --}}
<section class="py-16 bg-gray-900 text-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl sm:text-5xl font-extrabold mb-3">{{ __('pages.privacy.hero_title') }}</h1>
        <p class="text-gray-400 text-lg">{{ __('pages.privacy.updated') }}</p>
    </div>
</section>

{{-- Content --}}
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex gap-12 items-start">

            {{-- Sticky Sidebar TOC --}}
            <aside class="hidden lg:block w-64 flex-shrink-0 sticky top-24">
                <div class="bg-[#FAF7F2] rounded-2xl p-5 border border-gray-100">
                    <h2 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">{{ __('pages.privacy.toc_title') }}</h2>
                    <nav class="space-y-1">
                        <a href="#introduction" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">{{ __('pages.privacy.toc_intro') }}</a>
                        <a href="#information-we-collect" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">{{ __('pages.privacy.toc_collect') }}</a>
                        <a href="#how-we-use" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">{{ __('pages.privacy.toc_use') }}</a>
                        <a href="#data-sharing" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">{{ __('pages.privacy.toc_sharing') }}</a>
                        <a href="#data-retention" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">{{ __('pages.privacy.toc_retention') }}</a>
                        <a href="#your-rights" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">{{ __('pages.privacy.toc_rights') }}</a>
                        <a href="#cookies" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">{{ __('pages.privacy.toc_cookies') }}</a>
                        <a href="#childrens-privacy-main" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">{{ __('pages.privacy.toc_children') }}</a>
                        <a href="#security" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">{{ __('pages.privacy.toc_security') }}</a>
                        <a href="#contact-us" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">{{ __('pages.privacy.toc_contact') }}</a>
                        <div class="border-t border-gray-200 my-3"></div>
                        <a href="#childrens-privacy" class="block text-sm text-purple-600 hover:text-purple-700 font-medium py-1 transition-colors">{{ __('pages.privacy.toc_children_notice') }}</a>
                    </nav>
                </div>
            </aside>

            {{-- Main Content --}}
            <main class="flex-1 min-w-0 text-gray-700 leading-relaxed">

                <section id="introduction" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.privacy.intro_title') }}</h2>
                    <p class="mb-4">{!! __('pages.privacy.intro_p1') !!}</p>
                    <p class="mb-4">{{ __('pages.privacy.intro_p2') }}</p>
                    <p>{{ __('pages.privacy.intro_p3') }}</p>
                </section>

                <section id="information-we-collect" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.privacy.collect_title') }}</h2>

                    <h3 class="text-lg font-semibold text-gray-900 mb-2 mt-5">{{ __('pages.privacy.collect_account_title') }}</h3>
                    <p class="mb-4">{{ __('pages.privacy.collect_account_p') }}</p>

                    <h3 class="text-lg font-semibold text-gray-900 mb-2 mt-5">{{ __('pages.privacy.collect_usage_title') }}</h3>
                    <p class="mb-4">{{ __('pages.privacy.collect_usage_p') }}</p>

                    <h3 class="text-lg font-semibold text-gray-900 mb-2 mt-5">{{ __('pages.privacy.collect_payment_title') }}</h3>
                    <p class="mb-4">{{ __('pages.privacy.collect_payment_p') }}</p>

                    <h3 class="text-lg font-semibold text-gray-900 mb-2 mt-5">{{ __('pages.privacy.collect_cookies_title') }}</h3>
                    <p>{!! __('pages.privacy.collect_cookies_p', ['cookie' => $ppCookie]) !!}</p>
                </section>

                <section id="how-we-use" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.privacy.use_title') }}</h2>
                    <p class="mb-4">{{ __('pages.privacy.use_intro') }}</p>
                    <ul class="list-disc list-outside pl-5 space-y-2 mb-4">
                        <li>{!! __('pages.privacy.use_li1') !!}</li>
                        <li>{!! __('pages.privacy.use_li2') !!}</li>
                        <li>{!! __('pages.privacy.use_li3') !!}</li>
                        <li>{!! __('pages.privacy.use_li4') !!}</li>
                        <li>{!! __('pages.privacy.use_li5') !!}</li>
                        <li>{!! __('pages.privacy.use_li6') !!}</li>
                    </ul>
                    <p>{{ __('pages.privacy.use_footer') }}</p>
                </section>

                <section id="data-sharing" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.privacy.share_title') }}</h2>
                    <p class="mb-4">{!! __('pages.privacy.share_p1') !!}</p>

                    <h3 class="text-lg font-semibold text-gray-900 mb-2 mt-5">{{ __('pages.privacy.share_sp_title') }}</h3>
                    <p class="mb-4">{{ __('pages.privacy.share_sp_intro') }}</p>
                    <ul class="list-disc list-outside pl-5 space-y-2 mb-4">
                        <li>{!! __('pages.privacy.share_sp_li1') !!}</li>
                        <li>{!! __('pages.privacy.share_sp_li2') !!}</li>
                        <li>{!! __('pages.privacy.share_sp_li3') !!}</li>
                    </ul>

                    <h3 class="text-lg font-semibold text-gray-900 mb-2 mt-5">{{ __('pages.privacy.share_legal_title') }}</h3>
                    <p class="mb-4">{{ __('pages.privacy.share_legal_p') }}</p>

                    <h3 class="text-lg font-semibold text-gray-900 mb-2 mt-5">{{ __('pages.privacy.share_biz_title') }}</h3>
                    <p>{{ __('pages.privacy.share_biz_p') }}</p>
                </section>

                <section id="data-retention" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.privacy.ret_title') }}</h2>
                    <p class="mb-4">{{ __('pages.privacy.ret_p1') }}</p>
                    <p>{{ __('pages.privacy.ret_p2') }}</p>
                </section>

                <section id="your-rights" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.privacy.rights_title') }}</h2>
                    <p class="mb-4">{{ __('pages.privacy.rights_intro') }}</p>

                    <h3 class="text-lg font-semibold text-gray-900 mb-2 mt-5">{{ __('pages.privacy.rights_all_title') }}</h3>
                    <ul class="list-disc list-outside pl-5 space-y-2 mb-4">
                        <li>{!! __('pages.privacy.rights_all_li1') !!}</li>
                        <li>{!! __('pages.privacy.rights_all_li2') !!}</li>
                        <li>{!! __('pages.privacy.rights_all_li3') !!}</li>
                        <li>{!! __('pages.privacy.rights_all_li4') !!}</li>
                    </ul>

                    <h3 class="text-lg font-semibold text-gray-900 mb-2 mt-5">{{ __('pages.privacy.rights_ccpa_title') }}</h3>
                    <p class="mb-4">{!! __('pages.privacy.rights_ccpa_p', ['email' => $ppEmail]) !!}</p>

                    <h3 class="text-lg font-semibold text-gray-900 mb-2 mt-5">{{ __('pages.privacy.rights_gdpr_title') }}</h3>
                    <p class="mb-4">{{ __('pages.privacy.rights_gdpr_p') }}</p>

                    <p>{!! __('pages.privacy.rights_footer', ['email' => $ppEmail]) !!}</p>
                </section>

                <section id="cookies" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.privacy.cookies_title') }}</h2>
                    <p class="mb-4">{{ __('pages.privacy.cookies_p1') }}</p>
                    <p>{!! __('pages.privacy.cookies_p2', ['cookie' => $ppCookie]) !!}</p>
                </section>

                <section id="childrens-privacy-main" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.privacy.cm_title') }}</h2>
                    <p class="mb-4">{{ __('pages.privacy.cm_p1') }}</p>
                    <p class="mb-4">{{ __('pages.privacy.cm_p2') }}</p>
                    <p>{!! __('pages.privacy.cm_p3', ['section' => $ppSection]) !!}</p>
                </section>

                <section id="security" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.privacy.sec_title') }}</h2>
                    <p class="mb-4">{{ __('pages.privacy.sec_intro') }}</p>
                    <ul class="list-disc list-outside pl-5 space-y-2 mb-4">
                        <li>{{ __('pages.privacy.sec_li1') }}</li>
                        <li>{{ __('pages.privacy.sec_li2') }}</li>
                        <li>{{ __('pages.privacy.sec_li3') }}</li>
                        <li>{{ __('pages.privacy.sec_li4') }}</li>
                        <li>{{ __('pages.privacy.sec_li5') }}</li>
                    </ul>
                    <p>{{ __('pages.privacy.sec_footer') }}</p>
                </section>

                <section id="contact-us" class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.privacy.contact_title') }}</h2>
                    <p class="mb-4">{{ __('pages.privacy.contact_intro') }}</p>
                    <div class="bg-[#FAF7F2] rounded-xl p-5 border border-gray-100">
                        <p class="font-semibold text-gray-900 mb-1">{{ __('pages.privacy.contact_company') }}</p>
                        <p class="text-gray-700">{!! __('pages.privacy.contact_address') !!}</p>
                        <p class="mt-3"><a href="mailto:support@harmoniva.app" class="text-purple-600 font-medium hover:text-purple-700 transition-colors">support@harmoniva.app</a></p>
                    </div>
                    <p class="mt-4 text-sm text-gray-500">{{ __('pages.privacy.contact_footer') }}</p>
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
            <div class="flex items-center gap-2 px-4 py-2 rounded-full bg-purple-100 text-purple-700 text-sm font-semibold">
                <i data-lucide="shield" class="w-4 h-4"></i>
                {{ __('pages.privacy.cn_badge') }}
            </div>
            <div class="flex-1 h-px bg-gray-300"></div>
        </div>
    </div>
</div>

{{-- Children's Privacy Notice --}}
<section id="childrens-privacy" class="py-16 bg-[#FAF7F2]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex gap-12 items-start">

            {{-- Sidebar --}}
            <aside class="hidden lg:block w-64 flex-shrink-0 sticky top-24">
                <div class="bg-white rounded-2xl p-5 border border-gray-100">
                    <h2 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">{{ __('pages.privacy.cn_side_title') }}</h2>
                    <nav class="space-y-1">
                        <a href="#coppa-compliance" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">{{ __('pages.privacy.cn_toc_coppa') }}</a>
                        <a href="#data-from-minors" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">{{ __('pages.privacy.cn_toc_data') }}</a>
                        <a href="#parental-rights" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">{{ __('pages.privacy.cn_toc_rights') }}</a>
                        <a href="#deletion-requests" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">{{ __('pages.privacy.cn_toc_deletion') }}</a>
                        <a href="#parent-contact" class="block text-sm text-gray-600 hover:text-purple-600 py-1 transition-colors">{{ __('pages.privacy.cn_toc_contact') }}</a>
                    </nav>
                </div>
            </aside>

            {{-- Main Content --}}
            <main class="flex-1 min-w-0 text-gray-700 leading-relaxed">
                <div class="mb-8">
                    <h2 class="text-3xl font-extrabold text-gray-900 mb-2">{{ __('pages.privacy.cn_h') }}</h2>
                    <p class="text-gray-500">{{ __('pages.privacy.cn_sub') }}</p>
                </div>

                <section id="coppa-compliance" class="mb-10">
                    <h3 class="text-xl font-bold text-gray-900 mb-3">{{ __('pages.privacy.coppa_title') }}</h3>
                    <p class="mb-4">{{ __('pages.privacy.coppa_p1') }}</p>
                    <p class="mb-4">{!! __('pages.privacy.coppa_p2', ['email' => $ppEmail]) !!}</p>
                    <p>{{ __('pages.privacy.coppa_p3') }}</p>
                </section>

                <section id="data-from-minors" class="mb-10">
                    <h3 class="text-xl font-bold text-gray-900 mb-3">{{ __('pages.privacy.data_title') }}</h3>
                    <p class="mb-4">{{ __('pages.privacy.data_intro') }}</p>
                    <ul class="list-disc list-outside pl-5 space-y-2 mb-4">
                        <li>{!! __('pages.privacy.data_li1') !!}</li>
                        <li>{!! __('pages.privacy.data_li2') !!}</li>
                        <li>{!! __('pages.privacy.data_li3') !!}</li>
                    </ul>
                    <p class="mb-4">{!! __('pages.privacy.data_p1') !!}</p>
                    <p>{!! __('pages.privacy.data_p2') !!}</p>
                </section>

                <section id="parental-rights" class="mb-10">
                    <h3 class="text-xl font-bold text-gray-900 mb-3">{{ __('pages.privacy.prights_title') }}</h3>
                    <p class="mb-4">{{ __('pages.privacy.prights_intro') }}</p>
                    <ul class="list-disc list-outside pl-5 space-y-2 mb-4">
                        <li>{!! __('pages.privacy.prights_li1') !!}</li>
                        <li>{!! __('pages.privacy.prights_li2') !!}</li>
                        <li>{!! __('pages.privacy.prights_li3') !!}</li>
                        <li>{!! __('pages.privacy.prights_li4') !!}</li>
                        <li>{!! __('pages.privacy.prights_li5') !!}</li>
                    </ul>
                    <p>{{ __('pages.privacy.prights_footer') }}</p>
                </section>

                <section id="deletion-requests" class="mb-10">
                    <h3 class="text-xl font-bold text-gray-900 mb-3">{{ __('pages.privacy.del_title') }}</h3>
                    <p class="mb-4">{{ __('pages.privacy.del_intro') }}</p>
                    <ol class="list-decimal list-outside pl-5 space-y-2 mb-4">
                        <li>{!! __('pages.privacy.del_s1', ['email' => $ppEmail]) !!}</li>
                        <li>{{ __('pages.privacy.del_s2') }}</li>
                        <li>{{ __('pages.privacy.del_s3') }}</li>
                        <li>{{ __('pages.privacy.del_s4') }}</li>
                    </ol>
                    <p class="text-sm text-gray-500">{{ __('pages.privacy.del_footer') }}</p>
                </section>

                <section id="parent-contact" class="mb-4">
                    <h3 class="text-xl font-bold text-gray-900 mb-3">{{ __('pages.privacy.pcontact_title') }}</h3>
                    <p class="mb-4">{{ __('pages.privacy.pcontact_intro') }}</p>
                    <div class="bg-white rounded-xl p-5 border border-gray-100">
                        <p class="font-semibold text-gray-900 mb-1">{{ __('pages.privacy.pcontact_company') }}</p>
                        <p class="text-gray-700">{!! __('pages.privacy.pcontact_address') !!}</p>
                        <p class="mt-3">
                            {{ __('pages.privacy.pcontact_email_label') }} <a href="mailto:support@harmoniva.app" class="text-purple-600 font-medium hover:text-purple-700 transition-colors">support@harmoniva.app</a><br>
                            <span class="text-sm text-gray-500">{{ __('pages.privacy.pcontact_subject') }}</span>
                        </p>
                    </div>
                    <p class="mt-4 text-sm text-gray-500">{{ __('pages.privacy.pcontact_footer') }}</p>
                </section>
            </main>
        </div>
    </div>
</section>

@endsection
