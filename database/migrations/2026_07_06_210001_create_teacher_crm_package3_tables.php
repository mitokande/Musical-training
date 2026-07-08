<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Messaging ────────────────────────────────────────────────────────
        Schema::create('teacher_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
            $table->unique(['teacher_id', 'student_id']);
        });

        Schema::create('teacher_conversation_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')
                ->constrained('teacher_conversations', indexName: 'tc_messages_conversation_fk')
                ->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['conversation_id', 'created_at'], 'tc_messages_conversation_created');
        });

        Schema::create('teacher_conversation_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')
                ->constrained('teacher_conversation_messages', indexName: 'tc_attachments_message_fk')
                ->cascadeOnDelete();
            $table->string('disk', 30);
            $table->string('path', 500);
            $table->string('original_name');
            $table->string('mime_type', 100);
            $table->unsignedInteger('size');
            $table->timestamps();
        });

        // ── Availability & appointments ──────────────────────────────────────
        Schema::create('teacher_booking_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->boolean('booking_enabled')->default(false);
            $table->unsignedSmallInteger('lesson_duration_minutes')->default(45);
            $table->unsignedSmallInteger('buffer_minutes')->default(15);
            $table->unsignedSmallInteger('advance_booking_days')->default(30);
            $table->unsignedSmallInteger('min_notice_hours')->default(24);
            $table->string('timezone', 50)->default('Europe/Istanbul');
            $table->timestamps();
        });

        Schema::create('teacher_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('weekday'); // 0 = Sunday … 6 = Saturday
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
            $table->index(['teacher_id', 'weekday']);
        });

        Schema::create('teacher_availability_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time')->nullable(); // null = whole day
            $table->time('end_time')->nullable();
            $table->boolean('is_blocked')->default(true); // true = unavailable, false = extra availability
            $table->string('note')->nullable();
            $table->timestamps();
            $table->index(['teacher_id', 'date'], 'ta_exceptions_teacher_date');
        });

        Schema::create('teacher_appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('status', 30)->default('pending_teacher_approval');
            // pending_teacher_approval | confirmed | rejected | cancelled_by_teacher |
            // cancelled_by_student | reschedule_requested | completed | no_show
            $table->string('topic', 255)->nullable(); // student's booking message / lesson topic
            $table->text('teacher_note')->nullable(); // private to the teacher
            $table->string('meeting_provider', 30)->default('manual'); // manual | google_meet (future) | …
            $table->string('meeting_url', 500)->nullable();
            $table->timestamp('requested_starts_at')->nullable(); // pending reschedule request
            $table->timestamp('requested_ends_at')->nullable();
            $table->string('timezone', 50)->nullable(); // student timezone at booking time
            $table->timestamps();
            $table->index(['teacher_id', 'starts_at']);
            $table->index(['student_id', 'starts_at']);
        });

        Schema::create('teacher_appointment_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')
                ->constrained('teacher_appointments', indexName: 'ta_activities_appointment_fk')
                ->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 40); // requested | confirmed | rejected | cancelled | reschedule_requested | rescheduled | completed | no_show | note
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ── Public reviews ───────────────────────────────────────────────────
        Schema::create('teacher_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating'); // 1–5
            $table->text('body')->nullable();
            $table->string('status', 20)->default('approved'); // approved | hidden | pending
            $table->timestamp('reported_at')->nullable();
            $table->string('report_reason', 500)->nullable();
            $table->timestamps();
            $table->unique(['teacher_profile_id', 'student_id']);
            $table->index(['teacher_profile_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_reviews');
        Schema::dropIfExists('teacher_appointment_activities');
        Schema::dropIfExists('teacher_appointments');
        Schema::dropIfExists('teacher_availability_exceptions');
        Schema::dropIfExists('teacher_availabilities');
        Schema::dropIfExists('teacher_booking_settings');
        Schema::dropIfExists('teacher_conversation_attachments');
        Schema::dropIfExists('teacher_conversation_messages');
        Schema::dropIfExists('teacher_conversations');
    }
};
