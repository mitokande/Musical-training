<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Stripe\Checkout\Session;
use Stripe\StripeClient;
use Tests\TestCase;

class StripePaymentTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'whsec_test_secret';

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'payments.driver' => 'stripe',
            'services.stripe.webhook_secret' => self::SECRET,
            'services.stripe.prices.monthly' => 'price_monthly',
            'services.stripe.prices.yearly' => 'price_yearly',
        ]);
    }

    private function premiumPlan(): Plan
    {
        return Plan::create([
            'name' => 'User Premium', 'slug' => 'user-premium', 'role' => 'user',
            'type' => 'premium', 'price_monthly' => 6.90, 'price_yearly' => 40.00,
            'currency' => 'USD', 'is_active' => true,
        ]);
    }

    /** A pending Stripe subscription + its pending invoice, as purchase() would create. */
    private function pendingStripeSubscription(User $user, Plan $plan, string $cycle = 'monthly'): Subscription
    {
        $sub = Subscription::create([
            'user_id' => $user->id, 'plan_id' => $plan->id, 'status' => 'pending',
            'billing_cycle' => $cycle, 'auto_renew' => true, 'starts_at' => now(),
            'amount' => 6.90, 'currency' => 'USD', 'payment_provider' => 'stripe',
        ]);

        Invoice::create([
            'user_id' => $user->id, 'subscription_id' => $sub->id,
            'invoice_number' => Invoice::generateNumber(), 'billing_cycle' => $cycle,
            'amount' => 6.90, 'tax_amount' => 0, 'total_amount' => 6.90, 'currency' => 'USD',
            'status' => 'pending', 'provider' => 'stripe',
        ]);

        return $sub;
    }

    /** POST a Stripe-signed event to the webhook, like Stripe's servers would. */
    private function postStripeEvent(array $event, ?string $signature = null)
    {
        $payload = json_encode($event);
        $timestamp = time();
        $signature ??= 't='.$timestamp.',v1='.hash_hmac('sha256', $timestamp.'.'.$payload, self::SECRET);

        return $this->call(
            'POST', route('webhooks.stripe'), [], [], [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_STRIPE_SIGNATURE' => $signature],
            $payload,
        );
    }

    public function test_webhook_rejects_an_invalid_signature(): void
    {
        $response = $this->postStripeEvent(
            ['id' => 'evt_1', 'type' => 'checkout.session.completed', 'data' => ['object' => []]],
            signature: 't='.time().',v1=deadbeef',
        );

        $response->assertStatus(400);
    }

    public function test_checkout_session_completed_activates_premium(): void
    {
        $user = User::factory()->create(['role' => 'user', 'plan' => 'free']);
        $sub = $this->pendingStripeSubscription($user, $this->premiumPlan());

        $this->postStripeEvent([
            'id' => 'evt_checkout_1',
            'type' => 'checkout.session.completed',
            'data' => ['object' => [
                'id' => 'cs_1', 'object' => 'checkout.session', 'mode' => 'subscription',
                'payment_status' => 'paid', 'client_reference_id' => (string) $sub->id,
                'subscription' => 'sub_123', 'customer' => 'cus_123',
                'metadata' => ['subscription_id' => (string) $sub->id],
            ]],
        ])->assertOk();

        $sub->refresh();
        $user->refresh();
        $this->assertSame('active', $sub->status);
        $this->assertSame('sub_123', $sub->external_id);
        $this->assertSame('premium', $user->plan);
        $this->assertSame('cus_123', $user->stripe_customer_id);
        $this->assertSame('paid', $sub->invoices()->first()->status);
    }

    public function test_first_invoice_paid_records_precise_period_and_payment_reference(): void
    {
        $user = User::factory()->create(['role' => 'user', 'plan' => 'free']);
        $sub = $this->pendingStripeSubscription($user, $this->premiumPlan());
        $periodEnd = now()->addMonth()->startOfMinute();

        $this->postStripeEvent([
            'id' => 'evt_inv_1',
            'type' => 'invoice.paid',
            'data' => ['object' => [
                'id' => 'in_1', 'object' => 'invoice', 'billing_reason' => 'subscription_create',
                'subscription' => 'sub_123', 'customer' => 'cus_123',
                'subscription_details' => ['metadata' => ['subscription_id' => (string) $sub->id]],
                'payment_intent' => 'pi_abc', 'amount_paid' => 690, 'currency' => 'usd',
                'lines' => ['data' => [['period' => ['end' => $periodEnd->timestamp]]]],
            ]],
        ])->assertOk();

        $sub->refresh();
        $user->refresh();
        $this->assertSame('active', $sub->status);
        $this->assertSame($periodEnd->timestamp, $sub->ends_at->timestamp);
        $this->assertSame('premium', $user->plan);
        $this->assertSame('pi_abc', $sub->invoices()->where('status', 'paid')->first()->provider_reference);
        // No duplicate invoice for the first payment.
        $this->assertSame(1, $sub->invoices()->count());
    }

    public function test_checkout_then_invoice_records_refundable_payment_reference(): void
    {
        // Real event order: checkout.session.completed activates first (no
        // payment_intent on the session), then invoice.paid fills the reference
        // used by refund(). The checkout session id must never occupy that field.
        $user = User::factory()->create(['role' => 'user', 'plan' => 'free']);
        $sub = $this->pendingStripeSubscription($user, $this->premiumPlan());
        $periodEnd = now()->addMonth()->startOfMinute();

        $this->postStripeEvent([
            'id' => 'evt_order_checkout',
            'type' => 'checkout.session.completed',
            'data' => ['object' => [
                'id' => 'cs_order', 'object' => 'checkout.session', 'mode' => 'subscription',
                'payment_status' => 'paid', 'client_reference_id' => (string) $sub->id,
                'subscription' => 'sub_order', 'customer' => 'cus_order',
            ]],
        ])->assertOk();

        $this->postStripeEvent([
            'id' => 'evt_order_invoice',
            'type' => 'invoice.paid',
            'data' => ['object' => [
                'id' => 'in_order', 'object' => 'invoice', 'billing_reason' => 'subscription_create',
                'subscription' => 'sub_order', 'customer' => 'cus_order',
                'payment_intent' => 'pi_order', 'amount_paid' => 690, 'currency' => 'usd',
                'lines' => ['data' => [['period' => ['end' => $periodEnd->timestamp]]]],
            ]],
        ])->assertOk();

        $invoice = $sub->invoices()->where('status', 'paid')->first();
        $this->assertSame('pi_order', $invoice->provider_reference);
        $this->assertSame(1, $sub->invoices()->count());
    }

    public function test_recurring_invoice_paid_extends_period_and_adds_invoice(): void
    {
        $user = User::factory()->create(['role' => 'user', 'plan' => 'premium', 'plan_expires_at' => now()->addDay()]);
        $sub = $this->pendingStripeSubscription($user, $this->premiumPlan());
        $sub->update(['status' => 'active', 'external_id' => 'sub_123', 'ends_at' => now()->addDay()]);
        $sub->invoices()->update(['status' => 'paid']);

        $newEnd = now()->addMonth()->startOfMinute();

        $this->postStripeEvent([
            'id' => 'evt_inv_renew',
            'type' => 'invoice.paid',
            'data' => ['object' => [
                'id' => 'in_2', 'object' => 'invoice', 'billing_reason' => 'subscription_cycle',
                'subscription' => 'sub_123', 'customer' => 'cus_123', 'number' => 'STRIPE-2',
                'payment_intent' => 'pi_renew', 'amount_paid' => 690, 'currency' => 'usd',
                'lines' => ['data' => [['period' => ['end' => $newEnd->timestamp]]]],
            ]],
        ])->assertOk();

        $sub->refresh();
        $user->refresh();
        $this->assertSame($newEnd->timestamp, $sub->ends_at->timestamp);
        $this->assertSame($newEnd->timestamp, $user->plan_expires_at->timestamp);
        // A second, renewal invoice is recorded for billing history.
        $this->assertSame(2, $sub->invoices()->count());
        $this->assertSame('pi_renew', $sub->invoices()->latest('id')->first()->provider_reference);
    }

    public function test_subscription_deleted_downgrades_to_free(): void
    {
        $user = User::factory()->create(['role' => 'user', 'plan' => 'premium', 'plan_expires_at' => now()->addMonth()]);
        $sub = $this->pendingStripeSubscription($user, $this->premiumPlan());
        $sub->update(['status' => 'active', 'external_id' => 'sub_123', 'ends_at' => now()->addMonth()]);

        $this->postStripeEvent([
            'id' => 'evt_sub_del',
            'type' => 'customer.subscription.deleted',
            'data' => ['object' => [
                'id' => 'sub_123', 'object' => 'subscription', 'status' => 'canceled',
                'customer' => 'cus_123',
            ]],
        ])->assertOk();

        $sub->refresh();
        $user->refresh();
        $this->assertSame('expired', $sub->status);
        $this->assertSame('free', $user->plan);
    }

    public function test_duplicate_event_is_processed_only_once(): void
    {
        $user = User::factory()->create(['role' => 'user', 'plan' => 'premium', 'plan_expires_at' => now()->addDay()]);
        $sub = $this->pendingStripeSubscription($user, $this->premiumPlan());
        $sub->update(['status' => 'active', 'external_id' => 'sub_123', 'ends_at' => now()->addDay()]);
        $sub->invoices()->update(['status' => 'paid']);

        $event = [
            'id' => 'evt_dupe',
            'type' => 'invoice.paid',
            'data' => ['object' => [
                'id' => 'in_dup', 'object' => 'invoice', 'billing_reason' => 'subscription_cycle',
                'subscription' => 'sub_123', 'customer' => 'cus_123',
                'payment_intent' => 'pi_dup', 'amount_paid' => 690, 'currency' => 'usd',
                'lines' => ['data' => [['period' => ['end' => now()->addMonth()->timestamp]]]],
            ]],
        ];

        $this->postStripeEvent($event)->assertOk();
        $this->postStripeEvent($event)->assertOk()->assertJson(['status' => 'duplicate ignored']);

        // Only one renewal invoice despite two deliveries.
        $this->assertSame(2, $sub->invoices()->count());
    }

    public function test_success_return_page_activates_without_waiting_for_webhook(): void
    {
        $user = User::factory()->create(['role' => 'user', 'plan' => 'free']);
        $sub = $this->pendingStripeSubscription($user, $this->premiumPlan());

        // Mock the Checkout Session retrieve the return page performs.
        $session = Session::constructFrom([
            'id' => 'cs_return', 'mode' => 'subscription', 'payment_status' => 'paid',
            'client_reference_id' => (string) $sub->id, 'subscription' => 'sub_ret', 'customer' => 'cus_ret',
        ]);
        $sessions = Mockery::mock();
        $sessions->shouldReceive('retrieve')->with('cs_return', [])->andReturn($session);
        $checkout = new \stdClass;
        $checkout->sessions = $sessions;
        $stripe = Mockery::mock(StripeClient::class);
        $stripe->shouldReceive('getService')->with('checkout')->andReturn($checkout);
        $this->app->instance(StripeClient::class, $stripe);

        $this->actingAs($user)
            ->get(route('checkout.success', $sub).'?session_id=cs_return')
            ->assertOk();

        $sub->refresh();
        $user->refresh();
        $this->assertSame('active', $sub->status);
        $this->assertSame('sub_ret', $sub->external_id);
        $this->assertSame('premium', $user->plan);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
