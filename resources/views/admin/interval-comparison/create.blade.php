@extends('admin.layouts.admin')

@section('content')
<div class="max-w-2xl">
    <!-- Header -->
    <div class="mb-8">
        <a href="{{ route('admin.interval-comparison.index') }}" class="inline-flex items-center text-gray-500 hover:text-purple-600 transition-colors mb-4">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            Back to list
        </a>
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center">
                <i data-lucide="plus" class="w-5 h-5 text-orange-600"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Create Interval Comparison Practice</h1>
        </div>
        <p class="text-gray-500">Add a new interval comparison recognition question</p>
    </div>

    <!-- Form -->
    <form action="{{ route('admin.interval-comparison.store') }}" method="POST" class="card p-6 space-y-6">
        @csrf

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="interval_a" class="block text-sm font-semibold text-gray-700 mb-2">Interval A *</label>
                <input type="text" 
                       name="interval_a" 
                       id="interval_a" 
                       value="{{ old('interval_a') }}"
                       placeholder="e.g., C,E or D,A"
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                       required>
                <p class="mt-2 text-xs text-gray-500">Two notes separated by comma</p>
            </div>

            <div>
                <label for="interval_b" class="block text-sm font-semibold text-gray-700 mb-2">Interval B *</label>
                <input type="text" 
                       name="interval_b" 
                       id="interval_b" 
                       value="{{ old('interval_b') }}"
                       placeholder="e.g., C,E or D,A"
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                       required>
                <p class="mt-2 text-xs text-gray-500">Two notes separated by comma</p>
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-3">Larger Interval (Target) *</label>
            <div class="grid grid-cols-2 gap-4">
                <label class="relative flex flex-col items-center justify-center p-6 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 hover:bg-orange-50 transition-all has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50">
                    <input type="radio" name="target" value="a" class="sr-only peer" {{ old('target') == 'a' ? 'checked' : '' }} required>
                    <span class="text-4xl font-bold text-gray-700">A</span>
                    <span class="text-sm text-gray-500 mt-2">Interval A is larger</span>
                </label>
                <label class="relative flex flex-col items-center justify-center p-6 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 hover:bg-orange-50 transition-all has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50">
                    <input type="radio" name="target" value="b" class="sr-only peer" {{ old('target') == 'b' ? 'checked' : '' }}>
                    <span class="text-4xl font-bold text-gray-700">B</span>
                    <span class="text-sm text-gray-500 mt-2">Interval B is larger</span>
                </label>
            </div>
            <p class="mt-3 text-xs text-gray-500">Which interval has the larger distance (the correct answer)</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
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
                <p class="mt-2 text-xs text-gray-500">The clef for displaying intervals</p>
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
                <p class="mt-2 text-xs text-gray-500">The octave number (2-6)</p>
            </div>
        </div>

        <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
            <a href="{{ route('admin.interval-comparison.index') }}" 
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
