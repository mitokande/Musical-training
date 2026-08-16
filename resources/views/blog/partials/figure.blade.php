{{--
    Illustration card for a blog article.

    Params:
      $fig      blade name under blog/figures (drawn as inline SVG)
      $caption  lang key, relative to the post's section, for the line below it
      $tone     purple (default) | orange | green — tints the card frame

    The drawings are inline SVG rather than image files so they inherit the page
    font, stay crisp at any width, cost no extra request, and — the reason that
    matters most here — carry their labels as real text, which means a translated
    post gets translated diagrams for free.
--}}
@php
    $figTone = $tone ?? 'purple';
    $figFrame = [
        'purple' => 'background:linear-gradient(150deg,#ffffff 0%,#faf5ff 100%);border-color:#e9d5ff;',
        'orange' => 'background:linear-gradient(150deg,#ffffff 0%,#fff7ed 100%);border-color:#fed7aa;',
        'green' => 'background:linear-gradient(150deg,#ffffff 0%,#f0fdf4 100%);border-color:#bbf7d0;',
    ][$figTone];
@endphp
<figure class="hv-figure reveal" style="{{ $figFrame }}">
    <div class="hv-figure-art">
        @include('blog.figures.'.$fig)
    </div>
    @isset($caption)
        <figcaption>{{ $t($caption) }}</figcaption>
    @endisset
</figure>
