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
