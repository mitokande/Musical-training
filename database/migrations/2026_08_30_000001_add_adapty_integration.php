<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Adapty's own id for this device's profile. The mobile app calls
            // adapty.identify(user.id) on sign-in, so most events already carry
            // customer_user_id; this column is what rescues the other case —
            // someone who bought during onboarding, before the account existed,
            // whose purchase is only identifiable by profile_id until the app
            // links it (POST /api/v1/me/billing/adapty).
            $table->string('adapty_profile_id')->nullable()->after('stripe_customer_id')->index();
        });

        // Idempotency ledger for incoming Adapty webhooks, and the parking lot
        // for events that arrived before we knew whose they were.
        //
        //   processed_at set  → applied, a re-delivery is acknowledged and skipped
        //   processed_at null → deferred; replayed the moment a user claims the
        //                       profile, in arrival order.
        Schema::create('adapty_events', function (Blueprint $table) {
            $table->string('event_id')->primary();
            $table->string('type')->nullable();
            $table->string('profile_id')->nullable();
            $table->string('customer_user_id')->nullable();
            $table->text('payload');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['profile_id', 'processed_at']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('adapty_profile_id');
        });

        Schema::dropIfExists('adapty_events');
    }
};
