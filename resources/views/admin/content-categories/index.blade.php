@extends('admin.layouts.admin')
@section('page-title', 'Content Categories')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">Content Categories</h2>
        <a href="{{ route('admin.content-categories.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Add Category
        </a>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Name</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Parent</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Sort Order</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                        <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($categories as $category)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-800">{{ $category->name }}</div>
                                <div class="text-xs text-gray-400 font-mono">{{ $category->slug }}</div>
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                {{ $category->parent->name ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ $category->sort_order }}</td>
                            <td class="px-6 py-4">
                                @if($category->is_active)
                                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-full">Active</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-500 rounded-full">Inactive</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.content-categories.edit', $category) }}" class="p-1.5 text-gray-400 hover:text-purple-600 rounded-lg hover:bg-purple-50 transition" title="Edit">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </a>
                                    <form action="{{ route('admin.content-categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Delete this category?')" class="inline">
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
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                <i data-lucide="folder" class="w-10 h-10 mx-auto mb-2 opacity-50"></i>
                                <p>No categories found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
