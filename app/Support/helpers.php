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
        $base = rtrim(strtok($path, '#?'), '/') ?: '/';

        // A localized variant exists only for the landing root and for pages
        // listed in config('locales.public_pages'). Anything else stays on its
        // un-prefixed English URL so links never point at a missing route.
        $isLocalized = $base === '/'
            || array_key_exists($base, (array) config('locales.public_pages'));

        if (! $isLocalized || ! in_array($locale, config('locales.prefixed'), true)) {
            return url($path);
        }

        return url('/'.$locale.($path === '/' ? '' : $path));
    }
}
