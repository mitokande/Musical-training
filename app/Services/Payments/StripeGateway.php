<?php

namespace App\Services\Payments;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Payments\Contracts\PaymentGateway;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

/**
 * Recurring Premium subscriptions via Stripe Billing + Stripe Checkout.
 *
 * checkout() opens a hosted `mode: subscription` Checkout Session and returns its
 * URL; Stripe then bills the customer every cycle automatically. The durable
 * source of truth for the subscription lifecycle is the webhook
 * (StripeEventProcessor) — this gateway only starts checkout, reconciles the
 * return redirect, and forwards refunds / cancellations to Stripe.
 */
class StripeGateway implements PaymentGateway
{
    /**
     * Stable label (with an 8-letter suffix) so these Checkout Sessions can be
     * compared as one integration in the Stripe Dashboard.
     */
    private const INTEGRATION_IDENTIFIER = 'harmoniva_premium_qwbnlxpz';

    public function __construct(
        private StripeClient $stripe,
        private StripeEventProcessor $processor,
    ) {}

    public function key(): string
    {
        return 'stripe';
    }

    public function checkout(Subscription $subscription, Invoice $invoice): string
    {
        $user = $subscription->user;
        $customerId = $this->resolveCustomer($user);

        // Role-specific price (teacher/school/user tiers each bill their own
        // amount); fall back to the legacy flat key for the individual-user tier.
        $role = $subscription->plan->role ?? 'user';
        $cycle = $subscription->billing_cycle;
        $priceId = config("services.stripe.prices.{$role}.{$cycle}")
            ?? config("services.stripe.prices.{$cycle}");
        if (! $priceId) {
            throw new \RuntimeException("No Stripe price configured for [{$role}/{$cycle}].");
        }

        $session = $this->stripe->checkout->sessions->create([
            'mode' => 'subscription',
            'customer' => $customerId,
            // Do NOT set payment_method_types — let Stripe pick eligible methods
            // dynamically from the Dashboard configuration (maximises conversion).
            'line_items' => [[
                'price' => $priceId,
                'quantity' => 1,
            ]],
            // Both point back at the local records so the webhook + return page
            // can reconcile regardless of event ordering.
            'client_reference_id' => (string) $subscription->id,
            'metadata' => [
                'subscription_id' => (string) $subscription->id,
                'invoice_id' => (string) $invoice->id,
                'user_id' => (string) $user->id,
            ],
            'subscription_data' => [
                'metadata' => [
                    'subscription_id' => (string) $subscription->id,
                    'user_id' => (string) $user->id,
                ],
            ],
            'allow_promotion_codes' => true,
            'billing_address_collection' => 'auto',
            'integration_identifier' => self::INTEGRATION_IDENTIFIER,
            'success_url' => route('checkout.success', $subscription).'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.show', ['cycle' => $subscription->billing_cycle]),
        ]);

        // Note the checkout session on the invoice for traceability. invoice.provider_reference
        // is left free for the payment_intent the webhook records (used by refund()).
        $invoice->forceFill(['notes' => 'Stripe checkout '.$session->id])->save();

        return $session->url;
    }

    /**
     * Called from the checkout return page: fetch the completed session and grant
     * Premium immediately, so access does not wait on webhook delivery. The
     * webhook remains the durable backstop — both paths are idempotent.
     */
    public function fulfillReturn(string $sessionId): void
    {
        try {
            $session = $this->stripe->checkout->sessions->retrieve($sessionId, []);
            $this->processor->handle('checkout.session.completed', $session->toArray());
        } catch (ApiErrorException $e) {
            report($e);
        }
    }

    public function refund(Invoice $invoice): bool
    {
        try {
            // Refund the captured charge (payment_intent recorded when the payment
            // succeeded). Skip gracefully if we never captured a reference.
            $ref = $invoice->provider_reference;
            if (is_string($ref) && str_starts_with($ref, 'pi_')) {
                $this->stripe->refunds->create(['payment_intent' => $ref]);
            } elseif (is_string($ref) && str_starts_with($ref, 'ch_')) {
                $this->stripe->refunds->create(['charge' => $ref]);
            }

            // Stop future billing on the underlying Stripe subscription.
            $sub = $invoice->subscription;
            if ($sub && $this->isStripeSubscriptionId($sub->external_id)) {
                $this->stripe->subscriptions->cancel($sub->external_id);
            }

            return true;
        } catch (ApiErrorException $e) {
            report($e);

            return false;
        }
    }

    public function cancelAtProvider(Subscription $subscription, bool $immediate): void
    {
        if (! $this->isStripeSubscriptionId($subscription->external_id)) {
            return;
        }

        try {
            if ($immediate) {
                $this->stripe->subscriptions->cancel($subscription->external_id);
            } else {
                $this->stripe->subscriptions->update($subscription->external_id, [
                    'cancel_at_period_end' => true,
                ]);
            }
        } catch (ApiErrorException $e) {
            // Best-effort: the local ledger is already updated by the caller.
            report($e);
        }
    }

    /**
     * Reuse the user's Stripe Customer across purchases, creating one on first
     * checkout. Guarantees a one-user-to-one-customer mapping.
     */
    private function resolveCustomer(User $user): string
    {
        if ($user->stripe_customer_id) {
            return $user->stripe_customer_id;
        }

        $customer = $this->stripe->customers->create([
            'email' => $user->email,
            'name' => $user->name,
            'metadata' => ['user_id' => (string) $user->id],
        ]);

        $user->forceFill(['stripe_customer_id' => $customer->id])->save();

        return $customer->id;
    }

    private function isStripeSubscriptionId(?string $id): bool
    {
        return is_string($id) && str_starts_with($id, 'sub_');
    }
}
