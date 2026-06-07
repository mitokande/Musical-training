<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('game_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('game_slug', 50); // note-rush, melody-memory, interval-blitz, chord-clash
            $table->unsignedInteger('score')->default(0);
            $table->unsignedSmallInteger('max_streak')->default(0);
            $table->unsignedTinyInteger('level_reached')->default(1);
            $table->json('metadata')->nullable(); // rounds, accuracy, duration_seconds, etc.
            $table->timestamps();

            $table->index(['game_slug', 'score']); // leaderboard queries
            $table->index(['user_id', 'game_slug']); // personal best queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_scores');
    }
};
