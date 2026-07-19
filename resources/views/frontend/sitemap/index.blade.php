<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <sitemap>
        <loc>{{ route('frontend.sitemap.posts') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
    </sitemap>
    <sitemap>
        <loc>{{ route('frontend.sitemap.pages') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
    </sitemap>
    <sitemap>
        <loc>{{ route('frontend.sitemap.roadmaps') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
    </sitemap>
    <sitemap>
        <loc>{{ route('frontend.sitemap.taxonomies') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
    </sitemap>
    <sitemap>
        <loc>{{ route('frontend.sitemap.authors') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
    </sitemap>
</sitemapindex>
