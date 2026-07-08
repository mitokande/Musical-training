<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_profile_id')->constrained()->cascadeOnDelete();
            $table->string('institution');
            $table->string('program')->nullable();
            $table->string('field_of_study')->nullable();
            $table->unsignedSmallInteger('graduation_year')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('teacher_instruments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_profile_id')->constrained()->cascadeOnDelete();
            $table->string('instrument', 100);
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('teacher_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_profile_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('lesson_type', 100)->nullable();
            $table->string('format', 20)->nullable(); // online | in_person | hybrid
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->string('price_text', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('teacher_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_profile_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('youtube_id', 20);
            $table->string('url', 500);
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('teacher_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_profile_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 20); // photo | document
            $table->string('disk', 30);
            $table->string('path', 500);
            $table->string('original_name');
            $table->string('mime_type', 100);
            $table->unsignedInteger('size'); // bytes
            $table->string('title')->nullable();
            $table->string('visibility', 20)->default('public'); // public | private
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('teacher_payment_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_profile_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('url', 500);
            $table->text('description')->nullable();
            $table->string('price_text', 100)->nullable();
            $table->string('lesson_type', 100)->nullable();
            $table->string('visibility', 30)->default('public'); // public | approved_students | appointment_confirmation
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('teacher_profile_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('viewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_hash', 64);
            $table->date('viewed_on');
            $table->timestamps();
            $table->unique(['teacher_profile_id', 'ip_hash', 'viewed_on'], 'teacher_profile_views_dedup');
        });

        Schema::create('teacher_profile_moderation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 50); // submitted | approved | rejected | suspended | reinstated | forced_private | note
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Foundation for Package 2 — required now so the subscription benefit
        // service can count active approved Premium students.
        Schema::create('teacher_student_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 40)->default('pending_student_approval');
            // pending_teacher_request | pending_student_approval | active | declined | revoked_by_teacher | revoked_by_student | archived
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            $table->unique(['teacher_id', 'student_id']);
            $table->index(['teacher_id', 'status']);
        });

        Schema::create('teacher_subscription_benefits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // the teacher
            $table->string('type', 30); // discount | free_period
            $table->unsignedTinyInteger('discount_percentage')->nullable();
            $table->unsignedInteger('qualifying_student_count')->default(0);
            $table->string('status', 20)->default('active'); // active | expired | revoked | superseded
            $table->string('source', 30)->default('automatic'); // automatic | admin_override
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });

        Schema::create('teacher_subscription_benefit_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Explicit short FK name — the default generated name exceeds
            // MySQL's 64-character identifier limit.
            $table->foreignId('teacher_subscription_benefit_id')->nullable()
                ->constrained(table: 'teacher_subscription_benefits', indexName: 'tsbh_benefit_id_foreign')
                ->nullOnDelete();
            $table->string('event', 50); // granted | superseded | revoked | expired | recalculated | admin_override
            $table->json('details')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_subscription_benefit_histories');
        Schema::dropIfExists('teacher_subscription_benefits');
        Schema::dropIfExists('teacher_student_relationships');
        Schema::dropIfExists('teacher_profile_moderation_logs');
        Schema::dropIfExists('teacher_profile_views');
        Schema::dropIfExists('teacher_payment_links');
        Schema::dropIfExists('teacher_media');
        Schema::dropIfExists('teacher_videos');
        Schema::dropIfExists('teacher_services');
        Schema::dropIfExists('teacher_instruments');
        Schema::dropIfExists('teacher_educations');
    }
};
