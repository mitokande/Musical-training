<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_exercise_counts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('practice_id');
            $table->date('date');
            $table->unsignedInteger('count')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'practice_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_exercise_counts');
    }
};
