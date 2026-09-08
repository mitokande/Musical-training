<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sign in with Apple, the column.
 *
 * Apple's `sub` — its stable id for this person *in this app*, and the only
 * identifier that survives everything they can change. The address may be a
 * relay alias they can switch off, and they can change their real one; this
 * cannot, so account matching keys on it exactly as it keys on google_id.
 *
 * Unique, and nullable because almost no row will ever have one. Deleting an
 * account clears it (AccountDeletionService), or the unique index would lock
 * that Apple ID out of signing up again.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('apple_id', 255)->nullable()->unique()->after('google_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['apple_id']);
            $table->dropColumn('apple_id');
        });
    }
};
