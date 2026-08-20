<?php

namespace Tests\Feature;

use App\Models\TeacherProfile;
use App\Models\User;
use App\Services\Account\AccountDeletionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Soft account deletion: gone for the member, still there for admins.
 */
class AccountDeletionTest extends TestCase
{
    use RefreshDatabase;

    private function deletions(): AccountDeletionService
    {
        return app(AccountDeletionService::class);
    }

    public function test_a_deleted_account_cannot_log_in_again(): void
    {
        $user = User::factory()->create(['password' => Hash::make('password')]);
        $email = $user->email;

        $this->deletions()->delete($user);

        $this->post('/login', ['email' => $email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_deletion_releases_the_email_and_username_for_a_new_signup(): void
    {
        $user = User::factory()->create();
        $email = $user->email;
        $username = $user->username;

        $this->deletions()->delete($user);

        $user->refresh();
        $this->assertNotSame($email, $user->email);
        $this->assertSame($email, $user->deleted_email);
        $this->assertSame($username, $user->deleted_username);

        // The freed address can be used to open a brand new account.
        $fresh = User::factory()->create(['email' => $email, 'username' => $username]);
        $this->assertNotSame($user->id, $fresh->id);
    }

    public function test_deletion_revokes_api_tokens_and_archives_a_teacher_profile(): void
    {
        $user = User::factory()->create(['role' => 'teacher']);
        $user->createToken('mobile');
        $profile = TeacherProfile::create([
            'user_id' => $user->id,
            'slug' => 'test-teacher-'.$user->id,
            'display_name' => 'Test Teacher',
            'status' => TeacherProfile::STATUS_APPROVED,
            'entity_type' => TeacherProfile::ENTITY_TEACHER,
        ]);

        $this->deletions()->delete($user);

        $this->assertSame(0, $user->tokens()->count());
        $this->assertSame(TeacherProfile::STATUS_ARCHIVED, $profile->fresh()->status);
    }

    public function test_admin_can_see_and_restore_a_deleted_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $user = User::factory()->create(['name' => 'Deleted Member']);
        $email = $user->email;

        $this->deletions()->delete($user, 'Too expensive');

        // The deleted tab lists it; the normal tabs do not.
        $this->actingAs($admin)->get(route('admin.users.index', ['segment' => 'deleted']))
            ->assertOk()
            ->assertSee('Deleted Member')
            ->assertSee($email);

        $this->actingAs($admin)->get(route('admin.users.index'))
            ->assertOk()
            ->assertDontSee('Deleted Member');

        // The detail page still opens, reason included.
        $this->actingAs($admin)->get(route('admin.users.show', $user->id))
            ->assertOk()
            ->assertSee('Too expensive');

        $this->actingAs($admin)->post(route('admin.users.restore', $user->id))
            ->assertRedirect();

        $user->refresh();
        $this->assertNull($user->deleted_at);
        $this->assertSame($email, $user->email);
        $this->assertNull($user->deleted_email);
    }

    public function test_restore_keeps_the_placeholder_email_when_the_old_one_was_taken(): void
    {
        $user = User::factory()->create();
        $email = $user->email;

        $this->deletions()->delete($user);
        User::factory()->create(['email' => $email]);

        $this->deletions()->restore($user);

        $user->refresh();
        $this->assertNull($user->deleted_at);
        $this->assertNotSame($email, $user->email);
    }

    public function test_admin_deleting_a_member_soft_deletes_and_records_the_actor(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $user = User::factory()->create();

        $this->actingAs($admin)->delete(route('admin.users.destroy', $user->id))
            ->assertRedirect(route('admin.users.index'));

        $this->assertSoftDeleted('users', ['id' => $user->id, 'deleted_by' => $admin->id]);
    }

    public function test_permanent_erasure_is_only_available_for_deleted_accounts(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $user = User::factory()->create();

        $this->actingAs($admin)->from(route('admin.users.index'))
            ->delete(route('admin.users.force-delete', $user->id))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $user->id]);

        $this->deletions()->delete($user);

        $this->actingAs($admin)->delete(route('admin.users.force-delete', $user->id))
            ->assertRedirect(route('admin.users.index', ['segment' => 'deleted']));

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
