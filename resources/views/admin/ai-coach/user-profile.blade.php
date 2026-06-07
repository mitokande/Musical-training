@extends('admin.layouts.admin')

@section('page-title', 'AI User Profile')

@section('content')
<div class="space-y-6">

    {{-- Back Link --}}
    <a href="{{ route('admin.ai-coach-admin.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-purple-600 transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to AI Profiles
    </a>

    {{-- User Info Header --}}
    <div class="card p-6">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-gradient-to-br from-purple-500 to-orange-500 flex items-center justify-center text-white text-xl font-bold">
                {{ substr($user->name ?? 'U', 0, 1) }}
            </div>
            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ $user->name }}</h2>
                <p class="text-sm text-gray-500">{{ $user->email }}</p>
                <p class="text-xs text-gray-400 mt-1">Joined {{ $user->created_at->format('M d, Y') }}</p>
            </div>
        </div>
    </div>

    {{-- Questionnaire Responses --}}
    <div class="card p-6">
        <div class="flex items-center gap-2 mb-6">
            <i data-lucide="clipboard-list" class="w-5 h-5 text-purple-600"></i>
            <h2 class="text-lg font-semibold text-gray-900">Questionnaire Responses</h2>
        </div>

        @forelse($responses ?? [] as $response)
        <div class="mb-4 p-4 bg-gray-50 rounded-lg">
            <p class="text-sm font-semibold text-gray-700 mb-1">{{ $response->question_text ?? $response->question ?? 'Question' }}</p>
            <p class="text-sm text-gray-600">{{ $response->answer ?? 'No answer provided' }}</p>
        </div>
        @empty
        <div class="text-center py-6">
            <i data-lucide="inbox" class="w-8 h-8 text-gray-300 mx-auto mb-2"></i>
            <p class="text-sm text-gray-500">No questionnaire responses yet.</p>
        </div>
        @endforelse
    </div>

    {{-- Practice Performance Summary --}}
    <div class="card p-6">
        <div class="flex items-center gap-2 mb-6">
            <i data-lucide="trophy" class="w-5 h-5 text-orange-500"></i>
            <h2 class="text-lg font-semibold text-gray-900">Practice Performance</h2>
        </div>

        @if($practiceStats ?? false)
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="text-center p-4 bg-purple-50 rounded-lg">
                <p class="text-2xl font-bold text-purple-700">{{ $practiceStats->total_exercises ?? 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">Total Exercises</p>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-lg">
                <p class="text-2xl font-bold text-green-700">{{ $practiceStats->avg_score ?? 0 }}%</p>
                <p class="text-xs text-gray-500 mt-1">Average Score</p>
            </div>
            <div class="text-center p-4 bg-orange-50 rounded-lg">
                <p class="text-2xl font-bold text-orange-700">{{ $practiceStats->streak ?? 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">Day Streak</p>
            </div>
        </div>
        @else
        <p class="text-sm text-gray-500 text-center py-4">No practice data available.</p>
        @endif
    </div>

    {{-- AI Coaching Sessions History --}}
    <div class="card p-6">
        <div class="flex items-center gap-2 mb-6">
            <i data-lucide="brain" class="w-5 h-5 text-purple-600"></i>
            <h2 class="text-lg font-semibold text-gray-900">AI Coaching Sessions</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Date</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Model</th>
                        <th class="text-right py-3 px-4 font-semibold text-gray-600">Tokens</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($sessions ?? [] as $session)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="py-3 px-4 text-gray-600">{{ $session->created_at->format('M d, Y H:i') }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-700">{{ $session->model ?? 'N/A' }}</span>
                        </td>
                        <td class="py-3 px-4 text-right font-medium text-gray-900">{{ number_format($session->total_tokens ?? 0) }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">{{ ucfirst($session->status ?? 'completed') }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-8 text-center text-gray-500">No coaching sessions yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($sessions ?? collect(), 'links'))
        <div class="mt-4">{{ $sessions->links() }}</div>
        @endif
    </div>
</div>
@endsection
