<?php

namespace App\Services\EmailCenter;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Translates a campaign's segment definition (JSON) into a user query.
 * Marketing prerequisites (verified email, not suspended/restricted, not
 * suppressed) are always applied on top of the admin's filters.
 */
class SegmentBuilder
{
    public function query(array $segment): Builder
    {
        $query = User::query()
            ->whereNotNull('email_verified_at')
            ->whereNull('suspended_at')
            ->where('is_restricted', false)
            ->whereNotIn(DB::raw('LOWER(email)'), function ($sub) {
                $sub->select('email')->from('email_suppressions');
            });

        if (! empty($segment['plans'])) {
            $query->whereIn('plan', (array) $segment['plans']);
        }

        if (! empty($segment['roles'])) {
            $query->whereIn('role', (array) $segment['roles']);
        }

        if (! empty($segment['locales'])) {
            $query->whereIn('locale', (array) $segment['locales']);
        }

        switch ($segment['activity'] ?? 'any') {
            case 'active_7':
                $query->where('last_active_at', '>=', now()->subDays(7));
                break;
            case 'active_30':
                $query->where('last_active_at', '>=', now()->subDays(30));
                break;
            case 'inactive_30':
                $query->where(fn ($q) => $q->whereNull('last_active_at')->orWhere('last_active_at', '<', now()->subDays(30)));
                break;
            case 'inactive_90':
                $query->where(fn ($q) => $q->whereNull('last_active_at')->orWhere('last_active_at', '<', now()->subDays(90)));
                break;
        }

        if (! empty($segment['registered_within_days'])) {
            $query->where('created_at', '>=', now()->subDays((int) $segment['registered_within_days']));
        }

        if (! empty($segment['registered_before_days'])) {
            $query->where('created_at', '<', now()->subDays((int) $segment['registered_before_days']));
        }

        if (! empty($segment['has_learning_path'])) {
            $query->whereExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from('user_learning_path_progress')
                    ->whereColumn('user_learning_path_progress.user_id', 'users.id');
            });
        }

        return $query;
    }

    public function count(array $segment): int
    {
        return $this->query($segment)->count();
    }
}
