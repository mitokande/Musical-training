<?php

namespace App\Services\Payments;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Services\Payments\Contracts\PaymentGateway;

/**
 * No external provider. Depending on config('payments.manual.auto_confirm'):
 *  - true  → payment is treated as instantly successful (test / staging) and
 *            Premium is granted right away.
 *  - false → the subscription stays 'pending' for an admin to confirm from the
 *            admin panel before Premium is granted.
 *
 * This keeps the entire checkout → subscription → invoice → entitlement flow
 * fully functional before a real gateway is chosen and wired.
 */
class ManualGateway implements PaymentGateway
{
    public function __construct(private SubscriptionService $subscriptions) {}

    public function key(): string
    {
        return 'manual';
    }

    public function checkout(Subscription $subscription, Invoice $invoice): string
    {
        if (config('payments.manual.auto_confirm', true)) {
            $this->subscriptions->activate($subscription);

            return route('checkout.success', ['subscription' => $subscription->id]);
        }

        // Await manual admin confirmation.
        return route('checkout.pending', ['subscription' => $subscription->id]);
    }

    public function refund(Invoice $invoice): bool
    {
        // No money actually moved; the ledger is updated by SubscriptionService.
        return true;
    }

    public function cancelAtProvider(Subscription $subscription, bool $immediate): void
    {
        // No external recurring subscription to cancel; the local ledger is the
        // source of truth for the manual gateway.
    }
}
