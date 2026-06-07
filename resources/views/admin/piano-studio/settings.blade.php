@extends('admin.layouts.admin')

@section('page-title', 'Piano Studio Settings')

@section('content')
<div class="max-w-2xl space-y-6">

    <div class="card p-6">
        <div class="flex items-center gap-2 mb-6">
            <i data-lucide="piano" class="w-5 h-5 text-purple-600"></i>
            <h2 class="text-lg font-semibold text-gray-900">Piano Studio Configuration</h2>
        </div>

        <form action="{{ route('admin.piano-studio.settings.update') }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                <div>
                    <label for="instruments_enabled" class="text-sm font-medium text-gray-700">Instruments Enabled</label>
                    <p class="text-xs text-gray-400">Allow users to switch between piano instruments</p>
                </div>
                <div>
                    <input type="hidden" name="settings[instruments_enabled]" value="0">
                    <input type="checkbox" name="settings[instruments_enabled]" id="instruments_enabled" value="1"
                           {{ ($settings['instruments_enabled'] ?? true) ? 'checked' : '' }}
                           class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                </div>
            </div>

            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                <div>
                    <label for="sound_library" class="text-sm font-medium text-gray-700">Sound Library</label>
                    <p class="text-xs text-gray-400">Default sound library for the piano</p>
                </div>
                <div class="w-48">
                    <select name="settings[sound_library]" id="sound_library"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="default" {{ ($settings['sound_library'] ?? '') == 'default' ? 'selected' : '' }}>Default</option>
                        <option value="grand_piano" {{ ($settings['sound_library'] ?? '') == 'grand_piano' ? 'selected' : '' }}>Grand Piano</option>
                        <option value="electric" {{ ($settings['sound_library'] ?? '') == 'electric' ? 'selected' : '' }}>Electric Piano</option>
                        <option value="synth" {{ ($settings['sound_library'] ?? '') == 'synth' ? 'selected' : '' }}>Synthesizer</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                <div>
                    <label for="practice_mode" class="text-sm font-medium text-gray-700">Practice Mode</label>
                    <p class="text-xs text-gray-400">Default practice mode for new users</p>
                </div>
                <div class="w-48">
                    <select name="settings[practice_mode]" id="practice_mode"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="free_play" {{ ($settings['practice_mode'] ?? '') == 'free_play' ? 'selected' : '' }}>Free Play</option>
                        <option value="guided" {{ ($settings['practice_mode'] ?? '') == 'guided' ? 'selected' : '' }}>Guided</option>
                        <option value="challenge" {{ ($settings['practice_mode'] ?? '') == 'challenge' ? 'selected' : '' }}>Challenge</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                <div>
                    <label for="midi_enabled" class="text-sm font-medium text-gray-700">MIDI Support</label>
                    <p class="text-xs text-gray-400">Enable MIDI keyboard input</p>
                </div>
                <div>
                    <input type="hidden" name="settings[midi_enabled]" value="0">
                    <input type="checkbox" name="settings[midi_enabled]" id="midi_enabled" value="1"
                           {{ ($settings['midi_enabled'] ?? true) ? 'checked' : '' }}
                           class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                </div>
            </div>

            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                <div>
                    <label for="recording_enabled" class="text-sm font-medium text-gray-700">Recording</label>
                    <p class="text-xs text-gray-400">Allow users to record their sessions</p>
                </div>
                <div>
                    <input type="hidden" name="settings[recording_enabled]" value="0">
                    <input type="checkbox" name="settings[recording_enabled]" id="recording_enabled" value="1"
                           {{ ($settings['recording_enabled'] ?? true) ? 'checked' : '' }}
                           class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="btn-primary px-6 py-2.5 text-white text-sm font-semibold rounded-lg transition-all hover:shadow-lg">
                    <i data-lucide="save" class="w-4 h-4 inline mr-1"></i> Save Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
