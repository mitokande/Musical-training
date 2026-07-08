@extends('admin.layouts.admin')
@section('page-title', $campaign->name)

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold text-gray-800">{{ $campaign->name }}</h2>
                <span class="text-xs px-2 py-1 rounded-full font-medium
                    @switch($campaign->status)
                        @case('sent') bg-green-100 text-green-700 @break
                        @case('sending') bg-blue-100 text-blue-700 @break
                        @case('scheduled') bg-amber-100 text-amber-700 @break
                        @case('failed') @case('cancelled') bg-red-100 text-red-700 @break
                        @default bg-gray-100 text-gray-600
                    @endswitch">{{ ucfirst($campaign->status) }}</span>
            </div>
            <p class="text-sm text-gray-500 mt-1">{{ $campaign->subject }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if ($campaign->isEditable())
                <a href="{{ route('admin.email-campaigns.edit', $campaign) }}" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Edit</a>
                <form method="POST" action="{{ route('admin.email-campaigns.send', $campaign) }}"
                      onsubmit="return confirm('Send this campaign to ~{{ $estimatedRecipients }} recipients now?')">
                    @csrf
                    <button class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">
                        <i data-lucide="send" class="w-4 h-4"></i> Send Now
                    </button>
                </form>
            @endif
            @if (in_array($campaign->status, ['scheduled', 'sending']))
                <form method="POST" action="{{ route('admin.email-campaigns.cancel', $campaign) }}"
                      onsubmit="return confirm('Cancel this campaign? Unsent emails will be skipped.')">
                    @csrf
                    <button class="px-4 py-2 text-sm border border-red-300 text-red-600 rounded-lg hover:bg-red-50">Cancel Campaign</button>
                </form>
            @endif
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
        @foreach ([
            ['label' => 'Recipients', 'value' => $campaign->total_recipients ?: ($estimatedRecipients !== null ? '~'.$estimatedRecipients : 0)],
            ['label' => 'Sent', 'value' => $campaign->sent_count],
            ['label' => 'Delivered', 'value' => $campaign->delivered_count.($campaign->deliveryRate() !== null ? ' ('.$campaign->deliveryRate().'%)' : '')],
            ['label' => 'Opened*', 'value' => $campaign->opened_count.($campaign->openRate() !== null ? ' ('.$campaign->openRate().'%)' : '')],
            ['label' => 'Clicked', 'value' => $campaign->clicked_count.($campaign->clickRate() !== null ? ' ('.$campaign->clickRate().'%)' : '')],
            ['label' => 'Bounced', 'value' => $campaign->bounced_count],
            ['label' => 'Complaints', 'value' => $campaign->complained_count],
        ] as $stat)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="text-xs font-semibold text-gray-500 uppercase">{{ $stat['label'] }}</div>
                <div class="text-xl font-bold text-gray-800 mt-1">{{ $stat['value'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-3">
            <h3 class="font-semibold text-gray-800">Details</h3>
            <dl class="text-sm space-y-2">
                <div class="flex justify-between"><dt class="text-gray-500">Template</dt><dd class="text-gray-800">{{ $campaign->template?->name ?? 'Custom HTML' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Created by</dt><dd class="text-gray-800">{{ $campaign->creator?->name ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Scheduled</dt><dd class="text-gray-800">{{ $campaign->scheduled_at?->format('M j, Y H:i') ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Started</dt><dd class="text-gray-800">{{ $campaign->started_at?->format('M j, Y H:i') ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Completed</dt><dd class="text-gray-800">{{ $campaign->completed_at?->format('M j, Y H:i') ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">UTM campaign</dt><dd class="text-gray-800 font-mono text-xs">{{ $campaign->slug }}</dd></div>
            </dl>
            <div class="pt-2 border-t border-gray-100">
                <h4 class="text-sm font-semibold text-gray-700 mb-2">Segment</h4>
                @forelse ($campaign->segment ?? [] as $key => $value)
                    <div class="text-xs text-gray-600"><span class="font-medium">{{ str_replace('_', ' ', $key) }}:</span> {{ is_array($value) ? implode(', ', $value) : $value }}</div>
                @empty
                    <div class="text-xs text-gray-400">All eligible users</div>
                @endforelse
            </div>
            <div class="pt-2 border-t border-gray-100">
                <h4 class="text-sm font-semibold text-gray-700 mb-2">Send a test</h4>
                <form method="POST" action="{{ route('admin.email-campaigns.test-send', $campaign) }}" class="flex gap-2">
                    @csrf
                    <input type="email" name="recipient" required placeholder="you@example.com" class="flex-1 rounded-lg border-gray-300 text-sm">
                    <button class="px-3 py-2 text-sm bg-gray-800 text-white rounded-lg hover:bg-gray-700">Send Test</button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100"><h3 class="font-semibold text-gray-800">Email Preview</h3></div>
            <iframe sandbox="" srcdoc="{{ app(\App\Services\EmailCenter\TemplateRenderer::class)->preview($campaign->htmlBody()) }}" class="w-full" style="height: 560px; border: 0;"></iframe>
        </div>
    </div>
    <p class="text-xs text-gray-400">* Open rate is an estimated metric — privacy protections and blocked tracking pixels affect results.</p>
</div>
@endsection
