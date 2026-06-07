<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questionnaire_questions', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('question_text');
            $table->enum('question_type', ['text', 'number', 'single_choice', 'multi_choice', 'scale']);
            $table->json('options')->nullable();
            $table->string('category', 50);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_required')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questionnaire_questions');
    }
};
