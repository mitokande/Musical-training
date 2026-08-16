<?php

namespace App\Services\Seo;

use Illuminate\Http\Request;

/**
 * Canonical / hreflang / <html lang> facts for a public page request.
 *
 * Every public template used to recompute this inline (layouts/standalone and
 * partials/public-seo-alt carried two copies of the same @php block, and each
 * standalone <head> guessed its own <html lang>). They are derived here once and
 * View::share()d, so the whole site cannot drift apart again.
 *
 * The rule that matters for indexing: a /{locale} URL is only advertised as a
 * real translation when its `pages.*` section actually exists in that locale.
 * Otherwise the page renders English fallback copy at a second URL — Google reads
 * that as a duplicate, ignores the hreflang claim, and reports "Duplicate, Google
 * chose a different canonical than the user". Those URLs instead canonicalise to
 * English and are left out of the alternate set until their translation lands.
 */
class PublicPageSeo
{
    /**
     * @return array{
     *     seoBasePath: string,
     *     seoCurrentLocale: string,
     *     seoIsLocalized: bool,
     *     seoTranslated: bool,
     *     seoCanonical: string,
     *     seoAlternates: array<string, string>,
     *     seoHtmlLang: string,
     *     seoOgLocale: string
     * }
     */
    public function forRequest(Request $request): array
    {
        $prefixed = (array) config('locales.prefixed');
        $publicPages = array_keys((array) config('locales.public_pages'));
        $segments = $request->segments();

        $hasLocalePrefix = isset($segments[0]) && in_array($segments[0], $prefixed, true);

        $basePath = $hasLocalePrefix
            ? '/'.implode('/', array_slice($segments, 1))
            : '/'.implode('/', $segments);
        $basePath = rtrim($basePath, '/') ?: '/';

        // The locale comes from the URL itself, never app()->getLocale(): an IP
        // geolocation guess would otherwise make the un-prefixed English URL
        // canonicalise to /de and drop out of the index entirely.
        $currentLocale = $hasLocalePrefix ? $segments[0] : 'en';

        // Blog posts are localized like the static public pages, but come from
        // their own registry (config('blog.posts')) rather than public_pages.
        $isLocalized = in_array($basePath, $publicPages, true)
            || blog_post_for_path($basePath) !== null;
        $translated = $isLocalized && locale_page_translated($basePath, $currentLocale);

        // Untranslated locale variants point at the English original.
        $canonicalLocale = $translated ? $currentLocale : 'en';
        $canonical = locale_url($basePath, $isLocalized ? $canonicalLocale : 'en');

        // Alternates are only claimed on a page that is itself part of the
        // translated cluster, and only for locales that really have the copy.
        $alternates = [];
        if ($isLocalized && ($translated || $currentLocale === 'en')) {
            $alternates['en'] = locale_url($basePath, 'en');
            foreach ($prefixed as $locale) {
                if (locale_page_translated($basePath, $locale)) {
                    $alternates[$locale] = locale_url($basePath, $locale);
                }
            }
        }

        // A single-entry set is just the English page talking to itself — no
        // alternates to declare.
        if (count($alternates) < 2) {
            $alternates = [];
        }

        // The page renders English copy whenever the translation is missing, so
        // <html lang> and og:locale must say English rather than advertise a
        // language the body does not speak.
        $contentLocale = $isLocalized && ! $translated ? 'en' : $currentLocale;

        return [
            'seoBasePath' => $basePath,
            'seoCurrentLocale' => $currentLocale,
            'seoIsLocalized' => $isLocalized,
            'seoTranslated' => $translated,
            'seoCanonical' => $canonical,
            'seoAlternates' => $alternates,
            'seoHtmlLang' => str_replace('_', '-', $contentLocale),
            'seoOgLocale' => config('locales.og')[$contentLocale] ?? 'en_US',
        ];
    }
}
