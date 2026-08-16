{{--
    Canonical chord/scale name -> localised label, for inline <script> blocks.

    The practice scripts build feedback text ("Correct answer: …") from the
    canonical name carried in data-target / data-answer, where Blade's
    music_label() is not available. This partial publishes the same map the
    markup uses, keyed lower-case because those data attributes are lower-cased.

    Include it once per screen, before the script that uses musicLabel().

        musicLabel('dominant 7th', 'chord')   -> "Dominantseptakkord"
        musicLabel('aeolian', 'scale')        -> "Äolisch"

    An unlisted name is returned unchanged, so a newly added type shows up in
    its canonical English spelling instead of an empty string.
--}}
@php
    $musicLabelPayload = [
        'chord' => array_change_key_case(music_label_map('chord'), CASE_LOWER),
        'scale' => array_change_key_case(music_label_map('scale'), CASE_LOWER),
    ];
@endphp
<script>
    window.musicLabels = Object.assign(window.musicLabels || {}, @json($musicLabelPayload));
    window.musicLabel = function (canonical, kind) {
        if (!canonical) return canonical;
        var map = window.musicLabels[kind || 'chord'] || {};
        var key = String(canonical).toLowerCase();
        return Object.prototype.hasOwnProperty.call(map, key) ? map[key] : canonical;
    };
</script>
