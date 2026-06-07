<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('body')->nullable();
            $table->text('excerpt')->nullable();
            $table->string('featured_image', 500)->nullable();
            $table->enum('content_type', ['article', 'video', 'document', 'audio', 'sheet_music'])->default('article');
            $table->enum('status', ['draft', 'pending', 'published', 'rejected'])->default('draft');
            $table->enum('visibility', ['public', 'students_only', 'school_only', 'private'])->default('public');
            $table->string('video_url', 500)->nullable();
            $table->string('audio_file', 500)->nullable();
            $table->string('document_file', 500)->nullable();
            $table->json('tags')->nullable();
            $table->string('category', 100)->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['author_id', 'status']);
            $table->index(['content_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
