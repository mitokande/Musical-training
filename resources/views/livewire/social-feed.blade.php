<div class="w-full space-y-4">

    {{-- Composer --}}
    <div class="card p-5">
        <div class="flex gap-3">
            @php $me = auth()->user(); @endphp
            @if($me->hasAvatar())
                <img src="{{ $me->avatar }}" alt="" class="w-10 h-10 rounded-full object-cover shrink-0">
            @else
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-semibold shrink-0">
                    {{ substr($me->name ?? 'U', 0, 1) }}
                </div>
            @endif
            <div class="flex-1">
                <textarea wire:model="postBody" rows="2"
                          placeholder="{{ __('app.social.write_something') }}"
                          class="w-full resize-none rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500"></textarea>
                @error('postBody') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                <div class="flex justify-end mt-2">
                    <button wire:click="post" wire:loading.attr="disabled"
                            class="btn-primary text-white font-semibold text-sm py-2 px-5 rounded-xl inline-flex items-center gap-2 disabled:opacity-50">
                        <i data-lucide="send" class="w-4 h-4"></i> {{ __('app.social.post') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Scope toggle: Feed (all) / Following --}}
    <div class="card p-1.5 flex gap-1.5">
        <button wire:click="setScope('feed')"
                class="flex-1 flex items-center justify-center gap-2 py-2 rounded-xl text-sm font-semibold transition-all
                       {{ $scope === 'feed' ? 'btn-primary text-white' : 'text-gray-600 hover:bg-gray-50' }}">
            <i data-lucide="globe" class="w-4 h-4"></i> {{ __('app.social.scope_feed') }}
        </button>
        <button wire:click="setScope('following')"
                class="flex-1 flex items-center justify-center gap-2 py-2 rounded-xl text-sm font-semibold transition-all
                       {{ $scope === 'following' ? 'btn-primary text-white' : 'text-gray-600 hover:bg-gray-50' }}">
            <i data-lucide="user-check" class="w-4 h-4"></i> {{ __('app.social.scope_following') }}
        </button>
    </div>

    {{-- Feed --}}
    @forelse($items as $item)
        @php
            $actor = $item->actor;
            $liked = in_array($item->id, $likedIds);
        @endphp
        <div class="card p-5" wire:key="feed-{{ $item->id }}">
            <div class="flex gap-3">
                {{-- Avatar --}}
                <a href="{{ $actor ? url('/u/'.$actor->username) : '#' }}" class="shrink-0">
                    @if($actor && $actor->hasAvatar())
                        {{-- Uploaded photos render at 2× the placeholder size --}}
                        <img src="{{ $actor->avatar }}" alt="" class="w-20 h-20 rounded-full object-cover">
                    @else
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-semibold">
                            {{ substr($actor->name ?? 'U', 0, 1) }}
                        </div>
                    @endif
                </a>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2">
                        <div class="text-sm">
                            <a href="{{ $actor ? url('/u/'.$actor->username) : '#' }}" class="font-semibold text-gray-900 hover:underline">{{ $actor->name ?? __('app.social.someone') }}</a>
                            <span class="text-gray-400">· {{ $item->created_at->diffForHumans() }}</span>
                        </div>
                        @if($item->type === 'post' && $actor && $actor->id === auth()->id())
                            <button wire:click="deletePost({{ $item->id }})"
                                    wire:confirm="{{ __('app.social.delete_post_confirm') }}"
                                    class="text-gray-300 hover:text-red-500 transition-colors">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        @endif
                    </div>

                    {{-- Body by type --}}
                    <div class="mt-1 text-sm text-gray-700">
                        @switch($item->type)
                            @case('post')
                                <p class="whitespace-pre-line">{{ $item->body }}</p>
                                @break
                            @case('achievement')
                                <div class="flex items-center gap-2 bg-amber-50 border border-amber-100 rounded-xl px-3 py-2 text-amber-800">
                                    <i data-lucide="trophy" class="w-4 h-4 shrink-0"></i>
                                    <span>{{ $item->body }}</span>
                                </div>
                                @break
                            @case('new_member')
                                <div class="flex items-center gap-2 bg-purple-50 border border-purple-100 rounded-xl px-3 py-2 text-purple-800">
                                    <i data-lucide="sparkles" class="w-4 h-4 shrink-0"></i>
                                    <span>{{ __('app.social.new_member_joined') }}</span>
                                </div>
                                @break
                            @case('follow')
                                <p class="text-gray-600">
                                    <i data-lucide="user-plus" class="w-4 h-4 inline-block align-text-bottom"></i>
                                    {{ __('app.social.started_following') }}
                                    @if($item->subject)
                                        <a href="{{ url('/u/'.$item->subject->username) }}" class="font-semibold text-gray-900 hover:underline">{{ $item->subject->name }}</a>
                                    @endif
                                </p>
                                @break
                        @endswitch
                    </div>

                    {{-- Like --}}
                    <div class="mt-3 flex items-center gap-1">
                        <button wire:click="toggleLike({{ $item->id }})"
                                class="inline-flex items-center gap-1.5 text-sm font-medium transition-colors {{ $liked ? 'text-pink-600' : 'text-gray-400 hover:text-pink-600' }}">
                            <i data-lucide="heart" class="w-4 h-4 {{ $liked ? 'fill-current' : '' }}"></i>
                            @if($item->likes_count > 0)<span>{{ $item->likes_count }}</span>@endif
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="card p-10 text-center text-gray-400">
            <i data-lucide="rss" class="w-10 h-10 mx-auto mb-3 text-gray-300"></i>
            <p class="text-sm">{{ __('app.social.no_posts') }}</p>
        </div>
    @endforelse

    {{-- Infinite scroll: auto-loads the next batch as the sentinel scrolls into view,
         with a "Show more" button as a click fallback. --}}
    @if($hasMore)
        <div wire:key="feed-load-more"
             x-data="{ busy: false }"
             x-init="
                const io = new IntersectionObserver((entries) => {
                    if (entries[0].isIntersecting && !busy) {
                        busy = true;
                        $wire.loadMore().then(() => { busy = false; });
                    }
                }, { rootMargin: '400px' });
                io.observe($el);
             ">
            <button wire:click="loadMore" wire:loading.attr="disabled" wire:target="loadMore"
                    class="w-full card py-3 text-sm font-semibold text-purple-700 hover:bg-purple-50 transition-colors inline-flex items-center justify-center gap-2">
                <i data-lucide="loader-2" class="w-4 h-4 animate-spin hidden" wire:loading.class.remove="hidden" wire:target="loadMore"></i>
                {{ __('app.social.load_more') }}
            </button>
        </div>
    @endif
</div>
