@php
$ex = $ex ?? null;
$cfg = $ex ? ($ex->config_json ?? []) : [];
// After a validation error, old('config_json') has the raw JSON string — decode it
if (old('config_json')) {
    $oldCfg = json_decode(old('config_json'), true) ?? [];
    if (!empty($oldCfg)) $cfg = $oldCfg;
}
$practiceType = $cfg['practice_type'] ?? '';

// Known options
$allIntervals = [
    'Perfect Unison','Minor 2nd','Major 2nd','Minor 3rd','Major 3rd',
    'Perfect 4th','Tritone','Perfect 5th','Minor 6th','Major 6th',
    'Minor 7th','Major 7th','Perfect Octave',
];
$allNotes = ['C','C#','D','D#','E','F','F#','G','G#','A','A#','B'];
$allOctaves = ['2','3','4','5','6'];
$allScales = [
    'Major','Natural Minor','Harmonic Minor','Melodic Minor',
    'Dorian','Phrygian','Lydian','Mixolydian','Locrian',
    'Pentatonic Major','Pentatonic Minor','Blues',
];
$allChords = [
    'Major','Minor','Augmented','Diminished',
    'Dominant 7th','Major 7th','Minor 7th','Half Diminished 7th','Diminished 7th',
    'Suspended 2nd','Suspended 4th',
];
$allTimeSignatures = ['4/4','3/4','2/4','6/8','12/8','5/4','7/8'];
$allNoteValues = ['whole','half','quarter','eighth','sixteenth','dotted-half','dotted-quarter','dotted-eighth'];
$allClefs = ['treble','bass'];

$practiceTypes = [
    'melodic-interval-practice'      => 'Melodic Intervals',
    'harmonic-interval-practice'     => 'Harmonic Intervals',
    'interval-direction-practice'    => 'Interval Direction',
    'interval-construction-practice' => 'Interval Construction',
    'interval-comparison-practice'   => 'Interval Comparison',
    'scale-practice'                 => 'Scales & Modes',
    'chord-practice'                 => 'Chords',
    'rhythm-practice'                => 'Rhythm',
    'melodic-dictation'              => 'Melodic Dictation',
    'single-note-practice'           => 'Single Note',
];
@endphp

<div x-data="exerciseFormConfig()" x-init="init()" class="space-y-5">

    {{-- ── Basic Info ── --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label>
            <select name="category_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}"
                    @selected(old('category_id', $ex?->category_id ?? request('category_id')) == $cat->id)>
                    {{ $cat->parent_id ? '  └ ' : '' }}{{ $cat->name }}
                </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Seviye <span class="text-red-500">*</span></label>
            <select name="level" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                @foreach(['beginner'=>'Beginner','intermediate'=>'Intermediate','advanced'=>'Advanced'] as $val => $label)
                <option value="{{ $val }}" @selected(old('level', $ex?->level) === $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Başlık (EN) <span class="text-red-500">*</span></label>
            <input name="title" type="text" value="{{ old('title', $ex?->title) }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500" required>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Başlık (TR)</label>
            <input name="title_tr" type="text" value="{{ old('title_tr', $ex?->translations['tr']['title'] ?? '') }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Açıklama (EN) <span class="text-red-500">*</span></label>
            <textarea name="description" rows="3"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500" required>{{ old('description', $ex?->description) }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Açıklama (TR)</label>
            <textarea name="description_tr" rows="3"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">{{ old('description_tr', $ex?->translations['tr']['description'] ?? '') }}</textarea>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Slug <span class="text-red-500">*</span></label>
            <input name="slug" type="text" value="{{ old('slug', $ex?->slug) }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-purple-500" required>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Sıra No <span class="text-red-500">*</span></label>
            <input name="sort_order" type="number" min="1" max="999" value="{{ old('sort_order', $ex?->sort_order ?? 1) }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500" required>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Süre (dk) <span class="text-red-500">*</span></label>
            <input name="estimated_duration_minutes" type="number" min="1" value="{{ old('estimated_duration_minutes', $ex?->estimated_duration_minutes ?? 5) }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500" required>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Etiketler (virgülle ayır)</label>
            <input name="tags" type="text" value="{{ old('tags', $ex ? implode(', ', $ex->tags ?? []) : '') }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500"
                   placeholder="intervals, beginner, speed">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Geliştirilen Beceriler (virgülle ayır)</label>
            <input name="skills_trained" type="text" value="{{ old('skills_trained', $ex ? implode(', ', $ex->skills_trained ?? []) : '') }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500"
                   placeholder="interval-recognition, audiation">
        </div>
    </div>

    <div class="flex items-center gap-2">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" id="is_active" value="1"
               @checked(old('is_active', $ex?->is_active ?? true))
               class="w-4 h-4 text-purple-600 rounded border-gray-300">
        <label for="is_active" class="text-sm font-medium text-gray-700">Aktif (Learning Path'te görünsün)</label>
    </div>

    {{-- ── Config JSON Section ── --}}
    <div class="border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-5 py-4 bg-gradient-to-r from-purple-50 to-orange-50 border-b border-gray-200">
            <h3 class="font-semibold text-gray-800 text-sm flex items-center gap-2">
                <i data-lucide="settings-2" class="w-4 h-4 text-purple-600"></i>
                Egzersiz Konfigürasyonu
            </h3>
            <p class="text-xs text-gray-500 mt-0.5">Egzersiz tipini seçin, ardından soruları kontrol eden parametreleri ayarlayın.</p>
        </div>

        <div class="p-5 space-y-5 bg-white">

            {{-- Practice Type Selector --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Egzersiz Tipi <span class="text-red-500">*</span></label>
                <select x-model="practiceType" @change="onTypeChange()"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                    <option value="">— Tip Seçin —</option>
                    @foreach($practiceTypes as $val => $label)
                    <option value="{{ $val }}" {{ $practiceType === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Question Counts: shown for all types --}}
            <div x-show="practiceType !== ''" class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ücretsiz Soru Sayısı</label>
                    <input type="number" min="1" max="50" x-model="qFree"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Premium Soru Sayısı</label>
                    <input type="number" min="1" max="100" x-model="qPremium"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                </div>
            </div>

            {{-- INTERVAL TYPES (melodic, harmonic, direction, construction, comparison) --}}
            <template x-if="isIntervalType()">
                <div class="space-y-4">
                    {{-- Allowed Intervals --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-medium text-gray-700">İzin Verilen İntervallar</label>
                            <div class="flex gap-2">
                                <button type="button" @click="selectAllIntervals()"
                                        class="text-xs text-purple-600 hover:text-purple-800">Tümünü Seç</button>
                                <span class="text-xs text-gray-300">|</span>
                                <button type="button" @click="clearIntervals()"
                                        class="text-xs text-gray-500 hover:text-gray-700">Temizle</button>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            @foreach($allIntervals as $interval)
                            <label class="flex items-center gap-2 text-sm cursor-pointer hover:bg-gray-50 rounded px-2 py-1">
                                <input type="checkbox"
                                       :checked="allowedIntervals.includes('{{ $interval }}')"
                                       @change="toggleInterval('{{ $interval }}')"
                                       class="w-3.5 h-3.5 text-purple-600 rounded border-gray-300">
                                <span class="text-gray-700">{{ $interval }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Allowed Notes --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-medium text-gray-700">Başlangıç Notları</label>
                            <div class="flex gap-2">
                                <button type="button" @click="selectAllNotes()"
                                        class="text-xs text-purple-600 hover:text-purple-800">Tümünü Seç</button>
                                <span class="text-xs text-gray-300">|</span>
                                <button type="button" @click="clearNotes()"
                                        class="text-xs text-gray-500 hover:text-gray-700">Temizle</button>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @foreach($allNotes as $note)
                            <label class="flex items-center gap-1.5 text-sm cursor-pointer px-2.5 py-1.5 rounded-lg border border-gray-200 hover:border-purple-300"
                                   :class="allowedNotes.includes('{{ $note }}') ? 'bg-purple-100 border-purple-400 text-purple-800' : 'bg-white text-gray-700'">
                                <input type="checkbox"
                                       :checked="allowedNotes.includes('{{ $note }}')"
                                       @change="toggleNote('{{ $note }}')"
                                       class="sr-only">
                                <span class="font-medium text-xs">{{ $note }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Octave Range --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Oktav Aralığı</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($allOctaves as $oct)
                            <label class="flex items-center gap-1.5 text-sm cursor-pointer px-3 py-1.5 rounded-lg border border-gray-200 hover:border-purple-300"
                                   :class="octaveRange.includes('{{ $oct }}') ? 'bg-purple-100 border-purple-400 text-purple-800' : 'bg-white text-gray-700'">
                                <input type="checkbox"
                                       :checked="octaveRange.includes('{{ $oct }}')"
                                       @change="toggleOctave('{{ $oct }}')"
                                       class="sr-only">
                                <span class="font-medium text-xs">{{ $oct }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Direction (only for interval-direction-practice) --}}
                    <template x-if="practiceType === 'interval-direction-practice'">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Yön</label>
                            <div class="flex gap-3">
                                @foreach(['ascending'=>'Artan','descending'=>'Azalan','both'=>'İkisi de'] as $dir => $label)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" x-model="direction" value="{{ $dir }}"
                                           class="w-4 h-4 text-purple-600 border-gray-300">
                                    <span class="text-sm text-gray-700">{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- SCALE PRACTICE --}}
            <template x-if="practiceType === 'scale-practice'">
                <div class="space-y-4">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-medium text-gray-700">İzin Verilen Gamlar</label>
                            <button type="button" @click="selectAllScales()"
                                    class="text-xs text-purple-600 hover:text-purple-800">Tümünü Seç</button>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            @foreach($allScales as $scale)
                            <label class="flex items-center gap-2 text-sm cursor-pointer hover:bg-gray-50 rounded px-2 py-1">
                                <input type="checkbox"
                                       :checked="allowedScales.includes('{{ $scale }}')"
                                       @change="toggleScale('{{ $scale }}')"
                                       class="w-3.5 h-3.5 text-purple-600 rounded border-gray-300">
                                <span class="text-gray-700">{{ $scale }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Başlangıç Notları</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($allNotes as $note)
                            <label class="flex items-center gap-1.5 text-sm cursor-pointer px-2.5 py-1.5 rounded-lg border border-gray-200 hover:border-purple-300"
                                   :class="allowedNotes.includes('{{ $note }}') ? 'bg-purple-100 border-purple-400 text-purple-800' : 'bg-white text-gray-700'">
                                <input type="checkbox"
                                       :checked="allowedNotes.includes('{{ $note }}')"
                                       @change="toggleNote('{{ $note }}')"
                                       class="sr-only">
                                <span class="font-medium text-xs">{{ $note }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Oktav Aralığı</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($allOctaves as $oct)
                            <label class="flex items-center gap-1.5 text-sm cursor-pointer px-3 py-1.5 rounded-lg border border-gray-200 hover:border-purple-300"
                                   :class="octaveRange.includes('{{ $oct }}') ? 'bg-purple-100 border-purple-400 text-purple-800' : 'bg-white text-gray-700'">
                                <input type="checkbox"
                                       :checked="octaveRange.includes('{{ $oct }}')"
                                       @change="toggleOctave('{{ $oct }}')"
                                       class="sr-only">
                                <span class="font-medium text-xs">{{ $oct }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </template>

            {{-- CHORD PRACTICE --}}
            <template x-if="practiceType === 'chord-practice'">
                <div class="space-y-4">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-medium text-gray-700">İzin Verilen Akorlar</label>
                            <button type="button" @click="selectAllChords()"
                                    class="text-xs text-purple-600 hover:text-purple-800">Tümünü Seç</button>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            @foreach($allChords as $chord)
                            <label class="flex items-center gap-2 text-sm cursor-pointer hover:bg-gray-50 rounded px-2 py-1">
                                <input type="checkbox"
                                       :checked="allowedChords.includes('{{ $chord }}')"
                                       @change="toggleChord('{{ $chord }}')"
                                       class="w-3.5 h-3.5 text-purple-600 rounded border-gray-300">
                                <span class="text-gray-700">{{ $chord }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kök Notalar</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($allNotes as $note)
                            <label class="flex items-center gap-1.5 text-sm cursor-pointer px-2.5 py-1.5 rounded-lg border border-gray-200 hover:border-purple-300"
                                   :class="allowedNotes.includes('{{ $note }}') ? 'bg-purple-100 border-purple-400 text-purple-800' : 'bg-white text-gray-700'">
                                <input type="checkbox"
                                       :checked="allowedNotes.includes('{{ $note }}')"
                                       @change="toggleNote('{{ $note }}')"
                                       class="sr-only">
                                <span class="font-medium text-xs">{{ $note }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                            <input type="checkbox" x-model="includeInversions"
                                   class="w-4 h-4 text-purple-600 rounded border-gray-300">
                            <span class="font-medium text-gray-700">Çevrimleri Dahil Et (inversions)</span>
                        </label>
                    </div>
                </div>
            </template>

            {{-- RHYTHM PRACTICE --}}
            <template x-if="practiceType === 'rhythm-practice'">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Time Signatures</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($allTimeSignatures as $ts)
                            <label class="flex items-center gap-1.5 text-sm cursor-pointer px-3 py-1.5 rounded-lg border border-gray-200 hover:border-purple-300"
                                   :class="timeSignatures.includes('{{ $ts }}') ? 'bg-purple-100 border-purple-400 text-purple-800' : 'bg-white text-gray-700'">
                                <input type="checkbox"
                                       :checked="timeSignatures.includes('{{ $ts }}')"
                                       @change="toggleTimeSignature('{{ $ts }}')"
                                       class="sr-only">
                                <span class="font-medium text-xs">{{ $ts }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nota Değerleri</label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            @foreach($allNoteValues as $nv)
                            <label class="flex items-center gap-2 text-sm cursor-pointer hover:bg-gray-50 rounded px-2 py-1">
                                <input type="checkbox"
                                       :checked="noteValues.includes('{{ $nv }}')"
                                       @change="toggleNoteValue('{{ $nv }}')"
                                       class="w-3.5 h-3.5 text-purple-600 rounded border-gray-300">
                                <span class="text-gray-700">{{ $nv }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </template>

            {{-- SINGLE NOTE PRACTICE --}}
            <template x-if="practiceType === 'single-note-practice'">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">İzin Verilen Notlar</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($allNotes as $note)
                            <label class="flex items-center gap-1.5 text-sm cursor-pointer px-2.5 py-1.5 rounded-lg border border-gray-200 hover:border-purple-300"
                                   :class="allowedNotes.includes('{{ $note }}') ? 'bg-purple-100 border-purple-400 text-purple-800' : 'bg-white text-gray-700'">
                                <input type="checkbox"
                                       :checked="allowedNotes.includes('{{ $note }}')"
                                       @change="toggleNote('{{ $note }}')"
                                       class="sr-only">
                                <span class="font-medium text-xs">{{ $note }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Oktav Aralığı</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($allOctaves as $oct)
                            <label class="flex items-center gap-1.5 text-sm cursor-pointer px-3 py-1.5 rounded-lg border border-gray-200 hover:border-purple-300"
                                   :class="octaveRange.includes('{{ $oct }}') ? 'bg-purple-100 border-purple-400 text-purple-800' : 'bg-white text-gray-700'">
                                <input type="checkbox"
                                       :checked="octaveRange.includes('{{ $oct }}')"
                                       @change="toggleOctave('{{ $oct }}')"
                                       class="sr-only">
                                <span class="font-medium text-xs">{{ $oct }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nota Anahtarı (Clef)</label>
                        <div class="flex gap-4">
                            @foreach($allClefs as $clef)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" x-model="clef" value="{{ $clef }}"
                                       class="w-4 h-4 text-purple-600 border-gray-300">
                                <span class="text-sm text-gray-700 capitalize">{{ $clef }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </template>

            {{-- MELODIC DICTATION --}}
            <template x-if="practiceType === 'melodic-dictation'">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nota Havuzu</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($allNotes as $note)
                            <label class="flex items-center gap-1.5 text-sm cursor-pointer px-2.5 py-1.5 rounded-lg border border-gray-200 hover:border-purple-300"
                                   :class="allowedNotes.includes('{{ $note }}') ? 'bg-purple-100 border-purple-400 text-purple-800' : 'bg-white text-gray-700'">
                                <input type="checkbox"
                                       :checked="allowedNotes.includes('{{ $note }}')"
                                       @change="toggleNote('{{ $note }}')"
                                       class="sr-only">
                                <span class="font-medium text-xs">{{ $note }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sekans Uzunluğu (nota sayısı)</label>
                            <input type="number" min="2" max="16" x-model="sequenceLength"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Oktav Aralığı</label>
                            <div class="flex flex-wrap gap-2 pt-1">
                                @foreach($allOctaves as $oct)
                                <label class="flex items-center gap-1 text-xs cursor-pointer px-2 py-1 rounded border border-gray-200"
                                       :class="octaveRange.includes('{{ $oct }}') ? 'bg-purple-100 border-purple-400 text-purple-800' : 'bg-white text-gray-700'">
                                    <input type="checkbox"
                                           :checked="octaveRange.includes('{{ $oct }}')"
                                           @change="toggleOctave('{{ $oct }}')"
                                           class="sr-only">
                                    {{ $oct }}
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Advanced: Raw JSON toggle --}}
            <div x-show="practiceType !== ''" class="border-t border-gray-100 pt-4">
                <button type="button" @click="showRawJson = !showRawJson"
                        class="text-xs text-gray-400 hover:text-gray-600 flex items-center gap-1">
                    <i data-lucide="code" class="w-3.5 h-3.5"></i>
                    <span x-text="showRawJson ? 'Ham JSON\'u Gizle' : 'Gelişmiş: Ham JSON\'u Göster'"></span>
                </button>
                <div x-show="showRawJson" class="mt-2">
                    <pre class="text-xs bg-gray-50 border border-gray-200 rounded-lg p-3 overflow-auto max-h-40 text-gray-600"
                         x-text="JSON.stringify(buildConfig(), null, 2)"></pre>
                    <p class="text-xs text-gray-400 mt-1">Bu JSON form gönderildiğinde otomatik oluşturulur.</p>
                </div>
            </div>

        </div>
    </div>

    {{-- Hidden config_json field - auto-populated on submit --}}
    <input type="hidden" name="config_json" :value="JSON.stringify(buildConfig())" x-ref="configJsonField">

</div>

<script>
function exerciseFormConfig() {
    return {
        practiceType: @json($practiceType),
        qFree:        @json($cfg['question_counts']['free'] ?? 5),
        qPremium:     @json($cfg['question_counts']['premium'] ?? 15),

        allowedIntervals: @json($cfg['allowed_intervals'] ?? []),
        allowedNotes:     @json($cfg['allowed_notes'] ?? []),
        octaveRange:      @json($cfg['octave_range'] ?? ['3','4']),
        direction:        @json($cfg['direction'] ?? 'both'),

        allowedScales: @json($cfg['allowed_scales'] ?? []),
        allowedChords: @json($cfg['allowed_chords'] ?? []),
        includeInversions: @json($cfg['include_inversions'] ?? false),

        timeSignatures: @json($cfg['time_signatures'] ?? ['4/4']),
        noteValues:     @json($cfg['note_values'] ?? ['quarter','eighth']),

        clef:           @json($cfg['clef'] ?? 'treble'),
        sequenceLength: @json($cfg['sequence_length'] ?? 4),

        showRawJson: false,

        init() {
            // ensure config_json is correct on load
            this.$nextTick(() => this.syncConfig());
        },

        onTypeChange() {
            this.syncConfig();
        },

        syncConfig() {
            // config_json hidden field is bound reactively via :value
        },

        isIntervalType() {
            return [
                'melodic-interval-practice',
                'harmonic-interval-practice',
                'interval-direction-practice',
                'interval-construction-practice',
                'interval-comparison-practice',
            ].includes(this.practiceType);
        },

        buildConfig() {
            const base = {
                practice_type:  this.practiceType,
                question_counts: {
                    free:    parseInt(this.qFree) || 5,
                    premium: parseInt(this.qPremium) || 15,
                },
            };

            if (this.isIntervalType()) {
                base.allowed_intervals = this.allowedIntervals;
                base.allowed_notes     = this.allowedNotes;
                base.octave_range      = this.octaveRange;
                if (this.practiceType === 'interval-direction-practice') {
                    base.direction = this.direction;
                }
            } else if (this.practiceType === 'scale-practice') {
                base.allowed_scales = this.allowedScales;
                base.allowed_notes  = this.allowedNotes;
                base.octave_range   = this.octaveRange;
            } else if (this.practiceType === 'chord-practice') {
                base.allowed_chords    = this.allowedChords;
                base.allowed_notes     = this.allowedNotes;
                base.include_inversions = this.includeInversions;
            } else if (this.practiceType === 'rhythm-practice') {
                base.time_signatures = this.timeSignatures;
                base.note_values     = this.noteValues;
            } else if (this.practiceType === 'single-note-practice') {
                base.allowed_notes = this.allowedNotes;
                base.octave_range  = this.octaveRange;
                base.clef          = this.clef;
            } else if (this.practiceType === 'melodic-dictation') {
                base.note_pool      = this.allowedNotes;
                base.octave_range   = this.octaveRange;
                base.sequence_length = parseInt(this.sequenceLength) || 4;
            }

            return base;
        },

        // Toggle helpers
        toggleInterval(v) { this._toggle('allowedIntervals', v); },
        toggleNote(v)     { this._toggle('allowedNotes', v); },
        toggleOctave(v)   { this._toggle('octaveRange', v); },
        toggleScale(v)    { this._toggle('allowedScales', v); },
        toggleChord(v)    { this._toggle('allowedChords', v); },
        toggleTimeSignature(v) { this._toggle('timeSignatures', v); },
        toggleNoteValue(v) { this._toggle('noteValues', v); },

        _toggle(key, val) {
            const idx = this[key].indexOf(val);
            if (idx === -1) this[key].push(val);
            else this[key].splice(idx, 1);
        },

        selectAllIntervals() {
            this.allowedIntervals = @json($allIntervals);
        },
        clearIntervals() { this.allowedIntervals = []; },
        selectAllNotes() { this.allowedNotes = @json($allNotes); },
        clearNotes()     { this.allowedNotes = []; },
        selectAllScales() { this.allowedScales = @json($allScales); },
        selectAllChords() { this.allowedChords = @json($allChords); },
    };
}
</script>
