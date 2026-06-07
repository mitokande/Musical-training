@extends('layouts.standalone')

@section('title', 'Subscription Terms — Harmoniva')
@section('description', 'Harmoniva subscription terms: plans, billing cycles, auto-renewal, cancellation policy, and free trial details explained in plain English.')

@section('content')

{{-- Hero --}}
<section class="py-16 bg-gray-900 text-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl sm:text-5xl font-extrabold mb-3">Subscription Terms</h1>
        <p class="text-gray-400 text-lg">Last updated: June 1, 2026</p>
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
                <p class="text-gray-700 leading-relaxed">These Subscription Terms supplement our <a href="{{ route('page.terms-of-service') }}" class="text-purple-600 hover:text-purple-700 underline transition-colors">Terms of Service</a> and govern the billing, renewal, and cancellation of paid subscriptions. If you have any questions, contact us at <a href="mailto:support@harmoniva.app" class="text-purple-600 hover:text-purple-700 underline transition-colors">support@harmoniva.app</a>.</p>
            </div>
        </div>
    </div>
</section>

{{-- Content --}}
<section class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-gray-700 leading-relaxed">

        {{-- Subscription Plans --}}
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-5">Subscription Plans</h2>
            <p class="mb-6">Harmoniva offers the following subscription tiers:</p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6">
                <div class="bg-[#FAF7F2] rounded-2xl p-5 border border-gray-100">
                    <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center mb-3">
                        <i data-lucide="music" class="w-5 h-5 text-gray-600"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-2">Free</h3>
                    <p class="text-sm text-gray-600">No charge. Access to 3 exercises per type per day, 3 saved templates, and standard exercise types. No credit card required.</p>
                </div>

                <div class="bg-purple-50 rounded-2xl p-5 border border-purple-200">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mb-3">
                        <i data-lucide="star" class="w-5 h-5 text-purple-600"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-2">Premium</h3>
                    <p class="text-sm text-gray-600">Unlimited exercises, AI learning paths, advanced configuration, unlimited templates. Available monthly or annually.</p>
                </div>

                <div class="bg-orange-50 rounded-2xl p-5 border border-orange-200">
                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center mb-3">
                        <i data-lucide="users" class="w-5 h-5 text-orange-600"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-2">Teachers &amp; Schools</h3>
                    <p class="text-sm text-gray-600">All Premium features plus teacher dashboards, student management, bulk licensing, and institutional support. Contact us for pricing.</p>
                </div>
            </div>

            <p>Current prices for all plans are displayed on our <a href="{{ route('pricing.index') }}" class="text-purple-600 hover:text-purple-700 underline transition-colors">Pricing page</a>. All prices are in US dollars unless otherwise stated.</p>
        </div>

        {{-- Billing Cycles --}}
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Billing Cycles</h2>
            <p class="mb-4">Paid subscriptions are available on two billing cycles:</p>
            <ul class="list-disc list-outside pl-5 space-y-2 mb-4">
                <li><strong>Monthly</strong>: You are billed once per month on the anniversary of your subscription start date.</li>
                <li><strong>Annual</strong>: You are billed once per year, upfront, for the full 12-month period. Annual plans are offered at a discount compared to the equivalent monthly rate.</li>
            </ul>
            <p>Your first billing date is the date you subscribe. Your billing date remains the same each month or year unless you change your plan. If you upgrade mid-cycle, you will be charged a prorated amount for the remainder of the current period.</p>
        </div>

        {{-- Auto-Renewal --}}
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Auto-Renewal</h2>
            <p class="mb-4">Subscriptions automatically renew at the end of each billing cycle unless you cancel before the renewal date. By subscribing, you authorize Harmoniva (via Stripe) to charge your payment method on a recurring basis.</p>
            <p class="mb-4">We will send you a reminder email at least 7 days before your annual plan renews, giving you time to cancel if you no longer wish to continue. Monthly plans renew without a separate reminder, though your billing schedule is always visible in your account settings.</p>
            <p>To prevent renewal, cancel your subscription at least 24 hours before the next billing date. See "Cancellation Policy" below for instructions.</p>
        </div>

        {{-- Price Changes --}}
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Price Changes</h2>
            <p class="mb-4">We reserve the right to change subscription prices at any time. If we change the price of your current plan, we will notify you by email at least 30 days before the change takes effect.</p>
            <p>Your new price will take effect on your next renewal date after the change. If you do not wish to pay the new price, you may cancel your subscription before the renewal date. Continued use of the Service after the price change constitutes acceptance of the new price.</p>
        </div>

        {{-- Cancellation --}}
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Cancellation Policy</h2>
            <p class="mb-4">You may cancel your subscription at any time. To cancel:</p>
            <ol class="list-decimal list-outside pl-5 space-y-2 mb-4">
                <li>Log in to your Harmoniva account.</li>
                <li>Go to <strong>Account Settings → Subscription</strong>.</li>
                <li>Click "Cancel Subscription" and follow the prompts.</li>
            </ol>
            <p class="mb-4">Alternatively, you may email <a href="mailto:support@harmoniva.app" class="text-purple-600 hover:text-purple-700 underline transition-colors">support@harmoniva.app</a> and request cancellation. We process all cancellation requests within 1 business day.</p>
            <p class="mb-4">When you cancel, your subscription remains active until the end of your current paid period. You will retain full access to Premium features until that date. After your subscription ends, your account will revert to the free plan.</p>
            <p>Cancellation does not automatically trigger a refund. See our <a href="{{ route('page.refund-policy') }}" class="text-purple-600 hover:text-purple-700 underline transition-colors">Refund Policy</a> for details on when refunds are available.</p>
        </div>

        {{-- Refunds --}}
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Refunds</h2>
            <p class="mb-4">Our refund terms are outlined in detail in our <a href="{{ route('page.refund-policy') }}" class="text-purple-600 hover:text-purple-700 underline transition-colors">Refund Policy</a>. In summary:</p>
            <ul class="list-disc list-outside pl-5 space-y-2">
                <li>New subscribers are eligible for a full refund within 14 days of their first payment.</li>
                <li>Refunds are not available after 14 days or for partial billing periods.</li>
                <li>To request a refund, email <a href="mailto:support@harmoniva.app" class="text-purple-600 hover:text-purple-700 underline transition-colors">support@harmoniva.app</a> within the eligible window.</li>
            </ul>
        </div>

        {{-- Upgrades & Downgrades --}}
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Upgrades &amp; Downgrades</h2>

            <h3 class="text-lg font-semibold text-gray-900 mb-2 mt-4">Upgrading Your Plan</h3>
            <p class="mb-4">If you upgrade from a monthly to an annual plan, or from Free to Premium, you will be charged immediately. For mid-cycle upgrades between paid plans, you will be charged a prorated amount for the difference in price for the remainder of the current billing cycle.</p>

            <h3 class="text-lg font-semibold text-gray-900 mb-2 mt-4">Downgrading Your Plan</h3>
            <p class="mb-4">If you downgrade from an annual to a monthly plan, or from Premium to Free, the change takes effect at the end of your current billing cycle. You will not receive a prorated refund for unused time on your current plan, unless you are within the 14-day refund window.</p>
            <p>If you downgrade to the free plan, your Premium features will remain active until the end of your current paid period, after which they will be disabled. Your practice data is retained.</p>
        </div>

        {{-- Free Trial --}}
        <div class="mb-4">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Free Trial</h2>
            <p class="mb-4">When available, free trials give you access to Premium features for a specified period (typically 7 or 14 days) without a charge. At the end of the trial period, your account will automatically be charged for the selected plan unless you cancel before the trial ends.</p>
            <p class="mb-4">Free trials are available to new subscribers only. Each person and payment method is eligible for one free trial. We reserve the right to verify eligibility and revoke trial access if we determine that trial terms are being abused.</p>
            <p class="mb-4">To avoid being charged, cancel your subscription before the trial period ends. You can cancel at any time from your account settings without losing access for the remainder of the trial period.</p>
            <div class="bg-[#FAF7F2] rounded-xl p-5 border border-gray-100 mt-6">
                <p class="text-sm text-gray-600"><strong>Questions about your subscription?</strong> Contact us at <a href="mailto:support@harmoniva.app" class="text-purple-600 font-medium hover:text-purple-700 transition-colors">support@harmoniva.app</a> — we're happy to help.</p>
            </div>
        </div>

    </div>
</section>

@endsection
