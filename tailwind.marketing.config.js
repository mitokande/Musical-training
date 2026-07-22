/**
 * Shared Tailwind config for all public (indexable) pages that previously ran
 * the Play CDN: layouts/standalone (marketing, legal, blog), games, public
 * teacher/school profiles + booking, piano studio, and the teachers pricing
 * page. Compiled via resources/css/marketing.css.
 *
 * resources/css/marketing-class-inventory.txt is a snapshot of every class
 * rendered by the live public pages. It feeds the scanner the class names
 * interpolated from PHP (bg-{{ $color }}-100 etc.) that are invisible in the
 * blade source. Regenerate it after adding new dynamic color values:
 * curl each public URL, extract class="..." tokens, one per line.
 */

import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    plugins: [typography],

    content: [
        './resources/views/layouts/standalone.blade.php',
        './resources/views/pages/**/*.blade.php',
        './resources/views/articles/show.blade.php',
        './resources/views/pricing-teachers.blade.php',
        './resources/views/piano-studio.blade.php',
        './resources/views/games/**/*.blade.php',
        './resources/views/teachers/show.blade.php',
        './resources/views/teachers/booking.blade.php',
        './resources/views/teachers/partials/**/*.blade.php',
        './resources/views/partials/**/*.blade.php',
        './resources/css/marketing-class-inventory.txt',
    ],

    // Insurance for future DB-driven values (article categories, instrument
    // colors, …) that are not in the inventory snapshot yet.
    safelist: [
        {
            pattern: /^(bg|text|border|from|to|via)-(slate|gray|zinc|red|orange|amber|yellow|lime|green|emerald|teal|cyan|sky|blue|indigo|violet|purple|fuchsia|pink|rose|primary|accent)-(50|100|200|300|400|500|600|700|800|900)$/,
        },
        {
            pattern: /^(bg|text)-(slate|gray|red|orange|amber|yellow|green|emerald|teal|cyan|sky|blue|indigo|violet|purple|fuchsia|pink|rose|primary|accent)-(400|500|600)\/(10|15|20)$/,
        },
    ],

    theme: {
        extend: {
            // The pages were authored against the Play CDN (v4 engine), which
            // accepts any numeric color-opacity modifier (bg-white/4 …). The
            // compiled v3 engine only honours the opacity scale, so every odd
            // step used across these views must be declared here.
            opacity: {
                3: '0.03', 4: '0.04', 6: '0.06', 8: '0.08', 12: '0.12',
                14: '0.14', 18: '0.18', 35: '0.35', 45: '0.45', 55: '0.55',
                65: '0.65', 85: '0.85',
            },
            fontFamily: {
                sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'],
                serif: ['Instrument Serif', 'Georgia', 'serif'],
            },
            colors: {
                primary: {
                    50: '#faf5ff', 100: '#f3e8ff', 200: '#e9d5ff',
                    300: '#d8b4fe', 400: '#c084fc', 500: '#a855f7',
                    600: '#9333ea', 700: '#7c3aed', 800: '#6b21a8', 900: '#581c87',
                },
                accent: { 400: '#fb923c', 500: '#f97316', 600: '#ea580c' },
                cream: '#FAF7F2',
            },
        },
    },
};
