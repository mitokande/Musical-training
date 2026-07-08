@extends('teacher.layouts.crm')

@section('title', __('app.articles.create_heading'))

@section('content')

<div class="max-w-4xl">

    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('app.articles.create_heading') }}</h1>
        <a href="{{ route('articles.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-primary-600 transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            {{ __('app.common.back') }}
        </a>
    </div>

    @if($errors->any())
        <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 rounded-xl">
            <ul class="list-disc list-inside text-sm text-red-600">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('articles.store') }}" enctype="multipart/form-data" x-data="{ contentType: '{{ old('content_type', 'article') }}' }">
        @csrf

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <h2 class="text-lg font-bold text-gray-900 mb-6">{{ __('app.articles.basic_info') }}</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.articles.title_label') }}</label>
                    <input type="text" name="title" value="{{ old('title') }}" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.articles.content_type_label') }}</label>
                        <select name="content_type" x-model="contentType" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                            <option value="article">{{ __('app.articles.option_article') }}</option>
                            <option value="video">{{ __('app.articles.option_video') }}</option>
                            <option value="document">{{ __('app.articles.option_document') }}</option>
                            <option value="audio">{{ __('app.articles.option_audio') }}</option>
                            <option value="sheet_music">{{ __('app.articles.option_sheet_music') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.articles.visibility_label') }}</label>
                        <select name="visibility" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                            <option value="public" {{ old('visibility') === 'public' ? 'selected' : '' }}>{{ __('app.articles.visibility_public') }}</option>
                            <option value="students_only" {{ old('visibility') === 'students_only' ? 'selected' : '' }}>{{ __('app.articles.visibility_students_only') }}</option>
                            <option value="school_only" {{ old('visibility') === 'school_only' ? 'selected' : '' }}>{{ __('app.articles.visibility_school_only') }}</option>
                            <option value="private" {{ old('visibility') === 'private' ? 'selected' : '' }}>{{ __('app.articles.visibility_private') }}</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.articles.excerpt_label') }}</label>
                    <textarea name="excerpt" rows="2" maxlength="500" placeholder="{{ __('app.articles.excerpt_placeholder') }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">{{ old('excerpt') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.articles.content_label') }}</label>
                    <textarea name="body" rows="10" placeholder="{{ __('app.articles.content_placeholder') }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">{{ old('body') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Medya --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <h2 class="text-lg font-bold text-gray-900 mb-6">{{ __('app.articles.media') }}</h2>

            <div x-show="contentType === 'video'" x-cloak class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.articles.video_url_label') }}</label>
                <input type="url" name="video_url" value="{{ old('video_url') }}" placeholder="https://www.youtube.com/watch?v=..." class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
            </div>

            <div x-show="contentType === 'audio'" x-cloak class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.articles.audio_file_label') }}</label>
                <input type="file" name="audio_file" accept=".mp3,.wav,.ogg,.m4a" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
            </div>

            <div x-show="contentType === 'document' || contentType === 'sheet_music'" x-cloak class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.articles.document_file_label') }}</label>
                <input type="file" name="document_file" accept=".pdf,.doc,.docx" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.articles.featured_image_label') }}</label>
                <input type="file" name="featured_image" accept="image/jpeg,image/png,image/webp" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
            </div>
        </div>

        {{-- Meta --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <h2 class="text-lg font-bold text-gray-900 mb-6">{{ __('app.articles.additional_info') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.articles.category_label') }}</label>
                    <input type="text" name="category" value="{{ old('category') }}" placeholder="{{ __('app.articles.category_placeholder') }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('app.articles.tags_label') }}</label>
                    <input type="text" name="tags" value="{{ old('tags') }}" placeholder="{{ __('app.articles.tags_placeholder') }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <button type="submit" name="action" value="draft" class="inline-flex items-center gap-2 px-6 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm font-medium">
                <i data-lucide="save" class="w-4 h-4"></i>
                {{ __('app.articles.save_draft') }}
            </button>
            <button type="submit" name="action" value="publish" class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition text-sm font-medium">
                <i data-lucide="send" class="w-4 h-4"></i>
                {{ __('app.articles.send_for_approval') }}
            </button>
        </div>
    </form>
</div>

@endsection
