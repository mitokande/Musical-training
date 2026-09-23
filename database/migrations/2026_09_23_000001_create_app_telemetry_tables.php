<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Product analytics from the mobile app: what learners do, screen by screen.
 *
 * Two tables. `app_installs` is one row per installation — the device a batch
 * came from, and the account it was last seen signed in to. It is what links a
 * visitor's onboarding (which happens before there is an account) to the
 * account made at the end of it, and what counts the visitors who never make
 * one. `app_events` is the raw event stream, append-only.
 *
 * `occurred_at` is the device's clock corrected by the batch's own skew (see
 * TelemetryIngestor), with milliseconds, because a lesson produces several
 * events a second and their order is the point.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_installs', function (Blueprint $table) {
            $table->id();
            $table->uuid('install_id')->unique();
            // The account this install was last seen signed in to. Not an
            // ownership claim — a shared tablet has had several.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('platform', 16);
            $table->string('os_version', 32)->nullable();
            $table->string('app_version', 32)->nullable();
            $table->string('build', 32)->nullable();
            $table->string('locale', 35)->nullable();
            $table->string('timezone', 64)->nullable();
            // An explicit default on every NOT NULL timestamp: production runs
            // with explicit_defaults_for_timestamp off, where a bare one gets
            // ON UPDATE CURRENT_TIMESTAMP (first) or a zero date (the rest).
            $table->timestamp('first_seen_at')->useCurrent();
            $table->timestamp('last_seen_at')->useCurrent();
            $table->timestamps();

            $table->index('user_id');
            $table->index('first_seen_at');
        });

        Schema::create('app_events', function (Blueprint $table) {
            $table->id();
            // The device-minted id: a replayed batch is ignored on this index.
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->uuid('install_id');
            $table->uuid('session_id')->nullable();
            $table->string('name', 64);
            $table->json('props');
            // Per event rather than only on the install, so a release can be
            // compared with the one before it.
            $table->string('platform', 16);
            $table->string('app_version', 32)->nullable();
            $table->dateTime('occurred_at', 3);
            $table->timestamp('received_at')->useCurrent();

            $table->index(['name', 'occurred_at']);
            $table->index(['user_id', 'occurred_at']);
            $table->index(['install_id', 'occurred_at']);
            $table->index('session_id');
            $table->index('occurred_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_events');
        Schema::dropIfExists('app_installs');
    }
};
