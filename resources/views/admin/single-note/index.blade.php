@extends('admin.layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center">
                    <i data-lucide="music" class="w-5 h-5 text-purple-600"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Single Note Practices</h1>
            </div>
            <p class="text-gray-500">Manage single note recognition questions</p>
        </div>
        <a href="{{ route('admin.single-note.create') }}" 
           class="btn-primary inline-flex items-center gap-2 px-5 py-2.5 text-white font-semibold rounded-lg transition-all hover:shadow-lg">
            <i data-lucide="plus" class="w-5 h-5"></i>
            Add New
        </a>
    </div>

    <!-- Table -->
    <div class="card overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Target</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Options</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Octave</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($practices as $practice)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $practice->id }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-purple-100 text-purple-700 text-sm font-semibold">
                            {{ $practice->target }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-700 capitalize">{{ $practice->target_type }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">{{ $practice->other_options }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-gray-100 text-gray-700 text-sm font-medium">
                            {{ $practice->octave }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.single-note.edit', $practice) }}" 
                               class="p-2 text-gray-500 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition-colors">
                                <i data-lucide="pencil" class="w-4 h-4"></i>
                            </a>
                            <form action="{{ route('admin.single-note.destroy', $practice) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this practice?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                                <i data-lucide="music" class="w-8 h-8 text-gray-400"></i>
                            </div>
                            <p class="text-gray-500 mb-2">No single note practices found.</p>
                            <a href="{{ route('admin.single-note.create') }}" class="text-purple-600 hover:text-purple-700 font-medium">
                                Create your first one
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if ($practices->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            {{ $practices->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
