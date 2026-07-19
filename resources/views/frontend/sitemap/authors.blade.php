<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach ($authors as $author)
    <url>
        <loc>{{ route('frontend.user', $author->username) }}</loc>
        <lastmod>{{ $author->updated_at->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.5</priority>
    </url>
    @endforeach
</urlset>
