<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'role'     => ['required', 'string', 'in:user,teacher,school'],
            'name'     => ['required', 'string', 'max:255'],
            'surname'  => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'country'  => ['nullable', 'string', 'max:2'],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'surname'  => $request->surname,
            'username' => $this->generateUniqueUsername($request->name, $request->surname),
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
            'plan'     => 'free',
            'locale'   => session('locale', config('app.locale')),
            'country'  => $request->country,
        ]);

        // Store detected country in session based on the user's locale
        if (! session()->has('detected_country') && $user->locale) {
            $localeToCountry = [
                'tr' => 'TR', 'en' => 'US', 'de' => 'DE', 'fr' => 'FR',
                'es' => 'ES', 'it' => 'IT', 'ru' => 'RU', 'ar' => 'SA',
                'zh' => 'CN', 'ja' => 'JP', 'ko' => 'KR', 'pt' => 'BR',
                'nl' => 'NL', 'pl' => 'PL', 'sv' => 'SE',
            ];
            $detectedCountry = $localeToCountry[$user->locale] ?? '';
            if ($detectedCountry) {
                session(['detected_country' => $detectedCountry]);
            }
        }

        event(new Registered($user));

        return redirect()->route('verification.notice');
    }

    private function generateUniqueUsername(string $name, string $surname): string
    {
        $base = Str::slug($name . $surname, '');
        $username = $base;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $base . $counter;
            $counter++;
        }

        return $username;
    }
}
