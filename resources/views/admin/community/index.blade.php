@extends('admin.layouts.admin')

@section('page-title', 'Community Feed')

@section('content')
<div class="space-y-6">

    {{-- Stat cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
        <x-admin.stat-card title="Feed Items" :value="$stats['total_items']" icon="rss" color="purple" />
        <x-admin.stat-card title="Posts" :value="$stats['total_posts']" icon="message-square-text" color="blue" />
        <x-admin.stat-card title="Items (7d)" :value="$stats['items_week']" icon="calendar-days" color="green" />
        <x-admin.stat-card title="Likes" :value="$stats['total_likes']" icon="heart" color="pink" />
        <x-admin.stat-card title="Follows" :value="$stats['total_follows']" icon="user-plus" color="orange" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Feed list --}}
        <div class="card p-6 lg:col-span-2">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                <div class="flex items-center gap-2">
                    <i data-lucide="rss" class="w-5 h-5 text-purple-600"></i>
                    <h2 class="text-lg font-semibold text-gray-900">Feed Items</h2>
                </div>
                <form method="GET" class="flex items-center gap-2">
                    <select name="type" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500">
                        <option value="">All types</option>
                        @foreach($types as $type)
                            <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search body..."
                           class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 w-40">
                    <button type="submit" class="p-2 text-gray-500 hover:text-purple-600 hover:bg-purple-50 rounded-lg">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>

            <div class="space-y-3">
                @forelse($feedItems as $item)
                <div class="flex items-start gap-3 p-3 rounded-lg border border-gray-100 hover:bg-gray-50 transition-colors">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-semibold text-sm shrink-0">
                        {{ strtoupper(substr($item->actor->name ?? '?', 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-sm font-medium text-gray-900">{{ $item->actor->name ?? 'Deleted user' }}</span>
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full
                                {{ $item->type === 'post' ? 'bg-blue-100 text-blue-700' : ($item->type === 'achievement' ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-600') }}">
                                {{ str_replace('_', ' ', $item->type) }}
                            </span>
                            <span class="text-xs text-gray-400">{{ $item->created_at->diffForHumans() }}</span>
                        </div>
                        @if($item->body)
                            <p class="text-sm text-gray-700 mt-1 break-words">{{ Str::limit($item->body, 220) }}</p>
                        @endif
                        <p class="text-xs text-gray-400 mt-1 flex items-center gap-1">
                            <i data-lucide="heart" class="w-3 h-3"></i> {{ $item->likes_count }}
                            @if($item->subject) &middot; subject: {{ $item->subject->name }} @endif
                        </p>
                    </div>
                    <form action="{{ route('admin.community.destroy', $item) }}" method="POST" onsubmit="return confirm('Delete this feed item?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
                @empty
                <p class="text-sm text-gray-500 text-center py-8">No feed items found.</p>
                @endforelse
            </div>

            @if($feedItems->hasPages())
            <div class="mt-4">{{ $feedItems->links() }}</div>
            @endif
        </div>

        {{-- Top posters --}}
        <div class="card p-6 h-fit">
            <div class="flex items-center gap-2 mb-5">
                <i data-lucide="trophy" class="w-5 h-5 text-orange-500"></i>
                <h2 class="text-lg font-semibold text-gray-900">Top Posters</h2>
            </div>
            <div class="space-y-3">
                @forelse($topPosters as $i => $row)
                <div class="flex items-center gap-3">
                    <span class="w-6 text-sm font-bold text-gray-400">#{{ $i + 1 }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $row->actor->name ?? 'Deleted user' }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $row->actor->email ?? '' }}</p>
                    </div>
                    <span class="text-sm font-semibold text-gray-900">{{ $row->post_count }}</span>
                </div>
                @empty
                <p class="text-sm text-gray-500 text-center py-4">No posts yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
