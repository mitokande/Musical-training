<x-mail::message>
# You're invited to Harmoniva 🎵

**{{ $schoolName }}** invited you to join their music school on Harmoniva as a teacher.

Harmoniva is a music education platform with ear training, music theory practice, and guided learning paths. As a member teacher you get the full teacher toolset — students, classes, assignments, messaging and a booking calendar — and your school can support you in managing your students.

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
