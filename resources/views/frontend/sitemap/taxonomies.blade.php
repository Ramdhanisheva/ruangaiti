<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
    @foreach ($categories as $category)
    <url>
        <loc>{{ route('frontend.category', $category->slug) }}</loc>
        <lastmod>{{ $category->updated_at->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
        @if ($category->image)
        <image:image>
            <image:loc>{{ asset('uploads/category/' . $category->image) }}</image:loc>
            <image:title><![CDATA[{{ $category->title }}]]></image:title>
        </image:image>
        @endif
    </url>
    @endforeach
    @foreach ($tags as $tag)
    <url>
        <loc>{{ route('frontend.tag', strtolower($tag->name)) }}</loc>
        <lastmod>{{ $tag->updated_at ? $tag->updated_at->toAtomString() : now()->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.5</priority>
    </url>
    @endforeach
</urlset>
