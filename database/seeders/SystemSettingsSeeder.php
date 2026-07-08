<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    /**
     * Seed the settings the application actually consumes so the admin
     * Settings page has something real to manage. Existing values are kept.
     */
    public function run(): void
    {
        $defaults = [
            // AI Coach (read by AiCoachController / AiChatController / AIController)
            ['key' => 'ai_model',          'value' => 'gpt-4.1-mini',    'type' => 'string',  'group' => 'ai_coach', 'description' => 'OpenAI model used for AI Coach, AI Chat and AI exercises'],
            ['key' => 'ai_max_tokens',     'value' => '2000',            'type' => 'integer', 'group' => 'ai_coach', 'description' => 'Max tokens per AI Coach response'],
            ['key' => 'ai_temperature',    'value' => '0.5',             'type' => 'string',  'group' => 'ai_coach', 'description' => 'AI temperature (0-2, lower = more focused)'],
            ['key' => 'ai_daily_limit',    'value' => '10',              'type' => 'integer', 'group' => 'ai_coach', 'description' => 'Daily AI requests for free users'],
            ['key' => 'ai_premium_limit',  'value' => '50',              'type' => 'integer', 'group' => 'ai_coach', 'description' => 'Daily AI requests for premium users'],
            ['key' => 'ai_enabled',        'value' => '1',               'type' => 'boolean', 'group' => 'ai_coach', 'description' => 'Enable AI features'],

            // Games (managed on the Music Games page)
            ['key' => 'game_enabled',           'value' => '1',                'type' => 'boolean', 'group' => 'games', 'description' => 'Enable music games'],
            ['key' => 'game_difficulty_levels', 'value' => 'easy,medium,hard', 'type' => 'string',  'group' => 'games', 'description' => 'Available game difficulty levels'],
            ['key' => 'game_time_limit',        'value' => '60',               'type' => 'integer', 'group' => 'games', 'description' => 'Default game time limit (seconds)'],
            ['key' => 'game_points_correct',    'value' => '10',               'type' => 'integer', 'group' => 'games', 'description' => 'Points per correct answer'],
            ['key' => 'game_points_streak',     'value' => '5',                'type' => 'integer', 'group' => 'games', 'description' => 'Streak bonus points'],
            ['key' => 'game_leaderboard_size',  'value' => '100',              'type' => 'integer', 'group' => 'games', 'description' => 'Max leaderboard entries'],
        ];

        foreach ($defaults as $setting) {
            SystemSetting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
