<?php

namespace App\Services\Payments;

use App\Services\Payments\Contracts\PaymentGateway;
use InvalidArgumentException;

/**
 * Resolves the active PaymentGateway from config('payments.driver'). Gateways are
 * built lazily through the container, so adding a real provider is a one-line
 * registration here plus its implementation class.
 */
class PaymentManager
{
    /** driver key => gateway class */
    private array $gateways = [
        'manual' => ManualGateway::class,
        'stripe' => StripeGateway::class,
        // Never the active driver — a store subscription cannot be started from
        // the web. Registered so subscriptions bought in the mobile app resolve
        // to a gateway that refuses on their behalf instead of falling through
        // to the manual one, which would report a refund we never made.
        'adapty' => AdaptyGateway::class,
        // 'paddle' => PaddleGateway::class,
        // 'iyzico' => IyzicoGateway::class,
    ];

    public function driver(?string $name = null): PaymentGateway
    {
        $name ??= config('payments.driver', 'manual');

        if (! isset($this->gateways[$name])) {
            throw new InvalidArgumentException("Unsupported payment driver [{$name}].");
        }

        return app($this->gateways[$name]);
    }

    /** @return string[] keys of registered gateways */
    public function available(): array
    {
        return array_keys($this->gateways);
    }
}
