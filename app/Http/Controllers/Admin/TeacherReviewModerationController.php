<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherReview;
use Illuminate\Http\Request;

class TeacherReviewModerationController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'reported');

        $reviews = TeacherReview::with(['student', 'teacherProfile.user'])
            ->when($filter === 'reported', fn ($q) => $q->whereNotNull('reported_at'))
            ->when($filter === 'hidden', fn ($q) => $q->where('status', TeacherReview::STATUS_HIDDEN))
            ->orderByDesc('reported_at')
            ->orderByDesc('created_at')
            ->paginate(30);

        return view('admin.teacher-reviews.index', [
            'reviews' => $reviews,
            'filter' => $filter,
        ]);
    }

    public function hide(TeacherReview $review)
    {
        $review->update(['status' => TeacherReview::STATUS_HIDDEN]);

        return back()->with('success', 'Review hidden.');
    }

    public function approve(TeacherReview $review)
    {
        $review->update([
            'status' => TeacherReview::STATUS_APPROVED,
            'reported_at' => null,
            'report_reason' => null,
        ]);

        return back()->with('success', 'Review approved.');
    }

    public function destroy(TeacherReview $review)
    {
        $review->delete();

        return back()->with('success', 'Review removed.');
    }
}
