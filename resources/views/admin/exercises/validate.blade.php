@extends('admin.layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center">
                    <i data-lucide="shield-check" class="w-5 h-5 text-purple-600"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Exercise Validation</h1>
            </div>
            <p class="text-gray-500">Validate and repair all interval practice questions for data consistency.</p>
        </div>
        <div class="flex gap-3">
            <form action="{{ route('admin.exercises.repair') }}" method="POST">
                @csrf
                <input type="hidden" name="dry_run" value="1">
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <i data-lucide="eye" class="w-4 h-4"></i>
                    Dry Run Repair
                </button>
            </form>
            <form action="{{ route('admin.exercises.repair') }}" method="POST" onsubmit="return confirm('This will repair all invalid questions and save backup data. Continue?')">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-lg transition-colors">
                    <i data-lucide="wrench" class="w-4 h-4"></i>
                    Repair All
                </button>
            </form>
        </div>
    </div>

    @if (session('flash_message'))
    <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl">
        <pre class="text-sm text-blue-800 whitespace-pre-wrap">{{ session('flash_message') }}</pre>
    </div>
    @endif

    <!-- Summary Table -->
    <div class="card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="font-semibold text-gray-900">Validation Summary</h3>
        </div>
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Practice Type</th>
                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Total</th>
                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Valid</th>
                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Invalid</th>
                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Needs Review</th>
                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($summary as $type => $stats)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium text-gray-900">{{ $type }}</td>
                    <td class="px-6 py-4 text-center text-sm text-gray-600">{{ $stats['total'] }}</td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                            {{ $stats['valid'] }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if ($stats['invalid'] > 0)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                            {{ $stats['invalid'] }}
                        </span>
                        @else
                        <span class="text-gray-400 text-sm">0</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if ($stats['needsReview'] > 0)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                            {{ $stats['needsReview'] }}
                        </span>
                        @else
                        <span class="text-gray-400 text-sm">0</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if ($stats['invalid'] > 0)
                        <form action="{{ route('admin.exercises.repair') }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="type" value="{{ $type }}">
                            <button type="submit" class="text-sm text-purple-600 hover:text-purple-800 font-medium">
                                Repair this type
                            </button>
                        </form>
                        @else
                        <span class="text-sm text-gray-400">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Legend -->
    <div class="card p-4">
        <h4 class="text-sm font-semibold text-gray-700 mb-3">Validation Status Legend</h4>
        <div class="flex flex-wrap gap-3 text-xs">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-green-100 text-green-700 font-semibold">Valid — all fields consistent</span>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-red-100 text-red-700 font-semibold">Invalid — direction/answer mismatch detected</span>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-yellow-100 text-yellow-700 font-semibold">Needs Review — enharmonic equivalents or ambiguous</span>
        </div>
        <p class="text-xs text-gray-500 mt-3">
            The repair command backs up original data to the <code>backup_data</code> column before changing any field.
            Ambiguous questions (enharmonic equivalents) are marked "Needs Review" and are never auto-repaired.
        </p>
    </div>
</div>
@endsection
