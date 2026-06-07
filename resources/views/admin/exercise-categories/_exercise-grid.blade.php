{{-- Exercise Card Grid: 4 per row desktop, 2 tablet, 1 mobile --}}
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
    @foreach($exerciseList as $ex)
    @php
        $levelColors = [
            'beginner'     => ['bg' => 'bg-green-100', 'text' => 'text-green-700'],
            'intermediate' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700'],
            'advanced'     => ['bg' => 'bg-red-100',   'text' => 'text-red-700'],
        ];
        $lc = $levelColors[$ex->level] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-600'];
        $isPremium = isset($ex->config_json['question_counts']['premium'])
            && $ex->config_json['question_counts']['premium'] > ($ex->config_json['question_counts']['free'] ?? 5);
        $attempts  = $ex->user_progress_count ?? 0;
        $avgScore  = round($ex->user_progress_avg_score ?? 0, 1);
    @endphp

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow flex flex-col relative group">

        {{-- Top-right: Edit button --}}
        <div class="absolute top-3 right-3 z-10">
            <a href="{{ route('admin.learning-path-exercises.edit', $ex) }}"
               title="Düzenle"
               class="inline-flex items-center justify-center w-7 h-7 bg-yellow-100 hover:bg-yellow-200 text-yellow-700 rounded-lg transition opacity-80 group-hover:opacity-100">
                <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        {{-- Card body --}}
        <div class="p-4 flex-1">
            {{-- Badges row --}}
            <div class="flex items-center gap-1.5 mb-2 flex-wrap pr-8">
                <span class="px-1.5 py-0.5 text-xs font-medium rounded {{ $lc['bg'] }} {{ $lc['text'] }}">
                    {{ ucfirst($ex->level) }}
                </span>
                @if($isPremium)
                <span class="px-1.5 py-0.5 text-xs font-medium bg-orange-100 text-orange-700 rounded">Premium</span>
                @else
                <span class="px-1.5 py-0.5 text-xs font-medium bg-green-100 text-green-700 rounded">Free</span>
                @endif
                @if($ex->is_active)
                <span class="px-1.5 py-0.5 text-xs font-medium bg-blue-100 text-blue-700 rounded">Aktif</span>
                @else
                <span class="px-1.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-500 rounded">Pasif</span>
                @endif
            </div>

            {{-- Title --}}
            <h4 class="font-semibold text-gray-900 text-sm leading-snug mb-1 line-clamp-2">{{ $ex->title }}</h4>
            <p class="text-xs text-gray-500 line-clamp-2 mb-3">{{ $ex->description }}</p>

            {{-- Practice type --}}
            @if(isset($ex->config_json['practice_type']))
            <p class="text-xs text-gray-400 font-mono mb-3 truncate">{{ $ex->config_json['practice_type'] }}</p>
            @endif

            {{-- Stats --}}
            <div class="grid grid-cols-2 gap-2">
                <div class="bg-gray-50 rounded-lg px-2.5 py-2 text-center">
                    <div class="text-sm font-bold text-gray-800">{{ number_format($attempts) }}</div>
                    <div class="text-xs text-gray-400">Deneme</div>
                </div>
                <div class="bg-gray-50 rounded-lg px-2.5 py-2 text-center">
                    <div class="text-sm font-bold text-gray-800">{{ $avgScore }}%</div>
                    <div class="text-xs text-gray-400">Ort. Skor</div>
                </div>
            </div>

            {{-- Last edited --}}
            <p class="text-xs text-gray-300 mt-3">{{ $ex->updated_at->format('d.m.Y') }}</p>
        </div>

        {{-- Bottom action bar --}}
        <div class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
            {{-- Toggle status --}}
            <form method="POST" action="{{ route('admin.learning-path-exercises.toggle-status', $ex) }}">
                @csrf @method('PATCH')
                <button type="submit"
                        title="{{ $ex->is_active ? 'Pasif yap' : 'Aktif yap' }}"
                        class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-lg transition
                               {{ $ex->is_active ? 'bg-gray-100 text-gray-500 hover:bg-gray-200' : 'bg-green-100 text-green-700 hover:bg-green-200' }}">
                    <i data-lucide="{{ $ex->is_active ? 'pause' : 'play' }}" class="w-3 h-3"></i>
                    {{ $ex->is_active ? 'Pasif Yap' : 'Aktif Yap' }}
                </button>
            </form>

            {{-- Duplicate + Delete --}}
            <div class="flex items-center gap-1.5">
                <form method="POST" action="{{ route('admin.learning-path-exercises.duplicate', $ex) }}">
                    @csrf
                    <button type="submit" title="Kopyala"
                            class="inline-flex items-center justify-center w-7 h-7 bg-gray-100 hover:bg-gray-200 text-gray-500 rounded-lg transition">
                        <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                    </button>
                </form>

                {{-- Delete: opens modal --}}
                <button type="button"
                        title="Sil"
                        @click="$dispatch('open-delete-modal', {
                            url: '{{ route('admin.learning-path-exercises.destroy', $ex) }}',
                            title: '{{ addslashes($ex->title) }}'
                        })"
                        class="inline-flex items-center justify-center w-7 h-7 bg-red-100 hover:bg-red-200 text-red-600 rounded-lg transition">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                </button>
            </div>
        </div>
    </div>
    @endforeach
</div>
