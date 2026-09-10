<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Paddle\SDK\Client;
use Paddle\SDK\Entities\Shared\CurrencyCode;
use Paddle\SDK\Entities\Shared\Interval;
use Paddle\SDK\Entities\Shared\Money;
use Paddle\SDK\Entities\Shared\TimePeriod;
use Paddle\SDK\Resources\Prices\Operations\CreatePrice;

/**
 * Create the recurring Paddle prices the checkout needs, one per role/cycle.
 *
 * The plans table is the source of truth for the figures; this only mirrors
 * them into the Paddle catalog and prints the price ids to paste into .env.
 *
 * Idempotent: a price whose internal description already exists is reported and
 * skipped, so re-running after adding one tier never duplicates the others.
 * Prices are financial records in Paddle — they can be archived but not
 * deleted — so a careless second run is expensive to tidy up.
 */
class SeedPaddlePrices extends Command
{
    protected $signature = 'paddle:seed-prices
                            {--product= : Paddle product id (pro_…) to attach the prices to}
                            {--dry-run : Show what would be created without touching Paddle}';

    protected $description = 'Create the Premium recurring prices in the Paddle catalog';

    /** role => [cycle => [amount in minor units, customer-facing name, .env key]] */
    private const SPECS = [
        'teacher' => [
            'monthly' => ['1690', 'Teacher Premium — Monthly', 'PADDLE_PRICE_TEACHER_MONTHLY'],
            'yearly' => ['8000', 'Teacher Premium — Yearly', 'PADDLE_PRICE_TEACHER_YEARLY'],
        ],
        'school' => [
            'monthly' => ['2990', 'School Premium — Monthly', 'PADDLE_PRICE_SCHOOL_MONTHLY'],
            'yearly' => ['16900', 'School Premium — Yearly', 'PADDLE_PRICE_SCHOOL_YEARLY'],
        ],
    ];

    public function handle(Client $paddle): int
    {
        $productId = $this->option('product');
        if (! $productId) {
            $this->error('Pass --product=pro_… (Paddle > Catalog > Products).');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');

        // Read the catalog first so a re-run cannot create a second copy.
        $existing = [];
        foreach ($paddle->prices->list() as $price) {
            $existing[$price->description] = $price->id;
        }

        $rows = [];

        foreach (self::SPECS as $role => $cycles) {
            foreach ($cycles as $cycle => [$amount, $name, $envKey]) {
                $description = "{$role}-{$cycle}";

                if (isset($existing[$description])) {
                    $rows[] = [$envKey, $existing[$description], 'already existed'];

                    continue;
                }

                if ($dryRun) {
                    $rows[] = [$envKey, '(would create '.$description.')', $amount.' USD'];

                    continue;
                }

                $price = $paddle->prices->create(new CreatePrice(
                    description: $description,
                    productId: $productId,
                    unitPrice: new Money($amount, CurrencyCode::USD()),
                    name: $name,
                    billingCycle: new TimePeriod(
                        $cycle === 'yearly' ? Interval::Year() : Interval::Month(),
                        1,
                    ),
                ));

                $rows[] = [$envKey, $price->id, 'created'];
            }
        }

        $this->table(['.env key', 'Paddle price id', 'status'], $rows);

        if (! $dryRun) {
            $this->info('Paste these into .env, then run: php artisan config:clear');
        }

        return self::SUCCESS;
    }
}
