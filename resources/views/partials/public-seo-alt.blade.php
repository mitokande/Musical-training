{{--
    Per-locale canonical + hreflang alternates for full-HTML public pages that
    do NOT extend layouts.standalone (they have their own <head>). Include it
    inside <head>.

    Everything here comes from App\Services\Seo\PublicPageSeo, shared by the view
    composer in AppServiceProvider — the same values layouts/standalone renders,
    so the two can never disagree. $seoCanonical / $seoHtmlLang / $seoOgLocale are
    available to the including page for its own og:url and <html lang>.
--}}
<link rel="canonical" href="{{ $seoCanonical }}">
@foreach ($seoAlternates as $seoAltLocale => $seoAltUrl)
<link rel="alternate" hreflang="{{ $seoAltLocale }}" href="{{ $seoAltUrl }}">
@endforeach
@if ($seoAlternates !== [])
<link rel="alternate" hreflang="x-default" href="{{ $seoAlternates['en'] }}">
@endif
@php
    // Same WebPage node layouts/standalone emits, for the pages that bring their
    // own <head>. Without it these URLs carried no machine-readable language and
    // two of them (piano-studio, pricing/teachers-and-schools) had no structured
    // data at all. Built inside @php so Blade does not eat the "@context" key.
    $seoWebPageJsonLd = json_encode(array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        '@id' => $seoCanonical.'#webpage',
        'url' => $seoCanonical,
        'name' => $seoPageTitle ?? null,
        'description' => $seoPageDescription ?? null,
        'inLanguage' => $seoHtmlLang,
        'isPartOf' => [
            '@type' => 'WebSite',
            '@id' => url('/').'#website',
            'name' => 'Harmoniva',
            'url' => url('/'),
        ],
        'publisher' => [
            '@type' => 'Organization',
            '@id' => url('/').'#organization',
            'name' => 'Harmoniva',
            'url' => url('/'),
            'logo' => asset('images/logo-full.png'),
        ],
    ]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp
<script type="application/ld+json">{!! $seoWebPageJsonLd !!}</script>
