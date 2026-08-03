<?php

namespace App\Http\Controllers;

use App\Models\TeacherAppointment;
use App\Services\Teacher\TeacherCapabilityService;
use App\Services\Zoom\ZoomClient;
use App\Services\Zoom\ZoomSdkSignature;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * The embedded Lesson Room — one route for both sides of a lesson; the
 * controller decides who is the Zoom host.
 *
 * The Meeting SDK credentials are deliberately NOT rendered into the page.
 * signature() is a separate authenticated POST that re-runs every guard and
 * mints them on demand, so a bookmarked or shared room URL is worthless and the
 * teacher's ZAK is created only at the moment they actually open the room.
 */
class LessonRoomController extends Controller
{
    public function __construct(
        private TeacherCapabilityService $capabilities,
        private ZoomSdkSignature $signer,
        private ZoomClient $zoom,
    ) {}

    public function show(Request $request, TeacherAppointment $appointment)
    {
        $this->authorizeRoom($request, $appointment);

        $appointment->load(['teacher', 'student', 'zoomMeeting']);
        $user = $request->user();
        $isTeacher = $user->id === $appointment->teacher_id;

        return view('lessons.room', [
            'appointment' => $appointment,
            'isTeacher' => $isTeacher,
            'counterpart' => $isTeacher ? $appointment->student : $appointment->teacher,
            'open' => $appointment->roomIsOpen(),
            'meetingNumber' => $appointment->zoomMeeting?->zoom_meeting_id,
            // The room lives outside the /teacher and /school route namespaces,
            // so crm_route() cannot infer the right one from the request — the
            // viewer's own CRM namespace decides.
            'links' => $isTeacher ? [
                'back' => route($user->crmRouteName('calendar.index')),
                'student' => route($user->crmRouteName('students.show'), $appointment->student_id),
                // Recipients are picked at send time, not at create, so there
                // is nothing to prefill here — this is just a fast way in.
                'assign' => route($user->crmRouteName('assignments.create')),
                'note' => route($user->crmRouteName('appointments.note'), $appointment),
            ] : [
                'back' => route('my-appointments.index'),
            ],
        ]);
    }

    /**
     * Mint the short-lived join credentials for this user. Returns 403 rather
     * than a signature whenever the room is not currently joinable, so the
     * window is enforced at the credential boundary and not just in the UI.
     */
    public function signature(Request $request, TeacherAppointment $appointment): JsonResponse
    {
        $this->authorizeRoom($request, $appointment);

        $meeting = $appointment->zoomMeeting;

        if (! $meeting || ! $meeting->isActive() || ! $appointment->roomIsOpen()) {
            return response()->json(['message' => __('teacher.lesson_room.not_open')], 403);
        }

        if (! $this->signer->configured()) {
            return response()->json(['message' => __('teacher.lesson_room.unavailable')], 503);
        }

        $isTeacher = $request->user()->id === $appointment->teacher_id;

        $payload = [
            'signature' => $this->signer->generate(
                $meeting->zoom_meeting_id,
                $isTeacher ? ZoomSdkSignature::ROLE_HOST : ZoomSdkSignature::ROLE_PARTICIPANT,
            ),
            'sdkKey' => (string) config('services.zoom.sdk_key'),
            'meetingNumber' => $meeting->zoom_meeting_id,
            'passcode' => (string) $meeting->passcode,
            'userName' => trim($request->user()->name.' '.$request->user()->surname),
            'userEmail' => $request->user()->email,
        ];

        // Only the teacher gets a ZAK, and only right now — it is the credential
        // that starts the meeting as the host account.
        if ($isTeacher && $meeting->host) {
            try {
                $payload['zak'] = $this->zoom->zakFor($meeting->host->zoom_user_id);
            } catch (\Throwable $e) {
                Log::error('Failed to fetch a Zoom ZAK for a lesson host.', [
                    'appointment_id' => $appointment->id,
                    'exception' => $e->getMessage(),
                ]);

                return response()->json(['message' => __('teacher.lesson_room.unavailable')], 503);
            }
        }

        return response()->json($payload);
    }

    /**
     * Both parties may enter; nobody else may. A teacher additionally needs the
     * premium capability — losing premium must close the room, not just hide
     * the button.
     */
    private function authorizeRoom(Request $request, TeacherAppointment $appointment): void
    {
        $user = $request->user();

        abort_unless($appointment->involves($user), 403);
        abort_unless($appointment->isConfirmed(), 404);

        if ($user->id === $appointment->teacher_id) {
            abort_unless($this->capabilities->canHostLiveLessons($user), 403);
        }
    }
}
