@extends('admin.layouts.admin')

@section('content')
<div class="max-w-2xl">
    <!-- Header -->
    <div class="mb-8">
        <a href="{{ route('admin.interval-direction.index') }}" class="inline-flex items-center text-gray-500 hover:text-purple-600 transition-colors mb-4">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            Back to list
        </a>
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                <i data-lucide="plus" class="w-5 h-5 text-blue-600"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Create Interval Direction Practice</h1>
        </div>
        <p class="text-gray-500">Add a new interval direction recognition question</p>
    </div>

    <!-- Form -->
    <form action="{{ route('admin.interval-direction.store') }}" method="POST" class="card p-6 space-y-6">
        @csrf

        <div>
            <label for="clef" class="block text-sm font-semibold text-gray-700 mb-2">Clef *</label>
            <select name="clef" 
                    id="clef"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                    required>
                <option value="treble" {{ old('clef') == 'treble' ? 'selected' : '' }}>Treble</option>
                <option value="alto" {{ old('clef') == 'alto' ? 'selected' : '' }}>Alto</option>
                <option value="bass" {{ old('clef') == 'bass' ? 'selected' : '' }}>Bass</option>
            </select>
            <p class="mt-2 text-xs text-gray-500">The clef for displaying the notes</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="note1" class="block text-sm font-semibold text-gray-700 mb-2">First Note *</label>
                <input type="text" 
                       name="note1" 
                       id="note1" 
                       value="{{ old('note1') }}"
                       placeholder="e.g., C, D#, Eb"
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                       required>
                <p class="mt-2 text-xs text-gray-500">The first note played</p>
            </div>

            <div>
                <label for="note2" class="block text-sm font-semibold text-gray-700 mb-2">Second Note *</label>
                <input type="text" 
                       name="note2" 
                       id="note2" 
                       value="{{ old('note2') }}"
                       placeholder="e.g., C, D#, Eb"
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                       required>
                <p class="mt-2 text-xs text-gray-500">The second note played</p>
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-3">Direction *</label>
            <div class="grid grid-cols-2 gap-4">
                <label class="relative flex items-center justify-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-green-300 hover:bg-green-50 transition-all has-[:checked]:border-green-500 has-[:checked]:bg-green-50">
                    <input type="radio" name="direction" value="ascending" class="sr-only peer" {{ old('direction') == 'ascending' ? 'checked' : '' }} required>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                            <i data-lucide="arrow-up" class="w-5 h-5 text-green-600"></i>
                        </div>
                        <span class="font-semibold text-gray-700">Ascending</span>
                    </div>
                </label>
                <label class="relative flex items-center justify-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-red-300 hover:bg-red-50 transition-all has-[:checked]:border-red-500 has-[:checked]:bg-red-50">
                    <input type="radio" name="direction" value="descending" class="sr-only peer" {{ old('direction') == 'descending' ? 'checked' : '' }}>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                            <i data-lucide="arrow-down" class="w-5 h-5 text-red-600"></i>
                        </div>
                        <span class="font-semibold text-gray-700">Descending</span>
                    </div>
                </label>
            </div>
            <p class="mt-3 text-xs text-gray-500">Whether the interval goes up or down</p>
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
            <p class="mt-2 text-xs text-gray-500">The octave number for the notes (2-6)</p>
        </div>

        <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
            <a href="{{ route('admin.interval-direction.index') }}" 
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
@endsection
