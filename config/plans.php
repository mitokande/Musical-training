<?php

/*
 * Central plan / entitlement matrix — the single source of truth for every
 * access rule and quota. Admin-managed Plan rows (plans table) may override
 * individual keys via User::getPlanLimit(); this file is the fallback and
 * the only place new keys are introduced.
 *
 * Conventions: -1 = unlimited, false = feature off, integers = daily limits
 * unless the key says otherwise. Guest limits all reset daily.
 */

return [

    // Anonymous visitors. Tracked server-side per guest id (UsageQuotaService);
    // every guest limit renews daily.
    'guest' => [
        'learning_path_daily_sessions' => 2,
        'studio_daily_sessions' => 2,
        'session_question_cap' => 5,
        'games_daily_plays_per_type' => 1,
        'games_max_level' => 1,
        'games_leaderboard' => false,
        'ask_ai_daily' => 0,
        'ai_exercises' => false,
        'ai_coach' => false,
        // Piano Studio stays fully open; only the (dismissible) signup popup
        // timing is configured here, in seconds.
        'piano_popup_initial_seconds' => 180,
        'piano_popup_repeat_seconds' => 60,
    ],

    'user' => [
        'free' => [
            'daily_exercises_per_type' => 3,
            'learning_path_daily_sessions' => 5,
            'studio_daily_sessions' => 5,
            'session_question_cap' => 5,
            'ask_ai_daily' => 1,
            'ai_exercises' => false,
            'ai_coach' => 'limited',
            'detailed_charts' => false,
            'all_modules' => false,
            'exercise_setup_studio' => true,
            'saved_plans_limit' => 3,
            'ai_mode_exercises' => false,
            'adaptive_difficulty' => false,
            'games_daily_plays_per_type' => 2,
            'games_daily_plays_total' => 6,
            'games_max_level' => -1,
            'games_leaderboard' => false,
        ],
        'premium' => [
            'daily_exercises_per_type' => -1,
            'learning_path_daily_sessions' => -1,
            'studio_daily_sessions' => -1,
            'session_question_cap' => -1,
            'ask_ai_daily' => 10,
            'ai_exercises' => true,
            'ai_coach' => 'full',
            'detailed_charts' => true,
            'all_modules' => true,
            'exercise_setup_studio' => true,
            'saved_plans_limit' => -1,
            'ai_mode_exercises' => true,
            'adaptive_difficulty' => true,
            'games_daily_plays_per_type' => -1,
            'games_daily_plays_total' => -1,
            'games_max_level' => -1,
            'games_leaderboard' => true,
        ],
    ],

    'teacher' => [
        'free' => [
            'daily_exercises_per_type' => -1,
            'learning_path_daily_sessions' => 5,
            'studio_daily_sessions' => 5,
            'session_question_cap' => 5,
            'ask_ai_daily' => 1,
            'ai_exercises' => false,
            'ai_coach' => 'limited',
            // CRM resource limits (not daily): counts of live records.
            'max_free_students' => 5,      // premium-plan students are unlimited
            'max_active_assignments' => 2,
            'assignment_ai' => false,
            'daily_teacher_messages' => 5, // sent messages per day
            'max_documents' => 5,          // document-archive uploads (PDF etc.)
            'max_students' => 5,           // legacy key, kept for old UI copy
            'crm' => false,
            'assignments' => true,
            'content_publishing' => false,
            'calendar' => false,
            'live_lessons' => false,
            'detailed_reports' => false,
            'games_daily_plays_per_type' => 2,
            'games_daily_plays_total' => 6,
            'games_max_level' => -1,
            'games_leaderboard' => false,
        ],
        'premium' => [
            'daily_exercises_per_type' => -1,
            'learning_path_daily_sessions' => -1,
            'studio_daily_sessions' => -1,
            'session_question_cap' => -1,
            'ask_ai_daily' => 10,
            'ai_exercises' => true,
            'ai_coach' => 'full',
            'max_free_students' => -1,
            'max_active_assignments' => -1,
            'assignment_ai' => true,
            'daily_teacher_messages' => -1,
            'max_documents' => -1,
            'max_students' => -1,
            'crm' => true,
            'assignments' => true,
            'content_publishing' => true,
            'calendar' => true,
            'live_lessons' => true,
            'detailed_reports' => true,
            'games_daily_plays_per_type' => -1,
            'games_daily_plays_total' => -1,
            'games_max_level' => -1,
            'games_leaderboard' => true,
        ],
    ],

    'school' => [
        'free' => [
            'daily_exercises_per_type' => -1,
            'learning_path_daily_sessions' => 5,
            'studio_daily_sessions' => 5,
            'session_question_cap' => 5,
            'ask_ai_daily' => 1,
            'ai_exercises' => false,
            'ai_coach' => 'limited',
            'max_teachers' => 2,
            'max_free_students' => 5,      // premium-plan students are unlimited
            'max_active_assignments' => 2,
            'assignment_ai' => false,
            'daily_teacher_messages' => 5,
            'max_documents' => 5,
            'max_students' => 5,           // legacy key, kept for old UI copy
            'crm' => false,
            'content_publishing' => false,
            'calendar' => false,
            'live_lessons' => false,
            'advanced_reports' => false,
            'games_daily_plays_per_type' => 2,
            'games_daily_plays_total' => 6,
            'games_max_level' => -1,
            'games_leaderboard' => false,
        ],
        'premium' => [
            'daily_exercises_per_type' => -1,
            'learning_path_daily_sessions' => -1,
            'studio_daily_sessions' => -1,
            'session_question_cap' => -1,
            'ask_ai_daily' => 10,
            'ai_exercises' => true,
            'ai_coach' => 'full',
            'max_teachers' => -1,
            'max_free_students' => -1,
            'max_active_assignments' => -1,
            'assignment_ai' => true,
            'daily_teacher_messages' => -1,
            'max_documents' => -1,
            'max_students' => -1,
            'crm' => true,
            'content_publishing' => true,
            'calendar' => true,
            'live_lessons' => true,
            'advanced_reports' => true,
            'games_daily_plays_per_type' => -1,
            'games_daily_plays_total' => -1,
            'games_max_level' => -1,
            'games_leaderboard' => true,
        ],
    ],

];
