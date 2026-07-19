@extends("frontend.master")

@section("title", config('app.sitesettings')::first()->site_title." - ".config('app.sitesettings')::first()->tagline)

@section("content")
@php
    $siteSettings = config('app.sitesettings')::first();
@endphp
<h1 class="sr-only">{{ $siteSettings->site_title }} - {{ $siteSettings->tagline }}</h1>

@include("frontend.home.inc.featuredpost")

<section class="home-content-section">
    <div class="container-fluid">
        @include("frontend.home.inc.category")
        
        <div class="home-layout">
            @include("frontend.home.inc.recentpost")
            @include("frontend.home.inc.sidebar")
        </div>
    </div>
</section>

@endsection

@section("structured_data")
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "WebSite",
      "@id": "{{ route('frontend.home') }}#website",
      "url": "{{ route('frontend.home') }}",
      "name": "{{ $siteSettings->site_title }}",
      "description": "{{ $siteSettings->description }}",
      "potentialAction": {
        "@type": "SearchAction",
        "target": {
          "@type": "EntryPoint",
          "urlTemplate": "{{ route('frontend.search') }}?q={search_term_string}"
        },
        "query-input": "required name=search_term_string"
      },
      "inLanguage": "{{ str_replace('_', '-', app()->getLocale()) }}"
    },
    {
      "@type": "Organization",
      "@id": "{{ route('frontend.home') }}#organization",
      "name": "{{ $siteSettings->site_title }}",
      "url": "{{ route('frontend.home') }}",
      "logo": {
        "@type": "ImageObject",
        "@id": "{{ route('frontend.home') }}#logo",
        "url": "{{ asset('uploads/logo/'.$siteSettings->logo_light) }}",
        "caption": "{{ $siteSettings->site_title }}"
      },
      "sameAs": {!! json_encode(\App\Models\SocialMedia::where('status', true)->pluck('link')->toArray()) !!}
    }
  ]
}
</script>
@endsection
