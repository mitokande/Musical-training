<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_learning_path_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('learning_path_exercise_id')
                ->constrained('learning_path_exercises')->cascadeOnDelete();
            $table->unsignedSmallInteger('question_count_attempted');
            $table->unsignedSmallInteger('total_questions')->default(0);
            $table->unsignedSmallInteger('correct_answers')->default(0);
            $table->decimal('score', 5, 2)->nullable();
            $table->boolean('completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'learning_path_exercise_id'], 'ulpp_user_exercise_unique');
            $table->index(['user_id', 'learning_path_exercise_id'], 'ulpp_user_exercise_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_learning_path_progress');
    }
};
