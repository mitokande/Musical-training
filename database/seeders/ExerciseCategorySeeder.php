<?php

namespace Database\Seeders;

use App\Models\ExerciseCategory;
use App\Models\Practice;
use Illuminate\Database\Seeder;

class ExerciseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $intervals = ExerciseCategory::updateOrCreate(
            ['slug' => 'intervals'],
            ['name' => 'Intervals', 'icon' => 'arrow-up-down', 'sort_order' => 1, 'is_active' => true]
        );

        $children = [
            ['name' => 'Melodic Intervals', 'slug' => 'melodic-intervals', 'icon' => 'music', 'sort_order' => 1],
            ['name' => 'Interval Direction', 'slug' => 'interval-direction', 'icon' => 'arrow-up-down', 'sort_order' => 2],
            ['name' => 'Harmonic Intervals', 'slug' => 'harmonic-intervals', 'icon' => 'layers', 'sort_order' => 3],
            ['name' => 'Interval Comparison', 'slug' => 'interval-comparison', 'icon' => 'bar-chart-2', 'sort_order' => 4],
            ['name' => 'Interval Construction', 'slug' => 'interval-construction', 'icon' => 'puzzle', 'sort_order' => 5],
        ];

        foreach ($children as $child) {
            ExerciseCategory::updateOrCreate(
                ['slug' => $child['slug']],
                array_merge($child, ['parent_id' => $intervals->id, 'is_active' => true])
            );
        }

        ExerciseCategory::updateOrCreate(
            ['slug' => 'single-note'],
            ['name' => 'Single Note Recognition', 'icon' => 'circle-dot', 'sort_order' => 2, 'is_active' => true]
        );

        ExerciseCategory::updateOrCreate(
            ['slug' => 'scales-modes'],
            ['name' => 'Scales & Modes', 'icon' => 'git-branch', 'sort_order' => 3, 'is_active' => true, 'is_premium' => true]
        );

        ExerciseCategory::updateOrCreate(
            ['slug' => 'chords'],
            ['name' => 'Chords', 'icon' => 'layers', 'sort_order' => 4, 'is_active' => true, 'is_premium' => true]
        );

        ExerciseCategory::updateOrCreate(
            ['slug' => 'rhythm'],
            ['name' => 'Rhythm', 'icon' => 'timer', 'sort_order' => 5, 'is_active' => true, 'is_premium' => true]
        );

        ExerciseCategory::updateOrCreate(
            ['slug' => 'melodic-dictation'],
            ['name' => 'Melodic Dictation', 'icon' => 'pencil', 'sort_order' => 6, 'is_active' => true, 'is_premium' => true]
        );

        // Link practices to categories
        $slugToCategory = [
            'melodic-interval-practice' => 'melodic-intervals',
            'interval-direction-practice' => 'interval-direction',
            'harmonic-interval-practice' => 'harmonic-intervals',
            'interval-comparison-practice' => 'interval-comparison',
            'interval-construction-practice' => 'interval-construction',
            'single-note-practice' => 'single-note',
        ];

        foreach ($slugToCategory as $practiceSlug => $categorySlug) {
            $category = ExerciseCategory::where('slug', $categorySlug)->first();
            if ($category) {
                Practice::where('slug', $practiceSlug)->update(['category_id' => $category->id]);
            }
        }
    }
}
