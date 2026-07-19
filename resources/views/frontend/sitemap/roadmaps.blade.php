<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ route('frontend.roadmap') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    @foreach ($roadmaps as $roadmap)
    <url>
        <loc>{{ route('frontend.roadmap.show', $roadmap->slug) }}</loc>
        <lastmod>{{ $roadmap->lastUpdated()->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.9</priority>
    </url>
    @endforeach
</urlset>
