<?php

namespace Tests\Feature;

use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SchoolPanelTest extends TestCase
{
    use RefreshDatabase;

    private function school(array $overrides = []): User
    {
        return User::factory()->create(array_merge(['role' => 'school', 'plan' => 'free'], $overrides));
    }

    public function test_first_panel_visit_auto_provisions_a_school_entity_draft(): void
    {
        $school = $this->school();

        $this->actingAs($school)->get('/school/dashboard')->assertOk();

        $this->assertDatabaseHas('teacher_profiles', [
            'user_id' => $school->id,
            'entity_type' => 'school',
            'tier' => 'basic',
            'status' => 'draft',
        ]);
    }

    public function test_school_panel_pages_render_for_school_accounts(): void
    {
        $school = $this->school();

        $this->actingAs($school)->get('/school/dashboard')->assertOk();
        $this->actingAs($school)->get('/school/students')->assertOk();
        $this->actingAs($school)->get('/school/profile')->assertOk();
        $this->actingAs($school)->get('/school/teachers')->assertOk();
    }

    public function test_non_school_users_cannot_access_the_school_panel(): void
    {
        $teacher = User::factory()->create(['role' => 'user']);
        TeacherProfile::createDraftFor($teacher);

        $this->actingAs($teacher)->get('/school/dashboard')->assertForbidden();
        $this->actingAs($teacher)->get('/school/teachers')->assertForbidden();
    }

    public function test_school_accounts_are_redirected_from_teacher_urls_to_school_urls(): void
    {
        $school = $this->school();
        $this->actingAs($school)->get('/school/dashboard'); // provision profile

        $this->actingAs($school)->get('/teacher/dashboard')
            ->assertRedirect(route('school.dashboard'));
        $this->actingAs($school)->get('/teacher/students')
            ->assertRedirect(route('school.students.index'));
    }

    public function test_dashboard_route_sends_schools_to_the_school_panel(): void
    {
        $school = $this->school(['email_verified_at' => now()]);

        $this->actingAs($school)->get('/dashboard')->assertRedirect(route('school.dashboard'));
    }

    public function test_public_school_profile_is_served_under_schools_and_404s_under_teachers(): void
    {
        $school = $this->school();
        $profile = TeacherProfile::createDraftFor($school, TeacherProfile::ENTITY_SCHOOL);
        $profile->update(['status' => TeacherProfile::STATUS_APPROVED, 'public_profile' => true, 'approved_at' => now()]);

        $this->get('/schools/'.$profile->slug)->assertOk();
        $this->get('/teachers/'.$profile->slug)->assertNotFound();
    }

    public function test_teacher_profile_404s_under_schools_url(): void
    {
        $teacher = User::factory()->create(['role' => 'user']);
        $profile = TeacherProfile::createDraftFor($teacher);
        $profile->update(['status' => TeacherProfile::STATUS_APPROVED, 'public_profile' => true, 'approved_at' => now()]);

        $this->get('/teachers/'.$profile->slug)->assertOk();
        $this->get('/schools/'.$profile->slug)->assertNotFound();
    }

    public function test_moderation_index_filters_by_entity(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $school = $this->school();
        TeacherProfile::createDraftFor($school, TeacherProfile::ENTITY_SCHOOL);
        $teacher = User::factory()->create(['role' => 'user']);
        TeacherProfile::createDraftFor($teacher);

        $this->actingAs($admin)->get('/admin/teacher-profiles?entity=school')
            ->assertOk()
            ->assertViewHas('profiles', fn ($profiles) => $profiles->count() === 1
                && $profiles->first()->entity_type === 'school');
    }

    public function test_school_can_update_account_info_from_settings(): void
    {
        $school = $this->school();
        $this->actingAs($school)->get('/school/dashboard'); // provision profile

        $response = $this->actingAs($school)->put('/school/settings/account', [
            'name' => 'Harmony',
            'surname' => 'Academy',
            'username' => 'harmony-academy',
            'email' => $school->email,
        ]);

        $response->assertRedirect(route('school.settings'));
        $this->assertDatabaseHas('users', [
            'id' => $school->id,
            'name' => 'Harmony',
            'surname' => 'Academy',
            'username' => 'harmony-academy',
        ]);
    }

    public function test_account_update_rejects_a_taken_username(): void
    {
        User::factory()->create(['username' => 'taken-name']);
        $school = $this->school();
        $this->actingAs($school)->get('/school/dashboard');

        $this->actingAs($school)->put('/school/settings/account', [
            'name' => $school->name,
            'surname' => $school->surname,
            'username' => 'taken-name',
            'email' => $school->email,
        ])->assertSessionHasErrors('username');
    }

    public function test_changing_email_resets_verification(): void
    {
        Notification::fake();

        $school = $this->school(['email_verified_at' => now()]);
        $this->actingAs($school)->get('/school/dashboard');

        $this->actingAs($school)->put('/school/settings/account', [
            'name' => $school->name,
            'surname' => $school->surname,
            'username' => $school->username ?? 'school-account',
            'email' => 'new-address@example.com',
        ])->assertRedirect(route('school.settings'));

        $fresh = $school->fresh();
        $this->assertSame('new-address@example.com', $fresh->email);
        $this->assertNull($fresh->email_verified_at);
    }

    public function test_school_can_upload_a_cover_image(): void
    {
        $school = $this->school();
        $this->actingAs($school)->get('/school/dashboard'); // provision profile

        $response = $this->actingAs($school)->post('/school/profile/cover', [
            'cover' => UploadedFile::fake()->image('cover.jpg', 1600, 500),
        ]);

        $response->assertSessionHasNoErrors()->assertSessionHas('status', 'cover-updated');

        $path = $school->fresh()->teacherProfile->cover_image_path;
        $this->assertNotNull($path);
        $this->assertFileExists(public_path($path));
        @unlink(public_path($path)); // keep the real public/ dir clean
    }

    public function test_profile_saves_with_a_single_field_filled(): void
    {
        $school = $this->school();
        $this->actingAs($school)->get('/school/dashboard');

        $this->actingAs($school)->put('/school/profile', ['headline' => 'Sophia Music School'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'profile-updated');

        $this->assertSame('Sophia Music School', $school->fresh()->teacherProfile->headline);
    }

    public function test_profile_save_normalizes_scheme_less_urls(): void
    {
        $school = $this->school();
        $this->actingAs($school)->get('/school/dashboard');

        $this->actingAs($school)->put('/school/profile', [
            'website_url' => 'www.sophia-music.com',
            'social_links' => ['instagram' => 'instagram.com/sophiamusic'],
        ])->assertSessionHasNoErrors();

        $profile = $school->fresh()->teacherProfile;
        $this->assertSame('https://www.sophia-music.com', $profile->website_url);
        $this->assertSame('https://instagram.com/sophiamusic', $profile->social_links['instagram']);
    }

    public function test_school_role_cannot_create_a_teacher_entity_draft_via_become(): void
    {
        $school = $this->school(['email_verified_at' => now()]);

        $this->actingAs($school)->post('/teacher/become')->assertRedirect(route('school.dashboard'));

        $this->assertDatabaseMissing('teacher_profiles', [
            'user_id' => $school->id,
            'entity_type' => 'teacher',
        ]);
    }
}
