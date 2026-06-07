<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('primary_instrument', 100)->nullable();
            $table->json('secondary_instruments')->nullable();
            $table->enum('musical_level', ['beginner', 'intermediate', 'advanced'])->nullable();
            $table->enum('education_status', ['self_taught', 'private_lessons', 'music_school', 'professional'])->nullable();
            $table->unsignedInteger('weekly_practice_hours')->nullable();
            $table->json('learning_goals')->nullable();
            $table->json('interests')->nullable();
            $table->text('bio')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
