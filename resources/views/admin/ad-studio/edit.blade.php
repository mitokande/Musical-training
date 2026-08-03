@extends('admin.layouts.admin')

@section('page-title', 'Ad Studio — '.$creative->name)

@push('head')
    @livewireStyles
@endpush

@section('content')
    @livewire('admin.ad-creative-editor', ['creative' => $creative])
@endsection

@push('scripts')
    @livewireScripts
    {{-- Livewire replaces DOM on every round trip, so the icon set has to be
         re-created afterwards or every icon vanishes on the first interaction. --}}
    <script>
        document.addEventListener('livewire:navigated', () => lucide.createIcons());
        document.addEventListener('livewire:update', () => lucide.createIcons());
        Livewire.hook('morph.updated', () => lucide.createIcons());
    </script>
@endpush
