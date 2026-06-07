<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('role', 'student')->update(['role' => 'user']);

        DB::table('users')->update(['plan' => 'premium']);
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'user')->update(['role' => 'student']);

        DB::table('users')->where('role', '!=', 'admin')->update(['plan' => 'free']);
    }
};
