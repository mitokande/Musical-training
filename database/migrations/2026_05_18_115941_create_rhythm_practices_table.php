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
        Schema::create('rhythm_practices', function (Blueprint $table) {
            $table->id();
            $table->string('time_signature', 5)->default('4/4');
            $table->json('note_values');
            $table->json('other_options');
            $table->unsignedSmallInteger('tempo')->default(80);
            $table->tinyInteger('bars')->default(2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rhythm_practices');
    }
};
