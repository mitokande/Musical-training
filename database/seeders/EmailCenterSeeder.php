<?php

namespace Database\Seeders;

use App\Models\EmailAutomation;
use App\Models\EmailTemplate;
use App\Services\EmailCenter\EmailTemplateLibrary;
use Illuminate\Database\Seeder;

/**
 * Seeds the system email templates and lifecycle automations from the shared
 * EmailTemplateLibrary. Uses firstOrCreate so it is safe to run on an existing
 * install (never clobbers admin edits). To push a template *redesign* to an
 * already-seeded database, run `php artisan email:sync-templates` instead.
 */
class EmailCenterSeeder extends Seeder
{
    public function run(EmailTemplateLibrary $library): void
    {
        foreach ($library->templateRecords() as $data) {
            EmailTemplate::withTrashed()->firstOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'subject' => $data['subject'],
                    'preheader' => $data['preheader'],
                    'html_body' => $data['html_body'],
                    'category' => $data['category'],
                    'is_active' => true,
                    'variables' => $data['variables'],
                    'translations' => $data['translations'],
                ]
            );
        }

        foreach ($library->automations() as $data) {
            EmailAutomation::firstOrCreate(
                ['key' => $data['key']],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'template_id' => EmailTemplate::where('slug', $data['template'])->value('id'),
                    'enabled' => $data['enabled'],
                    'config' => $data['config'],
                ]
            );
        }
    }
}
