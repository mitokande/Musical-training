@extends('layouts.standalone')

@section('title', 'Cookie Policy — Harmoniva')
@section('description', 'Learn how Harmoniva uses cookies and similar technologies on harmoniva.app.')

@section('content')

{{-- Hero --}}
<section class="py-16 bg-gray-900 text-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl sm:text-5xl font-extrabold mb-3">Cookie Policy</h1>
        <p class="text-gray-400 text-lg">Last updated: June 1, 2026</p>
    </div>
</section>

{{-- Content --}}
<section class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-gray-700 leading-relaxed">

        {{-- What Are Cookies --}}
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">What Are Cookies?</h2>
            <p class="mb-4">Cookies are small text files that are placed on your device (computer, phone, or tablet) when you visit a website. They are widely used to make websites work more efficiently, to remember your preferences, and to provide information to website owners.</p>
            <p class="mb-4">Cookies may be "session cookies," which expire when you close your browser, or "persistent cookies," which remain on your device for a set period or until you delete them. We also use similar technologies such as local storage and session storage for functional purposes.</p>
            <p>By using harmoniva.app, you consent to the use of cookies as described in this policy. You can manage or disable cookies at any time using your browser settings, though this may affect some functionality of the Service.</p>
        </div>

        {{-- Types of Cookies --}}
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Types of Cookies We Use</h2>

            <div class="space-y-6">
                <div class="bg-[#FAF7F2] rounded-2xl p-6 border border-gray-100">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-1">
                            <i data-lucide="shield-check" class="w-5 h-5 text-green-600"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-lg mb-1">Essential Cookies</h3>
                            <p class="text-gray-600 text-sm mb-2"><strong>Required for the Service to function.</strong> These cannot be disabled.</p>
                            <p class="text-gray-600 text-sm">These cookies are necessary for you to log in, navigate the platform, and use core features. Without them, the Service cannot function. They do not collect personal information used for marketing purposes.</p>
                        </div>
                    </div>
                </div>

                <div class="bg-[#FAF7F2] rounded-2xl p-6 border border-gray-100">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-1">
                            <i data-lucide="bar-chart-2" class="w-5 h-5 text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-lg mb-1">Performance &amp; Analytics Cookies</h3>
                            <p class="text-gray-600 text-sm mb-2"><strong>Help us understand how you use the Service.</strong></p>
                            <p class="text-gray-600 text-sm">These cookies collect information about which pages you visit, how long you spend on them, and any errors you encounter. The information is aggregated and anonymized where possible. We use this data to improve the performance and content of the Service.</p>
                        </div>
                    </div>
                </div>

                <div class="bg-[#FAF7F2] rounded-2xl p-6 border border-gray-100">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-1">
                            <i data-lucide="settings-2" class="w-5 h-5 text-purple-600"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-lg mb-1">Functional Cookies</h3>
                            <p class="text-gray-600 text-sm mb-2"><strong>Remember your preferences and settings.</strong></p>
                            <p class="text-gray-600 text-sm">These cookies allow the Service to remember choices you've made — such as your language/locale preference — and provide enhanced, personalized features. They may be set by us or by third-party providers whose services we have integrated.</p>
                        </div>
                    </div>
                </div>

                <div class="bg-[#FAF7F2] rounded-2xl p-6 border border-gray-100 opacity-75">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-1">
                            <i data-lucide="ban" class="w-5 h-5 text-gray-500"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-lg mb-1">Marketing Cookies</h3>
                            <p class="text-gray-600 text-sm mb-2"><strong>Not currently used.</strong></p>
                            <p class="text-gray-600 text-sm">We do not currently use marketing or advertising cookies. We do not place tracking cookies for third-party advertising networks, retargeting, or behavioral profiling.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Cookie Table --}}
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-5">Cookies We Use</h2>
            <p class="mb-5 text-gray-600">The following table describes the specific cookies set by harmoniva.app:</p>

            <div class="overflow-x-auto rounded-xl border border-gray-200">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="text-left px-4 py-3 font-semibold text-gray-900">Cookie Name</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-900">Purpose</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-900">Duration</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-900">Type</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 font-mono text-purple-700 font-medium">harmoniva_session</td>
                            <td class="px-4 py-3 text-gray-700">Maintains your login session. Identifies you as a logged-in user between page loads.</td>
                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">Session</td>
                            <td class="px-4 py-3"><span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-medium">Essential</span></td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 font-mono text-purple-700 font-medium">XSRF-TOKEN</td>
                            <td class="px-4 py-3 text-gray-700">Cross-site request forgery protection. Prevents unauthorized form submissions on your behalf.</td>
                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">Session</td>
                            <td class="px-4 py-3"><span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-medium">Essential</span></td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 font-mono text-purple-700 font-medium">remember_web_*</td>
                            <td class="px-4 py-3 text-gray-700">"Remember me" token. Keeps you logged in across browser sessions when you select "Remember me" at login.</td>
                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">5 years</td>
                            <td class="px-4 py-3"><span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-medium">Essential</span></td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 font-mono text-purple-700 font-medium">locale</td>
                            <td class="px-4 py-3 text-gray-700">Stores your preferred interface language/locale so it persists across sessions.</td>
                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">1 year</td>
                            <td class="px-4 py-3"><span class="px-2 py-0.5 bg-purple-100 text-purple-700 rounded-full text-xs font-medium">Functional</span></td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 font-mono text-purple-700 font-medium">csrf_token</td>
                            <td class="px-4 py-3 text-gray-700">Additional CSRF token used by Livewire components to secure real-time interactions.</td>
                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">Session</td>
                            <td class="px-4 py-3"><span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-medium">Essential</span></td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 font-mono text-purple-700 font-medium">_ga, _gid</td>
                            <td class="px-4 py-3 text-gray-700">Google Analytics — used to distinguish users and track page visits in aggregate. Not linked to personal identity.</td>
                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">2 years / 24h</td>
                            <td class="px-4 py-3"><span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">Analytics</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- How to Manage Cookies --}}
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">How to Manage Cookies</h2>
            <p class="mb-4">Most browsers allow you to control cookies through their settings. You can usually find these settings in the "Options," "Preferences," or "Settings" menu of your browser. You can choose to block or delete cookies at any time.</p>
            <p class="mb-6 text-amber-700 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-sm">
                <strong>Please note:</strong> Disabling essential cookies will prevent you from logging in and using core features of the Service. We recommend only disabling non-essential cookies (analytics, functional) if you have privacy concerns.
            </p>

            <h3 class="text-lg font-semibold text-gray-900 mb-3">Browser Cookie Settings</h3>
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
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Questions About Our Cookie Use?</h2>
            <p class="mb-4">If you have any questions about this Cookie Policy or our use of cookies, please contact us:</p>
            <div class="bg-[#FAF7F2] rounded-xl p-5 border border-gray-100">
                <p class="font-semibold text-gray-900 mb-1">H&amp;P LLC — Harmoniva</p>
                <p class="text-gray-700">8 The Green STE B, Dover, DE 19901, United States</p>
                <p class="mt-2"><a href="mailto:support@harmoniva.app" class="text-purple-600 font-medium hover:text-purple-700 transition-colors">support@harmoniva.app</a></p>
            </div>
            <p class="mt-4 text-sm text-gray-500">This Cookie Policy may be updated from time to time. We will post the updated policy on this page with a revised "last updated" date.</p>
        </div>

    </div>
</section>

@endsection
