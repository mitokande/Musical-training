<?php

namespace Database\Seeders;

use App\Models\EmailAutomation;
use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailCenterSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Welcome',
                'slug' => 'welcome',
                'subject' => 'Welcome to {{app_name}}, {{user_first_name}}! 🎵',
                'preheader' => 'Your musical ear training journey starts here',
                'body' => <<<'HTML'
<h2 style="margin:0 0 16px;color:#111827;">Welcome, {{user_first_name}}! 🎶</h2>
<p>We're thrilled to have you at <strong>{{app_name}}</strong> — your new home for ear training and music theory practice.</p>
<p>Here's how to get the most out of your first week:</p>
<ul style="color:#374151;line-height:1.8;">
    <li><strong>Take the placement quiz</strong> so we can tailor your Learning Path.</li>
    <li><strong>Try a practice session</strong> — start with Single Note or Melodic Intervals.</li>
    <li><strong>Set a daily goal</strong> — 10 minutes a day works wonders.</li>
</ul>
<p style="text-align:center;margin:28px 0;">
    <a href="{{app_url}}/dashboard" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 32px;border-radius:10px;font-weight:600;display:inline-block;">Start Training</a>
</p>
<p>Questions? Just reply to this email — we read everything.</p>
HTML,
            ],
            [
                'name' => 'First Exercise Reminder',
                'slug' => 'first-exercise-reminder',
                'subject' => '{{user_first_name}}, your first exercise is waiting 🎧',
                'preheader' => 'It only takes 5 minutes to train your ear',
                'body' => <<<'HTML'
<h2 style="margin:0 0 16px;color:#111827;">Ready for your first session?</h2>
<p>Hi {{user_first_name}},</p>
<p>You created your {{app_name}} account a couple of days ago, but you haven't tried an exercise yet. The first one takes less than five minutes — and it's the most important one.</p>
<p style="text-align:center;margin:28px 0;">
    <a href="{{app_url}}/exercise-setup" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 32px;border-radius:10px;font-weight:600;display:inline-block;">Try Your First Exercise</a>
</p>
<p>Not sure where to start? <a href="{{app_url}}/learn" style="color:#4f46e5;">The Learning Path</a> guides you step by step.</p>
HTML,
            ],
            [
                'name' => 'Learning Path Reminder',
                'slug' => 'learning-path-reminder',
                'subject' => 'Your Learning Path misses you, {{user_first_name}} 🎼',
                'preheader' => 'Pick up right where you left off',
                'body' => <<<'HTML'
<h2 style="margin:0 0 16px;color:#111827;">Pick up where you left off</h2>
<p>Hi {{user_first_name}},</p>
<p>Your ear was getting sharper — don't let the progress fade! Your Learning Path is exactly where you left it, ready when you are.</p>
<p style="text-align:center;margin:28px 0;">
    <a href="{{app_url}}/learn" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 32px;border-radius:10px;font-weight:600;display:inline-block;">Continue Learning Path</a>
</p>
<p>Even one short session keeps the momentum going.</p>
HTML,
            ],
            [
                'name' => 'Weekly Progress Summary',
                'slug' => 'weekly-progress',
                'subject' => 'Your week at {{app_name}}: {{weekly_sessions}} sessions 📈',
                'preheader' => 'Your weekly ear training recap',
                'body' => <<<'HTML'
<h2 style="margin:0 0 16px;color:#111827;">Your week in review 📈</h2>
<p>Hi {{user_first_name}}, here's what you accomplished this week:</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:20px 0;">
    <tr>
        <td align="center" style="padding:16px;background:#eef2ff;border-radius:12px;">
            <div style="font-size:28px;font-weight:700;color:#4f46e5;">{{weekly_sessions}}</div>
            <div style="font-size:13px;color:#6b7280;">Sessions</div>
        </td>
        <td style="width:12px;"></td>
        <td align="center" style="padding:16px;background:#ecfdf5;border-radius:12px;">
            <div style="font-size:28px;font-weight:700;color:#059669;">{{weekly_accuracy}}</div>
            <div style="font-size:13px;color:#6b7280;">Accuracy</div>
        </td>
        <td style="width:12px;"></td>
        <td align="center" style="padding:16px;background:#fff7ed;border-radius:12px;">
            <div style="font-size:28px;font-weight:700;color:#ea580c;">{{weekly_minutes}}</div>
            <div style="font-size:13px;color:#6b7280;">Minutes</div>
        </td>
    </tr>
</table>
<p style="text-align:center;margin:28px 0;">
    <a href="{{app_url}}/dashboard" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 32px;border-radius:10px;font-weight:600;display:inline-block;">Keep It Going</a>
</p>
HTML,
            ],
            [
                'name' => 'Re-engagement',
                'slug' => 're-engagement',
                'subject' => 'We saved your progress, {{user_first_name}} 🎹',
                'preheader' => 'Your ear training progress is safe — come back anytime',
                'body' => <<<'HTML'
<h2 style="margin:0 0 16px;color:#111827;">Your progress is safe with us</h2>
<p>Hi {{user_first_name}},</p>
<p>It's been a while since your last practice session at {{app_name}}. Your stats, streaks and Learning Path progress are all saved — jump back in whenever you're ready.</p>
<p style="text-align:center;margin:28px 0;">
    <a href="{{app_url}}/dashboard" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 32px;border-radius:10px;font-weight:600;display:inline-block;">Resume Training</a>
</p>
<p>Five minutes today beats an hour someday.</p>
HTML,
            ],
            [
                'name' => 'Premium Upsell',
                'slug' => 'premium-upsell',
                'subject' => 'You outgrew the free plan, {{user_first_name}} ⭐',
                'preheader' => 'Unlimited exercises, AI mode and more with Premium',
                'body' => <<<'HTML'
<h2 style="margin:0 0 16px;color:#111827;">You're putting in the work 👏</h2>
<p>Hi {{user_first_name}},</p>
<p>You've been practicing consistently — that's exactly how ears get trained. With <strong>{{app_name}} Premium</strong> you unlock:</p>
<ul style="color:#374151;line-height:1.8;">
    <li><strong>Unlimited daily exercises</strong> — no more 3-per-day limits</li>
    <li><strong>AI-assisted practice</strong> tailored to your weak spots</li>
    <li><strong>Unlimited saved templates</strong> for your favorite drills</li>
</ul>
<p style="text-align:center;margin:28px 0;">
    <a href="{{app_url}}/plans" style="background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 32px;border-radius:10px;font-weight:600;display:inline-block;">See Premium Plans</a>
</p>
HTML,
            ],
        ];

        foreach ($templates as $data) {
            EmailTemplate::withTrashed()->firstOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'subject' => $data['subject'],
                    'preheader' => $data['preheader'],
                    'html_body' => $this->wrapLayout($data['body'], $data['preheader']),
                    'category' => 'marketing',
                    'is_active' => true,
                    'variables' => ['user_first_name', 'user_name', 'user_email', 'app_name', 'app_url', 'unsubscribe_url'],
                ]
            );
        }

        $automations = [
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
            ['key' => 'premium_upsell', 'name' => 'Premium Upsell', 'template' => 'premium-upsell', 'enabled' => false,
                'description' => 'Sent to engaged free users (10+ sessions, account 14+ days old). Repeats at most every 30 days.',
                'config' => ['min_account_days' => 14, 'min_sessions' => 10, 'cooldown_days' => 30]],
        ];

        foreach ($automations as $data) {
            EmailAutomation::firstOrCreate(
                ['key' => $data['key']],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'template_id' => EmailTemplate::where('slug', $data['template'])->value('id'),
                    'enabled' => $data['enabled'],
                    'config' => $data['config'],
                ]
            );
        }
    }

    protected function wrapLayout(string $content, string $preheader = ''): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin:0;padding:0;background:#f3f4f6;">
<div style="display:none;max-height:0;overflow:hidden;">{$preheader}</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:24px 0;">
<tr><td align="center">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">
<tr><td style="padding:24px;text-align:center;">
    <a href="{{app_url}}" style="font-size:22px;font-weight:800;color:#4f46e5;text-decoration:none;font-family:-apple-system,'Segoe UI',sans-serif;">🎵 {{app_name}}</a>
</td></tr>
<tr><td style="background:#ffffff;border-radius:16px;padding:36px;font-family:-apple-system,'Segoe UI',sans-serif;font-size:15px;color:#374151;line-height:1.7;">
{$content}
</td></tr>
<tr><td style="padding:24px;text-align:center;font-family:-apple-system,'Segoe UI',sans-serif;font-size:12px;color:#9ca3af;">
    © {{current_year}} {{app_name}} · <a href="{{app_url}}" style="color:#9ca3af;">harmoniva.app</a><br>
    <a href="{{unsubscribe_url}}" style="color:#9ca3af;">Unsubscribe</a> from marketing emails
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
HTML;
    }
}
