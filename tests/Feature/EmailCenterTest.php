<?php

namespace Tests\Feature;

use App\Models\EmailAutomation;
use App\Models\EmailCampaign;
use App\Models\EmailEvent;
use App\Models\EmailMessage;
use App\Models\EmailSuppression;
use App\Models\EmailTemplate;
use App\Models\ExerciseSession;
use App\Models\SchoolTeacherRelationship;
use App\Models\SupportConversation;
use App\Models\TeacherStudentRelationship;
use App\Models\User;
use App\Services\EmailCenter\AutomationEngine;
use App\Services\EmailCenter\EmailDispatchService;
use App\Services\EmailCenter\EmailTemplateLibrary;
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

    public function test_trial_ending_automation_targets_every_role_once_per_trial(): void
    {
        $template = $this->makeTemplate(['category' => 'transactional']);
        $automation = EmailAutomation::create([
            'key' => 'trial_ending',
            'name' => 'Trial Ending',
            'template_id' => $template->id,
            'enabled' => true,
            'config' => ['lead_days' => 3],
        ]);

        $onTrial = ['plan' => 'premium', 'trial_started_at' => now()->subDays(13), 'trial_ends_at' => now()->addDays(2)];

        // The trial is offered to all three roles, so the notice must reach them all.
        $student = $this->makeVerifiedUser($onTrial);
        $teacher = $this->makeVerifiedUser($onTrial + ['role' => 'teacher']);
        $school = $this->makeVerifiedUser($onTrial + ['role' => 'school']);

        $earlyInTrial = $this->makeVerifiedUser(['plan' => 'premium', 'trial_started_at' => now()->subDay(), 'trial_ends_at' => now()->addDays(14)]);
        $paying = $this->makeVerifiedUser(['plan' => 'premium']);

        app(AutomationEngine::class)->run();

        foreach ([$student, $teacher, $school] as $user) {
            $this->assertEquals(1, EmailMessage::where('automation_id', $automation->id)->where('user_id', $user->id)->count());
        }
        $this->assertEquals(0, EmailMessage::where('user_id', $earlyInTrial->id)->count());
        $this->assertEquals(0, EmailMessage::where('user_id', $paying->id)->count());

        // Trial notices are service mail, not marketing.
        $this->assertSame('transactional', EmailMessage::where('automation_id', $automation->id)->first()->email_type);

        // One notice per granted trial.
        app(AutomationEngine::class)->run();
        $this->assertEquals(3, EmailMessage::where('automation_id', $automation->id)->count());
    }

    // --- Audience fan-out ---

    public function test_automation_sends_each_audience_its_own_template_variant(): void
    {
        $base = $this->makeTemplate(['slug' => 'welcome']);
        EmailTemplate::create(['name' => 'T', 'slug' => 'welcome-teacher', 'subject' => 'T', 'html_body' => '<p>t</p>', 'category' => 'marketing', 'is_active' => true]);
        EmailTemplate::create(['name' => 'S', 'slug' => 'welcome-school', 'subject' => 'S', 'html_body' => '<p>s</p>', 'category' => 'marketing', 'is_active' => true]);

        EmailAutomation::create([
            'key' => 'welcome', 'name' => 'Welcome', 'template_id' => $base->id,
            'enabled' => true, 'config' => ['delay_hours' => 1],
        ]);

        $student = $this->makeVerifiedUser(['created_at' => now()->subHours(2)]);
        $teacher = $this->makeVerifiedUser(['role' => 'teacher', 'created_at' => now()->subHours(2)]);
        $school = $this->makeVerifiedUser(['role' => 'school', 'created_at' => now()->subHours(2)]);

        app(AutomationEngine::class)->run();

        $slugOf = fn (User $u) => EmailMessage::where('user_id', $u->id)->first()?->template?->slug;

        $this->assertSame('welcome', $slugOf($student));
        $this->assertSame('welcome-teacher', $slugOf($teacher));
        $this->assertSame('welcome-school', $slugOf($school));
    }

    public function test_first_step_reminder_asks_each_audience_for_its_own_first_step(): void
    {
        $automation = EmailAutomation::create([
            'key' => 'first_exercise_reminder', 'name' => 'First step',
            'template_id' => $this->makeTemplate()->id, 'enabled' => true,
            'config' => ['delay_days' => 2],
        ]);

        $old = ['created_at' => now()->subDays(5)];

        // Due: nothing to show for themselves yet.
        $idleStudent = $this->makeVerifiedUser($old);
        $teacherWithoutStudents = $this->makeVerifiedUser($old + ['role' => 'teacher']);
        $schoolWithoutTeachers = $this->makeVerifiedUser($old + ['role' => 'school']);

        // Not due: already took the first step.
        $practisingStudent = $this->makeVerifiedUser($old);
        ExerciseSession::create([
            'user_id' => $practisingStudent->id,
            'exercise_type' => 'single-note-practice',
            'settings_json' => [],
        ]);

        $teacherWithStudents = $this->makeVerifiedUser($old + ['role' => 'teacher']);
        TeacherStudentRelationship::create([
            'teacher_id' => $teacherWithStudents->id,
            'student_id' => $idleStudent->id,
            'status' => TeacherStudentRelationship::STATUS_ACTIVE,
        ]);

        $schoolWithTeachers = $this->makeVerifiedUser($old + ['role' => 'school']);
        SchoolTeacherRelationship::create([
            'school_id' => $schoolWithTeachers->id,
            'teacher_id' => $teacherWithStudents->id,
            'status' => SchoolTeacherRelationship::STATUS_ACTIVE,
        ]);

        app(AutomationEngine::class)->run();

        $got = fn (User $u) => EmailMessage::where('automation_id', $automation->id)->where('user_id', $u->id)->exists();

        $this->assertTrue($got($idleStudent));
        $this->assertTrue($got($teacherWithoutStudents));
        $this->assertTrue($got($schoolWithoutTeachers));

        $this->assertFalse($got($practisingStudent));
        $this->assertFalse($got($teacherWithStudents));
        $this->assertFalse($got($schoolWithTeachers));
    }

    public function test_audience_scope_partitions_every_user_exactly_once(): void
    {
        $this->makeVerifiedUser();
        $this->makeVerifiedUser(['role' => 'teacher']);
        $this->makeVerifiedUser(['role' => 'school']);

        $total = User::count();
        $sum = collect(AutomationEngine::AUDIENCES)
            ->sum(fn ($audience) => User::forEmailAudience($audience)->count());

        $this->assertSame($total, $sum);

        // and the SQL scope agrees with the PHP accessor for every account
        foreach (User::with('teacherProfile')->get() as $user) {
            $this->assertTrue(
                User::forEmailAudience($user->emailAudience())->whereKey($user->id)->exists(),
                "User {$user->id} is not in its own emailAudience() bucket"
            );
        }
    }

    public function test_system_templates_are_translated_into_every_supported_locale(): void
    {
        $records = app(EmailTemplateLibrary::class)->templateRecords();

        // 9 lifecycle automations × student/teacher/school
        $this->assertCount(27, $records);

        foreach ($records as $record) {
            foreach (EmailTemplate::LOCALES as $locale) {
                if ($locale === 'en') {
                    $this->assertNotSame('', trim($record['subject']), "{$record['slug']} has no English subject");

                    continue;
                }

                $translation = $record['translations'][$locale] ?? null;
                $this->assertNotNull($translation, "{$record['slug']} is missing the {$locale} translation");

                // an untranslated key leaks through as the raw "email.foo.bar" path
                $this->assertStringNotContainsString('email.', $translation['subject'], "{$record['slug']} [{$locale}] subject has an unresolved lang key");
                $this->assertStringNotContainsString('email.', $translation['html_body'], "{$record['slug']} [{$locale}] body has an unresolved lang key");
            }
        }
    }

    public function test_trial_ended_automation_skips_users_who_converted_to_paid(): void
    {
        $template = $this->makeTemplate(['category' => 'transactional']);
        $automation = EmailAutomation::create([
            'key' => 'trial_ended',
            'name' => 'Trial Ended',
            'template_id' => $template->id,
            'enabled' => true,
            'config' => ['window_days' => 3],
        ]);

        $lapsed = $this->makeVerifiedUser([
            'plan' => 'free',
            'trial_started_at' => now()->subDays(16),
            'trial_ends_at' => now()->subDay(),
        ]);

        // Converted mid-trial: activate() clears trial_ends_at precisely so this
        // person is never told their trial ended.
        $converted = $this->makeVerifiedUser([
            'plan' => 'premium',
            'trial_started_at' => now()->subDays(16),
            'trial_ends_at' => null,
        ]);

        $longGone = $this->makeVerifiedUser([
            'plan' => 'free',
            'trial_started_at' => now()->subDays(60),
            'trial_ends_at' => now()->subDays(45),
        ]);

        app(AutomationEngine::class)->run();

        $this->assertEquals(1, EmailMessage::where('user_id', $lapsed->id)->count());
        $this->assertEquals(0, EmailMessage::where('user_id', $converted->id)->count());
        $this->assertEquals(0, EmailMessage::where('user_id', $longGone->id)->count());
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
