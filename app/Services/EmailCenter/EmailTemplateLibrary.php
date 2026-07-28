<?php

namespace App\Services\EmailCenter;

use App\Models\EmailTemplate;

/**
 * Canonical source for the Harmoniva system email templates and the layout
 * that wraps them. Shared by EmailCenterSeeder (fresh installs, firstOrCreate)
 * and the `email:sync-templates` command (pushes redesigns to an existing DB).
 *
 * Every user-facing string comes from resources/lang/{locale}/email.php, so
 * templateRecords() emits an English base plus a translations map for the
 * other locales (EmailTemplate::LOCALES). Everything is email-client safe:
 * table layout, inline styles, emoji icon badges. Brand palette: purple
 * #9333ea → #7c3aed. {{placeholders}} are substituted per recipient by
 * TemplateRenderer — never resolve them here.
 */
class EmailTemplateLibrary
{
    /** Standard variables every template exposes. */
    private const BASE_VARS = [
        'user_first_name', 'user_name', 'user_email', 'app_name', 'app_url',
        'dashboard_url', 'guide_url', 'premium_url', 'unsubscribe_url', 'preferences_url', 'current_year',
    ];

    /** A localised string from the email lang file. */
    private function t(string $key, string $locale): string
    {
        return trans('email.'.$key, [], $locale);
    }

    /**
     * Localised template definitions for one locale.
     *
     * @return array<int, array<string, mixed>>
     */
    public function templates(string $locale = 'en'): array
    {
        return [
            $this->def('Welcome', 'welcome', 'welcome', 'marketing', $this->welcomeBody($locale), $locale),
            $this->def('First Exercise Reminder', 'first-exercise-reminder', 'first_exercise', 'marketing', $this->firstExerciseBody($locale), $locale),
            $this->def('Learning Path Reminder', 'learning-path-reminder', 'learning_path', 'marketing', $this->learningPathBody($locale), $locale),
            $this->def('Weekly Progress Summary', 'weekly-progress', 'weekly_progress', 'marketing', $this->weeklyProgressBody($locale), $locale, ['weekly_sessions', 'weekly_accuracy', 'weekly_minutes']),
            $this->def('Re-engagement', 're-engagement', 're_engagement', 'marketing', $this->reEngagementBody($locale), $locale),
            $this->def('Premium Intro', 'premium-intro', 'premium_intro', 'marketing', $this->premiumIntroBody($locale), $locale),
            $this->def('Premium Upsell', 'premium-upsell', 'premium_upsell', 'marketing', $this->premiumUpsellBody($locale), $locale),
            $this->def('Trial Ending', 'trial-ending', 'trial_ending', 'transactional', $this->trialEndingBody($locale), $locale, ['trial_days_left', 'trial_ends_on']),
            $this->def('Trial Ended', 'trial-ended', 'trial_ended', 'transactional', $this->trialEndedBody($locale), $locale, ['trial_days_left', 'trial_ends_on']),

            // Teacher audience variants
            $this->def('Welcome — Teacher', 'welcome-teacher', 'welcome_teacher', 'marketing', $this->welcomeTeacherBody($locale), $locale),
            $this->def('Premium Intro — Teacher', 'premium-intro-teacher', 'premium_intro_teacher', 'marketing', $this->premiumIntroTeacherBody($locale), $locale),
            $this->def('Trial Ending — Teacher', 'trial-ending-teacher', 'trial_ending_teacher', 'transactional', $this->trialEndingTeacherBody($locale), $locale, ['trial_days_left', 'trial_ends_on']),
            $this->def('Trial Ended — Teacher', 'trial-ended-teacher', 'trial_ended_teacher', 'transactional', $this->trialEndedTeacherBody($locale), $locale, ['trial_days_left', 'trial_ends_on']),

            // School audience variants
            $this->def('Welcome — School', 'welcome-school', 'welcome_school', 'marketing', $this->welcomeSchoolBody($locale), $locale),
            $this->def('Premium Intro — School', 'premium-intro-school', 'premium_intro_school', 'marketing', $this->premiumIntroSchoolBody($locale), $locale),
            $this->def('Trial Ending — School', 'trial-ending-school', 'trial_ending_school', 'transactional', $this->trialEndingSchoolBody($locale), $locale, ['trial_days_left', 'trial_ends_on']),
            $this->def('Trial Ended — School', 'trial-ended-school', 'trial_ended_school', 'transactional', $this->trialEndedSchoolBody($locale), $locale, ['trial_days_left', 'trial_ends_on']),
        ];
    }

    /**
     * @param  array<int, string>  $extraVars
     * @return array<string, mixed>
     */
    private function def(string $name, string $slug, string $key, string $category, string $body, string $locale, array $extraVars = []): array
    {
        return [
            'name' => $name,
            'slug' => $slug,
            'subject' => $this->t($key.'.subject', $locale),
            'preheader' => $this->t($key.'.preheader', $locale),
            'category' => $category,
            'body' => $body,
            'variables' => $extraVars,
        ];
    }

    /**
     * Persistence-ready rows: English base columns + a per-locale translations
     * map for every other supported locale.
     *
     * @return array<int, array<string, mixed>>
     */
    public function templateRecords(): array
    {
        $byLocale = [];
        foreach (EmailTemplate::LOCALES as $loc) {
            foreach ($this->templates($loc) as $t) {
                $byLocale[$loc][$t['slug']] = $t;
            }
        }

        $records = [];
        foreach ($byLocale['en'] as $slug => $en) {
            $translations = [];
            foreach (EmailTemplate::LOCALES as $loc) {
                if ($loc === 'en') {
                    continue;
                }
                $t = $byLocale[$loc][$slug];
                $translations[$loc] = [
                    'subject' => $t['subject'],
                    'preheader' => $t['preheader'],
                    'html_body' => $this->layout($t['body'], $t['preheader'], $loc),
                ];
            }

            $records[] = [
                'name' => $en['name'],
                'slug' => $slug,
                'subject' => $en['subject'],
                'preheader' => $en['preheader'],
                'category' => $en['category'] ?? 'marketing',
                'html_body' => $this->layout($en['body'], $en['preheader'], 'en'),
                'variables' => array_values(array_unique(array_merge(self::BASE_VARS, $en['variables'] ?? []))),
                'translations' => $translations,
            ];
        }

        return $records;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function automations(): array
    {
        return [
            ['key' => 'welcome', 'name' => 'Welcome Email', 'template' => 'welcome', 'enabled' => false,
                'description' => 'Sent once, ~1 hour after signup (verified accounts only).',
                'config' => ['delay_hours' => 1]],
            ['key' => 'first_exercise_reminder', 'name' => 'First Exercise Reminder', 'template' => 'first-exercise-reminder', 'enabled' => false,
                'description' => 'Sent once to users who signed up 2+ days ago but never completed an exercise.',
                'config' => ['delay_days' => 2]],
            ['key' => 'learning_path_reminder', 'name' => 'Learning Path Reminder', 'template' => 'learning-path-reminder', 'enabled' => false,
                'description' => 'Nudges users with Learning Path progress who have been inactive for 7 days. Repeats at most every 14 days.',
                'config' => ['inactive_days' => 7, 'cooldown_days' => 14]],
            ['key' => 'weekly_progress', 'name' => 'Weekly Progress Summary', 'template' => 'weekly-progress', 'enabled' => false,
                'description' => 'Weekly digest with session count, accuracy and minutes for users active in the last 7 days.',
                'config' => []],
            ['key' => 're_engagement', 'name' => 'Re-engagement', 'template' => 're-engagement', 'enabled' => false,
                'description' => 'Win-back email after 30 days of inactivity. Repeats at most every 60 days.',
                'config' => ['inactive_days' => 30, 'cooldown_days' => 60]],
            ['key' => 'premium_intro', 'name' => 'Premium Intro', 'template' => 'premium-intro', 'enabled' => false,
                'description' => 'Introduces Premium to every free user ~3 days after signup. Sent once per account.',
                'config' => ['min_account_days' => 3]],
            ['key' => 'premium_upsell', 'name' => 'Premium Upsell', 'template' => 'premium-upsell', 'enabled' => false,
                'description' => 'Sent to engaged free users (10+ sessions, account 14+ days old). Repeats at most every 30 days.',
                'config' => ['min_account_days' => 14, 'min_sessions' => 10, 'cooldown_days' => 30]],
            ['key' => 'trial_ending', 'name' => 'Trial Ending Reminder', 'template' => 'trial-ending', 'enabled' => false,
                'description' => 'Warns users whose free Premium trial ends within 3 days. Transactional; sent once per granted trial; reaches users, teachers and schools.',
                'config' => ['lead_days' => 3]],
            ['key' => 'trial_ended', 'name' => 'Trial Ended', 'template' => 'trial-ended', 'enabled' => false,
                'description' => 'Sent after a free trial lapses and the account drops back to free (never to users who converted to paid). Transactional; once per granted trial.',
                'config' => ['window_days' => 3]],
        ];
    }

    // --- Reusable building blocks -------------------------------------------

    /** A branded CTA button with an optional slogan line under it. */
    public function button(string $url, string $label, ?string $slogan = null): string
    {
        $sloganHtml = $slogan
            ? '<div style="font-size:12px;color:#9ca3af;margin-top:10px;">'.$slogan.'</div>'
            : '';

        return <<<HTML
<div style="text-align:center;margin:30px 0;">
    <a href="{$url}" style="background:linear-gradient(135deg,#9333ea 0%,#7c3aed 100%);color:#ffffff;text-decoration:none;padding:15px 38px;border-radius:12px;font-weight:700;font-size:15px;display:inline-block;box-shadow:0 8px 20px rgba(124,58,237,0.28);">{$label}</a>
    {$sloganHtml}
</div>
HTML;
    }

    /** A feature card row: coloured emoji badge + title + description. */
    public function feature(string $emoji, string $title, string $desc, string $bg = '#f3e8ff'): string
    {
        return <<<HTML
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 12px;">
<tr>
    <td width="52" valign="top" style="padding-right:14px;">
        <div style="width:44px;height:44px;border-radius:12px;background:{$bg};text-align:center;line-height:44px;font-size:22px;">{$emoji}</div>
    </td>
    <td valign="top">
        <div style="font-weight:700;color:#111827;font-size:15px;margin:0 0 2px;">{$title}</div>
        <div style="color:#6b7280;font-size:13.5px;line-height:1.55;">{$desc}</div>
    </td>
</tr>
</table>
HTML;
    }

    /** A soft promo card ("learn more" block). */
    public function promoBlock(string $emoji, string $title, string $slogan, string $label, string $url): string
    {
        return <<<HTML
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0;background:#faf5ff;border:1px solid #ecdcff;border-radius:14px;">
<tr><td style="padding:20px 22px;">
    <div style="font-size:26px;line-height:1;margin-bottom:8px;">{$emoji}</div>
    <div style="font-weight:800;color:#6b21a8;font-size:16px;margin:0 0 4px;">{$title}</div>
    <div style="color:#7c3aed;font-size:13.5px;line-height:1.6;margin-bottom:14px;">{$slogan}</div>
    <a href="{$url}" style="background:#ffffff;border:1.5px solid #9333ea;color:#7c3aed;text-decoration:none;padding:10px 22px;border-radius:10px;font-weight:700;font-size:13.5px;display:inline-block;">{$label}</a>
</td></tr>
</table>
HTML;
    }

    /** The slogan-led "user guide" promo used on the student welcome. */
    private function guideBlock(string $locale): string
    {
        return $this->promoBlock('🧭', $this->t('guide_block.title', $locale), $this->t('guide_block.slogan', $locale), $this->t('guide_block.button', $locale), '{{guide_url}}');
    }

    /** Localised greeting line. */
    private function hi(string $locale): string
    {
        return '<p>'.$this->t('hi', $locale).'</p>';
    }

    // --- Student bodies -----------------------------------------------------

    private function welcomeBody(string $l): string
    {
        $features = $this->feature('🎯', $this->t('welcome.f1_t', $l), $this->t('welcome.f1_d', $l), '#f3e8ff')
            .$this->feature('🎧', $this->t('welcome.f2_t', $l), $this->t('welcome.f2_d', $l), '#e0f2fe')
            .$this->feature('📈', $this->t('welcome.f3_t', $l), $this->t('welcome.f3_d', $l), '#dcfce7')
            .$this->feature('🤖', $this->t('welcome.f4_t', $l), $this->t('welcome.f4_d', $l), '#fef3c7');

        $button = $this->button('{{dashboard_url}}', $this->t('welcome.btn', $l), $this->t('welcome.btn_sub', $l));
        $guide = $this->guideBlock($l);
        $title = $this->t('welcome.title', $l);
        $subtitle = $this->t('welcome.subtitle', $l);
        $ps = $this->t('welcome.ps', $l);

        return <<<HTML
<div style="text-align:center;margin-bottom:8px;">
    <div style="display:inline-block;width:64px;height:64px;border-radius:18px;background:linear-gradient(135deg,#9333ea,#7c3aed);line-height:64px;font-size:32px;">🎶</div>
</div>
<h1 style="margin:6px 0 6px;color:#111827;font-size:24px;text-align:center;">{$title}</h1>
<p style="text-align:center;color:#6b7280;margin:0 0 26px;">{$subtitle}</p>
{$features}
{$button}
{$guide}
<p style="text-align:center;color:#9ca3af;font-size:13px;margin-top:24px;">{$ps}</p>
HTML;
    }

    private function firstExerciseBody(string $l): string
    {
        $button = $this->button('{{app_url}}/exercise-setup', $this->t('first_exercise.btn', $l), $this->t('first_exercise.btn_sub', $l));
        $title = $this->t('first_exercise.title', $l);
        $hi = $this->hi($l);
        $p1 = $this->t('first_exercise.p1', $l);
        $p2 = $this->t('first_exercise.p2', $l);

        return <<<HTML
<div style="text-align:center;font-size:40px;margin-bottom:6px;">🎧</div>
<h1 style="margin:0 0 14px;color:#111827;font-size:22px;text-align:center;">{$title}</h1>
{$hi}
<p>{$p1}</p>
{$button}
<p style="color:#6b7280;font-size:13.5px;">{$p2}</p>
HTML;
    }

    private function learningPathBody(string $l): string
    {
        $button = $this->button('{{app_url}}/learn', $this->t('learning_path.btn', $l), $this->t('learning_path.btn_sub', $l));
        $title = $this->t('learning_path.title', $l);
        $hi = $this->hi($l);
        $p1 = $this->t('learning_path.p1', $l);
        $p2 = $this->t('learning_path.p2', $l);

        return <<<HTML
<div style="text-align:center;font-size:40px;margin-bottom:6px;">🎼</div>
<h1 style="margin:0 0 14px;color:#111827;font-size:22px;text-align:center;">{$title}</h1>
{$hi}
<p>{$p1}</p>
{$button}
<p style="color:#6b7280;font-size:13.5px;text-align:center;">{$p2}</p>
HTML;
    }

    private function weeklyProgressBody(string $l): string
    {
        $button = $this->button('{{dashboard_url}}', $this->t('weekly_progress.btn', $l), $this->t('weekly_progress.btn_sub', $l));
        $title = $this->t('weekly_progress.title', $l);
        $subtitle = $this->t('weekly_progress.subtitle', $l);
        $sessions = $this->t('weekly_progress.sessions', $l);
        $accuracy = $this->t('weekly_progress.accuracy', $l);
        $minutes = $this->t('weekly_progress.minutes', $l);

        return <<<HTML
<div style="text-align:center;font-size:40px;margin-bottom:6px;">📈</div>
<h1 style="margin:0 0 6px;color:#111827;font-size:22px;text-align:center;">{$title}</h1>
<p style="text-align:center;color:#6b7280;margin:0 0 20px;">{$subtitle}</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 8px;">
    <tr>
        <td align="center" width="33%" style="padding:18px 8px;background:#f5f3ff;border-radius:14px;">
            <div style="font-size:22px;margin-bottom:4px;">🎯</div>
            <div style="font-size:26px;font-weight:800;color:#7c3aed;">{{weekly_sessions}}</div>
            <div style="font-size:12px;color:#6b7280;">{$sessions}</div>
        </td>
        <td style="width:10px;"></td>
        <td align="center" width="33%" style="padding:18px 8px;background:#ecfdf5;border-radius:14px;">
            <div style="font-size:22px;margin-bottom:4px;">✅</div>
            <div style="font-size:26px;font-weight:800;color:#059669;">{{weekly_accuracy}}</div>
            <div style="font-size:12px;color:#6b7280;">{$accuracy}</div>
        </td>
        <td style="width:10px;"></td>
        <td align="center" width="33%" style="padding:18px 8px;background:#fff7ed;border-radius:14px;">
            <div style="font-size:22px;margin-bottom:4px;">⏱️</div>
            <div style="font-size:26px;font-weight:800;color:#ea580c;">{{weekly_minutes}}</div>
            <div style="font-size:12px;color:#6b7280;">{$minutes}</div>
        </td>
    </tr>
</table>
{$button}
HTML;
    }

    private function reEngagementBody(string $l): string
    {
        $button = $this->button('{{dashboard_url}}', $this->t('re_engagement.btn', $l), $this->t('re_engagement.btn_sub', $l));
        $title = $this->t('re_engagement.title', $l);
        $hi = $this->hi($l);
        $p1 = $this->t('re_engagement.p1', $l);

        return <<<HTML
<div style="text-align:center;font-size:40px;margin-bottom:6px;">🎹</div>
<h1 style="margin:0 0 14px;color:#111827;font-size:22px;text-align:center;">{$title}</h1>
{$hi}
<p>{$p1}</p>
{$button}
HTML;
    }

    private function premiumIntroBody(string $l): string
    {
        $features = $this->feature('♾️', $this->t('premium_intro.f1_t', $l), $this->t('premium_intro.f1_d', $l), '#f3e8ff')
            .$this->feature('🤖', $this->t('premium_intro.f2_t', $l), $this->t('premium_intro.f2_d', $l), '#fef3c7')
            .$this->feature('🗂️', $this->t('premium_intro.f3_t', $l), $this->t('premium_intro.f3_d', $l), '#e0f2fe')
            .$this->feature('🎼', $this->t('premium_intro.f4_t', $l), $this->t('premium_intro.f4_d', $l), '#dcfce7');

        $button = $this->button('{{premium_url}}', $this->t('premium_intro.btn', $l), $this->t('premium_intro.btn_sub', $l));
        $badge = $this->t('premium_intro.badge', $l);
        $title = $this->t('premium_intro.title', $l);
        $subtitle = $this->t('premium_intro.subtitle', $l);
        $p2 = $this->t('premium_intro.p2', $l);

        return <<<HTML
<div style="text-align:center;margin-bottom:8px;">
    <span style="display:inline-block;background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;font-weight:800;font-size:12px;letter-spacing:.06em;padding:6px 14px;border-radius:999px;">{$badge}</span>
</div>
<h1 style="margin:8px 0 6px;color:#111827;font-size:23px;text-align:center;">{$title}</h1>
<p style="text-align:center;color:#6b7280;margin:0 0 24px;">{$subtitle}</p>
{$features}
{$button}
<p style="color:#6b7280;font-size:13.5px;text-align:center;">{$p2}</p>
HTML;
    }

    private function premiumUpsellBody(string $l): string
    {
        $features = $this->feature('♾️', $this->t('premium_upsell.f1_t', $l), $this->t('premium_upsell.f1_d', $l), '#f3e8ff')
            .$this->feature('🤖', $this->t('premium_upsell.f2_t', $l), $this->t('premium_upsell.f2_d', $l), '#fef3c7')
            .$this->feature('🗂️', $this->t('premium_upsell.f3_t', $l), $this->t('premium_upsell.f3_d', $l), '#e0f2fe');

        $button = $this->button('{{premium_url}}', $this->t('premium_upsell.btn', $l), $this->t('premium_upsell.btn_sub', $l));
        $title = $this->t('premium_upsell.title', $l);
        $subtitle = $this->t('premium_upsell.subtitle', $l);

        return <<<HTML
<div style="text-align:center;font-size:40px;margin-bottom:6px;">👏</div>
<h1 style="margin:0 0 6px;color:#111827;font-size:22px;text-align:center;">{$title}</h1>
<p style="text-align:center;color:#6b7280;margin:0 0 22px;">{$subtitle}</p>
{$features}
{$button}
HTML;
    }

    private function trialEndingBody(string $l): string
    {
        $button = $this->button('{{app_url}}/account/billing', $this->t('trial_ending.btn', $l), $this->t('trial_ending.btn_sub', $l));

        return $this->trialLayout('⏳', $this->t('trial_ending.title', $l), $l, [
            $this->t('trial_ending.p1', $l), $this->t('trial_ending.p2', $l), $this->t('trial_ending.p3', $l),
        ], $button);
    }

    private function trialEndedBody(string $l): string
    {
        $button = $this->button('{{app_url}}/account/billing', $this->t('trial_ended.btn', $l), $this->t('trial_ended.btn_sub', $l));

        return $this->trialLayout('🙏', $this->t('trial_ended.title', $l), $l, [
            $this->t('trial_ended.p1', $l), $this->t('trial_ended.p2', $l),
        ], $button);
    }

    // --- Teacher bodies -----------------------------------------------------

    private function welcomeTeacherBody(string $l): string
    {
        $features = $this->feature('📝', $this->t('welcome_teacher.f1_t', $l), $this->t('welcome_teacher.f1_d', $l), '#ecfdf5')
            .$this->feature('🗓️', $this->t('welcome_teacher.f2_t', $l), $this->t('welcome_teacher.f2_d', $l), '#e0f2fe')
            .$this->feature('👥', $this->t('welcome_teacher.f3_t', $l), $this->t('welcome_teacher.f3_d', $l), '#f3e8ff')
            .$this->feature('✍️', $this->t('welcome_teacher.f4_t', $l), $this->t('welcome_teacher.f4_d', $l), '#fef3c7');

        $button = $this->button('{{dashboard_url}}', $this->t('welcome_teacher.btn', $l), $this->t('welcome_teacher.btn_sub', $l));
        $promo = $this->promoBlock('🧭', $this->t('welcome_teacher.promo_t', $l), $this->t('welcome_teacher.promo_s', $l), $this->t('welcome_teacher.promo_btn', $l), '{{guide_url}}');

        return $this->audienceWelcome('🎓 FOR TEACHERS', 'linear-gradient(135deg,#059669,#0d9488)', $this->t('welcome_teacher.badge', $l), $this->t('welcome_teacher.title', $l), $this->t('welcome_teacher.subtitle', $l), $features, $button, $promo, $this->t('welcome_teacher.ps', $l));
    }

    private function premiumIntroTeacherBody(string $l): string
    {
        $features = $this->feature('📅', $this->t('premium_intro_teacher.f1_t', $l), $this->t('premium_intro_teacher.f1_d', $l), '#ecfdf5')
            .$this->feature('✍️', $this->t('premium_intro_teacher.f2_t', $l), $this->t('premium_intro_teacher.f2_d', $l), '#fef3c7')
            .$this->feature('⭐', $this->t('premium_intro_teacher.f3_t', $l), $this->t('premium_intro_teacher.f3_d', $l), '#f3e8ff')
            .$this->feature('📊', $this->t('premium_intro_teacher.f4_t', $l), $this->t('premium_intro_teacher.f4_d', $l), '#e0f2fe');

        $button = $this->button('{{premium_url}}', $this->t('premium_intro_teacher.btn', $l), $this->t('premium_intro_teacher.btn_sub', $l));

        return $this->audiencePremium($this->t('premium_intro_teacher.badge', $l), $this->t('premium_intro_teacher.title', $l), $this->t('premium_intro_teacher.subtitle', $l), $features, $button, $this->t('premium_intro_teacher.p2', $l));
    }

    private function trialEndingTeacherBody(string $l): string
    {
        $button = $this->button('{{premium_url}}', $this->t('trial_ending_teacher.btn', $l), $this->t('trial_ending_teacher.btn_sub', $l));

        return $this->trialLayout('⏳', $this->t('trial_ending_teacher.title', $l), $l, [
            $this->t('trial_ending_teacher.p1', $l), $this->t('trial_ending_teacher.p2', $l),
        ], $button);
    }

    private function trialEndedTeacherBody(string $l): string
    {
        $button = $this->button('{{premium_url}}', $this->t('trial_ended_teacher.btn', $l), $this->t('trial_ended_teacher.btn_sub', $l));

        return $this->trialLayout('🎓', $this->t('trial_ended_teacher.title', $l), $l, [
            $this->t('trial_ended_teacher.p1', $l), $this->t('trial_ended_teacher.p2', $l),
        ], $button);
    }

    // --- School bodies ------------------------------------------------------

    private function welcomeSchoolBody(string $l): string
    {
        $features = $this->feature('🏫', $this->t('welcome_school.f1_t', $l), $this->t('welcome_school.f1_d', $l), '#eff6ff')
            .$this->feature('👨‍🏫', $this->t('welcome_school.f2_t', $l), $this->t('welcome_school.f2_d', $l), '#f3e8ff')
            .$this->feature('🔗', $this->t('welcome_school.f3_t', $l), $this->t('welcome_school.f3_d', $l), '#ecfdf5')
            .$this->feature('🌟', $this->t('welcome_school.f4_t', $l), $this->t('welcome_school.f4_d', $l), '#fef3c7');

        $button = $this->button('{{dashboard_url}}', $this->t('welcome_school.btn', $l), $this->t('welcome_school.btn_sub', $l));
        $promo = $this->promoBlock('🧭', $this->t('welcome_school.promo_t', $l), $this->t('welcome_school.promo_s', $l), $this->t('welcome_school.promo_btn', $l), '{{guide_url}}');

        return $this->audienceWelcome('🏫 FOR SCHOOLS', 'linear-gradient(135deg,#2563eb,#4f46e5)', $this->t('welcome_school.badge', $l), $this->t('welcome_school.title', $l), $this->t('welcome_school.subtitle', $l), $features, $button, $promo, $this->t('welcome_school.ps', $l));
    }

    private function premiumIntroSchoolBody(string $l): string
    {
        $features = $this->feature('♾️', $this->t('premium_intro_school.f1_t', $l), $this->t('premium_intro_school.f1_d', $l), '#eff6ff')
            .$this->feature('🎨', $this->t('premium_intro_school.f2_t', $l), $this->t('premium_intro_school.f2_d', $l), '#f3e8ff')
            .$this->feature('⭐', $this->t('premium_intro_school.f3_t', $l), $this->t('premium_intro_school.f3_d', $l), '#fef3c7')
            .$this->feature('📊', $this->t('premium_intro_school.f4_t', $l), $this->t('premium_intro_school.f4_d', $l), '#ecfdf5');

        $button = $this->button('{{premium_url}}', $this->t('premium_intro_school.btn', $l), $this->t('premium_intro_school.btn_sub', $l));

        return $this->audiencePremium($this->t('premium_intro_school.badge', $l), $this->t('premium_intro_school.title', $l), $this->t('premium_intro_school.subtitle', $l), $features, $button, $this->t('premium_intro_school.p2', $l));
    }

    private function trialEndingSchoolBody(string $l): string
    {
        $button = $this->button('{{premium_url}}', $this->t('trial_ending_school.btn', $l), $this->t('trial_ending_school.btn_sub', $l));

        return $this->trialLayout('⏳', $this->t('trial_ending_school.title', $l), $l, [
            $this->t('trial_ending_school.p1', $l), $this->t('trial_ending_school.p2', $l),
        ], $button);
    }

    private function trialEndedSchoolBody(string $l): string
    {
        $button = $this->button('{{premium_url}}', $this->t('trial_ended_school.btn', $l), $this->t('trial_ended_school.btn_sub', $l));

        return $this->trialLayout('🏫', $this->t('trial_ended_school.title', $l), $l, [
            $this->t('trial_ended_school.p1', $l), $this->t('trial_ended_school.p2', $l),
        ], $button);
    }

    // --- Shared body scaffolds ----------------------------------------------

    /** @param  array<int, string>  $paragraphs */
    private function trialLayout(string $emoji, string $title, string $locale, array $paragraphs, string $button): string
    {
        $hi = $this->hi($locale);
        $body = $hi;
        foreach ($paragraphs as $p) {
            $body .= "\n<p>{$p}</p>";
        }

        return <<<HTML
<div style="text-align:center;font-size:40px;margin-bottom:6px;">{$emoji}</div>
<h1 style="margin:0 0 14px;color:#111827;font-size:22px;text-align:center;">{$title}</h1>
{$body}
{$button}
HTML;
    }

    private function audienceWelcome(string $tag, string $tagBg, string $badge, string $title, string $subtitle, string $features, string $button, string $promo, string $ps): string
    {
        return <<<HTML
<div style="text-align:center;margin-bottom:8px;">
    <span style="display:inline-block;background:{$tagBg};color:#fff;font-weight:800;font-size:12px;letter-spacing:.06em;padding:6px 14px;border-radius:999px;">{$badge}</span>
</div>
<h1 style="margin:8px 0 6px;color:#111827;font-size:23px;text-align:center;">{$title}</h1>
<p style="text-align:center;color:#6b7280;margin:0 0 24px;">{$subtitle}</p>
{$features}
{$button}
{$promo}
<p style="text-align:center;color:#9ca3af;font-size:13px;margin-top:24px;">{$ps}</p>
HTML;
    }

    private function audiencePremium(string $badge, string $title, string $subtitle, string $features, string $button, string $p2): string
    {
        return <<<HTML
<div style="text-align:center;margin-bottom:8px;">
    <span style="display:inline-block;background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;font-weight:800;font-size:12px;letter-spacing:.06em;padding:6px 14px;border-radius:999px;">{$badge}</span>
</div>
<h1 style="margin:8px 0 6px;color:#111827;font-size:23px;text-align:center;">{$title}</h1>
<p style="text-align:center;color:#6b7280;margin:0 0 24px;">{$subtitle}</p>
{$features}
{$button}
<p style="color:#6b7280;font-size:13.5px;text-align:center;">{$p2}</p>
HTML;
    }

    // --- Layout -------------------------------------------------------------

    /**
     * Wrap body content in the branded, responsive email shell. Footer carries
     * the copyright, unsubscribe link and the email-preferences link — all
     * localised for the recipient.
     */
    public function layout(string $content, string $preheader = '', string $locale = 'en'): string
    {
        $managePrefs = $this->t('footer.manage_prefs', $locale);
        $unsubscribe = $this->t('footer.unsubscribe', $locale);

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="light">
</head>
<body style="margin:0;padding:0;background:#f4f2fb;">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;">{$preheader}</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f2fb;padding:24px 12px;">
<tr><td align="center">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">

<tr><td style="padding:6px 0 20px;text-align:center;">
    <a href="{{app_url}}" style="text-decoration:none;font-size:22px;font-weight:800;color:#7c3aed;letter-spacing:-0.02em;">
        <span style="display:inline-block;width:30px;height:30px;border-radius:9px;background:linear-gradient(135deg,#9333ea,#7c3aed);line-height:30px;text-align:center;font-size:16px;vertical-align:middle;margin-right:8px;">🎵</span>{{app_name}}
    </a>
</td></tr>

<tr><td style="height:6px;background:linear-gradient(90deg,#9333ea,#7c3aed,#c084fc);border-radius:6px 6px 0 0;"></td></tr>
<tr><td style="background:#ffffff;border-radius:0 0 18px 18px;padding:34px 34px 30px;font-size:15px;color:#374151;line-height:1.7;box-shadow:0 10px 30px rgba(79,70,229,0.06);">
{$content}
</td></tr>

<tr><td style="padding:22px 20px 8px;text-align:center;font-size:12px;color:#9ca3af;line-height:1.8;">
    © {{current_year}} {{app_name}} · <a href="{{app_url}}" style="color:#9ca3af;text-decoration:none;">harmoniva.app</a><br>
    <a href="{{preferences_url}}" style="color:#7c3aed;text-decoration:none;font-weight:600;">{$managePrefs}</a>
    &nbsp;·&nbsp;
    <a href="{{unsubscribe_url}}" style="color:#9ca3af;text-decoration:underline;">{$unsubscribe}</a>
</td></tr>

</table>
</td></tr>
</table>
</body>
</html>
HTML;
    }
}
