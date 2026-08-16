<?php

/*
 * Blog posts (resources/views/blog/posts/*.blade.php, served at /blog/{slug}).
 *
 * One top-level key per post, matching the `section` of its config('blog.posts')
 * entry. English is the source of truth; a locale that mirrors a post's keys
 * turns /{locale}/blog/{slug} on by itself — locale_page_translated() measures
 * that locale's coverage of this exact section, and until it clears the
 * threshold the localized URL canonicalises to English and stays out of both
 * the hreflang set and the sitemap.
 *
 * So: to translate a post, copy the whole section into
 * resources/lang/{locale}/blog.php and translate the values. Do not rename,
 * reorder or drop keys — the blade addresses them by name, and a missing key
 * counts against the coverage that gates the URL.
 *
 * Shared chrome (byline, author box, exercise widget) lives under 'ui', so it
 * is translated once for every post rather than per post.
 */

return [

    'ui' => [
        'by' => 'By',
        'published' => 'Published',
        'updated' => 'Updated',
        'read_time' => ':min min read',
        'toc_title' => 'In this article',
        'toc_subtitle' => ':count sections — jump to any of them',
        'takeaway' => 'Key takeaway',
        'back_to_blog' => 'All articles',
        'author_box_title' => 'About the author',
        'author_view_profile' => 'View profile',
        'author_lessons' => 'Book a lesson',

        /*
         * Canonical interval name -> the label a reader sees in the exercise
         * boxes. Same rule as music_label() for chords and scales: the value
         * the generator produces and the answer is compared against never
         * changes, only what is printed. A locale that omits a name simply
         * shows its canonical English spelling.
         */
        'intervals' => [
            'Perfect Unison' => 'Perfect Unison',
            'Minor 2nd' => 'Minor 2nd',
            'Major 2nd' => 'Major 2nd',
            'Minor 3rd' => 'Minor 3rd',
            'Major 3rd' => 'Major 3rd',
            'Perfect 4th' => 'Perfect 4th',
            'Tritone' => 'Tritone',
            'Perfect 5th' => 'Perfect 5th',
            'Minor 6th' => 'Minor 6th',
            'Major 6th' => 'Major 6th',
            'Minor 7th' => 'Minor 7th',
            'Major 7th' => 'Major 7th',
            'Perfect Octave' => 'Perfect Octave',
        ],

        'exercise' => [
            'badge' => 'Try it now',
            'melodic_title' => 'Melodic interval — 5 questions',
            'melodic_hint' => 'Two notes, one after another. Which interval is it?',
            'harmonic_title' => 'Harmonic interval — 5 questions',
            'harmonic_hint' => 'Two notes at the same time. Which interval is it?',
            'play' => 'Play interval',
            'replay' => 'Play again',
            'playing' => 'Playing…',
            'play_prompt' => 'Press play to hear the interval.',
            'answer_prompt' => 'Which interval did you hear?',
            'replay_prompt' => 'Play it again to compare.',
            'question' => 'Question :current of :total',
            'correct' => 'Correct!',
            'incorrect' => 'Not quite — it was :answer',
            'next' => 'Next question',
            'score' => 'You scored :correct / :total',
            'gate_title' => 'That was the last free question.',
            'gate_body' => 'Create a free Harmoniva account to keep going — full interval training, adaptive AI exercises, and a Learning Path that follows your progress.',
            'gate_button' => 'Sign up free',
            'gate_secondary' => 'Explore the exercises',
            'loading' => 'Loading piano sound…',
            'audio_error' => 'Audio could not start. Check your sound and try again.',
        ],
    ],

    'music_intervals' => [

        // ── Head / card ──────────────────────────────────────────────────────
        'meta_title' => 'Music Intervals Explained: A Complete Beginner’s Guide',
        'meta_description' => 'Learn what music intervals are, how to identify and count them, and how melodic, harmonic, major, minor, perfect, and compound intervals work.',
        'title' => 'Music Intervals Explained: A Complete Beginner’s Guide',
        'hero_image_alt' => 'Musical intervals shown on piano keys and standard music notation',
        // Byline text for the About-the-author box. Lives with the post rather
        // than on the profile so each translation reads naturally in its own
        // language instead of pulling untranslated profile copy.
        'author_bio' => 'Tuba Günvar is a piano and music educator with 30 years of experience. A graduate of DEU İzmir State Conservatory, she holds a master’s degree in Music Education from Marmara University. She teaches piano, solfège, harmony, and music theory, prepares students for conservatory, ABRSM, and LCM examinations, and currently offers online lessons from Málaga to students worldwide.',

        // ── Table of contents ────────────────────────────────────────────────
        'toc_what' => 'What is an interval?',
        'toc_number_quality' => 'Number and quality',
        'toc_main' => 'The main intervals',
        'toc_melodic_harmonic' => 'Melodic and harmonic',
        'toc_compound' => 'Simple and compound',
        'toc_consonance' => 'Consonance and dissonance',
        'toc_inversion' => 'Interval inversion',
        'toc_why' => 'Why intervals matter',
        'toc_staff' => 'Identifying on the staff',
        'toc_ear' => 'Recognizing by ear',
        'toc_harmoniva' => 'Practising with Harmoniva',
        'toc_faq' => 'Frequently asked questions',

        /*
         * ── Diagram labels ──────────────────────────────────────────────────
         * Text drawn inside the inline-SVG illustrations under
         * resources/views/blog/figures/. Keep them short: they sit in fixed-width
         * pills and captions, and a label two or three times longer than the
         * English will overflow its shape. Note names (C, E4), interval
         * abbreviations (m3, P5) and numbers are notation and stay as they are
         * in every language — they are not in this list on purpose.
         * fig_*_alt is the accessible description read instead of the drawing.
         */
        /*
         * Note names drawn inside the diagrams. Letter names in English; a
         * locale whose readers learn solfège (Do, Re, Mi …) overrides them here
         * so the drawings speak the same language as the prose around them.
         */
        'fig_note_c' => 'C',
        'fig_note_d' => 'D',
        'fig_note_e' => 'E',
        'fig_note_c4' => 'C4',
        'fig_note_e4' => 'E4',
        'fig_note_e5' => 'E5',

        'fig_staff_interval_alt' => 'A treble staff showing C and E, a major third apart',
        'fig_staff_label' => 'Major 3rd · 4 semitones',
        'fig_staff_interval_caption' => 'C and E span three letter names — C, D, E — and four semitones. That makes it a major third.',

        'fig_counting_alt' => 'Counting the letter names C, D and E as 1, 2, 3',
        'fig_count_label' => 'C to E = a 3rd',
        'fig_counting_caption' => 'Count the starting note, every letter name in between, and the final note. Three names means a third.',

        'fig_quality_alt' => 'Four thirds of increasing size: diminished, minor, major, augmented',
        'fig_quality_title' => 'Same number, four different qualities',
        'fig_quality_dim' => 'Diminished 3rd',
        'fig_quality_min' => 'Minor 3rd',
        'fig_quality_maj' => 'Major 3rd',
        'fig_quality_aug' => 'Augmented 3rd',
        'fig_quality_unit' => '2 semitones',
        'fig_quality_caption' => 'Every one of these is a third. The quality tells you which third — each step adds one semitone.',

        'fig_semitone_ruler_alt' => 'A ruler of the thirteen intervals from unison to octave by semitone count',
        'fig_ruler_caption' => 'semitones above the starting note',
        'fig_ruler_figcaption' => 'The same thirteen intervals as the table above, laid out by size. The tritone sits exactly halfway.',

        'fig_melodic_harmonic_alt' => 'Two staves: notes played one after another, and notes played together',
        'fig_mh_melodic' => 'MELODIC',
        'fig_mh_harmonic' => 'HARMONIC',
        'fig_mh_seq' => 'one after the other',
        'fig_mh_sim' => 'at the same time',
        'fig_melodic_harmonic_caption' => 'Same two notes, same distance — but a melodic interval unfolds in time, while a harmonic interval arrives all at once.',

        'fig_compound_alt' => 'A third and a tenth drawn from the same starting note',
        'fig_comp_simple' => '3rd · simple',
        'fig_comp_compound' => '10th · compound',
        'fig_compound_caption' => 'A tenth is a third stretched by an octave. Subtract 7 from a compound interval to find its simple equivalent.',

        'fig_contour_alt' => 'A melodic line moving by steps, then by leaps',
        'fig_contour_steps' => 'STEPS',
        'fig_contour_leaps' => 'LEAPS',
        'fig_contour_smooth' => 'smooth, connected',
        'fig_contour_open' => 'open, dramatic',
        'fig_contour_caption' => 'Seconds walk; anything wider jumps. Most melodies mix the two, and the balance is a large part of their character.',

        'fig_spectrum_alt' => 'Intervals arranged from consonant to dissonant',
        'fig_spectrum_cons' => 'CONSONANT',
        'fig_spectrum_diss' => 'DISSONANT',
        'fig_spectrum_note' => 'a tendency, not a rule — context decides',
        'fig_spectrum_caption' => 'Roughly how these intervals are heard in Western tonal music. The perfect fourth deliberately sits in the middle.',

        'fig_inversion_alt' => 'A major third inverting into a minor sixth',
        'fig_inv_from' => 'Major 3rd',
        'fig_inv_to' => 'Minor 6th',
        'fig_inv_low' => 'C below E',
        'fig_inv_high' => 'C above E',
        'fig_inv_note' => 'the C moves up an octave · 3 + 6 = 9',
        'fig_inversion_caption' => 'Move the lower note up an octave and the interval inverts: the numbers add to 9, and major becomes minor.',

        'fig_shape_2nd' => '2nd',
        'fig_shape_3rd' => '3rd',
        'fig_shape_5th' => '5th',
        'fig_staff_shapes_alt' => 'Three interval shapes on a staff: a second, a third and a fifth',
        'fig_shapes_caption' => 'line to the next space, line to line, line to two lines up',
        'fig_staff_shapes_figcaption' => 'Reading by shape rather than by note: once these distances are familiar, you stop decoding one note at a time.',

        // ── Key takeaways ────────────────────────────────────────────────────
        'take_what' => 'An interval is a distance between two notes — one you can both see on the staff and hear. Learning to do both is what interval study actually is.',
        'take_nq' => 'A complete interval name always has two parts. The number comes from the letter names; the quality comes from the semitones.',
        'take_mh' => 'Melodic and harmonic intervals are the same distances heard in two different ways, and they are two different listening skills.',
        'take_cons' => 'Consonance and dissonance are not good and bad. Tension is one of the main things that makes music move.',
        'take_staff' => 'Count the letters first, then check the semitones. Spelling decides the name, even when two intervals sound identical.',
        'take_ear' => 'Train in contrasting pairs, add direction and harmony gradually, and aim to hear the interval itself rather than a reference song.',

        // ── Intro ────────────────────────────────────────────────────────────
        'intro_1' => 'Music is built on relationships between notes. In music theory, we call these relationships intervals.',
        'intro_2' => 'Learning intervals gives musicians a practical way to understand how far apart two notes are, how they sound in relation to one another, and why certain combinations can feel stable, bright, dark, open, tense, or unresolved.',
        'intro_3' => 'Intervals also sit at the heart of ear training because they connect several skills that musicians often study separately: reading notation, listening, singing, understanding harmony, and playing an instrument.',
        'intro_4' => 'In this guide, we will look closely at what musical intervals are, how they are named, the main types of intervals, why they matter in music education, and how you can learn to recognize them both on the staff and by ear.',

        // ── 1. What is an interval? ──────────────────────────────────────────
        'what_h' => 'What Is an Interval in Music?',
        'what_p1' => 'An interval is the distance between two notes.',
        'what_example_label' => 'For example:',
        'what_ex_1' => 'C to D is a second.',
        'what_ex_2' => 'C to E is a third.',
        'what_ex_3' => 'C to G is a fifth.',
        'what_p2' => 'We can think about intervals in two main ways:',
        'what_visual_label' => 'Visually',
        'what_visual' => 'the distance between two notes written on the staff.',
        'what_aural_label' => 'Aurally',
        'what_aural' => 'the perceived pitch distance between two sounds.',
        'what_p3' => 'Both approaches are important. In some cases, an interval that sounds the same can be written and named in two different ways on the staff. This is one of the reasons interval study involves more than simply counting piano keys or semitones.',

        // ── 2. Number and quality ────────────────────────────────────────────
        'nq_h' => 'Interval Number and Interval Quality',
        'nq_p1' => 'The full name of an interval has two parts: its number and its quality.',

        'nq_number_h' => 'Interval Number',
        'nq_number_p1' => 'The interval number tells us the numerical distance between two note names.',
        'nq_number_p2' => 'When counting an interval, we include the starting note, all note names in between, and the final note.',
        'nq_number_p3' => 'For example, to find the interval from C to E, we count:',
        'nq_number_count' => 'C (1), D (2), E (3)',
        'nq_number_p4' => 'So C to E is a third.',
        'nq_number_p5' => 'Sharps and flats do not change the interval number. C to E, C to E-flat, and C-sharp to E-sharp are all some type of third because each pair spans the letter names C, D, and E.',
        'nq_number_p6' => 'The accidentals affect the quality of the interval, not its number.',

        'nq_quality_h' => 'Interval Quality',
        'nq_quality_p1' => 'Interval quality describes the exact size and type of an interval.',
        'nq_quality_p2' => 'The five standard interval qualities are:',
        'nq_quality_1' => 'Perfect',
        'nq_quality_2' => 'Major',
        'nq_quality_3' => 'Minor',
        'nq_quality_4' => 'Augmented',
        'nq_quality_5' => 'Diminished',
        'nq_quality_p3' => 'Unisons, fourths, fifths, and octaves belong to the perfect family. Seconds, thirds, sixths, and sevenths belong to the major and minor family.',
        'nq_quality_p4' => 'A minor interval is one semitone smaller than the corresponding major interval. For example, C to E is a major third, while C to E-flat is a minor third.',
        'nq_quality_p5' => 'An augmented interval is one semitone larger than the corresponding perfect or major interval. C to E-sharp, for example, is an augmented third.',
        'nq_quality_p6' => 'A diminished interval is one semitone smaller than the corresponding perfect or minor interval. D to F is a minor third, while D to F-flat is a diminished third.',
        'nq_quality_p7' => 'Understanding both number and quality is essential. Saying that two notes form a “third” tells you part of the story; identifying that third as major, minor, augmented, or diminished gives you the complete theoretical name.',

        // ── 3. The main intervals within an octave ───────────────────────────
        'main_h' => 'The Main Intervals Within an Octave',
        'main_p1' => 'In twelve-tone equal temperament, an octave is divided into 12 equal semitones.',
        'main_p2' => 'The following table shows the most commonly studied intervals within an octave and the number of semitones each contains:',

        'table_caption' => 'The intervals within one octave, with their abbreviations and semitone counts',
        'table_interval' => 'Interval',
        'table_abbrev' => 'Abbreviation',
        'table_semitones' => 'Semitones',
        'table_example' => 'Example',

        'iv_p1' => 'Perfect unison',
        'iv_m2' => 'Minor second',
        'iv_M2' => 'Major second',
        'iv_m3' => 'Minor third',
        'iv_M3' => 'Major third',
        'iv_p4' => 'Perfect fourth',
        'iv_tt' => 'Tritone',
        // The only abbreviation carrying a translatable word; the rest (P1, m2,
        // M2 …) are notation and stay in the blade.
        'abbr_tt' => 'A4 or d5',
        'iv_p5' => 'Perfect fifth',
        'iv_m6' => 'Minor sixth',
        'iv_M6' => 'Major sixth',
        'iv_m7' => 'Minor seventh',
        'iv_M7' => 'Major seventh',
        'iv_p8' => 'Perfect octave',

        'ex_p1' => 'C to C',
        'ex_m2' => 'C to D-flat',
        'ex_M2' => 'C to D',
        'ex_m3' => 'C to E-flat',
        'ex_M3' => 'C to E',
        'ex_p4' => 'C to F',
        'ex_tt' => 'C to F-sharp or C to G-flat',
        'ex_p5' => 'C to G',
        'ex_m6' => 'C to A-flat',
        'ex_M6' => 'C to A',
        'ex_m7' => 'C to B-flat',
        'ex_M7' => 'C to B',
        'ex_p8' => 'C to the next C',

        'main_p3' => 'The tritone is especially interesting because it divides the octave into two equal halves. Depending on how the notes are spelled and how the interval functions harmonically, a tritone may be written as an augmented fourth or a diminished fifth.',
        'main_example_label' => 'For example:',
        'main_tt_1' => 'C to F-sharp is an augmented fourth.',
        'main_tt_2' => 'C to G-flat is a diminished fifth.',
        'main_p4' => 'On an equal-tempered instrument such as a modern piano, these two intervals sound the same. They are therefore enharmonically equivalent, even though their notation and theoretical meaning are different.',

        // ── 4. Melodic and harmonic ──────────────────────────────────────────
        'mh_h' => 'Melodic and Harmonic Intervals',
        'mh_p1' => 'Intervals can also be classified according to how the two notes are played.',

        'mh_mel_h' => 'Melodic Intervals',
        'mh_mel_p1' => 'A melodic interval occurs when two notes are heard one after another.',
        'mh_mel_p2' => 'A melodic interval may be:',
        'mh_mel_1' => 'Ascending',
        'mh_mel_2' => 'Descending',
        'mh_mel_3' => 'Unison, or the same pitch',
        'mh_mel_p3' => 'The first note is always the starting note. If the first note is lower in pitch than the second note, the interval is ascending. If the first note is higher in pitch than the second note, the interval is descending. If both notes have exactly the same pitch, the result is a unison.',
        'mh_mel_p4' => 'For example, moving from C4 to G4 creates an ascending perfect fifth. Moving from G4 to C4 creates a descending perfect fifth.',
        'mh_mel_p5' => 'This distinction becomes particularly important in ear training. Recognizing the size of an interval and recognizing its direction are related skills, but they are not exactly the same task.',

        'mh_har_h' => 'Harmonic Intervals',
        'mh_har_p1' => 'A harmonic interval occurs when two notes are sounded at the same time.',
        'mh_har_p2' => 'Harmonic intervals are among the basic building blocks of harmony. They play an essential role in chords, accompaniment, counterpoint, voicing, and multi-part music.',
        'mh_har_p3' => 'A melodic major third and a harmonic major third contain the same pitch distance. However, recognizing them by ear requires somewhat different listening skills.',
        'mh_har_p4' => 'With a melodic interval, you can follow the movement from the first note to the second. With a harmonic interval, both pitches arrive together. You need to hear the combined sonority while learning to distinguish the two individual pitches within it.',
        'mh_har_p5' => 'For many students, harmonic interval recognition therefore develops more gradually than basic melodic interval recognition.',

        // ── 5. Simple and compound ───────────────────────────────────────────
        'comp_h' => 'Simple and Compound Intervals',
        'comp_p1' => 'Intervals from the unison through the octave are called simple intervals. Intervals larger than an octave are known as compound intervals.',
        'comp_example_label' => 'For example:',
        'comp_1' => 'A ninth combines an octave and a second.',
        'comp_2' => 'A tenth combines an octave and a third.',
        'comp_3' => 'An eleventh combines an octave and a fourth.',
        'comp_4' => 'A twelfth combines an octave and a fifth.',
        'comp_p2' => 'For the most common compound intervals, you can find the corresponding simple interval by subtracting 7 from the interval number.',
        'comp_p3' => 'For example, the simple equivalent of a tenth is a third:',
        'comp_math' => '10 − 7 = 3',
        'comp_p4' => 'So a major tenth has the same basic interval quality as a major third, extended by an octave.',
        'comp_p5' => 'Compound intervals appear frequently in chord voicings, orchestration, jazz harmony, arranging, piano textures, and music written across a wide register.',

        // ── 6. Steps, leaps, consonance, dissonance ──────────────────────────
        'cons_h' => 'Steps, Leaps, Consonance, and Dissonance',
        'cons_p1' => 'When a melody moves to a neighboring scale degree, the movement is usually described as a step. In most cases, this involves some type of second.',
        'cons_p2' => 'A melodic movement of a third or larger is generally considered a leap or skip.',
        'cons_p3' => 'Stepwise motion often gives a melody a smooth and connected character. Larger leaps can add openness, emphasis, dramatic contrast, or a strong change of direction.',
        'cons_p4' => 'Intervals can also be discussed in terms of consonance and dissonance. In many contexts within Western tonal music, the following intervals are considered relatively consonant:',
        'cons_1' => 'Perfect unison',
        'cons_2' => 'Perfect octave',
        'cons_3' => 'Perfect fifth',
        'cons_4' => 'Major and minor thirds',
        'cons_5' => 'Major and minor sixths',
        'cons_p5' => 'Seconds, sevenths, and the tritone are generally perceived as more dissonant or tense.',
        'cons_p6' => 'The perfect fourth is more context-dependent. In some situations it behaves as a consonance; in others it can function as a tension that seeks resolution.',
        'cons_p7' => 'Consonance and dissonance should not simply be understood as “pleasant” and “unpleasant.” The way an interval is perceived depends on musical style, voicing, register, tuning system, rhythm, timbre, harmonic context, and cultural familiarity.',
        'cons_p8' => 'A dissonant interval is not necessarily something to avoid. In fact, tension and resolution are among the most important tools composers use to create movement and expression.',

        // ── 7. Inversion ─────────────────────────────────────────────────────
        'inv_h' => 'What Is Interval Inversion?',
        'inv_p1' => 'An interval inversion occurs when one of the notes in an interval is moved by an octave so that the lower note becomes the upper note, or the upper note becomes the lower note.',
        'inv_p2' => 'The numbers of an interval and its inversion always add up to 9:',
        'inv_n1' => 'Unison and octave',
        'inv_n2' => 'Second and seventh',
        'inv_n3' => 'Third and sixth',
        'inv_n4' => 'Fourth and fifth',
        'inv_p3' => 'Interval qualities also follow a predictable pattern when inverted:',
        'inv_q1' => 'Perfect stays perfect.',
        'inv_q2' => 'Major becomes minor.',
        'inv_q3' => 'Minor becomes major.',
        'inv_q4' => 'Augmented becomes diminished.',
        'inv_q5' => 'Diminished becomes augmented.',
        'inv_p4' => 'For example, the inversion of a major third is a minor sixth. The inversion of a perfect fourth is a perfect fifth.',
        'inv_p5' => 'Understanding interval inversions makes it much easier to understand chord inversions, different voicings, melodic transformation, counterpoint, and the relationship between different interval pairs.',

        // ── 8. Why intervals matter ──────────────────────────────────────────
        'why_h' => 'Why Are Intervals Important in Music?',
        'why_p1' => 'Intervals connect many musical skills that beginners often encounter as separate subjects.',

        'why_read_h' => 'Music Reading',
        'why_read_p1' => 'Learning to recognize the visual shape of thirds, fifths, octaves, and other intervals on the staff can make music reading considerably faster.',
        'why_read_p2' => 'Instead of identifying every note individually, an experienced reader begins to recognize patterns and distances.',
        'why_read_p3' => 'For example, two notes on adjacent staff lines form a third. A note on one line and another note two lines above it form a fifth.',
        'why_read_p4' => 'Once these visual patterns become familiar, sight-reading becomes less dependent on decoding notes one at a time.',

        'why_ear_h' => 'Ear Training',
        'why_ear_p1' => 'Recognizing intervals by ear develops your ability to hear relationships between pitches. This can support skills such as:',
        'why_ear_1' => 'Melodic dictation',
        'why_ear_2' => 'Chord recognition',
        'why_ear_3' => 'Intonation',
        'why_ear_4' => 'Transcription',
        'why_ear_5' => 'Playing by ear',
        'why_ear_6' => 'Improvisation',
        'why_ear_7' => 'Listening to polyphonic music',
        'why_ear_p2' => 'Interval training is particularly useful because it bridges theoretical knowledge and actual sound. A major third stops being only a term in a textbook and becomes a sound you can recognize, sing, and reproduce on your instrument.',

        'why_sing_h' => 'Singing and Intonation',
        'why_sing_p1' => 'Singers rely on interval awareness to perform melodic leaps accurately and maintain their position within a harmony.',
        'why_sing_p2' => 'Rather than thinking only about the destination note, a singer can learn to hear and anticipate the interval between the starting pitch and the target pitch.',
        'why_sing_p3' => 'This is especially useful when a melody includes larger leaps or when the singer must enter without instrumental reinforcement.',

        'why_inst_h' => 'Instrumental Technique',
        'why_inst_p1' => 'Instrumentalists can connect the sound of an interval with a physical pattern on their instrument. For example:',
        'why_inst_1' => 'Pianists can associate intervals with distances across the keyboard.',
        'why_inst_2' => 'Guitarists can connect them with fret and string patterns.',
        'why_inst_3' => 'String players can relate them to finger positions.',
        'why_inst_4' => 'Wind players can associate them with fingering patterns, embouchure, and breath support.',
        'why_inst_p2' => 'As these relationships become familiar, theoretical interval knowledge begins to support practical playing.',

        'why_harm_h' => 'Harmony',
        'why_harm_p1' => 'Triads and seventh chords are built from intervals. Consider a major triad. From the root:',
        'why_harm_1' => 'The third is a major third above the root.',
        'why_harm_2' => 'The fifth is a perfect fifth above the root.',
        'why_harm_p2' => 'In a minor triad, the interval between the root and third is a minor third.',
        'why_harm_p3' => 'This means that learning chord construction is, to a large extent, learning how intervals work together.',
        'why_harm_p4' => 'The same principle becomes increasingly important when studying seventh chords, extensions, altered chords, voice leading, and more advanced harmony.',

        'why_comp_h' => 'Composition and Improvisation',
        'why_comp_p1' => 'Intervals help shape the identity of a melody. Changing a single interval can alter the character of a phrase, its emotional effect, or even the harmony it suggests.',
        'why_comp_p2' => 'Composers and improvisers use interval choices deliberately. Stepwise movement can create one type of phrase, while a wide sixth, seventh, or octave leap may immediately produce a more distinctive gesture.',
        'why_comp_p3' => 'Interval awareness therefore becomes useful not only for analysing existing music but also for creating it.',

        // ── 9. Identifying on the staff ──────────────────────────────────────
        'staff_h' => 'How Do You Identify an Interval on the Staff?',
        'staff_p1' => 'A practical way to identify an interval is to use three steps:',
        'staff_1' => 'Count the note names to determine the interval number.',
        'staff_2' => 'Treat the lower note as your starting point and compare the upper note with the major scale built on that starting note.',
        'staff_3' => 'Take any sharps or flats into account to determine the interval quality.',
        'staff_p2' => 'Consider F to C. Count the note names:',
        'staff_count' => 'F, G, A, B, C',
        'staff_p3' => 'There are five letter names, so the interval is a fifth. C belongs to the F major scale, which means F to C is a perfect fifth.',
        'staff_p4' => 'Now consider F to C-sharp. The letter names still span a fifth, so the interval number has not changed. However, C-sharp makes the interval one semitone larger than a perfect fifth. The result is an augmented fifth.',
        'staff_p5' => 'Counting semitones can be extremely useful for checking your answer, but semitone counting alone is not enough. Correct interval naming also depends on the written note names.',
        'staff_p6' => 'For example, C to D-sharp and C to E-flat are enharmonically equivalent on a piano. You would press the same keys to play them. But theoretically:',
        'staff_enh_1' => 'C to D-sharp is an augmented second.',
        'staff_enh_2' => 'C to E-flat is a minor third.',
        'staff_p7' => 'They sound the same in equal temperament, but they are spelled differently and can serve different musical functions.',
        'staff_p8' => 'This is why learning intervals properly involves both sound and notation.',

        // ── 10. Recognizing by ear ───────────────────────────────────────────
        'ear_h' => 'How Can You Recognize Intervals by Ear?',
        'ear_p1' => 'Trying to memorize every interval at once is rarely the most effective approach.',
        'ear_p2' => 'Start with interval pairs that are relatively easy to distinguish:',
        'ear_easy_1' => 'Unison and octave',
        'ear_easy_2' => 'Minor second and major second',
        'ear_easy_3' => 'Minor third and major third',
        'ear_easy_4' => 'Perfect fourth and perfect fifth',
        'ear_p3' => 'Once these become more secure, move on to closer comparisons:',
        'ear_hard_1' => 'Minor sixth and major sixth',
        'ear_hard_2' => 'Minor seventh and major seventh',
        'ear_hard_3' => 'Perfect fourth, tritone, and perfect fifth',
        'ear_p4' => 'For beginners, ascending melodic intervals may be easier to approach first. Descending melodic intervals and harmonic intervals can then be added gradually.',
        'ear_p5' => 'At a more advanced stage, practice intervals:',
        'ear_adv_1' => 'From different starting notes',
        'ear_adv_2' => 'In different octaves',
        'ear_adv_3' => 'With different instrument sounds and timbres',
        'ear_adv_4' => 'In both ascending and descending directions',
        'ear_adv_5' => 'As both melodic and harmonic intervals',
        'ear_adv_6' => 'In mixed exercises where the interval type is not predictable',
        'ear_p6' => 'Some students use the opening notes of familiar songs as references for intervals. This can be helpful when you are starting out. However, it has limitations.',
        'ear_p7' => 'The same interval may feel surprisingly different when it descends instead of ascends, begins on a different scale degree, appears in another octave, or is played with a different timbre.',
        'ear_p8' => 'For that reason, the long-term goal should be to recognize the interval directly rather than having to compare it with a memorized song every time.',
        'ear_p9' => 'Singing intervals can also strengthen the learning process. Try this simple sequence:',
        'ear_step_1' => 'Listen to the interval.',
        'ear_step_2' => 'Sing the pitches back.',
        'ear_step_3' => 'Identify the interval.',
        'ear_step_4' => 'Check your answer on an instrument.',
        'ear_p10' => 'This creates a useful connection between hearing, memory, vocal production, notation, and the physical experience of playing an instrument.',

        // ── 11. Practising with Harmoniva ────────────────────────────────────
        'hv_h' => 'Practising Music Intervals with Harmoniva',
        'hv_p1' => 'Harmoniva does not treat interval training as a single exercise where the only task is to name an interval. Instead, interval training is divided into several related skills that can be developed progressively.',
        'hv_p2' => 'Beginners can start with:',
        'hv_start_1' => 'Unisons and octaves',
        'hv_start_2' => 'Major and minor seconds',
        'hv_start_3' => 'Major and minor thirds',
        'hv_start_4' => 'Perfect fourths and perfect fifths',
        'hv_start_5' => 'Sixths',
        'hv_start_6' => 'Sevenths',
        'hv_start_7' => 'The tritone',

        'hv_lp_h' => 'Interval Learning Path',
        'hv_lp_p1' => 'Harmoniva’s Interval Learning Path separates interval study into several skill areas:',
        'hv_lp_1' => 'Melodic interval recognition',
        'hv_lp_2' => 'Interval direction',
        'hv_lp_3' => 'Harmonic interval recognition',
        'hv_lp_4' => 'Interval construction',
        'hv_lp_5' => 'Interval comparison',
        'hv_lp_p2' => 'These skills support one another, but they are not identical. Being able to name an interval does not automatically mean you can identify whether it is ascending or descending. Likewise, recognizing an interval by ear is different from being asked to construct that interval correctly from a given starting note.',
        'hv_lp_p3' => 'Interval comparison adds another useful layer. Instead of simply naming a sound, you learn to judge differences in interval size and distinguish closely related options.',
        'hv_lp_p4' => 'This allows interval training to progress from basic recognition toward a more complete understanding of pitch relationships.',

        'hv_studio_h' => 'Exercise Setup Studio',
        'hv_studio_p1' => 'Harmoniva’s Exercise Setup Studio allows learners to create more focused interval practice. Depending on the exercise, users can work with:',
        'hv_studio_1' => 'Melodic intervals',
        'hv_studio_2' => 'Harmonic intervals',
        'hv_studio_3' => 'Interval construction',
        'hv_studio_4' => 'Interval comparison',
        'hv_studio_p2' => 'The session can also be customized with options such as:',
        'hv_studio_o1' => 'Ascending, descending, or mixed direction',
        'hv_studio_o2' => 'Selected interval groups',
        'hv_studio_o3' => 'Clef',
        'hv_studio_o4' => 'Difficulty level',
        'hv_studio_o5' => 'Number of questions',
        'hv_studio_p3' => 'This makes it possible to isolate a specific weakness instead of repeatedly practising material that is already secure.',
        'hv_studio_p4' => 'For example, a student may have no trouble distinguishing major and minor thirds melodically but consistently confuse them when the two notes are played together. Instead of returning to a broad beginner lesson, that student can focus specifically on harmonic major and minor thirds.',
        'hv_studio_p5' => 'Focused practice of this kind can make an ear-training session both more efficient and more relevant to the learner’s actual needs.',

        'hv_blitz_h' => 'Interval Blitz',
        'hv_blitz_p1' => 'Interval Blitz takes interval training into a faster, game-based format.',
        'hv_blitz_p2' => 'The earlier stages focus on melodic intervals. Later stages introduce harmonic intervals, followed by mixed challenges that require the listener to switch between different ways of hearing interval relationships.',
        'hv_blitz_p3' => 'This type of practice can be useful once basic recognition is becoming reliable and the goal begins to shift toward faster recall.',

        'hv_routine_h' => 'A 10-Minute Interval Practice Routine',
        'hv_routine_p1' => 'You do not need a long practice session every time you work on intervals. A focused 10-minute session might look like this:',
        'hv_routine_1_time' => '3 min',
        'hv_routine_1_text' => 'Targeted interval comparison',
        'hv_routine_2_time' => '3 min',
        'hv_routine_2_text' => 'Singing or interval construction',
        'hv_routine_3_time' => '4 min',
        'hv_routine_3_text' => 'Mixed interval recognition',
        'hv_routine_p2' => 'For beginners, accuracy should come before speed. Once you can recognize the intervals consistently, you can gradually reduce your response time and make the exercises more challenging.',

        // ── 12. FAQ ──────────────────────────────────────────────────────────
        'faq_h' => 'Frequently Asked Questions About Music Intervals',

        'faq_1_q' => 'How many intervals are there within an octave?',
        'faq_1_a' => 'There are 12 different semitone distances above a starting pitch before the octave repeats. When both the perfect unison and perfect octave are included as separate positions, the standard beginner list contains 13 positions from P1 through P8. Compound intervals and microtonal systems extend beyond this basic framework.',

        'faq_2_q' => 'Does an interval need two notes?',
        'faq_2_a' => 'Yes. An interval describes a relationship between two pitches. A single note does not create an interval unless it is being compared with another pitch or reference note.',

        'faq_3_q' => 'Are intervals the same in every key?',
        'faq_3_a' => 'Yes. The actual notes change, but the interval relationship remains the same. C to E, F to A, and A-flat to C are all major thirds. This consistency is one of the reasons intervals are so useful: once you understand a particular interval, you can recognize and build that relationship from many different starting notes.',

        'faq_4_q' => 'Do I need perfect pitch to recognize intervals?',
        'faq_4_a' => 'No. Interval recognition is primarily a relative pitch skill. The goal is not to hear a note in isolation and know its exact name without a reference. Instead, you learn to recognize the relationship between two pitches. This means interval ear training can be developed even if you do not have absolute or perfect pitch.',

        'faq_5_q' => 'Should I count semitones or use the major scale?',
        'faq_5_a' => 'Both methods are useful, but they serve slightly different purposes. First, use the note names to determine the interval number. Then use the major scale built on the lower note to help determine its quality. Counting semitones is an excellent way to confirm your answer. However, if you want to name an interval correctly, you must also consider the actual note spelling: enharmonically equivalent notes can produce different theoretical interval names even when they sound the same on an equal-tempered instrument.',

        'faq_6_q' => 'What is the easiest interval to recognize by ear?',
        'faq_6_a' => 'Many beginners find the unison, octave, and perfect fifth relatively distinctive. That does not mean they are equally easy for everyone. The difficulty of an interval can change depending on whether it is ascending or descending, register, instrument timbre, whether the notes are melodic or harmonic, and the student’s previous musical experience. A student who plays guitar, for example, may develop different interval associations from a singer or pianist.',

        'faq_7_q' => 'How long does interval ear training take?',
        'faq_7_a' => 'There is no fixed timeline. Progress depends on your practice routine, previous musical experience, listening habits, and the way the exercises are structured. Short, regular, focused sessions are often more sustainable than occasional long practice sessions. For many learners, 10 to 15 minutes of deliberate interval work each day is more useful than one long session once a week. The important point is not simply how much time you spend practising — it is whether you are listening actively, receiving feedback, correcting mistakes, and gradually increasing the difficulty.',

        // ── 13. Final thoughts ───────────────────────────────────────────────
        'final_h' => 'Final Thoughts',
        'final_p1' => 'Intervals are the basic vocabulary of pitch relationships. They explain how melodies move, how harmony and chords are constructed, and how musicians recognize the same musical patterns in different keys.',
        'final_p2' => 'Try not to treat intervals as a collection of theoretical names that must simply be memorized. Instead:',
        'final_1' => 'Read them on the staff.',
        'final_2' => 'Listen to them.',
        'final_3' => 'Sing them.',
        'final_4' => 'Build them on your instrument.',
        'final_5' => 'Compare them with one another.',
        'final_6' => 'Notice them in real music.',
        'final_p3' => 'As interval relationships become familiar, music theory starts to feel less like a collection of abstract rules.',
        'final_p4' => 'Reading becomes faster, listening becomes more precise, chords and melodies become easier to understand, and you begin to hear musical relationships that may previously have passed unnoticed.',
        'final_p5' => 'That is ultimately the value of interval training: not just knowing what a major third or perfect fifth is called, but being able to recognize what those relationships are doing inside the music you hear and play.',

        // ── Closing CTA ──────────────────────────────────────────────────────
        'cta_title' => 'Put this into practice',
        'cta_body' => 'Interval recognition, direction, construction and comparison — as a guided Learning Path that follows your progress.',
        'cta_primary' => 'Start free',
        'cta_secondary' => 'See the Learning Path',
    ],
];
