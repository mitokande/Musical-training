{{--
    Shared SEO head block for the guest-accessible /practice/{slug} exercises.
    Expects $slug in scope. Metadata comes from config/practice_seo.php.
    These pages are indexable and listed in the sitemap, so each gets a unique
    title, description, canonical, Open Graph/Twitter cards, and JSON-LD.
--}}
@php
    $seo = config('practice_seo.'.($slug ?? ''), [
        'name' => 'Ear Training Practice',
        'title' => 'Ear Training Practice',
        'description' => 'Free online ear-training exercise on Harmoniva — train your musical ear with instant feedback.',
    ]);
    $practiceUrl = route('practice', $slug);
    $practiceName = $seo['name'];
    $practiceDescription = $seo['description'];

    // Built inside @php so Blade does not compile the "@context"/"@type"
    // literal keys as its own directives and corrupt the JSON.
    $practiceJsonLd = json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'LearningResource',
        'name' => $practiceName,
        'description' => $practiceDescription,
        'url' => $practiceUrl,
        'learningResourceType' => 'Exercise',
        'educationalUse' => 'Practice',
        'interactivityType' => 'active',
        'educationalLevel' => 'Beginner to Advanced',
        'teaches' => 'Ear training and aural music skills',
        'isAccessibleForFree' => true,
        'inLanguage' => str_replace('_', '-', app()->getLocale()),
        'provider' => [
            '@type' => 'Organization',
            'name' => 'Harmoniva',
            'url' => url('/'),
            'logo' => asset('images/logo-full.png'),
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    $breadcrumbJsonLd = json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Learning Path', 'item' => route('learn')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $practiceName, 'item' => $practiceUrl],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp
<meta name="description" content="{{ $practiceDescription }}">
<link rel="canonical" href="{{ $practiceUrl }}">
<meta property="og:type" content="website">
<meta property="og:site_name" content="Harmoniva">
<meta property="og:title" content="{{ $practiceName }} — Harmoniva">
<meta property="og:description" content="{{ $practiceDescription }}">
<meta property="og:url" content="{{ $practiceUrl }}">
<meta property="og:image" content="{{ asset('images/og-image.png') }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $practiceName }} — Harmoniva">
<meta name="twitter:description" content="{{ $practiceDescription }}">
<meta name="twitter:image" content="{{ asset('images/og-image.png') }}">
<script type="application/ld+json">{!! $practiceJsonLd !!}</script>
<script type="application/ld+json">{!! $breadcrumbJsonLd !!}</script>
