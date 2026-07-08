<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Services\EmailCenter\TemplateRenderer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EmailTemplateController extends Controller
{
    public function index()
    {
        $templates = EmailTemplate::withCount(['campaigns', 'automations'])->latest()->paginate(20);

        return view('admin.email-center.templates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.email-center.templates.form', ['template' => new EmailTemplate]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);
        $validated['slug'] = Str::slug($validated['name']);

        if (EmailTemplate::withTrashed()->where('slug', $validated['slug'])->exists()) {
            $validated['slug'] .= '-'.Str::lower(Str::random(4));
        }

        EmailTemplate::create($validated);

        return redirect()->route('admin.email-templates.index')->with('success', 'Template created.');
    }

    public function edit(EmailTemplate $template)
    {
        return view('admin.email-center.templates.form', compact('template'));
    }

    public function update(Request $request, EmailTemplate $template)
    {
        $template->update($this->validated($request));

        return redirect()->route('admin.email-templates.index')->with('success', 'Template updated.');
    }

    public function destroy(EmailTemplate $template)
    {
        $template->delete();

        return back()->with('success', 'Template deleted.');
    }

    /**
     * Rendered preview with sample variables, shown in a sandboxed iframe.
     */
    public function preview(Request $request, TemplateRenderer $renderer, ?EmailTemplate $template = null)
    {
        $html = $request->isMethod('post')
            ? (string) $request->input('html_body', '')
            : ($template?->html_body ?? '');

        return response($renderer->preview($html))->header('Content-Type', 'text/html; charset=utf-8');
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'subject' => 'required|string|max:255',
            'preheader' => 'nullable|string|max:255',
            'html_body' => 'required|string',
            'category' => 'required|in:'.implode(',', EmailTemplate::CATEGORIES),
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
