<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Services\Payments\SubscriptionService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(private SubscriptionService $subscriptions) {}

    /** Roles that can buy a Premium plan (admins never check out). */
    private const BUYABLE_ROLES = ['user', 'teacher', 'school'];

    /**
     * Resolve the Premium plan that matches the signed-in user's role.
     */
    private function planForUser($user): ?Plan
    {
        $role = in_array($user->role, self::BUYABLE_ROLES, true) ? $user->role : 'user';

        return Plan::active()->forRole($role)->where('type', 'premium')->first();
    }

    /**
     * Checkout / order-summary page: plan details, cycle toggle, price breakdown,
     * refund guarantee, terms acceptance.
     */
    public function show(Request $request)
    {
        $user = $request->user();

        // Already Premium (paid or via teacher/school incentive) → nothing to buy.
        if ($user->isEffectivelyPremium()) {
            return redirect()->route('billing.index')
                ->with('info', __('You already have Premium access.'));
        }

        $plan = $this->planForUser($user);
        if (! $plan) {
            return redirect()->route('pricing.index')
                ->with('error', __('This plan is not available right now.'));
        }

        $cycle = $request->query('cycle') === 'yearly' ? 'yearly' : 'monthly';

        return view('checkout.show', $this->orderData($plan, $cycle));
    }

    /**
     * Create the pending subscription + invoice and hand off to the gateway.
     */
    public function store(Request $request)
    {
        $request->validate([
            'cycle' => 'required|in:monthly,yearly',
            'terms' => 'accepted',
        ]);

        $user = $request->user();

        if ($user->isEffectivelyPremium()) {
            return redirect()->route('billing.index')
                ->with('info', __('You already have Premium access.'));
        }

        $plan = $this->planForUser($user);
        if (! $plan) {
            return redirect()->route('pricing.index')
                ->with('error', __('This plan is not available right now.'));
        }

        $result = $this->subscriptions->purchase($user, $plan, $request->input('cycle'));

        return redirect()->to($result['redirect']);
    }

    public function success(Subscription $subscription, Request $request)
    {
        abort_unless($subscription->user_id === $request->user()->id, 403);

        // Grant Premium right away from the return redirect (Stripe Checkout
        // appends ?session_id=…); the webhook is the durable backstop.
        $this->subscriptions->confirmReturn($subscription, $request->query('session_id'));

        $subscription->load('plan');

        return view('checkout.success', compact('subscription'));
    }

    public function pending(Subscription $subscription, Request $request)
    {
        abort_unless($subscription->user_id === $request->user()->id, 403);
        $subscription->load('plan');

        return view('checkout.pending', compact('subscription'));
    }

    /**
     * Shared price breakdown for the order summary + the cycle toggle so the
     * blade can render both cycles without a round-trip.
     */
    private function orderData(Plan $plan, string $cycle): array
    {
        $breakdown = fn (string $c) => [
            'amount' => $this->subscriptions->priceFor($plan, $c),
            'tax' => $this->subscriptions->taxFor($this->subscriptions->priceFor($plan, $c)),
            'total' => round($this->subscriptions->priceFor($plan, $c) + $this->subscriptions->taxFor($this->subscriptions->priceFor($plan, $c)), 2),
        ];

        return [
            'plan' => $plan,
            'cycle' => $cycle,
            'currencySymbol' => config('payments.currency_symbol', '$'),
            'currency' => $plan->currency ?: config('payments.currency', 'USD'),
            'taxRate' => (float) config('payments.tax_rate', 0),
            'refundDays' => (int) config('payments.refund_days', 14),
            'monthly' => $breakdown('monthly'),
            'yearly' => $breakdown('yearly'),
        ];
    }
}
