<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Persisted practice runs for the mobile API. The web flows keep their PHP
 * session keys (learning_path_session, exercise_practice_session); a token API
 * has no session, so the generated questions live here instead.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('practice_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('source', 20)->default('studio');
            $table->string('practice_type', 40)->index();
            $table->foreignId('learning_path_exercise_id')->nullable()
                ->constrained('learning_path_exercises')->nullOnDelete();
            $table->foreignId('exercise_session_id')->nullable()
                ->constrained('exercise_sessions')->nullOnDelete();
            $table->json('config_json')->nullable();
            $table->json('questions_json');
            $table->json('answers_json')->nullable();
            $table->unsignedSmallInteger('question_count');
            $table->unsignedSmallInteger('current_index')->default(0);
            $table->unsignedSmallInteger('answered_count')->default(0);
            $table->unsignedSmallInteger('correct_count')->default(0);
            $table->decimal('score', 5, 2)->nullable();
            $table->string('status', 16)->default('active');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('practice_sessions');
    }
};
