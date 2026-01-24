@extends('admin.layouts.admin')

@section('content')
<div class="max-w-2xl">
    <!-- Header -->
    <div class="mb-8">
        <a href="{{ route('admin.interval-construction.index') }}" class="inline-flex items-center text-gray-500 hover:text-green-600 transition-colors mb-4">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            Back to list
        </a>
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
                <i data-lucide="plus" class="w-5 h-5 text-green-600"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Create Interval Construction Practice</h1>
        </div>
        <p class="text-gray-500">Add a new interval construction question</p>
    </div>

    <!-- Form -->
    <form action="{{ route('admin.interval-construction.store') }}" method="POST" class="card p-6 space-y-6">
        @csrf

        <div>
            <label for="interval" class="block text-sm font-semibold text-gray-700 mb-2">Interval Type *</label>
            <select name="interval" id="interval" class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all" required>
                <option value="">Select Interval</option>
                @foreach (['Minor 2nd', 'Major 2nd', 'Minor 3rd', 'Major 3rd', 'Perfect 4th', 'Tritone', 'Perfect 5th', 'Minor 6th', 'Major 6th', 'Minor 7th', 'Major 7th', 'Perfect Octave'] as $interval)
                <option value="{{ $interval }}" {{ old('interval') == $interval ? 'selected' : '' }}>{{ $interval }}</option>
                @endforeach
            </select>
            <p class="mt-2 text-xs text-gray-500">The interval the user needs to build</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="note1" class="block text-sm font-semibold text-gray-700 mb-2">Starting Note *</label>
                <select name="note1" id="note1" class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all" required>
                    @foreach (['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'] as $note)
                    <option value="{{ $note }}" {{ old('note1') == $note ? 'selected' : '' }}>{{ $note }}</option>
                    @endforeach
                </select>
                <p class="mt-2 text-xs text-gray-500">The note shown to the user</p>
            </div>
            <div>
                <label for="note2" class="block text-sm font-semibold text-gray-700 mb-2">Target Note (Answer) *</label>
                <select name="note2" id="note2" class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all" required>
                    @foreach (['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'] as $note)
                    <option value="{{ $note }}" {{ old('note2') == $note ? 'selected' : '' }}>{{ $note }}</option>
                    @endforeach
                </select>
                <p class="mt-2 text-xs text-gray-500">The correct answer</p>
            </div>
        </div>

        <div>
            <label for="octave" class="block text-sm font-semibold text-gray-700 mb-2">Octave *</label>
            <select name="octave" id="octave" class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all" required>
                @foreach (['2', '3', '4', '5', '6'] as $octave)
                <option value="{{ $octave }}" {{ old('octave', '4') == $octave ? 'selected' : '' }}>{{ $octave }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
            <a href="{{ route('admin.interval-construction.index') }}" class="px-5 py-2.5 text-gray-600 hover:text-gray-800 font-medium transition-colors">Cancel</a>
            <button type="submit" class="btn-primary px-6 py-2.5 text-white font-semibold rounded-lg transition-all hover:shadow-lg">Create Practice</button>
        </div>
    </form>
</div>
@endsection
