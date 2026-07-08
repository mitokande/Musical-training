<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ url('/') }}</loc>
        <changefreq>weekly</changefreq>
    </url>
@foreach ($profiles as $profile)
    <url>
        <loc>{{ route('teachers.show', $profile->slug) }}</loc>
        <lastmod>{{ $profile->updated_at?->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
    </url>
@endforeach
</urlset>
