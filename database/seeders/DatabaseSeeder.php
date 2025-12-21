<?php

namespace Database\Seeders;

use App\Models\SingleNotePractice;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'username' => 'testuser',
            'role' => 'admin',
            'email' => 'test@example.com',
            'password' => \Hash::make('password'),
        ]);

        SingleNotePractice::create([
            'target' => 'C',
            'octave' => '4',
            'target_type' => 'note',
            'other_options' => 'C, E, G',
        ]);

        SingleNotePractice::create([
            'target' => 'E',
            'octave' => '3',
            'target_type' => 'note',
            'other_options' => 'A, G, F',
        ]);
    }
}
