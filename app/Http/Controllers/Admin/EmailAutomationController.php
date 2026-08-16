<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailAutomation;
use App\Models\EmailMessage;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmailAutomationController extends Controller
{
    public function index()
    {
        $automations = EmailAutomation::with('template')
            ->withCount(['messages as sent_30d' => fn ($q) => $q->where('created_at', '>=', now()->subDays(30))])
            ->orderBy('id')
            ->get();

        $templates = EmailTemplate::where('is_active', true)->orderBy('name')->get();

        return view('admin.email-center.automations.index', [
            'automations' => $automations,
            'templates' => $templates,
            'audienceCounts' => $this->audienceCounts(),
        ]);
    }

    /**
     * Sends of the last 30 days per automation, split by the audience the mail
     * actually went out as. Each automation fans out to a student, teacher and
     * school variant, so a single total hides whether the teacher and school
     * copy is really reaching anyone.
     *
     * The audience is read back off the template slug that was sent, which is
     * what the recipient actually received — more truthful than re-deriving it
     * from the user's role today, since roles change after the fact.
     *
     * @return array<int, array{student: int, teacher: int, school: int}>
     */
    protected function audienceCounts(): array
    {
        $rows = EmailMessage::query()
            ->whereNotNull('automation_id')
            ->where('email_messages.created_at', '>=', now()->subDays(30))
            ->whereNotIn('status', ['queued', 'suppressed', 'failed'])
            ->join('email_templates', 'email_templates.id', '=', 'email_messages.template_id')
            ->groupBy('automation_id', 'email_templates.slug')
            ->get(['automation_id', 'email_templates.slug', DB::raw('COUNT(*) as total')]);

        $counts = [];

        foreach ($rows as $row) {
            $audience = str_ends_with($row->slug, '-teacher') ? 'teacher'
                : (str_ends_with($row->slug, '-school') ? 'school' : 'student');

            $counts[$row->automation_id][$audience] = ($counts[$row->automation_id][$audience] ?? 0) + (int) $row->total;
        }

        return $counts;
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
