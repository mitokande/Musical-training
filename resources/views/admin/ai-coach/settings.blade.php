@extends('admin.layouts.admin')

@section('page-title', 'AI Settings')

@section('content')
<div class="max-w-2xl space-y-6">

    <a href="{{ route('admin.ai-coach-admin.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-purple-600 transition-colors">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to AI Coach
    </a>

    <div class="card p-6">
        <div class="flex items-center gap-2 mb-6">
            <i data-lucide="settings" class="w-5 h-5 text-purple-600"></i>
            <h2 class="text-lg font-semibold text-gray-900">AI Configuration</h2>
        </div>

        <form action="{{ route('admin.ai-coach-admin.settings.update') }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            {{-- Default Model --}}
            <div>
                <label for="default_model" class="block text-sm font-medium text-gray-700 mb-1">Default Model</label>
                <select name="default_model" id="default_model" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <option value="gpt-4o-mini" {{ old('default_model', $settings['default_model'] ?? '') == 'gpt-4o-mini' ? 'selected' : '' }}>GPT-4o Mini</option>
                    <option value="gpt-4o" {{ old('default_model', $settings['default_model'] ?? '') == 'gpt-4o' ? 'selected' : '' }}>GPT-4o</option>
                    <option value="gpt-4.1-mini" {{ old('default_model', $settings['default_model'] ?? '') == 'gpt-4.1-mini' ? 'selected' : '' }}>GPT-4.1 Mini</option>
                </select>
                @error('default_model') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Max Tokens Per Session --}}
            <div>
                <label for="max_tokens_per_session" class="block text-sm font-medium text-gray-700 mb-1">Max Tokens Per Session</label>
                <input type="number" name="max_tokens_per_session" id="max_tokens_per_session"
                       value="{{ old('max_tokens_per_session', $settings['max_tokens_per_session'] ?? 4096) }}"
                       min="100" max="128000"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                @error('max_tokens_per_session') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- AI Prompt Template --}}
            <div>
                <label for="ai_prompt_template" class="block text-sm font-medium text-gray-700 mb-1">AI Prompt Template</label>
                <textarea name="ai_prompt_template" id="ai_prompt_template" rows="6"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                          placeholder="Enter the system prompt template for AI coaching sessions...">{{ old('ai_prompt_template', $settings['ai_prompt_template'] ?? '') }}</textarea>
                @error('ai_prompt_template') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- AI Enabled --}}
            <div class="flex items-center gap-3">
                <input type="hidden" name="ai_enabled" value="0">
                <input type="checkbox" name="ai_enabled" id="ai_enabled" value="1"
                       {{ old('ai_enabled', $settings['ai_enabled'] ?? false) ? 'checked' : '' }}
                       class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                <label for="ai_enabled" class="text-sm font-medium text-gray-700">Enable AI Coach</label>
            </div>

            {{-- Save --}}
            <div class="pt-4 border-t border-gray-200">
                <button type="submit" class="btn-primary px-6 py-2.5 text-white text-sm font-semibold rounded-lg transition-all hover:shadow-lg">
                    <i data-lucide="save" class="w-4 h-4 inline mr-1"></i> Save Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
