<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-user marketing email preferences: how often and about which topics a
 * user wants to hear from us. Only marketing mail (campaigns + lifecycle
 * automations) respects these — account/transactional notices (booking
 * confirmations, lesson reminders, password resets) always send, because
 * they never pass through the marketing gate in EmailDispatchService.
 *
 * A user without a row keeps the permissive default (receive everything),
 * so existing accounts are unaffected until they opt to change anything.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            // all = respect the topic toggles; weekly = only the weekly progress
            // digest; important_only = no marketing mail at all.
            $table->string('frequency')->default('all');

            $table->boolean('topic_tips')->default(true);      // learning reminders / nudges
            $table->boolean('topic_progress')->default(true);  // weekly progress summary
            $table->boolean('topic_offers')->default(true);    // premium & promotions
            $table->boolean('topic_product')->default(true);   // product news / announcements

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_preferences');
    }
};
