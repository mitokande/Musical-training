<?php

namespace App\Services\Payments;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Reconciles the local subscription/invoice/entitlement state from Paddle
 * webhook events. Deliberately network-free: every handler works off the plain
 * `data` array Paddle delivers, so the whole lifecycle is unit-testable without
 * touching the API.
 *
 * Idempotency (Paddle retries every non-2xx delivery) is enforced upstream by
 * PaddleWebhookController via the paddle_events ledger; the handlers here are
 * additionally written to be safe to re-run.
 */
class PaddleEventProcessor
{
    /**
     * Currencies Paddle quotes without minor units — an amount of "1000" is
     * 1000, not 10.00. Everything else is cents/pence/etc.
     */
    private const ZERO_DECIMAL_CURRENCIES = ['JPY', 'KRW', 'CLP'];

    public function __construct(private SubscriptionService $subscriptions) {}

    /**
     * Dispatch one event to its handler. $object is the Paddle event's `data`.
     */
    public function handle(string $type, array $object): void
    {
        match ($type) {
            'transaction.completed' => $this->handleTransactionCompleted($object),
            'transaction.payment_failed' => $this->handleTransactionPaymentFailed($object),
            'subscription.created', 'subscription.activated' => $this->handleSubscriptionActivated($object),
            'subscription.trialing' => $this->handleSubscriptionTrialing($object),
            'subscription.updated' => $this->handleSubscriptionUpdated($object),
            'subscription.past_due' => $this->handleSubscriptionPastDue($object),
            'subscription.canceled' => $this->handleSubscriptionCanceled($object),
            'adjustment.updated' => $this->handleAdjustmentUpdated($object),
            default => null, // events we don't act on are acknowledged and ignored
        };
    }

    /**
     * The money event: Paddle captured a payment. Covers both the first charge
     * and every renewal — `origin` is what tells them apart.
     */
    private function handleTransactionCompleted(array $txn): void
    {
        $local = $this->resolveLocal([
            'local_id' => data_get($txn, 'custom_data.local_subscription_id'),
            'paddle_sub' => $txn['subscription_id'] ?? null,
            'customer' => $txn['customer_id'] ?? null,
        ]);
        if (! $local) {
            Log::warning('Paddle transaction.completed: no local subscription', ['transaction' => $txn['id'] ?? null]);

            return;
        }

        $this->link($local, $txn['subscription_id'] ?? null, $txn['customer_id'] ?? null);

        $periodEnd = $this->periodEnd(data_get($txn, 'billing_period.ends_at'));
        // The transaction id is the handle a refund is created against later.
        $paymentRef = $txn['id'] ?? null;

        $currency = strtoupper((string) ($txn['currency_code'] ?? $local->currency));
        $amount = $this->toMajorUnits(data_get($txn, 'details.totals.grand_total'), $currency);

        // Paddle raises a completed transaction worth nothing when a trial (or a
        // 100% discount) starts. It is not a payment: booking it as one would
        // record revenue that was never collected, and would mark the pending
        // invoice paid before anyone has been charged.
        if ($amount <= 0.0) {
            $this->subscriptions->startProviderTrial($local, $periodEnd);

            return;
        }

        // A recurring charge Paddle raised on its own vs. the first payment
        // (origin 'web' for a checkout, 'api'/'subscription_charge' otherwise).
        $isRenewal = ($txn['origin'] ?? null) === 'subscription_recurring';

        if (! $isRenewal || $local->status !== 'active') {
            $this->subscriptions->activate($local, $periodEnd, $paymentRef);

            return;
        }

        $number = $txn['invoice_number'] ?? null;

        $this->subscriptions->renew(
            $local,
            $periodEnd,
            $amount,
            $currency,
            $paymentRef,
            $number ? "Paddle invoice {$number}" : null,
        );
    }

    private function handleTransactionPaymentFailed(array $txn): void
    {
        $local = $this->resolveLocal([
            'local_id' => data_get($txn, 'custom_data.local_subscription_id'),
            'paddle_sub' => $txn['subscription_id'] ?? null,
            'customer' => $txn['customer_id'] ?? null,
        ]);
        if (! $local || $local->status !== 'active') {
            return;
        }

        // Keep entitlement until the paid-through period ends; Paddle's dunning
        // will retry. subscription.canceled downgrades if it ultimately fails.
        $local->update(['status' => 'past_due']);
    }

    /**
     * Paddle considers the subscription live. Grants Premium without waiting on
     * transaction.completed, which may arrive in either order — both paths are
     * idempotent.
     */
    private function handleSubscriptionActivated(array $sub): void
    {
        $local = $this->resolveLocal([
            'local_id' => data_get($sub, 'custom_data.local_subscription_id'),
            'paddle_sub' => $sub['id'] ?? null,
            'customer' => $sub['customer_id'] ?? null,
        ]);
        if (! $local) {
            Log::warning('Paddle subscription event: no local subscription', ['subscription' => $sub['id'] ?? null]);

            return;
        }

        $this->link($local, $sub['id'] ?? null, $sub['customer_id'] ?? null);

        // A subscription Paddle has only created (not yet paid) must not grant
        // anything — the transaction.completed event does that.
        if (($sub['status'] ?? null) !== 'active') {
            return;
        }

        $this->subscriptions->activate($local, $this->periodEnd(data_get($sub, 'current_billing_period.ends_at')));
    }

    /**
     * Paddle put the subscription into its trial period. Grants Premium without
     * recording a payment — see SubscriptionService::startProviderTrial().
     */
    private function handleSubscriptionTrialing(array $sub): void
    {
        $local = $this->resolveLocal([
            'local_id' => data_get($sub, 'custom_data.local_subscription_id'),
            'paddle_sub' => $sub['id'] ?? null,
            'customer' => $sub['customer_id'] ?? null,
        ]);
        if (! $local) {
            Log::warning('Paddle subscription.trialing: no local subscription', ['subscription' => $sub['id'] ?? null]);

            return;
        }

        $this->link($local, $sub['id'] ?? null, $sub['customer_id'] ?? null);

        $this->subscriptions->startProviderTrial(
            $local,
            $this->periodEnd(data_get($sub, 'current_billing_period.ends_at')),
        );
    }

    private function handleSubscriptionUpdated(array $sub): void
    {
        $local = $this->resolveLocal([
            'local_id' => data_get($sub, 'custom_data.local_subscription_id'),
            'paddle_sub' => $sub['id'] ?? null,
            'customer' => $sub['customer_id'] ?? null,
        ]);
        if (! $local) {
            return;
        }

        $updates = [];

        // Paddle expresses "will cancel at period end" as a scheduled change
        // rather than Stripe's cancel_at_period_end boolean. The key is present
        // on every subscription.updated payload, null when nothing is pending.
        if (array_key_exists('scheduled_change', $sub)) {
            $cancelling = data_get($sub, 'scheduled_change.action') === 'cancel';
            $updates['auto_renew'] = ! $cancelling;
            $updates['cancelled_at'] = $cancelling ? now() : null;
        }

        // A recovered ('active') subscription clears a prior past_due flag.
        if (($sub['status'] ?? null) === 'active' && $local->status === 'past_due') {
            $updates['status'] = 'active';
        }

        // Keep the paid-through date in step (a plan change moves it).
        if ($end = $this->periodEnd(data_get($sub, 'current_billing_period.ends_at'))) {
            $updates['ends_at'] = $end;
        }

        if ($updates) {
            $local->update($updates);
        }
    }

    private function handleSubscriptionPastDue(array $sub): void
    {
        $local = $this->resolveLocal([
            'local_id' => data_get($sub, 'custom_data.local_subscription_id'),
            'paddle_sub' => $sub['id'] ?? null,
            'customer' => $sub['customer_id'] ?? null,
        ]);
        if (! $local || $local->status !== 'active') {
            return;
        }

        $local->update(['status' => 'past_due']);
    }

    private function handleSubscriptionCanceled(array $sub): void
    {
        $local = $this->resolveLocal([
            'local_id' => data_get($sub, 'custom_data.local_subscription_id'),
            'paddle_sub' => $sub['id'] ?? null,
            'customer' => $sub['customer_id'] ?? null,
        ]);
        if (! $local || in_array($local->status, ['expired', 'cancelled'], true)) {
            return;
        }

        // Paddle stopped billing this subscription for good — drop entitlement.
        $this->subscriptions->expire($local);
    }

    /**
     * A refund we asked for was reviewed by Paddle.
     *
     * We mark an invoice refunded as soon as the adjustment is accepted, since
     * refunds below 400 USD on a verified account are approved automatically
     * and every plan we sell is far below that. This handler exists for the
     * case where that optimism was wrong: a rejected refund has to put the
     * invoice back, or the books show money returned that Paddle kept.
     */
    private function handleAdjustmentUpdated(array $adjustment): void
    {
        if (($adjustment['action'] ?? null) !== 'refund') {
            return;
        }

        $transactionId = $adjustment['transaction_id'] ?? null;
        if (! $transactionId) {
            return;
        }

        $invoice = Invoice::where('provider', 'paddle')
            ->where('provider_reference', $transactionId)
            ->latest('id')
            ->first();
        if (! $invoice) {
            return;
        }

        if (($adjustment['status'] ?? null) === 'rejected' && $invoice->status === 'refunded') {
            // Deliberately does not restore the subscription: the cancellation
            // that went with the refund has already been sent to Paddle and
            // cannot be un-sent from here. Someone has to look at this.
            $this->subscriptions->revertRefund($invoice);

            Log::error('Paddle rejected a refund already recorded as refunded', [
                'invoice_id' => $invoice->id,
                'adjustment_id' => $adjustment['id'] ?? null,
                'transaction_id' => $transactionId,
            ]);
        }
    }

    /**
     * Attach the Paddle subscription + customer to the local row. Covers events
     * arriving in any order, and is a no-op once already linked.
     */
    private function link(Subscription $local, ?string $paddleSubId, ?string $customerId): void
    {
        if ($paddleSubId && $local->external_id !== $paddleSubId) {
            $local->forceFill(['external_id' => $paddleSubId, 'payment_provider' => 'paddle'])->save();
        }

        if ($customerId) {
            $user = $local->user;
            if ($user && $user->paddle_customer_id !== $customerId) {
                $user->forceFill(['paddle_customer_id' => $customerId])->save();
            }
        }
    }

    /**
     * Locate the local Subscription from whatever identifiers an event carries,
     * most-specific first: our own id (round-tripped through custom_data), the
     * Paddle subscription id, then the customer's most recent Paddle row.
     *
     * @param  array{local_id?:mixed, paddle_sub?:?string, customer?:?string}  $hints
     */
    private function resolveLocal(array $hints): ?Subscription
    {
        if (! empty($hints['local_id']) && ($s = Subscription::find($hints['local_id']))) {
            return $s;
        }

        if (! empty($hints['paddle_sub'])) {
            $s = Subscription::where('external_id', $hints['paddle_sub'])
                ->where('payment_provider', 'paddle')
                ->first();
            if ($s) {
                return $s;
            }
        }

        if (! empty($hints['customer'])) {
            $user = User::where('paddle_customer_id', $hints['customer'])->first();
            if ($user) {
                return $user->subscriptions()
                    ->where('payment_provider', 'paddle')
                    ->latest('id')
                    ->first();
            }
        }

        return null;
    }

    /** Paddle timestamps are RFC 3339 strings, not unix seconds. */
    private function periodEnd(mixed $value): ?Carbon
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Paddle sends money as a string in the currency's lowest denomination.
     */
    private function toMajorUnits(mixed $amount, string $currency): float
    {
        $raw = (float) ($amount ?? 0);

        return in_array(strtoupper($currency), self::ZERO_DECIMAL_CURRENCIES, true)
            ? $raw
            : $raw / 100;
    }
}
