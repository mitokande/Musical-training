<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Seed the real Premium prices onto the plans table. These mirror the
     * public pricing pages (single source of truth for figures). Free plans
     * stay at 0. Idempotent: safe to re-run.
     */
    public function up(): void
    {
        $prices = [
            'user' => ['monthly' => 6.90, 'yearly' => 40.00],
            'teacher' => ['monthly' => 16.90, 'yearly' => 149.00],
            'school' => ['monthly' => 29.90, 'yearly' => 169.00],
        ];

        foreach ($prices as $role => $p) {
            DB::table('plans')
                ->where('role', $role)
                ->where('type', 'premium')
                ->update([
                    'price_monthly' => $p['monthly'],
                    'price_yearly' => $p['yearly'],
                    'currency' => 'USD',
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        DB::table('plans')->where('type', 'premium')->update([
            'price_monthly' => 0,
            'price_yearly' => 0,
        ]);
    }
};
