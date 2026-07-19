<!-- resources/views/components/frontend/meta.blade.php -->
@props([
    'title' => config('app.name'),
    'description' => config('app.description'),
    'canonical' => null,
    'ogImage' => null,
    'twitterCard' => 'summary_large_image',
    'twitterSite' => '@yourtwitter',
])

<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}" />
@if($canonical)
    <link rel="canonical" href="{{ $canonical }}" />
@endif
{{-- Open Graph --}}
<meta property="og:title" content="{{ $title }}" />
<meta property="og:description" content="{{ $description }}" />
@if($ogImage)
    <meta property="og:image" content="{{ $ogImage }}" />
@endif
<meta property="og:url" content="{{ request()->url() }}" />
<meta property="og:type" content="website" />
{{-- Twitter Card --}}
<meta name="twitter:card" content="{{ $twitterCard }}" />
<meta name="twitter:site" content="{{ $twitterSite }}" />
<meta name="twitter:title" content="{{ $title }}" />
<meta name="twitter:description" content="{{ $description }}" />
@if($ogImage)
    <meta name="twitter:image" content="{{ $ogImage }}" />
@endif
