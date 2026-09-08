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
        // Recurring Price ids for the Premium plan. Role-keyed so each tier
        // (individual user, teacher, school) is billed its own amount; the flat
        // 'monthly'/'yearly' keys are kept as a legacy fallback for the user tier.
        'prices' => [
            'monthly' => env('STRIPE_PRICE_MONTHLY'),
            'yearly' => env('STRIPE_PRICE_YEARLY'),
            'user' => [
                'monthly' => env('STRIPE_PRICE_MONTHLY'),
                'yearly' => env('STRIPE_PRICE_YEARLY'),
            ],
            'teacher' => [
                'monthly' => env('STRIPE_PRICE_TEACHER_MONTHLY'),
                'yearly' => env('STRIPE_PRICE_TEACHER_YEARLY'),
            ],
            'school' => [
                'monthly' => env('STRIPE_PRICE_SCHOOL_MONTHLY'),
                'yearly' => env('STRIPE_PRICE_SCHOOL_YEARLY'),
            ],
        ],
    ],

    // Adapty — the mobile app's in-app purchases. Apple and Google take the
    // money; Adapty watches both stores and posts the subscription lifecycle to
    // /webhooks/adapty, which is the only place this server learns about a
    // renewal, a cancellation made in the store's own settings, or a refund.
    // The SDK key in the app is public by design and is NOT one of these.
    'adapty' => [
        // The shared secret Adapty presents on every call. Configure the same
        // header name and value under Dashboard → Integrations → Webhook.
        // Without it the endpoint refuses everything, so a misconfiguration is
        // an outage, never an open door.
        'webhook_header' => env('ADAPTY_WEBHOOK_HEADER', 'Authorization'),
        'webhook_secret' => env('ADAPTY_WEBHOOK_SECRET'),

        // Optional second gate: an HMAC-SHA256 of the raw body, for workspaces
        // that have request signing switched on. Left empty, the header above is
        // the only check.
        'signing_secret' => env('ADAPTY_WEBHOOK_SIGNING_SECRET'),
        'signature_header' => env('ADAPTY_WEBHOOK_SIGNATURE_HEADER', 'Adapty-Signature'),

        // The access level the products grant, as configured in the Adapty
        // dashboard. Must match PREMIUM_ACCESS_LEVEL in the mobile app
        // (src/billing/entitlement.ts) — events for any other level are ignored.
        'access_level' => env('ADAPTY_ACCESS_LEVEL', 'premium'),

        // Sandbox purchases cost nothing and anyone with a TestFlight or
        // internal-testing build can make one. Leave this on while testing;
        // turn it off in production so a tester cannot grant themselves Premium
        // on the live database.
        'accept_sandbox' => (bool) env('ADAPTY_ACCEPT_SANDBOX', true),
    ],

    // Google — the web sign-in (Socialite's redirect flow, SocialAuthController)
    // and the mobile one (a native sign-in on the device, its ID token verified
    // by GoogleIdTokenVerifier) share this project.
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', '/auth/google/callback'),

        // Extra OAuth client ids the mobile app may sign in with, comma
        // separated — the iOS client, and the Android one if you ever stop
        // passing the web client id as serverClientId. These are audiences we
        // accept ID tokens for; anything not listed here (or in client_id
        // above) is refused, which is what stops a token minted for someone
        // else's app being spent on an account here. Public values, not
        // secrets, but the list must be exact.
        'mobile_client_ids' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('GOOGLE_MOBILE_CLIENT_IDS', '')),
        ))),
    ],

    // Apple — the native Sign in with Apple in the mobile app. There is no
    // secret and no redirect here: the device does the whole flow with Apple
    // and posts the identity token, which AppleIdTokenVerifier checks. A web
    // flow would need a Services ID and a signing key; nothing here uses one.
    'apple' => [
        // Bundle identifiers whose tokens this server accepts, comma
        // separated: the App Store build and the dev variant. Public values,
        // not secrets, but the list must be exact — it is the only thing
        // stopping a token minted for someone else's app being spent here, so
        // never widen it to get a build working.
        'client_ids' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('APPLE_CLIENT_IDS', '')),
        ))),
    ],

    // Zoom live lessons. Two separate Marketplace apps are required:
    //   • Server-to-Server OAuth — creates/updates/deletes meetings on the
    //     pooled host accounts and issues host ZAK tokens.
    //     Scopes: meeting:write:admin, meeting:read:admin, user:read:admin,
    //     user:write:admin.
    //   • Meeting SDK — signs the browser JWT the embedded Lesson Room joins
    //     with. Its key/secret cannot be used for REST calls, and the S2S
    //     credentials cannot sign SDK JWTs, so both pairs are needed.
    // Behaviour flags live in config/zoom.php. Secrets stay in .env only; the
    // client secret must never be rendered into a page.
    'zoom' => [
        'account_id' => env('ZOOM_ACCOUNT_ID'),
        'client_id' => env('ZOOM_CLIENT_ID'),
        'client_secret' => env('ZOOM_CLIENT_SECRET'),
        'sdk_key' => env('ZOOM_SDK_KEY'),
        'sdk_secret' => env('ZOOM_SDK_SECRET'),
    ],

];
