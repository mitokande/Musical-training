{{--
    Numbered H2 for a blog article — the chapter chip pattern from
    /ear-training-guide, so the two pages scan the same way.

    Params: $n (chapter number, null for an unnumbered closing section),
            $id (anchor, must match the `toc` order in config('blog.posts')),
            $key (lang key relative to the post's section)
--}}
<h2 @isset($id) id="{{ $id }}" @endisset class="hv-h2">
    @isset($n)
        <span class="hv-h2-num" aria-hidden="true">{{ $n }}</span>
    @endisset
    <span>{{ $t($key) }}</span>
</h2>
