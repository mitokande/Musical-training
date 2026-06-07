<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->datetime('starts_at');
            $table->datetime('ends_at');
            $table->enum('type', ['lesson', 'consultation', 'trial', 'meeting'])->default('lesson');
            $table->enum('status', ['scheduled', 'confirmed', 'cancelled', 'completed'])->default('scheduled');
            $table->string('location')->nullable();
            $table->string('meeting_url')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['teacher_id', 'starts_at']);
            $table->index('student_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
