{{-- Harmoniva responsive notation — the single site-wide standard for VexFlow staves.

     VexFlow renders fixed-pixel SVGs without a viewBox, so untreated they either
     overflow (wide renders) or get CSS-squeezed into unreadability on phones.

     Behaviour:
     - Desktop (>= 640px): unchanged — SVGs shrink to fit their container.
     - Mobile  (<  640px): every notation SVG is displayed at 1.25x its natural
       VexFlow size (~80% taller than the old shrink-to-fit) inside a
       horizontally swipeable container, so the staff/clef/notes stay readable
       and long staves scroll sideways instead of being crushed.
     - Answer-entry staves (#melody-staff-output) auto-scroll to the right end
       as the user adds notes, keeping the newest notes visible.

     Any view that renders notation must @include this partial once. New
     notation containers are covered automatically when their id is in
     NOTATION_IDS below. --}}
<script>
// Site-wide staff layout standard, used by every VexFlow-rendering component:
// - startPad: gap between the clef/signatures and the first note
//   (40px natural; 40-70px displayed across viewports at the 1.25x mobile scale).
// - spacing(n): average px between adjacent notes — 80px max when few notes,
//   tightening to 40px min as the staff fills up. Equal-duration notes space
//   evenly; 4 notes always fit one mobile screen at the 1.25x display scale.
// - span(n): total width to hand to VexFlow's Formatter for n notes.
window.HarmonivaStaff = {
    startPad: 40,
    spacing: function (n) {
        n = Math.max(1, n);
        return Math.max(40, Math.min(80, Math.round(160 / n)));
    },
    span: function (n) {
        return Math.max(1, n) * this.spacing(n);
    }
};

// Site-wide accidental display standard, mirrored in PHP by
// MusicTheoryService::toDisplaySymbol(). Converts internal ASCII note
// spellings (#, b, ##, bb, or the VexFlow 'x' double-sharp alias) into the
// universal Unicode accidental symbols (♯, ♭) for user-facing display.
// Doubled accidentals become doubled symbols (♯♯, ♭♭) — the standard way
// double-sharps/flats are engraved. Trailing octave digits pass through.
window.HarmonivaNotation = {
    toDisplaySymbol: function (note) {
        var s = String(note == null ? '' : note);
        var m = s.match(/^([A-Ga-g])(##|bb|x|#|b)?(.*)$/);
        if (!m) return s.replace(/#/g, '♯').replace(/b/g, '♭');
        var letter = m[1].toUpperCase();
        var acc = { '#': '♯', '##': '♯♯', 'x': '♯♯', 'b': '♭', 'bb': '♭♭' }[(m[2] || '').toLowerCase()] || '';
        return letter + acc + m[3];
    }
};

(function () {
    var MOBILE_BP    = 640;   // Tailwind "sm" breakpoint
    var MOBILE_SCALE = 1.25;  // display scale vs natural VexFlow size on mobile
    var NOTATION_IDS = [
        'output',                    // all classic practice components
        'melody-staff-output',       // melodic dictation answer staff
        'correctMelodyStaffOutput',  // melodic dictation reveal staff
        'rhythm-staff-output',       // rhythm listening/reading staff
        'rhythmTable',               // rhythm builder staff
        'rhythmRevealRow',           // rhythm builder reveal staff
        'notation-output'            // piano studio
    ];
    var SELECTOR = NOTATION_IDS.map(function (id) { return '#' + id + ' svg'; }).join(', ');
    // Answer-option mini staves (rhythm recognition etc.): never enlarged or
    // scrolled — always shrink to fit inside their button, on every viewport.
    var SHRINK_SELECTOR = '.staff-container svg';

    // Nearest self-or-ancestor (max 3 levels) that already scrolls horizontally.
    function findScrollHost(el) {
        var p = el;
        for (var i = 0; i < 3 && p && p !== document.body; i++) {
            var o = window.getComputedStyle(p).overflowX;
            if (o === 'auto' || o === 'scroll') return p;
            p = p.parentElement;
        }
        return null;
    }

    function fitOne(svg) {
        var vb = svg.getAttribute('viewBox'), w, h;
        if (vb) {
            var parts = vb.split(/[\s,]+/);
            w = parseFloat(parts[2]);
            h = parseFloat(parts[3]);
        } else {
            w = parseFloat(svg.getAttribute('width'));
            h = parseFloat(svg.getAttribute('height'));
            if (!w || !h) return;
            svg.setAttribute('viewBox', '0 0 ' + w + ' ' + h);
        }
        if (!w || !h) return;

        var box = svg.parentElement;
        if (!box) return;

        if (window.innerWidth < MOBILE_BP) {
            svg.style.width = Math.round(w * MOBILE_SCALE) + 'px';
            svg.style.maxWidth = 'none';
            svg.style.height = 'auto';
            svg.style.flexShrink = '0';

            var host = findScrollHost(box);
            if (!host) {
                box.style.overflowX = 'auto';
                box.style.overflowY = 'hidden';
                box.style.webkitOverflowScrolling = 'touch';
                host = box;
            }
            if (host === box) {
                // A fixed inline height would clip the taller stave; keep it as a minimum.
                if (box.style.height && box.style.height !== 'auto') {
                    box.style.minHeight = box.style.height;
                    box.style.height = 'auto';
                }
                // Flex centering clips the left edge of overflowing content —
                // left-align while scrolling is needed, restore otherwise.
                if (box.dataset.nfJustify === undefined) {
                    box.dataset.nfJustify = box.style.justifyContent || '';
                }
                box.style.justifyContent =
                    (svg.getBoundingClientRect().width > box.clientWidth + 1)
                        ? 'flex-start'
                        : box.dataset.nfJustify;
            }
            // Answer-entry staff grows as notes are added: keep newest notes visible.
            if (box.id === 'melody-staff-output' && !svg.dataset.nfScrolled) {
                svg.dataset.nfScrolled = '1';
                host.scrollLeft = host.scrollWidth;
            }
        } else {
            svg.style.width = '';
            svg.style.maxWidth = '100%';
            svg.style.height = 'auto';
            svg.style.flexShrink = '';
            if (box.dataset.nfJustify !== undefined) {
                box.style.justifyContent = box.dataset.nfJustify;
            }
        }
    }

    function shrinkOne(svg) {
        if (!svg.getAttribute('viewBox')) {
            var w = parseFloat(svg.getAttribute('width'));
            var h = parseFloat(svg.getAttribute('height'));
            if (!w || !h) return;
            svg.setAttribute('viewBox', '0 0 ' + w + ' ' + h);
        }
        svg.style.maxWidth = '100%';
        svg.style.height = 'auto';
    }

    function fitNotationSvgs() {
        document.querySelectorAll(SELECTOR).forEach(fitOne);
        document.querySelectorAll(SHRINK_SELECTOR).forEach(shrinkOne);
    }

    document.addEventListener('DOMContentLoaded', function () {
        fitNotationSvgs();
        new MutationObserver(fitNotationSvgs).observe(document.body, { childList: true, subtree: true });
        window.addEventListener('resize', fitNotationSvgs);
        window.addEventListener('orientationchange', fitNotationSvgs);
    });
})();
</script>
