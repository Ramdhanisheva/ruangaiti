<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
    @foreach ($posts as $post)
    <url>
        <loc>{{ route('frontend.post', $post->slug) }}</loc>
        <lastmod>{{ $post->updated_at->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
        @if ($post->thumbnail)
        <image:image>
            <image:loc>{{ asset('uploads/post/' . $post->thumbnail) }}</image:loc>
            <image:title><![CDATA[{{ $post->title }}]]></image:title>
            <image:caption><![CDATA[{{ $post->title }}]]></image:caption>
        </image:image>
        @endif
    </url>
    @endforeach
</urlset>
