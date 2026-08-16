<div style="font-family:-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;font-size:15px;color:#1f2937;line-height:1.6;">
    <p style="margin:0 0 16px;"><strong>New contact form message</strong></p>

    <table cellpadding="0" cellspacing="0" style="margin:0 0 16px;font-size:14px;">
        <tr><td style="padding:2px 12px 2px 0;color:#6b7280;">Name</td><td>{{ $name }}</td></tr>
        <tr><td style="padding:2px 12px 2px 0;color:#6b7280;">Email</td><td><a href="mailto:{{ $email }}">{{ $email }}</a></td></tr>
        <tr><td style="padding:2px 12px 2px 0;color:#6b7280;">Topic</td><td>{{ $topic }}</td></tr>
    </table>

    <div style="white-space:pre-wrap;background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:16px;">{{ $body }}</div>

    <p style="margin:16px 0 0;font-size:13px;color:#6b7280;">
        Reply from the Support Inbox:
        <a href="{{ route('admin.support-inbox.show', $conversation) }}">conversation #{{ $conversation->id }}</a>
    </p>
</div>
