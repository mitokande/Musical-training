<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercise_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('parent_id')->nullable()->constrained('exercise_categories')->nullOnDelete();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_premium')->default(false);
            $table->timestamps();
        });

        Schema::table('practices', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('is_premium')->constrained('exercise_categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('practices', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
        Schema::dropIfExists('exercise_categories');
    }
};
