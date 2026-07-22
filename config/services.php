<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
    ],

    // Stripe — powers the recurring Premium subscription (config('payments.driver')
    // must be 'stripe' to activate). Keys live only in .env, never in source.
    // Prefer a restricted key (rk_…) scoped to Checkout Sessions, Customers,
    // Subscriptions, Invoices and Refunds over a full secret key (sk_…).
    'stripe' => [
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        // Pinned API version — keeps behaviour stable across SDK upgrades and
        // enables integration_identifier tagging on Checkout Sessions.
        'api_version' => env('STRIPE_API_VERSION', '2026-06-24.dahlia'),
        // Recurring Price ids for the Premium plan, keyed by billing cycle.
        'prices' => [
            'monthly' => env('STRIPE_PRICE_MONTHLY'),
            'yearly' => env('STRIPE_PRICE_YEARLY'),
        ],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', '/auth/google/callback'),
    ],

];
