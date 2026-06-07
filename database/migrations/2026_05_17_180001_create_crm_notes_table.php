<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->text('note');
            $table->enum('type', ['general', 'follow_up', 'payment', 'support', 'academic'])->default('general');
            $table->boolean('is_pinned')->default(false);
            $table->date('follow_up_date')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index('admin_id');
            $table->index('follow_up_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_notes');
    }
};
