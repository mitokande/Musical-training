<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TrialTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['payments.trial.enabled' => true, 'payments.trial.days' => 15]);
    }

    private function premiumPlan(string $role = 'user'): Plan
    {
        return Plan::create([
            'name' => ucfirst($role).' Premium',
            'slug' => "{$role}-premium",
            'role' => $role,
            'type' => 'premium',
            'price_monthly' => 6.90,
            'price_yearly' => 40.00,
            'currency' => 'USD',
            'is_active' => true,
        ]);
    }

    private function freeUser(string $role = 'user'): User
    {
        return User::factory()->create(['role' => $role, 'plan' => 'free']);
    }

    public function test_starting_a_trial_grants_premium_without_an_invoice(): void
    {
        $this->premiumPlan();
        $user = $this->freeUser();

        $this->actingAs($user)->post(route('trial.store'))
            ->assertRedirect(route('billing.index'));

        $user->refresh();
        $this->assertSame('premium', $user->plan);
        $this->assertTrue($user->isPremium());
        $this->assertTrue($user->isEffectivelyPremium());
        $this->assertTrue($user->onTrial());
        // A trial is not a paid cycle: nothing to bill, nothing to renew.
        $this->assertNull($user->plan_cycle);
        $this->assertNotNull($user->trial_started_at);
        $this->assertEqualsWithDelta(15, now()->diffInDays($user->trial_ends_at, false), 0.01);

        $subscription = Subscription::first();
        $this->assertNotNull($subscription);
        $this->assertSame('trialing', $subscription->status);
        $this->assertSame('trial', $subscription->payment_provider);
        $this->assertFalse((bool) $subscription->auto_renew);
        $this->assertEquals(0.0, (float) $subscription->amount);

        // Nothing was charged, so the billing history must stay empty.
        $this->assertSame(0, Invoice::count());
    }

    public function test_trial_can_only_be_claimed_once(): void
    {
        $this->premiumPlan();
        $user = $this->freeUser();

        $this->actingAs($user)->post(route('trial.store'));
        $this->actingAs($user->fresh())->post(route('trial.store'));

        $this->assertSame(1, Subscription::count());
    }

    public function test_a_lapsed_trial_user_cannot_claim_a_second_trial(): void
    {
        $this->premiumPlan();
        $user = $this->freeUser();

        $this->actingAs($user)->post(route('trial.store'));

        $this->travel(16)->days();
        $this->artisan('subscriptions:expire');

        $user->refresh();
        $this->assertSame('free', $user->plan);
        $this->assertFalse($user->canStartTrial());

        $this->actingAs($user)->post(route('trial.store'));
        $this->assertSame(1, Subscription::count());
    }

    public function test_admins_cannot_start_a_trial(): void
    {
        $this->premiumPlan();
        $admin = User::factory()->create(['role' => 'admin', 'plan' => 'free']);

        $this->actingAs($admin)->post(route('trial.store'));

        $this->assertSame(0, Subscription::count());
        $this->assertSame('free', $admin->fresh()->plan);
    }

    public function test_an_already_premium_user_cannot_start_a_trial(): void
    {
        $this->premiumPlan();
        $user = User::factory()->create(['role' => 'user', 'plan' => 'premium']);

        $this->actingAs($user)->post(route('trial.store'));

        $this->assertSame(0, Subscription::count());
        $this->assertNull($user->fresh()->trial_started_at);
    }

    public function test_trial_works_for_teachers_and_syncs_the_profile_tier(): void
    {
        $this->premiumPlan('teacher');
        $teacher = $this->freeUser('teacher');
        $teacher->teacherProfile()->create(['tier' => TeacherProfile::TIER_BASIC, 'status' => TeacherProfile::STATUS_DRAFT]);

        $this->actingAs($teacher)->post(route('trial.store'));

        $teacher->refresh();
        $this->assertTrue($teacher->onTrial());
        // Teacher/school CRM features gate on the profile tier, not the plan.
        $this->assertSame(TeacherProfile::TIER_PREMIUM, $teacher->teacherProfile->fresh()->tier);
        $this->assertSame('teacher-premium', Subscription::first()->plan->slug);
    }

    public function test_trial_works_for_schools(): void
    {
        $this->premiumPlan('school');
        $school = $this->freeUser('school');

        $this->actingAs($school)->post(route('trial.store'));

        $this->assertTrue($school->fresh()->onTrial());
        $this->assertSame('school-premium', Subscription::first()->plan->slug);
    }

    public function test_expiring_a_trial_drops_the_user_back_to_free(): void
    {
        $this->premiumPlan('teacher');
        $teacher = $this->freeUser('teacher');
        $teacher->teacherProfile()->create(['tier' => TeacherProfile::TIER_BASIC, 'status' => TeacherProfile::STATUS_DRAFT]);

        $this->actingAs($teacher)->post(route('trial.store'));

        $this->travel(16)->days();
        $this->artisan('subscriptions:expire')->assertSuccessful();

        $teacher->refresh();
        $this->assertSame('free', $teacher->plan);
        $this->assertFalse($teacher->onTrial());
        $this->assertFalse($teacher->isEffectivelyPremium());
        $this->assertNull($teacher->plan_expires_at);
        $this->assertSame('expired', Subscription::first()->status);
        $this->assertSame(TeacherProfile::TIER_BASIC, $teacher->teacherProfile->fresh()->tier);

        // The once-ever record survives the downgrade.
        $this->assertNotNull($teacher->trial_started_at);
    }

    public function test_a_running_trial_is_not_expired_early(): void
    {
        $this->premiumPlan();
        $user = $this->freeUser();

        $this->actingAs($user)->post(route('trial.store'));

        $this->travel(14)->days();
        $this->artisan('subscriptions:expire');

        $this->assertSame('premium', $user->fresh()->plan);
        $this->assertSame('trialing', Subscription::first()->status);
    }

    public function test_admin_can_grant_and_reset_a_trial(): void
    {
        $this->premiumPlan();
        $admin = User::factory()->create(['role' => 'admin']);
        $user = $this->freeUser();

        $this->actingAs($admin)->post(route('admin.users.bulk-action'), [
            'action' => 'start_trial',
            'user_ids' => [$user->id],
        ])->assertRedirect(route('admin.users.index'));

        $this->assertTrue($user->fresh()->onTrial());

        $this->actingAs($admin)->post(route('admin.users.bulk-action'), [
            'action' => 'reset_trial',
            'user_ids' => [$user->id],
        ]);

        $this->assertNull($user->fresh()->trial_started_at);
    }

    // --- Checkout page: the only place the trial is advertised ------------

    public function test_checkout_page_offers_the_trial_while_payments_are_closed(): void
    {
        config(['payments.checkout_enabled' => false]);
        $this->premiumPlan();
        $user = $this->freeUser();

        $this->actingAs($user)->get(route('checkout.show'))
            ->assertOk()
            ->assertSee('Try Premium free for 15 days')
            ->assertSee('Start my 15-day free trial')
            ->assertSee('No credit card required')
            // The payment form must be gone, not merely hidden.
            ->assertDontSee('Continue to Secure Payment');
    }

    public function test_checkout_page_reports_status_to_a_user_already_on_trial(): void
    {
        config(['payments.checkout_enabled' => false]);
        $this->premiumPlan();
        $user = $this->freeUser();

        $this->actingAs($user)->post(route('trial.store'));

        $this->actingAs($user->fresh())->get(route('checkout.show'))
            ->assertOk()
            ->assertSee('Your Premium trial is active')
            // No second trial on offer.
            ->assertDontSee('Start my 15-day free trial')
            ->assertDontSee('Continue to Secure Payment');
    }

    public function test_checkout_page_says_payments_open_soon_once_the_trial_is_spent(): void
    {
        config(['payments.checkout_enabled' => false]);
        $this->premiumPlan();
        $user = $this->freeUser();

        $this->actingAs($user)->post(route('trial.store'));
        $this->travel(16)->days();
        $this->artisan('subscriptions:expire');

        $this->actingAs($user->fresh())->get(route('checkout.show'))
            ->assertOk()
            ->assertSee('Card payments open soon')
            ->assertDontSee('Start my 15-day free trial')
            ->assertDontSee('Continue to Secure Payment');
    }

    public function test_direct_post_to_checkout_is_blocked_while_payments_are_closed(): void
    {
        config(['payments.checkout_enabled' => false]);
        $this->premiumPlan();
        $user = $this->freeUser();

        $this->actingAs($user)->post(route('checkout.store'), ['cycle' => 'monthly', 'terms' => '1'])
            ->assertRedirect(route('checkout.show'));

        // Crucially, no order was created behind the redirect.
        $this->assertSame(0, Subscription::count());
    }

    public function test_the_trial_is_not_advertised_outside_the_checkout_page(): void
    {
        config(['payments.checkout_enabled' => false]);
        $this->premiumPlan();
        $user = $this->freeUser();

        // Upgrade CTAs across the app must keep selling Premium, not the trial.
        foreach (['pricing.index', 'billing.index', 'profile.edit', 'games.index', 'dashboard'] as $routeName) {
            $this->actingAs($user)->get(route($routeName))
                ->assertOk()
                ->assertDontSee('free trial', false)
                ->assertDontSee('15-day', false);
        }
    }

    public function test_a_trialing_user_can_still_reach_checkout_to_convert(): void
    {
        config(['payments.checkout_enabled' => true]);
        $this->premiumPlan();
        $user = $this->freeUser();

        $this->actingAs($user)->post(route('trial.store'));

        $this->actingAs($user->fresh())->get(route('checkout.show'))->assertOk();
    }

    public function test_converting_from_a_trial_closes_the_trial_subscription(): void
    {
        config(['payments.checkout_enabled' => true, 'payments.manual.auto_confirm' => true]);
        $this->premiumPlan();
        $user = $this->freeUser();

        $this->actingAs($user)->post(route('trial.store'));
        $trial = Subscription::first();

        $this->actingAs($user->fresh())->post(route('checkout.store'), [
            'cycle' => 'monthly',
            'terms' => '1',
        ]);

        $this->assertSame('expired', $trial->fresh()->status);

        $user->refresh();
        $this->assertSame('premium', $user->plan);
        $this->assertSame('monthly', $user->plan_cycle);
        $this->assertFalse($user->onTrial());

        // The paid subscription must survive the next expiry sweep.
        $this->artisan('subscriptions:expire');
        $this->assertSame('premium', $user->fresh()->plan);
    }

    // --- Rendering ---------------------------------------------------------

    /**
     * Granting Premium changes what these pages render, so make sure each one
     * still loads in all three trial states.
     */
    #[DataProvider('trialSurfaces')]
    public function test_key_surfaces_render_in_every_trial_state(string $routeName): void
    {
        config(['payments.checkout_enabled' => false]);
        $this->premiumPlan();
        $user = $this->freeUser();

        // Eligible for the trial.
        $this->actingAs($user)->get(route($routeName))->assertOk();

        // Running trial.
        $this->actingAs($user)->post(route('trial.store'));
        $this->actingAs($user->fresh())->get(route($routeName))->assertOk();

        // Trial spent, back on free.
        $this->travel(16)->days();
        $this->artisan('subscriptions:expire');
        $this->actingAs($user->fresh())->get(route($routeName))->assertOk();
    }

    public static function trialSurfaces(): array
    {
        return [
            'checkout' => ['checkout.show'],
            'pricing' => ['pricing.index'],
            'teacher pricing' => ['pricing.teachers'],
            'billing' => ['billing.index'],
            'profile' => ['profile.edit'],
            'games' => ['games.index'],
        ];
    }

    public function test_billing_page_describes_a_trial_rather_than_a_paid_plan(): void
    {
        config(['payments.checkout_enabled' => false]);
        $this->premiumPlan();
        $user = $this->freeUser();

        $this->actingAs($user)->post(route('trial.store'));

        $this->actingAs($user->fresh())->get(route('billing.index'))
            ->assertOk()
            ->assertSee(__('app.trial.plan_label'))
            // No card was taken, so there must be nothing to cancel.
            ->assertDontSee('Cancel subscription');
    }

    public function test_trial_endpoint_refuses_when_the_offer_is_disabled(): void
    {
        config(['payments.trial.enabled' => false]);
        $this->premiumPlan();
        $user = $this->freeUser();

        $this->actingAs($user)->post(route('trial.store'));

        $this->assertSame(0, Subscription::count());
        $this->assertSame('free', $user->fresh()->plan);
    }
}
