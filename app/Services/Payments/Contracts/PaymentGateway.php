<?php

namespace App\Services\Payments\Contracts;

use App\Models\Invoice;
use App\Models\Subscription;

/**
 * A payment gateway drives the money movement for a subscription. The rest of
 * the app (checkout controller, SubscriptionService, admin) is provider-agnostic
 * and talks only to this contract, so a real provider can be dropped in later
 * without touching the checkout UX or the entitlement logic.
 */
interface PaymentGateway
{
    /** Stable identifier stored on subscriptions.payment_provider / invoices.provider. */
    public function key(): string;

    /**
     * Begin payment for a freshly created pending subscription + invoice.
     * Returns the absolute URL the user should be redirected to next — a
     * provider-hosted checkout page for real gateways, or an internal
     * success/pending URL for the manual gateway.
     */
    public function checkout(Subscription $subscription, Invoice $invoice): string;

    /** Attempt to refund a paid invoice at the provider. Returns success. */
    public function refund(Invoice $invoice): bool;

    /**
     * Propagate a cancellation to the provider so it stops billing. $immediate
     * cancels right away; otherwise the provider keeps the subscription until the
     * current period ends (cancel-at-period-end). A no-op for gateways that hold
     * no external recurring subscription (e.g. the manual gateway).
     */
    public function cancelAtProvider(Subscription $subscription, bool $immediate): void;
}
