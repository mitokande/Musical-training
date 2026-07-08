@extends('admin.layouts.admin')

@section('page-title', 'Music Games')

@section('content')
<div class="space-y-6">

    {{-- Per-game stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($games as $slug => $name)
        @php $gs = $gameStats->get($slug); @endphp
        <a href="{{ route('admin.games.index', ['game' => $slug]) }}" class="card p-5 hover:shadow-md transition-shadow {{ request('game') === $slug ? 'ring-2 ring-purple-500' : '' }}">
            <p class="text-sm font-semibold text-gray-900">{{ $name }}</p>
            <div class="mt-2 space-y-1 text-xs text-gray-500">
                <p>Plays: <span class="font-semibold text-gray-800">{{ number_format($gs->plays ?? 0) }}</span></p>
                <p>Players: <span class="font-semibold text-gray-800">{{ number_format($gs->players ?? 0) }}</span></p>
                <p>Top score: <span class="font-semibold text-gray-800">{{ number_format($gs->top_score ?? 0) }}</span></p>
            </div>
        </a>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
        {{-- Leaderboard --}}
        <div class="card p-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-2">
                    <i data-lucide="trophy" class="w-5 h-5 text-orange-500"></i>
                    <h2 class="text-lg font-semibold text-gray-900">
                        Top Scores {{ request('game') ? '— '.($games[request('game')] ?? request('game')) : '' }}
                    </h2>
                </div>
                @if(request('game'))
                <a href="{{ route('admin.games.index') }}" class="text-sm text-purple-600 hover:text-purple-700 font-medium">All games</a>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left">
                            <th class="py-2.5 px-3 font-semibold text-gray-600">Player</th>
                            <th class="py-2.5 px-3 font-semibold text-gray-600">Game</th>
                            <th class="py-2.5 px-3 font-semibold text-gray-600 text-right">Score</th>
                            <th class="py-2.5 px-3 font-semibold text-gray-600 whitespace-nowrap">Date</th>
                            <th class="py-2.5 px-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($scores as $score)
                        <tr class="hover:bg-gray-50">
                            <td class="py-2.5 px-3">
                                <p class="font-medium text-gray-900">{{ $score->user->name ?? 'Deleted user' }}</p>
                                <p class="text-xs text-gray-400">{{ $score->user->email ?? '' }}</p>
                            </td>
                            <td class="py-2.5 px-3 text-gray-600">{{ $games[$score->game_slug] ?? $score->game_slug }}</td>
                            <td class="py-2.5 px-3 text-right font-semibold text-gray-900">{{ number_format($score->score) }}</td>
                            <td class="py-2.5 px-3 text-gray-500 whitespace-nowrap">{{ $score->created_at->format('M d, Y') }}</td>
                            <td class="py-2.5 px-3 text-right">
                                <form action="{{ route('admin.games.scores.destroy', $score) }}" method="POST" class="inline" onsubmit="return confirm('Delete this score? Use this to remove suspicious/cheated scores.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete score">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="py-8 text-center text-gray-500">No scores recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($scores->hasPages())
            <div class="mt-4">{{ $scores->links() }}</div>
            @endif
        </div>

        {{-- Settings --}}
        <div class="card p-6">
            <div class="flex items-center gap-2 mb-6">
                <i data-lucide="gamepad-2" class="w-5 h-5 text-purple-600"></i>
                <h2 class="text-lg font-semibold text-gray-900">Game Configuration</h2>
            </div>

            <form action="{{ route('admin.games.settings.update') }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="flex items-center justify-between py-3 border-b border-gray-100">
                    <div>
                        <label for="game_enabled" class="text-sm font-medium text-gray-700">Games Enabled</label>
                        <p class="text-xs text-gray-400">Enable music games for all users</p>
                    </div>
                    <div>
                        <input type="hidden" name="game_enabled" value="0">
                        <input type="checkbox" name="game_enabled" id="game_enabled" value="1"
                               {{ $settings['game_enabled'] ? 'checked' : '' }}
                               class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                    </div>
                </div>

                <div class="flex items-center justify-between py-3 border-b border-gray-100">
                    <div>
                        <label for="game_difficulty_levels" class="text-sm font-medium text-gray-700">Difficulty Levels</label>
                        <p class="text-xs text-gray-400">Comma-separated list of difficulty levels</p>
                    </div>
                    <div class="w-48">
                        <input type="text" name="game_difficulty_levels" id="game_difficulty_levels"
                               value="{{ $settings['game_difficulty_levels'] }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>
                </div>

                <div class="flex items-center justify-between py-3 border-b border-gray-100">
                    <div>
                        <label for="game_time_limit" class="text-sm font-medium text-gray-700">Default Time Limit</label>
                        <p class="text-xs text-gray-400">Default game time limit in seconds</p>
                    </div>
                    <div class="w-32">
                        <input type="number" name="game_time_limit" id="game_time_limit"
                               value="{{ $settings['game_time_limit'] }}" min="10" max="300"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>
                </div>

                <div class="flex items-center justify-between py-3 border-b border-gray-100">
                    <div>
                        <label for="game_points_correct" class="text-sm font-medium text-gray-700">Points per Correct Answer</label>
                    </div>
                    <div class="w-32">
                        <input type="number" name="game_points_correct" id="game_points_correct"
                               value="{{ $settings['game_points_correct'] }}" min="1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>
                </div>

                <div class="flex items-center justify-between py-3 border-b border-gray-100">
                    <div>
                        <label for="game_points_streak" class="text-sm font-medium text-gray-700">Streak Bonus Points</label>
                    </div>
                    <div class="w-32">
                        <input type="number" name="game_points_streak" id="game_points_streak"
                               value="{{ $settings['game_points_streak'] }}" min="1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>
                </div>

                <div class="flex items-center justify-between py-3 border-b border-gray-100">
                    <div>
                        <label for="game_leaderboard_size" class="text-sm font-medium text-gray-700">Leaderboard Size</label>
                        <p class="text-xs text-gray-400">Max entries shown on leaderboards</p>
                    </div>
                    <div class="w-32">
                        <input type="number" name="game_leaderboard_size" id="game_leaderboard_size"
                               value="{{ $settings['game_leaderboard_size'] }}" min="10" max="500"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn-primary px-6 py-2.5 text-white text-sm font-semibold rounded-lg transition-all hover:shadow-lg">
                        <i data-lucide="save" class="w-4 h-4 inline mr-1"></i> Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
