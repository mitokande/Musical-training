<?php

namespace App\Services\Payments;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Payments\Contracts\PaymentGateway;
use App\Services\Payments\Paddle\PaddleApi;
use Paddle\SDK\Exceptions\ApiError;
use RuntimeException;

/**
 * Recurring Premium subscriptions via Paddle Billing.
 *
 * checkout() opens a Paddle transaction for the plan's recurring price and
 * returns the URL to send the buyer to — our own /pay page with `?_ptxn=…`
 * appended, where Paddle.js opens the checkout. Paddle then bills the customer
 * every cycle by itself. The durable source of truth for the lifecycle is the
 * webhook (PaddleEventProcessor); this gateway only starts the checkout and
 * forwards refunds and cancellations.
 *
 * Unlike Stripe, Paddle is the Merchant of Record: it sells to the customer on
 * our behalf and it decides the tax. The amount the buyer is shown is whatever
 * Paddle computes from the price — config('payments.tax_rate') plays no part in
 * a Paddle checkout.
 */
class PaddleGateway implements PaymentGateway
{
    public function __construct(private PaddleApi $api) {}

    public function key(): string
    {
        return 'paddle';
    }

    public function checkout(Subscription $subscription, Invoice $invoice): string
    {
        $user = $subscription->user;
        $customerId = $this->resolveCustomer($user);

        // Role-specific price (teacher/school/user tiers each bill their own
        // amount); fall back to the flat key for the individual-user tier.
        $role = $subscription->plan->role ?? 'user';
        $cycle = $subscription->billing_cycle;
        $priceId = config("services.paddle.prices.{$role}.{$cycle}")
            ?? config("services.paddle.prices.{$cycle}");
        if (! $priceId) {
            throw new RuntimeException("No Paddle price configured for [{$role}/{$cycle}].");
        }

        $transaction = $this->api->createTransaction($priceId, $customerId, [
            // Our own ids, so the webhook can find the local records no matter
            // which event arrives first. Deliberately prefixed 'local_':
            // Paddle's payloads have their own subscription_id/customer_id
            // fields and confusing the two silently attaches a purchase to the
            // wrong row.
            'local_subscription_id' => (string) $subscription->id,
            'local_invoice_id' => (string) $invoice->id,
            'local_user_id' => (string) $user->id,
        ]);

        if (empty($transaction['checkout_url'])) {
            // Paddle only omits the checkout URL when the account has no
            // approved default payment link, which no retry will fix.
            throw new RuntimeException(
                'Paddle returned no checkout URL for transaction '.$transaction['id']
                .' — check the default payment link under Checkout settings.'
            );
        }

        // Note the transaction on the invoice for traceability.
        // invoice.provider_reference is left free for the completed transaction
        // id the webhook records (which is what refund() acts on).
        $invoice->forceFill(['notes' => 'Paddle transaction '.$transaction['id']])->save();

        return $transaction['checkout_url'];
    }

    public function refund(Invoice $invoice): bool
    {
        $ref = $invoice->provider_reference;
        if (! is_string($ref) || ! str_starts_with($ref, 'txn_')) {
            // Nothing captured under a Paddle transaction — refunding here
            // would report money moving that never did.
            return false;
        }

        try {
            $status = $this->api->refundTransaction($ref, 'Customer refund requested');
        } catch (ApiError $e) {
            report($e);

            return false;
        }

        // 'approved' is money on its way back. 'pending_approval' is Paddle
        // reviewing it — the common case only for amounts over 400 USD, which
        // no plan we sell reaches, but it still has to be reported honestly:
        // the caller marks the invoice refunded either way, and a later
        // 'rejected' arrives as adjustment.updated and undoes that.
        if (in_array($status, ['approved', 'pending_approval'], true)) {
            return true;
        }

        return false;
    }

    public function cancelAtProvider(Subscription $subscription, bool $immediate): void
    {
        if (! $this->isPaddleSubscriptionId($subscription->external_id)) {
            return;
        }

        try {
            $this->api->cancelSubscription($subscription->external_id, $immediate);
        } catch (ApiError $e) {
            // Best-effort: the local ledger is already updated by the caller.
            report($e);
        }
    }

    /**
     * Reuse the user's Paddle Customer across purchases, creating one on first
     * checkout. Guarantees a one-user-to-one-customer mapping.
     */
    private function resolveCustomer(User $user): string
    {
        if ($user->paddle_customer_id) {
            return $user->paddle_customer_id;
        }

        $customerId = $this->api->createCustomer($user->email, $user->name, [
            'local_user_id' => (string) $user->id,
        ]);

        $user->forceFill(['paddle_customer_id' => $customerId])->save();

        return $customerId;
    }

    private function isPaddleSubscriptionId(?string $id): bool
    {
        return is_string($id) && str_starts_with($id, 'sub_');
    }
}
