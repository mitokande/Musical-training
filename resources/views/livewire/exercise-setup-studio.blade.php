<div>
    {{-- AI Recommendation Panel --}}
    @if($aiLoading)
        <div class="flex items-center justify-center gap-3 py-4 text-purple-600">
            <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
            <span class="text-sm font-medium">{{ __('app.exercises.ai_analyzing') }}</span>
        </div>
    @endif

    @if($aiError)
        <div class="bg-red-50 border border-red-200 rounded-xl p-3 text-sm text-red-700 flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
            {{ $aiError }}
        </div>
    @endif

    @if($aiRecommendation)
        <div class="bg-purple-50 border border-purple-200 rounded-xl p-4 space-y-3"
             x-data
             x-init="$dispatch('ai-recommendation-ready', {{ json_encode($aiRecommendation) }})">
            <div class="flex items-start gap-2">
                <i data-lucide="sparkles" class="w-5 h-5 text-purple-600 flex-shrink-0 mt-0.5"></i>
                <div>
                    <p class="text-sm font-semibold text-purple-900">{{ __('app.exercises.ai_recommendation_label') }}: {{ $aiRecommendation['category'] ?? '' }}</p>
                    <p class="text-sm text-purple-700 mt-1">{{ $aiRecommendation['explanation'] ?? '' }}</p>
                </div>
            </div>
            <div class="flex gap-2">
                <button @click="$dispatch('apply-ai-recommendation', {{ json_encode($aiRecommendation) }})"
                    class="flex-1 bg-purple-600 text-white text-sm font-semibold py-2 px-3 rounded-lg hover:bg-purple-700 transition-colors flex items-center justify-center gap-1">
                    <i data-lucide="check" class="w-4 h-4"></i> {{ __('app.exercises.apply') }}
                </button>
                <button wire:click="$set('aiRecommendation', null)"
                    class="text-purple-600 text-sm font-medium py-2 px-3 rounded-lg hover:bg-purple-100 transition-colors">
                    {{ __('app.common.close') }}
                </button>
            </div>
        </div>
    @endif

    {{-- Saved Plans --}}
    <div>
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-1.5">
                <i data-lucide="bookmark" class="w-4 h-4 text-purple-600"></i>
                {{ __('app.exercises.saved_plans') }}
            </h3>
            <span class="text-xs text-gray-400">
                {{ count($templates) }}{{ $savedPlansLimit !== -1 ? '/'.$savedPlansLimit : '' }}
            </span>
        </div>

        @if(count($templates) === 0)
            <p class="text-xs text-gray-400 text-center py-3">{{ __('app.exercises.no_saved_plans') }}</p>
        @else
            <div class="space-y-2">
                @foreach($templates as $template)
                    <div class="flex items-center gap-2 p-3 bg-white border border-gray-200 rounded-xl hover:border-purple-300 transition-colors group">
                        <button @click="$dispatch('load-plan', {{ json_encode($template) }})"
                            class="flex-1 text-left min-w-0">
                            <div class="flex items-center gap-1.5">
                                @if($template['is_favorite'])
                                    <i data-lucide="star" class="w-3.5 h-3.5 text-yellow-500 flex-shrink-0 fill-yellow-500"></i>
                                @endif
                                @if($template['is_ai_generated'])
                                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-purple-500 flex-shrink-0"></i>
                                @endif
                                <span class="text-sm font-medium text-gray-900 truncate">{{ $template['name'] }}</span>
                            </div>
                            <p class="text-xs text-gray-400 truncate mt-0.5">{{ $template['category'] }}</p>
                        </button>
                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button wire:click="toggleFavorite({{ $template['id'] }})"
                                class="w-7 h-7 rounded-lg hover:bg-yellow-50 flex items-center justify-center text-gray-400 hover:text-yellow-500 transition-colors">
                                <i data-lucide="{{ $template['is_favorite'] ? 'star' : 'star' }}" class="w-3.5 h-3.5 {{ $template['is_favorite'] ? 'fill-yellow-500 text-yellow-500' : '' }}"></i>
                            </button>
                            <button wire:click="deletePlan({{ $template['id'] }})"
                                wire:confirm="{{ __('app.exercises.delete_plan_confirm') }}"
                                class="w-7 h-7 rounded-lg hover:bg-red-50 flex items-center justify-center text-gray-400 hover:text-red-500 transition-colors">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
