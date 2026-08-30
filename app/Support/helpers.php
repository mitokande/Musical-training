<?php

use Illuminate\Support\Facades\Lang;

/*
 * Shared CRM helpers: the teacher CRM and the school panel run the same
 * controllers/blades under two route namespaces (teacher.* / school.*).
 * These helpers resolve routes and translations against the active namespace.
 * They are request-scoped — queued jobs/notifications must use
 * User::crmRouteName() instead.
 */

if (! function_exists('crm_prefix')) {
    /** Active CRM route namespace: 'school' inside /school/*, else 'teacher'. */
    function crm_prefix(): string
    {
        return request()->routeIs('school.*') ? 'school' : 'teacher';
    }
}

if (! function_exists('crm_route')) {
    /** route() against the active CRM namespace, e.g. crm_route('students.index'). */
    function crm_route(string $suffix, mixed $params = [], bool $absolute = true): string
    {
        return route(crm_prefix().'.'.$suffix, $params, $absolute);
    }
}

if (! function_exists('crm_route_is')) {
    /** request()->routeIs() with the active CRM namespace prepended to each pattern. */
    function crm_route_is(string ...$suffixes): bool
    {
        $prefix = crm_prefix();

        return request()->routeIs(...array_map(
            fn (string $suffix) => $prefix.'.'.$suffix,
            $suffixes
        ));
    }
}

if (! function_exists('crm_trans')) {
    /**
     * __() for shared CRM blades: in the school namespace, school.{key}
     * overrides win and anything not overridden falls back to teacher.{key}.
     */
    function crm_trans(string $key, array $replace = [], ?string $locale = null): string
    {
        if (crm_prefix() === 'school' && Lang::has('school.'.$key, $locale)) {
            return __('school.'.$key, $replace, $locale);
        }

        return __('teacher.'.$key, $replace, $locale);
    }
}

if (! function_exists('blog_post_for_slug')) {
    /**
     * The blog post a slug belongs to — in any language.
     *
     * Each post carries one permanent identity (its config('blog.posts') key,
     * which is also its English slug) plus a readable slug per translated
     * locale. Resolution has to run in both directions, so every lookup goes
     * through here rather than indexing the config by slug: a Turkish reader
     * arrives on `muzikte-araliklar-rehberi` and must land on the same post an
     * English reader reaches through `music-intervals-guide`.
     *
     * `locale` is the language the matched slug belongs to, which is what lets
     * the controller notice a slug used under the wrong prefix and redirect.
     *
     * @return array{key: string, slug: string, locale: string, post: array<string, mixed>}|null
     */
    function blog_post_for_slug(string $slug): ?array
    {
        foreach ((array) config('blog.posts') as $key => $post) {
            if ($slug === $key) {
                return ['key' => $key, 'slug' => $slug, 'locale' => 'en', 'post' => $post];
            }

            foreach ((array) ($post['slugs'] ?? []) as $locale => $localizedSlug) {
                if ($slug === $localizedSlug) {
                    return ['key' => $key, 'slug' => $slug, 'locale' => $locale, 'post' => $post];
                }
            }
        }

        return null;
    }
}

if (! function_exists('blog_post_slug')) {
    /** A post's slug in one language, falling back to its English identity. */
    function blog_post_slug(string $key, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return config('blog.posts.'.$key.'.slugs.'.$locale) ?? $key;
    }
}

if (! function_exists('blog_post_for_path')) {
    /**
     * The config('blog.posts') entry a public path belongs to, or null.
     *
     * Blog posts are localized exactly like the static pages in
     * config('locales.public_pages') — same /{locale} prefix, same canonical and
     * hreflang rules — but they live in their own registry so a new post is added
     * in one file instead of three. Every place that asks "is this path a
     * localized public page?" routes through here for the /blog/{slug} half of
     * the answer.
     *
     * The path carries whichever language's slug the visitor arrived on; the
     * returned entry names the post itself, so callers never have to care.
     *
     * @return array{key: string, slug: string, locale: string, post: array<string, mixed>}|null
     */
    function blog_post_for_path(string $path): ?array
    {
        $path = rtrim(strtok('/'.ltrim($path, '/'), '#?'), '/') ?: '/';

        if (! preg_match('#^/blog/([A-Za-z0-9-]+)$#', $path, $matches)) {
            return null;
        }

        return blog_post_for_slug($matches[1]);
    }
}

if (! function_exists('localized_page_section')) {
    /**
     * Fully-qualified translation section backing a public page ('pages.faq',
     * 'blog.music_intervals'), or null when the path has none declared.
     *
     * locale_page_translated() measures a locale's coverage of exactly this
     * section to decide whether the localized URL is a real translation, so the
     * two registries (config('locales.page_sections') for template pages,
     * config('blog.posts') for articles) resolve through one function.
     */
    function localized_page_section(string $path): ?string
    {
        $path = rtrim($path, '/') ?: '/';

        if ($section = config('locales.page_sections')[$path] ?? null) {
            return 'pages.'.$section;
        }

        if ($entry = blog_post_for_path($path)) {
            return 'blog.'.($entry['post']['section'] ?? $entry['key']);
        }

        return null;
    }
}

if (! function_exists('locale_url')) {
    /**
     * Public-page URL for a locale. English (and any non-prefixed locale) lives
     * at the un-prefixed path; every prefixed locale gets a /{locale} prefix
     * (locale_url('/pricing') → '/es/pricing' when the active locale is es).
     * Defaults to the active locale. A leading '#' or '?' fragment on the path
     * is preserved. Used so navbar/footer/in-page links keep the visitor inside
     * their language while browsing.
     */
    function locale_url(string $path = '/', ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $path = '/'.ltrim($path, '/');

        // Base path without any #fragment/?query, for the localized-page lookup.
        $raw = strtok($path, '#?');
        $suffix = substr($path, strlen($raw));
        $base = rtrim($raw, '/') ?: '/';

        // Blog posts are the one case where switching language rewrites the path
        // itself rather than just prefixing it: each translation has its own
        // readable slug. Whatever language's slug came in, the post is resolved
        // by identity and re-spelled in the target language.
        if ($entry = blog_post_for_path($base)) {
            $target = '/blog/'.blog_post_slug($entry['key'], $locale).$suffix;

            return in_array($locale, config('locales.prefixed'), true)
                ? url('/'.$locale.$target)
                : url($target);
        }

        // A localized variant otherwise exists only for the landing root and for
        // pages listed in config('locales.public_pages'). Anything else stays on
        // its un-prefixed English URL so links never point at a missing route.
        $isLocalized = $base === '/'
            || array_key_exists($base, (array) config('locales.public_pages'));

        if (! $isLocalized || ! in_array($locale, config('locales.prefixed'), true)) {
            return url($path);
        }

        return url('/'.$locale.($path === '/' ? '' : $path));
    }
}

if (! function_exists('locale_page_translated')) {
    /**
     * Does the localized variant of a public page actually carry translated copy?
     *
     * The /{locale} route group registers every page in config('locales.public_pages')
     * for every prefixed locale, regardless of whether that locale has the strings.
     * A locale missing its `pages.*` section renders the English fallback at a second
     * URL — Google reads that as a duplicate, ignores the hreflang claim, and picks
     * the English page as canonical ("Duplicate, Google chose a different canonical").
     *
     * So canonical/hreflang/sitemap all gate on this: an untranslated locale points
     * at the English URL and is never advertised as an alternate. Because the answer
     * is derived from the language files themselves, a page flips back on by itself
     * the moment its translation is added — nothing else needs editing.
     */
    function locale_page_translated(string $path, ?string $locale = null): bool
    {
        $locale ??= app()->getLocale();

        // English is the source; a non-prefixed locale has no localized URL at all.
        if ($locale === 'en' || ! in_array($locale, (array) config('locales.prefixed'), true)) {
            return true;
        }

        $section = localized_page_section($path);

        // A page with no declared section can't be verified — treat it as English-only
        // rather than risk advertising an untranslated alternate.
        if ($section === null) {
            return false;
        }

        // Memoized per process: the flatten runs once per (locale, section), and
        // the language files it reads are already loaded to render the page.
        // What is cached is the coverage ratio, not the verdict — caching the
        // boolean would freeze in the threshold that happened to be configured on
        // the first call.
        static $coverage = [];
        $cacheKey = $locale.'|'.$section;
        $threshold = (float) config('locales.page_translation_threshold', 0.95);

        if (isset($coverage[$cacheKey])) {
            return $coverage[$cacheKey] >= $threshold;
        }

        $source = Lang::get($section, [], 'en', false);
        $target = Lang::get($section, [], $locale, false);

        // Lang::get() with fallback disabled returns the key itself when missing.
        if (! is_array($source) || ! is_array($target)) {
            $coverage[$cacheKey] = 0.0;

            return false;
        }

        $flatten = function (array $items, string $prefix = '') use (&$flatten): array {
            $flat = [];
            foreach ($items as $key => $value) {
                $dotted = $prefix === '' ? (string) $key : $prefix.'.'.$key;
                $flat += is_array($value) ? $flatten($value, $dotted) : [$dotted => true];
            }

            return $flat;
        };

        $sourceKeys = $flatten($source);
        $targetKeys = $flatten($target);

        if ($sourceKeys === []) {
            $coverage[$cacheKey] = 1.0;

            return true;
        }

        $present = count(array_intersect_key($sourceKeys, $targetKeys));
        $coverage[$cacheKey] = $present / count($sourceKeys);

        return $coverage[$cacheKey] >= $threshold;
    }
}

if (! function_exists('music_label')) {
    /**
     * Display name for a canonical chord/scale/interval type.
     *
     * The generator, the answer checker and every stored config speak the
     * canonical English names ('Dominant 7th', 'Natural Minor', …) — those must
     * never change. This only swaps what the learner reads, so a German user
     * practising chords sees "Dominantseptakkord" while data-answer stays
     * "dominant 7th". An unknown name falls through to the canonical spelling,
     * so a newly added chord type shows up in English instead of blank.
     *
     * @param  string  $kind  'chord', 'scale' or 'interval'
     */
    function music_label(string $canonical, string $kind = 'chord', ?string $locale = null): string
    {
        $map = __('app.music.'.$kind, [], $locale);

        return is_array($map) ? ($map[$canonical] ?? $canonical) : $canonical;
    }
}

if (! function_exists('music_label_map')) {
    /**
     * The whole canonical => display map, for inline <script> blocks that build
     * feedback text ("Correct answer: …") from the canonical name.
     *
     * @return array<string, string>
     */
    function music_label_map(string $kind = 'chord', ?string $locale = null): array
    {
        $map = __('app.music.'.$kind, [], $locale);

        return is_array($map) ? $map : [];
    }
}
