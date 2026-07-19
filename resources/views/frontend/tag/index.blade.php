@extends("frontend.master")

@push('head')
    <meta name="page-entity-type" content="App\Models\Tag">
    <meta name="page-entity-id" content="{{ $tagModel->id }}">
@endpush

@section("title", "Posts tagged \"".$tag."\" - ".config('app.sitesettings')::first()?->site_title)
@section("meta_description", 'Explore all posts tagged with '.$tag)
@section("structured_data")
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    { "@type": "ListItem", "position": 1, "name": "Home", "item": "{{ route('frontend.home') }}" },
    { "@type": "ListItem", "position": 2, "name": "#{{ $tag }}", "item": "{{ request()->url() }}" }
  ]
}
</script>
@endsection

@section("content")
@include("frontend.tag.inc.header")

<section class="home-content-section" style="padding: var(--space-4) 0 var(--space-5);">
    <div class="container">
        <div class="home-layout">
            {{-- Main Feed --}}
            <div class="oredoo-content">
                <div class="article-feed">
                    @forelse ($posts as $post)
                        @include('frontend.partials.article-card')
                    @empty
                    <div class="col-lg-12">
                        <x-frontend.empty-state
                            icon="file-text"
                            title="Tidak Ada Artikel"
                            message="Tidak ada artikel yang dikaitkan dengan tag ini saat ini."
                            cta-text="Kembali ke Beranda"
                            :cta-url="route('frontend.home')"
                        />
                    </div>
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
