<?php

namespace Tests\Feature;

use App\Models\TeacherProfile;
use App\Models\User;
use App\Services\Teacher\TeacherCapabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_any_registered_user_can_create_a_basic_teacher_account(): void
    {
        $user = User::factory()->create(['role' => 'user', 'plan' => 'free']);

        $response = $this->actingAs($user)->post('/teacher/become');

        $response->assertRedirect(route('teacher.profile.edit'));
        $this->assertDatabaseHas('teacher_profiles', [
            'user_id' => $user->id,
            'tier' => 'basic',
            'status' => 'draft',
        ]);
        $this->assertTrue($user->fresh()->hasTeacherAccount());
        // Role stays untouched — teacher-ness is orthogonal to role.
        $this->assertSame('user', $user->fresh()->role);
    }

    public function test_becoming_a_teacher_twice_does_not_create_a_second_profile(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/teacher/become');
        $this->actingAs($user)->post('/teacher/become');

        $this->assertSame(1, TeacherProfile::where('user_id', $user->id)->count());
    }

    public function test_slugs_are_unique_per_teacher(): void
    {
        $a = User::factory()->create(['name' => 'Jane', 'surname' => 'Doe']);
        $b = User::factory()->create(['name' => 'Jane', 'surname' => 'Doe']);

        $this->actingAs($a)->post('/teacher/become');
        $this->actingAs($b)->post('/teacher/become');

        $slugs = TeacherProfile::pluck('slug');
        $this->assertSame($slugs->unique()->count(), $slugs->count());
    }

    public function test_user_without_teacher_account_cannot_access_teacher_crm(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->get('/teacher/dashboard')->assertForbidden();
    }

    public function test_user_with_teacher_account_can_access_teacher_crm(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        TeacherProfile::create(['user_id' => $user->id, 'tier' => 'basic', 'status' => 'draft', 'slug' => 'test-teacher']);

        $this->actingAs($user)->get('/teacher/dashboard')->assertOk();
    }

    public function test_guest_is_redirected_to_login_from_teacher_crm(): void
    {
        $this->get('/teacher/dashboard')->assertRedirect(route('login'));
    }

    public function test_capability_service_gates_premium_features_by_tier(): void
    {
        $service = app(TeacherCapabilityService::class);

        $student = User::factory()->create(['role' => 'user']);
        $this->assertFalse($service->canViewTeacherCrm($student));

        $basic = User::factory()->create(['role' => 'user']);
        TeacherProfile::create(['user_id' => $basic->id, 'tier' => 'basic', 'status' => 'draft', 'slug' => 'basic-t']);
        $basic = $basic->fresh();

        $this->assertTrue($service->canViewTeacherCrm($basic));
        $this->assertFalse($service->canReplyToMessages($basic));
        $this->assertFalse($service->canManageStudents($basic));
        $this->assertFalse($service->canCreateAssignments($basic));
        $this->assertFalse($service->canManageAvailability($basic));
        $this->assertFalse($service->canUseExternalPaymentLinks($basic));

        $premium = User::factory()->create(['role' => 'user']);
        TeacherProfile::create(['user_id' => $premium->id, 'tier' => 'premium', 'status' => 'draft', 'slug' => 'premium-t']);
        $premium = $premium->fresh();

        $this->assertTrue($service->canReplyToMessages($premium));
        $this->assertTrue($service->canManageStudents($premium));
        $this->assertTrue($service->canCreateAssignments($premium));
        $this->assertTrue($service->canManageAvailability($premium));
        $this->assertTrue($service->canUseExternalPaymentLinks($premium));
        $this->assertTrue($service->canUseAIHomeworkBuilder($premium));
    }

    public function test_teacher_translations_resolve(): void
    {
        // Guards against the lang file living outside resources/lang —
        // raw keys like "teacher.nav.dashboard" must never reach the UI.
        $this->assertNotSame('teacher.nav.dashboard', __('teacher.nav.dashboard'));

        app()->setLocale('tr');
        $this->assertNotSame('teacher.nav.dashboard', __('teacher.nav.dashboard'));
        app()->setLocale('en');
    }

    public function test_legacy_teacher_role_lands_in_the_teacher_crm(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        TeacherProfile::create(['user_id' => $teacher->id, 'tier' => 'basic', 'status' => 'draft', 'slug' => 'legacy-t']);

        $this->actingAs($teacher)->get('/dashboard')->assertRedirect(route('teacher.dashboard'));
    }

    public function test_teacher_login_redirects_straight_to_the_teacher_crm(): void
    {
        $teacher = User::factory()->create(['role' => 'user']);
        TeacherProfile::create(['user_id' => $teacher->id, 'tier' => 'basic', 'status' => 'draft', 'slug' => 'login-t']);

        $response = $this->post('/login', [
            'email' => $teacher->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('teacher.dashboard', absolute: false));
    }

    public function test_student_login_keeps_the_existing_profile_landing(): void
    {
        $student = User::factory()->create(['role' => 'user']);

        $response = $this->post('/login', [
            'email' => $student->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('profile.edit', absolute: false));
    }

    public function test_role_based_user_without_profile_gets_draft_created_on_profile_edit(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($teacher)->get('/teacher/profile')->assertOk();

        $this->assertDatabaseHas('teacher_profiles', ['user_id' => $teacher->id, 'status' => 'draft']);
    }

    public function test_teacher_settings_page_renders_inside_the_crm(): void
    {
        $teacher = User::factory()->create(['role' => 'user']);
        TeacherProfile::create(['user_id' => $teacher->id, 'tier' => 'basic', 'status' => 'draft', 'slug' => 'settings-t']);

        $this->actingAs($teacher)->get('/teacher/settings')
            ->assertOk()
            ->assertSee(__('teacher.settings.password'))
            ->assertSee(__('teacher.settings.language'));
    }

    public function test_teacher_dropdown_hides_old_profile_menu_entries(): void
    {
        $teacher = User::factory()->create(['role' => 'user']);
        TeacherProfile::create(['user_id' => $teacher->id, 'tier' => 'basic', 'status' => 'draft', 'slug' => 'menu-t']);

        $response = $this->actingAs($teacher)->get('/teacher/dashboard');

        // Teacher accounts get the CRM-centric menu; old student profile
        // entries must be gone from their navbar.
        $response->assertDontSee(__('app.nav.my_progress'));
        $response->assertDontSee(__('app.nav.profile_settings'));

        $student = User::factory()->create(['role' => 'user']);
        $this->actingAs($student)->get('/feed')->assertSee(__('app.nav.my_progress'));
    }

    public function test_student_premium_plan_is_independent_from_teacher_tier(): void
    {
        // Student Premium + Teacher Basic must be a legal combination.
        $user = User::factory()->create(['role' => 'user', 'plan' => 'premium']);
        TeacherProfile::create(['user_id' => $user->id, 'tier' => 'basic', 'status' => 'draft', 'slug' => 'combo-t']);
        $user = $user->fresh();

        $this->assertTrue($user->isPremium());          // student side
        $this->assertFalse($user->isTeacherPremium());  // teacher side
        $this->assertSame('basic', $user->teacherTier());
    }
}
