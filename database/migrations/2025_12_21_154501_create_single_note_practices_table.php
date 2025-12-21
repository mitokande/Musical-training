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
        Schema::create('single_note_practices', function (Blueprint $table) {
            $table->id();
            $table->string('target');
            $table->string('target_type')->enum('note', 'chord');
            $table->string('other_options');
            $table->string('octave')->default('4');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('single_note_practices');
    }
};
