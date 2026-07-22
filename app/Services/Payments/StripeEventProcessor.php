<?php

namespace App\Services\Payments;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Reconciles the local subscription/invoice/entitlement state from Stripe webhook
 * events (and the checkout return page). Deliberately network-free: every handler
 * works off the plain event-object array Stripe delivers, so the whole lifecycle
 * is unit-testable without hitting the API.
 *
 * Idempotency (each Stripe event may arrive more than once) is enforced upstream
 * by StripeWebhookController via the stripe_events ledger; the handlers here are
 * additionally written to be safe to re-run.
 */
class StripeEventProcessor
{
    public function __construct(private SubscriptionService $subscriptions) {}

    /**
     * Dispatch one event to its handler. $object is the Stripe event's
     * `data.object`, already converted to an array.
     */
    public function handle(string $type, array $object): void
    {
        match ($type) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($object),
            'invoice.paid', 'invoice.payment_succeeded' => $this->handleInvoicePaid($object),
            'invoice.payment_failed' => $this->handleInvoicePaymentFailed($object),
            'customer.subscription.updated' => $this->handleSubscriptionUpdated($object),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($object),
            default => null, // events we don't act on are acknowledged and ignored
        };
    }

    private function handleCheckoutCompleted(array $session): void
    {
        // Only subscription checkouts that actually completed payment.
        if (($session['mode'] ?? null) !== 'subscription') {
            return;
        }
        if (! in_array($session['payment_status'] ?? null, ['paid', 'no_payment_required'], true)) {
            return;
        }

        $local = $this->resolveLocal([
            'local_id' => $session['client_reference_id'] ?? data_get($session, 'metadata.subscription_id'),
            'customer' => $session['customer'] ?? null,
        ]);
        if (! $local) {
            Log::warning('Stripe checkout.session.completed: no local subscription', ['session' => $session['id'] ?? null]);

            return;
        }

        // Link the local subscription to the Stripe subscription + customer.
        $stripeSub = $this->stringId($session['subscription'] ?? null);
        $customer = $this->stringId($session['customer'] ?? null);

        $local->forceFill(array_filter([
            'external_id' => $stripeSub,
            'payment_provider' => 'stripe',
        ]))->save();

        if ($customer) {
            $this->attachCustomer($local->user, $customer);
        }

        // Grant Premium now (period end reconciled precisely by invoice.paid).
        $this->subscriptions->activate($local);
    }

    private function handleInvoicePaid(array $invoice): void
    {
        $local = $this->resolveLocal([
            'local_id' => data_get($invoice, 'subscription_details.metadata.subscription_id')
                ?? data_get($invoice, 'lines.data.0.metadata.subscription_id'),
            'stripe_sub' => $this->stringId($invoice['subscription'] ?? data_get($invoice, 'parent.subscription_details.subscription')),
            'customer' => $this->stringId($invoice['customer'] ?? null),
        ]);
        if (! $local) {
            Log::warning('Stripe invoice.paid: no local subscription', ['invoice' => $invoice['id'] ?? null]);

            return;
        }

        // Make sure we're linked (covers invoice.paid arriving before checkout.session.completed).
        $stripeSub = $this->stringId($invoice['subscription'] ?? null);
        if ($stripeSub && $local->external_id !== $stripeSub) {
            $local->forceFill(['external_id' => $stripeSub, 'payment_provider' => 'stripe'])->save();
        }
        if ($customer = $this->stringId($invoice['customer'] ?? null)) {
            $this->attachCustomer($local->user, $customer);
        }

        $periodEnd = $this->periodEnd($invoice);
        $paymentRef = $this->paymentReference($invoice);
        $reason = $invoice['billing_reason'] ?? null;

        // First payment (or a first payment we hadn't activated yet) vs. a renewal.
        if ($reason === 'subscription_create' || $local->status !== 'active') {
            $this->subscriptions->activate($local, $periodEnd, $paymentRef);

            return;
        }

        $amount = ((int) ($invoice['amount_paid'] ?? 0)) / 100;
        $currency = strtoupper((string) ($invoice['currency'] ?? $local->currency));

        $this->subscriptions->renew($local, $periodEnd, $amount, $currency, $paymentRef, $invoice['number'] ?? null);
    }

    private function handleInvoicePaymentFailed(array $invoice): void
    {
        $local = $this->resolveLocal([
            'stripe_sub' => $this->stringId($invoice['subscription'] ?? null),
            'customer' => $this->stringId($invoice['customer'] ?? null),
        ]);
        if (! $local || $local->status !== 'active') {
            return;
        }

        // Keep entitlement until the paid-through period ends; Stripe dunning will
        // retry. customer.subscription.deleted downgrades if it ultimately fails.
        $local->update(['status' => 'past_due']);
    }

    private function handleSubscriptionUpdated(array $sub): void
    {
        $local = $this->resolveLocal([
            'local_id' => data_get($sub, 'metadata.subscription_id'),
            'stripe_sub' => $this->stringId($sub['id'] ?? null),
            'customer' => $this->stringId($sub['customer'] ?? null),
        ]);
        if (! $local) {
            return;
        }

        $updates = [];

        // Cancellation scheduled/cleared from the Customer Portal or Dashboard.
        if (array_key_exists('cancel_at_period_end', $sub)) {
            $updates['auto_renew'] = ! $sub['cancel_at_period_end'];
            $updates['cancelled_at'] = $sub['cancel_at_period_end'] ? now() : null;
        }

        // A recovered ('active') subscription clears a prior past_due flag.
        if (($sub['status'] ?? null) === 'active' && $local->status === 'past_due') {
            $updates['status'] = 'active';
        }

        if ($updates) {
            $local->update($updates);
        }
    }

    private function handleSubscriptionDeleted(array $sub): void
    {
        $local = $this->resolveLocal([
            'local_id' => data_get($sub, 'metadata.subscription_id'),
            'stripe_sub' => $this->stringId($sub['id'] ?? null),
            'customer' => $this->stringId($sub['customer'] ?? null),
        ]);
        if (! $local || in_array($local->status, ['expired', 'cancelled'], true)) {
            return;
        }

        // Stripe stopped billing this subscription for good — drop entitlement.
        $this->subscriptions->expire($local);
    }

    /**
     * Locate the local Subscription from whatever identifiers an event carries,
     * most-specific first: our own id, the Stripe subscription id, then the
     * customer's most recent Stripe subscription.
     *
     * @param  array{local_id?:mixed, stripe_sub?:?string, customer?:?string}  $hints
     */
    private function resolveLocal(array $hints): ?Subscription
    {
        if (! empty($hints['local_id']) && ($s = Subscription::find($hints['local_id']))) {
            return $s;
        }

        if (! empty($hints['stripe_sub']) && ($s = Subscription::where('external_id', $hints['stripe_sub'])->first())) {
            return $s;
        }

        if (! empty($hints['customer'])) {
            $user = User::where('stripe_customer_id', $hints['customer'])->first();
            if ($user) {
                return $user->subscriptions()
                    ->where('payment_provider', 'stripe')
                    ->latest('id')
                    ->first();
            }
        }

        return null;
    }

    private function attachCustomer(User $user, string $customerId): void
    {
        if ($user->stripe_customer_id !== $customerId) {
            $user->forceFill(['stripe_customer_id' => $customerId])->save();
        }
    }

    /** Precise paid-through instant from a Stripe invoice, if present. */
    private function periodEnd(array $invoice): ?Carbon
    {
        $ts = data_get($invoice, 'lines.data.0.period.end')
            ?? ($invoice['period_end'] ?? null);

        return $ts ? Carbon::createFromTimestamp($ts) : null;
    }

    /** The charge reference we can later refund (payment_intent, else charge). */
    private function paymentReference(array $invoice): ?string
    {
        return $this->stringId($invoice['payment_intent'] ?? null)
            ?? $this->stringId(data_get($invoice, 'payments.data.0.payment.payment_intent'))
            ?? $this->stringId($invoice['charge'] ?? null);
    }

    /** Stripe fields are sometimes an id string and sometimes an expanded object. */
    private function stringId(mixed $value): ?string
    {
        if (is_string($value) && $value !== '') {
            return $value;
        }
        if (is_array($value) && ! empty($value['id'])) {
            return (string) $value['id'];
        }

        return null;
    }
}
