<?php

namespace App\Http\Controllers;

use App\Models\TeacherBookingSetting;
use App\Models\TeacherProfile;
use App\Services\Teacher\TeacherSchedulingService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

/**
 * Public-profile booking: slot listing + appointment requests. Only approved
 * premium profiles with booking enabled are bookable.
 */
class TeacherBookingController extends Controller
{
    public function __construct(private TeacherSchedulingService $scheduling) {}

    /** General booking page: full calendar on top, booking rules below. */
    public function page(Request $request, string $slug): View
    {
        $profile = TeacherProfile::with('user')->where('slug', $slug)->firstOrFail();

        $viewer = $request->user();
        $isOwner = $viewer && $viewer->id === $profile->user_id;
        abort_unless($profile->isPubliclyVisible() || $isOwner || ($viewer && $viewer->isAdmin()), 404);

        $settings = TeacherBookingSetting::forTeacher($profile->user_id);

        return view('teachers.booking', [
            'profile' => $profile,
            'settings' => $settings,
            'bookingEnabled' => $profile->isPremiumTier() && $settings->booking_enabled,
        ]);
    }

    /** JSON slot list for a date (public profile booking panel). */
    public function slots(Request $request, string $slug)
    {
        $profile = $this->bookableProfile($slug);

        $request->validate(['date' => 'required|date']);

        $settings = TeacherBookingSetting::forTeacher($profile->user_id);
        $date = CarbonImmutable::parse($request->date, $settings->timezone);

        $slots = collect($this->scheduling->slotsFor($profile->user, $date))
            ->map(fn ($slot) => [
                'value' => $slot['starts_at']->toIso8601String(),
                'label' => $slot['starts_at']->format('H:i'),
            ]);

        return response()->json([
            'timezone' => $settings->timezone,
            'slots' => $slots,
        ]);
    }

    /** Authenticated student requests an appointment. */
    public function book(Request $request, string $slug)
    {
        $profile = $this->bookableProfile($slug);

        abort_if($request->user()->id === $profile->user_id, 403);

        $request->validate([
            'starts_at' => 'required|date',
            'topic' => 'nullable|string|max:255',
        ]);

        try {
            $this->scheduling->request(
                $profile->user,
                $request->user(),
                CarbonImmutable::parse($request->starts_at),
                $request->input('topic'),
            );
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['booking' => $e->getMessage()]);
        }

        return redirect()->route('my-appointments.index')->with('status', 'appointment-requested');
    }

    private function bookableProfile(string $slug): TeacherProfile
    {
        $profile = TeacherProfile::with('user')->where('slug', $slug)->firstOrFail();

        abort_unless($profile->isPubliclyVisible() && $profile->isPremiumTier(), 404);

        return $profile;
    }
}
