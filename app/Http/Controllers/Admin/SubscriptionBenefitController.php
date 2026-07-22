<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\TeacherSubscriptionBenefit;
use App\Models\User;
use App\Services\Teacher\TeacherSubscriptionBenefitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Payments → Premium Incentives.
 *
 * Tracks the premium-student incentive program: teachers with 10+ active
 * Premium students use Harmoniva free automatically; schools reaching 20+
 * Premium students get a pending free-period grant that an admin approves
 * from this screen.
 */
class SubscriptionBenefitController extends Controller
{
    public function __construct(
        private TeacherSubscriptionBenefitService $benefits,
    ) {}

    public function index(Request $request): View
    {
        $pendingSchools = TeacherSubscriptionBenefit::query()
            ->pending()
            ->with('user.teacherProfile')
            ->latest('id')
            ->get();

        $query = TeacherSubscriptionBenefit::query()
            ->with('user.teacherProfile')
            ->latest('id');

        if ($search = $request->string('search')->trim()->value()) {
            $query->whereHas('user', fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"));
        }

        if ($status = $request->string('status')->value()) {
            $query->where('status', $status);
        }

        if ($type = $request->string('type')->value()) {
            $query->where('type', $type);
        }

        if ($role = $request->string('role')->value()) {
            $query->whereHas('user', function ($q) use ($role) {
                $role === 'school'
                    ? $q->where('role', 'school')
                    : $q->where('role', '!=', 'school');
            });
        }

        $benefits = $query->paginate(25)->withQueryString();

        $stats = [
            'pending_schools' => $pendingSchools->count(),
            'active_free' => TeacherSubscriptionBenefit::active()
                ->where('type', TeacherSubscriptionBenefit::TYPE_FREE_PERIOD)->count(),
            'active_discount' => TeacherSubscriptionBenefit::active()
                ->where('type', TeacherSubscriptionBenefit::TYPE_DISCOUNT)->count(),
            'settings' => [
                'enabled' => $this->benefits->isEnabled(),
                'teacher_free_threshold' => $this->benefits->freePeriodThreshold(),
                'school_free_threshold' => (int) SystemSetting::get('school_free_subscription_student_threshold', 20),
                'discount_threshold' => $this->benefits->discountThreshold(),
                'discount_percentage' => $this->benefits->discountPercentage(),
                'free_months' => $this->benefits->freePeriodMonths(),
            ],
        ];

        return view('admin.incentives.index', compact('benefits', 'pendingSchools', 'stats'));
    }

    public function approve(Request $request, TeacherSubscriptionBenefit $benefit): RedirectResponse
    {
        if ($benefit->status !== TeacherSubscriptionBenefit::STATUS_PENDING) {
            return back()->with('error', 'Only pending grants can be approved.');
        }

        $this->benefits->approve($benefit, $request->user());

        return back()->with('success', "Free period approved for {$benefit->user->name}.");
    }

    public function revoke(Request $request, TeacherSubscriptionBenefit $benefit): RedirectResponse
    {
        if (! in_array($benefit->status, [
            TeacherSubscriptionBenefit::STATUS_ACTIVE,
            TeacherSubscriptionBenefit::STATUS_PENDING,
        ], true)) {
            return back()->with('error', 'Only active or pending benefits can be revoked.');
        }

        $this->benefits->revoke($benefit, $request->user(), $request->string('reason')->value() ?: null);

        return back()->with('success', "Benefit revoked for {$benefit->user->name}.");
    }

    public function recalculate(User $user): RedirectResponse
    {
        $this->benefits->recalculate($user);

        return back()->with('success', "Benefits recalculated for {$user->name}.");
    }
}
