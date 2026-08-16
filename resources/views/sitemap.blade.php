<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
@foreach ($landingUrls as $locale => $url)
    <url>
        <loc>{{ $url }}</loc>
@foreach ($landingUrls as $altLocale => $altUrl)
        <xhtml:link rel="alternate" hreflang="{{ $altLocale }}" href="{{ $altUrl }}"/>
@endforeach
        <xhtml:link rel="alternate" hreflang="x-default" href="{{ $landingUrls['en'] }}"/>
        <changefreq>weekly</changefreq>
    </url>
@endforeach
@foreach ($localizedUrls as $set)
@foreach ($set as $url)
    <url>
        <loc>{{ $url }}</loc>
@foreach ($set as $altLocale => $altUrl)
        <xhtml:link rel="alternate" hreflang="{{ $altLocale }}" href="{{ $altUrl }}"/>
@endforeach
        <xhtml:link rel="alternate" hreflang="x-default" href="{{ $set['en'] }}"/>
        <changefreq>weekly</changefreq>
    </url>
@endforeach
@endforeach
@foreach ($staticUrls as $url)
    <url>
        <loc>{{ $url }}</loc>
        <changefreq>weekly</changefreq>
    </url>
@endforeach
@foreach ($lessons as $lesson)
    <url>
        <loc>{{ $lesson['loc'] }}</loc>
@if ($lesson['lastmod'])
        <lastmod>{{ $lesson['lastmod'] }}</lastmod>
@endif
        <changefreq>monthly</changefreq>
    </url>
@endforeach
@foreach ($articles as $article)
    <url>
        <loc>{{ route('articles.show', $article->slug) }}</loc>
        <lastmod>{{ ($article->updated_at ?? $article->published_at)?->toAtomString() }}</lastmod>
        <changefreq>monthly</changefreq>
    </url>
@endforeach
@foreach ($gameUrls as $url)
    <url>
        <loc>{{ $url }}</loc>
        <changefreq>monthly</changefreq>
    </url>
@endforeach
@foreach ($profiles as $profile)
    <url>
        <loc>{{ $profile->publicUrl() }}</loc>
        <lastmod>{{ $profile->updated_at?->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
    </url>
@endforeach
</urlset>
