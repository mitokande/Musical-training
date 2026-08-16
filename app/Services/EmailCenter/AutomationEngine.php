<?php

namespace App\Services\EmailCenter;

use App\Models\EmailAutomation;
use App\Models\EmailMessage;
use App\Models\EmailTemplate;
use App\Models\ExerciseSession;
use App\Models\TeacherStudentRelationship;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Runs the standard lifecycle automations. Each enabled automation finds
 * its due users, applies cooldowns and the shared frequency cap (via
 * EmailDispatchService) and queues at most $batchLimit sends per run so a
 * newly enabled automation cannot flood the queue.
 */
class AutomationEngine
{
    public const KEYS = [
        'welcome',
        'first_exercise_reminder',
        'learning_path_reminder',
        'weekly_progress',
        're_engagement',
        'premium_intro',
        'premium_upsell',
        'trial_ending',
        'trial_ended',
    ];

    /** The audiences every automation is run for, in send order. */
    public const AUDIENCES = ['student', 'teacher', 'school'];

    /**
     * Automations that are service mail, not marketing. "Your trial ends in 3
     * days" is a notice about the user's own account, so it must not be dropped
     * by the marketing frequency cap or an unsubscribe from promotional email.
     */
    private const TRANSACTIONAL_KEYS = ['trial_ending', 'trial_ended'];

    protected int $batchLimit = 200;

    public function __construct(protected EmailDispatchService $dispatcher) {}

    public function run(): array
    {
        $results = [];

        foreach (EmailAutomation::where('enabled', true)->whereNotNull('template_id')->with('template')->get() as $automation) {
            if (! $automation->template?->is_active) {
                continue;
            }

            $sent = 0;

            // Each audience is targeted by its own conditions and mailed with
            // its own template variant. The audiences partition the user base,
            // so nobody is picked up twice.
            foreach (self::AUDIENCES as $audience) {
                foreach ($this->dueUsers($automation, $audience)->limit($this->batchLimit)->get() as $user) {
                    $message = $this->dispatcher->dispatch(
                        recipient: $user,
                        emailType: in_array($automation->key, self::TRANSACTIONAL_KEYS, true) ? 'transactional' : 'automation',
                        template: $this->templateFor($automation, $user),
                        automation: $automation,
                        context: $this->context($automation, $user),
                    );

                    if ($message) {
                        $sent++;
                    }
                }
            }

            $automation->update(['last_run_at' => now()]);
            $results[$automation->key] = $sent;

            if ($sent > 0) {
                Log::info('Email automation run', ['key' => $automation->key, 'queued' => $sent]);
            }
        }

        return $results;
    }

    /**
     * The template to send this recipient — an audience-specific variant
     * (e.g. welcome-teacher, premium-intro-school) when an active one exists,
     * otherwise the automation's base (student) template.
     */
    protected function templateFor(EmailAutomation $automation, User $user): ?EmailTemplate
    {
        $base = $automation->template;
        $audience = $user->emailAudience();

        if ($base && $audience !== 'student') {
            $variant = EmailTemplate::where('slug', $base->slug.'-'.$audience)
                ->where('is_active', true)
                ->first();

            if ($variant) {
                return $variant;
            }
        }

        return $base;
    }

    protected function dueUsers(EmailAutomation $automation, string $audience = 'student'): Builder
    {
        $query = User::query()
            ->with('teacherProfile') // audience resolution (template variant + links)
            ->forEmailAudience($audience)
            ->whereNotNull('email_verified_at')
            ->whereNull('suspended_at')
            ->where('is_restricted', false);

        switch ($automation->key) {
            case 'welcome':
                $delay = (int) $automation->configValue('delay_hours', 1);
                $query->where('created_at', '<=', now()->subHours($delay))
                    // never greet accounts older than a week (first-enable safety)
                    ->where('created_at', '>=', now()->subDays(7));
                $this->neverReceived($query, $automation);
                break;

            case 'first_exercise_reminder':
                // "You haven't done the first thing yet" — which thing depends on
                // the audience: a student practises, a teacher takes on students,
                // a school takes on teachers.
                $days = (int) $automation->configValue('delay_days', 2);
                $query->where('created_at', '<=', now()->subDays($days))
                    ->where('created_at', '>=', now()->subDays(30));
                $this->withoutFirstStep($query, $audience);
                $this->neverReceived($query, $automation);
                break;

            case 'learning_path_reminder':
                // Someone with something waiting for them who has gone quiet.
                $inactive = (int) $automation->configValue('inactive_days', 7);
                $query->where('last_active_at', '<', now()->subDays($inactive));
                $this->hasSomethingWaiting($query, $audience);
                $this->notReceivedWithin($query, $automation, (int) $automation->configValue('cooldown_days', 14));
                break;

            case 'weekly_progress':
                $query->where('last_active_at', '>=', now()->subDays(7));

                if ($audience === 'student') {
                    $query->whereHas('exerciseSessions', fn ($q) => $q->where('created_at', '>=', now()->subDays(7)));
                } else {
                    // A teacher/school digest is about the people under them, so
                    // having any is enough — their own practice is irrelevant.
                    $this->hasSomethingWaiting($query, $audience);
                }

                $this->notReceivedWithin($query, $automation, 6);
                break;

            case 're_engagement':
                $inactive = (int) $automation->configValue('inactive_days', 30);
                $query->where('last_active_at', '<', now()->subDays($inactive));
                $this->notReceivedWithin($query, $automation, (int) $automation->configValue('cooldown_days', 60));
                break;

            case 'premium_intro':
                // Introduce Premium to every free user a few days after signup.
                // Sent once per account; a 30-day floor keeps a first enable from
                // blasting the whole back catalogue of old free users.
                $days = (int) $automation->configValue('min_account_days', 3);
                $query->where('plan', 'free')
                    ->where('created_at', '<=', now()->subDays($days))
                    ->where('created_at', '>=', now()->subDays(30));
                $this->neverReceived($query, $automation);
                break;

            case 'premium_upsell':
                // Engaged free accounts. "Engaged" is practice volume for a
                // student, and having anyone to teach for a teacher/school.
                $query->where('plan', 'free')
                    ->where('created_at', '<=', now()->subDays((int) $automation->configValue('min_account_days', 14)));

                if ($audience === 'student') {
                    $query->whereHas('exerciseSessions', fn ($q) => $q, '>=', (int) $automation->configValue('min_sessions', 10));
                } else {
                    $this->hasSomethingWaiting($query, $audience);
                }

                $this->notReceivedWithin($query, $automation, (int) $automation->configValue('cooldown_days', 30));
                break;

            case 'trial_ending':
                // Still on the trial, inside the final stretch. Sent once per
                // trial: neverReceived() is too broad (a second trial granted by
                // an admin should notify again), so we key off trial_started_at.
                $lead = (int) $automation->configValue('lead_days', 3);
                $query->where('plan', 'premium')
                    ->whereNotNull('trial_ends_at')
                    ->where('trial_ends_at', '>', now())
                    ->where('trial_ends_at', '<=', now()->addDays($lead));
                $this->notReceivedSince($query, $automation, 'trial_started_at');
                break;

            case 'trial_ended':
                // Trial lapsed and the account really did fall back to free —
                // someone who converted to paid must never get this.
                $window = (int) $automation->configValue('window_days', 3);
                $query->where('plan', 'free')
                    ->whereNotNull('trial_ends_at')
                    ->where('trial_ends_at', '<=', now())
                    ->where('trial_ends_at', '>=', now()->subDays($window));
                $this->notReceivedSince($query, $automation, 'trial_started_at');
                break;

            default:
                // unknown key — match nobody
                $query->whereRaw('1 = 0');
        }

        return $query->orderBy('id');
    }

    /**
     * The account has not taken the first step that makes Harmoniva useful to
     * it: practising (student), taking on a student (teacher), taking on a
     * teacher (school).
     */
    protected function withoutFirstStep(Builder $query, string $audience): void
    {
        match ($audience) {
            'school' => $query->whereDoesntHave('schoolTeacherRelationships', fn ($q) => $q->active()),
            'teacher' => $query->whereDoesntHave('studentRelationships', fn ($q) => $q->active()),
            default => $query->whereDoesntHave('exerciseSessions'),
        };
    }

    /**
     * The mirror image: the account has people depending on it (or its own
     * Learning Path progress), so a nudge has something concrete to point at.
     */
    protected function hasSomethingWaiting(Builder $query, string $audience): void
    {
        match ($audience) {
            'school' => $query->whereHas('schoolTeacherRelationships', fn ($q) => $q->active()),
            'teacher' => $query->whereHas('studentRelationships', fn ($q) => $q->active()),
            default => $query->whereExists(function ($sub) {
                $sub->selectRaw('1')->from('user_learning_path_progress')
                    ->whereColumn('user_learning_path_progress.user_id', 'users.id');
            }),
        };
    }

    protected function neverReceived(Builder $query, EmailAutomation $automation): void
    {
        $query->whereNotIn('id', EmailMessage::where('automation_id', $automation->id)
            ->whereNotNull('user_id')->select('user_id'));
    }

    protected function notReceivedWithin(Builder $query, EmailAutomation $automation, int $days): void
    {
        $query->whereNotIn('id', EmailMessage::where('automation_id', $automation->id)
            ->where('created_at', '>=', now()->subDays($days))
            ->whereNotNull('user_id')->select('user_id'));
    }

    /**
     * Not sent since the moment recorded in $column on the user's own row.
     * Used by the trial automations so each granted trial gets exactly one
     * notice — including a second trial handed out later by an admin.
     */
    protected function notReceivedSince(Builder $query, EmailAutomation $automation, string $column): void
    {
        $query->whereNotExists(function ($sub) use ($automation, $column) {
            $sub->selectRaw('1')
                ->from('email_messages')
                ->whereColumn('email_messages.user_id', 'users.id')
                ->where('email_messages.automation_id', $automation->id)
                ->whereColumn('email_messages.created_at', '>=', 'users.'.$column);
        });
    }

    /**
     * Template variables specific to each automation.
     */
    protected function context(EmailAutomation $automation, User $user): array
    {
        if (in_array($automation->key, self::TRANSACTIONAL_KEYS, true)) {
            return [
                'trial_days_left' => (string) $user->trialDaysLeft(),
                'trial_ends_on' => $user->trial_ends_at?->format('M j, Y') ?? '',
            ];
        }

        if ($automation->key !== 'weekly_progress') {
            return [];
        }

        return match ($user->emailAudience()) {
            'school' => $this->schoolWeekly($user),
            'teacher' => $this->teacherWeekly($user),
            default => $this->studentWeekly($user),
        };
    }

    /** The student's own week of practice. */
    protected function studentWeekly(User $user): array
    {
        $sessions = ExerciseSession::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(7))
            ->get();

        return [
            'weekly_sessions' => (string) $sessions->count(),
            'weekly_accuracy' => $sessions->avg('accuracy') !== null ? round($sessions->avg('accuracy')).'%' : '—',
            'weekly_minutes' => (string) (int) round($sessions->sum('duration_seconds') / 60),
        ];
    }

    /** A teacher's week is their students' week, not their own. */
    protected function teacherWeekly(User $user): array
    {
        $studentIds = $user->studentRelationships()->active()->pluck('student_id');

        return array_merge($this->weeklyFor($studentIds), [
            'weekly_students' => (string) $studentIds->count(),
            'weekly_assignments' => (string) $user->teacherAssignments()
                ->where('created_at', '>=', now()->subDays(7))->count(),
        ]);
    }

    /** A school's week aggregates every student of every teacher on its roster. */
    protected function schoolWeekly(User $user): array
    {
        $teacherIds = $user->schoolTeacherRelationships()->active()->pluck('teacher_id');

        $studentIds = TeacherStudentRelationship::query()
            ->whereIn('teacher_id', $teacherIds)
            ->where('status', TeacherStudentRelationship::STATUS_ACTIVE)
            ->distinct()
            ->pluck('student_id');

        return array_merge($this->weeklyFor($studentIds), [
            'weekly_teachers' => (string) $teacherIds->count(),
            'weekly_students' => (string) $studentIds->count(),
        ]);
    }

    /**
     * Session count and average accuracy over the last 7 days for a set of
     * students. An empty roster must still render, so the numbers degrade to
     * 0 / '—' rather than being left as unsubstituted placeholders.
     *
     * @param  Collection<int, int>  $studentIds
     */
    protected function weeklyFor($studentIds): array
    {
        if ($studentIds->isEmpty()) {
            return ['weekly_sessions' => '0', 'weekly_accuracy' => '—', 'weekly_minutes' => '0'];
        }

        $sessions = ExerciseSession::whereIn('user_id', $studentIds)
            ->where('created_at', '>=', now()->subDays(7))
            ->get();

        return [
            'weekly_sessions' => (string) $sessions->count(),
            'weekly_accuracy' => $sessions->avg('accuracy') !== null ? round($sessions->avg('accuracy')).'%' : '—',
            'weekly_minutes' => (string) (int) round($sessions->sum('duration_seconds') / 60),
        ];
    }
}
