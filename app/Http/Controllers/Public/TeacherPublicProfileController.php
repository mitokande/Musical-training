<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\FeedItem;
use App\Models\TeacherBookingSetting;
use App\Models\TeacherProfile;
use App\Models\TeacherProfileView;
use App\Models\TeacherReview;
use App\Models\TeacherStudentRelationship;
use App\Services\Teacher\TeacherSchedulingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherPublicProfileController extends Controller
{
    public function show(Request $request, string $slug): View
    {
        $profile = TeacherProfile::with([
            'user', 'educations', 'instruments',
            'services' => fn ($q) => $q->active(),
            'videos', 'media',
            'paymentLinks' => fn ($q) => $q->active(),
        ])->where('slug', $slug)->firstOrFail();

        $viewer = $request->user();
        $isOwner = $viewer && $viewer->id === $profile->user_id;
        $isAdmin = $viewer && $viewer->isAdmin();

        // Unapproved / hidden profiles are only visible to the owner and admins.
        if (! $profile->isPubliclyVisible()) {
            abort_unless($isOwner || $isAdmin, 404);
        } else {
            // Lifts the site-wide noindex (see NoIndex middleware).
            $request->attributes->set('allow_indexing', true);
            $this->recordView($request, $profile, $isOwner);
        }

        $reviews = TeacherReview::with('student')
            ->approved()
            ->where('teacher_profile_id', $profile->id)
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        // Current or former approved students may write (or update) a review.
        $canReview = $viewer
            && $viewer->id !== $profile->user_id
            && TeacherStudentRelationship::where('teacher_id', $profile->user_id)
                ->where('student_id', $viewer->id)
                ->whereNotNull('approved_at')
                ->exists();

        $bookingEnabled = $profile->isPremiumTier()
            && TeacherBookingSetting::where('teacher_id', $profile->user_id)
                ->where('booking_enabled', true)
                ->exists();

        return view('teachers.show', [
            'profile' => $profile,
            'isPreview' => ! $profile->isPubliclyVisible(),
            'articles' => $profile->user->articles()->published()->latest('published_at')->limit(12)->get(),
            'reviews' => $reviews,
            'reviewStats' => [
                'count' => $reviews->count(),
                'average' => $reviews->isNotEmpty() ? round($reviews->avg('rating'), 1) : null,
            ],
            'canReview' => $canReview,
            'myReview' => $canReview
                ? TeacherReview::where('teacher_profile_id', $profile->id)->where('student_id', $viewer->id)->first()
                : null,
            'bookingEnabled' => $bookingEnabled,
            'bookingDays' => app(TeacherSchedulingService::class)->bookingDaysGrid($profile->user),
            'feedItems' => FeedItem::with(['actor', 'subject'])
                ->where('user_id', $profile->user_id)
                ->latest()
                ->limit(30)
                ->get(),
        ]);
    }

    /** Count at most one view per visitor per profile per day; never the owner. */
    private function recordView(Request $request, TeacherProfile $profile, bool $isOwner): void
    {
        if ($isOwner) {
            return;
        }

        $ipHash = hash('sha256', $request->ip().'|'.config('app.key'));

        $created = TeacherProfileView::firstOrCreateForToday($profile, $request->user()?->id, $ipHash);

        if ($created) {
            $profile->increment('view_count');
        }
    }
}
