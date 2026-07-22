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
