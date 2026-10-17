<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ad-hoc live lessons a teacher starts from the CRM dashboard, hosted on
 * Derslik (meeting.harmoniva.app) and embedded on Harmoniva's own pages.
 *
 * `share_token` is the public part of the student link (/live/{token}); the
 * Derslik student URL itself is never shown, so every shared link stays on
 * harmoniva.app. `teacher_url` carries Derslik's teacher key and is stored
 * encrypted (see the model cast). `ended_at` set = links closed, Derslik room
 * deleted.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 80);
            $table->string('derslik_room_id', 32)->unique();
            $table->text('teacher_url');
            $table->string('student_url', 255);
            $table->string('share_token', 40)->unique();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index(['teacher_id', 'ended_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_lessons');
    }
};
