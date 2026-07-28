<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
