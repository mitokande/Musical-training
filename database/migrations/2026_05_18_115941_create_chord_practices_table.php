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
        Schema::create('chord_practices', function (Blueprint $table) {
            $table->id();
            $table->string('chord_type', 50);
            $table->string('root_note', 5);
            $table->enum('voicing', ['block', 'arpeggiated'])->default('block');
            $table->tinyInteger('inversion')->default(0);
            $table->string('octave', 3)->default('4');
            $table->json('other_options');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chord_practices');
    }
};
