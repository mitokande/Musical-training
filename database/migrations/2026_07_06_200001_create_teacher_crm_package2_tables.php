<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('level', 30)->nullable(); // beginner | intermediate | advanced
            $table->string('instrument_focus', 100)->nullable();
            $table->string('cover_path', 500)->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            $table->index(['teacher_id', 'archived_at']);
        });

        Schema::create('teacher_class_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['teacher_class_id', 'student_id'], 'class_student_unique');
        });

        Schema::create('teacher_student_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 10); // email | link
            $table->string('email')->nullable();
            $table->string('name')->nullable();
            $table->string('token', 64)->unique();
            $table->foreignId('teacher_class_id')->nullable()
                ->constrained('teacher_classes')->nullOnDelete();
            $table->string('status', 20)->default('pending'); // pending | accepted | revoked | expired
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('accepted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
            $table->index(['teacher_id', 'status']);
        });

        Schema::create('teacher_student_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 50);
            $table->string('color', 20)->nullable();
            $table->timestamps();
            $table->unique(['teacher_id', 'name']);
        });

        Schema::create('teacher_student_tag_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tag_id')->constrained('teacher_student_tags')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['tag_id', 'student_id'], 'tag_student_unique');
        });

        Schema::create('teacher_student_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
            $table->index(['teacher_id', 'student_id']);
        });

        Schema::create('teacher_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->string('type', 30); // exercise | learning_path | ai_generated | practice_goal
            $table->string('practice_type', 60)->nullable(); // practice slug; null for practice_goal
            $table->foreignId('learning_path_exercise_id')->nullable()
                ->constrained('learning_path_exercises')->nullOnDelete();
            $table->json('config_json')->nullable();
            $table->text('ai_prompt')->nullable();
            $table->unsignedSmallInteger('question_count')->default(0);
            $table->string('difficulty', 20)->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->unsignedTinyInteger('max_attempts')->nullable(); // null = unlimited
            $table->unsignedSmallInteger('daily_practice_minutes')->nullable();
            $table->unsignedSmallInteger('weekly_practice_minutes')->nullable();
            $table->string('attachment_path', 500)->nullable();
            $table->string('attachment_name')->nullable();
            $table->string('reward_label', 100)->nullable();
            $table->string('status', 20)->default('draft'); // draft | scheduled | sent | completed | archived
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['teacher_id', 'status']);
        });

        // Immutable canonical question snapshots. Rows are written by the
        // generator during draft review and frozen once the assignment is sent —
        // student playback and evaluation always read these exact rows.
        Schema::create('teacher_assignment_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_assignment_id')
                ->constrained('teacher_assignments', indexName: 'ta_questions_assignment_fk')
                ->cascadeOnDelete();
            $table->unsignedSmallInteger('position');
            $table->json('question_data');
            $table->timestamps();
            $table->index(['teacher_assignment_id', 'position'], 'ta_questions_assignment_position');
        });

        Schema::create('teacher_assignment_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_assignment_id')
                ->constrained('teacher_assignments', indexName: 'ta_recipients_assignment_fk')
                ->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('teacher_class_id')->nullable()
                ->constrained('teacher_classes', indexName: 'ta_recipients_class_fk')
                ->nullOnDelete();
            $table->string('status', 20)->default('assigned'); // assigned | started | completed
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->decimal('best_score', 5, 2)->nullable();
            $table->unsignedSmallInteger('attempts_count')->default(0);
            $table->timestamps();
            $table->unique(['teacher_assignment_id', 'student_id'], 'ta_recipients_unique');
            $table->index(['student_id', 'status']);
        });

        Schema::create('teacher_assignment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipient_id')
                ->constrained('teacher_assignment_recipients', indexName: 'ta_attempts_recipient_fk')
                ->cascadeOnDelete();
            $table->unsignedSmallInteger('attempt_number');
            $table->json('answers')->nullable(); // per question: given, correct, is_correct, answered_at
            $table->unsignedSmallInteger('correct_count')->default(0);
            $table->unsignedSmallInteger('question_count')->default(0);
            $table->decimal('score', 5, 2)->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('teacher_student_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('teacher_assignment_id')->nullable()
                ->constrained('teacher_assignments', indexName: 'ts_rewards_assignment_fk')
                ->nullOnDelete();
            $table->string('type', 20)->default('sticker'); // sticker | badge | label | milestone
            $table->string('label', 100);
            $table->string('icon', 50)->nullable();
            $table->string('note', 500)->nullable();
            $table->timestamps();
            $table->index(['student_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_student_rewards');
        Schema::dropIfExists('teacher_assignment_attempts');
        Schema::dropIfExists('teacher_assignment_recipients');
        Schema::dropIfExists('teacher_assignment_questions');
        Schema::dropIfExists('teacher_assignments');
        Schema::dropIfExists('teacher_student_notes');
        Schema::dropIfExists('teacher_student_tag_assignments');
        Schema::dropIfExists('teacher_student_tags');
        Schema::dropIfExists('teacher_student_invitations');
        Schema::dropIfExists('teacher_class_students');
        Schema::dropIfExists('teacher_classes');
    }
};
