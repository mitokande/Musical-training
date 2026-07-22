<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_profiles', function (Blueprint $table) {
            $table->string('entity_type', 16)->default('teacher')->index()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('teacher_profiles', function (Blueprint $table) {
            $table->dropIndex(['entity_type']);
            $table->dropColumn('entity_type');
        });
    }
};
