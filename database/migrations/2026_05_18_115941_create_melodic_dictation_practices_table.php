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
        Schema::create('melodic_dictation_practices', function (Blueprint $table) {
            $table->id();
            $table->json('notes');
            $table->tinyInteger('bars')->default(2);
            $table->enum('clef', ['treble', 'bass', 'alto'])->default('treble');
            $table->string('key_signature', 10)->default('C');
            $table->unsignedSmallInteger('tempo')->default(60);
            $table->boolean('include_rhythm')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('melodic_dictation_practices');
    }
};
