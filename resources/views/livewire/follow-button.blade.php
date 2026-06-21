<div>
    @auth
        @if(auth()->id() !== $user->id)
            <button wire:click="toggleFollow"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold transition-all
                           {{ $isFollowing
                              ? 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                              : 'text-white btn-primary' }}">
                <i data-lucide="{{ $isFollowing ? 'user-check' : 'user-plus' }}" class="w-4 h-4"></i>
                {{ $isFollowing ? __('app.social.following') : __('app.social.follow') }}
            </button>
        @endif
    @endauth
</div>
