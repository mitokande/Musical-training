<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The free trial is granted as plan='premium' backed by a 'trialing'
        // subscription row; these two columns mirror it onto the user for the
        // same reason plan_expires_at exists — so a blade can render "N days
        // left" and the once-ever guard can run without a subscriptions lookup
        // on every request.
        Schema::table('users', function (Blueprint $table) {
            // Set the moment a trial is claimed and never cleared afterwards:
            // its presence is what stops a second claim. An admin re-grant
            // nulls it deliberately.
            $table->timestamp('trial_started_at')->nullable()->after('plan_cycle');
            $table->timestamp('trial_ends_at')->nullable()->after('trial_started_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['trial_started_at', 'trial_ends_at']);
        });
    }
};
