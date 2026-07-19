@extends("frontend.master")

@section("title", $user->name." - ".config('app.sitesettings')::first()?->site_title)
@section("meta_description", $user->about ?? 'Explore all posts written by '.$user->name)
@section("og_image", asset('uploads/author/'.($user->profile ?? 'default.webp')))
@section("structured_data")
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "BreadcrumbList",
      "itemListElement": [
        { "@type": "ListItem", "position": 1, "name": "Home", "item": "{{ route('frontend.home') }}" },
        { "@type": "ListItem", "position": 2, "name": "{{ $user->name }}", "item": "{{ request()->url() }}" }
      ]
    },
    {
      "@type": "Person",
      "@id": "{{ request()->url() }}#person",
      "name": "{{ $user->name }}",
      "url": "{{ request()->url() }}",
      "image": "{{ asset('uploads/author/'.($user->profile ?? 'default.webp')) }}",
      "description": "{{ $user->about ?? '' }}"
    }
  ]
}
</script>
@endsection

@section("content")
@include("frontend.user.inc.author")

<section class="home-content-section" style="padding: var(--space-4) 0 var(--space-5); position: relative; z-index: 1;">
    <div class="container">
        <div class="home-layout">
            {{-- Main Feed --}}
            <div class="oredoo-content">
                <div class="article-feed">
                    @forelse ($posts as $post)
                        @include('frontend.partials.article-card')
                    @empty
                    <x-frontend.empty-state
                        icon="file-text"
                        title="Tidak Ada Artikel"
                        message="Penulis ini belum mempublikasikan artikel apa pun saat ini."
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
