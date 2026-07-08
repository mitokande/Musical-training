<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ config('app.name') }} — Email Preferences</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f9fafb; margin: 0; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card { background: #fff; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,.06); padding: 40px; max-width: 440px; width: 90%; text-align: center; }
        h1 { font-size: 20px; color: #111827; margin: 0 0 12px; }
        p { color: #6b7280; font-size: 14px; line-height: 1.6; }
        .email { color: #111827; font-weight: 600; }
        button { background: #4f46e5; color: #fff; border: 0; border-radius: 10px; padding: 12px 28px; font-size: 15px; font-weight: 600; cursor: pointer; margin-top: 16px; }
        button:hover { background: #4338ca; }
        .success { color: #059669; font-weight: 600; margin-top: 8px; }
        a { color: #4f46e5; }
    </style>
</head>
<body>
    <div class="card">
        <h1>{{ config('app.name') }}</h1>
        @if ($alreadyUnsubscribed)
            <p class="success">You have been unsubscribed.</p>
            <p>The address <span class="email">{{ $message->recipient_email }}</span> will no longer receive marketing emails from {{ config('app.name') }}. Account and support emails are not affected.</p>
            <p><a href="{{ config('app.url') }}">Back to {{ config('app.name') }}</a></p>
        @else
            <p>Unsubscribe <span class="email">{{ $message->recipient_email }}</span> from {{ config('app.name') }} marketing emails?</p>
            <p>You will still receive account-related emails such as password resets and support replies.</p>
            <form method="POST" action="{{ url()->signedRoute('email.unsubscribe', ['token' => $message->tracking_token]) }}">
                <button type="submit">Unsubscribe</button>
            </form>
        @endif
    </div>
</body>
</html>
