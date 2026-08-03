<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The pool of Harmoniva-owned licensed Zoom users that lessons are
        // hosted on. Populated by `php artisan zoom:sync-hosts`; teachers never
        // own a licence. A future per-teacher BYO-Zoom mode leaves this table
        // untouched and simply stores a null zoom_host_id on the meeting.
        Schema::create('zoom_hosts', function (Blueprint $table) {
            $table->id();
            $table->string('zoom_user_id', 64)->unique();
            $table->string('email');
            $table->string('display_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });

        // One row per appointment that has a live Zoom meeting.
        //
        // Kept out of teacher_appointments deliberately: the appointment's
        // meeting_url column is only 500 chars, and this table is what the host
        // allocator scans for overlaps. The host `start_url` is NOT stored —
        // it is a long-lived host credential; the Lesson Room fetches a fresh
        // short-lived ZAK at join time instead.
        Schema::create('zoom_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')
                ->constrained('teacher_appointments', indexName: 'zm_appointment_fk')
                ->cascadeOnDelete();
            $table->foreignId('zoom_host_id')
                ->nullable()
                ->constrained('zoom_hosts', indexName: 'zm_host_fk')
                ->nullOnDelete();
            $table->string('zoom_meeting_id', 32);
            $table->string('zoom_meeting_uuid', 64)->nullable();
            $table->text('join_url');
            $table->string('passcode', 64)->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('status', 20)->default('active'); // active | cancelled
            $table->timestamps();

            $table->unique('appointment_id', 'zm_appointment_unique');
            $table->index(['zoom_host_id', 'status', 'starts_at'], 'zm_host_window');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zoom_meetings');
        Schema::dropIfExists('zoom_hosts');
    }
};
