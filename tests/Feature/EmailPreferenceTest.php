<?php

namespace Tests\Feature;

use App\Models\EmailAutomation;
use App\Models\EmailMessage;
use App\Models\EmailPreference;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Services\EmailCenter\AutomationEngine;
use App\Services\EmailCenter\EmailDispatchService;
use App\Services\EmailCenter\SuppressionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailPreferenceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    protected function makeUser(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'email_verified_at' => now(),
            'role' => 'user',
            'plan' => 'free',
        ], $overrides));
    }

    protected function makeTemplate(array $overrides = []): EmailTemplate
    {
        return EmailTemplate::create(array_merge([
            'name' => 'T',
            'slug' => 'tpl-'.uniqid(),
            'subject' => 'Hi {{user_first_name}}',
            'html_body' => '<html><body><p>Hi</p></body></html>',
            'category' => 'marketing',
            'is_active' => true,
        ], $overrides));
    }

    protected function automation(string $key): EmailAutomation
    {
        return EmailAutomation::create([
            'key' => $key,
            'name' => $key,
            'template_id' => $this->makeTemplate()->id,
            'enabled' => true,
            'config' => [],
        ]);
    }

    // --- The dispatch gate --------------------------------------------------

    public function test_default_user_receives_all_marketing(): void
    {
        config(['email-center.frequency_cap' => 0]); // isolate preferences from the cap
        $user = $this->makeUser();
        $service = app(EmailDispatchService::class);

        foreach (['welcome', 'first_exercise_reminder', 'weekly_progress', 'premium_upsell'] as $key) {
            $msg = $service->dispatch($user, 'automation', $this->makeTemplate(), automation: $this->automation($key));
            $this->assertNotNull($msg, "default user should receive {$key}");
        }
    }

    public function test_important_only_blocks_marketing_but_not_transactional(): void
    {
        $user = $this->makeUser();
        EmailPreference::create(['user_id' => $user->id, 'frequency' => 'important_only']);
        $service = app(EmailDispatchService::class);

        // welcome (onboarding) and an upsell are both marketing → blocked
        $this->assertNull($service->dispatch($user, 'automation', $this->makeTemplate(), automation: $this->automation('welcome')));
        $this->assertNull($service->dispatch($user, 'automation', $this->makeTemplate(), automation: $this->automation('premium_upsell')));

        // a transactional trial notice bypasses preferences entirely
        $this->assertNotNull($service->dispatch($user, 'transactional', $this->makeTemplate(['category' => 'transactional']), automation: $this->automation('trial_ending')));
    }

    public function test_topic_toggle_blocks_only_that_category(): void
    {
        $user = $this->makeUser();
        EmailPreference::create([
            'user_id' => $user->id, 'frequency' => 'all',
            'topic_offers' => false, 'topic_progress' => true, 'topic_tips' => true, 'topic_product' => true,
        ]);
        $service = app(EmailDispatchService::class);

        // offers off → premium blocked; progress on → weekly digest allowed
        $this->assertNull($service->dispatch($user, 'automation', $this->makeTemplate(), automation: $this->automation('premium_upsell')));
        $this->assertNotNull($service->dispatch($user, 'automation', $this->makeTemplate(), automation: $this->automation('weekly_progress')));
    }

    public function test_weekly_frequency_allows_only_progress(): void
    {
        $user = $this->makeUser();
        EmailPreference::create(['user_id' => $user->id, 'frequency' => 'weekly']);
        $service = app(EmailDispatchService::class);

        $this->assertNotNull($service->dispatch($user, 'automation', $this->makeTemplate(), automation: $this->automation('weekly_progress')));
        $this->assertNull($service->dispatch($user, 'automation', $this->makeTemplate(), automation: $this->automation('first_exercise_reminder')));
        // onboarding (welcome) always sends except under important_only
        $this->assertNotNull($service->dispatch($user, 'automation', $this->makeTemplate(), automation: $this->automation('welcome')));
    }

    // --- Preferences page ---------------------------------------------------

    public function test_signed_token_page_loads_and_saves(): void
    {
        $user = $this->makeUser();
        $message = app(EmailDispatchService::class)->dispatch($user, 'automation', $this->makeTemplate(), automation: $this->automation('welcome'));

        $showUrl = URL::signedRoute('email.preferences', ['token' => $message->tracking_token]);
        $this->get($showUrl)->assertOk()->assertSee($user->email);

        $saveUrl = URL::signedRoute('email.preferences.update', ['token' => $message->tracking_token]);
        $this->post($saveUrl, [
            'frequency' => 'weekly',
            'topic_progress' => '1',
            'unsubscribe_all' => '1',
        ])->assertOk();

        $this->assertDatabaseHas('email_preferences', ['user_id' => $user->id, 'frequency' => 'weekly', 'topic_tips' => false]);
        $this->assertDatabaseHas('email_suppressions', ['email' => mb_strtolower($user->email), 'reason' => 'unsubscribe']);
    }

    public function test_unchecking_unsubscribe_all_removes_suppression(): void
    {
        $user = $this->makeUser();
        app(SuppressionService::class)->suppress($user->email, 'unsubscribe');

        // A suppressed user cannot be dispatched marketing, so mint a message row
        // directly to obtain a valid preferences token.
        $message = EmailMessage::create([
            'user_id' => $user->id, 'recipient_email' => $user->email, 'email_type' => 'automation',
            'subject' => 'x', 'status' => 'sent',
        ]);

        $saveUrl = URL::signedRoute('email.preferences.update', ['token' => $message->tracking_token]);
        $this->post($saveUrl, ['frequency' => 'all'])->assertOk(); // unsubscribe_all absent → false

        $this->assertDatabaseMissing('email_suppressions', ['email' => mb_strtolower($user->email)]);
    }

    public function test_authenticated_preferences_page(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->get(route('email-preferences.edit'))->assertOk();

        $this->actingAs($user)->put(route('email-preferences.update'), [
            'frequency' => 'important_only',
        ])->assertRedirect(route('email-preferences.edit'));

        $this->assertDatabaseHas('email_preferences', ['user_id' => $user->id, 'frequency' => 'important_only']);
    }

    // --- premium_intro automation ------------------------------------------

    public function test_premium_intro_targets_new_free_users_only(): void
    {
        $template = $this->makeTemplate();
        $automation = EmailAutomation::create([
            'key' => 'premium_intro', 'name' => 'Premium Intro', 'template_id' => $template->id,
            'enabled' => true, 'config' => ['min_account_days' => 3],
        ]);

        $due = $this->makeUser(['created_at' => now()->subDays(4)]);
        $tooNew = $this->makeUser(['created_at' => now()->subDay()]);
        $premium = $this->makeUser(['created_at' => now()->subDays(4), 'plan' => 'premium']);

        app(AutomationEngine::class)->run();

        $this->assertEquals(1, EmailMessage::where('automation_id', $automation->id)->where('user_id', $due->id)->count());
        $this->assertEquals(0, EmailMessage::where('user_id', $tooNew->id)->count());
        $this->assertEquals(0, EmailMessage::where('user_id', $premium->id)->count());

        // sent once
        app(AutomationEngine::class)->run();
        $this->assertEquals(1, EmailMessage::where('automation_id', $automation->id)->count());
    }

    // --- Template sync command ---------------------------------------------

    public function test_sync_templates_refreshes_body_and_preserves_active_flag(): void
    {
        $template = EmailTemplate::create([
            'name' => 'Welcome', 'slug' => 'welcome', 'subject' => 'old',
            'html_body' => '<html><body>old</body></html>', 'category' => 'marketing', 'is_active' => false,
        ]);

        $this->artisan('email:sync-templates')->assertSuccessful();

        $template->refresh();
        $this->assertStringContainsString('Manage email preferences', $template->html_body);
        $this->assertStringContainsString('Welcome aboard', $template->html_body);
        $this->assertFalse($template->is_active, 'admin is_active flag must be preserved');
    }
}
