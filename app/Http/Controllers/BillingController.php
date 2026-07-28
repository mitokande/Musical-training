<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Services\Payments\SubscriptionService;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function __construct(private SubscriptionService $subscriptions) {}

    /** Account → Billing: current subscription, renewal date, invoice history. */
    public function index(Request $request)
    {
        $user = $request->user();

        // Includes a running free trial, so the page can describe it instead of
        // reporting "no subscription" while the user is clearly on Premium.
        $subscription = $user->currentSubscription();
        $invoices = $user->invoices()->latest()->limit(50)->get();

        return view('billing.index', [
            'user' => $user,
            'subscription' => $subscription,
            'invoices' => $invoices,
            'isEffectivelyPremium' => $user->isEffectivelyPremium(),
        ]);
    }

    /** Cancel auto-renew; Premium is kept until the current period ends. */
    public function cancel(Subscription $subscription, Request $request)
    {
        abort_unless($subscription->user_id === $request->user()->id, 403);

        $this->subscriptions->cancel($subscription);

        return redirect()->route('billing.index')
            ->with('success', __('Your subscription will not renew. You keep Premium until the end of the current period.'));
    }
}
