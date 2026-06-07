<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_interval_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('practice_id');
            $table->string('interval_name');
            $table->unsignedInteger('total_questions')->default(0);
            $table->unsignedInteger('correct_answers')->default(0);
            $table->unsignedInteger('incorrect_answers')->default(0);
            $table->timestamp('last_answered_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'practice_id', 'interval_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_interval_stats');
    }
};
