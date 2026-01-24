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
        Schema::create('melodic_interval_practices', function (Blueprint $table) {
            $table->id();
            $table->string('interval');
            $table->string('note1');
            $table->string('note2');
            $table->string('octave');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('melodic_interval_practices');
    }
};
