{{-- Shared composer. Expects $action. --}}
@if ($errors->any())
    <div class="mt-4 px-4 py-3 bg-red-50 border border-red-200 rounded-xl">
        @foreach ($errors->all() as $error)<p class="text-sm text-red-600">{{ $error }}</p>@endforeach
    </div>
@endif
<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="card p-4 mt-4 space-y-3">
    @csrf
    <textarea name="body" rows="3" required maxlength="5000"
              placeholder="{{ __('teacher.messaging.type_message') }}"
              class="w-full rounded-lg border-gray-300 text-sm">{{ old('body') }}</textarea>
    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="flex-1">
            <label class="block text-[11px] font-semibold text-gray-400 mb-1">{{ __('teacher.messaging.attachments') }}</label>
            <input type="file" name="attachments[]" multiple accept=".pdf,.jpg,.jpeg,.png,.webp" class="text-xs text-gray-500">
        </div>
        <button class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg transition">
            <i data-lucide="send" class="w-4 h-4"></i> {{ __('teacher.messaging.send') }}
        </button>
    </div>
</form>
