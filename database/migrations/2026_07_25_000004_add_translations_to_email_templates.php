<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-locale variants of the system email templates. The base columns
 * (subject/preheader/html_body) stay English; `translations` holds a map of
 * { locale => { subject, preheader, html_body } } for the other 6 languages.
 * Empty/null = English-only behaviour (exactly as before), so this is a safe
 * additive change. Managed by the EmailTemplateLibrary + email:sync-templates.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            $table->json('translations')->nullable()->after('variables');
        });
    }

    public function down(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            $table->dropColumn('translations');
        });
    }
};
