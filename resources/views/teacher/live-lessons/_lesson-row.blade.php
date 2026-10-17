{{-- One open live lesson: enter, copy/share the student link, end. $status (optional) = live status from Derslik. --}}
@php $ll = 'live_lessons'; $joinUrl = $lesson->joinUrl(); @endphp
<div class="py-3 border-b border-gray-100 last:border-0"
     x-data="liveLessonLink(@js($joinUrl), @js(crm_trans($ll.'.share_text', ['url' => $joinUrl])))">
    <div class="flex flex-wrap items-center gap-3">
        <div class="w-9 h-9 rounded-full bg-primary-50 text-primary-600 flex items-center justify-center shrink-0">
            <i data-lucide="video" class="w-4 h-4"></i>
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-sm font-semibold text-gray-800 truncate">{{ $lesson->title }}</p>
            <p class="text-xs text-gray-500">
                {{ crm_trans($ll.'.opened', ['time' => $lesson->created_at->diffForHumans()]) }}
                @isset($status)
                    ·
                    @if($status === null)
                        <span class="text-gray-400">{{ crm_trans($ll.'.status_unknown') }}</span>
                    @elseif($status['active'] && $status['participants'] > 0)
                        <span class="inline-flex items-center gap-1 font-semibold text-green-600"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>{{ crm_trans($ll.'.live_now', ['count' => $status['participants']]) }}</span>
                    @else
                        <span class="text-gray-400">{{ crm_trans($ll.'.empty_room') }}</span>
                    @endif
                @endisset
            </p>
        </div>
        <div class="flex items-center gap-1.5 shrink-0">
            <a href="{{ $lesson->roomUrl() }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold rounded-lg transition">
                <i data-lucide="log-in" class="w-3.5 h-3.5"></i> {{ crm_trans($ll.'.enter') }}
            </a>
            <form method="POST" action="{{ crm_route('live-lessons.end', $lesson) }}"
                  onsubmit="return confirm(@js(crm_trans($ll.'.end_confirm')))">
                @csrf
                <button class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-semibold text-red-600 hover:text-white hover:bg-red-600 border border-red-200 hover:border-red-600 rounded-lg transition"
                        title="{{ crm_trans($ll.'.end') }}">
                    <i data-lucide="circle-stop" class="w-3.5 h-3.5"></i> {{ crm_trans($ll.'.end_short') }}
                </button>
            </form>
        </div>
    </div>
    {{-- Student link: what the teacher sends --}}
    <div class="mt-2 sm:ml-12 flex flex-wrap items-center gap-1.5">
        <label class="sr-only" for="ll-link-{{ $lesson->id }}">{{ crm_trans($ll.'.student_link') }}</label>
        <input id="ll-link-{{ $lesson->id }}" type="text" readonly value="{{ $joinUrl }}" @focus="$el.select()"
               class="flex-1 min-w-[180px] px-2.5 py-1.5 text-xs text-gray-600 bg-gray-50 border border-gray-200 rounded-lg focus:ring-primary-500 focus:border-primary-500">
        <button type="button" @click="copy()"
                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 rounded-lg transition">
            <i data-lucide="copy" class="w-3.5 h-3.5"></i>
            <span x-show="!copied">{{ crm_trans($ll.'.copy_link') }}</span>
            <span x-show="copied" x-cloak>{{ crm_trans($ll.'.copied') }}</span>
        </button>
        <a :href="whatsapp()" target="_blank" rel="noopener"
           class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-semibold text-green-700 bg-green-50 hover:bg-green-100 rounded-lg transition"
           title="{{ crm_trans($ll.'.share_whatsapp') }}">
            <i data-lucide="message-circle" class="w-3.5 h-3.5"></i> <span class="hidden sm:inline">WhatsApp</span>
        </a>
        <button type="button" x-show="canShare" x-cloak @click="share()"
                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
            <i data-lucide="share-2" class="w-3.5 h-3.5"></i> {{ crm_trans($ll.'.share') }}
        </button>
    </div>
</div>
