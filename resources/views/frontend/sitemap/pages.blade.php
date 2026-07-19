<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach ($pages as $page)
    <url>
        <loc>{{ route('frontend.page', $page->slug) }}</loc>
        <lastmod>{{ $page->updated_at->toAtomString() }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.6</priority>
    </url>
    @endforeach
</urlset>
