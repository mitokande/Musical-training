@extends('layouts.standalone')

@section('title', 'Refund Policy — Harmoniva')
@section('description', 'Harmoniva\'s refund policy. 14-day money-back guarantee on Premium plans. Learn how to request a refund and what to expect.')

@section('content')

{{-- Hero --}}
<section class="py-16 bg-gray-900 text-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl sm:text-5xl font-extrabold mb-3">Refund Policy</h1>
        <p class="text-gray-400 text-lg">Last updated: June 1, 2026</p>
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
                    <h2 class="text-xl font-bold text-green-900 mb-2">14-Day Money-Back Guarantee</h2>
                    <p class="text-green-800 leading-relaxed">If you are not completely satisfied with your Harmoniva Premium subscription, contact us within 14 days of your first payment and we will issue a full refund — no questions asked.</p>
                </div>
            </div>

            <h2 class="text-2xl font-bold text-gray-900 mb-4">Our Commitment to Customer Satisfaction</h2>
            <p class="mb-4">We built Harmoniva because we genuinely believe in what it does for musicians. If you subscribe and find that it's not the right fit for you, we don't want to keep your money. Our refund policy is designed to be fair, clear, and easy to use.</p>
            <p>We stand behind the quality of our product. If something isn't working as expected, or if you feel the Service didn't deliver what was promised, please reach out before requesting a refund — our support team can often resolve issues quickly and we'd love the chance to help.</p>
        </div>

        {{-- 14-Day Guarantee --}}
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">14-Day Money-Back Guarantee</h2>
            <p class="mb-4">New Premium subscribers are eligible for a full refund within <strong>14 calendar days</strong> of their first payment. This applies to:</p>
            <ul class="list-disc list-outside pl-5 space-y-2 mb-4">
                <li>Monthly Premium subscriptions (within 14 days of the first payment)</li>
                <li>Annual Premium subscriptions (within 14 days of the annual payment)</li>
                <li>Any new subscription, whether upgraded from a free account or starting fresh</li>
            </ul>
            <p class="mb-4">The 14-day window begins on the date your payment is processed, not the date you first log in. If your payment processes on June 1, your refund window closes at end of day on June 15.</p>
            <p>The money-back guarantee applies to your first payment for each plan tier. Subsequent renewals are not covered by the money-back guarantee, but you may cancel future renewals at any time.</p>
        </div>

        {{-- How to Request --}}
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">How to Request a Refund</h2>
            <p class="mb-5">Requesting a refund is simple and takes less than 2 minutes:</p>

            <div class="space-y-4 mb-6">
                <div class="flex items-start gap-4">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0 font-bold text-purple-700 text-sm mt-0.5">1</div>
                    <div>
                        <p class="font-semibold text-gray-900">Email us within 14 days</p>
                        <p class="text-gray-600 text-sm mt-1">Send an email to <a href="mailto:support@harmoniva.app" class="text-purple-600 hover:text-purple-700 underline transition-colors">support@harmoniva.app</a> with the subject line: <strong>"Refund Request."</strong></p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0 font-bold text-purple-700 text-sm mt-0.5">2</div>
                    <div>
                        <p class="font-semibold text-gray-900">Include your account email</p>
                        <p class="text-gray-600 text-sm mt-1">Tell us the email address associated with your Harmoniva account and the approximate date of your payment. Optionally, let us know why the Service didn't meet your needs — your feedback helps us improve.</p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0 font-bold text-purple-700 text-sm mt-0.5">3</div>
                    <div>
                        <p class="font-semibold text-gray-900">We'll confirm and process</p>
                        <p class="text-gray-600 text-sm mt-1">We will confirm your refund request within 1 business day and begin processing immediately. See "Refund Timeline" below for how long it takes to reach your account.</p>
                    </div>
                </div>
            </div>

            <p class="text-sm text-gray-500 italic">You do not need to cancel your subscription before requesting a refund — we'll handle the cancellation as part of the refund process. However, if you request a refund and then continue using the Service, we may not be able to issue a second refund.</p>
        </div>

        {{-- Non-Refundable --}}
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Non-Refundable Situations</h2>
            <p class="mb-4">Refunds are not available in the following situations:</p>
            <ul class="list-disc list-outside pl-5 space-y-2 mb-4">
                <li><strong>After 14 days</strong>: Refund requests received more than 14 days after the payment date are not eligible, except in cases of documented technical failure on our part.</li>
                <li><strong>Subscription renewals</strong>: Auto-renewal charges are not refundable unless you contact us within 7 days of the renewal and did not use the Service during that renewed period.</li>
                <li><strong>Partial months</strong>: We do not issue prorated refunds for partial subscription periods. If you cancel mid-cycle, you retain access until the end of the paid period.</li>
                <li><strong>Accounts terminated for policy violations</strong>: No refund is available if your account was terminated due to violations of our Terms of Service.</li>
                <li><strong>After extensive use</strong>: In our sole discretion, we may decline a refund if a user has made extensive use of premium features (e.g., completing hundreds of AI-generated exercises) before requesting a refund. We will assess these cases fairly.</li>
            </ul>
            <p>If you believe you have a valid refund claim outside of the above policy (e.g., a billing error, duplicate charge, or technical issue preventing use), please contact us — we will review your case individually.</p>
        </div>

        {{-- Timeline --}}
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Refund Process &amp; Timeline</h2>
            <p class="mb-4">Once your refund is approved:</p>
            <ul class="list-disc list-outside pl-5 space-y-2 mb-4">
                <li>We will cancel your subscription and immediately downgrade your account to the free plan.</li>
                <li>The refund will be issued to your original payment method via Stripe.</li>
                <li>You will receive an email confirmation of the refund.</li>
            </ul>
            <div class="bg-[#FAF7F2] rounded-xl p-5 border border-gray-100 mb-4">
                <div class="flex items-center gap-3 mb-2">
                    <i data-lucide="clock" class="w-5 h-5 text-purple-600"></i>
                    <span class="font-semibold text-gray-900">Typical refund timeline: 5–10 business days</span>
                </div>
                <p class="text-sm text-gray-600">Refunds typically appear on your credit or debit card statement within 5–10 business days of our processing date. The exact timing depends on your card issuer or bank and is outside our control. Some banks may take up to 14 business days.</p>
            </div>
            <p class="text-sm text-gray-500">If you do not see your refund after 14 business days, please check with your bank first. If the issue persists, contact us and we'll investigate with Stripe.</p>
        </div>

        {{-- Contact --}}
        <div class="mb-4">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Contact Us</h2>
            <p class="mb-5">Have a question about a refund? We're here to help.</p>
            <div class="bg-gray-900 rounded-2xl p-6 text-white flex flex-col sm:flex-row items-start sm:items-center gap-5">
                <div class="w-12 h-12 bg-purple-600 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i data-lucide="mail" class="w-6 h-6 text-white"></i>
                </div>
                <div class="flex-1">
                    <p class="font-bold text-white text-lg mb-1">Refund &amp; Billing Support</p>
                    <p class="text-gray-400 text-sm">Response time: within 1 business day, Monday–Friday.</p>
                </div>
                <a href="mailto:support@harmoniva.app" class="inline-flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold px-5 py-2.5 rounded-xl transition-colors duration-200 whitespace-nowrap">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    support@harmoniva.app
                </a>
            </div>
            <p class="mt-6 text-sm text-gray-500">This Refund Policy is subject to our <a href="{{ route('page.terms-of-service') }}" class="text-purple-600 hover:text-purple-700 transition-colors">Terms of Service</a>. For subscription billing details, see our <a href="{{ route('page.subscription-terms') }}" class="text-purple-600 hover:text-purple-700 transition-colors">Subscription Terms</a>.</p>
        </div>

    </div>
</section>

@endsection
