<x-mail::message>
# You're invited to Harmoniva 🎵

**{{ $teacherName }}** invited you to join Harmoniva as their student.

Harmoniva is a music education platform with ear training, music theory practice, and guided learning paths. Once connected, your teacher can assign you homework and follow your progress.

<x-mail::button :url="$acceptUrl">
Accept the invitation
</x-mail::button>

@if ($invitation->expires_at)
This invitation expires on {{ $invitation->expires_at->format('F j, Y') }}.
@endif

If you were not expecting this invitation, you can safely ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
