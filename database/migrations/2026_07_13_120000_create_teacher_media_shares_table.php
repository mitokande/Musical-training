<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pivot linking a teacher document (teacher_media, kind=document) to the
 * specific students it has been shared with ("paylaştıklarım"). A row here
 * grants that student download access regardless of the file's base visibility.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_media_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_media_id')->constrained('teacher_media')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // the student
            $table->timestamps();

            $table->unique(['teacher_media_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_media_shares');
    }
};
