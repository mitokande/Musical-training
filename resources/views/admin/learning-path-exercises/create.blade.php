@extends('admin.layouts.admin')
@section('page-title', 'Create Learning Path Exercise')

@section('content')
<div class="max-w-5xl space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.learning-path-exercises.index') }}" class="text-gray-400 hover:text-gray-600">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <h2 class="text-2xl font-bold text-gray-800">Yeni Egzersiz Oluştur</h2>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <ul class="text-sm text-red-700 space-y-1">
                @foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.learning-path-exercises.store') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-5">
        @csrf
        @include('admin.learning-path-exercises._form')
        <div class="flex justify-end gap-3 pt-4 border-t">
            <a href="{{ route('admin.learning-path-exercises.index') }}"
               class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 rounded-lg border border-gray-200 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-6 py-2 text-sm bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium">
                Create Exercise
            </button>
        </div>
    </form>
</div>
@endsection
