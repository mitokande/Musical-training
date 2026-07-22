/**
 * Dedicated Tailwind config for the public landing page (welcome.blade.php),
 * compiled via resources/css/landing.css — replaces the former Play CDN setup.
 */

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/views/welcome.blade.php',
        './resources/views/partials/footer.blade.php',
    ],

    // Classes interpolated from PHP arrays in the blade (bg-{{ $s['bg'] }} etc.)
    // are invisible to the scanner and must be safelisted explicitly.
    safelist: [
        'bg-accent-500/15', 'bg-amber-100', 'bg-amber-500/15', 'bg-blue-100', 'bg-blue-500/15',
        'bg-cyan-500/15', 'bg-emerald-500/15', 'bg-green-100', 'bg-green-500/15', 'bg-orange-100',
        'bg-primary-100', 'bg-primary-400/15', 'bg-primary-600/15', 'bg-purple-100', 'bg-purple-500/15',
        'bg-red-100', 'bg-red-500/15', 'bg-rose-100', 'bg-rose-500/15', 'bg-sky-500/15',
        'bg-violet-500/15', 'bg-yellow-500/15',
        'text-accent-400', 'text-amber-400', 'text-amber-600', 'text-amber-700', 'text-blue-400',
        'text-blue-600', 'text-cyan-400', 'text-emerald-400', 'text-green-400', 'text-green-600',
        'text-orange-600', 'text-primary-300', 'text-primary-400', 'text-primary-700', 'text-purple-400',
        'text-purple-600', 'text-red-400', 'text-red-600', 'text-rose-400', 'text-rose-600',
        'text-sky-400', 'text-violet-400', 'text-yellow-400',
        'border-primary-300/50', 'border-primary-400/50', 'border-accent-400/50',
        'from-accent-500', 'to-accent-400', 'from-amber-500', 'to-amber-400',
        'from-blue-500', 'to-blue-400', 'from-green-500', 'to-green-400',
        'from-purple-500', 'to-purple-400', 'from-rose-500', 'to-rose-400',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'],
                serif: ['Instrument Serif', 'Georgia', 'serif'],
            },
            colors: {
                surface: {
                    DEFAULT: '#0C0A10',
                    raised: '#14111C',
                    overlay: '#1C1828',
                    soft: '#18142A',
                },
                primary: {
                    50: '#faf5ff', 100: '#f3e8ff', 200: '#e9d5ff',
                    300: '#d8b4fe', 400: '#c084fc', 500: '#a855f7',
                    600: '#9333ea', 700: '#7c3aed', 800: '#6b21a8', 900: '#581c87',
                },
                accent: { 400: '#fb923c', 500: '#f97316', 600: '#ea580c' },
            },
        },
    },
};
