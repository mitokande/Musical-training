<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds a teacher/school-only topic to email preferences: non-critical
 * teaching-activity mail (appointment summaries, student activity digests).
 * Shown on the preferences page only to teacher/school audiences. Critical
 * booking/lesson notices remain transactional and are never gated.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_preferences', function (Blueprint $table) {
            $table->boolean('topic_teaching')->default(true)->after('topic_product');
        });
    }

    public function down(): void
    {
        Schema::table('email_preferences', function (Blueprint $table) {
            $table->dropColumn('topic_teaching');
        });
    }
};
