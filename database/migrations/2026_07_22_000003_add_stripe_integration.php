<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The Stripe Customer that owns this user's subscriptions + payment methods.
        // Created lazily on first checkout and reused for every later purchase so a
        // user maps to exactly one Stripe customer (renewals, portal, refunds).
        Schema::table('users', function (Blueprint $table) {
            $table->string('stripe_customer_id')->nullable()->after('plan_cycle')->index();
        });

        // Idempotency ledger for incoming Stripe webhooks. Stripe delivers each
        // event at-least-once (retries on non-2xx, occasional duplicates); we record
        // every processed event id and skip anything we have already handled so
        // renewals are never double-counted.
        Schema::create('stripe_events', function (Blueprint $table) {
            $table->string('event_id')->primary(); // Stripe's evt_… id
            $table->string('type')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('stripe_customer_id');
        });

        Schema::dropIfExists('stripe_events');
    }
};
