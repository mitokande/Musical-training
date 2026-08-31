<?php

/*
 * Payment / checkout configuration.
 *
 * The active gateway is resolved from `driver`. Today only the `manual`
 * gateway ships (no external provider wired): it drives the full checkout →
 * subscription → invoice → entitlement flow so the experience is complete and
 * demoable, while the actual charge is either auto-confirmed (test) or left
 * pending for an admin to confirm. A real provider (Paddle, iyzico, Stripe…)
 * slots in later by implementing App\Services\Payments\Contracts\PaymentGateway
 * and registering itself in PaymentManager::$gateways.
 */

return [

    // Active gateway key: manual (default) | paddle | iyzico | stripe (future).
    'driver' => env('PAYMENT_DRIVER', 'manual'),

    // Master switch for the paid checkout. While the Stripe account is still
    // unverified it cannot charge a real card, so /checkout must not be
    // reachable: it redirects to the pricing page (which offers the free trial
    // instead). Flip to true on go-live — nothing else has to change.
    'checkout_enabled' => (bool) env('CHECKOUT_ENABLED', false),

    // Free-trial offer: full Premium for a fixed number of days, no card taken.
    // Opt-in — a user claims it once, ever. `enabled = false` hides every trial
    // CTA and makes the endpoint refuse.
    'trial' => [
        'enabled' => (bool) env('TRIAL_ENABLED', true),
        'days' => (int) env('TRIAL_DAYS', 15),
    ],

    // Display + charge currency. Public pricing is USD.
    'currency' => env('PAYMENT_CURRENCY', 'USD'),
    'currency_symbol' => '$',

    // VAT/sales tax rate applied on top of the plan price at checkout.
    // 0 = tax-inclusive / no separate tax line. Kept configurable for when a
    // Merchant-of-Record or local-tax setup is chosen.
    'tax_rate' => (float) env('PAYMENT_TAX_RATE', 0),

    // Money-back window advertised on the refund policy page.
    'refund_days' => 14,

    // Billing cycles offered at checkout and their period length.
    'cycles' => [
        'monthly' => ['label' => 'Monthly', 'months' => 1],
        'yearly' => ['label' => 'Yearly', 'months' => 12],
    ],

    // In-app purchases relayed from Adapty. Never the `driver`: nothing here is
    // charged by us, the stores bill the customer and the webhook tells us what
    // happened (see App\Services\Payments\AdaptyEventProcessor). Credentials
    // live in config/services.php with the other third-party secrets.
    'adapty' => [
        // vendor product id => billing cycle, for the subscriptions ledger.
        // Anything not listed falls back to reading the period out of the
        // product id, and then out of the purchase → expiry distance the event
        // carries, so an unlisted product still records a sensible cycle.
        //
        // Note the app sells a weekly plan the web checkout does not: 'weekly'
        // is a valid value here and is stored as-is rather than rounded to the
        // nearest thing /checkout offers.
        'products' => [
            // 'com.harmoniva.premium.weekly' => 'weekly',
            // 'com.harmoniva.premium.monthly' => 'monthly',
            // 'com.harmoniva.premium.yearly' => 'yearly',
        ],
    ],

    'manual' => [
        // true  → checkout instantly activates Premium (test / staging).
        // false → subscription is created 'pending'; an admin confirms payment
        //         from the admin panel before Premium is granted.
        'auto_confirm' => (bool) env('PAYMENT_MANUAL_AUTO_CONFIRM', true),
    ],
];
