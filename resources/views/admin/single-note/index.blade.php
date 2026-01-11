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

    <!-- Practice Settings -->
    @if($settings)
    <div class="card p-6">
        <div class="flex items-center gap-2 mb-6">
            <i data-lucide="settings" class="w-5 h-5 text-purple-600"></i>
            <h2 class="text-lg font-semibold text-gray-900">Practice Settings</h2>
        </div>
        
        <form action="{{ route('admin.single-note.settings') }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Practice Name</label>
                    <input type="text" 
                           name="name" 
                           id="name" 
                           value="{{ old('name', $settings->name) }}"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                           required>
                </div>
                
                <div>
                    <label for="type" class="block text-sm font-semibold text-gray-700 mb-2">Practice Type</label>
                    <select name="type" 
                            id="type"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                            required>
                        <option value="Recognition" {{ old('type', $settings->type) == 'Recognition' ? 'selected' : '' }}>Recognition</option>
                        <option value="Dictation" {{ old('type', $settings->type) == 'Dictation' ? 'selected' : '' }}>Dictation</option>
                        <option value="Sight Reading" {{ old('type', $settings->type) == 'Sight Reading' ? 'selected' : '' }}>Sight Reading</option>
                        <option value="Theory" {{ old('type', $settings->type) == 'Theory' ? 'selected' : '' }}>Theory</option>
                    </select>
                </div>
                
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                    <textarea name="description" 
                              id="description" 
                              rows="2"
                              class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all resize-none"
                              placeholder="Brief description of this practice...">{{ old('description', $settings->description) }}</textarea>
                </div>
            </div>
            
            <div class="flex items-center justify-between mt-6 pt-6 border-t border-gray-200">
                <label class="relative flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" 
                           name="is_premium" 
                           value="1"
                           {{ old('is_premium', $settings->is_premium) ? 'checked' : '' }}
                           class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                    <div class="flex items-center gap-2">
                        <i data-lucide="crown" class="w-4 h-4 text-orange-500"></i>
                        <span class="text-sm font-medium text-gray-700">Premium Only</span>
                    </div>
                </label>
                
                <button type="submit" 
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-900 hover:bg-gray-800 text-white font-semibold rounded-lg transition-all">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Save Settings
                </button>
            </div>
        </form>
    </div>
    @endif

    <!-- Table -->
    <div class="card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="font-semibold text-gray-900">Practice Questions</h3>
        </div>
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
