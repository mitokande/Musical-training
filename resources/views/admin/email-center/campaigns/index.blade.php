@extends('admin.layouts.admin')
@section('page-title', 'Email Campaigns')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">Email Campaigns</h2>
        <a href="{{ route('admin.email-campaigns.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
            <i data-lucide="plus" class="w-4 h-4"></i> New Campaign
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Campaign</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Recipients</th>
                        <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Delivered</th>
                        <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Opens*</th>
                        <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Clicks</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Scheduled</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($campaigns as $campaign)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3">
                                <a href="{{ route('admin.email-campaigns.show', $campaign) }}" class="font-medium text-indigo-600 hover:underline">{{ $campaign->name }}</a>
                                <div class="text-xs text-gray-500">{{ $campaign->subject }}</div>
                            </td>
                            <td class="px-6 py-3">
                                <span class="text-xs px-2 py-1 rounded-full font-medium
                                    @switch($campaign->status)
                                        @case('sent') bg-green-100 text-green-700 @break
                                        @case('sending') bg-blue-100 text-blue-700 @break
                                        @case('scheduled') bg-amber-100 text-amber-700 @break
                                        @case('failed') @case('cancelled') bg-red-100 text-red-700 @break
                                        @default bg-gray-100 text-gray-600
                                    @endswitch">{{ ucfirst($campaign->status) }}</span>
                            </td>
                            <td class="px-6 py-3 text-right">{{ number_format($campaign->total_recipients) }}</td>
                            <td class="px-6 py-3 text-right">{{ number_format($campaign->delivered_count) }}{{ $campaign->deliveryRate() !== null ? ' ('.$campaign->deliveryRate().'%)' : '' }}</td>
                            <td class="px-6 py-3 text-right">{{ number_format($campaign->opened_count) }}{{ $campaign->openRate() !== null ? ' ('.$campaign->openRate().'%)' : '' }}</td>
                            <td class="px-6 py-3 text-right">{{ number_format($campaign->clicked_count) }}{{ $campaign->clickRate() !== null ? ' ('.$campaign->clickRate().'%)' : '' }}</td>
                            <td class="px-6 py-3 text-gray-500">{{ $campaign->scheduled_at?->format('M j, Y H:i') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-10 text-center text-gray-500">No campaigns yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-3 border-t border-gray-100">{{ $campaigns->links() }}</div>
    </div>
    <p class="text-xs text-gray-400">* Open rate is an estimated metric — privacy protections and blocked tracking pixels affect results.</p>
</div>
@endsection
