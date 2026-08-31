<?php

namespace App\Services\Payments;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Services\Payments\Contracts\PaymentGateway;
use RuntimeException;

/**
 * In-app purchases made in the mobile app, relayed to us by Adapty.
 *
 * Unlike Stripe, this gateway never moves money: the App Store and Google Play
 * charge the customer, Adapty watches those stores, and the webhook
 * (AdaptyEventProcessor) is the only thing that writes the local subscription.
 * It exists so subscriptions with payment_provider='adapty' resolve to a
 * gateway at all — SubscriptionService::cancel() and refund() look one up by
 * provider, and without this they would silently fall back to the manual
 * gateway and report a store cancellation as if we had performed it.
 *
 * Every method below therefore refuses rather than pretends.
 */
class AdaptyGateway implements PaymentGateway
{
    public function key(): string
    {
        return 'adapty';
    }

    public function checkout(Subscription $subscription, Invoice $invoice): string
    {
        // Store rules: an iOS/Android subscription can only be sold through the
        // store's own sheet, inside the app.
        throw new RuntimeException('Adapty subscriptions are purchased in the mobile app, not on the web.');
    }

    public function refund(Invoice $invoice): bool
    {
        // Only Apple and Google can refund their own charge. Returning false
        // keeps the admin panel honest instead of marking a store invoice
        // refunded while the customer's money stays where it was.
        return false;
    }

    public function cancelAtProvider(Subscription $subscription, bool $immediate): void
    {
        // Auto-renew belongs to the store's subscription-management screen; we
        // learn about a cancellation from the webhook, we never cause one.
    }
}
