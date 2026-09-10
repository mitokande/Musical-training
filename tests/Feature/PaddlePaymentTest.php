<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Payments\Paddle\PaddleApi;
use App\Services\Payments\PaddleGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class PaddlePaymentTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'pdl_ntfset_test_secret';

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'payments.driver' => 'paddle',
            'services.paddle.webhook_secret' => self::SECRET,
            'services.paddle.prices.monthly' => 'pri_monthly',
            'services.paddle.prices.yearly' => 'pri_yearly',
        ]);
    }

    private function premiumPlan(string $role = 'user'): Plan
    {
        return Plan::create([
            'name' => ucfirst($role).' Premium', 'slug' => $role.'-premium', 'role' => $role,
            'type' => 'premium', 'price_monthly' => 6.90, 'price_yearly' => 40.00,
            'currency' => 'USD', 'is_active' => true,
        ]);
    }

    /** A pending Paddle subscription + its pending invoice, as purchase() would create. */
    private function pendingPaddleSubscription(User $user, Plan $plan, string $cycle = 'monthly'): Subscription
    {
        $sub = Subscription::create([
            'user_id' => $user->id, 'plan_id' => $plan->id, 'status' => 'pending',
            'billing_cycle' => $cycle, 'auto_renew' => true, 'starts_at' => now(),
            'amount' => 6.90, 'currency' => 'USD', 'payment_provider' => 'paddle',
        ]);

        Invoice::create([
            'user_id' => $user->id, 'subscription_id' => $sub->id,
            'invoice_number' => Invoice::generateNumber(), 'billing_cycle' => $cycle,
            'amount' => 6.90, 'tax_amount' => 0, 'total_amount' => 6.90, 'currency' => 'USD',
            'status' => 'pending', 'provider' => 'paddle',
        ]);

        return $sub;
    }

    /** POST a Paddle-signed event to the webhook, like Paddle's servers would. */
    private function postPaddleEvent(array $event, ?string $signature = null)
    {
        $payload = json_encode($event);
        $timestamp = time();
        $signature ??= 'ts='.$timestamp.';h1='.hash_hmac('sha256', $timestamp.':'.$payload, self::SECRET);

        return $this->call(
            'POST', route('webhooks.paddle'), [], [], [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_PADDLE_SIGNATURE' => $signature],
            $payload,
        );
    }

    /** A transaction.completed payload for the given local subscription. */
    private function completedTransaction(Subscription $sub, array $overrides = []): array
    {
        return array_replace_recursive([
            'event_id' => 'evt_'.uniqid(),
            'event_type' => 'transaction.completed',
            'data' => [
                'id' => 'txn_first',
                'status' => 'completed',
                'origin' => 'web',
                'customer_id' => 'ctm_123',
                'subscription_id' => 'sub_abc',
                'currency_code' => 'USD',
                'invoice_number' => '1001',
                'custom_data' => ['local_subscription_id' => (string) $sub->id],
                'billing_period' => [
                    'starts_at' => now()->toIso8601String(),
                    'ends_at' => now()->addMonth()->toIso8601String(),
                ],
                'details' => ['totals' => ['grand_total' => '690']],
            ],
        ], $overrides);
    }

    public function test_webhook_rejects_an_invalid_signature(): void
    {
        $response = $this->postPaddleEvent(
            ['event_id' => 'evt_1', 'event_type' => 'transaction.completed', 'data' => []],
            'ts='.time().';h1=deadbeef',
        );

        $response->assertStatus(400);
        $this->assertDatabaseCount('paddle_events', 0);
    }

    public function test_webhook_rejects_a_stale_timestamp(): void
    {
        $payload = json_encode(['event_id' => 'evt_1', 'event_type' => 'transaction.completed', 'data' => []]);
        $old = time() - 3600;
        $signature = 'ts='.$old.';h1='.hash_hmac('sha256', $old.':'.$payload, self::SECRET);

        $response = $this->call(
            'POST', route('webhooks.paddle'), [], [], [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_PADDLE_SIGNATURE' => $signature],
            $payload,
        );

        $response->assertStatus(400);
    }

    public function test_transaction_completed_activates_premium(): void
    {
        $user = User::factory()->create(['plan' => 'free']);
        $plan = $this->premiumPlan();
        $sub = $this->pendingPaddleSubscription($user, $plan);

        $this->postPaddleEvent($this->completedTransaction($sub))->assertOk();

        $sub->refresh();
        $user->refresh();

        $this->assertSame('active', $sub->status);
        $this->assertSame('sub_abc', $sub->external_id);
        $this->assertSame('premium', $user->plan);
        $this->assertSame('ctm_123', $user->paddle_customer_id);
    }

    public function test_transaction_completed_records_period_end_and_refundable_reference(): void
    {
        $user = User::factory()->create(['plan' => 'free']);
        $plan = $this->premiumPlan();
        $sub = $this->pendingPaddleSubscription($user, $plan);

        $periodEnd = now()->addDays(31)->startOfSecond();

        $this->postPaddleEvent($this->completedTransaction($sub, [
            'data' => ['billing_period' => ['ends_at' => $periodEnd->toIso8601String()]],
        ]))->assertOk();

        $sub->refresh();
        $this->assertSame($periodEnd->timestamp, $sub->ends_at->timestamp);

        // The transaction id is what a later refund is created against.
        $invoice = $sub->invoices()->first();
        $this->assertSame('paid', $invoice->status);
        $this->assertSame('txn_first', $invoice->provider_reference);
    }

    public function test_recurring_transaction_extends_period_and_adds_invoice(): void
    {
        $user = User::factory()->create(['plan' => 'free']);
        $plan = $this->premiumPlan();
        $sub = $this->pendingPaddleSubscription($user, $plan);

        $this->postPaddleEvent($this->completedTransaction($sub))->assertOk();
        $this->assertSame(1, $sub->invoices()->count());

        $renewalEnd = now()->addMonths(2)->startOfSecond();
        $this->postPaddleEvent($this->completedTransaction($sub, [
            'data' => [
                'id' => 'txn_renewal',
                'origin' => 'subscription_recurring',
                'invoice_number' => '1002',
                'billing_period' => ['ends_at' => $renewalEnd->toIso8601String()],
            ],
        ]))->assertOk();

        $sub->refresh();
        $this->assertSame('active', $sub->status);
        $this->assertSame($renewalEnd->timestamp, $sub->ends_at->timestamp);
        $this->assertSame(2, $sub->invoices()->count());

        // Amounts arrive in the currency's lowest denomination.
        $renewal = $sub->invoices()->where('provider_reference', 'txn_renewal')->first();
        $this->assertNotNull($renewal);
        $this->assertSame('6.90', (string) $renewal->amount);
    }

    public function test_payment_failed_marks_past_due_without_dropping_premium(): void
    {
        $user = User::factory()->create(['plan' => 'free']);
        $plan = $this->premiumPlan();
        $sub = $this->pendingPaddleSubscription($user, $plan);
        $this->postPaddleEvent($this->completedTransaction($sub))->assertOk();

        $this->postPaddleEvent([
            'event_id' => 'evt_failed',
            'event_type' => 'transaction.payment_failed',
            'data' => ['id' => 'txn_x', 'subscription_id' => 'sub_abc', 'customer_id' => 'ctm_123'],
        ])->assertOk();

        $sub->refresh();
        $user->refresh();

        $this->assertSame('past_due', $sub->status);
        // Paddle is still retrying — access is kept until the period actually lapses.
        $this->assertSame('premium', $user->plan);
    }

    public function test_scheduled_cancellation_turns_off_auto_renew(): void
    {
        $user = User::factory()->create(['plan' => 'free']);
        $plan = $this->premiumPlan();
        $sub = $this->pendingPaddleSubscription($user, $plan);
        $this->postPaddleEvent($this->completedTransaction($sub))->assertOk();

        $this->postPaddleEvent([
            'event_id' => 'evt_updated',
            'event_type' => 'subscription.updated',
            'data' => [
                'id' => 'sub_abc',
                'status' => 'active',
                'customer_id' => 'ctm_123',
                'scheduled_change' => ['action' => 'cancel', 'effective_at' => now()->addMonth()->toIso8601String()],
                'current_billing_period' => ['ends_at' => now()->addMonth()->toIso8601String()],
            ],
        ])->assertOk();

        $sub->refresh();
        $this->assertFalse($sub->auto_renew);
        $this->assertNotNull($sub->cancelled_at);
        // Still active: they keep Premium until the period they paid for ends.
        $this->assertSame('active', $sub->status);
    }

    public function test_subscription_canceled_downgrades_to_free(): void
    {
        $user = User::factory()->create(['plan' => 'free']);
        $plan = $this->premiumPlan();
        $sub = $this->pendingPaddleSubscription($user, $plan);
        $this->postPaddleEvent($this->completedTransaction($sub))->assertOk();

        $this->postPaddleEvent([
            'event_id' => 'evt_canceled',
            'event_type' => 'subscription.canceled',
            'data' => ['id' => 'sub_abc', 'status' => 'canceled', 'customer_id' => 'ctm_123'],
        ])->assertOk();

        $sub->refresh();
        $user->refresh();

        $this->assertSame('expired', $sub->status);
        $this->assertSame('free', $user->plan);
    }

    public function test_duplicate_event_is_processed_only_once(): void
    {
        $user = User::factory()->create(['plan' => 'free']);
        $plan = $this->premiumPlan();
        $sub = $this->pendingPaddleSubscription($user, $plan);

        $event = $this->completedTransaction($sub, [
            'data' => ['id' => 'txn_renewal', 'origin' => 'subscription_recurring'],
        ]);

        $this->postPaddleEvent($event)->assertOk();
        $this->postPaddleEvent($event)->assertOk()->assertJson(['status' => 'duplicate ignored']);

        $this->assertDatabaseCount('paddle_events', 1);
        $this->assertSame(1, $sub->invoices()->count());
    }

    public function test_rejected_refund_puts_the_invoice_back(): void
    {
        $user = User::factory()->create(['plan' => 'free']);
        $plan = $this->premiumPlan();
        $sub = $this->pendingPaddleSubscription($user, $plan);
        $this->postPaddleEvent($this->completedTransaction($sub))->assertOk();

        $invoice = $sub->invoices()->first();
        $invoice->update(['status' => 'refunded', 'refunded_at' => now()]);

        $this->postPaddleEvent([
            'event_id' => 'evt_adj',
            'event_type' => 'adjustment.updated',
            'data' => [
                'id' => 'adj_1',
                'action' => 'refund',
                'status' => 'rejected',
                'transaction_id' => 'txn_first',
            ],
        ])->assertOk();

        $invoice->refresh();
        $this->assertSame('paid', $invoice->status);
        $this->assertNull($invoice->refunded_at);
    }

    public function test_checkout_opens_a_transaction_on_the_role_specific_price(): void
    {
        config([
            'services.paddle.prices.teacher.yearly' => 'pri_teacher_yearly',
        ]);

        $user = User::factory()->create(['role' => 'teacher', 'plan' => 'free']);
        $plan = $this->premiumPlan('teacher');
        $sub = $this->pendingPaddleSubscription($user, $plan, 'yearly');
        $invoice = $sub->invoices()->first();

        $api = Mockery::mock(PaddleApi::class);
        $api->shouldReceive('createCustomer')->once()->andReturn('ctm_new');
        $api->shouldReceive('createTransaction')
            ->once()
            ->withArgs(function (string $priceId, string $customerId, array $customData) use ($sub) {
                return $priceId === 'pri_teacher_yearly'
                    && $customerId === 'ctm_new'
                    && $customData['local_subscription_id'] === (string) $sub->id;
            })
            ->andReturn(['id' => 'txn_new', 'checkout_url' => 'https://harmoniva.app/pay?_ptxn=txn_new']);

        $url = (new PaddleGateway($api))->checkout($sub, $invoice);

        $this->assertSame('https://harmoniva.app/pay?_ptxn=txn_new', $url);
        $this->assertSame('ctm_new', $user->fresh()->paddle_customer_id);
    }

    public function test_checkout_fails_loudly_when_no_default_payment_link_is_set(): void
    {
        $user = User::factory()->create(['plan' => 'free']);
        $plan = $this->premiumPlan();
        $sub = $this->pendingPaddleSubscription($user, $plan);
        $invoice = $sub->invoices()->first();

        $api = Mockery::mock(PaddleApi::class);
        $api->shouldReceive('createCustomer')->andReturn('ctm_new');
        $api->shouldReceive('createTransaction')->andReturn(['id' => 'txn_new', 'checkout_url' => null]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('default payment link');

        (new PaddleGateway($api))->checkout($sub, $invoice);
    }

    public function test_a_zero_value_transaction_starts_a_trial_without_booking_revenue(): void
    {
        $user = User::factory()->create(['plan' => 'free']);
        $plan = $this->premiumPlan();
        $sub = $this->pendingPaddleSubscription($user, $plan);

        $trialEnd = now()->addDays(15)->startOfSecond();

        // Paddle raises a completed transaction worth nothing when the price
        // carries a trial period.
        $this->postPaddleEvent($this->completedTransaction($sub, [
            'data' => [
                'billing_period' => ['ends_at' => $trialEnd->toIso8601String()],
                'details' => ['totals' => ['grand_total' => '0']],
            ],
        ]))->assertOk();

        $sub->refresh();
        $user->refresh();

        $this->assertSame('trialing', $sub->status);
        $this->assertSame($trialEnd->timestamp, $sub->ends_at->timestamp);
        $this->assertSame('premium', $user->plan);
        // A null cycle is what marks a trial rather than a paid plan.
        $this->assertNull($user->plan_cycle);

        // Nothing was charged, so nothing may appear in the billing history.
        $invoice = $sub->invoices()->first();
        $this->assertSame('pending', $invoice->status);
        $this->assertNull($invoice->paid_at);
        $this->assertSame(0, Invoice::where('status', 'paid')->count());
    }

    public function test_subscription_trialing_event_grants_premium(): void
    {
        $user = User::factory()->create(['plan' => 'free']);
        $plan = $this->premiumPlan();
        $sub = $this->pendingPaddleSubscription($user, $plan);

        $trialEnd = now()->addDays(15)->startOfSecond();

        $this->postPaddleEvent([
            'event_id' => 'evt_trialing',
            'event_type' => 'subscription.trialing',
            'data' => [
                'id' => 'sub_abc',
                'status' => 'trialing',
                'customer_id' => 'ctm_123',
                'custom_data' => ['local_subscription_id' => (string) $sub->id],
                'current_billing_period' => ['ends_at' => $trialEnd->toIso8601String()],
            ],
        ])->assertOk();

        $sub->refresh();
        $this->assertSame('trialing', $sub->status);
        $this->assertSame('sub_abc', $sub->external_id);
        $this->assertSame('premium', $user->fresh()->plan);
        $this->assertSame('pending', $sub->invoices()->first()->status);
    }

    public function test_the_first_real_charge_converts_the_trial_and_pays_the_waiting_invoice(): void
    {
        $user = User::factory()->create(['plan' => 'free']);
        $plan = $this->premiumPlan();
        $sub = $this->pendingPaddleSubscription($user, $plan);

        // Trial starts…
        $this->postPaddleEvent($this->completedTransaction($sub, [
            'data' => ['details' => ['totals' => ['grand_total' => '0']]],
        ]))->assertOk();

        // …then the trial ends and Paddle takes the first real payment.
        $paidUntil = now()->addDays(45)->startOfSecond();
        $this->postPaddleEvent($this->completedTransaction($sub, [
            'event_id' => 'evt_first_charge',
            'data' => [
                'id' => 'txn_first_charge',
                'billing_period' => ['ends_at' => $paidUntil->toIso8601String()],
                'details' => ['totals' => ['grand_total' => '690']],
            ],
        ]))->assertOk();

        $sub->refresh();
        $user->refresh();

        $this->assertSame('active', $sub->status);
        $this->assertSame($paidUntil->timestamp, $sub->ends_at->timestamp);
        $this->assertSame('monthly', $user->plan_cycle);

        // The invoice that was left pending through the trial is the one paid —
        // no second row, so the history shows one charge, not two.
        $this->assertSame(1, $sub->invoices()->count());
        $invoice = $sub->invoices()->first();
        $this->assertSame('paid', $invoice->status);
        $this->assertSame('txn_first_charge', $invoice->provider_reference);
    }

    public function test_pay_page_is_reachable_without_signing_in(): void
    {
        // Paddle mails this URL to customers whose card needs updating; putting
        // it behind auth would turn a fixable payment failure into a churn.
        $this->get('/pay?_ptxn=txn_abc')->assertOk();
        $this->get('/pay')->assertOk();
    }
}
