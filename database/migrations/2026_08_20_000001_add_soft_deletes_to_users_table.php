<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Soft account deletion.
 *
 * A deleted account disappears completely from the member's point of view —
 * they are logged out, cannot log back in, and their e-mail / username are
 * freed so the address can be used to sign up again. Nothing is destroyed:
 * the row stays behind `deleted_at` with the original identity preserved in
 * `deleted_email` / `deleted_username` so admins can still find, inspect and
 * (if support ever needs to) restore the account.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
            // Original identity, kept for the admin panel. The live email /
            // username columns are anonymised on delete so their unique
            // indexes stop blocking a fresh sign-up.
            $table->string('deleted_email')->nullable()->after('deleted_at');
            $table->string('deleted_username')->nullable()->after('deleted_email');
            $table->string('deletion_reason', 500)->nullable()->after('deleted_username');
            // Null when the member deleted their own account.
            $table->unsignedBigInteger('deleted_by')->nullable()->after('deletion_reason');
            // Side-effects that have to be undone on restore (google id,
            // teacher profile status, …).
            $table->json('deletion_meta')->nullable()->after('deleted_by');

            $table->index('deleted_email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['deleted_email']);
            $table->dropColumn([
                'deleted_at',
                'deleted_email',
                'deleted_username',
                'deletion_reason',
                'deleted_by',
                'deletion_meta',
            ]);
        });
    }
};
