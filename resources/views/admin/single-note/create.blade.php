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
                <i data-lucide="plus" class="w-5 h-5 text-purple-600"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Create Single Note Practice</h1>
        </div>
        <p class="text-gray-500">Add a new single note recognition question</p>
    </div>

    <!-- Form -->
    <form action="{{ route('admin.single-note.store') }}" method="POST" class="card p-6 space-y-6">
        @csrf

        <div>
            <label for="target" class="block text-sm font-semibold text-gray-700 mb-2">Target Note *</label>
            <input type="text" 
                   name="target" 
                   id="target" 
                   value="{{ old('target') }}"
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
                <option value="note" {{ old('target_type') == 'note' ? 'selected' : '' }}>Note</option>
                <option value="chord" {{ old('target_type') == 'chord' ? 'selected' : '' }}>Chord</option>
            </select>
            <p class="mt-2 text-xs text-gray-500">The type of musical element</p>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Answer Options *</label>
            <input type="hidden" name="other_options" id="other_options" value="{{ old('other_options') }}">
            <div id="options-container" class="space-y-2">
                <!-- Dynamic option inputs will be added here -->
            </div>
            <button type="button" 
                    onclick="addOption()"
                    class="mt-3 inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-purple-600 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Add Option
            </button>
            <p class="mt-2 text-xs text-gray-500">Add all answer options (include the target note)</p>
        </div>

        <div>
            <label for="octave" class="block text-sm font-semibold text-gray-700 mb-2">Octave *</label>
            <select name="octave" 
                    id="octave"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                    required>
                @foreach (['2', '3', '4', '5', '6'] as $octave)
                <option value="{{ $octave }}" {{ old('octave', '4') == $octave ? 'selected' : '' }}>{{ $octave }}</option>
                @endforeach
            </select>
            <p class="mt-2 text-xs text-gray-500">The octave number for the note (2-6)</p>
        </div>

        <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
            <a href="{{ route('admin.single-note.index') }}" 
               class="px-5 py-2.5 text-gray-600 hover:text-gray-800 font-medium transition-colors">
                Cancel
            </a>
            <button type="submit" 
                    class="btn-primary px-6 py-2.5 text-white font-semibold rounded-lg transition-all hover:shadow-lg">
                Create Practice
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('options-container');
        const hiddenInput = document.getElementById('other_options');
        const form = hiddenInput.closest('form');
        
        // Parse existing value (from old() on validation error)
        const existingValue = hiddenInput.value;
        if (existingValue) {
            const options = existingValue.split(',').map(opt => opt.trim()).filter(opt => opt);
            options.forEach(opt => addOption(opt));
        } else {
            // Add one empty option by default
            addOption();
        }
        
        // Before form submit, collect all options
        form.addEventListener('submit', function(e) {
            const inputs = container.querySelectorAll('input[type="text"]');
            const values = Array.from(inputs)
                .map(input => input.value.trim())
                .filter(val => val);
            hiddenInput.value = values.join(',');
            
            if (values.length === 0) {
                e.preventDefault();
                alert('Please add at least one option');
            }
        });
        
        // Re-initialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
    
    function addOption(value = '') {
        const container = document.getElementById('options-container');
        const div = document.createElement('div');
        div.className = 'flex items-center gap-2';
        div.innerHTML = `
            <input type="text" 
                   value="${value}"
                   placeholder="e.g., C, D#, Eb"
                   class="flex-1 px-4 py-3 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
            <button type="button" 
                    onclick="removeOption(this)"
                    class="p-3 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors">
                <i data-lucide="trash-2" class="w-5 h-5"></i>
            </button>
        `;
        container.appendChild(div);
        
        // Re-initialize Lucide icons for the new button
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }
    
    function removeOption(button) {
        const container = document.getElementById('options-container');
        if (container.children.length > 1) {
            button.closest('div').remove();
        } else {
            alert('At least one option is required');
        }
    }
</script>
@endpush
@endsection
