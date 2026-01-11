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
        Schema::create('interval_comparison_practices', function (Blueprint $table) {
            $table->id();
            $table->string('interval_a')->nullable();
            $table->string('interval_b')->nullable();
            $table->string('target')->nullable();
            $table->string('octave')->default('4');
            $table->string('clef')->default('treble');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interval_comparison_practices');
    }
};
