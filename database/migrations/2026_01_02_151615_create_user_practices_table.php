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
        Schema::create('user_practices', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('practice_id');
            $table->integer('score')->nullable();
            $table->integer('total_questions')->nullable();
            $table->integer('correct_answers')->nullable();
            $table->integer('incorrect_answers')->nullable();
            $table->integer('skipped_answers')->nullable();
            $table->integer('total_time')->nullable();
            $table->integer('average_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_practices');
    }
};
