<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Blog posts
    |--------------------------------------------------------------------------
    |
    | Single source of truth for every long-form post under /blog/{slug}. The
    | route (routes/web.php), the canonical/hreflang block (PublicPageSeo), the
    | sitemap (SitemapController) and the /blog index card all read from here,
    | so a new post is registered once and cannot drift apart across them.
    |
    | Copy never lives in this file — only structure. Every string a reader sees
    | comes from resources/lang/{locale}/blog.php under the post's `section`,
    | which is what makes a post translatable: adding es/blog.php with the same
    | section keys switches /es/blog/{slug} on by itself (locale_page_translated),
    | and until then that URL canonicalises to English and stays out of the
    | sitemap rather than surfacing English copy at a second address.
    |
    | The array key is the post's permanent identity and doubles as its English
    | slug. Other languages get a readable slug of their own through `slugs`;
    | everything internal keeps addressing the post by its key, so a translated
    | URL is still one post with one hreflang cluster.
    |
    | Invariant, enforced by tests/Feature/BlogPostTest: a locale may appear in
    | `slugs` only once its translation is complete. Otherwise the URL would
    | promise that language while the body fell back to English.
    |
    | Keys per post:
    |   view          blade rendering the body
    |   section       `blog.*` translation section holding the copy
    |   slugs         locale => URL slug; absent locales use the array key
    |   category      one of the /blog filter categories (theory|ear|tips|ai)
    |   icon          lucide icon for the index card
    |   author_slug   TeacherProfile slug — byline + About-the-author box
    |   published_at  ISO date; drives <time> + BlogPosting datePublished
    |   updated_at    ISO date; BlogPosting dateModified
    |   reading_time  minutes, shown on the card and in the hero
    |   toc           lang keys (relative to the section) of the H2s to list
    |   featured_slot optional /blog grid slot this post takes over (a1…a9)
    |   faq_count     number of faq_N_q/faq_N_a pairs, for the FAQPage schema
    |
    */

    'posts' => [

        'music-intervals-guide' => [
            'view' => 'blog.posts.music-intervals-guide',
            'section' => 'music_intervals',
            // ASCII only: the route pattern and every share/analytics tool
            // handle a plain slug better than percent-encoded diacritics.
            'slugs' => [
                'tr' => 'muzikte-araliklar-rehberi',
            ],
            'category' => 'theory',
            'icon' => 'ruler',
            'author_slug' => 'tuba-gunvar',
            'published_at' => '2026-08-10',
            'updated_at' => '2026-08-10',
            'reading_time' => 11,
            'featured_slot' => 'a2',
            // Number of faq_N_q / faq_N_a pairs in the section — drives both the
            // rendered FAQ list and the FAQPage structured data.
            'faq_count' => 7,
            'toc' => [
                'toc_what', 'toc_number_quality', 'toc_main', 'toc_melodic_harmonic',
                'toc_compound', 'toc_consonance', 'toc_inversion', 'toc_why',
                'toc_staff', 'toc_ear', 'toc_harmoniva', 'toc_faq',
            ],
        ],

    ],
];
