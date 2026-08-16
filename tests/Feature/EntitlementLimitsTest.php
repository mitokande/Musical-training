<?php

namespace Tests\Feature;

use App\Models\ExerciseCategory;
use App\Models\LearningPathExercise;
use App\Models\TeacherAssignment;
use App\Models\TeacherConversation;
use App\Models\TeacherProfile;
use App\Models\TeacherStudentRelationship;
use App\Models\TeacherSubscriptionBenefit;
use App\Models\User;
use App\Services\Teacher\TeacherMessagingService;
use App\Services\Teacher\TeacherStudentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use InvalidArgumentException;
use Tests\TestCase;

/**
 * Access + quota matrix tests for the central entitlement layer:
 * guest daily session limits, free-plan daily session limits, AI gating,
 * and teacher/school CRM resource caps (config/plans.php).
 */
class EntitlementLimitsTest extends TestCase
{
    use RefreshDatabase;

    private function lpExercise(): LearningPathExercise
    {
        $category = ExerciseCategory::create([
            'name' => 'Intervals',
            'slug' => 'intervals-test',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        return LearningPathExercise::create([
            'category_id' => $category->id,
            'title' => 'Test Lesson',
            'description' => 'Interval basics',
            'translations' => [],
            'tags' => [],
            'skills_trained' => [],
            'slug' => 'test-lesson',
            'level' => 'beginner',
            'sort_order' => 1,
            'is_active' => true,
            'estimated_duration_minutes' => 5,
            'config_json' => [
                'practice_type' => 'melodic-interval-practice',
                'allowed_intervals' => ['Major 2nd', 'Major 3rd'],
                'allowed_notes' => ['C', 'D', 'E', 'F', 'G'],
                'octave_range' => [4, 4],
                'question_counts' => ['free' => 5],
            ],
        ]);
    }

    private function teacher(string $tier = 'basic'): User
    {
        $teacher = User::factory()->create(['role' => 'teacher', 'plan' => 'free']);
        $profile = TeacherProfile::createDraftFor($teacher);
        if ($tier === 'premium') {
            $profile->update(['tier' => TeacherProfile::TIER_PREMIUM]);
        }

        return $teacher->fresh();
    }

    // ── Guest: Learning Path daily quota ───────────────────────────────────

    public function test_guest_can_view_learning_path_pages(): void
    {
        $exercise = $this->lpExercise();

        $this->get('/learn')->assertOk();
        $this->get('/learn-exercise/'.$exercise->slug)->assertOk();
    }

    public function test_guest_lp_sessions_are_limited_per_day_and_capped_at_5_questions(): void
    {
        $exercise = $this->lpExercise();
        $limit = (int) config('plans.guest.learning_path_daily_sessions');

        for ($i = 0; $i < $limit; $i++) {
            $this->post('/learn-exercise/'.$exercise->slug.'/start', ['question_count' => 15])
                ->assertRedirect('/practice/melodic-interval-practice');

            $lp = session('learning_path_session');
            $this->assertLessThanOrEqual(5, $lp['question_count'], 'Guest sessions must be capped at 5 questions');
        }

        // The next start must be rejected with the guest CTA flag.
        $this->post('/learn-exercise/'.$exercise->slug.'/start', ['question_count' => 5])
            ->assertRedirect(route('learning-path.show', $exercise->slug))
            ->assertSessionHas('lp_limit_reached', 'guest');
    }

    // ── Free user: LP daily quota ──────────────────────────────────────────

    public function test_free_user_lp_sessions_limited_per_day(): void
    {
        $exercise = $this->lpExercise();
        $user = User::factory()->create(['role' => 'user', 'plan' => 'free']);
        $limit = (int) config('plans.user.free.learning_path_daily_sessions');

        for ($i = 0; $i < $limit; $i++) {
            $this->actingAs($user)
                ->post('/learn-exercise/'.$exercise->slug.'/start', ['question_count' => 5])
                ->assertRedirect('/practice/melodic-interval-practice');
        }

        $this->actingAs($user)
            ->post('/learn-exercise/'.$exercise->slug.'/start', ['question_count' => 5])
            ->assertSessionHas('lp_limit_reached', 'free');
    }

    public function test_premium_user_lp_sessions_unlimited(): void
    {
        $exercise = $this->lpExercise();
        $user = User::factory()->create(['role' => 'user', 'plan' => 'premium']);

        for ($i = 0; $i < 7; $i++) {
            $this->actingAs($user)
                ->post('/learn-exercise/'.$exercise->slug.'/start', ['question_count' => 5])
                ->assertRedirect('/practice/melodic-interval-practice');
        }

        $this->assertTrue(true);
    }

    // ── Exercise Setup Studio quotas ───────────────────────────────────────

    public function test_guest_studio_sessions_limited_per_day(): void
    {
        $limit = (int) config('plans.guest.studio_daily_sessions');
        $payload = [
            'exercise_type' => 'melodic-intervals',
            'question_count' => 10,
            'settings' => json_encode(['interval_pool' => ['M2', 'M3'], 'clef' => 'treble', 'direction' => 'ascending']),
        ];

        for ($i = 0; $i < $limit; $i++) {
            $this->post('/exercise-setup/launch', $payload)->assertRedirect('/practice/melodic-interval-practice');
            $this->assertLessThanOrEqual(5, session('exercise_settings')['question_count']);
            session()->forget('exercise_settings');
        }

        $this->post('/exercise-setup/launch', $payload)->assertSessionHas('studio_limit_reached', 'guest');
    }

    public function test_free_user_studio_sessions_limited_per_day(): void
    {
        $user = User::factory()->create(['role' => 'user', 'plan' => 'free']);
        $limit = (int) config('plans.user.free.studio_daily_sessions');
        $payload = [
            'exercise_type' => 'melodic-intervals',
            'question_count' => 5,
            'settings' => json_encode(['interval_pool' => ['M2', 'M3'], 'clef' => 'treble', 'direction' => 'ascending']),
        ];

        for ($i = 0; $i < $limit; $i++) {
            $this->actingAs($user)->post('/exercise-setup/launch', $payload)
                ->assertRedirect('/practice/melodic-interval-practice');
            session()->forget('exercise_settings');
        }

        $this->actingAs($user)->post('/exercise-setup/launch', $payload)
            ->assertSessionHas('studio_limit_reached', 'free');
    }

    // ── AI gating ──────────────────────────────────────────────────────────

    /**
     * AI Coach is gated at the route ('plan:ai_coach'), so a free user never
     * reaches the page. AI Exercises deliberately differs: the page is an
     * upsell that free users may look at, and only the generate POST is gated.
     */
    public function test_ai_coach_requires_premium(): void
    {
        $free = User::factory()->create(['role' => 'user', 'plan' => 'free']);
        $premium = User::factory()->create(['role' => 'user', 'plan' => 'premium']);

        $this->actingAs($free)->get('/ai-coach')->assertRedirect(route('dashboard'));
        $this->actingAs($premium)->get('/ai-coach')->assertOk();
    }

    public function test_ai_exercises_page_is_visible_to_free_users_but_generating_is_gated(): void
    {
        $free = User::factory()->create(['role' => 'user', 'plan' => 'free']);
        $premium = User::factory()->create(['role' => 'user', 'plan' => 'premium']);

        // The page renders for everyone, but free users get the upsell state.
        $this->actingAs($free)->get('/ai-exercises')
            ->assertOk()
            ->assertViewHas('canUseAi', false);

        $this->actingAs($premium)->get('/ai-exercises')
            ->assertOk()
            ->assertViewHas('canUseAi', true);

        // The actual entitlement lives on the generate route.
        $this->actingAs($free)
            ->post(route('ai.generate-practices'), ['exercise_types' => ['chord-practice']])
            ->assertRedirect(route('dashboard'));
    }

    public function test_free_teacher_and_school_cannot_generate_ai_exercises(): void
    {
        $teacher = $this->teacher();
        $school = User::factory()->create(['role' => 'school', 'plan' => 'free']);

        foreach ([$teacher, $school] as $user) {
            $this->actingAs($user)->get('/ai-exercises')
                ->assertOk()
                ->assertViewHas('canUseAi', false);

            $this->actingAs($user)
                ->post(route('ai.generate-practices'), ['exercise_types' => ['chord-practice']])
                ->assertRedirect(route('dashboard'));

            $this->actingAs($user)->get('/ai-coach')->assertRedirect(route('dashboard'));
        }
    }

    public function test_ask_ai_daily_limit_for_free_users_is_one(): void
    {
        $this->assertSame(1, (int) config('plans.user.free.ask_ai_daily'));
        $this->assertSame(1, (int) config('plans.teacher.free.ask_ai_daily'));
        $this->assertSame(1, (int) config('plans.school.free.ask_ai_daily'));
    }

    // ── Teacher CRM quotas ─────────────────────────────────────────────────

    public function test_free_teacher_capped_at_free_student_limit(): void
    {
        Notification::fake();

        $teacher = $this->teacher();
        $service = app(TeacherStudentService::class);
        $limit = (int) config('plans.teacher.free.max_free_students');

        for ($i = 0; $i < $limit; $i++) {
            $service->requestExistingUser($teacher, User::factory()->create(['plan' => 'free']));
        }

        // Free student beyond the cap → rejected.
        try {
            $service->requestExistingUser($teacher, User::factory()->create(['plan' => 'free']));
            $this->fail('Free-student cap was not enforced');
        } catch (InvalidArgumentException $e) {
            $this->assertNotSame('', $e->getMessage());
        }

        // A premium student never counts against the cap.
        $service->requestExistingUser($teacher, User::factory()->create(['plan' => 'premium']));
        $this->assertTrue(true);
    }

    public function test_premium_teacher_has_no_student_cap(): void
    {
        Notification::fake();

        $teacher = $this->teacher('premium');
        $service = app(TeacherStudentService::class);

        for ($i = 0; $i < 7; $i++) {
            $service->requestExistingUser($teacher, User::factory()->create(['plan' => 'free']));
        }

        $this->assertSame(7, TeacherStudentRelationship::where('teacher_id', $teacher->id)->count());
    }

    public function test_free_teacher_capped_at_two_active_assignments(): void
    {
        $teacher = $this->teacher();
        $limit = (int) config('plans.teacher.free.max_active_assignments');

        for ($i = 0; $i < $limit; $i++) {
            TeacherAssignment::create([
                'teacher_id' => $teacher->id,
                'title' => 'A'.$i,
                'type' => 'practice_goal',
                'status' => TeacherAssignment::STATUS_DRAFT,
            ]);
        }

        $this->actingAs($teacher)
            ->post('/teacher/assignments', ['title' => 'Over limit', 'type' => 'practice_goal'])
            ->assertSessionHasErrors('limit');

        // Archived assignments do not count.
        TeacherAssignment::where('teacher_id', $teacher->id)->first()
            ->update(['status' => TeacherAssignment::STATUS_ARCHIVED]);

        $this->actingAs($teacher)
            ->post('/teacher/assignments', [
                'title' => 'Now allowed', 'type' => 'practice_goal', 'daily_practice_minutes' => 10,
            ])
            ->assertSessionDoesntHaveErrors('limit');
    }

    public function test_free_teacher_cannot_use_ai_homework_builder(): void
    {
        $teacher = $this->teacher();

        $this->actingAs($teacher)
            ->post('/teacher/assignments/ai-suggest', ['prompt' => 'Ten easy interval questions'])
            ->assertForbidden();
    }

    public function test_free_teacher_daily_message_limit(): void
    {
        Notification::fake();

        $teacher = $this->teacher();
        $student = User::factory()->create(['plan' => 'free']);

        TeacherStudentRelationship::create([
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
            'status' => TeacherStudentRelationship::STATUS_ACTIVE,
            'approved_at' => now(),
        ]);

        $messaging = app(TeacherMessagingService::class);
        $conversation = TeacherConversation::create(['teacher_id' => $teacher->id, 'student_id' => $student->id]);

        $limit = (int) config('plans.teacher.free.daily_teacher_messages');
        for ($i = 0; $i < $limit; $i++) {
            $messaging->send($conversation, $teacher, 'Message '.$i);
        }

        $this->expectException(InvalidArgumentException::class);
        $messaging->send($conversation, $teacher, 'One too many');
    }

    // ── School quotas ──────────────────────────────────────────────────────

    public function test_free_school_capped_at_two_member_teachers(): void
    {
        Notification::fake();

        $school = User::factory()->create(['role' => 'school', 'plan' => 'free']);
        TeacherProfile::createDraftFor($school, TeacherProfile::ENTITY_SCHOOL);
        $school = $school->fresh();

        $limit = (int) config('plans.school.free.max_teachers');
        $this->assertSame(2, $limit);

        for ($i = 0; $i < $limit; $i++) {
            $this->actingAs($school)
                ->post('/school/teacher-relationships', ['user_id' => User::factory()->create()->id])
                ->assertSessionHas('status', 'teacher-relationship-requested');
        }

        $this->actingAs($school)
            ->post('/school/teacher-relationships', ['user_id' => User::factory()->create()->id])
            ->assertSessionHasErrors();
    }

    // ── Games: guest daily limit config ────────────────────────────────────

    public function test_guest_game_play_is_tracked_server_side_and_daily(): void
    {
        $this->get('/games/note-rush')->assertOk();

        $this->post('/games/note-rush/guest-track')
            ->assertOk()
            ->assertJson(['success' => true, 'can_play_again' => false]);

        // Same guest (cookie carried by the test session) is now blocked.
        $response = $this->get('/games/note-rush');
        $response->assertOk();
        $this->assertFalse($response->viewData('canPlay'));
    }

    // ── Premium entitlement via incentive benefit ──────────────────────────

    public function test_free_period_benefit_grants_effective_premium(): void
    {
        $teacher = $this->teacher();
        $this->assertFalse($teacher->isEffectivelyPremium());

        $teacher->teacherSubscriptionBenefits()->create([
            'type' => TeacherSubscriptionBenefit::TYPE_FREE_PERIOD,
            'status' => TeacherSubscriptionBenefit::STATUS_ACTIVE,
            'source' => 'automatic',
            'qualifying_student_count' => 10,
            'starts_at' => now(),
            'ends_at' => now()->addYear(),
        ]);

        $this->assertTrue($teacher->fresh()->isEffectivelyPremium());
    }
}
