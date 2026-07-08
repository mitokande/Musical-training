<?php

namespace Tests\Feature;

use App\Models\EmailAutomation;
use App\Models\EmailCampaign;
use App\Models\EmailEvent;
use App\Models\EmailMessage;
use App\Models\EmailSuppression;
use App\Models\EmailTemplate;
use App\Models\SupportConversation;
use App\Models\User;
use App\Services\EmailCenter\AutomationEngine;
use App\Services\EmailCenter\EmailDispatchService;
use App\Services\EmailCenter\SegmentBuilder;
use App\Services\EmailCenter\SesEventProcessor;
use App\Services\EmailCenter\SesStatusService;
use App\Services\EmailCenter\SnsMessageValidator;
use App\Services\EmailCenter\SuppressionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailCenterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    protected function makeTemplate(array $overrides = []): EmailTemplate
    {
        return EmailTemplate::create(array_merge([
            'name' => 'Test Template',
            'slug' => 'test-template-'.uniqid(),
            'subject' => 'Hello {{user_first_name}}',
            'html_body' => '<html><body><p>Hi {{user_first_name}}</p><a href="https://harmoniva.app/dashboard">Go</a></body></html>',
            'category' => 'marketing',
            'is_active' => true,
        ], $overrides));
    }

    protected function makeVerifiedUser(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'email_verified_at' => now(),
            'role' => 'user',
            'plan' => 'free',
        ], $overrides));
    }

    // --- Dispatch pipeline ---

    public function test_dispatch_creates_message_and_sends(): void
    {
        $user = $this->makeVerifiedUser();
        $template = $this->makeTemplate();

        $message = app(EmailDispatchService::class)->dispatch(
            recipient: $user,
            emailType: 'campaign',
            template: $template,
        );

        $this->assertNotNull($message);
        // sync queue: the job already ran and marked it sent
        $this->assertEquals('sent', $message->fresh()->status);
        $this->assertNotNull($message->tracking_token);
    }

    public function test_marketing_mail_blocked_for_suppressed_address(): void
    {
        $user = $this->makeVerifiedUser();
        app(SuppressionService::class)->suppress($user->email, 'unsubscribe');

        $message = app(EmailDispatchService::class)->dispatch(
            recipient: $user,
            emailType: 'campaign',
            template: $this->makeTemplate(),
        );

        $this->assertNull($message);
    }

    public function test_transactional_mail_allowed_for_unsubscribed_but_not_hard_bounced(): void
    {
        $service = app(EmailDispatchService::class);
        $template = $this->makeTemplate(['category' => 'transactional']);

        app(SuppressionService::class)->suppress('unsub@example.com', 'unsubscribe');
        $this->assertNotNull($service->dispatch('unsub@example.com', 'transactional', $template));

        app(SuppressionService::class)->suppress('bounced@example.com', 'hard_bounce');
        $this->assertNull($service->dispatch('bounced@example.com', 'transactional', $template));
    }

    public function test_frequency_cap_blocks_marketing_mail(): void
    {
        config(['email-center.frequency_cap' => 2]);
        $user = $this->makeVerifiedUser();
        $template = $this->makeTemplate();
        $service = app(EmailDispatchService::class);

        $this->assertNotNull($service->dispatch($user, 'campaign', $template));
        $this->assertNotNull($service->dispatch($user, 'campaign', $template));
        $this->assertNull($service->dispatch($user, 'campaign', $template));

        // transactional ignores the cap
        $this->assertNotNull($service->dispatch($user, 'transactional', $this->makeTemplate(['category' => 'transactional'])));
    }

    public function test_unverified_users_never_get_marketing_mail(): void
    {
        $user = $this->makeVerifiedUser(['email_verified_at' => null]);

        $message = app(EmailDispatchService::class)->dispatch($user, 'campaign', $this->makeTemplate());

        $this->assertNull($message);
    }

    // --- Tracking endpoints ---

    public function test_open_pixel_records_open(): void
    {
        $user = $this->makeVerifiedUser();
        $message = app(EmailDispatchService::class)->dispatch($user, 'campaign', $this->makeTemplate());

        $response = $this->get(route('email.open', ['token' => $message->tracking_token]));

        $response->assertOk();
        $this->assertEquals('image/gif', $response->headers->get('Content-Type'));
        $this->assertNotNull($message->fresh()->opened_at);
        $this->assertDatabaseHas('email_events', [
            'email_message_id' => $message->id,
            'event_type' => 'opened',
            'source' => 'internal',
        ]);
    }

    public function test_click_redirect_requires_valid_signature(): void
    {
        $user = $this->makeVerifiedUser();
        $message = app(EmailDispatchService::class)->dispatch($user, 'campaign', $this->makeTemplate());

        $this->get(route('email.click', ['token' => $message->tracking_token, 'url' => 'https://harmoniva.app/x']))
            ->assertForbidden();

        $signed = URL::signedRoute('email.click', ['token' => $message->tracking_token, 'url' => 'https://harmoniva.app/x']);
        $this->get($signed)->assertRedirect('https://harmoniva.app/x');

        $this->assertNotNull($message->fresh()->clicked_at);
    }

    public function test_unsubscribe_flow_suppresses_address(): void
    {
        $user = $this->makeVerifiedUser();
        $message = app(EmailDispatchService::class)->dispatch($user, 'campaign', $this->makeTemplate());

        $url = URL::signedRoute('email.unsubscribe', ['token' => $message->tracking_token]);

        $this->get($url)->assertOk()->assertSee('Unsubscribe');
        $this->post($url)->assertOk();

        $this->assertDatabaseHas('email_suppressions', [
            'email' => mb_strtolower($user->email),
            'reason' => 'unsubscribe',
        ]);
    }

    // --- SNS webhook ---

    public function test_webhook_rejects_invalid_payload(): void
    {
        $this->postJson('/webhooks/aws/ses/events', ['foo' => 'bar'])->assertForbidden();
        $this->post('/webhooks/aws/ses/events', [], ['Content-Type' => 'text/plain'])->assertStatus(400);
    }

    public function test_webhook_processes_delivery_event(): void
    {
        $this->mock(SnsMessageValidator::class)->shouldReceive('validate')->andReturnTrue();

        $user = $this->makeVerifiedUser();
        $message = app(EmailDispatchService::class)->dispatch($user, 'campaign', $this->makeTemplate());
        $message->update(['ses_message_id' => 'ses-test-123']);

        $event = [
            'eventType' => 'Delivery',
            'mail' => ['messageId' => 'ses-test-123', 'destination' => [$user->email], 'timestamp' => now()->toIso8601String()],
            'delivery' => ['timestamp' => now()->toIso8601String()],
        ];

        $this->postJson('/webhooks/aws/ses/events', [
            'Type' => 'Notification',
            'MessageId' => 'sns-1',
            'TopicArn' => 'arn:test',
            'Message' => json_encode($event),
        ])->assertOk();

        $this->assertEquals('delivered', $message->fresh()->status);
        $this->assertNotNull($message->fresh()->delivered_at);
    }

    public function test_hard_bounce_suppresses_recipient(): void
    {
        $user = $this->makeVerifiedUser();
        $message = app(EmailDispatchService::class)->dispatch($user, 'campaign', $this->makeTemplate());
        $message->update(['ses_message_id' => 'ses-bounce-1']);

        app(SesEventProcessor::class)->process([
            'eventType' => 'Bounce',
            'mail' => ['messageId' => 'ses-bounce-1', 'destination' => [$user->email]],
            'bounce' => [
                'bounceType' => 'Permanent',
                'bounceSubType' => 'General',
                'bouncedRecipients' => [['emailAddress' => $user->email]],
                'timestamp' => now()->toIso8601String(),
            ],
        ]);

        $this->assertDatabaseHas('email_suppressions', [
            'email' => mb_strtolower($user->email),
            'reason' => 'hard_bounce',
        ]);
        $this->assertEquals('bounced', $message->fresh()->status);
    }

    public function test_complaint_suppresses_recipient(): void
    {
        $user = $this->makeVerifiedUser();
        $message = app(EmailDispatchService::class)->dispatch($user, 'campaign', $this->makeTemplate());
        $message->update(['ses_message_id' => 'ses-complaint-1']);

        app(SesEventProcessor::class)->process([
            'eventType' => 'Complaint',
            'mail' => ['messageId' => 'ses-complaint-1', 'destination' => [$user->email]],
            'complaint' => [
                'complainedRecipients' => [['emailAddress' => $user->email]],
                'complaintFeedbackType' => 'abuse',
                'timestamp' => now()->toIso8601String(),
            ],
        ]);

        $this->assertDatabaseHas('email_suppressions', [
            'email' => mb_strtolower($user->email),
            'reason' => 'complaint',
        ]);
    }

    public function test_duplicate_ses_events_are_idempotent(): void
    {
        $event = [
            'eventType' => 'Open',
            'mail' => ['messageId' => 'ses-dup-1', 'destination' => ['x@example.com'], 'timestamp' => '2026-07-05T10:00:00Z'],
            'open' => ['timestamp' => '2026-07-05T10:00:00Z'],
        ];

        app(SesEventProcessor::class)->process($event);
        app(SesEventProcessor::class)->process($event);

        $this->assertEquals(1, EmailEvent::where('ses_message_id', 'ses-dup-1')->count());
    }

    // --- Segments & campaigns ---

    public function test_segment_builder_filters_and_exclusions(): void
    {
        $free = $this->makeVerifiedUser(['plan' => 'free']);
        $premium = $this->makeVerifiedUser(['plan' => 'premium']);
        $unverified = $this->makeVerifiedUser(['email_verified_at' => null]);
        $suppressed = $this->makeVerifiedUser();
        EmailSuppression::create(['email' => mb_strtolower($suppressed->email), 'reason' => 'complaint', 'suppressed_at' => now()]);

        $builder = app(SegmentBuilder::class);

        $all = $builder->query([])->pluck('id');
        $this->assertTrue($all->contains($free->id));
        $this->assertTrue($all->contains($premium->id));
        $this->assertFalse($all->contains($unverified->id));
        $this->assertFalse($all->contains($suppressed->id));

        $freeOnly = $builder->query(['plans' => ['free']])->pluck('id');
        $this->assertTrue($freeOnly->contains($free->id));
        $this->assertFalse($freeOnly->contains($premium->id));
    }

    public function test_campaign_send_flow_queues_messages_for_segment(): void
    {
        $admin = $this->makeVerifiedUser(['role' => 'admin']);
        $users = collect(range(1, 3))->map(fn () => $this->makeVerifiedUser());
        $template = $this->makeTemplate();

        $campaign = EmailCampaign::create([
            'name' => 'Test Blast',
            'subject' => 'Big News',
            'template_id' => $template->id,
            'segment' => ['plans' => ['free']],
            'status' => 'draft',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.email-campaigns.send', $campaign))
            ->assertRedirect();

        $campaign->refresh();
        $this->assertContains($campaign->status, ['sending', 'sent']);
        $this->assertGreaterThanOrEqual(3, EmailMessage::where('campaign_id', $campaign->id)->count());
    }

    // --- Automations ---

    public function test_welcome_automation_sends_once_to_recent_users(): void
    {
        $template = $this->makeTemplate();
        $automation = EmailAutomation::create([
            'key' => 'welcome',
            'name' => 'Welcome',
            'template_id' => $template->id,
            'enabled' => true,
            'config' => ['delay_hours' => 1],
        ]);

        $due = $this->makeVerifiedUser(['created_at' => now()->subHours(2)]);
        $tooNew = $this->makeVerifiedUser(['created_at' => now()->subMinutes(10)]);
        $tooOld = $this->makeVerifiedUser(['created_at' => now()->subDays(30)]);

        app(AutomationEngine::class)->run();

        $this->assertEquals(1, EmailMessage::where('automation_id', $automation->id)->where('user_id', $due->id)->count());
        $this->assertEquals(0, EmailMessage::where('user_id', $tooNew->id)->count());
        $this->assertEquals(0, EmailMessage::where('user_id', $tooOld->id)->count());

        // second run does not resend
        app(AutomationEngine::class)->run();
        $this->assertEquals(1, EmailMessage::where('automation_id', $automation->id)->count());
    }

    // --- Admin pages ---

    public function test_admin_email_center_pages_load(): void
    {
        $admin = $this->makeVerifiedUser(['role' => 'admin']);

        // fixture data so show/edit pages render every branch
        $template = $this->makeTemplate();
        $campaign = EmailCampaign::create([
            'name' => 'Fixture Campaign',
            'subject' => 'Hello',
            'template_id' => $template->id,
            'segment' => ['plans' => ['free']],
            'status' => 'draft',
            'created_by' => $admin->id,
        ]);
        EmailAutomation::create(['key' => 'welcome', 'name' => 'Welcome', 'template_id' => $template->id, 'config' => ['delay_hours' => 1]]);
        $message = app(EmailDispatchService::class)->dispatch($this->makeVerifiedUser(), 'campaign', $template, campaign: $campaign);
        $conversation = SupportConversation::create([
            'subject' => 'Help', 'subject_key' => 'help', 'contact_email' => 'guest@example.com',
            'status' => 'open', 'last_message_at' => now(), 'message_count' => 1,
        ]);
        $conversation->messages()->create([
            'direction' => 'inbound', 'from_email' => 'guest@example.com',
            'to_email' => 'support@harmoniva.app', 'subject' => 'Help',
            'plain_text_body' => 'I need help', 'received_at' => now(),
        ]);

        $this->mock(SesStatusService::class)
            ->shouldReceive('status')->andReturn(['ok' => false, 'error' => 'mocked']);

        foreach ([
            route('admin.email-center.dashboard'),
            route('admin.email-campaigns.index'),
            route('admin.email-campaigns.create'),
            route('admin.email-campaigns.show', $campaign),
            route('admin.email-campaigns.edit', $campaign),
            route('admin.email-templates.index'),
            route('admin.email-templates.create'),
            route('admin.email-templates.edit', $template),
            route('admin.email-templates.preview', $template),
            route('admin.email-automations.index'),
            route('admin.email-center.suppressions'),
            route('admin.email-center.logs'),
            route('admin.email-center.logs.show', $message),
            route('admin.email-center.settings'),
            route('admin.support-inbox.index'),
            route('admin.support-inbox.show', $conversation),
        ] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_non_admin_cannot_access_email_center(): void
    {
        $user = $this->makeVerifiedUser();

        $this->actingAs($user)->get(route('admin.email-center.dashboard'))->assertForbidden();
    }
}
