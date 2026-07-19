@extends("frontend.master")

@push('head')
    <meta name="search-query" content="{{ $query }}">
    <meta name="search-results-count" content="{{ $posts->total() }}">
@endpush

@section("title", "Search results for ".$query." - ".config('app.sitesettings')::first()?->site_title)
@section("meta_description", 'Search results for search query: '.$query)
@section("meta_robots", "noindex, follow")
@section("structured_data")
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    { "@type": "ListItem", "position": 1, "name": "Home", "item": "{{ route('frontend.home') }}" },
    { "@type": "ListItem", "position": 2, "name": "Search: {{ $query }}", "item": "{{ request()->url() }}" }
  ]
}
</script>
@endsection

@section("content")
@include("frontend.search.inc.header")

<section class="home-content-section" style="padding: var(--space-4) 0 var(--space-5);">
    <div class="container">
        <div class="home-layout">
            {{-- Main Feed --}}
            <div class="oredoo-content">
                <div class="article-feed">
                    @forelse ($posts as $post)
                        @include('frontend.partials.article-card')
                    @empty
                    <x-frontend.empty-state
                        icon="search"
                        title="Hasil Tidak Ditemukan"
                        message="Kami tidak menemukan artikel yang cocok dengan kata pencarian Anda."
                        cta-text="Kembali ke Beranda"
                        :cta-url="route('frontend.home')"
                    />
                    @endforelse
                </div>

                {{-- Pagination --}}
                <div class="pagination">
                    <div class="pagination-area">
                        {{ $posts->links('vendor.pagination.custom') }}
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <aside class="oredoo-sidebar">
                <div class="sidebar">
                    <x-frontend.sidebar-search/>
                    <x-frontend.sidebar-category/>
                    <x-frontend.popular-posts/>
                    <x-frontend.sidebar-social/>
                    <x-frontend.tags/>
                    <x-frontend.newsletter/>
                </div>
            </aside>
        </div>
    </div>
</section>
@endsection
