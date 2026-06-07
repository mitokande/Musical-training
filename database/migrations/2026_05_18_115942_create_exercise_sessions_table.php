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
        Schema::create('exercise_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('exercise_setup_templates')->nullOnDelete();
            $table->string('exercise_type', 50);
            $table->string('difficulty', 20)->default('intermediate');
            $table->unsignedSmallInteger('question_count')->default(10);
            $table->boolean('ai_mode')->default(false);
            $table->json('settings_json');
            $table->unsignedSmallInteger('score')->nullable();
            $table->decimal('accuracy', 5, 2)->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'exercise_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercise_sessions');
    }
};
