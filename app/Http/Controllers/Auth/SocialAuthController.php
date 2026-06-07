<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Google ile giriş yapılamadı. Lütfen tekrar deneyin.');
        }

        $user = User::where('google_id', $googleUser->getId())->first();

        if ($user) {
            Auth::login($user, remember: true);
            return redirect()->intended(route('profile.edit'));
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            $user->update([
                'google_id' => $googleUser->getId(),
                'avatar_url' => $googleUser->getAvatar(),
            ]);

            Auth::login($user, remember: true);
            return redirect()->intended(route('profile.edit'));
        }

        $user = User::create([
            'name' => $googleUser->getName(),
            'username' => $this->generateUniqueUsername($googleUser->getName()),
            'email' => $googleUser->getEmail(),
            'google_id' => $googleUser->getId(),
            'avatar_url' => $googleUser->getAvatar(),
            'role' => 'user',
            'plan' => 'free',
            'email_verified_at' => now(),
        ]);

        event(new Registered($user));

        Auth::login($user, remember: true);

        return redirect()->route('profile.edit');
    }

    private function generateUniqueUsername(string $name): string
    {
        $base = Str::slug($name, '');
        $username = $base;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $base . $counter;
            $counter++;
        }

        return $username;
    }
}
