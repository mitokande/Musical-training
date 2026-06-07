<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Yeni Icerik - {{ config('app.name', 'Harmoniva') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@0.460.0"></script>
    <script defer src="https://unpkg.com/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'] },
                    colors: {
                        primary: { 50:'#faf5ff',100:'#f3e8ff',200:'#e9d5ff',300:'#d8b4fe',400:'#c084fc',500:'#a855f7',600:'#9333ea',700:'#7c3aed',800:'#6b21a8',900:'#581c87' },
                        accent: { 400:'#fb923c',500:'#f97316',600:'#ea580c' }
                    }
                }
            }
        }
    </script>
</head>
<body class="font-sans bg-gray-50 min-h-screen">

@include('partials.navbar', ['active' => 'articles'])

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Yeni Icerik Olustur</h1>
        <a href="{{ route('articles.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-primary-600 transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Geri Don
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
            <h2 class="text-lg font-bold text-gray-900 mb-6">Temel Bilgiler</h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Baslik *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Icerik Turu *</label>
                        <select name="content_type" x-model="contentType" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                            <option value="article">Makale</option>
                            <option value="video">Video</option>
                            <option value="document">Dokuman / Ders Notu</option>
                            <option value="audio">Ses Kaydi</option>
                            <option value="sheet_music">Nota</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Gorunurluk *</label>
                        <select name="visibility" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                            <option value="public" {{ old('visibility') === 'public' ? 'selected' : '' }}>Herkese Acik</option>
                            <option value="students_only" {{ old('visibility') === 'students_only' ? 'selected' : '' }}>Sadece Ogrenciler</option>
                            <option value="school_only" {{ old('visibility') === 'school_only' ? 'selected' : '' }}>Sadece Okul</option>
                            <option value="private" {{ old('visibility') === 'private' ? 'selected' : '' }}>Ozel</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ozet</label>
                    <textarea name="excerpt" rows="2" maxlength="500" placeholder="Kisa bir ozet..." class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">{{ old('excerpt') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Icerik</label>
                    <textarea name="body" rows="10" placeholder="Iceriginizi buraya yazin..." class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">{{ old('body') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Medya --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <h2 class="text-lg font-bold text-gray-900 mb-6">Medya</h2>

            <div x-show="contentType === 'video'" x-cloak class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Video URL (YouTube / Vimeo)</label>
                <input type="url" name="video_url" value="{{ old('video_url') }}" placeholder="https://www.youtube.com/watch?v=..." class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
            </div>

            <div x-show="contentType === 'audio'" x-cloak class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Ses Dosyasi (MP3, WAV, OGG, M4A - maks 20MB)</label>
                <input type="file" name="audio_file" accept=".mp3,.wav,.ogg,.m4a" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
            </div>

            <div x-show="contentType === 'document' || contentType === 'sheet_music'" x-cloak class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Dokuman (PDF, DOC, DOCX - maks 10MB)</label>
                <input type="file" name="document_file" accept=".pdf,.doc,.docx" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kapak Gorseli (JPG, PNG, WebP - maks 2MB)</label>
                <input type="file" name="featured_image" accept="image/jpeg,image/png,image/webp" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
            </div>
        </div>

        {{-- Meta --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <h2 class="text-lg font-bold text-gray-900 mb-6">Ek Bilgiler</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                    <input type="text" name="category" value="{{ old('category') }}" placeholder="Ornegin: Piyano, Teori..." class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Etiketler (virgul ile ayirin)</label>
                    <input type="text" name="tags" value="{{ old('tags') }}" placeholder="piyano, baslangic, teori" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <button type="submit" name="action" value="draft" class="inline-flex items-center gap-2 px-6 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm font-medium">
                <i data-lucide="save" class="w-4 h-4"></i>
                Taslak Kaydet
            </button>
            <button type="submit" name="action" value="publish" class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition text-sm font-medium">
                <i data-lucide="send" class="w-4 h-4"></i>
                Onaya Gonder
            </button>
        </div>
    </form>
</div>

<script>lucide.createIcons();</script>
<style>[x-cloak] { display: none !important; }</style>
</body>
</html>
