@extends("frontend.master")

@section("title", $post->title." - ".config('app.sitesettings')::first()?->site_title)
@section("meta_description", $post->excerpt(155))
@section("og_type", "article")
@section("og_image", asset("uploads/post/".$post->thumbnail))

@push('head')
    {{-- Article-specific Open Graph for WhatsApp / Facebook rich card --}}
    <meta property="article:published_time" content="{{ $post->created_at->toIso8601String() }}">
    <meta property="article:modified_time" content="{{ $post->updated_at->toIso8601String() }}">
    <meta property="article:author" content="{{ $post->user->name }}">
    <meta property="article:section" content="{{ $post->category->title }}">
    @foreach($post->tags as $tag)
    <meta property="article:tag" content="{{ $tag->name }}">
    @endforeach
    <meta name="page-entity-type" content="App\Models\Post">
    <meta name="page-entity-id" content="{{ $post->id }}">
    
    <!-- Code Highlight theme and upgraded editor layout styles -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/styles/atom-one-dark.min.css">
    <link rel="stylesheet" href="{{ asset("assets/dashboard/css/editor-upgrade.css") }}"/>
@endpush

@section('structured_data')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "Article",
      "@id": "{{ request()->url() }}#article",
      "url": "{{ request()->url() }}",
      "headline": "{{ $post->title }}",
      "image": "{{ asset('uploads/post/' . $post->thumbnail) }}",
      "datePublished": "{{ $post->created_at->toIso8601String() }}",
      "dateModified": "{{ $post->updated_at->toIso8601String() }}",
      "author": {
        "@type": "Person",
        "name": "{{ $post->user->name }}",
        "url": "{{ route('frontend.user', $post->user->username) }}"
      },
      "publisher": {
        "@type": "Organization",
        "name": "{{ config('app.sitesettings')::first()?->site_title ?? config('app.name') }}",
        "logo": {
          "@type": "ImageObject",
          "url": "{{ asset('uploads/logo/'.(config('app.sitesettings')::first()?->logo_light ?? 'logo.png')) }}"
        }
      },
      "description": "{{ $post->excerpt(155) }}"
    },
    {
      "@type": "BreadcrumbList",
      "@id": "{{ request()->url() }}#breadcrumb",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "name": "Home",
          "item": "{{ route('frontend.home') }}"
        },
        @if($roadmap)
        {
          "@type": "ListItem",
          "position": 2,
          "name": "Roadmap",
          "item": "{{ route('frontend.roadmap') }}"
        },
        {
          "@type": "ListItem",
          "position": 3,
          "name": "{{ $roadmap->title }}",
          "item": "{{ route('frontend.roadmap.show', $roadmap->slug) }}"
        },
        @if($activeModule)
        {
          "@type": "ListItem",
          "position": 4,
          "name": "{{ $activeModule->title }}",
          "item": "{{ route('frontend.roadmap.show', $roadmap->slug) }}#module-{{ $activeModule->id }}"
        }
        @endif
        @else
        {
          "@type": "ListItem",
          "position": 2,
          "name": "{{ $post->category->title }}",
          "item": "{{ route('frontend.category', $post->category->slug) }}"
        }
        @endif
      ]
    }
  ]
}
</script>
@endsection

@section("content")
<section class="post-single">
    <div class="container-fluid" style="max-width: 1360px; padding: 0 var(--space-3);">
        <!-- Post Header -->
        @if(isset($hasChapters) && $hasChapters && $currentPage > 1)
            <!-- Compact Header for Page 2+ -->
            <header class="post-single-header post-single-header--compact" style="margin-bottom: var(--space-3); padding-bottom: var(--space-2); border-bottom: 1px solid var(--border-color);">
                <div class="breadcrumb" style="margin-bottom: var(--space-1); font-size: 12px; display: flex; align-items: center; gap: 6px; color: var(--text-muted);">
                    <a href="{{ route("frontend.home") }}">Home</a>
                    <x-icon name="chevron-right" width="10" height="10" />
                    @if($roadmap)
                        <a href="{{ route("frontend.roadmap") }}">Roadmap</a>
                        <x-icon name="chevron-right" width="10" height="10" />
                        <a href="{{ route("frontend.roadmap.show", $roadmap->slug) }}">{{ $roadmap->title }}</a>
                    @else
                        <a href="{{ route("frontend.category", $post->category->slug) }}">{{ $post->category->title }}</a>
                    @endif
                </div>
                <h4 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--text-primary); line-height: 1.3;">
                    <a href="{{ route('frontend.post', $post->slug) }}" style="transition: color var(--transition-fast);" onmouseover="this.style.color='var(--color-primary)'" onmouseout="this.style.color='inherit'">{{ $post->title }}</a>
                </h4>
            </header>
        @else
            <!-- Full Header for Page 1 -->
            <header class="post-single-header">
                <div class="breadcrumb">
                    <a href="{{ route("frontend.home") }}">Home</a>
                    <x-icon name="chevron-right" width="12" height="12" />
                    @if($roadmap)
                        <a href="{{ route("frontend.roadmap") }}">Roadmap</a>
                        <x-icon name="chevron-right" width="12" height="12" />
                        <a href="{{ route("frontend.roadmap.show", $roadmap->slug) }}">{{ $roadmap->title }}</a>
                        @if($activeModule)
                            <x-icon name="chevron-right" width="12" height="12" />
                            <a href="{{ route('frontend.roadmap.show', $roadmap->slug) }}#module-{{ $activeModule->id }}">{{ $activeModule->title }}</a>
                        @endif
                    @else
                        <a href="{{ route("frontend.category", $post->category->slug) }}">{{ $post->category->title }}</a>
                    @endif
                </div>

                @if($roadmap)
                <div class="roadmap-context-bar">
                    <div class="roadmap-context-bar__left">
                        <i class="fas fa-route mr-1 text-info"></i>
                        Jalur Belajar: <a href="{{ route('frontend.roadmap.show', $roadmap->slug) }}" class="roadmap-context-bar__link">{{ $roadmap->title }}</a>
                        @if($activeModule)
                            <span class="roadmap-context-bar__sep">/</span> Modul: <strong>{{ $activeModule->title }}</strong>
                        @endif
                    </div>
                    <div class="roadmap-context-bar__badge">
                        Pelajaran {{ $currentLessonNumber }} / {{ $totalLessons }}
                    </div>
                </div>
                @endif

                <h1>{{ $post->title }}</h1>
                <div class="post-author-meta">
                    <img class="author-img" src="{{ asset("uploads/author/".($post->user->profile ?? "default.webp")) }}" alt="{{ $post->user->name }}" style="width:40px; height:40px; border-radius:50%; object-fit:cover;"/>
                    <div>
                        <div class="author-name" style="display: flex; flex-direction: column;">
                            <a href="{{ route("frontend.user", $post->user->username) }}" style="font-weight:600; line-height: 1.2;">{{ $post->user->name }}</a>
                            @if(!empty($post->user->tagline))
                                <span class="author-tagline-meta" style="font-size: 11px; color: var(--text-muted); font-weight: normal; margin-top: 2px;">{{ $post->user->tagline }}</span>
                            @endif
                        </div>
                        <div class="meta-row" style="margin-top:2px; font-size:12px; color:var(--text-secondary); display:flex; align-items:center; gap:6px;">
                            <span>{{ $post->created_at->format("M d, Y") }}</span>
                            <div class="meta-dot"></div>
                            <span>{{ $post->readTime() }}</span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Post Cover Image -->
            <div class="post-cover-image">
                <img src="{{ asset("uploads/post/".$post->thumbnail) }}" alt="{{ $post->title }}"/>
            </div>
        @endif

        @php
            $currentIpHash = hash('sha256', request()->ip());
            $hasLiked = \App\Models\LikesFeedback::whereIn('likeable_type', ['post', 'App\\Models\\Post'])
                ->where('likeable_id', $post->id)
                ->where('type', 'like')
                ->where('ip_hash', $currentIpHash)
                ->exists();
            
            $hasHelpfulYes = \App\Models\LikesFeedback::whereIn('likeable_type', ['post', 'App\\Models\\Post'])
                ->where('likeable_id', $post->id)
                ->where('type', 'helpful_yes')
                ->where('ip_hash', $currentIpHash)
                ->exists();

            $hasHelpfulNo = \App\Models\LikesFeedback::whereIn('likeable_type', ['post', 'App\\Models\\Post'])
                ->where('likeable_id', $post->id)
                ->where('type', 'helpful_no')
                ->where('ip_hash', $currentIpHash)
                ->exists();
        @endphp

        <!-- Post Layout Grid -->
        <div class="post-layout-grid">
            <!-- Left Actions Panel -->
            <aside class="post-actions-panel">
                {{-- Like Button --}}
                <div class="action-btn-group" id="like-action-group">
                    <button
                        id="like-btn"
                        class="action-btn action-btn--like {{ $hasLiked ? 'liked' : '' }}"
                        aria-label="Like Article"
                        title="Suka artikel ini"
                        data-action="like"
                        data-type="post"
                        data-id="{{ $post->id }}">
                        <x-icon name="heart" width="18" height="18" />
                        <span class="action-btn-count like-count">{{ \App\Models\LikesFeedback::whereIn('likeable_type', ['post', 'App\\Models\\Post'])->where('likeable_id', $post->id)->where('type', 'like')->count() }}</span>
                    </button>
                </div>
                {{-- Share Button --}}
                <button id="share-link-btn" class="action-btn" aria-label="Share Article" title="Share Article"><x-icon name="share" width="18" height="18" /></button>
            </aside>

            <!-- Center Article Content -->
            <main class="post-main-content" id="article-content">
                <article class="post-body">
                    @if(isset($hasChapters) && $hasChapters)
                        @php
                            $displayTitle = '';
                            if ($currentPage == 1) {
                                $displayTitle = !empty($post->first_page_title) ? $post->first_page_title : 'Pendahuluan';
                            } elseif ($activeChapter) {
                                // Clean any legacy "Chapter X" or "Chapter" prefix from the title
                                $displayTitle = trim(preg_replace('/^chapter\s*\d*\s*:?\s*/i', '', $activeChapter->title ?? ''));
                            }
                        @endphp
                        <div class="chapter-header-badge">
                            Halaman {{ $currentPage }} dari {{ $totalChapters }}{{ !empty($displayTitle) ? ' — ' . $displayTitle : '' }}
                        </div>
                        <h2 class="chapter-title-heading">
                            {{ !empty($displayTitle) ? $displayTitle : 'Halaman ' . $currentPage }}
                        </h2>
                    @endif
                    {!! preg_replace('/<h1\b([^>]*)>(.*?)<\/h1>/i', '<h2$1>$2</h2>', $post->content) !!}
                </article>

                {{-- Chapter Navigation Controls --}}
                @if(isset($hasChapters) && $hasChapters)
                <div class="chapter-navigation-container">
                    <div class="chapter-navigation-info">
                        Halaman {{ $currentPage }} dari {{ $totalChapters }}
                    </div>
                    
                    <div class="chapter-navigation-controls">
                        {{-- Previous Button --}}
                        @if($currentPage > 1)
                            <a href="{{ request()->fullUrlWithQuery(['page' => $currentPage - 1]) }}#article-content" class="chapter-nav-btn chapter-nav-btn--prev">
                                <i class="fas fa-arrow-left mr-2"></i> Halaman Sebelumnya
                            </a>
                        @else
                            <button class="chapter-nav-btn chapter-nav-btn--prev" disabled>
                                <i class="fas fa-arrow-left mr-2"></i> Halaman Sebelumnya
                            </button>
                        @endif
                        
                        {{-- Page Numbers --}}
                        <div class="chapter-nav-pages">
                            @for($i = 1; $i <= $totalChapters; $i++)
                                <a href="{{ request()->fullUrlWithQuery(['page' => $i]) }}#article-content" class="chapter-nav-page-link {{ $currentPage == $i ? 'active' : '' }}">
                                    {{ $i }}
                                </a>
                            @endfor
                        </div>
                        
                        {{-- Next Button --}}
                        @if($currentPage < $totalChapters)
                            <a href="{{ request()->fullUrlWithQuery(['page' => $currentPage + 1]) }}#article-content" class="chapter-nav-btn chapter-nav-btn--next">
                                Halaman Selanjutnya <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        @else
                            <button class="chapter-nav-btn chapter-nav-btn--next" disabled>
                                Halaman Selanjutnya <i class="fas fa-arrow-right ml-2"></i>
                            </button>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Post Tags section -->
                @if ($post->tags_count > 0)
                <div class="tags-cloud" style="margin-top:32px; padding-top:24px; border-top:1px solid var(--border-color);">
                    @foreach ($post->tags as $tag)
                    <a href="{{ route("frontend.tag", $str::slug($tag->name)) }}" class="tag-badge">{{ $tag->name }}</a>
                    @endforeach
                </div>
                @endif

                <!-- Engagement Section (Likes & Feedback) -->
                <div class="post-engagement-section">
                    <h4 class="engagement-title">Apakah artikel ini bermanfaat?</h4>
                    <div class="engagement-buttons">
                        {{-- Helpful Button --}}
                        <button class="action-btn-pill {{ $hasHelpfulYes ? 'active' : '' }}" data-action="helpful-yes" data-type="post" data-id="{{ $post->id }}" aria-label="Bermanfaat" title="Artikel ini bermanfaat">
                            <i class="fas fa-thumbs-up"></i> Bermanfaat
                            <span class="count helpful-yes-count">({{ \App\Models\LikesFeedback::whereIn('likeable_type', ['post', 'App\\Models\\Post'])->where('likeable_id', $post->id)->where('type', 'helpful_yes')->count() }})</span>
                        </button>
                        
                        {{-- Not Helpful Button --}}
                        <button class="action-btn-pill {{ $hasHelpfulNo ? 'active' : '' }}" data-action="helpful-no" data-type="post" data-id="{{ $post->id }}" aria-label="Tidak Bermanfaat" title="Artikel ini tidak bermanfaat">
                            <i class="fas fa-thumbs-down"></i> Tidak Bermanfaat
                            <span class="count helpful-no-count">({{ \App\Models\LikesFeedback::whereIn('likeable_type', ['post', 'App\\Models\\Post'])->where('likeable_id', $post->id)->where('type', 'helpful_no')->count() }})</span>
                        </button>
                        
                        {{-- Like Button --}}
                        <button class="action-btn-pill action-btn-pill--like {{ $hasLiked ? 'liked' : '' }}" data-action="like" data-type="post" data-id="{{ $post->id }}" aria-label="Suka" title="Suka artikel ini">
                            <i class="fas fa-heart text-danger"></i> Suka
                            <span class="count like-count">({{ \App\Models\LikesFeedback::whereIn('likeable_type', ['post', 'App\\Models\\Post'])->where('likeable_id', $post->id)->where('type', 'like')->count() }})</span>
                        </button>
                    </div>
                </div>

                <!-- Author Bio Section -->
                @include("frontend.post.inc.author")

                <!-- Comments Section -->
                @include("frontend.post.inc.comment")

                {{-- Prev / Next Navigation --}}
                @if ($roadmap && ($prevLesson || $nextLesson))
                <div class="lesson-nav-container">
                    <div class="lesson-nav-header">
                        <i class="fas fa-play-circle text-primary"></i> Lanjutkan Belajar
                    </div>
                    <div class="lesson-nav-grid">
                        @if ($prevLesson)
                        <a href="{{ route('frontend.post', $prevLesson->slug) }}?roadmap={{ $roadmap->slug }}" class="lesson-nav-card lesson-nav-card--prev">
                            <div class="lesson-nav-card-thumb-wrapper">
                                <img src="{{ asset('uploads/post/' . $prevLesson->thumbnail) }}" alt="{{ $prevLesson->title }}" loading="lazy">
                            </div>
                            <div class="lesson-nav-card-body">
                                <div class="lesson-nav-card-label">
                                    <i class="fas fa-arrow-left mr-1"></i> Pelajaran Sebelumnya
                                </div>
                                <div class="lesson-nav-card-badge">Pelajaran {{ $prevLesson->lesson_number }}</div>
                                <h4 class="lesson-nav-card-title">{{ $prevLesson->title }}</h4>
                                <span class="lesson-nav-card-readtime">
                                    <i class="far fa-clock mr-1"></i> {{ $prevLesson->readTime() }}
                                </span>
                            </div>
                        </a>
                        @endif

                        {{-- Current Lesson --}}
                        <div class="lesson-nav-card lesson-nav-card--current">
                            <div class="lesson-nav-card-thumb-wrapper">
                                <img src="{{ asset('uploads/post/' . $post->thumbnail) }}" alt="{{ $post->title }}" loading="lazy">
                            </div>
                            <div class="lesson-nav-card-body">
                                <div class="lesson-nav-card-label text-primary">
                                    <i class="fas fa-book-open mr-1"></i> Pelajaran Sekarang
                                </div>
                                <div class="lesson-nav-card-badge">Pelajaran {{ $currentLessonNumber }}</div>
                                <h4 class="lesson-nav-card-title">{{ $post->title }}</h4>
                                <span class="lesson-nav-card-readtime">
                                    <i class="far fa-clock mr-1"></i> {{ $post->readTime() }}
                                </span>
                            </div>
                        </div>

                        @if ($nextLesson)
                        <a href="{{ route('frontend.post', $nextLesson->slug) }}?roadmap={{ $roadmap->slug }}" class="lesson-nav-card lesson-nav-card--next">
                            <div class="lesson-nav-card-body text-right">
                                <div class="lesson-nav-card-label">
                                    Pelajaran Berikutnya <i class="fas fa-arrow-right ml-1"></i>
                                </div>
                                <div class="lesson-nav-card-badge">Pelajaran {{ $nextLesson->lesson_number }}</div>
                                <h4 class="lesson-nav-card-title">{{ $nextLesson->title }}</h4>
                                <span class="lesson-nav-card-readtime">
                                    <i class="far fa-clock mr-1"></i> {{ $nextLesson->readTime() }}
                                </span>
                            </div>
                            <div class="lesson-nav-card-thumb-wrapper">
                                <img src="{{ asset('uploads/post/' . $nextLesson->thumbnail) }}" alt="{{ $nextLesson->title }}" loading="lazy">
                            </div>
                        </a>
                        @endif
                    </div>
                </div>
                @elseif ($prevPost || $nextPost)
                <div class="lesson-nav-container">
                    <div class="lesson-nav-grid">
                        {{-- Previous Post Card --}}
                        @if ($prevPost)
                        <a href="{{ route('frontend.post', $prevPost->slug) }}" class="lesson-nav-card lesson-nav-card--prev">
                            <div class="lesson-nav-card-thumb-wrapper">
                                <img src="{{ asset('uploads/post/' . $prevPost->thumbnail) }}" alt="{{ $prevPost->title }}" loading="lazy">
                            </div>
                            <div class="lesson-nav-card-body">
                                <div class="lesson-nav-card-label">
                                    <i class="fas fa-arrow-left mr-1"></i> Post Sebelumnya
                                </div>
                                <h4 class="lesson-nav-card-title">{{ $prevPost->title }}</h4>
                                <span class="lesson-nav-card-readtime">
                                    <i class="far fa-clock mr-1"></i> {{ $prevPost->readTime() }}
                                </span>
                            </div>
                        </a>
                        @else
                        <a href="{{ route('frontend.home') }}" class="lesson-nav-card lesson-nav-card--prev text-muted" style="opacity: 0.85;">
                            <div class="lesson-nav-card-icon-placeholder">
                                <i class="fas fa-home"></i>
                            </div>
                            <div class="lesson-nav-card-body">
                                <div class="lesson-nav-card-label">Beranda</div>
                                <h4 class="lesson-nav-card-title">Kembali ke Beranda</h4>
                                <span class="lesson-nav-card-readtime">Jelajahi artikel lainnya</span>
                            </div>
                        </a>
                        @endif

                        {{-- Next Post Card --}}
                        @if ($nextPost)
                        <a href="{{ route('frontend.post', $nextPost->slug) }}" class="lesson-nav-card lesson-nav-card--next">
                            <div class="lesson-nav-card-body text-right">
                                <div class="lesson-nav-card-label">
                                    Post Selanjutnya <i class="fas fa-arrow-right ml-1"></i>
                                </div>
                                <h4 class="lesson-nav-card-title">{{ $nextPost->title }}</h4>
                                <span class="lesson-nav-card-readtime">
                                    <i class="far fa-clock mr-1"></i> {{ $nextPost->readTime() }}
                                </span>
                            </div>
                            <div class="lesson-nav-card-thumb-wrapper">
                                <img src="{{ asset('uploads/post/' . $nextPost->thumbnail) }}" alt="{{ $nextPost->title }}" loading="lazy">
                            </div>
                        </a>
                        @else
                        <a href="{{ route('frontend.home') }}" class="lesson-nav-card lesson-nav-card--next text-muted" style="opacity: 0.85;">
                            <div class="lesson-nav-card-body text-right" style="align-items: flex-end;">
                                <div class="lesson-nav-card-label">Beranda <i class="fas fa-home ml-1"></i></div>
                                <h4 class="lesson-nav-card-title">Kembali ke Beranda</h4>
                                <span class="lesson-nav-card-readtime">Jelajahi artikel lainnya</span>
                            </div>
                            <div class="lesson-nav-card-icon-placeholder">
                                <i class="fas fa-home"></i>
                            </div>
                        </a>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Standalone Home Button --}}
                <div class="back-to-home-wrapper">
                    <a href="{{ route('frontend.home') }}" class="btn-back-to-home">
                        <i class="fas fa-home mr-2"></i> Kembali ke Beranda
                    </a>
                </div>
            </main>

            <!-- Right Sticky TOC Panel -->
            <aside class="post-toc-panel">
                @if(isset($hasChapters) && $hasChapters)
                    {{-- Multi-page post: Show Daftar Halaman --}}
                    <div class="toc-container">
                        <div class="toc-title" onclick="if(window.innerWidth <= 768) { var exp = this.parentElement.classList.toggle('expanded'); this.setAttribute('aria-expanded', String(exp)); }" role="button" aria-expanded="false">Daftar Halaman</div>
                        <nav class="toc-links">
                            @for($i = 1; $i <= $totalChapters; $i++)
                                @php
                                    $pageTitle = '';
                                    if ($i == 1) {
                                        $pageTitle = '1. ' . (!empty($post->first_page_title) ? $post->first_page_title : 'Pendahuluan');
                                    } else {
                                        $chap = $chapters->get($i - 2);
                                        $cleanChapTitle = $chap ? trim(preg_replace('/^chapter\s*\d*\s*:?\s*/i', '', $chap->title ?? '')) : '';
                                        $pageTitle = $i . '. ' . (!empty($cleanChapTitle) ? $cleanChapTitle : 'Halaman ' . $i);
                                    }
                                @endphp
                                <a href="{{ request()->fullUrlWithQuery(['page' => $i]) }}#article-content" class="toc-link {{ $currentPage == $i ? 'active' : '' }}" style="display: block; padding: 6px 12px; font-size: 13px; line-height: 1.4; border-left: 2px solid {{ $currentPage == $i ? 'var(--color-primary)' : 'transparent' }}; font-weight: {{ $currentPage == $i ? '600' : 'normal' }}; transition: all var(--transition-fast);">
                                    {{ $pageTitle }}
                                </a>
                            @endfor
                        </nav>
                    </div>
                @endif

                {{-- Daftar Isi (Heading TOC) --}}
                <div class="toc-container" id="toc-container">
                    <div class="toc-title" onclick="if(window.innerWidth <= 768) { var exp = this.parentElement.classList.toggle('expanded'); this.setAttribute('aria-expanded', String(exp)); }" role="button" aria-expanded="false">Daftar Isi</div>
                    <nav class="toc-links" id="toc-links">
                        <!-- Populated by JS -->
                    </nav>
                </div>
            </aside>
        </div>
    </div>
</section>

{{-- Related Posts --}}
@if ($relatedPosts->count() > 0)
<section class="related-articles-section" aria-label="Related articles">
    <div class="container">
        <h2 class="related-articles-title">Related Articles</h2>
        <div class="related-articles-grid">
            @foreach ($relatedPosts as $related)
            <article class="article-card related-article-card">
                <a href="{{ route('frontend.post', $related->slug) }}" class="related-article-thumb" tabindex="-1" aria-hidden="true">
                    <img src="{{ asset('uploads/post/'.$related->thumbnail) }}" alt="{{ $related->title }}" loading="lazy">
                </a>
                <div class="related-article-body">
                    <a href="{{ route('frontend.category', $related->category->slug) }}"
                       class="category-style-1"
                       style="--cat-color: var(--cat-{{ \Illuminate\Support\Str::slug($related->category->slug) }});">{{ $related->category->title }}</a>
                    <h3 class="related-article-card-title">
                        <a href="{{ route('frontend.post', $related->slug) }}">{{ $related->title }}</a>
                    </h3>
                    <div class="related-article-meta">
                        <span>{{ $related->user->name }} &middot; {{ $related->created_at->format('M d, Y') }}</span>
                        @if(!empty($related->user->tagline))
                            <span class="related-article-tagline">{{ $related->user->tagline }}</span>
                        @endif
                    </div>
                </div>
            </article>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection

@section("structured_data")
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "BreadcrumbList",
      "@id": "{{ request()->url() }}#breadcrumb",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "name": "Home",
          "item": "{{ route('frontend.home') }}"
        },
        {
          "@type": "ListItem",
          "position": 2,
          "name": "{{ $post->category->title }}",
          "item": "{{ route('frontend.category', $post->category->slug) }}"
        },
        {
          "@type": "ListItem",
          "position": 3,
          "name": "{{ $post->title }}",
          "item": "{{ request()->url() }}"
        }
      ]
    },
    {
      "@type": "BlogPosting",
      "@id": "{{ request()->url() }}#post",
      "headline": "{{ $post->title }}",
      "image": "{{ asset('uploads/post/'.$post->thumbnail) }}",
      "datePublished": "{{ $post->created_at->toIso8601String() }}",
      "dateModified": "{{ $post->updated_at->toIso8601String() }}",
      "author": {
        "@type": "Person",
        "name": "{{ $post->user->name }}",
        "url": "{{ route('frontend.user', $post->user->username) }}"
      },
      "publisher": {
        "@type": "Organization",
        "name": "{{ config('app.sitesettings')::first()?->site_title }}",
        "logo": {
          "@type": "ImageObject",
          "url": "{{ asset('uploads/logo/'.(config('app.sitesettings')::first()?->logo_light ?? '')) }}"
        }
      },
      "description": "{{ $post->excerpt(150) }}",
      "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": "{{ request()->url() }}"
      }
    }
  ]
}
</script>
@endsection

@section("script")
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/highlight.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Highlight.js safely
        try {
            if (typeof hljs !== 'undefined') {
                hljs.highlightAll();
            }
        } catch (e) {
            console.warn('Highlight.js load failed:', e);
        }

        // Wrap tables in responsive container dynamically
        document.querySelectorAll('.post-body table').forEach(function(table) {
            if (!table.parentNode.classList.contains('table-responsive-wrapper')) {
                const wrapper = document.createElement('div');
                wrapper.className = 'table-responsive-wrapper';
                table.parentNode.insertBefore(wrapper, table);
                wrapper.appendChild(table);
            }
        });

        // Initialize Lightbox for images
        const lightboxOverlay = document.createElement('div');
        lightboxOverlay.className = 'editor-lightbox-overlay';
        lightboxOverlay.innerHTML = '<img class="editor-lightbox-img" src="" alt="Preview">';
        document.body.appendChild(lightboxOverlay);

        lightboxOverlay.addEventListener('click', function() {
            lightboxOverlay.classList.remove('active');
        });

        document.querySelectorAll('.post-body img').forEach(img => {
            img.style.cursor = 'zoom-in';
            img.addEventListener('click', function(e) {
                // Don't trigger if image is inside a link
                if (img.closest('a')) return;
                
                lightboxOverlay.querySelector('.editor-lightbox-img').src = img.src;
                lightboxOverlay.querySelector('.editor-lightbox-img').alt = img.alt || '';
                lightboxOverlay.classList.add('active');
                e.preventDefault();
            });
        });

        // ── VS Code-style Code Block Wrapper ──────────────────────────────────
        // Run AFTER hljs.highlightAll() so classes are already applied
        document.querySelectorAll('.post-body pre').forEach(function(pre) {
            // Avoid double-wrapping
            if (pre.parentNode.classList.contains('code-block-wrapper')) return;

            var code = pre.querySelector('code');
            var lang = 'code';
            if (code) {
                var langClass = Array.from(code.classList).find(function(c) { return c.startsWith('language-'); });
                if (langClass) lang = langClass.replace('language-', '');
                // highlight.js may add hljs class
                var hljsLang = code.getAttribute('data-highlighted') ? (code.className.match(/language-(\S+)/) || [])[1] : null;
                if (hljsLang) lang = hljsLang;
            }

            // Build wrapper
            var wrapper = document.createElement('div');
            wrapper.className = 'code-block-wrapper';

            // Build header
            var header = document.createElement('div');
            header.className = 'code-block-header';

            // macOS traffic light dots
            var dots = document.createElement('div');
            dots.className = 'code-block-dots';
            dots.innerHTML = '<span></span><span></span><span></span>';

            // Language label
            var langEl = document.createElement('span');
            langEl.className = 'code-block-lang';
            langEl.textContent = lang.toUpperCase();

            // Copy button
            var copyBtn = document.createElement('button');
            copyBtn.className = 'code-copy-btn';
            copyBtn.type = 'button';
            copyBtn.innerHTML = '<i class="fas fa-copy"></i> Copy';
            copyBtn.addEventListener('click', function() {
                var text = code ? (code.innerText || code.textContent) : pre.innerText;
                navigator.clipboard.writeText(text.trim()).then(function() {
                    copyBtn.innerHTML = '<i class="fas fa-check"></i> Copied!';
                    copyBtn.classList.add('copied');
                    setTimeout(function() {
                        copyBtn.innerHTML = '<i class="fas fa-copy"></i> Copy';
                        copyBtn.classList.remove('copied');
                    }, 2000);
                }).catch(function() {
                    // Fallback for older browsers
                    var ta = document.createElement('textarea');
                    ta.value = text.trim();
                    document.body.appendChild(ta);
                    ta.select();
                    document.execCommand('copy');
                    document.body.removeChild(ta);
                    copyBtn.innerHTML = '<i class="fas fa-check"></i> Copied!';
                    copyBtn.classList.add('copied');
                    setTimeout(function() {
                        copyBtn.innerHTML = '<i class="fas fa-copy"></i> Copy';
                        copyBtn.classList.remove('copied');
                    }, 2000);
                });
            });

            header.appendChild(dots);
            header.appendChild(langEl);
            header.appendChild(copyBtn);

            // Insert wrapper around pre
            pre.parentNode.insertBefore(wrapper, pre);
            wrapper.appendChild(header);
            wrapper.appendChild(pre);
        });
    });
</script>
@endsection
