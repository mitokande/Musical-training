@extends('teacher.layouts.crm')

@section('title', crm_trans('content.title'))

@section('content')
@php $c = 'content'; @endphp

@if (session('status') && in_array(session('status'), ['media-saved', 'media-deleted', 'media-shared', 'video-saved', 'video-deleted', 'article-shared']))
    <div class="mb-6 px-4 py-3 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3">
        <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
        <p class="text-sm text-green-700">{{ crm_trans('profile.saved') }}</p>
    </div>
@endif
@if ($errors->any())
    <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 rounded-xl">
        @foreach ($errors->all() as $error)<p class="text-sm text-red-600">{{ $error }}</p>@endforeach
    </div>
@endif

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">{{ crm_trans($c.'.title') }}</h1>
    <p class="text-gray-500 text-[15px] mt-1">{{ crm_trans($c.'.subtitle') }}</p>
</div>

<div x-data="{ tab: '{{ session('status') === 'video-saved' || session('status') === 'video-deleted' ? 'videos' : (session('status') === 'article-shared' ? 'articles' : 'documents') }}',
    shareOpen: false, shareAction: '', shareTitle: '', shareMeta: '', shareIds: [], shareIsArticle: false }">

    {{-- Tab bar: My Documents · My Articles · My Videos --}}
    <div class="inline-flex p-1 mb-6 bg-gray-100 rounded-2xl">
        <button type="button" @click="tab='documents'" :class="tab==='documents' ? 'bg-white shadow text-primary-700' : 'text-gray-500 hover:text-gray-700'" class="inline-flex items-center gap-2 px-4 sm:px-5 py-2.5 rounded-xl text-[15px] font-semibold transition">
            <i data-lucide="folder" class="w-4 h-4"></i> {{ crm_trans($c.'.tab_documents') }}
        </button>
        <button type="button" @click="tab='articles'" :class="tab==='articles' ? 'bg-white shadow text-primary-700' : 'text-gray-500 hover:text-gray-700'" class="inline-flex items-center gap-2 px-4 sm:px-5 py-2.5 rounded-xl text-[15px] font-semibold transition">
            <i data-lucide="newspaper" class="w-4 h-4"></i> {{ crm_trans($c.'.tab_articles') }}
        </button>
        <button type="button" @click="tab='videos'" :class="tab==='videos' ? 'bg-white shadow text-primary-700' : 'text-gray-500 hover:text-gray-700'" class="inline-flex items-center gap-2 px-4 sm:px-5 py-2.5 rounded-xl text-[15px] font-semibold transition">
            <i data-lucide="video" class="w-4 h-4"></i> {{ crm_trans($c.'.tab_videos') }}
        </button>
    </div>

    {{-- ============================ MY DOCUMENTS ============================ --}}
    <div x-show="tab==='documents'" x-cloak class="grid lg:grid-cols-3 gap-6">
        {{-- Upload --}}
        <div class="lg:col-span-1">
            <div class="card p-5 sm:p-6">
                <h2 class="font-bold text-gray-900 mb-1">{{ crm_trans($c.'.upload_document') }}</h2>
                <p class="text-sm text-gray-500 mb-4">{{ crm_trans($c.'.documents_hint') }}</p>
                @php
                    $crmQuota = app(\App\Services\Teacher\CrmQuotaService::class);
                    $documentLimit = $crmQuota->limit(auth()->user(), 'max_documents');
                @endphp
                @if($documentLimit !== -1)
                    <span class="inline-block mb-3 px-3 py-1 rounded-full bg-purple-50 border border-purple-200 text-purple-700 text-xs font-bold">
                        {{ __('teacher.limits.documents_counter', ['used' => $crmQuota->documentCount(auth()->user()), 'limit' => $documentLimit]) }}
                    </span>
                @endif
                @if($errors->has('file'))
                    <div class="mb-3 px-3 py-2 bg-orange-50 border border-orange-200 rounded-xl text-xs text-orange-700">{{ $errors->first('file') }}</div>
                @endif
                @if($hasProfile)
                    <form method="POST" action="{{ crm_route('media.store') }}" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <input type="hidden" name="kind" value="document">
                        <input type="text" name="title" maxlength="255" placeholder="{{ crm_trans($c.'.document_title') }}" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-2.5 text-[15px]">
                        <input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png,.webp" class="w-full text-sm text-gray-600 file:mr-3 file:px-4 file:py-2 file:rounded-xl file:border-0 file:bg-primary-50 file:text-primary-700 file:font-semibold file:text-sm">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">{{ crm_trans($c.'.visibility') }}</label>
                            <select name="visibility" class="w-full rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-2.5 text-[15px]">
                                <option value="private">{{ crm_trans($c.'.visibility_private') }}</option>
                                <option value="students">{{ crm_trans($c.'.visibility_students') }}</option>
                                <option value="shared">{{ crm_trans($c.'.visibility_shared') }}</option>
                            </select>
                        </div>
                        <p class="text-xs text-gray-400">{{ crm_trans($c.'.max_size_hint') }}</p>
                        <button class="w-full py-3 bg-primary-600 hover:bg-primary-700 text-white text-[15px] font-semibold rounded-xl transition">
                            {{ crm_trans($c.'.upload_document') }}
                        </button>
                    </form>
                @else
                    <p class="text-sm text-gray-400">{{ crm_trans($c.'.needs_profile') }}</p>
                @endif
            </div>
        </div>

        {{-- List --}}
        <div class="lg:col-span-2">
            <div class="card overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="font-bold text-gray-900">{{ crm_trans($c.'.tab_documents') }} ({{ $documents->count() }})</h2>
                </div>
                @if($documents->isEmpty())
                    <div class="p-8 text-center text-[15px] text-gray-400">{{ crm_trans($c.'.no_documents') }}</div>
                @else
                    <ul class="divide-y divide-gray-100">
                        @foreach($documents as $doc)
                            @php $ext = strtoupper(pathinfo($doc->original_name, PATHINFO_EXTENSION)); @endphp
                            <li class="flex items-center gap-3 px-5 py-4">
                                <div class="w-10 h-10 rounded-xl bg-primary-50 flex items-center justify-center shrink-0">
                                    <i data-lucide="file-text" class="w-5 h-5 text-primary-600"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[15px] font-semibold text-gray-900 truncate">{{ $doc->title ?: $doc->original_name }}</p>
                                    <p class="text-xs text-gray-400">
                                        {{ $ext }} · {{ number_format($doc->size / 1024) }} KB · {{ crm_trans($c.'.visibility_'.$doc->visibility) }}
                                        @if($doc->sharedStudents->count()) · {{ trans_choice($c.'.shared_count', $doc->sharedStudents->count(), ['count' => $doc->sharedStudents->count()]) }}@endif
                                    </p>
                                </div>
                                <button type="button"
                                    @click="shareOpen=true; shareIsArticle=false; shareAction='{{ crm_route('media.share', $doc) }}'; shareTitle=@js($doc->title ?: $doc->original_name); shareMeta=@js($ext.' · '.number_format($doc->size / 1024).' KB · '.crm_trans($c.'.visibility_'.$doc->visibility)); shareIds=@js($doc->sharedStudents->pluck('id')->values())"
                                    class="px-3 py-2 text-sm font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 rounded-lg transition">
                                    {{ crm_trans($c.'.details') }}
                                </button>
                                <a href="{{ crm_route('media.download', $doc) }}" class="p-2 text-gray-400 hover:text-primary-600" title="{{ crm_trans($c.'.download') }}"><i data-lucide="download" class="w-4 h-4"></i></a>
                                <form method="POST" action="{{ crm_route('media.destroy', $doc) }}" onsubmit="return confirm(@js(crm_trans($c.'.delete_document_confirm')))">
                                    @csrf @method('DELETE')
                                    <button class="p-2 text-gray-400 hover:text-red-600"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    {{-- ============================ MY ARTICLES ============================ --}}
    <div x-show="tab==='articles'" x-cloak>
        <div class="flex justify-end mb-4">
            <a href="{{ route('articles.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-xl transition">
                <i data-lucide="plus" class="w-4 h-4"></i> {{ crm_trans($c.'.new_article') }}
            </a>
        </div>
        <div class="card overflow-hidden">
            @if($articles->isEmpty())
                <div class="p-8 text-center text-[15px] text-gray-400">{{ crm_trans($c.'.no_articles') }}</div>
            @else
                <ul class="divide-y divide-gray-100">
                    @foreach($articles as $article)
                        <li class="flex items-center gap-4 px-5 py-4">
                            <div class="w-10 h-10 rounded-xl bg-primary-50 flex items-center justify-center shrink-0">
                                <i data-lucide="newspaper" class="w-5 h-5 text-primary-600"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[15px] font-semibold text-gray-900 truncate">{{ $article->title }}</p>
                                <p class="text-sm text-gray-400">
                                    {{ $article->created_at->format('M j, Y') }}
                                    · <span class="font-medium {{ $article->status === 'published' ? 'text-green-600' : 'text-gray-500' }}">{{ ucfirst($article->status) }}</span>
                                </p>
                            </div>
                            @if($article->status === 'published')
                                <button type="button"
                                    @click="shareOpen=true; shareIsArticle=true; shareAction='{{ crm_route('content.articles.share', $article) }}'; shareTitle=@js($article->title); shareMeta=''; shareIds=[]"
                                    class="px-3 py-2 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition inline-flex items-center gap-1.5">
                                    <i data-lucide="share-2" class="w-4 h-4"></i> {{ crm_trans($c.'.share') }}
                                </button>
                            @endif
                            <a href="{{ route('articles.edit', $article) }}" class="px-3 py-2 text-sm font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 rounded-lg transition">
                                {{ crm_trans($c.'.edit') }}
                            </a>
                            <form method="POST" action="{{ route('articles.destroy', $article) }}" onsubmit="return confirm(@js(crm_trans($c.'.delete_article_confirm')))">
                                @csrf @method('DELETE')
                                <button class="p-2 text-gray-400 hover:text-red-600"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    {{-- ============================ MY VIDEOS ============================ --}}
    <div x-show="tab==='videos'" x-cloak>
        <div class="card p-5 sm:p-6">
            <h2 class="font-bold text-gray-900 mb-4">{{ crm_trans($c.'.tab_videos') }} ({{ $videos->count() }})</h2>
            @if($hasProfile)
                <form method="POST" action="{{ crm_route('videos.store') }}" class="flex flex-col sm:flex-row gap-2 mb-4">
                    @csrf
                    <input type="text" name="title" required maxlength="255" placeholder="{{ crm_trans($c.'.video_title') }}" class="flex-1 rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-2.5 text-[15px]">
                    <input type="url" name="url" required placeholder="https://www.youtube.com/watch?v=…" class="flex-1 rounded-xl border-2 border-gray-200 bg-gray-50/50 px-4 py-2.5 text-[15px]">
                    <button class="px-5 py-2.5 bg-gray-800 hover:bg-gray-900 text-white text-[15px] font-semibold rounded-xl transition">{{ crm_trans($c.'.add') }}</button>
                </form>
            @endif
            @if($videos->isEmpty())
                <p class="text-[15px] text-gray-400">{{ crm_trans($c.'.no_videos') }}</p>
            @else
                <div class="grid sm:grid-cols-2 gap-4">
                    @foreach($videos as $video)
                        <div class="rounded-2xl overflow-hidden border border-gray-100">
                            <div class="aspect-video">
                                <iframe src="{{ $video->embedUrl() }}" title="{{ $video->title }}" class="w-full h-full" frameborder="0" loading="lazy" allowfullscreen></iframe>
                            </div>
                            <div class="p-3 flex items-center gap-2">
                                <p class="flex-1 text-sm font-semibold text-gray-800 truncate">{{ $video->title }}</p>
                                <form method="POST" action="{{ crm_route('videos.destroy', $video) }}">
                                    @csrf @method('DELETE')
                                    <button class="p-1.5 text-gray-400 hover:text-red-600"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ============================ SHARE / DETAILS MODAL ============================ --}}
    <div x-show="shareOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
        <div class="absolute inset-0 bg-black/40" @click="shareOpen=false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md max-h-[85vh] overflow-y-auto">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-bold text-gray-900" x-text="shareIsArticle ? '{{ crm_trans($c.'.share_article') }}' : '{{ crm_trans($c.'.details') }}'"></h3>
                <button type="button" @click="shareOpen=false" class="p-1.5 text-gray-400 hover:text-gray-700"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <div class="p-5 space-y-4">
                <div>
                    <p class="text-[15px] font-semibold text-gray-900" x-text="shareTitle"></p>
                    <p class="text-xs text-gray-400 mt-0.5" x-text="shareMeta" x-show="shareMeta"></p>
                </div>

                <form :action="shareAction" method="POST">
                    @csrf
                    <p class="text-sm font-semibold text-gray-700 mb-2">{{ crm_trans($c.'.share_with_students') }}</p>
                    @if($students->isEmpty())
                        <p class="text-sm text-gray-400 mb-4">{{ crm_trans($c.'.no_students') }}</p>
                    @else
                        <div class="max-h-56 overflow-y-auto space-y-1 mb-4 -mx-1 px-1">
                            @foreach($students as $student)
                                <label class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-gray-50 cursor-pointer">
                                    <input type="checkbox" name="student_ids[]" value="{{ $student->id }}"
                                        :checked="shareIds.includes({{ $student->id }})"
                                        class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                    <span class="text-[15px] text-gray-800">{{ trim($student->name.' '.$student->surname) }}</span>
                                </label>
                            @endforeach
                        </div>
                        <button class="w-full py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-[15px] font-semibold rounded-xl transition">
                            {{ crm_trans($c.'.share') }}
                        </button>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
