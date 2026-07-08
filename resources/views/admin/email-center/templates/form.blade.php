@extends('admin.layouts.admin')
@section('page-title', $template->exists ? 'Edit Template' : 'New Template')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">{{ $template->exists ? 'Edit Template: '.$template->name : 'New Template' }}</h2>
        <a href="{{ route('admin.email-templates.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back to templates</a>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ $template->exists ? route('admin.email-templates.update', $template) : route('admin.email-templates.store') }}">
        @csrf
        @if ($template->exists) @method('PUT') @endif

        <div class="grid lg:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input type="text" name="name" value="{{ old('name', $template->name) }}" required class="w-full rounded-lg border-gray-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select name="category" class="w-full rounded-lg border-gray-300 text-sm">
                                <option value="marketing" @selected(old('category', $template->category) === 'marketing')>Marketing (respects unsubscribe)</option>
                                <option value="transactional" @selected(old('category', $template->category) === 'transactional')>Transactional</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                        <input type="text" name="subject" value="{{ old('subject', $template->subject) }}" required class="w-full rounded-lg border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Preheader</label>
                        <input type="text" name="preheader" value="{{ old('preheader', $template->preheader) }}" class="w-full rounded-lg border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">HTML Body</label>
                        <textarea name="html_body" id="html_body" rows="22" required class="w-full rounded-lg border-gray-300 text-xs font-mono">{{ old('html_body', $template->html_body) }}</textarea>
                        <p class="text-xs text-gray-400 mt-1">Variables: @{{user_first_name}}, @{{user_name}}, @{{user_email}}, @{{app_name}}, @{{app_url}}, @{{unsubscribe_url}}, @{{current_year}} — weekly digest also gets @{{weekly_sessions}}, @{{weekly_accuracy}}, @{{weekly_minutes}}</p>
                    </div>
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-indigo-600" @checked(old('is_active', $template->exists ? $template->is_active : true))>
                        Active (available for campaigns and automations)
                    </label>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="refreshPreview()" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Refresh Preview</button>
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">
                        <i data-lucide="save" class="w-4 h-4"></i> {{ $template->exists ? 'Update Template' : 'Create Template' }}
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-3 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-800 text-sm">Live Preview</h3>
                    <span class="text-xs text-gray-400">sample data</span>
                </div>
                <iframe id="preview-frame" sandbox="" class="w-full" style="height: 720px; border: 0;"></iframe>
            </div>
        </div>
    </form>

    @if ($template->exists)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-lg">
            <h3 class="font-semibold text-gray-800 text-sm mb-3">Send a test email</h3>
            <form method="POST" action="{{ route('admin.email-center.test-send') }}" class="flex gap-2">
                @csrf
                <input type="hidden" name="template_id" value="{{ $template->id }}">
                <input type="email" name="recipient" required placeholder="you@example.com" value="{{ auth()->user()->email }}" class="flex-1 rounded-lg border-gray-300 text-sm">
                <button class="px-3 py-2 text-sm bg-gray-800 text-white rounded-lg hover:bg-gray-700">Send Test</button>
            </form>
        </div>
    @endif
</div>

<script>
    function refreshPreview() {
        const data = new FormData();
        data.append('_token', '{{ csrf_token() }}');
        data.append('html_body', document.getElementById('html_body').value);

        fetch('{{ route('admin.email-templates.preview.live') }}', { method: 'POST', body: data })
            .then(r => r.text())
            .then(html => document.getElementById('preview-frame').srcdoc = html);
    }
    refreshPreview();
</script>
@endsection
