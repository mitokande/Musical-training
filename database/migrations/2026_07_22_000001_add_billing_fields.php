<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A purchase starts life as 'pending' until the gateway confirms payment.
        // The original column was a DB enum (a CHECK constraint on SQLite) that did
        // not allow 'pending'; relax it to a plain string on every driver so the
        // application controls the allowed statuses.
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->change();
        });

        // Premium entitlement is still driven by users.plan ('free'|'premium');
        // these columns let a paid subscription expire the entitlement without a
        // separate lookup on every request.
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('plan_expires_at')->nullable()->after('plan');
            $table->string('plan_cycle', 12)->nullable()->after('plan_expires_at'); // monthly|yearly
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('billing_cycle', 12)->default('monthly')->after('status'); // monthly|yearly
            $table->boolean('auto_renew')->default(true)->after('billing_cycle');
        });

        Schema::table('invoices', function (Blueprint $table) {
            // Snapshot of the plan/cycle purchased, so an invoice reads on its own.
            $table->string('billing_cycle', 12)->nullable()->after('subscription_id');
            $table->string('provider')->nullable()->after('payment_method');
            $table->string('provider_reference')->nullable()->after('provider');
            $table->timestamp('refunded_at')->nullable()->after('paid_at');
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE subscriptions MODIFY COLUMN status ENUM('active','cancelled','expired','past_due','trialing') NOT NULL DEFAULT 'active'");
        } else {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->string('status', 20)->default('active')->change();
            });
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['plan_expires_at', 'plan_cycle']);
        });
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['billing_cycle', 'auto_renew']);
        });
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['billing_cycle', 'provider', 'provider_reference', 'refunded_at']);
        });
    }
};
