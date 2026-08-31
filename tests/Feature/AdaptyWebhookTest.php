<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdaptyWebhookTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'adapty_test_secret';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.adapty.webhook_header' => 'Authorization',
            'services.adapty.webhook_secret' => self::SECRET,
            'services.adapty.access_level' => 'premium',
            'services.adapty.accept_sandbox' => true,
        ]);

        $this->premiumPlan();
    }

    private function premiumPlan(): Plan
    {
        return Plan::firstOrCreate(
            ['slug' => 'user-premium'],
            [
                'name' => 'User Premium', 'role' => 'user', 'type' => 'premium',
                'price_monthly' => 6.90, 'price_yearly' => 40.00,
                'currency' => 'USD', 'is_active' => true,
            ],
        );
    }

    /** POST an event to the webhook the way Adapty's servers would. */
    private function postAdaptyEvent(array $event, ?string $secret = self::SECRET)
    {
        $headers = ['CONTENT_TYPE' => 'application/json'];
        if ($secret !== null) {
            $headers['HTTP_AUTHORIZATION'] = 'Bearer '.$secret;
        }

        return $this->call(
            'POST', route('webhooks.adapty'), [], [], [], $headers, json_encode($event),
        );
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    private function event(string $type, array $properties = [], array $overrides = []): array
    {
        return array_merge([
            'event_id' => 'evt_'.md5($type.json_encode($properties)),
            'event_type' => $type,
            'profile_id' => 'prof_1',
            'customer_user_id' => null,
            'event_properties' => array_merge([
                'access_level_id' => 'premium',
                'vendor_product_id' => 'com.harmoniva.premium.monthly',
                'store' => 'app_store',
                'environment' => 'Production',
                'transaction_id' => 'txn_1',
                'original_transaction_id' => 'orig_1',
                'purchase_date' => now()->toIso8601String(),
                'expires_at' => now()->addMonth()->toIso8601String(),
                'price_local' => 6.90,
                'currency' => 'USD',
                'is_trial' => false,
            ], $properties),
        ], $overrides);
    }

    public function test_the_webhook_refuses_a_call_without_the_shared_secret(): void
    {
        $this->postAdaptyEvent($this->event('subscription_initial_purchase'), secret: null)
            ->assertStatus(401);

        $this->postAdaptyEvent($this->event('subscription_initial_purchase'), secret: 'wrong')
            ->assertStatus(401);

        $this->assertDatabaseCount('adapty_events', 0);
    }

    public function test_the_webhook_refuses_everything_when_no_secret_is_configured(): void
    {
        config(['services.adapty.webhook_secret' => null]);

        $this->postAdaptyEvent($this->event('subscription_initial_purchase'))
            ->assertStatus(500);
    }

    public function test_an_initial_purchase_grants_premium_and_records_the_charge(): void
    {
        $user = User::factory()->create(['role' => 'user', 'plan' => 'free']);
        $expires = now()->addMonth()->startOfSecond();

        $this->postAdaptyEvent($this->event(
            'subscription_initial_purchase',
            ['expires_at' => $expires->toIso8601String()],
            ['customer_user_id' => (string) $user->id],
        ))->assertOk();

        $user->refresh();
        $this->assertSame('premium', $user->plan);
        $this->assertSame('monthly', $user->plan_cycle);
        $this->assertSame($expires->timestamp, $user->plan_expires_at->timestamp);
        $this->assertSame('prof_1', $user->adapty_profile_id);

        $subscription = $user->subscriptions()->firstOrFail();
        $this->assertSame('active', $subscription->status);
        $this->assertSame('adapty', $subscription->payment_provider);
        $this->assertSame('orig_1', $subscription->external_id);
        $this->assertSame($expires->timestamp, $subscription->ends_at->timestamp);

        $invoice = $subscription->invoices()->firstOrFail();
        $this->assertSame('paid', $invoice->status);
        $this->assertSame('adapty', $invoice->provider);
        $this->assertSame('txn_1', $invoice->provider_reference);
        $this->assertSame('6.90', (string) $invoice->total_amount);
    }

    public function test_a_redelivered_event_is_acknowledged_but_not_applied_twice(): void
    {
        $user = User::factory()->create(['role' => 'user', 'plan' => 'free']);
        $event = $this->event(
            'subscription_initial_purchase',
            [],
            ['customer_user_id' => (string) $user->id],
        );

        $this->postAdaptyEvent($event)->assertOk();
        $this->postAdaptyEvent($event)->assertOk()->assertJsonPath('status', 'duplicate ignored');

        $this->assertSame(1, $user->subscriptions()->count());
        $this->assertSame(1, Invoice::where('user_id', $user->id)->count());
    }

    public function test_a_renewal_extends_the_period_and_adds_an_invoice(): void
    {
        $user = User::factory()->create(['role' => 'user', 'plan' => 'free']);

        $this->postAdaptyEvent($this->event(
            'subscription_initial_purchase',
            [],
            ['customer_user_id' => (string) $user->id],
        ))->assertOk();

        $renewedTo = now()->addMonths(2)->startOfSecond();

        $this->postAdaptyEvent($this->event(
            'subscription_renewed',
            [
                'transaction_id' => 'txn_2',
                'expires_at' => $renewedTo->toIso8601String(),
            ],
            ['customer_user_id' => (string) $user->id],
        ))->assertOk();

        $user->refresh();
        $subscription = $user->subscriptions()->firstOrFail();

        $this->assertSame('premium', $user->plan);
        $this->assertSame($renewedTo->timestamp, $subscription->ends_at->timestamp);
        $this->assertSame($renewedTo->timestamp, $user->plan_expires_at->timestamp);
        // One row for the first charge, one for the renewal.
        $this->assertSame(2, $subscription->invoices()->count());
        $this->assertSame('txn_2', $subscription->invoices()->latest('id')->first()->provider_reference);
    }

    public function test_an_expiry_drops_the_user_back_to_free(): void
    {
        $user = User::factory()->create(['role' => 'user', 'plan' => 'free']);

        $this->postAdaptyEvent($this->event(
            'subscription_initial_purchase',
            [],
            ['customer_user_id' => (string) $user->id],
        ))->assertOk();

        $this->postAdaptyEvent($this->event(
            'subscription_expired',
            ['transaction_id' => 'txn_end', 'expires_at' => now()->subMinute()->toIso8601String()],
            ['customer_user_id' => (string) $user->id],
        ))->assertOk();

        $user->refresh();
        $this->assertSame('free', $user->plan);
        $this->assertNull($user->plan_expires_at);
        $this->assertSame('expired', $user->subscriptions()->firstOrFail()->status);
    }

    public function test_turning_off_auto_renew_keeps_access_until_the_period_ends(): void
    {
        $user = User::factory()->create(['role' => 'user', 'plan' => 'free']);

        $this->postAdaptyEvent($this->event(
            'subscription_initial_purchase',
            [],
            ['customer_user_id' => (string) $user->id],
        ))->assertOk();

        $this->postAdaptyEvent($this->event(
            'subscription_renewal_cancelled',
            ['transaction_id' => 'txn_cancel'],
            ['customer_user_id' => (string) $user->id],
        ))->assertOk();

        $user->refresh();
        $subscription = $user->subscriptions()->firstOrFail();

        $this->assertSame('premium', $user->plan, 'they paid for this period');
        $this->assertFalse($subscription->auto_renew);
        $this->assertNotNull($subscription->cancelled_at);
        $this->assertSame('active', $subscription->status);
    }

    public function test_a_refund_revokes_access_and_marks_the_invoice_refunded(): void
    {
        $user = User::factory()->create(['role' => 'user', 'plan' => 'free']);

        $this->postAdaptyEvent($this->event(
            'subscription_initial_purchase',
            [],
            ['customer_user_id' => (string) $user->id],
        ))->assertOk();

        $this->postAdaptyEvent($this->event(
            'subscription_refunded',
            ['transaction_id' => 'txn_1'],
            ['customer_user_id' => (string) $user->id],
        ))->assertOk();

        $user->refresh();
        $subscription = $user->subscriptions()->firstOrFail();

        $this->assertSame('free', $user->plan);
        $this->assertSame('cancelled', $subscription->status);
        $this->assertSame('refunded', $subscription->invoices()->firstOrFail()->status);
    }

    public function test_a_trial_is_mirrored_onto_the_user_and_cleared_when_it_converts(): void
    {
        $user = User::factory()->create(['role' => 'user', 'plan' => 'free']);
        $trialEnds = now()->addWeek()->startOfSecond();

        $this->postAdaptyEvent($this->event(
            'trial_started',
            ['is_trial' => true, 'price_local' => 0, 'expires_at' => $trialEnds->toIso8601String()],
            ['customer_user_id' => (string) $user->id],
        ))->assertOk();

        $user->refresh();
        $this->assertSame('premium', $user->plan);
        $this->assertTrue($user->onTrial());
        $this->assertSame($trialEnds->timestamp, $user->trial_ends_at->timestamp);
        // Nothing was charged, so nothing is billed.
        $this->assertSame(0, Invoice::where('user_id', $user->id)->count());

        $paidUntil = now()->addMonth()->startOfSecond();

        $this->postAdaptyEvent($this->event(
            'trial_converted',
            ['transaction_id' => 'txn_convert', 'expires_at' => $paidUntil->toIso8601String()],
            ['customer_user_id' => (string) $user->id],
        ))->assertOk();

        $user->refresh();
        $this->assertSame('premium', $user->plan);
        $this->assertFalse($user->onTrial());
        $this->assertNull($user->trial_ends_at);
        $this->assertSame($paidUntil->timestamp, $user->plan_expires_at->timestamp);
        $this->assertSame(1, Invoice::where('user_id', $user->id)->count());
    }

    public function test_a_purchase_made_before_sign_up_is_parked_and_claimed_after_it(): void
    {
        // Onboarding shows the paywall before the account exists, so the event
        // arrives with an anonymous profile and nobody to attribute it to.
        $this->postAdaptyEvent($this->event('subscription_initial_purchase'))
            ->assertOk()
            ->assertJsonPath('status', 'deferred');

        $this->assertDatabaseHas('adapty_events', ['profile_id' => 'prof_1', 'processed_at' => null]);
        $this->assertSame(0, Subscription::count());

        $user = User::factory()->create(['role' => 'user', 'plan' => 'free']);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/me/billing/adapty', ['profile_id' => 'prof_1'])
            ->assertOk()
            ->assertJsonPath('data.replayed', 1)
            ->assertJsonPath('data.is_premium', true);

        $user->refresh();
        $this->assertSame('premium', $user->plan);
        $this->assertSame('prof_1', $user->adapty_profile_id);
        $this->assertSame('active', $user->subscriptions()->firstOrFail()->status);
        $this->assertDatabaseMissing('adapty_events', ['profile_id' => 'prof_1', 'processed_at' => null]);
    }

    public function test_a_profile_cannot_be_claimed_away_from_another_account(): void
    {
        $owner = User::factory()->create();
        $owner->forceFill(['adapty_profile_id' => 'prof_1'])->save();

        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/me/billing/adapty', ['profile_id' => 'prof_1'])
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'profile_already_linked');
    }

    public function test_a_sandbox_purchase_is_ignored_when_the_environment_refuses_them(): void
    {
        config(['services.adapty.accept_sandbox' => false]);
        $user = User::factory()->create(['role' => 'user', 'plan' => 'free']);

        $this->postAdaptyEvent($this->event(
            'subscription_initial_purchase',
            ['environment' => 'Sandbox'],
            ['customer_user_id' => (string) $user->id],
        ))->assertOk();

        $this->assertSame('free', $user->refresh()->plan);
        $this->assertSame(0, Subscription::count());
    }

    public function test_an_event_for_another_access_level_is_left_alone(): void
    {
        $user = User::factory()->create(['role' => 'user', 'plan' => 'free']);

        $this->postAdaptyEvent($this->event(
            'subscription_initial_purchase',
            ['access_level_id' => 'masterclass'],
            ['customer_user_id' => (string) $user->id],
        ))->assertOk();

        $this->assertSame('free', $user->refresh()->plan);
        $this->assertSame(0, Subscription::count());
    }

    public function test_a_yearly_product_is_recorded_as_a_yearly_cycle(): void
    {
        $user = User::factory()->create(['role' => 'user', 'plan' => 'free']);

        $this->postAdaptyEvent($this->event(
            'subscription_initial_purchase',
            [
                'vendor_product_id' => 'com.harmoniva.premium.yearly',
                'expires_at' => now()->addYear()->toIso8601String(),
                'price_local' => 40.00,
            ],
            ['customer_user_id' => (string) $user->id],
        ))->assertOk();

        $this->assertSame('yearly', $user->subscriptions()->firstOrFail()->billing_cycle);
        $this->assertSame('yearly', $user->refresh()->plan_cycle);
    }

    public function test_a_store_renewal_is_not_labelled_as_a_stripe_invoice(): void
    {
        $user = User::factory()->create(['role' => 'user', 'plan' => 'free']);

        $this->postAdaptyEvent($this->event(
            'subscription_initial_purchase',
            [],
            ['customer_user_id' => (string) $user->id],
        ))->assertOk();

        $this->postAdaptyEvent($this->event(
            'subscription_renewed',
            ['transaction_id' => 'txn_2'],
            ['customer_user_id' => (string) $user->id],
        ))->assertOk();

        $renewal = $user->subscriptions()->firstOrFail()->invoices()->latest('id')->firstOrFail();

        // The billing history has to name the store that took the money.
        $this->assertSame('app_store com.harmoniva.premium.monthly', $renewal->notes);
        $this->assertStringNotContainsString('Stripe', (string) $renewal->notes);
    }

    public function test_the_same_renewal_charge_is_billed_once_under_a_second_event_id(): void
    {
        $user = User::factory()->create(['role' => 'user', 'plan' => 'free']);

        $this->postAdaptyEvent($this->event(
            'subscription_initial_purchase',
            [],
            ['customer_user_id' => (string) $user->id],
        ))->assertOk();

        $renewedTo = now()->addMonths(2)->startOfSecond();
        $renewal = $this->event(
            'subscription_renewed',
            ['transaction_id' => 'txn_2', 'expires_at' => $renewedTo->toIso8601String()],
            ['customer_user_id' => (string) $user->id],
        );

        $this->postAdaptyEvent($renewal)->assertOk();

        // Adapty can resend a delivered event under a fresh id (a dashboard
        // resend, a workspace replay), which the event ledger cannot collapse.
        // The charge itself is the same one, so it must not be billed twice.
        $resent = array_merge($renewal, ['event_id' => 'evt_resent']);
        $this->postAdaptyEvent($resent)->assertOk();

        $subscription = $user->subscriptions()->firstOrFail();

        $this->assertSame(2, $subscription->invoices()->count(), 'first charge + one renewal');
        $this->assertSame(1, $subscription->invoices()->where('provider_reference', 'txn_2')->count());
        // The period still moved, the resend is only ignored for the ledger.
        $this->assertSame($renewedTo->timestamp, $subscription->ends_at->timestamp);
    }

    public function test_the_api_surface_endpoint_delivers_the_same_events(): void
    {
        // The URL registered in the Adapty workspace. Both routes reach the same
        // controller and the same ledger, so a delivery repeated across them is
        // still applied once.
        $user = User::factory()->create(['role' => 'user', 'plan' => 'free']);
        $event = $this->event(
            'subscription_initial_purchase',
            [],
            ['customer_user_id' => (string) $user->id],
        );

        $this->call(
            'POST',
            route('webhooks.adapty.api'),
            [], [], [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_AUTHORIZATION' => 'Bearer '.self::SECRET],
            json_encode($event),
        )->assertOk();

        $this->assertSame('premium', $user->refresh()->plan);

        // Same event over the web route: recognised as already delivered.
        $this->postAdaptyEvent($event)->assertOk()->assertJsonPath('status', 'duplicate ignored');
        $this->assertSame(1, $user->subscriptions()->count());
    }

    public function test_the_api_surface_endpoint_still_demands_the_secret(): void
    {
        $this->call(
            'POST',
            route('webhooks.adapty.api'),
            [], [], [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($this->event('subscription_initial_purchase')),
        )->assertStatus(401);

        $this->assertDatabaseCount('adapty_events', 0);
    }
}
