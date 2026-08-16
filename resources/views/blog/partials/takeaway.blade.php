{{--
    "Key takeaway" callout — the one sentence a reader should leave a section
    with. Mirrors the callout on /ear-training-guide so the two long-form pages
    read as one family.

    Params: $key (lang key relative to the post's section)
--}}
<aside class="hv-takeaway reveal">
    <span class="hv-takeaway-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 18h6"/><path d="M10 22h4"/>
            <path d="M15.09 14c.18-.98.65-1.74 1.41-2.5A4.65 4.65 0 0 0 18 8 6 6 0 0 0 6 8c0 1 .23 2.23 1.5 3.5A4.61 4.61 0 0 1 8.91 14"/>
        </svg>
    </span>
    <div>
        <p class="hv-takeaway-label">{{ __('blog.ui.takeaway') }}</p>
        <p class="hv-takeaway-text">{{ $t($key) }}</p>
    </div>
</aside>
