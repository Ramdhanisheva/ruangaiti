@extends("frontend.master")

@push('head')
    <meta name="page-entity-type" content="App\Models\Category">
    <meta name="page-entity-id" content="{{ $category->id }}">
@endpush

@section("title", $category->title . ' - ' . config('app.sitesettings')::first()?->site_title)
@section("meta_description", $category->description ?? 'Explore all posts in '.$category->title)
@if($category->image)
@section("og_image", asset('uploads/category/'.$category->image))
@endif
@section("structured_data")
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    { "@type": "ListItem", "position": 1, "name": "Home", "item": "{{ route('frontend.home') }}" },
    { "@type": "ListItem", "position": 2, "name": "{{ $category->title }}", "item": "{{ request()->url() }}" }
  ]
}
</script>
@endsection

@section("content")

{{-- Jika tidak ada gambar kategori, tampilkan text hero di luar --}}
@if (!($category->image && file_exists(public_path("uploads/category/".$category->image))))
<div class="page-hero-section">
    <div class="container-fluid">
        <div class="breadcrumb">
            <a href="{{ route('frontend.home') }}">Home</a>
            <x-icon name="chevron-right" width="12" height="12" />
            <span>{{ $category->title }}</span>
        </div>
        <h1 class="page-hero-title">@if($category->icon)<i class="{{ $category->icon }} mr-2"></i>@endif{{ $category->title }}</h1>
        <p class="page-hero-subtitle">Explore all articles in <strong>{{ $category->title }}</strong></p>
    </div>
</div>
@endif

<section class="home-content-section" style="padding: var(--space-4) 0 var(--space-5);">
    <div class="container">
        <div class="home-layout">
            {{-- Main Feed --}}
            <div class="oredoo-content">
                
                {{-- Jika gambar kategori ada, tampilkan di dalam oredoo-content (sejajar dengan feed) --}}
                @if ($category->image && file_exists(public_path("uploads/category/".$category->image)))
                <div class="category-banner-card">
                    <div class="category-banner-img-wrapper">
                        <img src="{{ asset('uploads/category/'.$category->image) }}" alt="{{ $category->title }}" loading="lazy">
                    </div>
                    <div class="category-banner-content">
                        <div class="breadcrumb">
                            <a href="{{ route('frontend.home') }}">Home</a>
                            <x-icon name="chevron-right" width="12" height="12" />
                            <span>{{ $category->title }}</span>
                        </div>
                        <h1 class="page-hero-title">@if($category->icon)<i class="{{ $category->icon }} mr-2"></i>@endif{{ $category->title }}</h1>
                        <p class="page-hero-subtitle">Explore all articles in <strong>{{ $category->title }}</strong></p>
                    </div>
                </div>
                @endif

                <div class="article-feed">
                    @forelse ($posts as $post)
                        @include('frontend.partials.article-card')
                    @empty
                    <x-frontend.empty-state
                        icon="file-text"
                        title="Tidak Ada Artikel"
                        message="Belum ada artikel yang diterbitkan dalam kategori ini."
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
