<?php

namespace App\Services\Payments\Paddle;

use Paddle\SDK\Client;
use Paddle\SDK\Entities\Shared\Action;
use Paddle\SDK\Entities\Shared\CustomData;
use Paddle\SDK\Entities\Subscription\SubscriptionEffectiveFrom;
use Paddle\SDK\Resources\Adjustments\Operations\CreateAdjustment;
use Paddle\SDK\Resources\Customers\Operations\CreateCustomer;
use Paddle\SDK\Resources\Subscriptions\Operations\CancelSubscription;
use Paddle\SDK\Resources\Transactions\Operations\Create\TransactionCreateItem;
use Paddle\SDK\Resources\Transactions\Operations\CreateTransaction;
use Paddle\SDK\Undefined;

/**
 * The one place the Paddle SDK is touched.
 *
 * PaddleGateway talks to this, never to Paddle\SDK\Client directly, for two
 * reasons: the SDK's entities are readonly value objects that cannot be mocked,
 * and keeping the vendor surface to four calls means an SDK major upgrade is a
 * change to this file rather than to the gateway and its tests.
 *
 * Every method returns plain PHP — ids, arrays, strings — so nothing downstream
 * has to know an SDK type.
 */
class PaddleApi
{
    public function __construct(private Client $client) {}

    /**
     * Create a Paddle Customer and return its ctm_… id.
     *
     * @param  array<string,string>  $customData
     */
    public function createCustomer(string $email, ?string $name, array $customData = []): string
    {
        $customer = $this->client->customers->create(new CreateCustomer(
            email: $email,
            name: $name ?: new Undefined,
            customData: $customData ? new CustomData($customData) : new Undefined,
        ));

        return $customer->id;
    }

    /**
     * Open a transaction for one recurring price and return its id plus the
     * checkout URL to send the buyer to.
     *
     * The URL is the account's default payment link with `?_ptxn=<id>` appended
     * — our own /pay page, which loads Paddle.js and opens the checkout for the
     * transaction. Paddle returns it as null when no default payment link is
     * configured, which is a misconfiguration rather than a runtime condition,
     * so the caller is expected to treat a null as fatal.
     *
     * @param  array<string,string>  $customData
     * @return array{id: string, checkout_url: string|null}
     */
    public function createTransaction(string $priceId, string $customerId, array $customData = []): array
    {
        $transaction = $this->client->transactions->create(new CreateTransaction(
            items: [new TransactionCreateItem($priceId, 1)],
            customerId: $customerId,
            customData: $customData ? new CustomData($customData) : new Undefined,
        ));

        return [
            'id' => $transaction->id,
            'checkout_url' => $transaction->checkout?->url,
        ];
    }

    /**
     * Stop Paddle billing a subscription. $immediate revokes now; otherwise the
     * subscription runs to the end of the period the customer already paid for.
     */
    public function cancelSubscription(string $subscriptionId, bool $immediate): void
    {
        $this->client->subscriptions->cancel($subscriptionId, new CancelSubscription(
            effectiveFrom: $immediate
                ? SubscriptionEffectiveFrom::Immediately()
                : SubscriptionEffectiveFrom::NextBillingPeriod(),
        ));
    }

    /**
     * Ask Paddle to refund a completed transaction in full.
     *
     * Returns the adjustment's status, which is the whole point of the call:
     * unlike a card processor, Paddle does not simply do it. Most live refunds
     * are created as 'pending_approval' and only become 'approved' or
     * 'rejected' after Paddle reviews them — though refunds under 400 USD on a
     * verified account are approved automatically, which covers every plan we
     * sell.
     */
    public function refundTransaction(string $transactionId, string $reason): string
    {
        $adjustment = $this->client->adjustments->create(
            CreateAdjustment::full(Action::Refund(), $reason, $transactionId),
        );

        return (string) $adjustment->status->getValue();
    }
}
