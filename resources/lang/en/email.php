<?php

/**
 * System email copy (Email Center templates). Rendered into HTML by
 * App\Services\EmailCenter\EmailTemplateLibrary and synced into the
 * email_templates table (base + per-locale translations) by
 * `php artisan email:sync-templates`. {{placeholders}} are substituted per
 * recipient by TemplateRenderer — keep them intact in every translation.
 */
return [

    'footer' => [
        'manage_prefs' => 'Manage email preferences',
        'unsubscribe' => 'Unsubscribe',
    ],

    // Shared bits
    'hi' => 'Hi {{user_first_name}},',
    'guide_block' => [
        'title' => 'New to ear training? Start here.',
        'slogan' => '“Train smarter, not harder.” Our step-by-step guide walks you from your first interval to fluent listening.',
        'button' => '📖 Read the User Guide',
    ],

    'welcome' => [
        'subject' => 'Welcome to {{app_name}}, {{user_first_name}}! 🎵',
        'preheader' => 'Your musical ear starts training today — here is everything you unlock',
        'title' => 'Welcome aboard, {{user_first_name}}!',
        'subtitle' => "You just joined {{app_name}} — the friendliest way to train your musical ear. Here's what's waiting for you:",
        'f1_t' => 'Take the placement quiz', 'f1_d' => 'We tailor a personalised Learning Path to your exact level.',
        'f2_t' => 'Train with real audio', 'f2_d' => 'Single notes, intervals, chords, scales, rhythm and melodic dictation.',
        'f3_t' => 'Track every session', 'f3_d' => 'Accuracy, streaks and progress charts keep you moving forward.',
        'f4_t' => 'AI-assisted practice', 'f4_d' => 'Smart exercises that target your weak spots (Premium).',
        'btn' => '🚀 Start Training', 'btn_sub' => 'No setup needed — jump straight into your first session.',
        'ps' => 'Questions? Just reply to this email — a real human reads every one. 💜',
    ],

    'first_exercise' => [
        'subject' => '{{user_first_name}}, your first exercise is waiting 🎧',
        'preheader' => 'It only takes 5 minutes to train your ear',
        'title' => 'Ready for your first session?',
        'p1' => "You created your {{app_name}} account a couple of days ago, but haven't tried an exercise yet. The very first one takes less than five minutes — and it's the one that gets your ear moving.",
        'btn' => '🎧 Try Your First Exercise', 'btn_sub' => 'Less than 5 minutes. The hardest part is starting.',
        'p2' => 'Not sure where to begin? The <a href="{{app_url}}/learn" style="color:#7c3aed;font-weight:600;">Learning Path</a> guides you step by step, or skim the <a href="{{guide_url}}" style="color:#7c3aed;font-weight:600;">User Guide</a> first.',
    ],

    'learning_path' => [
        'subject' => 'Your Learning Path misses you, {{user_first_name}} 🎼',
        'preheader' => 'Pick up right where you left off',
        'title' => 'Pick up where you left off',
        'p1' => "Your ear was getting sharper — don't let that progress fade. Your Learning Path is exactly where you left it, streaks and all, ready whenever you are.",
        'btn' => '🎼 Continue Learning Path', 'btn_sub' => 'Even one short session keeps the momentum going.',
        'p2' => '🔥 Consistency beats intensity. Five focused minutes today is a win.',
    ],

    'weekly_progress' => [
        'subject' => 'Your week at {{app_name}}: {{weekly_sessions}} sessions 📈',
        'preheader' => 'Your weekly ear training recap',
        'title' => 'Your week in review',
        'subtitle' => "Nice work this week, {{user_first_name}}. Here's the recap:",
        'sessions' => 'Sessions', 'accuracy' => 'Accuracy', 'minutes' => 'Minutes',
        'btn' => '📈 Keep It Going', 'btn_sub' => 'Small weeks add up to a trained ear.',
    ],

    're_engagement' => [
        'subject' => 'We saved your progress, {{user_first_name}} 🎹',
        'preheader' => 'Your ear training progress is safe — come back anytime',
        'title' => 'Your progress is safe with us',
        'p1' => "It's been a while since your last practice session at {{app_name}}. Good news: your stats, streaks and Learning Path progress are all saved exactly where you left them.",
        'btn' => '🎹 Resume Training', 'btn_sub' => 'Five minutes today beats an hour someday.',
    ],

    'premium_intro' => [
        'subject' => 'Meet {{app_name}} Premium, {{user_first_name}} ⭐',
        'preheader' => 'Unlimited practice, AI coaching and more — see what Premium adds',
        'badge' => '✦ PREMIUM',
        'title' => 'Take your training further',
        'subtitle' => "Hi {{user_first_name}} — you've had a few days to explore {{app_name}}. Here's what <strong style=\"color:#7c3aed;\">Premium</strong> unlocks whenever you're ready:",
        'f1_t' => 'Unlimited daily exercises', 'f1_d' => 'No more 3-per-day limits — practice as much as your ear wants.',
        'f2_t' => 'AI-assisted practice', 'f2_d' => 'Exercises generated around your personal weak spots.',
        'f3_t' => 'Unlimited saved templates', 'f3_d' => 'Keep every custom drill you love, one tap away.',
        'f4_t' => 'Full melodic dictation', 'f4_d' => 'The complete dictation engine with rhythm and tonal melodies.',
        'btn' => '⭐ Explore Premium', 'btn_sub' => 'Upgrade anytime. Cancel anytime.',
        'p2' => 'Curious how it all fits together? The <a href="{{guide_url}}" style="color:#7c3aed;font-weight:600;">User Guide</a> shows every feature in action.',
    ],

    'premium_upsell' => [
        'subject' => 'You outgrew the free plan, {{user_first_name}} ⭐',
        'preheader' => 'Unlimited exercises, AI mode and more with Premium',
        'title' => "You're putting in the work",
        'subtitle' => "Hi {{user_first_name}} — you've been practising consistently. That's exactly how ears get trained. Here's what would take you further:",
        'f1_t' => 'Unlimited daily exercises', 'f1_d' => 'You keep hitting the free 3-per-day cap — remove it entirely.',
        'f2_t' => 'AI-assisted practice', 'f2_d' => 'Tailored to the exact intervals and chords you miss most.',
        'f3_t' => 'Unlimited saved templates', 'f3_d' => 'Save every drill in your rotation.',
        'btn' => '⭐ See Premium Plans', 'btn_sub' => "You've earned the upgrade.",
    ],

    'trial_ending' => [
        'subject' => 'Your Premium trial ends in {{trial_days_left}} days',
        'preheader' => 'Keep your unlimited practice going',
        'title' => 'Your trial is nearly up',
        'p1' => "Your free <strong>{{app_name}} Premium</strong> trial ends on <strong>{{trial_ends_on}}</strong> — that's {{trial_days_left}} days from now.",
        'p2' => 'Nothing will be charged: we never took your card details. When the trial ends your account simply returns to the free plan, and all of your practice history stays exactly where it is.',
        'p3' => 'Want to keep unlimited exercises, AI-assisted practice and everything else Premium unlocks? You can subscribe any time.',
        'btn' => '💳 Manage My Plan', 'btn_sub' => 'Keep unlimited practice without missing a beat.',
    ],

    'trial_ended' => [
        'subject' => 'Your Premium trial has ended, {{user_first_name}}',
        'preheader' => 'You are back on the free plan — your progress is safe',
        'title' => 'Thanks for trying Premium',
        'p1' => 'Your free trial has ended and your account is back on the <strong>free plan</strong>. You were not charged — we never asked for a card.',
        'p2' => 'Everything you practised during the trial is saved: your stats, streaks and Learning Path progress are all still there.',
        'btn' => '⭐ See Premium Plans', 'btn_sub' => 'Pick Premium back up in one click.',
    ],

    // --- Teacher audience ---
    'welcome_teacher' => [
        'subject' => 'Welcome to {{app_name}} for teachers, {{user_first_name}}! 🎓',
        'preheader' => 'Set up your profile, get discovered and start teaching',
        'badge' => '🎓 FOR TEACHERS',
        'title' => 'Welcome aboard, {{user_first_name}}!',
        'subtitle' => "Your <strong style=\"color:#7c3aed;\">{{app_name}}</strong> teacher account is ready. Here's how to get set up and start reaching students:",
        'f1_t' => 'Complete your public profile', 'f1_d' => 'Add your bio, instruments and experience, then submit it for approval.',
        'f2_t' => 'Set your availability', 'f2_d' => 'Open your calendar so students can book lessons directly.',
        'f3_t' => 'Connect with students', 'f3_d' => 'Invite your own students or get discovered in the directory.',
        'f4_t' => 'Publish content', 'f4_d' => 'Share articles and lessons to build your reputation.',
        'btn' => '🎓 Open Teacher Dashboard', 'btn_sub' => 'Your teaching hub — profile, calendar, students and messages.',
        'promo_t' => 'New to teaching on {{app_name}}?', 'promo_s' => '“Teach the ear, grow your studio.” See how profiles, bookings and payments work.', 'promo_btn' => '📖 How teaching works',
        'ps' => "Questions about your teaching account? Just reply — we're happy to help. 💜",
    ],

    'premium_intro_teacher' => [
        'subject' => 'Grow your teaching with {{app_name}} Premium, {{user_first_name}} ⭐',
        'preheader' => 'Bookings, payment links, content publishing and a featured profile',
        'badge' => '✦ TEACHER PREMIUM',
        'title' => 'Grow your teaching studio',
        'subtitle' => "Hi {{user_first_name}} — you've had a few days on {{app_name}}. Here's what <strong style=\"color:#7c3aed;\">Premium</strong> unlocks for teachers:",
        'f1_t' => 'Accept bookings & payments', 'f1_d' => 'Let students book and pay for lessons with your own payment links.',
        'f2_t' => 'Publish unlimited content', 'f2_d' => 'Articles, lessons and media to showcase your expertise.',
        'f3_t' => 'Featured, priority profile', 'f3_d' => 'Stand out in the teacher directory and get discovered faster.',
        'f4_t' => 'Student management tools', 'f4_d' => 'Assignments, progress tracking and messaging in one place.',
        'btn' => '⭐ See Teacher Premium', 'btn_sub' => 'Turn your teaching into a thriving studio.',
        'p2' => 'Want the full picture first? <a href="{{guide_url}}" style="color:#7c3aed;font-weight:600;">See how teaching works</a>.',
    ],

    'trial_ending_teacher' => [
        'subject' => 'Your teacher Premium trial ends in {{trial_days_left}} days',
        'preheader' => 'Keep bookings, payments and content publishing',
        'title' => 'Your teacher trial is nearly up',
        'p1' => "Your free <strong>{{app_name}} teacher Premium</strong> trial ends on <strong>{{trial_ends_on}}</strong> — that's {{trial_days_left}} days from now.",
        'p2' => 'Nothing will be charged: we never took your card details. When the trial ends your teacher account returns to <strong>Basic</strong>, and features like bookings, payment links and content publishing pause — but your profile and students stay exactly where they are.',
        'btn' => '💳 Keep Teacher Premium', 'btn_sub' => "Don't lose your bookings and content.",
    ],

    'trial_ended_teacher' => [
        'subject' => 'Your teacher Premium trial has ended, {{user_first_name}}',
        'preheader' => 'Your teaching profile is safe — you are back on Basic',
        'title' => 'Your teacher trial has ended',
        'p1' => 'Your free trial has ended and your teacher account is back on <strong>Basic</strong>. You were not charged — we never asked for a card.',
        'p2' => 'Your public profile, students and messages are all safe. Booking, payment links and content publishing are ready to switch back on whenever you upgrade.',
        'btn' => '⭐ See Teacher Premium', 'btn_sub' => 'Pick your studio tools back up in one click.',
    ],

    // --- School audience ---
    'welcome_school' => [
        'subject' => 'Welcome to {{app_name}} for schools, {{user_first_name}}! 🏫',
        'preheader' => 'Set up your school, add your teachers and manage everything in one place',
        'badge' => '🏫 FOR SCHOOLS',
        'title' => 'Welcome aboard, {{user_first_name}}!',
        'subtitle' => "Your <strong style=\"color:#7c3aed;\">{{app_name}}</strong> school account is ready. Here's how to set up and bring your teachers on board:",
        'f1_t' => 'Set up your school profile', 'f1_d' => 'Add your school details and branding, then submit for approval.',
        'f2_t' => 'Add your teachers', 'f2_d' => 'Invite or connect member teachers and manage them in one panel.',
        'f3_t' => 'Manage memberships', 'f3_d' => 'Handle teacher relationships, invitations and access centrally.',
        'f4_t' => 'Get discovered', 'f4_d' => 'Showcase your school in the public directory.',
        'btn' => '🏫 Open School Panel', 'btn_sub' => 'Your school hub — profile, teachers and memberships.',
        'promo_t' => 'New to {{app_name}} for schools?', 'promo_s' => '“One home for your whole music school.” See how schools and teachers work together.', 'promo_btn' => '📖 How schools work',
        'ps' => "Need a hand setting up your school? Just reply — we'll help you get started. 💜",
    ],

    'premium_intro_school' => [
        'subject' => 'Unlock {{app_name}} Premium for your school, {{user_first_name}} ⭐',
        'preheader' => 'Unlimited teachers, school branding and priority visibility',
        'badge' => '✦ SCHOOL PREMIUM',
        'title' => 'Everything your school needs',
        'subtitle' => "Hi {{user_first_name}} — here's what <strong style=\"color:#7c3aed;\">Premium</strong> unlocks for your school on {{app_name}}:",
        'f1_t' => 'Unlimited member teachers', 'f1_d' => 'Add as many teachers to your school as you need.',
        'f2_t' => 'School branding', 'f2_d' => 'Present your school with your own identity across Harmoniva.',
        'f3_t' => 'Priority visibility', 'f3_d' => 'Rank higher and get discovered in the directory.',
        'f4_t' => 'Oversight & tools', 'f4_d' => 'Manage teachers, memberships and activity from one panel.',
        'btn' => '⭐ See School Premium', 'btn_sub' => 'Everything your music school needs to grow.',
        'p2' => 'Want to see it first? <a href="{{guide_url}}" style="color:#7c3aed;font-weight:600;">How schools work on {{app_name}}</a>.',
    ],

    'trial_ending_school' => [
        'subject' => 'Your school Premium trial ends in {{trial_days_left}} days',
        'preheader' => 'Keep unlimited teachers and school branding',
        'title' => 'Your school trial is nearly up',
        'p1' => "Your free <strong>{{app_name}} school Premium</strong> trial ends on <strong>{{trial_ends_on}}</strong> — that's {{trial_days_left}} days from now.",
        'p2' => 'Nothing will be charged: we never took your card details. When the trial ends your school account returns to <strong>Basic</strong> — but your school profile, teachers and memberships all stay exactly where they are.',
        'btn' => '💳 Keep School Premium', 'btn_sub' => 'Keep your teachers and branding.',
    ],

    'trial_ended_school' => [
        'subject' => 'Your school Premium trial has ended, {{user_first_name}}',
        'preheader' => 'Your school profile is safe — you are back on Basic',
        'title' => 'Your school trial has ended',
        'p1' => 'Your free trial has ended and your school account is back on <strong>Basic</strong>. You were not charged — we never asked for a card.',
        'p2' => 'Your school profile, teachers and memberships are all safe. School Premium features are ready to switch back on whenever you upgrade.',
        'btn' => '⭐ See School Premium', 'btn_sub' => 'Reactivate your school tools in one click.',
    ],

    'first_exercise_teacher' => [
        'subject' => '{{user_first_name}}, add your first student 👥',
        'preheader' => 'Your teaching dashboard is ready — it just needs students',
        'title' => 'Ready for your first student?',
        'p1' => 'Your {{app_name}} teacher account has been open for a couple of days, but no students have joined yet. Inviting one takes a minute: send the link, and their practice, assignments and progress all land in your dashboard.',
        'btn' => '👥 Invite Your First Student',
        'btn_sub' => 'One link. Their progress shows up automatically.',
        'p2' => 'Already teaching elsewhere? Bring your existing students over — they keep practising, you get the data.',
    ],

    'first_exercise_school' => [
        'subject' => '{{user_first_name}}, add your first teacher 🏫',
        'preheader' => 'Your school dashboard is ready — it just needs teachers',
        'title' => 'Ready for your first teacher?',
        'p1' => 'Your {{app_name}} school account has been open for a couple of days, but no teachers are on the roster yet. Invite one and their students, lessons and progress roll up into your school dashboard automatically.',
        'btn' => '🏫 Invite Your First Teacher',
        'btn_sub' => 'One link. Their studio connects to your school.',
        'p2' => 'Every teacher you add brings their students with them — that is your whole school in one view.',
    ],

    'learning_path_teacher' => [
        'subject' => 'Your students are waiting, {{user_first_name}} 📋',
        'preheader' => 'Check in on your studio — progress is piling up',
        'title' => 'Your studio kept going without you',
        'p1' => 'It has been a quiet week on your side, but your students have been practising. Their sessions, accuracy and streaks are waiting in your dashboard — and a single assignment is often all it takes to push someone past a plateau.',
        'btn' => '📋 Check On Your Students',
        'btn_sub' => 'See who is improving and who is stuck.',
        'p2' => '🎯 A short assignment sent today is worth more than a long one sent next month.',
    ],

    'learning_path_school' => [
        'subject' => 'Your school kept going, {{user_first_name}} 📋',
        'preheader' => 'Your teachers and students have been busy',
        'title' => 'Your school kept going without you',
        'p1' => 'You have not looked in for a week, but your teachers and their students have been practising. Lesson activity, student progress and teacher rosters are all up to date in your school dashboard.',
        'btn' => '📋 Open School Dashboard',
        'btn_sub' => 'Every teacher, every student, one view.',
        'p2' => '🎯 A quick weekly look is usually enough to spot who needs support.',
    ],

    'weekly_progress_teacher' => [
        'subject' => 'Your studio this week: {{weekly_sessions}} sessions 📈',
        'preheader' => 'How your students practised this week',
        'title' => 'Your studio this week',
        'subtitle' => 'Here is how your students practised this week, {{user_first_name}}. You sent {{weekly_assignments}} new assignments.',
        'm1' => 'Students',
        'm2' => 'Sessions',
        'm3' => 'Accuracy',
        'btn' => '📈 Open Teacher Dashboard',
        'btn_sub' => 'Dig into who improved and who needs a nudge.',
    ],

    'weekly_progress_school' => [
        'subject' => 'Your school this week: {{weekly_sessions}} sessions 📈',
        'preheader' => 'How your school practised this week',
        'title' => 'Your school this week',
        'subtitle' => 'Here is the week across your school, {{user_first_name}} — average accuracy {{weekly_accuracy}}.',
        'm1' => 'Teachers',
        'm2' => 'Students',
        'm3' => 'Sessions',
        'btn' => '📈 Open School Dashboard',
        'btn_sub' => 'Per-teacher and per-student breakdowns inside.',
    ],

    're_engagement_teacher' => [
        'subject' => 'Your teaching account is still here, {{user_first_name}} 🎓',
        'preheader' => 'Your students, assignments and profile are all saved',
        'title' => 'Everything is where you left it',
        'p1' => 'It has been a while since you opened your {{app_name}} teacher dashboard. Your student list, assignments, profile and reviews are exactly as you left them — nothing expired, nothing lost.',
        'btn' => '🎓 Back To Teaching',
        'btn_sub' => 'Pick up your studio in one click.',
    ],

    're_engagement_school' => [
        'subject' => 'Your school account is still here, {{user_first_name}} 🏫',
        'preheader' => 'Your teachers, students and settings are all saved',
        'title' => 'Everything is where you left it',
        'p1' => 'It has been a while since you opened your {{app_name}} school dashboard. Your teacher roster, student records and school profile are exactly as you left them — nothing expired, nothing lost.',
        'btn' => '🏫 Back To Your School',
        'btn_sub' => 'Pick up where your school left off.',
    ],

    'premium_upsell_teacher' => [
        'subject' => 'Your studio outgrew the free plan, {{user_first_name}} ⭐',
        'preheader' => 'Bookings, payments and unlimited assignments with Premium',
        'badge' => '⭐ TEACHER PREMIUM',
        'title' => 'You are building a real studio',
        'subtitle' => 'Hi {{user_first_name}} — you have students practising regularly. Here is what Premium adds to the studio you have already built:',
        'f1_t' => 'Online booking calendar',
        'f1_d' => 'Students book lessons in your free slots. No back-and-forth messages.',
        'f2_t' => 'Unlimited assignments',
        'f2_d' => 'Send as many custom exercises as your students need, with no daily cap.',
        'f3_t' => 'A featured public profile',
        'f3_d' => 'Rank higher in the teacher directory and take payments through your profile.',
        'btn' => '⭐ See Teacher Plans',
        'btn_sub' => 'Built for teachers who are already teaching.',
        'p2' => 'Not ready yet? Your free account keeps working exactly as it does today.',
    ],

    'premium_upsell_school' => [
        'subject' => 'Your school outgrew the free plan, {{user_first_name}} ⭐',
        'preheader' => 'Unlimited teachers, school branding and reporting with Premium',
        'badge' => '⭐ SCHOOL PREMIUM',
        'title' => 'Your school is growing',
        'subtitle' => 'Hi {{user_first_name}} — your teachers are active and their students are practising. Here is what Premium adds:',
        'f1_t' => 'Unlimited teachers on the roster',
        'f1_d' => 'Add your whole faculty without a per-seat limit.',
        'f2_t' => 'School-wide reporting',
        'f2_d' => 'Compare teachers and cohorts, and export progress for parents.',
        'f3_t' => 'A branded school profile',
        'f3_d' => 'Your logo, your page, ranked in the school directory.',
        'btn' => '⭐ See School Plans',
        'btn_sub' => 'Built for schools with a real roster.',
        'p2' => 'Not ready yet? Your free account keeps working exactly as it does today.',
    ],

];
