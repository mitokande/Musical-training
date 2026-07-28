<?php

namespace Tests\Feature;

use App\Models\EmailTemplate;
use App\Models\User;
use Tests\TestCase;

class NotificationLocalizationTest extends TestCase
{
    public function test_user_exposes_preferred_locale(): void
    {
        $user = new User(['locale' => 'tr']);
        $this->assertSame('tr', $user->preferredLocale());
    }

    public function test_notification_keys_translated_in_every_locale(): void
    {
        // Representative keys across appointments, verification and invitations.
        $keys = [
            'notifications.appointment.status_subject',
            'notifications.appointment.status.confirmed',
            'notifications.appointment.request_subject',
            'notifications.appointment.review',
            'notifications.verify.subject',
            'notifications.verify.action',
            'notifications.invite.teacher_subject',
            'notifications.invite.school_subject',
            'notifications.invite.accept',
            'notifications.invite.thanks',
        ];

        foreach (EmailTemplate::LOCALES as $locale) {
            foreach ($keys as $key) {
                $value = trans($key, [], $locale);
                $this->assertNotSame($key, $value, "Missing $key for locale $locale");
            }
        }
    }

    public function test_placeholders_survive_translation(): void
    {
        foreach (EmailTemplate::LOCALES as $locale) {
            $line = trans('notifications.appointment.request_line', ['name' => 'Ada', 'when' => 'Jul 28'], $locale);
            $this->assertStringContainsString('Ada', $line);
            $this->assertStringContainsString('Jul 28', $line);
        }
    }
}
