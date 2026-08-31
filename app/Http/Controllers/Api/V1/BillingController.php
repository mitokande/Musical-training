<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Payments\AdaptyEventProcessor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The mobile app's half of the Adapty integration.
 *
 * Almost every store event identifies its owner on its own: the app calls
 * adapty.identify(user.id) on sign-in, so customer_user_id is the users.id and
 * the webhook needs nothing from the client. This endpoint exists for the one
 * case that cannot work that way — onboarding shows the paywall *before*
 * sign-up, so somebody can pay while their Adapty profile is still anonymous and
 * no account exists to attribute it to. The webhook parks those events; this is
 * where they are claimed.
 *
 * It grants nothing by itself. All it does is record which Adapty profile
 * belongs to this account and re-run the events already sitting in the ledger —
 * events that came from Adapty, over an authenticated webhook, describing a
 * purchase the store has already confirmed. A client cannot invent one.
 */
class BillingController extends Controller
{
    public function linkAdapty(Request $request, AdaptyEventProcessor $processor): JsonResponse
    {
        $validated = $request->validate([
            'profile_id' => ['required', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $profileId = $validated['profile_id'];

        // An Adapty profile belongs to one account. Without this, anyone who
        // learned another customer's profile id could claim their subscription.
        $takenByAnother = User::where('adapty_profile_id', $profileId)
            ->whereKeyNot($user->getKey())
            ->exists();

        if ($takenByAnother) {
            return response()->json([
                'error' => [
                    'code' => 'profile_already_linked',
                    'message' => __('This subscription profile is already linked to another account.'),
                ],
            ], 409);
        }

        if ($user->adapty_profile_id !== $profileId) {
            $user->forceFill(['adapty_profile_id' => $profileId])->save();
        }

        $replayed = $processor->replayDeferred($profileId);

        return response()->json([
            'data' => [
                'linked' => true,
                // How many parked events this claim turned into entitlement, so
                // the app knows whether to expect /me/plan to have changed.
                'replayed' => $replayed,
                'is_premium' => $user->fresh()->isEffectivelyPremium(),
            ],
        ]);
    }
}
