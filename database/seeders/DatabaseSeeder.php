<?php

namespace Database\Seeders;

use App\Models\Practice;
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


        Practice::create([
            'name' => 'Single Note Practice',
            'slug' => 'single-note-practice',
            'description' => 'Practice single notes',
            'type' => 'Recognition',
            'is_premium' => false,
        ]);

        Practice::create([
            'name' => 'Interval Practice',
            'slug' => 'interval-practice',
            'description' => 'Practice intervals',
            'type' => 'Dictation',
            'is_premium' => false,
        ]);

        SingleNotePractice::create([
            'target' => 'C',
            'octave' => '4',
            'target_type' => 'note',
            'other_options' => 'C, E, G, B',
        ]);

        SingleNotePractice::create([
            'target' => 'E',
            'octave' => '3',
            'target_type' => 'note',
            'other_options' => 'A, G, F, D',
        ]);
    }
}
