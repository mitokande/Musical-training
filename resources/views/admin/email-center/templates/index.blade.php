@extends('admin.layouts.admin')
@section('page-title', 'Email Templates')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">Email Templates</h2>
        <a href="{{ route('admin.email-templates.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
            <i data-lucide="plus" class="w-4 h-4"></i> New Template
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Name</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Subject</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Category</th>
                    <th class="text-center px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Used By</th>
                    <th class="text-center px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Active</th>
                    <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($templates as $template)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3">
                            <a href="{{ route('admin.email-templates.edit', $template) }}" class="font-medium text-indigo-600 hover:underline">{{ $template->name }}</a>
                            <div class="text-xs text-gray-400 font-mono">{{ $template->slug }}</div>
                        </td>
                        <td class="px-6 py-3 text-gray-600">{{ Str::limit($template->subject, 50) }}</td>
                        <td class="px-6 py-3">
                            <span class="text-xs px-2 py-1 rounded-full {{ $template->category === 'marketing' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">{{ ucfirst($template->category) }}</span>
                        </td>
                        <td class="px-6 py-3 text-center text-gray-600">{{ $template->campaigns_count }} campaigns · {{ $template->automations_count }} automations</td>
                        <td class="px-6 py-3 text-center">
                            <span class="inline-block w-2.5 h-2.5 rounded-full {{ $template->is_active ? 'bg-green-500' : 'bg-gray-300' }}"></span>
                        </td>
                        <td class="px-6 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.email-templates.preview', $template) }}" target="_blank" class="text-gray-400 hover:text-indigo-600" title="Preview"><i data-lucide="eye" class="w-4 h-4"></i></a>
                                <a href="{{ route('admin.email-templates.edit', $template) }}" class="text-gray-400 hover:text-indigo-600" title="Edit"><i data-lucide="pencil" class="w-4 h-4"></i></a>
                                <form method="POST" action="{{ route('admin.email-templates.destroy', $template) }}" onsubmit="return confirm('Delete this template?')">
                                    @csrf @method('DELETE')
                                    <button class="text-gray-400 hover:text-red-600" title="Delete"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">No templates yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-3 border-t border-gray-100">{{ $templates->links() }}</div>
    </div>
</div>
@endsection
