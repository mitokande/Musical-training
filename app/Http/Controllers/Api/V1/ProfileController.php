<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UserResource;
use App\Services\Account\AccountDeletionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'surname' => ['sometimes', 'nullable', 'string', 'max:255'],
            'email' => [
                'sometimes', 'string', 'lowercase', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'locale' => ['sometimes', 'string', Rule::in(config('locales.supported', ['en']))],
            'country' => ['sometimes', 'nullable', 'string', 'max:100'],
            'city' => ['sometimes', 'nullable', 'string', 'max:100'],
            'date_of_birth' => ['sometimes', 'nullable', 'date'],
        ]);

        // Changing the address invalidates verification, same as the web flow.
        if (isset($validated['email']) && $validated['email'] !== $user->email) {
            $user->email_verified_at = null;
        }

        $user->fill($validated)->save();

        return response()->json(['data' => new UserResource($user->fresh())]);
    }

    /**
     * Delete the signed-in account from inside the app.
     *
     * Google Play and the App Store both require account deletion to be
     * reachable in-app once an app can create accounts; the same journey is
     * offered on the web at /account/delete for people who no longer have the
     * app installed.
     *
     * Same one-way door as the web flow (see AccountDeletionService): every
     * token is revoked, the account can never sign in again, and the e-mail
     * address is released. Confirmation is the account password, or — for
     * Google sign-in accounts, which have none — the account's own e-mail
     * address typed back.
     */
    public function destroy(Request $request, AccountDeletionService $deletions): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'password' => [$user->hasPassword() ? 'required' : 'nullable', 'string'],
            'confirm_email' => [$user->hasPassword() ? 'nullable' : 'required', 'string'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        if ($user->hasPassword()) {
            if (! Hash::check($validated['password'], $user->password)) {
                throw ValidationException::withMessages([
                    'password' => [__('The provided password does not match your current password.')],
                ]);
            }
        } elseif (mb_strtolower(trim($validated['confirm_email'])) !== mb_strtolower($user->email)) {
            throw ValidationException::withMessages([
                'confirm_email' => [__('app.profile.delete_email_mismatch')],
            ]);
        }

        $deletions->delete($user, $validated['reason'] ?? null);

        // The service already revoked every token — this response is the last
        // thing the client can do with its credentials.
        return response()->json(['data' => ['status' => 'deleted']]);
    }
}
