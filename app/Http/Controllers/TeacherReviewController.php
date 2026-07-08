<?php

namespace App\Http\Controllers;

use App\Models\TeacherProfile;
use App\Models\TeacherReview;
use App\Models\TeacherStudentRelationship;
use App\Notifications\Teacher\TeacherReviewReceived;
use Illuminate\Http\Request;

/**
 * Public teacher reviews. Only users with a current or former approved
 * relationship with the teacher may review; one review per student.
 */
class TeacherReviewController extends Controller
{
    public function store(Request $request, string $slug)
    {
        $profile = TeacherProfile::with('user')->where('slug', $slug)->firstOrFail();
        abort_unless($profile->isPubliclyVisible(), 404);
        abort_if($request->user()->id === $profile->user_id, 403);

        // Verified current or former student: relationship that was approved at least once.
        $eligible = TeacherStudentRelationship::where('teacher_id', $profile->user_id)
            ->where('student_id', $request->user()->id)
            ->whereNotNull('approved_at')
            ->exists();

        abort_unless($eligible, 403, __('teacher.reviews.error_not_eligible'));

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'body' => 'nullable|string|max:2000',
        ]);

        $review = TeacherReview::updateOrCreate(
            ['teacher_profile_id' => $profile->id, 'student_id' => $request->user()->id],
            [
                'rating' => $validated['rating'],
                'body' => $validated['body'] ?? null,
                'status' => TeacherReview::STATUS_APPROVED,
            ],
        );

        if ($review->wasRecentlyCreated) {
            $profile->user->notify(new TeacherReviewReceived($review));
        }

        return back()->with('status', 'review-saved');
    }

    /** Teacher reports an abusive review for admin moderation. */
    public function report(Request $request, TeacherReview $review)
    {
        abort_unless($review->teacherProfile->user_id === $request->user()->id, 403);

        $request->validate(['reason' => 'nullable|string|max:500']);

        $review->update([
            'reported_at' => now(),
            'report_reason' => $request->input('reason'),
        ]);

        return back()->with('status', 'review-reported');
    }
}
