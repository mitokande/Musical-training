<?php

namespace App\Console\Commands;

use App\Models\EmailAutomation;
use App\Models\EmailTemplate;
use App\Services\EmailCenter\EmailTemplateLibrary;
use Illuminate\Console\Command;

/**
 * Pushes the current EmailTemplateLibrary design to the database. Updates the
 * html_body / subject / preheader / category / variables of each system
 * template in place, while preserving the admin-controlled `is_active` flag
 * (and the `name`, so a rename in the admin UI survives). Missing templates
 * are created; automations still lacking a template_id are wired up by slug.
 *
 * Run manually after deploying a template redesign — this is the only path
 * that overwrites existing rows (the seeder never does).
 */
class SyncEmailTemplates extends Command
{
    protected $signature = 'email:sync-templates {--dry-run : Show what would change without writing}';

    protected $description = 'Sync the system email templates (design + copy) into the database';

    public function handle(EmailTemplateLibrary $library): int
    {
        $dry = (bool) $this->option('dry-run');
        $created = 0;
        $updated = 0;

        foreach ($library->templateRecords() as $data) {
            $template = EmailTemplate::withTrashed()->firstWhere('slug', $data['slug']);

            if (! $template) {
                if (! $dry) {
                    EmailTemplate::create([
                        'name' => $data['name'],
                        'slug' => $data['slug'],
                        'subject' => $data['subject'],
                        'preheader' => $data['preheader'],
                        'html_body' => $data['html_body'],
                        'category' => $data['category'],
                        'is_active' => true,
                        'variables' => $data['variables'],
                        'translations' => $data['translations'],
                    ]);
                }
                $created++;
                $this->line("  <fg=green>create</> {$data['slug']}");

                continue;
            }

            // Preserve is_active and name (admin-controlled); refresh the design/copy.
            if (! $dry) {
                $template->fill([
                    'subject' => $data['subject'],
                    'preheader' => $data['preheader'],
                    'html_body' => $data['html_body'],
                    'category' => $data['category'],
                    'variables' => $data['variables'],
                    'translations' => $data['translations'],
                ])->save();
            }
            $updated++;
            $this->line("  <fg=yellow>update</> {$data['slug']}");
        }

        // Wire up automations that still have no template (e.g. a newly added key).
        foreach ($library->automations() as $data) {
            $automation = EmailAutomation::where('key', $data['key'])->first();

            if ($automation && ! $automation->template_id) {
                $templateId = EmailTemplate::where('slug', $data['template'])->value('id');
                if ($templateId && ! $dry) {
                    $automation->update(['template_id' => $templateId]);
                    $this->line("  <fg=cyan>link</>   {$data['key']} → {$data['template']}");
                }
            }
        }

        $this->info(($dry ? '[dry-run] ' : '')."Templates synced: {$created} created, {$updated} updated.");

        return self::SUCCESS;
    }
}
