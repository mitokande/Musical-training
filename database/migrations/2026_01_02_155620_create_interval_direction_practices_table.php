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
        Schema::create('interval_direction_practices', function (Blueprint $table) {
            $table->id();
            //Two notes are played, student must decide whether it is in ascending or descending order. Bass, alto and treble cleff support. Choosen per question.
            $table->string('clef')->default('treble');
            $table->string('note1')->nullable();
            $table->string('note2')->nullable();
            $table->string('direction')->nullable();
            $table->string('octave')->default('4');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interval_direction_practices');
    }
};
