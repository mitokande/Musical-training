{{--
    JS translation bridge for the practice screens.

    The exercise blades build feedback and button labels inside inline <script>
    blocks, where Blade's __() is not available. This partial publishes the
    app.practice_js dictionary for the current locale and a small pt() reader,
    so those scripts can localise the same way the markup does.

    Include it once per screen, before the script that uses pt().

        pt('playing_chord')                       -> "Akor çalıyor..."
        pt('incorrect_answer_is', {answer: 'M3'}) -> "✗ Yanlış. Doğru cevap: M3."

    Unknown keys return the key itself, so a typo shows up on screen instead of
    silently rendering an empty label.
--}}
<script>
    window.practiceI18n = Object.assign(window.practiceI18n || {}, @json(__('app.practice_js')));
    window.pt = function (key, reps) {
        var s = Object.prototype.hasOwnProperty.call(window.practiceI18n, key)
            ? window.practiceI18n[key]
            : key;
        if (reps) {
            for (var k in reps) {
                if (Object.prototype.hasOwnProperty.call(reps, k)) {
                    s = s.split(':' + k).join(reps[k]);
                }
            }
        }
        return s;
    };
</script>
