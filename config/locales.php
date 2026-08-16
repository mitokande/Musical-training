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

    /*
     * public_pages path => the `pages.*` translation section its blade reads.
     * locale_page_translated() uses this to tell whether a localized URL really
     * carries translated copy: a /{locale} route whose section is missing (or
     * mostly missing) renders English at a second URL, which Google reads as a
     * duplicate and folds into the English page. Such a URL must canonicalise
     * to English, drop out of the hreflang set, and stay out of the sitemap
     * until its translation lands — at which point it turns itself back on.
     */
    'page_sections' => [
        '/pricing' => 'pricing',
        '/students' => 'students',
        '/teachers' => 'teachers',
        '/schools' => 'schools',
        '/piano-learners' => 'piano_learners',
        '/community-feed' => 'community',
        '/request-demo' => 'request_demo',
        '/faq' => 'faq',
        '/contact' => 'contact',
        '/help' => 'help',
        '/about' => 'about',
        '/press' => 'press',
        '/partners' => 'partners',
        '/music-theory-basics' => 'music_theory',
        '/blog' => 'articles',
        '/find-teachers' => 'find_teachers',
        '/ear-training-guide' => 'ear_guide',
        '/how-it-works' => 'how_it_works',
        '/learn' => 'learn',
        '/pricing/teachers-and-schools' => 'pricing_teachers',
        '/piano-studio' => 'piano_studio',
        '/refund-policy' => 'refund',
        '/subscription-terms' => 'subscription',
        '/cookie-policy' => 'cookie',
        '/terms-of-service' => 'terms',
        '/privacy-policy' => 'privacy',
    ],

    /*
     * Share of a section's English keys a locale must define before its URL is
     * advertised as a real translation. Below this the page is still mostly
     * English fallback copy, so claiming it via hreflang is a duplicate signal.
     */
    'page_translation_threshold' => 0.95,
];
