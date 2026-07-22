<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherProfile;
use App\Models\TeacherReview;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Reviews hub under the Members section: lists new sign-ups for control,
 * pending teacher/school profile approvals, and public review moderation.
 * Approve/reject/hide actions post to the existing teacher-profiles and
 * teacher-reviews moderation routes.
 */
class MemberReviewController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'members');
        if (! in_array($tab, ['members', 'unverified', 'approvals', 'reviews'], true)) {
            $tab = 'members';
        }

        $stats = [
            'new_members' => User::where('created_at', '>=', now()->subDays(7))->count(),
            'unverified' => User::whereNull('email_verified_at')->count(),
            'pending_profiles' => TeacherProfile::where('status', TeacherProfile::STATUS_SUBMITTED)->count(),
            'reported_reviews' => TeacherReview::whereNotNull('reported_at')->count(),
        ];

        $members = null;
        $pendingProfiles = null;
        $reviews = null;
        $filter = $request->query('filter', 'reported');

        if ($tab === 'members') {
            $members = User::with('teacherProfile')
                ->latest()
                ->paginate(20)
                ->withQueryString();
        } elseif ($tab === 'unverified') {
            $members = User::with('teacherProfile')
                ->whereNull('email_verified_at')
                ->latest()
                ->paginate(20)
                ->withQueryString();
        } elseif ($tab === 'approvals') {
            $pendingProfiles = TeacherProfile::with('user')
                ->where('status', TeacherProfile::STATUS_SUBMITTED)
                ->latest('submitted_at')
                ->latest('id')
                ->paginate(20)
                ->withQueryString();
        } else {
            $reviews = TeacherReview::with(['student', 'teacherProfile.user'])
                ->when($filter === 'reported', fn ($q) => $q->whereNotNull('reported_at'))
                ->when($filter === 'hidden', fn ($q) => $q->where('status', TeacherReview::STATUS_HIDDEN))
                ->orderByDesc('reported_at')
                ->orderByDesc('created_at')
                ->paginate(20)
                ->withQueryString();
        }

        return view('admin.member-reviews.index', compact(
            'tab', 'stats', 'members', 'pendingProfiles', 'reviews', 'filter'
        ));
    }
}
