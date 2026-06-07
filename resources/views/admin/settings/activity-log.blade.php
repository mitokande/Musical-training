@extends('admin.layouts.admin')

@section('page-title', 'Activity Log')

@section('content')
<div class="space-y-6">

    {{-- Filters --}}
    <div class="card p-4">
        <form method="GET" action="{{ route('admin.settings.activity-log') }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Admin</label>
                <select name="admin_id" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <option value="">All Admins</option>
                    @foreach($admins ?? [] as $admin)
                        <option value="{{ $admin->id }}" {{ request('admin_id') == $admin->id ? 'selected' : '' }}>{{ $admin->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}"
                       class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}"
                       class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-sm font-medium text-gray-700 rounded-lg transition-colors">
                <i data-lucide="filter" class="w-4 h-4 inline mr-1"></i> Filter
            </button>
        </form>
    </div>

    {{-- Activity Table --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Date</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Admin</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Action</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Subject</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-600">Properties</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($activities ?? [] as $activity)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="py-3 px-4 text-gray-600 whitespace-nowrap">{{ $activity->created_at->format('M d, Y H:i') }}</td>
                        <td class="py-3 px-4">
                            <span class="font-medium text-gray-900">{{ $activity->causer->name ?? 'System' }}</span>
                        </td>
                        <td class="py-3 px-4 text-gray-700">{{ $activity->description }}</td>
                        <td class="py-3 px-4">
                            @if($activity->subject_type)
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-700">{{ class_basename($activity->subject_type) }}</span>
                                <span class="text-xs text-gray-500 ml-1">#{{ $activity->subject_id }}</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="py-3 px-4" x-data="{ open: false }">
                            @if($activity->properties && $activity->properties->count() > 0)
                                <button @click="open = !open" class="text-xs text-purple-600 hover:text-purple-800 font-medium">
                                    <span x-text="open ? 'Hide' : 'Show'">Show</span> Details
                                </button>
                                <div x-show="open" x-collapse class="mt-2">
                                    @if($activity->properties->has('old'))
                                    <div class="mb-1">
                                        <span class="text-xs font-semibold text-red-500">Old:</span>
                                        <pre class="text-xs text-gray-600 bg-red-50 p-2 rounded mt-0.5 overflow-x-auto">{{ json_encode($activity->properties['old'], JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                    @endif
                                    @if($activity->properties->has('attributes'))
                                    <div>
                                        <span class="text-xs font-semibold text-green-500">New:</span>
                                        <pre class="text-xs text-gray-600 bg-green-50 p-2 rounded mt-0.5 overflow-x-auto">{{ json_encode($activity->properties['attributes'], JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                    @endif
                                </div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-gray-500">No activity recorded.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if(method_exists($activities ?? collect(), 'links'))
    <div>{{ $activities->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
