<?php

namespace Tests\Feature;

use App\Mail\EmailCenterMailable;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Services\EmailCenter\EmailDispatchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailLocalizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    protected function user(string $locale): User
    {
        return User::factory()->create([
            'email_verified_at' => now(), 'role' => 'user', 'plan' => 'free', 'locale' => $locale,
        ]);
    }

    protected function template(array $translations = []): EmailTemplate
    {
        return EmailTemplate::create([
            'name' => 'T', 'slug' => 'tpl-'.uniqid(),
            'subject' => 'English subject {{user_first_name}}',
            'preheader' => 'English preheader',
            'html_body' => '<html><body><p>English body {{user_first_name}}</p></body></html>',
            'category' => 'marketing', 'is_active' => true,
            'translations' => $translations,
        ]);
    }

    public function test_recipient_gets_localized_subject_and_body(): void
    {
        $template = $this->template([
            'tr' => [
                'subject' => 'Türkçe konu {{user_first_name}}',
                'preheader' => 'Türkçe ön izleme',
                'html_body' => '<html><body><p>Türkçe gövde {{user_first_name}}</p></body></html>',
            ],
        ]);
        $user = $this->user('tr');

        app(EmailDispatchService::class)->dispatch($user, 'automation', $template);

        Mail::assertSent(EmailCenterMailable::class, function (EmailCenterMailable $mail) {
            return str_contains($mail->renderedSubject, 'Türkçe konu')
                && str_contains($mail->renderedHtml, 'Türkçe gövde');
        });
    }

    public function test_missing_locale_falls_back_to_english(): void
    {
        // Template has only a Turkish translation; a German recipient falls back.
        $template = $this->template([
            'tr' => ['subject' => 'TR', 'preheader' => 'TR', 'html_body' => '<html><body>TR</body></html>'],
        ]);
        $user = $this->user('de');

        app(EmailDispatchService::class)->dispatch($user, 'automation', $template);

        Mail::assertSent(EmailCenterMailable::class, function (EmailCenterMailable $mail) {
            return str_contains($mail->renderedSubject, 'English subject')
                && str_contains($mail->renderedHtml, 'English body');
        });
    }

    public function test_untranslated_app_locale_gets_english(): void
    {
        // A user whose language is outside the 7 translated email locales
        // (e.g. Japanese) still receives a valid English email.
        $template = $this->template([
            'tr' => ['subject' => 'TR', 'preheader' => 'TR', 'html_body' => '<html><body>TR</body></html>'],
        ]);
        $user = $this->user('ja');

        app(EmailDispatchService::class)->dispatch($user, 'automation', $template);

        Mail::assertSent(EmailCenterMailable::class, fn (EmailCenterMailable $mail) => str_contains($mail->renderedSubject, 'English subject'));
    }

    public function test_localized_helper_falls_back_per_field(): void
    {
        $template = $this->template(['tr' => ['subject' => 'TR konu']]); // only subject translated

        $tr = $template->localized('tr');
        $this->assertSame('TR konu', $tr['subject']);
        // untranslated fields fall back to the English base
        $this->assertSame('English preheader', $tr['preheader']);
        $this->assertStringContainsString('English body', $tr['html_body']);
    }

    public function test_sync_command_populates_all_locale_translations(): void
    {
        $this->artisan('email:sync-templates')->assertSuccessful();

        $welcome = EmailTemplate::where('slug', 'welcome')->first();
        $this->assertNotNull($welcome->translations);
        $this->assertArrayHasKey('tr', $welcome->translations);
        $this->assertArrayHasKey('de', $welcome->translations);
        $this->assertStringContainsString('hoş geldin', $welcome->translations['tr']['subject']);
    }
}
