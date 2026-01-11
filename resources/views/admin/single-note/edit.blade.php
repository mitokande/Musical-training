@extends('admin.layouts.admin')

@section('content')
<div class="max-w-2xl">
    <!-- Header -->
    <div class="mb-8">
        <a href="{{ route('admin.single-note.index') }}" class="inline-flex items-center text-gray-500 hover:text-purple-600 transition-colors mb-4">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            Back to list
        </a>
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center">
                <i data-lucide="pencil" class="w-5 h-5 text-purple-600"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Single Note Practice</h1>
        </div>
        <p class="text-gray-500">Update single note recognition question #{{ $practice->id }}</p>
    </div>

    <!-- Form -->
    <form action="{{ route('admin.single-note.update', $practice) }}" method="POST" class="card p-6 space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label for="target" class="block text-sm font-semibold text-gray-700 mb-2">Target Note *</label>
            <input type="text" 
                   name="target" 
                   id="target" 
                   value="{{ old('target', $practice->target) }}"
                   placeholder="e.g., C, D#, Eb"
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                   required>
            <p class="mt-2 text-xs text-gray-500">The correct note the student should identify</p>
        </div>

        <div>
            <label for="target_type" class="block text-sm font-semibold text-gray-700 mb-2">Target Type *</label>
            <select name="target_type" 
                    id="target_type"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                    required>
                <option value="note" {{ old('target_type', $practice->target_type) == 'note' ? 'selected' : '' }}>Note</option>
                <option value="chord" {{ old('target_type', $practice->target_type) == 'chord' ? 'selected' : '' }}>Chord</option>
            </select>
            <p class="mt-2 text-xs text-gray-500">The type of musical element</p>
        </div>

        <div>
            <label for="other_options" class="block text-sm font-semibold text-gray-700 mb-2">Answer Options *</label>
            <input type="text" 
                   name="other_options" 
                   id="other_options" 
                   value="{{ old('other_options', $practice->other_options) }}"
                   placeholder="e.g., C, E, G, B"
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                   required>
            <p class="mt-2 text-xs text-gray-500">Comma-separated list of options (include the target)</p>
        </div>

        <div>
            <label for="octave" class="block text-sm font-semibold text-gray-700 mb-2">Octave *</label>
            <select name="octave" 
                    id="octave"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                    required>
                @foreach (['2', '3', '4', '5', '6'] as $octave)
                <option value="{{ $octave }}" {{ old('octave', $practice->octave) == $octave ? 'selected' : '' }}>{{ $octave }}</option>
                @endforeach
            </select>
            <p class="mt-2 text-xs text-gray-500">The octave number for the note (2-6)</p>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
            <form action="{{ route('admin.single-note.destroy', $practice) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this practice?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-2 text-red-600 hover:text-red-700 text-sm font-medium transition-colors">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                    Delete this practice
                </button>
            </form>
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.single-note.index') }}" 
                   class="px-5 py-2.5 text-gray-600 hover:text-gray-800 font-medium transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="btn-primary px-6 py-2.5 text-white font-semibold rounded-lg transition-all hover:shadow-lg">
                    Update Practice
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
