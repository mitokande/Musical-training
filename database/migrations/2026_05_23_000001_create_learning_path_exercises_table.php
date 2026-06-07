<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_path_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('exercise_categories')->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->json('translations');
            $table->enum('level', ['beginner', 'intermediate', 'advanced']);
            $table->unsignedTinyInteger('sort_order');
            $table->string('slug')->unique();
            $table->json('config_json');
            $table->json('tags')->nullable();
            $table->json('skills_trained')->nullable();
            $table->unsignedTinyInteger('estimated_duration_minutes')->default(5);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['category_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_path_exercises');
    }
};
