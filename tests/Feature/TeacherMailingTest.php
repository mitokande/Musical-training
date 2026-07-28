<?php

namespace Tests\Feature;

use App\Models\EmailAutomation;
use App\Models\EmailMessage;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Services\EmailCenter\AutomationEngine;
use App\Services\EmailCenter\EmailDispatchService;
use App\Services\EmailCenter\TemplateRenderer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class TeacherMailingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    protected function user(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'email_verified_at' => now(), 'role' => 'user', 'plan' => 'free',
        ], $overrides));
    }

    protected function template(string $slug, array $overrides = []): EmailTemplate
    {
        return EmailTemplate::create(array_merge([
            'name' => $slug, 'slug' => $slug, 'subject' => 'S {{user_first_name}}',
            'html_body' => "<html><body>{$slug} {{premium_url}}</body></html>",
            'category' => 'marketing', 'is_active' => true,
        ], $overrides));
    }

    // --- Audience resolution ------------------------------------------------

    public function test_email_audience_reflects_role_and_teacher_account(): void
    {
        $this->assertSame('student', $this->user()->emailAudience());
        $this->assertSame('teacher', $this->user(['role' => 'teacher'])->emailAudience());
        $this->assertSame('school', $this->user(['role' => 'school'])->emailAudience());
    }

    // --- Automation template variants --------------------------------------

    public function test_welcome_uses_audience_variant_templates(): void
    {
        $base = $this->template('welcome');
        $teacherTpl = $this->template('welcome-teacher');
        $schoolTpl = $this->template('welcome-school');

        $automation = EmailAutomation::create([
            'key' => 'welcome', 'name' => 'Welcome', 'template_id' => $base->id,
            'enabled' => true, 'config' => ['delay_hours' => 1],
        ]);

        $student = $this->user(['created_at' => now()->subHours(2)]);
        $teacher = $this->user(['role' => 'teacher', 'created_at' => now()->subHours(2)]);
        $school = $this->user(['role' => 'school', 'created_at' => now()->subHours(2)]);

        app(AutomationEngine::class)->run();

        $this->assertSame($base->id, EmailMessage::where('user_id', $student->id)->value('template_id'));
        $this->assertSame($teacherTpl->id, EmailMessage::where('user_id', $teacher->id)->value('template_id'));
        $this->assertSame($schoolTpl->id, EmailMessage::where('user_id', $school->id)->value('template_id'));
    }

    public function test_variant_falls_back_to_base_when_inactive(): void
    {
        $base = $this->template('welcome');
        $this->template('welcome-teacher', ['is_active' => false]); // inactive → ignored

        $automation = EmailAutomation::create([
            'key' => 'welcome', 'name' => 'Welcome', 'template_id' => $base->id,
            'enabled' => true, 'config' => ['delay_hours' => 1],
        ]);

        $teacher = $this->user(['role' => 'teacher', 'created_at' => now()->subHours(2)]);

        app(AutomationEngine::class)->run();

        $this->assertSame($base->id, EmailMessage::where('user_id', $teacher->id)->value('template_id'));
    }

    public function test_premium_intro_now_reaches_teachers_and_schools(): void
    {
        $base = $this->template('premium-intro');
        $teacherTpl = $this->template('premium-intro-teacher');

        EmailAutomation::create([
            'key' => 'premium_intro', 'name' => 'Premium Intro', 'template_id' => $base->id,
            'enabled' => true, 'config' => ['min_account_days' => 3],
        ]);

        $teacher = $this->user(['role' => 'teacher', 'created_at' => now()->subDays(4)]);

        app(AutomationEngine::class)->run();

        $this->assertSame($teacherTpl->id, EmailMessage::where('user_id', $teacher->id)->value('template_id'));
    }

    // --- Audience-aware links ----------------------------------------------

    public function test_premium_url_targets_teacher_pricing(): void
    {
        $teacher = $this->user(['role' => 'teacher']);
        $message = app(EmailDispatchService::class)->dispatch($teacher, 'automation', $this->template('welcome-teacher'));

        $vars = app(TemplateRenderer::class)->variables($message->fresh()->load('user'));

        $this->assertStringContainsString('/pricing/teachers-and-schools', $vars['premium_url']);
        $this->assertStringContainsString('/teachers', $vars['guide_url']);
    }

    public function test_student_premium_url_targets_general_pricing(): void
    {
        $student = $this->user();
        $message = app(EmailDispatchService::class)->dispatch($student, 'automation', $this->template('welcome'));

        $vars = app(TemplateRenderer::class)->variables($message->fresh()->load('user'));

        $this->assertStringContainsString('/pricing', $vars['premium_url']);
        $this->assertStringNotContainsString('teachers-and-schools', $vars['premium_url']);
    }

    // --- Preferences page teaching topic -----------------------------------

    public function test_teaching_topic_shown_only_to_teacher_school(): void
    {
        $teacher = $this->user(['role' => 'teacher']);
        $message = app(EmailDispatchService::class)->dispatch($teacher, 'automation', $this->template('welcome-teacher'));
        $url = URL::signedRoute('email.preferences', ['token' => $message->tracking_token]);
        $this->get($url)->assertOk()->assertSee('Teaching & school activity');

        $student = $this->user();
        $sMsg = app(EmailDispatchService::class)->dispatch($student, 'automation', $this->template('welcome'));
        $sUrl = URL::signedRoute('email.preferences', ['token' => $sMsg->tracking_token]);
        $this->get($sUrl)->assertOk()->assertDontSee('Teaching & school activity');
    }
}
