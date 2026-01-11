<?php

namespace Database\Seeders;

use App\Models\IntervalComparisonPractice;
use App\Models\IntervalDirectionPractice;
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
            'name' => 'Interval Direction Practice',
            'slug' => 'interval-direction-practice',
            'description' => 'Practice interval direction',
            'type' => 'Recognition',
            'is_premium' => false,
        ]);

        Practice::create([
            'name' => 'Interval Comparison Practice',
            'slug' => 'interval-comparison-practice',
            'description' => 'Practice interval comparison',
            'type' => 'Recognition',
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
            'octave' => '5',
            'target_type' => 'note',
            'other_options' => 'A, E, F, D',
        ]);

        IntervalDirectionPractice::create([
            'clef' => 'treble',
            'note1' => 'C',
            'note2' => 'D',
            'direction' => 'ascending',
            'octave' => '4',
        ]);

        IntervalDirectionPractice::create([
            'clef' => 'treble',
            'note1' => 'D',
            'note2' => 'C',
            'direction' => 'descending',
            'octave' => '4',
        ]);

        IntervalDirectionPractice::create([
            'clef' => 'treble',
            'note1' => 'G',
            'note2' => 'F',
            'direction' => 'descending',
            'octave' => '4',
        ]);

        IntervalComparisonPractice::create([
            'interval_a' => 'C,E',
            'interval_b' => 'D,F',
            'target' => 'C,E',
            'octave' => '4',
            'clef' => 'treble',
        ]);
    }
}
