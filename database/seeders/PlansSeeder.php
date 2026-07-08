<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    /**
     * Seed the plans table from config/plans.php so plan limits become
     * manageable in the admin panel. Existing plans are kept untouched.
     */
    public function run(): void
    {
        $roleNames = ['user' => 'Student', 'teacher' => 'Teacher', 'school' => 'Music School'];
        $sort = 0;

        foreach (config('plans') as $role => $types) {
            foreach ($types as $type => $features) {
                Plan::firstOrCreate(
                    ['role' => $role, 'type' => $type],
                    [
                        'name' => ($roleNames[$role] ?? ucfirst($role)).' '.ucfirst($type),
                        'slug' => "{$role}-{$type}",
                        'description' => "Default {$type} plan for {$role} accounts (limits synced from config/plans.php).",
                        'price_monthly' => 0,
                        'price_yearly' => 0,
                        'currency' => 'USD',
                        'features' => $features,
                        'is_active' => true,
                        'sort_order' => $sort++,
                    ]
                );
            }
        }
    }
}
