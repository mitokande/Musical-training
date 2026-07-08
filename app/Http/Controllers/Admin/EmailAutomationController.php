<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailAutomation;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailAutomationController extends Controller
{
    public function index()
    {
        $automations = EmailAutomation::with('template')
            ->withCount(['messages as sent_30d' => fn ($q) => $q->where('created_at', '>=', now()->subDays(30))])
            ->orderBy('id')
            ->get();

        $templates = EmailTemplate::where('is_active', true)->orderBy('name')->get();

        return view('admin.email-center.automations.index', compact('automations', 'templates'));
    }

    public function update(Request $request, EmailAutomation $automation)
    {
        $validated = $request->validate([
            'enabled' => 'boolean',
            'template_id' => 'nullable|exists:email_templates,id',
            'config' => 'nullable|array',
            'config.*' => 'nullable|integer|min:0|max:365',
        ]);

        $automation->update([
            'enabled' => $request->boolean('enabled'),
            'template_id' => $validated['template_id'] ?? $automation->template_id,
            'config' => array_merge($automation->config ?? [], array_map('intval', array_filter($validated['config'] ?? [], fn ($v) => $v !== null))),
        ]);

        return back()->with('success', "Automation \"{$automation->name}\" updated.");
    }
}
