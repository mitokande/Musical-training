<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('melodic_interval_practices', function (Blueprint $table) {
            $table->unsignedTinyInteger('note2_octave')->nullable()->after('octave');
        });
    }

    public function down(): void
    {
        Schema::table('melodic_interval_practices', function (Blueprint $table) {
            $table->dropColumn('note2_octave');
        });
    }
};
