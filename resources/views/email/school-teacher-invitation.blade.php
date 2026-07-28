<x-mail::message>
# {{ __('notifications.invite.heading') }}

{!! __('notifications.invite.school_intro', ['name' => $schoolName]) !!}

{{ __('notifications.invite.school_body') }}

<x-mail::button :url="$acceptUrl">
{{ __('notifications.invite.accept') }}
</x-mail::button>

@if ($invitation->expires_at)
{{ __('notifications.invite.expires', ['date' => $invitation->expires_at->format('F j, Y')]) }}
@endif

{{ __('notifications.invite.ignore') }}

{{ __('notifications.invite.thanks') }}<br>
{{ config('app.name') }}
</x-mail::message>
