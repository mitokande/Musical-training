@extends('admin.layouts.admin')
@section('page-title', 'Content Library')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">Content Library</h2>
        <a href="{{ route('admin.content.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Add Content
        </a>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <form method="GET" action="{{ route('admin.content.index') }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Content Type</label>
                <select name="content_type" class="rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500 text-sm">
                    <option value="">All Types</option>
                    <option value="article" {{ request('content_type') === 'article' ? 'selected' : '' }}>Article</option>
                    <option value="video" {{ request('content_type') === 'video' ? 'selected' : '' }}>Video</option>
                    <option value="document" {{ request('content_type') === 'document' ? 'selected' : '' }}>Document</option>
                    <option value="audio" {{ request('content_type') === 'audio' ? 'selected' : '' }}>Audio</option>
                    <option value="sheet_music" {{ request('content_type') === 'sheet_music' ? 'selected' : '' }}>Sheet Music</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500 text-sm">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Category</label>
                <select name="category_id" class="rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500 text-sm">
                    <option value="">All Categories</option>
                    @foreach($categories ?? [] as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm">
                <i data-lucide="filter" class="w-4 h-4"></i> Filter
            </button>
            <a href="{{ route('admin.content.index') }}" class="px-4 py-2 text-gray-500 hover:text-gray-700 transition text-sm">Reset</a>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Title</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Type</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Author</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Views</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Created</th>
                        <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($contents as $content)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-800 max-w-xs truncate">{{ $content->title }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $typeColors = [
                                        'article' => 'bg-blue-100 text-blue-700',
                                        'video' => 'bg-red-100 text-red-700',
                                        'document' => 'bg-gray-100 text-gray-700',
                                        'audio' => 'bg-green-100 text-green-700',
                                        'sheet_music' => 'bg-purple-100 text-purple-700',
                                    ];
                                    $typeIcons = [
                                        'article' => 'file-text',
                                        'video' => 'video',
                                        'document' => 'file',
                                        'audio' => 'music',
                                        'sheet_music' => 'music-2',
                                    ];
                                @endphp
                                <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full {{ $typeColors[$content->content_type] ?? 'bg-gray-100 text-gray-700' }}">
                                    <i data-lucide="{{ $typeIcons[$content->content_type] ?? 'file' }}" class="w-3 h-3"></i>
                                    {{ ucfirst(str_replace('_', ' ', $content->content_type)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $content->author->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4">
                                @php
                                    $statusColors = [
                                        'published' => 'bg-green-100 text-green-700',
                                        'pending' => 'bg-yellow-100 text-yellow-700',
                                        'rejected' => 'bg-red-100 text-red-700',
                                        'draft' => 'bg-gray-100 text-gray-600',
                                    ];
                                @endphp
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColors[$content->status] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ ucfirst($content->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ number_format($content->views ?? 0) }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ $content->created_at?->format('M d, Y') }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.content.edit', $content) }}" class="p-1.5 text-gray-400 hover:text-purple-600 rounded-lg hover:bg-purple-50 transition" title="Edit">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </a>
                                    @if($content->status === 'pending')
                                        <form action="{{ route('admin.content.approve', $content) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="p-1.5 text-gray-400 hover:text-green-600 rounded-lg hover:bg-green-50 transition" title="Approve">
                                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.content.reject', $content) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition" title="Reject">
                                                <i data-lucide="x-circle" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('admin.content.destroy', $content) }}" method="POST" onsubmit="return confirm('Delete this content?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition" title="Delete">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                <i data-lucide="library" class="w-10 h-10 mx-auto mb-2 opacity-50"></i>
                                <p>No content found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($contents->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $contents->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
