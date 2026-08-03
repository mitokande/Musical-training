<?php

return [
    // Every locale the app renders UI in.
    'supported' => ['en', 'es', 'de', 'fr', 'pt', 'tr', 'it'],

    // Locales that receive a /{locale} URL prefix on public pages. English
    // lives at the un-prefixed path and serves as the hreflang x-default.
    'prefixed' => ['es', 'de', 'fr', 'pt', 'tr', 'it'],

    // og:locale value per locale.
    'og' => [
        'en' => 'en_US',
        'es' => 'es_ES',
        'de' => 'de_DE',
        'fr' => 'fr_FR',
        'pt' => 'pt_PT',
        'tr' => 'tr_TR',
        'it' => 'it_IT',
    ],

    /*
     * Public, indexable template pages that have a per-locale variant.
     *   path => view
     * The path is the canonical English URL; the localized variant lives at
     * /{locale}{path} (e.g. /es/pricing). Single source of truth for the
     * localized route group (routes/web.php), the sitemap (SitemapController),
     * and the hreflang/canonical block (layouts/standalone.blade.php). A path
     * only belongs here once its blade has been fully key-extracted, so its
     * localized URLs never surface untranslated English.
     */
    'public_pages' => [
        '/pricing' => 'pages.pricing',
        '/students' => 'pages.students',
        '/teachers' => 'pages.teachers-solution',
        '/schools' => 'pages.schools',
        '/piano-learners' => 'pages.piano-learners',
        '/community-feed' => 'pages.community-feed',
        '/request-demo' => 'pages.request-demo',
        '/faq' => 'pages.faq',
        '/contact' => 'pages.contact',
        '/help' => 'pages.help',
        '/about' => 'pages.about',
        '/press' => 'pages.press',
        '/partners' => 'pages.partners',
        '/music-theory-basics' => 'pages.music-theory-basics',
        '/blog' => 'pages.articles',
        '/find-teachers' => 'pages.find-teachers',
        '/ear-training-guide' => 'pages.ear-training-guide',
        '/how-it-works' => 'pages.how-it-works',
        '/learn' => 'learn',
        '/pricing/teachers-and-schools' => 'pricing-teachers',
        '/piano-studio' => 'piano-studio',
        '/refund-policy' => 'pages.refund-policy',
        '/subscription-terms' => 'pages.subscription-terms',
        '/cookie-policy' => 'pages.cookie-policy',
        '/terms-of-service' => 'pages.terms-of-service',
        '/privacy-policy' => 'pages.privacy-policy',
    ],
];
