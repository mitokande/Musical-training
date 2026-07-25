<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_checkout_page_renders_for_free_user(): void
    {
        $this->premiumPlan();
        $user = $this->freeUser();

        $this->actingAs($user)->get(route('checkout.show'))
            ->assertOk()
            ->assertSee('Complete your upgrade');
    }

    public function test_purchase_activates_premium_with_auto_confirm(): void
    {
        config(['payments.manual.auto_confirm' => true]);
        $this->premiumPlan();
        $user = $this->freeUser();

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'cycle' => 'yearly',
            'terms' => '1',
        ]);

        $subscription = Subscription::first();
        $this->assertNotNull($subscription);
        $response->assertRedirect(route('checkout.success', $subscription));

        $this->assertSame('active', $subscription->status);
        $this->assertSame('yearly', $subscription->billing_cycle);

        $user->refresh();
        $this->assertSame('premium', $user->plan);
        $this->assertNotNull($user->plan_expires_at);
        $this->assertTrue($user->isPremium());

        $invoice = Invoice::first();
        $this->assertSame('paid', $invoice->status);
        $this->assertEquals(40.00, (float) $invoice->total_amount);
    }

    public function test_purchase_stays_pending_without_auto_confirm(): void
    {
        config(['payments.manual.auto_confirm' => false]);
        $this->premiumPlan();
        $user = $this->freeUser();

        $subscription = null;
        $this->actingAs($user)->post(route('checkout.store'), [
            'cycle' => 'monthly',
            'terms' => '1',
        ]);

        $subscription = Subscription::first();
        $this->assertSame('pending', $subscription->status);

        $user->refresh();
        $this->assertSame('free', $user->plan);

        // Admin confirms → premium granted.
        $admin = User::factory()->create(['role' => 'admin', 'plan' => 'free']);
        $this->actingAs($admin)->post(route('admin.subscriptions.confirm', $subscription))
            ->assertRedirect();

        $subscription->refresh();
        $user->refresh();
        $this->assertSame('active', $subscription->status);
        $this->assertSame('premium', $user->plan);
    }

    public function test_terms_must_be_accepted(): void
    {
        $this->premiumPlan();
        $user = $this->freeUser();

        $this->actingAs($user)->post(route('checkout.store'), ['cycle' => 'monthly'])
            ->assertSessionHasErrors('terms');

        $this->assertSame(0, Subscription::count());
    }

    public function test_already_premium_user_is_redirected_to_billing(): void
    {
        $this->premiumPlan();
        $user = User::factory()->create(['role' => 'user', 'plan' => 'premium']);

        $this->actingAs($user)->get(route('checkout.show'))
            ->assertRedirect(route('billing.index'));
    }

    public function test_cancel_turns_off_auto_renew_but_keeps_access(): void
    {
        config(['payments.manual.auto_confirm' => true]);
        $this->premiumPlan();
        $user = $this->freeUser();

        $this->actingAs($user)->post(route('checkout.store'), ['cycle' => 'monthly', 'terms' => '1']);
        $subscription = Subscription::first();

        $this->actingAs($user)->post(route('billing.cancel', $subscription))
            ->assertRedirect(route('billing.index'));

        $subscription->refresh();
        $user->refresh();
        $this->assertFalse((bool) $subscription->auto_renew);
        $this->assertNotNull($subscription->cancelled_at);
        // Still premium until period end.
        $this->assertSame('premium', $user->plan);
    }

    public function test_result_and_billing_pages_render(): void
    {
        config(['payments.manual.auto_confirm' => true]);
        $this->premiumPlan();
        $user = $this->freeUser();

        $this->actingAs($user)->post(route('checkout.store'), ['cycle' => 'monthly', 'terms' => '1']);
        $subscription = Subscription::first();

        $this->actingAs($user)->get(route('checkout.success', $subscription))
            ->assertOk()->assertSee("You're Premium", false);

        $this->actingAs($user)->get(route('billing.index'))
            ->assertOk()->assertSee('Billing & Subscription', false)
            ->assertSee($subscription->invoices()->first()->invoice_number);
    }

    public function test_admin_refund_revokes_premium(): void
    {
        config(['payments.manual.auto_confirm' => true]);
        $this->premiumPlan();
        $user = $this->freeUser();

        $this->actingAs($user)->post(route('checkout.store'), ['cycle' => 'monthly', 'terms' => '1']);
        $invoice = Invoice::first();

        $admin = User::factory()->create(['role' => 'admin', 'plan' => 'free']);
        $this->actingAs($admin)->post(route('admin.invoices.refund', $invoice))
            ->assertRedirect();

        $invoice->refresh();
        $user->refresh();
        $this->assertSame('refunded', $invoice->status);
        $this->assertSame('free', $user->plan);
    }

    public function test_teacher_purchase_syncs_profile_tier_and_refund_reverts_it(): void
    {
        config(['payments.manual.auto_confirm' => true]);
        Plan::create([
            'name' => 'Teacher Premium', 'slug' => 'teacher-premium', 'role' => 'teacher',
            'type' => 'premium', 'price_monthly' => 16.90, 'price_yearly' => 149.00,
            'currency' => 'USD', 'is_active' => true,
        ]);
        $user = User::factory()->create(['role' => 'teacher', 'plan' => 'free']);

        $this->actingAs($user)->post(route('checkout.store'), ['cycle' => 'monthly', 'terms' => '1']);

        $user->refresh();
        $this->assertSame('premium', $user->plan);
        // Teacher CRM premium features gate on the profile tier — it must flip too.
        $this->assertTrue($user->teacherProfile()->first()->isPremiumTier());

        // Refund reverts the tier back to basic.
        $admin = User::factory()->create(['role' => 'admin', 'plan' => 'free']);
        $this->actingAs($admin)->post(route('admin.invoices.refund', Invoice::first()))->assertRedirect();

        $user->refresh();
        $this->assertSame('free', $user->plan);
        $this->assertFalse($user->teacherProfile()->first()->isPremiumTier());
    }
}
