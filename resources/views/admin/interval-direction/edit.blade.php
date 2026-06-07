@extends('admin.layouts.admin')

@section('content')
<div class="max-w-2xl" x-data="intervalEditor('{{ $practice->note1 }}', {{ (int)$practice->octave }}, '{{ $practice->note2 }}', {{ (int)($practice->note2_octave ?? $practice->octave) }})">
    <!-- Header -->
    <div class="mb-8">
        <a href="{{ route('admin.interval-direction.index') }}" class="inline-flex items-center text-gray-500 hover:text-purple-600 transition-colors mb-4">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            Back to list
        </a>
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                <i data-lucide="pencil" class="w-5 h-5 text-blue-600"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Interval Direction Practice</h1>
        </div>
        <p class="text-gray-500">Update interval direction recognition question #{{ $practice->id }}</p>
    </div>

    <!-- Form -->
    <form action="{{ route('admin.interval-direction.update', $practice) }}" method="POST" class="card p-6 space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label for="clef" class="block text-sm font-semibold text-gray-700 mb-2">Clef *</label>
            <select name="clef"
                    id="clef"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                    required>
                <option value="treble" {{ old('clef', $practice->clef) == 'treble' ? 'selected' : '' }}>Treble</option>
                <option value="alto" {{ old('clef', $practice->clef) == 'alto' ? 'selected' : '' }}>Alto</option>
                <option value="bass" {{ old('clef', $practice->clef) == 'bass' ? 'selected' : '' }}>Bass</option>
            </select>
            <p class="mt-2 text-xs text-gray-500">The clef for displaying the notes</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="note1" class="block text-sm font-semibold text-gray-700 mb-2">First Note *</label>
                <select name="note1"
                        id="note1"
                        x-model="note1"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                        required>
                    @foreach (['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'] as $note)
                    <option value="{{ $note }}" {{ old('note1', $practice->note1) == $note ? 'selected' : '' }}>{{ $note }}</option>
                    @endforeach
                </select>
                <p class="mt-2 text-xs text-gray-500">The first note played</p>
            </div>

            <div>
                <label for="note2" class="block text-sm font-semibold text-gray-700 mb-2">Second Note *</label>
                <select name="note2"
                        id="note2"
                        x-model="note2"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                        required>
                    @foreach (['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'] as $note)
                    <option value="{{ $note }}" {{ old('note2', $practice->note2) == $note ? 'selected' : '' }}>{{ $note }}</option>
                    @endforeach
                </select>
                <p class="mt-2 text-xs text-gray-500">The second note played</p>
            </div>
        </div>

        <div>
            <label for="octave" class="block text-sm font-semibold text-gray-700 mb-2">Octave *</label>
            <select name="octave"
                    id="octave"
                    x-model.number="octave1"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                    required>
                @foreach (['2', '3', '4', '5', '6'] as $octave)
                <option value="{{ $octave }}" {{ old('octave', $practice->octave) == $octave ? 'selected' : '' }}>{{ $octave }}</option>
                @endforeach
            </select>
            <p class="mt-2 text-xs text-gray-500">The octave for the first note (2-6). Direction is auto-derived from note pitches.</p>
        </div>

        <!-- note2_octave hidden field — populated by auto-recalculate -->
        <input type="hidden" name="note2_octave" :value="note2Octave">

        <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
            <a href="{{ route('admin.interval-direction.index') }}"
               class="px-5 py-2.5 text-gray-600 hover:text-gray-800 font-medium transition-colors">
                Cancel
            </a>
            <button type="submit"
                    class="btn-primary px-6 py-2.5 text-white font-semibold rounded-lg transition-all hover:shadow-lg">
                Update Practice
            </button>
        </div>
    </form>

    <!-- Auto-recalculate panel -->
    <div class="mt-6 card p-6" x-show="recalcResult !== null">
        <h3 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
            <i data-lucide="calculator" class="w-4 h-4 text-blue-600"></i>
            Calculated Values
        </h3>
        <div class="grid grid-cols-2 gap-4 text-sm" x-show="recalcResult">
            <div>
                <span class="text-gray-500">Direction (auto):</span>
                <span class="font-semibold ml-2" x-text="recalcResult?.direction ?? '—'"></span>
            </div>
            <div>
                <span class="text-gray-500">Semitones:</span>
                <span class="font-semibold ml-2" x-text="recalcResult?.semitones ?? '—'"></span>
            </div>
            <div>
                <span class="text-gray-500">Interval:</span>
                <span class="font-semibold ml-2" x-text="recalcResult?.interval_name ?? '—'"></span>
            </div>
            <div>
                <span class="text-gray-500">Note2 Octave:</span>
                <span class="font-semibold ml-2" x-text="recalcResult?.note2_octave ?? '—'"></span>
            </div>
        </div>
    </div>

    <script>
    function intervalEditor(note1, octave1, note2, note2Octave) {
        return {
            note1, octave1, note2, note2Octave,
            recalcResult: null,
            async recalc() {
                try {
                    const params = new URLSearchParams({
                        note1: this.note1, note1_octave: this.octave1,
                        note2: this.note2, note2_octave: this.note2Octave
                    });
                    const r = await fetch(`{{ route('admin.api.recalculate-interval') }}?` + params, {
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                    });
                    this.recalcResult = await r.json();
                    if (this.recalcResult.note2_octave) {
                        this.note2Octave = this.recalcResult.note2_octave;
                    }
                } catch(e) { console.error(e); }
            },
            init() {
                this.$watch('note1', () => this.recalc());
                this.$watch('octave1', () => this.recalc());
                this.$watch('note2', () => this.recalc());
                this.recalc();
            }
        };
    }
    </script>

    <!-- Delete Practice (separate form outside main form) -->
    <div class="mt-4 p-4 bg-red-50 border border-red-200 rounded-xl">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600"></i>
                </div>
                <div>
                    <p class="font-semibold text-red-700">Danger Zone</p>
                    <p class="text-sm text-red-600">Permanently delete this practice question</p>
                </div>
            </div>
            <form action="{{ route('admin.interval-direction.destroy', $practice) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this practice?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                    Delete Practice
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
