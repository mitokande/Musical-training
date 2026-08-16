<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The users table still defaulted `role` to the legacy 'student' value, even
 * though 2026_05_10_000002 converted every existing row to 'user' and the app
 * has only known user/teacher/school/admin since. Any account created without
 * an explicit role therefore landed on a value that no Email Center automation
 * targets (AutomationEngine filters on user/teacher/school), silently cutting
 * that account out of the whole lifecycle mailing.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->change();
        });

        DB::table('users')->where('role', 'student')->update(['role' => 'user']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('student')->change();
        });
    }
};
