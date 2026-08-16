<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class LanguageController extends Controller
{
    protected array $supported = ['en', 'es', 'de', 'fr', 'pt', 'tr', 'it'];

    public function switch(Request $request): RedirectResponse
    {
        $locale = $request->input('locale');

        if (! in_array($locale, $this->supported)) {
            return back();
        }

        if (Auth::check()) {
            Auth::user()->update(['locale' => $locale]);
        }

        session(['locale' => $locale]);
        // Mark this as a deliberate choice so SetLocale treats the session locale
        // as explicit and the `/` landing honours it.
        session(['locale_selected' => true]);

        // The session expires in 120 minutes; guest usage quotas live in a
        // one-year cookie. Mirroring the choice into a cookie of the same length
        // keeps a guest's language from reverting to their browser's mid-visit —
        // which is how an English session ended up showing a Turkish
        // "daily limit reached" screen on the games pages.
        Cookie::queue(Cookie::make(SetLocale::LOCALE_COOKIE, $locale, 60 * 24 * 365));

        app()->setLocale($locale);

        // Redirect to the localized equivalent of the page they were on, so the
        // URL stays aligned with the language (e.g. /pricing → /es/pricing). Any
        // existing /{locale} prefix is stripped first; locale_url() re-adds the
        // correct one only for pages that actually have a localized route.
        $path = parse_url(url()->previous(), PHP_URL_PATH) ?: '/';
        $segments = array_values(array_filter(explode('/', $path), fn ($s) => $s !== ''));

        if (isset($segments[0]) && in_array($segments[0], config('locales.prefixed'), true)) {
            array_shift($segments);
        }

        $basePath = '/'.implode('/', $segments);

        return redirect(locale_url($basePath, $locale))->with('locale_changed', true);
    }
}
