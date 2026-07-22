<?php

/*
|--------------------------------------------------------------------------
| Practice page SEO metadata
|--------------------------------------------------------------------------
|
| Per-slug SEO title/description for the 10 guest-accessible ear-training
| exercises at /practice/{slug}. These pages are indexable and listed in the
| sitemap, so each needs a unique, keyword-rich title + meta description.
|
| Keyed by the same slugs used in PageController::$practiceMap and
| LearningPathQuestionGenerator. Consumed by resources/views/practice.blade.php
| via the partials.practice-seo include.
|
*/

return [
    'single-note-practice' => [
        'name' => 'Single Note Recognition',
        'title' => 'Single Note Recognition — Free Ear Training Exercise',
        'description' => 'Train your ear to identify single notes by sound. Free online single-note recognition exercise with staff notation, a virtual piano, and instant feedback.',
    ],
    'melodic-interval-practice' => [
        'name' => 'Melodic Interval Ear Training',
        'title' => 'Melodic Interval Ear Training — Free Online Exercise',
        'description' => 'Learn to recognise melodic intervals by ear as they are played one note after another. Free interval ear-training exercise with adjustable difficulty and instant feedback.',
    ],
    'harmonic-interval-practice' => [
        'name' => 'Harmonic Interval Ear Training',
        'title' => 'Harmonic Interval Ear Training — Free Online Exercise',
        'description' => 'Identify harmonic intervals played as two notes sounding together. Free ear-training exercise covering seconds through octaves with staff notation and instant feedback.',
    ],
    'interval-direction-practice' => [
        'name' => 'Interval Direction Training',
        'title' => 'Interval Direction (Ascending or Descending) — Free Exercise',
        'description' => 'Hear two notes and decide whether the interval moves up or down. Free interval-direction ear-training exercise for beginners, with instant feedback.',
    ],
    'interval-comparison-practice' => [
        'name' => 'Interval Comparison',
        'title' => 'Interval Comparison Ear Training — Free Online Exercise',
        'description' => 'Compare two intervals by ear and pick the larger one. Free interval-comparison exercise that sharpens your sense of relative pitch distance.',
    ],
    'interval-construction-practice' => [
        'name' => 'Interval Construction',
        'title' => 'Interval Construction — Free Music Theory Exercise',
        'description' => 'Build intervals on the staff from a given root note and direction. Free interval-construction exercise combining ear training with music-theory notation.',
    ],
    'chord-practice' => [
        'name' => 'Chord Recognition',
        'title' => 'Chord Recognition Ear Training — Free Online Exercise',
        'description' => 'Recognise chord types by ear — major, minor, diminished, augmented, seventh chords and inversions. Free chord-recognition exercise with staff notation and instant feedback.',
    ],
    'scale-practice' => [
        'name' => 'Scale & Mode Recognition',
        'title' => 'Scale & Mode Recognition — Free Ear Training Exercise',
        'description' => 'Identify scales and modes by ear — major, natural/harmonic/melodic minor, and the church modes. Free scale-recognition exercise with ascending and descending playback.',
    ],
    'rhythm-practice' => [
        'name' => 'Rhythm Training',
        'title' => 'Rhythm Training — Free Online Rhythmic Dictation Exercise',
        'description' => 'Listen to a rhythm and choose the notation that matches. Free rhythm-training exercise across multiple time signatures and note values, with instant feedback.',
    ],
    'melodic-dictation' => [
        'name' => 'Melodic Dictation',
        'title' => 'Melodic Dictation — Free Online Ear Training Exercise',
        'description' => 'Transcribe short tonal melodies by ear onto the staff. Free melodic-dictation exercise with rhythm, adjustable keys, and difficulty levels for every stage.',
    ],
];
