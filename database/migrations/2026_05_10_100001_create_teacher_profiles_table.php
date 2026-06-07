<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('title', 100)->nullable();
            $table->text('short_bio')->nullable();
            $table->text('long_bio')->nullable();
            $table->json('specializations')->nullable();
            $table->json('teaching_subjects')->nullable();
            $table->string('education_background')->nullable();
            $table->unsignedInteger('experience_years')->nullable();
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->string('currency', 3)->default('TRY');
            $table->enum('lesson_format', ['online', 'in_person', 'hybrid'])->nullable();
            $table->string('website_url', 500)->nullable();
            $table->json('social_links')->nullable();
            $table->string('payment_link', 500)->nullable();
            $table->string('location')->nullable();
            $table->json('languages')->nullable();
            $table->boolean('accepts_students')->default(true);
            $table->unsignedInteger('max_students')->nullable();
            $table->boolean('public_profile')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_profiles');
    }
};
