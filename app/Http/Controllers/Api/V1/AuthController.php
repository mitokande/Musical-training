<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'device_name' => ['required', 'string', 'max:120'],
            'locale' => ['nullable', 'string', 'max:5'],
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
