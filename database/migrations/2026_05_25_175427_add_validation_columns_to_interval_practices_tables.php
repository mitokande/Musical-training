<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    private array $tables = [
        'melodic_interval_practices',
        'harmonic_interval_practices',
        'interval_direction_practices',
        'interval_construction_practices',
        'interval_comparison_practices',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->json('backup_data')->nullable()->after('id');
                $blueprint->boolean('needs_review')->default(false)->after('backup_data');
                $blueprint->string('validation_status', 20)->nullable()->after('needs_review');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn(['backup_data', 'needs_review', 'validation_status']);
            });
        }
    }
};
