@extends('layouts.standalone')

@section('title', __('pages.cookie.meta_title'))
@section('description', __('pages.cookie.meta_description'))

@section('content')

{{-- Hero --}}
<section class="py-16 bg-gray-900 text-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl sm:text-5xl font-extrabold mb-3">{{ __('pages.cookie.hero_title') }}</h1>
        <p class="text-gray-400 text-lg">{{ __('pages.cookie.updated') }}</p>
    </div>
</section>

{{-- Content --}}
<section class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-gray-700 leading-relaxed">

        {{-- What Are Cookies --}}
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.cookie.what_title') }}</h2>
            <p class="mb-4">{{ __('pages.cookie.what_p1') }}</p>
            <p class="mb-4">{{ __('pages.cookie.what_p2') }}</p>
            <p>{{ __('pages.cookie.what_p3') }}</p>
        </div>

        {{-- Types of Cookies --}}
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('pages.cookie.types_title') }}</h2>

            <div class="space-y-6">
                @php $cookieTypes = [
                    ['icon' => 'shield-check', 'bg' => 'bg-green-100', 'fg' => 'text-green-600', 'opacity' => '', 'title' => __('pages.cookie.type_essential_title'), 'lead' => __('pages.cookie.type_essential_lead'), 'desc' => __('pages.cookie.type_essential_desc')],
                    ['icon' => 'bar-chart-2', 'bg' => 'bg-blue-100', 'fg' => 'text-blue-600', 'opacity' => '', 'title' => __('pages.cookie.type_perf_title'), 'lead' => __('pages.cookie.type_perf_lead'), 'desc' => __('pages.cookie.type_perf_desc')],
                    ['icon' => 'settings-2', 'bg' => 'bg-purple-100', 'fg' => 'text-purple-600', 'opacity' => '', 'title' => __('pages.cookie.type_func_title'), 'lead' => __('pages.cookie.type_func_lead'), 'desc' => __('pages.cookie.type_func_desc')],
                    ['icon' => 'ban', 'bg' => 'bg-gray-100', 'fg' => 'text-gray-500', 'opacity' => ' opacity-75', 'title' => __('pages.cookie.type_mkt_title'), 'lead' => __('pages.cookie.type_mkt_lead'), 'desc' => __('pages.cookie.type_mkt_desc')],
                ]; @endphp
                @foreach ($cookieTypes as $ct)
                <div class="bg-[#FAF7F2] rounded-2xl p-6 border border-gray-100{{ $ct['opacity'] }}">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 {{ $ct['bg'] }} rounded-lg flex items-center justify-center flex-shrink-0 mt-1">
                            <i data-lucide="{{ $ct['icon'] }}" class="w-5 h-5 {{ $ct['fg'] }}"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-lg mb-1">{{ $ct['title'] }}</h3>
                            <p class="text-gray-600 text-sm mb-2">{!! $ct['lead'] !!}</p>
                            <p class="text-gray-600 text-sm">{{ $ct['desc'] }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Cookie Table --}}
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-5">{{ __('pages.cookie.table_title') }}</h2>
            <p class="mb-5 text-gray-600">{{ __('pages.cookie.table_intro') }}</p>

            <div class="overflow-x-auto rounded-xl border border-gray-200">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="text-left px-4 py-3 font-semibold text-gray-900">{{ __('pages.cookie.col_name') }}</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-900">{{ __('pages.cookie.col_purpose') }}</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-900">{{ __('pages.cookie.col_duration') }}</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-900">{{ __('pages.cookie.col_type') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @php
                        $badges = [
                            'essential' => ['label' => __('pages.cookie.type_essential_badge'), 'cls' => 'bg-green-100 text-green-700'],
                            'functional' => ['label' => __('pages.cookie.type_functional_badge'), 'cls' => 'bg-purple-100 text-purple-700'],
                            'analytics' => ['label' => __('pages.cookie.type_analytics_badge'), 'cls' => 'bg-blue-100 text-blue-700'],
                        ];
                        $cookieRows = [
                            ['name' => 'harmoniva_session', 'purpose' => __('pages.cookie.p_session'), 'duration' => __('pages.cookie.dur_session'), 'type' => 'essential'],
                            ['name' => 'XSRF-TOKEN', 'purpose' => __('pages.cookie.p_xsrf'), 'duration' => __('pages.cookie.dur_session'), 'type' => 'essential'],
                            ['name' => 'remember_web_*', 'purpose' => __('pages.cookie.p_remember'), 'duration' => __('pages.cookie.dur_5y'), 'type' => 'essential'],
                            ['name' => 'locale', 'purpose' => __('pages.cookie.p_locale'), 'duration' => __('pages.cookie.dur_1y'), 'type' => 'functional'],
                            ['name' => 'csrf_token', 'purpose' => __('pages.cookie.p_csrf'), 'duration' => __('pages.cookie.dur_session'), 'type' => 'essential'],
                            ['name' => '_ga, _gid', 'purpose' => __('pages.cookie.p_ga'), 'duration' => __('pages.cookie.dur_ga'), 'type' => 'analytics'],
                        ];
                        @endphp
                        @foreach ($cookieRows as $row)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 font-mono text-purple-700 font-medium">{{ $row['name'] }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $row['purpose'] }}</td>
                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $row['duration'] }}</td>
                            <td class="px-4 py-3"><span class="px-2 py-0.5 {{ $badges[$row['type']]['cls'] }} rounded-full text-xs font-medium">{{ $badges[$row['type']]['label'] }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- How to Manage Cookies --}}
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.cookie.manage_title') }}</h2>
            <p class="mb-4">{{ __('pages.cookie.manage_p1') }}</p>
            <p class="mb-6 text-amber-700 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-sm">
                {!! __('pages.cookie.manage_note') !!}
            </p>

            <h3 class="text-lg font-semibold text-gray-900 mb-3">{{ __('pages.cookie.manage_sub') }}</h3>
            <ul class="space-y-2.5">
                <li class="flex items-center gap-3">
                    <i data-lucide="external-link" class="w-4 h-4 text-gray-400 flex-shrink-0"></i>
                    <a href="https://support.google.com/chrome/answer/95647" target="_blank" rel="noopener noreferrer" class="text-purple-600 hover:text-purple-700 underline transition-colors">Google Chrome</a>
                </li>
                <li class="flex items-center gap-3">
                    <i data-lucide="external-link" class="w-4 h-4 text-gray-400 flex-shrink-0"></i>
                    <a href="https://support.mozilla.org/en-US/kb/enhanced-tracking-protection-firefox-desktop" target="_blank" rel="noopener noreferrer" class="text-purple-600 hover:text-purple-700 underline transition-colors">Mozilla Firefox</a>
                </li>
                <li class="flex items-center gap-3">
                    <i data-lucide="external-link" class="w-4 h-4 text-gray-400 flex-shrink-0"></i>
                    <a href="https://support.apple.com/guide/safari/manage-cookies-sfri11471/mac" target="_blank" rel="noopener noreferrer" class="text-purple-600 hover:text-purple-700 underline transition-colors">Apple Safari</a>
                </li>
                <li class="flex items-center gap-3">
                    <i data-lucide="external-link" class="w-4 h-4 text-gray-400 flex-shrink-0"></i>
                    <a href="https://support.microsoft.com/en-us/microsoft-edge/delete-cookies-in-microsoft-edge-63947406-40ac-c3b8-57b9-2a946a29ae09" target="_blank" rel="noopener noreferrer" class="text-purple-600 hover:text-purple-700 underline transition-colors">Microsoft Edge</a>
                </li>
            </ul>
        </div>

        {{-- Contact --}}
        <div class="mb-4">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('pages.cookie.contact_title') }}</h2>
            <p class="mb-4">{{ __('pages.cookie.contact_intro') }}</p>
            <div class="bg-[#FAF7F2] rounded-xl p-5 border border-gray-100">
                <p class="font-semibold text-gray-900 mb-1">{{ __('pages.cookie.contact_company') }}</p>
                <p class="text-gray-700">{{ __('pages.cookie.contact_address') }}</p>
                <p class="mt-2"><a href="mailto:support@harmoniva.app" class="text-purple-600 font-medium hover:text-purple-700 transition-colors">support@harmoniva.app</a></p>
            </div>
            <p class="mt-4 text-sm text-gray-500">{{ __('pages.cookie.contact_footer') }}</p>
        </div>

    </div>
</section>

@endsection
