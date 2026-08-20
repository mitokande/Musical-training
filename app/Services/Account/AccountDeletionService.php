<?php

namespace App\Services\Account;

use App\Models\Subscription;
use App\Models\TeacherProfile;
use App\Models\User;
use App\Services\Payments\SubscriptionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Soft account deletion.
 *
 * From the member's side deletion is final: they are logged out, cannot log
 * back in (the soft-delete scope hides them from the auth provider), their
 * public traces disappear, their API tokens are revoked and their e-mail /
 * username are released so the address can be used for a brand new account.
 *
 * Nothing is actually destroyed. The row survives behind `deleted_at` with
 * the original identity in `deleted_email` / `deleted_username`, so the admin
 * panel can still list, inspect and restore deleted accounts.
 */
class AccountDeletionService
{
    public function __construct(private SubscriptionService $subscriptions) {}

    /**
     * Delete an account.
     *
     * @param  User  $user  the account being deleted
     * @param  string|null  $reason  optional free-text reason the member gave
     * @param  User|null  $actor  the admin doing it; null when self-service
     */
    public function delete(User $user, ?string $reason = null, ?User $actor = null): void
    {
        if ($user->isDeleted()) {
            return;
        }

        DB::transaction(function () use ($user, $reason, $actor) {
            $meta = [
                'google_id' => $user->google_id,
                'plan' => $user->plan,
                'role' => $user->role,
            ];

            // A deleted account must stop being billed.
            $this->cancelSubscriptions($user);

            // Public teacher / school listings live in their own table, so the
            // soft-delete scope on users does not hide them — archive instead,
            // remembering the status so a restore can put it back.
            if ($profile = $user->teacherProfile) {
                $meta['teacher_profile_status'] = $profile->status;
                $profile->update(['status' => TeacherProfile::STATUS_ARCHIVED]);
            }

            // Kill every mobile / API session.
            $user->tokens()->delete();

            $user->forceFill([
                'deleted_email' => $user->email,
                'deleted_username' => $user->username,
                'deletion_reason' => $reason ? mb_substr($reason, 0, 500) : null,
                'deleted_by' => $actor?->id,
                'deletion_meta' => $meta,
                // Release the unique identity columns so the same person can
                // sign up again from scratch.
                'email' => $this->anonymisedEmail($user),
                'username' => $this->anonymisedUsername($user),
                'google_id' => null,
                'remember_token' => null,
                'suspended_at' => null,
            ])->save();

            $user->delete();

            activity('account')
                ->causedBy($actor ?? $user)
                ->performedOn($user)
                ->withProperties(['self_service' => is_null($actor)])
                ->log('account_deleted');
        });
    }

    /**
     * Bring a deleted account back (admin only).
     *
     * The original e-mail / username are only handed back if nobody has
     * claimed them in the meantime; otherwise the account is restored with
     * its anonymised identity and the admin can edit it by hand.
     */
    public function restore(User $user): void
    {
        if (! $user->isDeleted()) {
            return;
        }

        DB::transaction(function () use ($user) {
            $meta = $user->deletion_meta ?? [];

            $attributes = [
                'deleted_email' => null,
                'deleted_username' => null,
                'deletion_reason' => null,
                'deleted_by' => null,
                'deletion_meta' => null,
            ];

            if ($user->deleted_email && ! $this->identityTaken('email', $user->deleted_email, $user->id)) {
                $attributes['email'] = $user->deleted_email;
                $attributes['google_id'] = $meta['google_id'] ?? null;
            }

            if ($user->deleted_username && ! $this->identityTaken('username', $user->deleted_username, $user->id)) {
                $attributes['username'] = $user->deleted_username;
            }

            $user->forceFill($attributes)->save();
            $user->restore();

            if (($status = $meta['teacher_profile_status'] ?? null) && $profile = $user->teacherProfile) {
                $profile->update(['status' => $status]);
            }

            activity('account')
                ->causedBy(auth()->user())
                ->performedOn($user)
                ->log('account_restored');
        });
    }

    /**
     * Cancel any live subscription so a deleted account is never charged
     * again. Best-effort: a payment-provider hiccup must not block deletion.
     */
    private function cancelSubscriptions(User $user): void
    {
        $live = Subscription::where('user_id', $user->id)
            ->whereIn('status', ['active', 'trialing'])
            ->get();

        foreach ($live as $subscription) {
            try {
                $this->subscriptions->cancel($subscription, immediate: true);
            } catch (\Throwable $e) {
                Log::warning('Account deletion could not cancel subscription', [
                    'user_id' => $user->id,
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Placeholder address on the reserved `deleted.invalid` domain — never
     * routable, so nothing can ever be mailed to a deleted account.
     */
    private function anonymisedEmail(User $user): string
    {
        return 'deleted-user-'.$user->id.'@deleted.invalid';
    }

    private function anonymisedUsername(User $user): string
    {
        return 'deleted_user_'.$user->id;
    }

    /**
     * Unique columns have to be checked across trashed rows too — the unique
     * index does not care about `deleted_at`.
     */
    private function identityTaken(string $column, string $value, int $exceptId): bool
    {
        return User::withTrashed()
            ->where($column, $value)
            ->where('id', '!=', $exceptId)
            ->exists();
    }
}
