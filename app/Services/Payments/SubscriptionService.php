<?php

namespace App\Services\Payments;

use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Analytics\PostHogService;
use App\Services\Payments\Contracts\PaymentGateway;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/**
 * The single orchestration point for the paid-subscription lifecycle. Controllers
 * and gateways call these methods; nothing else flips users.plan for a purchase.
 */
class SubscriptionService
{
    public function __construct(
        private PaymentManager $gateways,
        private PostHogService $posthog,
    ) {}

    /**
     * Report a subscription lifecycle change to PostHog.
     *
     * Captured server-side rather than in the browser: these are the revenue
     * events, and most of them originate from a Stripe webhook where no browser
     * is involved at all.
     */
    private function track(string $event, Subscription $subscription, array $properties = []): void
    {
        $this->posthog->capture($event, array_merge([
            'plan_id' => $subscription->plan_id,
            'billing_cycle' => $subscription->billing_cycle,
            'amount' => (float) $subscription->amount,
            'currency' => $subscription->currency,
            'provider' => $subscription->payment_provider,
            'subscription_id' => $subscription->id,
        ], $properties), $subscription->user);
    }

    /** Roles that can hold a Premium plan (admins never buy or trial one). */
    private const BUYABLE_ROLES = ['user', 'teacher', 'school'];

    /**
     * The Premium plan matching this user's role — the one thing they can buy
     * or trial. Unknown roles fall back to the student plan.
     */
    public function premiumPlanFor(User $user): ?Plan
    {
        $role = in_array($user->role, self::BUYABLE_ROLES, true) ? $user->role : 'user';

        return Plan::active()->forRole($role)->where('type', 'premium')->first();
    }

    /**
     * Price (excl. tax) for a plan on a given cycle.
     */
    public function priceFor(Plan $plan, string $cycle): float
    {
        return (float) ($cycle === 'yearly' ? $plan->price_yearly : $plan->price_monthly);
    }

    public function taxFor(float $amount): float
    {
        return round($amount * (float) config('payments.tax_rate', 0), 2);
    }

    /**
     * Create a pending subscription + invoice and hand off to the active gateway.
     * Returns ['subscription' => .., 'invoice' => .., 'redirect' => url].
     */
    public function purchase(User $user, Plan $plan, string $cycle): array
    {
        $cycle = $cycle === 'yearly' ? 'yearly' : 'monthly';
        $amount = $this->priceFor($plan, $cycle);
        $tax = $this->taxFor($amount);
        $currency = $plan->currency ?: config('payments.currency', 'USD');
        $gateway = $this->gateways->driver();

        return DB::transaction(function () use ($user, $plan, $cycle, $amount, $tax, $currency, $gateway) {
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'status' => 'pending',
                'billing_cycle' => $cycle,
                'auto_renew' => true,
                'starts_at' => now(),
                'amount' => $amount,
                'currency' => $currency,
                'payment_provider' => $gateway->key(),
            ]);

            $invoice = Invoice::create([
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'invoice_number' => Invoice::generateNumber(),
                'billing_cycle' => $cycle,
                'amount' => $amount,
                'tax_amount' => $tax,
                'total_amount' => round($amount + $tax, 2),
                'currency' => $currency,
                'status' => 'pending',
                'provider' => $gateway->key(),
            ]);

            $redirect = $gateway->checkout($subscription, $invoice);

            return compact('subscription', 'invoice', 'redirect');
        });
    }

    /**
     * Grant the one free trial: full Premium for config('payments.trial.days'),
     * no card, no charge. Returns null when the user is not eligible so callers
     * can surface a message instead of silently doing nothing.
     *
     * The entitlement is a real plan='premium' + a 'trialing' subscription
     * rather than a separate "trial" flag, so every premium gate in the app
     * already honours it and the hourly subscriptions:expire sweep already
     * takes it away again.
     */
    public function startTrial(User $user, Plan $plan): ?Subscription
    {
        if (! $user->canStartTrial()) {
            return null;
        }

        $end = now()->addDays((int) config('payments.trial.days', 15));

        $subscription = DB::transaction(function () use ($user, $plan, $end) {
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'status' => 'trialing',
                // Nothing is billed, but the column is NOT NULL; monthly is the
                // cycle the renewal prompt will offer by default.
                'billing_cycle' => 'monthly',
                'auto_renew' => false,
                'starts_at' => now(),
                'ends_at' => $end,
                'trial_ends_at' => $end,
                'amount' => 0,
                'currency' => $plan->currency ?: config('payments.currency', 'USD'),
                'payment_provider' => 'trial',
            ]);

            // Deliberately no Invoice row: nothing was charged, and an empty $0
            // line would show up in the user's billing history.

            $user->forceFill([
                'plan' => 'premium',
                // Null cycle is what distinguishes a trial from a paid
                // monthly/yearly subscription on the billing screen.
                'plan_cycle' => null,
                'plan_expires_at' => $end,
                'trial_started_at' => now(),
                'trial_ends_at' => $end,
            ])->save();

            // Teacher/school CRM premium features gate on the profile tier.
            $user->syncTeacherTierWithPlan();

            return $subscription;
        });

        $this->track('trial_started', $subscription, ['trial_days' => (int) config('payments.trial.days', 15)]);

        return $subscription;
    }

    /**
     * Mark a subscription active, pay its invoice, and grant Premium. Called by a
     * gateway once payment is confirmed (webhook, manual admin confirm, or the
     * auto-confirm test path).
     *
     * $periodEnd, when supplied (e.g. the exact paid-through instant from a Stripe
     * invoice), overrides the computed cycle end. $paymentRef records the
     * provider's charge reference on the invoice for later refunds. The method is
     * idempotent: re-running on an already-active subscription only reconciles a
     * more precise period end / payment reference.
     */
    public function activate(Subscription $subscription, ?CarbonInterface $periodEnd = null, ?string $paymentRef = null): void
    {
        if ($subscription->status === 'active') {
            $this->reconcileActive($subscription, $periodEnd, $paymentRef);

            return;
        }

        $months = (int) (config("payments.cycles.{$subscription->billing_cycle}.months") ?? 1);
        $start = now();
        $end = $periodEnd ? $periodEnd->copy() : (clone $start)->addMonthsNoOverflow($months);

        DB::transaction(function () use ($subscription, $start, $end, $paymentRef) {
            $subscription->update([
                'status' => 'active',
                'starts_at' => $start,
                'ends_at' => $end,
                'cancelled_at' => null,
            ]);

            $subscription->invoices()->where('status', 'pending')->update(array_filter([
                'status' => 'paid',
                'paid_at' => now(),
                'payment_method' => $subscription->payment_provider,
                'provider_reference' => $paymentRef,
            ]));

            // Converting off a trial: close the trial row so the billing screen
            // shows one current subscription and the hourly expiry sweep has
            // nothing stale left to act on.
            Subscription::where('user_id', $subscription->user_id)
                ->where('id', '!=', $subscription->id)
                ->where('status', 'trialing')
                ->update(['status' => 'expired']);

            $user = $subscription->user;
            if ($user->role !== 'admin') {
                $user->forceFill([
                    'plan' => 'premium',
                    'plan_cycle' => $subscription->billing_cycle,
                    'plan_expires_at' => $end,
                    // The trial is over because they bought, not because it
                    // lapsed. Clearing the end date stops the countdown UI and
                    // the "your trial is ending" mail from chasing a paying
                    // customer; trial_started_at stays as the once-ever record.
                    'trial_ends_at' => null,
                ])->save();

                // Teacher/school CRM premium features gate on the profile tier.
                $user->syncTeacherTierWithPlan();
            }
        });

        $this->track('subscription_activated', $subscription);
    }

    /**
     * Bring an already-active subscription in line with a later, more precise
     * signal (the exact Stripe period end and/or the captured payment reference)
     * without re-running the full activation.
     */
    private function reconcileActive(Subscription $subscription, ?CarbonInterface $periodEnd, ?string $paymentRef): void
    {
        if ($periodEnd) {
            $subscription->update(['ends_at' => $periodEnd->copy()]);

            $user = $subscription->user;
            if ($user->role !== 'admin') {
                $user->forceFill(['plan_expires_at' => $periodEnd->copy()])->save();
            }
        }

        if ($paymentRef) {
            $subscription->invoices()
                ->where('status', 'paid')
                ->whereNull('provider_reference')
                ->latest('id')
                ->limit(1)
                ->update(['provider_reference' => $paymentRef]);
        }
    }

    /**
     * A recurring cycle was charged by the provider: extend the paid-through
     * period, keep Premium, and record a paid invoice for the billing history.
     */
    public function renew(
        Subscription $subscription,
        ?CarbonInterface $periodEnd,
        float $amount,
        string $currency,
        ?string $paymentRef = null,
        ?string $externalNumber = null,
    ): void {
        $months = (int) (config("payments.cycles.{$subscription->billing_cycle}.months") ?? 1);
        $end = $periodEnd
            ? $periodEnd->copy()
            : ($subscription->ends_at && $subscription->ends_at->isFuture()
                ? $subscription->ends_at->copy()->addMonthsNoOverflow($months)
                : now()->addMonthsNoOverflow($months));

        DB::transaction(function () use ($subscription, $end, $amount, $currency, $paymentRef, $externalNumber) {
            $subscription->update([
                'status' => 'active',
                'ends_at' => $end,
                'cancelled_at' => null,
            ]);

            Invoice::create([
                'user_id' => $subscription->user_id,
                'subscription_id' => $subscription->id,
                'invoice_number' => Invoice::generateNumber(),
                'billing_cycle' => $subscription->billing_cycle,
                'amount' => $amount,
                'tax_amount' => 0,
                'total_amount' => $amount,
                'currency' => $currency ?: $subscription->currency,
                'status' => 'paid',
                'paid_at' => now(),
                'payment_method' => $subscription->payment_provider,
                'provider' => $subscription->payment_provider,
                'provider_reference' => $paymentRef,
                'notes' => $externalNumber ? "Stripe invoice {$externalNumber}" : null,
            ]);

            $user = $subscription->user;
            if ($user->role !== 'admin') {
                $user->forceFill([
                    'plan' => 'premium',
                    'plan_cycle' => $subscription->billing_cycle,
                    'plan_expires_at' => $end,
                ])->save();

                // Teacher/school CRM premium features gate on the profile tier.
                $user->syncTeacherTierWithPlan();
            }
        });

        // Renewal amount comes from the provider's invoice, not the stored
        // subscription price, so it overrides the default in track().
        $this->track('subscription_renewed', $subscription, [
            'amount' => $amount,
            'currency' => $currency ?: $subscription->currency,
        ]);
    }

    /**
     * Reconcile a subscription when the user lands back on the checkout success
     * page, so Premium is granted immediately instead of waiting on the webhook.
     * A no-op for gateways/records that don't support return-based fulfillment.
     */
    public function confirmReturn(Subscription $subscription, ?string $reference): void
    {
        if ($subscription->status === 'active' || $subscription->payment_provider !== 'stripe' || ! $reference) {
            return;
        }

        $gateway = $this->gatewayFor('stripe');
        if ($gateway instanceof StripeGateway) {
            $gateway->fulfillReturn($reference);
            $subscription->refresh();
        }
    }

    /**
     * Cancel auto-renew. By default Premium is kept until the period end
     * (ends_at); pass $immediate to revoke access right now.
     */
    public function cancel(Subscription $subscription, bool $immediate = false): void
    {
        $subscription->update([
            'auto_renew' => false,
            'cancelled_at' => now(),
            'status' => $immediate ? 'cancelled' : $subscription->status,
        ]);

        // Tell the provider to stop billing (cancel now, or at period end).
        $this->gatewayFor($subscription->payment_provider)->cancelAtProvider($subscription, $immediate);

        if ($immediate) {
            $this->downgradeIfLapsed($subscription->user);
        }

        $this->track('subscription_cancelled', $subscription, ['immediate' => $immediate]);
    }

    /** Period lapsed with no renewal — expire and drop entitlement. */
    public function expire(Subscription $subscription): void
    {
        $subscription->update(['status' => 'expired']);
        $this->downgradeIfLapsed($subscription->user);
    }

    /** Refund a paid invoice and immediately revoke access. */
    public function refund(Invoice $invoice): bool
    {
        if ($invoice->status !== 'paid') {
            return false;
        }

        $ok = $this->gatewayFor($invoice->provider)->refund($invoice);
        if (! $ok) {
            return false;
        }

        DB::transaction(function () use ($invoice) {
            $invoice->update(['status' => 'refunded', 'refunded_at' => now()]);
            if ($invoice->subscription) {
                $invoice->subscription->update([
                    'status' => 'cancelled',
                    'auto_renew' => false,
                    'cancelled_at' => now(),
                ]);
                $this->downgradeIfLapsed($invoice->subscription->user);
            }
        });

        return true;
    }

    /**
     * Drop a user back to free unless they still hold another active, in-period
     * subscription. Never touches a teacher/school incentive benefit — that is
     * layered on top by isEffectivelyPremium().
     */
    public function downgradeIfLapsed(User $user): void
    {
        $stillActive = $user->subscriptions()
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>', now());
            })
            ->exists();

        if ($stillActive || $user->role === 'admin') {
            return;
        }

        $user->forceFill([
            'plan' => 'free',
            'plan_cycle' => null,
            'plan_expires_at' => null,
        ])->save();

        // Drop the teacher/school profile back to the basic tier alongside the plan.
        $user->syncTeacherTierWithPlan();
    }

    /**
     * Resolve the gateway a record was actually created with, falling back to the
     * active driver for legacy rows that predate the provider column.
     */
    private function gatewayFor(?string $provider): PaymentGateway
    {
        $provider = $provider ?: config('payments.driver', 'manual');

        if (! in_array($provider, $this->gateways->available(), true)) {
            $provider = config('payments.driver', 'manual');
        }

        return $this->gateways->driver($provider);
    }
}
