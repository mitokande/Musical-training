<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('plan', 20)->default('free')->after('role');
            $table->string('google_id', 255)->nullable()->unique()->after('password');
            $table->string('avatar_url', 500)->nullable()->after('google_id');
            $table->string('phone', 20)->nullable()->after('avatar_url');
            $table->string('country', 100)->nullable()->after('phone');
            $table->string('city', 100)->nullable()->after('country');
            $table->date('date_of_birth')->nullable()->after('city');
            $table->timestamp('last_active_at')->nullable()->after('date_of_birth');

            $table->index('role');
            $table->index('plan');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable(false)->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['plan']);
            $table->dropColumn([
                'plan', 'google_id', 'avatar_url', 'phone',
                'country', 'city', 'date_of_birth', 'last_active_at',
            ]);
        });
    }
};
