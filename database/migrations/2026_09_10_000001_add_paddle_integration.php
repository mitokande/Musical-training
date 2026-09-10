<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The Paddle Customer that owns this user's subscriptions + payment
        // methods. Created lazily on first checkout and reused afterwards so a
        // user maps to exactly one Paddle customer (renewals, portal, refunds).
        // Sits alongside stripe_customer_id rather than replacing it: rows
        // bought through Stripe still have to resolve to their own provider.
        Schema::table('users', function (Blueprint $table) {
            $table->string('paddle_customer_id')->nullable()->after('adapty_profile_id')->index();
        });

        // Idempotency ledger for incoming Paddle webhooks. Paddle retries any
        // non-2xx delivery, so an event id we have already applied must be
        // acknowledged without being processed twice — otherwise a renewal is
        // billed into the invoice history more than once.
        Schema::create('paddle_events', function (Blueprint $table) {
            $table->string('event_id')->primary(); // Paddle's evt_… id
            $table->string('type')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('paddle_customer_id');
        });

        Schema::dropIfExists('paddle_events');
    }
};
