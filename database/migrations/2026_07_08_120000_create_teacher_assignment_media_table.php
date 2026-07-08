<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_assignment_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_assignment_id')
                ->constrained('teacher_assignments', indexName: 'tam_assignment_fk')
                ->cascadeOnDelete();
            $table->foreignId('teacher_media_id')
                ->constrained('teacher_media', indexName: 'tam_media_fk')
                ->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['teacher_assignment_id', 'teacher_media_id'], 'tam_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_assignment_media');
    }
};
