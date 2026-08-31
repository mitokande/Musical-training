<?php

namespace App\Services\Payments;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Reconciles the local subscription / invoice / entitlement state from Adapty
 * webhook events — the mobile app's App Store and Play Store purchases.
 *
 * Written the same way as StripeEventProcessor and for the same reasons: it is
 * network-free, every handler works off the plain payload array, and each one is
 * safe to re-run. Idempotency is enforced upstream by AdaptyWebhookController
 * through the adapty_events ledger.
 *
 * Two things differ from Stripe, and they are the whole reason this is not just
 * another branch of that class:
 *
 *  1. There is no local record to reconcile against. The web flow creates a
 *     pending Subscription + Invoice before the customer ever reaches the
 *     provider; a store purchase happens entirely inside the app, and the first
 *     this server hears of it is the webhook. So the subscription is created
 *     here, from the event.
 *
 *  2. The purchase can precede the account. Onboarding shows the paywall before
 *     sign-up, so an event can arrive with no customer_user_id and a profile
 *     nobody has claimed yet. Those return false — "deferred" — and are replayed
 *     by replayDeferred() when the app links the profile after sign-in.
 *
 * Adapty's field and event names are read defensively (several spellings
 * accepted, unknown types inferred from the expiry) because they are a
 * dashboard-side contract we do not control, and a purchase silently dropped on
 * a renamed key is the one failure mode that costs a paying customer their
 * subscription.
 */
class AdaptyEventProcessor
{
    private const GRANT = 'grant';

    private const RENEW = 'renew';

    private const REVOKE = 'revoke';

    private const REFUND = 'refund';

    private const CANCEL = 'cancel';

    private const REACTIVATE = 'reactivate';

    private const BILLING_ISSUE = 'billing_issue';

    private const IGNORE = 'ignore';

    public function __construct(private SubscriptionService $subscriptions) {}

    /**
     * Apply one event.
     *
     * @return bool true when handled (or deliberately ignored), false when it
     *              belongs to a profile no account has claimed yet and must be
     *              parked for replay.
     */
    public function handle(array $payload): bool
    {
        $type = $this->eventType($payload);
        $props = $this->properties($payload);

        // A sandbox purchase costs nothing and can be made by anyone with a
        // TestFlight build; on a production database it must not buy Premium.
        if ($this->isSandbox($props) && ! config('services.adapty.accept_sandbox', true)) {
            Log::info('Adapty webhook: sandbox event ignored', ['type' => $type]);

            return true;
        }

        // Other access levels (a future tier, a one-off unlock) are not what
        // users.plan describes, so they are acknowledged and left alone.
        if (! $this->concernsPremium($props)) {
            return true;
        }

        $user = $this->resolveUser($payload);
        if (! $user) {
            return false;
        }

        $expiresAt = $this->timestamp($props, ['expires_at', 'expires_date', 'expiration_date']);
        $intent = $this->intentFor($type, $expiresAt);

        if ($intent === self::IGNORE) {
            return true;
        }

        $subscription = $this->localSubscription($user, $props);

        if (! $subscription) {
            // Nothing local to take away — a revocation for a purchase we never
            // recorded is already true.
            if (! in_array($intent, [self::GRANT, self::RENEW], true)) {
                return true;
            }

            $subscription = $this->createSubscription($user, $props, $expiresAt);
            if (! $subscription) {
                return true;
            }
        }

        $this->apply($intent, $subscription, $props, $expiresAt);

        return true;
    }

    /**
     * Replay the events parked against an Adapty profile, oldest first, now that
     * a user has claimed it. Returns how many were applied.
     */
    public function replayDeferred(string $profileId): int
    {
        $applied = 0;

        $rows = DB::table('adapty_events')
            ->where('profile_id', $profileId)
            ->whereNull('processed_at')
            ->orderBy('created_at')
            ->get();

        foreach ($rows as $row) {
            $payload = json_decode($row->payload, true);
            if (! is_array($payload)) {
                continue;
            }

            if ($this->handle($payload)) {
                DB::table('adapty_events')
                    ->where('event_id', $row->event_id)
                    ->update(['processed_at' => now(), 'updated_at' => now()]);
                $applied++;
            }
        }

        return $applied;
    }

    // --- the lifecycle --------------------------------------------------------

    private function apply(string $intent, Subscription $subscription, array $props, ?Carbon $expiresAt): void
    {
        match ($intent) {
            self::GRANT => $this->grant($subscription, $props, $expiresAt),
            self::RENEW => $this->renew($subscription, $props, $expiresAt),
            self::REVOKE => $this->subscriptions->expire($subscription),
            self::REFUND => $this->refund($subscription, $props),
            self::CANCEL => $this->cancel($subscription),
            self::REACTIVATE => $this->reactivate($subscription),
            self::BILLING_ISSUE => $this->flagBillingIssue($subscription),
            default => null,
        };
    }

    /** First purchase, trial start, restore, or a conversion off a trial. */
    private function grant(Subscription $subscription, array $props, ?Carbon $expiresAt): void
    {
        $reference = $this->transactionId($props);
        $this->link($subscription, $props);

        $this->subscriptions->activate($subscription, $expiresAt, $reference);

        $subscription->refresh();
        $this->syncTrial($subscription, $props, $expiresAt);
        $this->recordInvoice($subscription, $props, $reference);
    }

    /** The store charged another cycle. */
    private function renew(Subscription $subscription, array $props, ?Carbon $expiresAt): void
    {
        // A renewed *trial* is still a trial: nothing was charged, so it must not
        // produce an invoice. Extending it is all a renewal means there.
        if ($this->isTrial($props)) {
            $this->grant($subscription, $props, $expiresAt);

            return;
        }

        $this->link($subscription, $props);

        $this->subscriptions->renew(
            $subscription,
            $expiresAt,
            $this->amount($props),
            $this->currency($props, $subscription),
            $this->transactionId($props),
            $this->storeLabel($props),
        );

        $subscription->refresh();
        $this->syncTrial($subscription, $props, $expiresAt);
    }

    /**
     * Apple or Google gave the money back. Access goes immediately — the store
     * has already reversed the charge by the time this event is sent.
     */
    private function refund(Subscription $subscription, array $props): void
    {
        DB::transaction(function () use ($subscription, $props) {
            $reference = $this->transactionId($props);

            // Refund the charge the event names where we recorded it; otherwise
            // this is a subscription-wide reversal and every paid line goes.
            $refundable = $subscription->invoices()->where('status', 'paid');
            if ($reference) {
                $named = $subscription->invoices()
                    ->where('status', 'paid')
                    ->where('provider_reference', $reference);

                if ($named->exists()) {
                    $refundable = $named;
                }
            }
            $refundable->update(['status' => 'refunded', 'refunded_at' => now()]);

            $subscription->update([
                'status' => 'cancelled',
                'auto_renew' => false,
                'cancelled_at' => now(),
            ]);

            $this->subscriptions->downgradeIfLapsed($subscription->user);
        });
    }

    /**
     * Auto-renew switched off in the store's subscription settings. Access is
     * kept until the period they already paid for runs out; the expiry event
     * that follows is what takes it away.
     */
    private function cancel(Subscription $subscription): void
    {
        if (! $subscription->auto_renew && $subscription->cancelled_at) {
            return;
        }

        $this->subscriptions->cancel($subscription, immediate: false);
    }

    /** They changed their mind inside the renewal window. */
    private function reactivate(Subscription $subscription): void
    {
        $subscription->update(['auto_renew' => true, 'cancelled_at' => null]);
    }

    /**
     * The store could not charge the card. Entitlement stays for the paid-through
     * period while the store retries; the expiry event downgrades if it never
     * recovers. Mirrors the Stripe dunning behaviour.
     */
    private function flagBillingIssue(Subscription $subscription): void
    {
        if ($subscription->status === 'active') {
            $subscription->update(['status' => 'past_due']);
        }
    }

    /**
     * Mirror a store trial onto the user the same way SubscriptionService::
     * startTrial() mirrors ours, so "N days left" and the once-ever guard read
     * the same columns whichever store the trial came from — and so somebody who
     * has already had a free month on iOS cannot claim another one on the web.
     */
    private function syncTrial(Subscription $subscription, array $props, ?Carbon $expiresAt): void
    {
        $user = $subscription->user;

        if ($this->isTrial($props)) {
            $subscription->forceFill(['trial_ends_at' => $expiresAt])->save();
            $user->forceFill([
                'trial_started_at' => $user->trial_started_at ?? now(),
                'trial_ends_at' => $expiresAt,
            ])->save();

            return;
        }

        // Converted to a paid subscription. Clearing the end date stops the
        // countdown UI chasing a paying customer; trial_started_at stays as the
        // once-ever record.
        if ($user->trial_ends_at !== null) {
            $user->forceFill(['trial_ends_at' => null])->save();
        }
    }

    /**
     * Record the store's charge in the billing history.
     *
     * activate() had no pending invoice to mark paid — a store purchase never
     * passed through our checkout — so the row is created here instead. The
     * amount is what the customer paid the store, before Apple's or Google's
     * commission; net proceeds are only knowable from the store's own reports.
     *
     * Keyed on the transaction id so a grant that runs twice (an initial purchase
     * followed by a conversion event carrying the same transaction) records one
     * invoice, not two.
     */
    private function recordInvoice(Subscription $subscription, array $props, ?string $reference): void
    {
        if ($this->isTrial($props) || ! $reference) {
            return;
        }

        $amount = $this->amount($props);
        if ($amount <= 0) {
            return;
        }

        $exists = Invoice::where('provider', 'adapty')
            ->where('provider_reference', $reference)
            ->exists();
        if ($exists) {
            return;
        }

        Invoice::create([
            'user_id' => $subscription->user_id,
            'subscription_id' => $subscription->id,
            'invoice_number' => Invoice::generateNumber(),
            'billing_cycle' => $subscription->billing_cycle,
            'amount' => $amount,
            // The store collects and remits the sales tax itself; there is no
            // separate line for us to record.
            'tax_amount' => 0,
            'total_amount' => $amount,
            'currency' => $this->currency($props, $subscription),
            'status' => 'paid',
            'paid_at' => now(),
            'payment_method' => 'adapty',
            'provider' => 'adapty',
            'provider_reference' => $reference,
            'notes' => $this->storeLabel($props),
        ]);
    }

    // --- resolution -----------------------------------------------------------

    /**
     * Whose purchase this is.
     *
     * customer_user_id is the users.id the app handed adapty.identify(); the
     * profile id is the fallback for a purchase made before sign-up, claimed
     * later through the link endpoint.
     */
    private function resolveUser(array $payload): ?User
    {
        $customerUserId = $payload['customer_user_id'] ?? null;

        if (is_scalar($customerUserId) && ctype_digit((string) $customerUserId)) {
            $user = User::find((int) $customerUserId);
            if ($user) {
                $this->attachProfile($user, $payload['profile_id'] ?? null);

                return $user;
            }
        }

        $profileId = $payload['profile_id'] ?? null;
        if (is_string($profileId) && $profileId !== '') {
            return User::where('adapty_profile_id', $profileId)->first();
        }

        return null;
    }

    private function attachProfile(User $user, mixed $profileId): void
    {
        if (is_string($profileId) && $profileId !== '' && $user->adapty_profile_id !== $profileId) {
            $user->forceFill(['adapty_profile_id' => $profileId])->save();
        }
    }

    /**
     * The local row for this store subscription, most-specific first: the
     * transaction lineage, then an adapty row of this user's that has not been
     * tied to one yet.
     */
    private function localSubscription(User $user, array $props): ?Subscription
    {
        $external = $this->externalId($props);

        if ($external) {
            $match = Subscription::where('payment_provider', 'adapty')
                ->where('external_id', $external)
                ->first();
            if ($match) {
                return $match;
            }
        }

        return $user->subscriptions()
            ->where('payment_provider', 'adapty')
            ->whereNull('external_id')
            ->latest('id')
            ->first();
    }

    private function createSubscription(User $user, array $props, ?Carbon $expiresAt): ?Subscription
    {
        $plan = $this->subscriptions->premiumPlanFor($user);

        if (! $plan) {
            // A configuration problem, not a delivery problem: retrying the
            // webhook cannot conjure a Plan row. Acknowledged loudly so it is
            // fixed and the event replayed from the Adapty dashboard.
            Log::error('Adapty webhook: no premium plan configured for this role', [
                'user_id' => $user->id,
                'role' => $user->role,
            ]);

            return null;
        }

        return Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            // activate() / renew() is what makes it real; 'pending' is the same
            // starting point the web checkout uses.
            'status' => 'pending',
            'billing_cycle' => $this->cycleFor($props, $expiresAt),
            'auto_renew' => true,
            'starts_at' => $this->timestamp($props, ['purchase_date', 'original_purchase_date', 'purchased_at']) ?? now(),
            'amount' => $this->amount($props),
            'currency' => $this->currency($props, null),
            'payment_provider' => 'adapty',
            'external_id' => $this->externalId($props),
        ]);
    }

    /** Tie a row to its store transaction lineage the first time we see one. */
    private function link(Subscription $subscription, array $props): void
    {
        $external = $this->externalId($props);

        if ($external && $subscription->external_id !== $external) {
            $subscription->forceFill([
                'external_id' => $external,
                'payment_provider' => 'adapty',
            ])->save();
        }
    }

    // --- reading the payload --------------------------------------------------

    private function eventType(array $payload): string
    {
        return strtolower(trim((string) ($payload['event_type'] ?? $payload['type'] ?? '')));
    }

    private function properties(array $payload): array
    {
        $props = $payload['event_properties'] ?? $payload['properties'] ?? [];

        // Older payload versions put the same fields at the top level; merging
        // means one reader covers both.
        return is_array($props) ? array_merge($payload, $props) : $payload;
    }

    /**
     * What this event asks us to do.
     *
     * Anything unrecognised falls back to the only question that actually
     * matters — is the access level still in date? — so a new or renamed Adapty
     * event still lands the customer in the right state instead of being
     * dropped.
     */
    private function intentFor(string $type, ?Carbon $expiresAt): string
    {
        return match ($type) {
            'subscription_initial_purchase', 'subscription_started', 'initial_purchase',
            'subscription_restored', 'trial_started', 'trial_converted',
            'non_subscription_purchase' => self::GRANT,

            'subscription_renewed', 'trial_renewed' => self::RENEW,

            'subscription_expired', 'trial_expired', 'subscription_paused' => self::REVOKE,

            'subscription_refunded', 'refund' => self::REFUND,

            'subscription_renewal_cancelled', 'trial_renewal_cancelled' => self::CANCEL,

            'subscription_renewal_reactivated', 'trial_renewal_reactivated' => self::REACTIVATE,

            'billing_issue_detected', 'entered_grace_period' => self::BILLING_ISSUE,

            default => $this->inferIntent($type, $expiresAt),
        };
    }

    private function inferIntent(string $type, ?Carbon $expiresAt): string
    {
        if (! $expiresAt) {
            Log::info('Adapty webhook: unhandled event acknowledged', ['type' => $type]);

            return self::IGNORE;
        }

        return $expiresAt->isFuture() ? self::GRANT : self::REVOKE;
    }

    /**
     * Whether this event is about the access level the app sells. Absent — some
     * event types carry no access level at all — is treated as ours, because the
     * app only sells one.
     */
    private function concernsPremium(array $props): bool
    {
        $level = $props['access_level_id'] ?? $props['access_level'] ?? null;

        if (! is_string($level) || $level === '') {
            return true;
        }

        return $level === config('services.adapty.access_level', 'premium');
    }

    private function isSandbox(array $props): bool
    {
        $environment = strtolower((string) ($props['environment'] ?? ''));

        return $environment === 'sandbox' || (bool) ($props['is_sandbox'] ?? false);
    }

    private function isTrial(array $props): bool
    {
        return (bool) ($props['is_trial'] ?? $props['is_in_trial'] ?? false);
    }

    /** The store transaction lineage: stable across every renewal. */
    private function externalId(array $props): ?string
    {
        $value = $props['original_transaction_id'] ?? $props['transaction_id'] ?? null;

        return is_scalar($value) && (string) $value !== '' ? (string) $value : null;
    }

    /** This particular charge, used to key the invoice it produced. */
    private function transactionId(array $props): ?string
    {
        $value = $props['transaction_id'] ?? $props['original_transaction_id'] ?? null;

        return is_scalar($value) && (string) $value !== '' ? (string) $value : null;
    }

    private function amount(array $props): float
    {
        $value = $props['price_local'] ?? $props['price_usd'] ?? $props['price'] ?? 0;

        return is_numeric($value) ? round((float) $value, 2) : 0.0;
    }

    private function currency(array $props, ?Subscription $subscription): string
    {
        $currency = $props['currency'] ?? $props['store_currency'] ?? null;

        if (! is_string($currency) || $currency === '') {
            // price_usd is the only amount left when the store did not report a
            // local one, and it is denominated in exactly one currency.
            $currency = isset($props['price_local'])
                ? ($subscription?->currency ?: config('payments.currency', 'USD'))
                : 'USD';
        }

        return strtoupper($currency);
    }

    private function storeLabel(array $props): ?string
    {
        $store = $props['store'] ?? null;
        $product = $props['vendor_product_id'] ?? null;

        if (! is_string($product) || $product === '') {
            return null;
        }

        return trim((is_string($store) ? $store.' ' : '').$product);
    }

    /**
     * Which cycle was bought, best evidence first: the mapping the dashboard's
     * product ids are declared under, then the product id's own wording, then how
     * long the period the store granted actually is.
     */
    private function cycleFor(array $props, ?Carbon $expiresAt): string
    {
        $product = (string) ($props['vendor_product_id'] ?? '');

        // Product ids contain dots, which config() would read as nesting.
        $configured = config('payments.adapty.products', [])[$product] ?? null;
        if (is_string($configured) && $configured !== '') {
            return $configured;
        }

        $name = strtolower($product);
        foreach (['year' => 'yearly', 'annual' => 'yearly', 'month' => 'monthly', 'week' => 'weekly'] as $needle => $cycle) {
            if (str_contains($name, $needle)) {
                return $cycle;
            }
        }

        // A trial period is not the cycle it converts into, so its length tells
        // us nothing; monthly is the safer guess for the ledger.
        if ($this->isTrial($props) || ! $expiresAt) {
            return 'monthly';
        }

        $start = $this->timestamp($props, ['purchase_date', 'original_purchase_date', 'purchased_at']) ?? now();
        $days = abs($start->diffInDays($expiresAt));

        return match (true) {
            $days >= 300 => 'yearly',
            $days >= 25 => 'monthly',
            default => 'weekly',
        };
    }

    /**
     * @param  string[]  $keys
     */
    private function timestamp(array $props, array $keys): ?Carbon
    {
        foreach ($keys as $key) {
            $value = $props[$key] ?? null;

            if (is_numeric($value)) {
                // Adapty sends ISO-8601 strings, but epoch seconds (and
                // milliseconds) turn up in older integrations.
                $value = (float) $value;

                return Carbon::createFromTimestamp($value > 1e11 ? $value / 1000 : $value);
            }

            if (is_string($value) && $value !== '') {
                try {
                    return Carbon::parse($value);
                } catch (\Throwable) {
                    continue;
                }
            }
        }

        return null;
    }
}
