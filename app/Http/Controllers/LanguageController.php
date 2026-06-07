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

        if (!in_array($locale, $this->supported)) {
            return back();
        }

        if (Auth::check()) {
            Auth::user()->update(['locale' => $locale]);
        }

        session(['locale' => $locale]);
        app()->setLocale($locale);

        return back()->with('locale_changed', true);
    }
}
