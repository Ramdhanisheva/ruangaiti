<?= '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL ?>
<rss version="2.0" 
     xmlns:content="http://purl.org/rss/1.0/modules/content/"
     xmlns:wfw="http://wellformedweb.org/CommentAPI/"
     xmlns:dc="http://purl.org/dc/elements/1.1/"
     xmlns:atom="http://www.w3.org/2005/Atom"
     xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
     xmlns:slash="http://purl.org/rss/1.0/modules/slash/">
    <channel>
        <title>{{ config('app.sitesettings')::first()?->site_title ?? 'RuangAiTi' }}</title>
        <atom:link href="{{ route('frontend.feed') }}" rel="self" type="application/rss+xml" />
        <link>{{ route('frontend.home') }}</link>
        <description>{{ config('app.sitesettings')::first()?->description ?? 'Blog IT dan Teknologi Indonesia' }}</description>
        <lastBuildDate>{{ now()->format('D, d M Y H:i:s O') }}</lastBuildDate>
        <language>{{ str_replace('_', '-', app()->getLocale()) }}</language>
        <sy:updatePeriod>hourly</sy:updatePeriod>
        <sy:updateFrequency>1</sy:updateFrequency>
        <generator>Laravel</generator>

        @foreach($posts as $post)
            <item>
                <title>{{ $post->title }}</title>
                <link>{{ route('frontend.post', $post->slug) }}</link>
                <pubDate>{{ $post->created_at->format('D, d M Y H:i:s O') }}</pubDate>
                <dc:creator><![CDATA[{{ $post->user->name }}]]></dc:creator>
                <category><![CDATA[{{ $post->category->title }}]]></category>
                <guid isPermaLink="false">{{ route('frontend.post', $post->slug) }}</guid>
                <description><![CDATA[{{ $post->excerpt(200) }}]]></description>
                <content:encoded><![CDATA[{!! $post->content !!}]]></content:encoded>
            </item>
        @endforeach
    </channel>
</rss>
