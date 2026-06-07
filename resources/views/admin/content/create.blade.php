@extends('admin.layouts.admin')
@section('page-title', 'Create Content')

@push('head')
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
@endpush

@section('content')
<div x-data="{ contentType: '{{ old('content_type', 'article') }}', seoOpen: false }" class="max-w-4xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.content.index') }}" class="p-2 text-gray-400 hover:text-purple-600 rounded-lg hover:bg-purple-50 transition">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <h2 class="text-2xl font-bold text-gray-800">Create Content</h2>
    </div>

    <form action="{{ route('admin.content.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- Main Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Title --}}
                <div class="md:col-span-2">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required
                        class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500"
                        x-on:input="$refs.slugField.value = $event.target.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '')">
                    @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Slug --}}
                <div class="md:col-span-2">
                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                    <input type="text" name="slug" id="slug" x-ref="slugField" value="{{ old('slug') }}" required
                        class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500 bg-gray-50 font-mono text-sm">
                    @error('slug') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Content Type --}}
                <div>
                    <label for="content_type" class="block text-sm font-medium text-gray-700 mb-1">Content Type</label>
                    <select name="content_type" id="content_type" x-model="contentType" required
                        class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                        <option value="article">Article</option>
                        <option value="video">Video</option>
                        <option value="document">Document</option>
                        <option value="audio">Audio</option>
                        <option value="sheet_music">Sheet Music</option>
                    </select>
                    @error('content_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Category --}}
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select name="category_id" id="category_id" class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                        <option value="">Select Category</option>
                        @foreach($categories ?? [] as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Status --}}
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="status" class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                        <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published</option>
                    </select>
                    @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Visibility --}}
                <div>
                    <label for="visibility" class="block text-sm font-medium text-gray-700 mb-1">Visibility</label>
                    <select name="visibility" id="visibility" class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                        <option value="public" {{ old('visibility') === 'public' ? 'selected' : '' }}>Public</option>
                        <option value="premium" {{ old('visibility') === 'premium' ? 'selected' : '' }}>Premium Only</option>
                        <option value="private" {{ old('visibility') === 'private' ? 'selected' : '' }}>Private</option>
                    </select>
                    @error('visibility') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Body (TinyMCE) --}}
            <div>
                <label for="body" class="block text-sm font-medium text-gray-700 mb-1">Body</label>
                <textarea name="body" id="body" rows="12"
                    class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">{{ old('body') }}</textarea>
                @error('body') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Excerpt --}}
            <div>
                <label for="excerpt" class="block text-sm font-medium text-gray-700 mb-1">Excerpt</label>
                <textarea name="excerpt" id="excerpt" rows="3"
                    class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500"
                    placeholder="Brief summary of the content...">{{ old('excerpt') }}</textarea>
                @error('excerpt') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Tags --}}
            <div>
                <label for="tags" class="block text-sm font-medium text-gray-700 mb-1">Tags</label>
                <input type="text" name="tags" id="tags" value="{{ old('tags') }}"
                    class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500"
                    placeholder="Comma-separated tags">
                @error('tags') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Featured Image --}}
            <div>
                <label for="featured_image" class="block text-sm font-medium text-gray-700 mb-1">Featured Image</label>
                <input type="file" name="featured_image" id="featured_image" accept="image/*"
                    class="w-full rounded-lg border border-gray-300 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                @error('featured_image') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Conditional: Video URL --}}
            <div x-show="contentType === 'video'" x-transition>
                <label for="video_url" class="block text-sm font-medium text-gray-700 mb-1">Video URL</label>
                <input type="url" name="video_url" id="video_url" value="{{ old('video_url') }}"
                    class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500"
                    placeholder="https://youtube.com/watch?v=...">
                @error('video_url') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Conditional: Audio File --}}
            <div x-show="contentType === 'audio'" x-transition>
                <label for="audio_file" class="block text-sm font-medium text-gray-700 mb-1">Audio File</label>
                <input type="file" name="audio_file" id="audio_file" accept="audio/*"
                    class="w-full rounded-lg border border-gray-300 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                @error('audio_file') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Conditional: Document File --}}
            <div x-show="contentType === 'document' || contentType === 'sheet_music'" x-transition>
                <label for="document_file" class="block text-sm font-medium text-gray-700 mb-1">Document File</label>
                <input type="file" name="document_file" id="document_file" accept=".pdf,.doc,.docx"
                    class="w-full rounded-lg border border-gray-300 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                @error('document_file') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Featured --}}
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_featured" id="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}
                    class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                <label for="is_featured" class="text-sm font-medium text-gray-700">Featured Content</label>
            </div>
        </div>

        {{-- SEO Section (Collapsible) --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <button type="button" @click="seoOpen = !seoOpen" class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition">
                <span class="font-semibold text-gray-800 flex items-center gap-2">
                    <i data-lucide="search" class="w-4 h-4 text-orange-500"></i> SEO Settings
                </span>
                <i data-lucide="chevron-down" class="w-5 h-5 text-gray-400 transition-transform" :class="seoOpen && 'rotate-180'"></i>
            </button>
            <div x-show="seoOpen" x-transition class="px-6 pb-6 space-y-4 border-t border-gray-100">
                <div class="pt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label for="meta_title" class="block text-sm font-medium text-gray-700 mb-1">Meta Title</label>
                        <input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title') }}"
                            class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                    </div>
                    <div class="md:col-span-2">
                        <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-1">Meta Description</label>
                        <textarea name="meta_description" id="meta_description" rows="2"
                            class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">{{ old('meta_description') }}</textarea>
                    </div>
                    <div>
                        <label for="meta_keywords" class="block text-sm font-medium text-gray-700 mb-1">Meta Keywords</label>
                        <input type="text" name="meta_keywords" id="meta_keywords" value="{{ old('meta_keywords') }}"
                            class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500"
                            placeholder="Comma-separated keywords">
                    </div>
                    <div>
                        <label for="canonical_url" class="block text-sm font-medium text-gray-700 mb-1">Canonical URL</label>
                        <input type="url" name="canonical_url" id="canonical_url" value="{{ old('canonical_url') }}"
                            class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                    </div>
                    <div class="md:col-span-2">
                        <label for="og_image" class="block text-sm font-medium text-gray-700 mb-1">OG Image URL</label>
                        <input type="url" name="og_image" id="og_image" value="{{ old('og_image') }}"
                            class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500"
                            placeholder="https://...">
                    </div>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.content.index') }}" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition">Cancel</a>
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                <i data-lucide="save" class="w-4 h-4"></i> Create Content
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    tinymce.init({
        selector: '#body',
        height: 400,
        menubar: true,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount'
        ],
        toolbar: 'undo redo | blocks | bold italic forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
        content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 14px; }'
    });
</script>
@endpush
