@extends('admin.layouts.admin')

@section('page-title', 'Music Games Settings')

@section('content')
<div class="max-w-2xl space-y-6">

    <div class="card p-6">
        <div class="flex items-center gap-2 mb-6">
            <i data-lucide="gamepad-2" class="w-5 h-5 text-purple-600"></i>
            <h2 class="text-lg font-semibold text-gray-900">Game Configuration</h2>
        </div>

        <form action="{{ route('admin.games.update') }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                <div>
                    <label for="games_enabled" class="text-sm font-medium text-gray-700">Games Enabled</label>
                    <p class="text-xs text-gray-400">Enable music games for all users</p>
                </div>
                <div>
                    <input type="hidden" name="settings[games_enabled]" value="0">
                    <input type="checkbox" name="settings[games_enabled]" id="games_enabled" value="1"
                           {{ ($settings['games_enabled'] ?? true) ? 'checked' : '' }}
                           class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                </div>
            </div>

            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                <div>
                    <label for="difficulty_levels" class="text-sm font-medium text-gray-700">Difficulty Levels</label>
                    <p class="text-xs text-gray-400">Number of difficulty levels available</p>
                </div>
                <div class="w-32">
                    <input type="number" name="settings[difficulty_levels]" id="difficulty_levels"
                           value="{{ $settings['difficulty_levels'] ?? 5 }}" min="1" max="10"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                </div>
            </div>

            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                <div>
                    <label for="leaderboard_enabled" class="text-sm font-medium text-gray-700">Leaderboard</label>
                    <p class="text-xs text-gray-400">Show global leaderboard in games</p>
                </div>
                <div>
                    <input type="hidden" name="settings[leaderboard_enabled]" value="0">
                    <input type="checkbox" name="settings[leaderboard_enabled]" id="leaderboard_enabled" value="1"
                           {{ ($settings['leaderboard_enabled'] ?? true) ? 'checked' : '' }}
                           class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                </div>
            </div>

            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                <div>
                    <label for="time_limit" class="text-sm font-medium text-gray-700">Default Time Limit</label>
                    <p class="text-xs text-gray-400">Default game time limit in seconds</p>
                </div>
                <div class="w-32">
                    <input type="number" name="settings[time_limit]" id="time_limit"
                           value="{{ $settings['time_limit'] ?? 60 }}" min="10" max="600"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                </div>
            </div>

            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                <div>
                    <label for="sound_effects" class="text-sm font-medium text-gray-700">Sound Effects</label>
                    <p class="text-xs text-gray-400">Enable game sound effects</p>
                </div>
                <div>
                    <input type="hidden" name="settings[sound_effects]" value="0">
                    <input type="checkbox" name="settings[sound_effects]" id="sound_effects" value="1"
                           {{ ($settings['sound_effects'] ?? true) ? 'checked' : '' }}
                           class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                </div>
            </div>

            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                <div>
                    <label for="achievements_enabled" class="text-sm font-medium text-gray-700">Achievements</label>
                    <p class="text-xs text-gray-400">Enable achievement badges in games</p>
                </div>
                <div>
                    <input type="hidden" name="settings[achievements_enabled]" value="0">
                    <input type="checkbox" name="settings[achievements_enabled]" id="achievements_enabled" value="1"
                           {{ ($settings['achievements_enabled'] ?? true) ? 'checked' : '' }}
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
