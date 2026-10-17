@once
    @push('scripts')
        <script>
            // Copy / share helpers for a live lesson's student link (dashboard card + live lessons page).
            window.liveLessonLink = (url, shareText) => ({
                url,
                copied: false,
                canShare: typeof navigator.share === 'function',
                async copy() {
                    try {
                        await navigator.clipboard.writeText(this.url);
                        this.copied = true;
                        setTimeout(() => (this.copied = false), 2000);
                    } catch (e) {
                        window.prompt(@json(crm_trans('live_lessons.copy_failed')), this.url);
                    }
                },
                share() {
                    navigator.share({ text: shareText.replace(this.url, '').trim(), url: this.url }).catch(() => {});
                },
                whatsapp() {
                    return 'https://wa.me/?text=' + encodeURIComponent(shareText);
                },
            });
        </script>
    @endpush
@endonce
