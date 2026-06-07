<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['follow_up', 'call', 'email', 'meeting', 'other'])->default('other');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->datetime('due_date')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->timestamps();

            $table->index(['admin_id', 'status']);
            $table->index('user_id');
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_tasks');
    }
};
