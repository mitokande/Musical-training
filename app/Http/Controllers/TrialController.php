<?php

namespace App\Http\Controllers;

use App\Services\Payments\SubscriptionService;
use Illuminate\Http\Request;

class TrialController extends Controller
{
    public function __construct(private SubscriptionService $subscriptions) {}

    /**
     * Claim the one free Premium trial. No card, no gateway round-trip — the
     * entitlement is granted locally and expires on its own.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if (! $user->canStartTrial()) {
            return back()->with('error', $user->hasUsedTrial()
                ? __('app.trial.already_used')
                : __('app.trial.not_available'));
        }

        $plan = $this->subscriptions->premiumPlanFor($user);
        if (! $plan) {
            return back()->with('error', __('This plan is not available right now.'));
        }

        if (! $this->subscriptions->startTrial($user, $plan)) {
            return back()->with('error', __('app.trial.not_available'));
        }

        return redirect()->route('billing.index')
            ->with('success', __('app.trial.started_flash', ['days' => (int) config('payments.trial.days', 15)]));
    }
}
