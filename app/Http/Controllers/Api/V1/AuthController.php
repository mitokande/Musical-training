<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use App\Services\Auth\GoogleIdTokenVerifier;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(private readonly GoogleIdTokenVerifier $googleTokens) {}

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'device_name' => ['required', 'string', 'max:120'],
            // Same rule as ProfileController::update. This used to take any
            // five characters, so a client sending 'en-US' wrote a value the
            // supported-locale list never matches — and the column defaults to
            // 'tr', so getting it wrong is not a quiet failure.
            'locale' => ['nullable', 'string', Rule::in(config('locales.supported', ['en']))],
            'country' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'surname' => $validated['surname'] ?? null,
            'username' => $this->uniqueUsername($validated['name']),
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            // Mobile signup is always a learner account.
            'role' => 'user',
            'plan' => 'free',
            'locale' => $validated['locale'] ?? config('app.locale'),
            'country' => $validated['country'] ?? null,
        ]);

        event(new Registered($user));

        return response()->json([
            'data' => [
                'token' => $user->createToken($validated['device_name'])->plainTextToken,
                'user' => new UserResource($user),
            ],
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['required', 'string', 'max:120'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! $user->password || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        if ($user->isSuspended()) {
            return response()->json([
                'error' => ['code' => 'account_suspended', 'message' => __('Your account has been suspended.')],
            ], 403);
        }

        return response()->json([
            'data' => [
                'token' => $user->createToken($validated['device_name'])->plainTextToken,
                'user' => new UserResource($user),
            ],
        ]);
    }

    /**
     * Sign in (or sign up) with the ID token from a native Google Sign-In.
     *
     * The mobile counterpart of SocialAuthController: same three cases, same
     * columns, but no redirect and no session — the app has already talked to
     * Google on the device and brings back a token, and leaves here with a
     * Sanctum token like every other /auth entry point.
     *
     * The account is matched on google_id first and only then on the address,
     * which is what lets someone who registered with a password later sign in
     * with Google and land on the same account rather than a second one.
     */
    public function google(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_token' => ['required', 'string'],
            'device_name' => ['required', 'string', 'max:120'],
            'locale' => ['nullable', 'string', Rule::in(config('locales.supported', ['en']))],
        ]);

        $identity = $this->googleTokens->verify($validated['id_token']);

        $user = User::where('google_id', $identity->id)->first();
        $created = false;

        if (! $user) {
            $user = User::where('email', $identity->email)->first();

            if ($user) {
                // An existing account meeting Google for the first time. The
                // password, if it has one, is left alone: this adds a way in,
                // it does not replace one.
                $user->update([
                    'google_id' => $identity->id,
                    'avatar_url' => $user->avatar_url ?: $identity->avatar,
                ]);
            }
        }

        if (! $user) {
            $user = User::create([
                'name' => $identity->name ?: Str::before($identity->email, '@'),
                'username' => $this->uniqueUsername($identity->name ?: Str::before($identity->email, '@')),
                'email' => $identity->email,
                'google_id' => $identity->id,
                'avatar_url' => $identity->avatar,
                'role' => 'user',
                'plan' => 'free',
                // The app's picker has had its say by the time anyone reaches
                // the sign-in screen; without this the users.locale default
                // ('tr') would win. Same reasoning as register().
                'locale' => $validated['locale'] ?? config('app.locale'),
                // Google only issues a token with email_verified, and the
                // verifier refuses one without it, so the address is proven.
                'email_verified_at' => now(),
            ]);

            $created = true;

            event(new Registered($user));
        }

        if ($user->isSuspended()) {
            return response()->json([
                'error' => ['code' => 'account_suspended', 'message' => __('Your account has been suspended.')],
            ], 403);
        }

        // Signing in through Google proves the address, so an account that
        // registered with a password and never opened the verification mail is
        // verified by arriving here. Nothing else in the app can un-stick it.
        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return response()->json([
            'data' => [
                'token' => $user->createToken($validated['device_name'])->plainTextToken,
                'user' => new UserResource($user),
                // Lets the app send a brand-new account straight to onboarding
                // instead of guessing from local state.
                'created' => $created,
            ],
        ], $created ? 201 : 200);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['data' => new UserResource($request->user())]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['data' => ['status' => 'logged_out']]);
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json(['data' => ['status' => 'logged_out']]);
    }

    /**
     * Always reports success so the endpoint cannot be used to probe which
     * addresses have accounts.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'string', 'email']]);

        Password::sendResetLink($request->only('email'));

        return response()->json(['data' => ['status' => 'sent']]);
    }

    public function resendVerification(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        return response()->json(['data' => ['status' => 'sent']]);
    }

    /**
     * The verification link itself stays a signed web URL; the app opens it in
     * a browser and polls this endpoint.
     */
    public function verificationStatus(Request $request): JsonResponse
    {
        return response()->json([
            'data' => ['verified' => $request->user()->hasVerifiedEmail()],
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => [$user->hasPassword() ? 'required' : 'nullable', 'string'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if ($user->hasPassword() && ! Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => [__('The provided password does not match your current password.')],
            ]);
        }

        $user->update(['password' => Hash::make($validated['password'])]);

        return response()->json(['data' => ['status' => 'updated']]);
    }

    /**
     * Mirrors RegisteredUserController's username strategy.
     */
    private function uniqueUsername(string $name): string
    {
        $base = preg_replace('/[^a-z0-9]/', '', strtolower($name)) ?: 'user';
        $base = substr($base, 0, 20);

        $candidate = $base;
        $suffix = 1;
        while (User::where('username', $candidate)->exists()) {
            $candidate = $base.$suffix;
            $suffix++;
        }

        return $candidate;
    }
}
