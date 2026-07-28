{{--
    Per-locale canonical + hreflang alternates for full-HTML public pages that
    do NOT extend layouts.standalone (they have their own <head>). Mirrors the
    logic baked into layouts/standalone.blade.php. Include it inside <head>;
    it also exposes $seoCanonical for the page's og:url / twitter meta.
    Only pages listed in config('locales.public_pages') advertise alternates.
--}}
@php
    $seoPrefixed = config('locales.prefixed');
    $seoPublicPages = array_keys((array) config('locales.public_pages'));
    $seoSegments = request()->segments();
    $seoBasePath = (isset($seoSegments[0]) && in_array($seoSegments[0], $seoPrefixed, true))
        ? '/'.implode('/', array_slice($seoSegments, 1))
        : '/'.implode('/', $seoSegments);
    $seoBasePath = rtrim($seoBasePath, '/') ?: '/';
    $seoIsLocalized = in_array($seoBasePath, $seoPublicPages, true);
    $seoCurrentLocale = app()->getLocale();
    $seoCanonical = locale_url($seoBasePath, $seoIsLocalized ? $seoCurrentLocale : 'en');
    $seoOgLocales = config('locales.og');
@endphp
<link rel="canonical" href="{{ $seoCanonical }}">
@if ($seoIsLocalized)
<link rel="alternate" hreflang="en" href="{{ locale_url($seoBasePath, 'en') }}">
@foreach ($seoPrefixed as $seoAltLocale)
<link rel="alternate" hreflang="{{ $seoAltLocale }}" href="{{ locale_url($seoBasePath, $seoAltLocale) }}">
@endforeach
<link rel="alternate" hreflang="x-default" href="{{ locale_url($seoBasePath, 'en') }}">
@endif
