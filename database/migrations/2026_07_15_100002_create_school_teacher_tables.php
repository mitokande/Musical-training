<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // School ↔ member-teacher memberships. Mirrors teacher_student_relationships:
        // both sides are users; school_id is the school account's user id.
        Schema::create('school_teacher_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 32)->default('pending_teacher_approval');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'teacher_id']);
            $table->index(['school_id', 'status']);
            $table->index(['teacher_id', 'status']);
        });

        // Email/link invitations for teachers who may not have an account yet.
        Schema::create('school_teacher_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 10); // email | link
            $table->string('email')->nullable();
            $table->string('name')->nullable();
            $table->string('token', 64)->unique();
            $table->string('status', 20)->default('pending');
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('accepted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_teacher_invitations');
        Schema::dropIfExists('school_teacher_relationships');
    }
};
