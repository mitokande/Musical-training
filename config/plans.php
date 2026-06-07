<?php

return [

    'user' => [
        'free' => [
            'daily_exercises_per_type' => 3,
            'ai_exercises' => false,
            'ai_coach' => 'limited',
            'detailed_charts' => false,
            'all_modules' => false,
            'exercise_setup_studio' => true,
            'saved_plans_limit' => 3,
            'ai_mode_exercises' => false,
            'adaptive_difficulty' => false,
            'games_daily_plays' => 5,
            'games_leaderboard' => false,
        ],
        'premium' => [
            'daily_exercises_per_type' => -1,
            'ai_exercises' => true,
            'ai_coach' => 'full',
            'detailed_charts' => true,
            'all_modules' => true,
            'exercise_setup_studio' => true,
            'saved_plans_limit' => -1,
            'ai_mode_exercises' => true,
            'adaptive_difficulty' => true,
            'games_daily_plays' => 15,
            'games_leaderboard' => true,
        ],
    ],

    'teacher' => [
        'free' => [
            'daily_exercises_per_type' => -1,
            'max_students' => 5,
            'crm' => false,
            'assignments' => true,
            'content_publishing' => false,
            'calendar' => false,
            'detailed_reports' => false,
            'games_daily_plays' => 5,
            'games_leaderboard' => false,
        ],
        'premium' => [
            'daily_exercises_per_type' => -1,
            'max_students' => -1,
            'crm' => true,
            'assignments' => true,
            'content_publishing' => true,
            'calendar' => true,
            'detailed_reports' => true,
            'games_daily_plays' => 15,
            'games_leaderboard' => true,
        ],
    ],

    'school' => [
        'free' => [
            'daily_exercises_per_type' => -1,
            'max_teachers' => 3,
            'max_students' => 20,
            'crm' => false,
            'content_publishing' => false,
            'calendar' => false,
            'advanced_reports' => false,
            'games_daily_plays' => 5,
            'games_leaderboard' => false,
        ],
        'premium' => [
            'daily_exercises_per_type' => -1,
            'max_teachers' => -1,
            'max_students' => -1,
            'crm' => true,
            'content_publishing' => true,
            'calendar' => true,
            'advanced_reports' => true,
            'games_daily_plays' => 15,
            'games_leaderboard' => true,
        ],
    ],

];
