<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usage_counters', function (Blueprint $table) {
            $table->id();
            // 'user' (subject_id = users.id) or 'guest' (subject_id = guest cookie token)
            $table->string('subject_type', 8);
            $table->string('subject_id', 64);
            $table->string('feature', 48);
            // 'total' for lifetime counters, otherwise a Y-m-d date for daily counters.
            // A plain string keeps the unique index reliable (NULL dates would not).
            $table->string('period', 10)->default('total');
            $table->unsignedInteger('count')->default(0);
            $table->timestamps();

            $table->unique(['subject_type', 'subject_id', 'feature', 'period'], 'usage_counters_subject_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usage_counters');
    }
};
