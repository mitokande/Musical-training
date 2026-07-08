@extends('admin.layouts.admin')
@section('page-title', 'Email Settings')

@section('content')
<div class="space-y-6 max-w-4xl">
    <h2 class="text-2xl font-bold text-gray-800">Email Settings</h2>

    {{-- SES status --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2"><i data-lucide="cloud" class="w-4 h-4 text-indigo-600"></i> Amazon SES Status</h3>
        @if ($ses['ok'])
            <dl class="grid md:grid-cols-2 gap-x-8 gap-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Region</dt><dd class="text-gray-800 font-mono text-xs">{{ $ses['region'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Sender domain ({{ $ses['domain'] }})</dt>
                    <dd>@if ($ses['domain_verified'])<span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700">Verified</span>@else<span class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-700">Not verified</span>@endif</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">24h quota</dt><dd class="text-gray-800">{{ number_format($ses['sent_last_24_hours']) }} / {{ number_format($ses['max_24_hour_send']) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Max send rate</dt><dd class="text-gray-800">{{ $ses['max_send_rate'] }}/second</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Configuration set ({{ $ses['configuration_set'] }})</dt>
                    <dd>@if ($ses['configuration_set_exists'])<span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700">Active</span>@else<span class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-700">Missing</span>@endif</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Event types</dt><dd class="text-gray-800 text-xs">{{ implode(', ', $ses['event_types']) ?: '—' }}</dd></div>
                <div class="flex justify-between md:col-span-2"><dt class="text-gray-500">SNS topic</dt><dd class="text-gray-800 font-mono text-xs break-all">{{ $ses['topic_arn'] ?? 'Not configured' }}</dd></div>
            </dl>
        @else
            <p class="text-sm text-red-600">SES status could not be loaded: {{ $ses['error'] }}</p>
        @endif
        <p class="text-xs text-gray-400 mt-4">AWS credentials and webhook secrets are managed in the server environment configuration and are never shown here.</p>
    </div>

    {{-- Addresses --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2"><i data-lucide="at-sign" class="w-4 h-4 text-indigo-600"></i> Addresses & Support Inbox</h3>
        <dl class="grid md:grid-cols-2 gap-x-8 gap-y-2 text-sm">
            <div class="flex justify-between"><dt class="text-gray-500">Default sender</dt><dd class="text-gray-800">{{ $fromAddress }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500">Reply-to</dt><dd class="text-gray-800">{{ $replyTo }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500">Support address</dt><dd class="text-gray-800">{{ $supportAddress }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500">Support inbound mode</dt>
                <dd><span class="text-xs px-2 py-0.5 rounded-full {{ $supportMode === 'imap' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                    {{ ['imap' => 'Local IMAP (this server)', 'external' => 'External mail provider', 'disabled' => 'Disabled'][$supportMode] ?? $supportMode }}
                </span></dd></div>
        </dl>
        <p class="text-xs text-gray-400 mt-3">
            Support inbox: local mail server (MX unchanged) · Campaign and automation emails: Amazon SES.
            <a href="{{ $webmailUrl }}" target="_blank" rel="noopener" class="text-indigo-600 hover:underline">Open webmail ↗</a>
        </p>
    </div>

    {{-- Editable settings --}}
    <form method="POST" action="{{ route('admin.email-center.settings.update') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
        @csrf @method('PUT')
        <h3 class="font-semibold text-gray-800 flex items-center gap-2"><i data-lucide="sliders-horizontal" class="w-4 h-4 text-indigo-600"></i> Sending Rules</h3>
        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Frequency cap <span class="text-gray-400">(marketing emails / user / week)</span></label>
                <input type="number" name="email_frequency_cap" value="{{ $settings['email_frequency_cap'] }}" min="0" max="50" class="w-full rounded-lg border-gray-300 text-sm">
                <p class="text-xs text-gray-400 mt-1">0 = unlimited</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Send rate <span class="text-gray-400">(emails / second)</span></label>
                <input type="number" name="email_send_rate" value="{{ $settings['email_send_rate'] }}" min="1" max="14" class="w-full rounded-lg border-gray-300 text-sm">
                <p class="text-xs text-gray-400 mt-1">SES account limit: 14/s</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Test recipient</label>
                <input type="email" name="email_test_recipient" value="{{ $settings['email_test_recipient'] }}" class="w-full rounded-lg border-gray-300 text-sm">
            </div>
        </div>
        <div class="flex justify-end">
            <button class="px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">Save Settings</button>
        </div>
    </form>
</div>
@endsection
