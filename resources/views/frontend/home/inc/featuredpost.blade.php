@if ($featuredposts->count() > 0)
@php
    $allFeatured = $featuredposts->values();
    $trendingPosts = $allFeatured->skip(1)->take(3);
    $total = $allFeatured->count();
    
    // Fallback: get recent posts excluding the active featured post
    $mainFeat = $allFeatured->first();
    $fallbackStories = $recentposts->filter(function($post) use ($mainFeat) {
        return $post->id !== $mainFeat->id;
    })->take(4);
@endphp

<section class="hero-section">
    <div class="container-fluid">
        <div class="hero-grid{{ $totalActivePosts <= 1 ? ' full-width' : '' }}">

            {{-- Left: Featured Slider --}}
            <div class="hero-slider-wrapper" id="heroSlider">

                {{-- Slides track --}}
                <div class="hero-slides-track">
                    @foreach ($allFeatured as $idx => $feat)
                    <article class="hero-featured-card{{ $idx === 0 ? ' active' : '' }}" data-index="{{ $idx }}">
                        {{-- Image + gradient overlay + content all inside one relative wrapper --}}
                        <div class="hero-img-wrapper">
                            <img src="{{ asset('uploads/post/'.$feat->thumbnail) }}" alt="{{ $feat->title }}" {{ $idx === 0 ? 'loading=eager' : 'loading=lazy' }} />

                            {{-- Content floats above gradient --}}
                            <div class="hero-content-wrapper">
                                <div>
                                    <a href="{{ route('frontend.category', $feat->category->slug) }}" class="category-style-1" style="--cat-color: var(--cat-{{ \Illuminate\Support\Str::slug($feat->category->slug) }}); margin-bottom:8px; display:inline-block;">{{ $feat->category->title }}</a>
                                    <a href="{{ route('frontend.post', $feat->slug) }}" class="hero-title-link">
                                        <h2>{{ $feat->title }}</h2>
                                    </a>
                                    <p class="hero-excerpt">{{ $feat->excerpt(120) }}</p>
                                </div>
                                <div class="author-block" style="margin-top:6px;">
                                    <img src="{{ asset('uploads/author/'.($feat->user->profile ?? 'default.webp')) }}" alt="{{ $feat->user->name }}" class="author-img"/>
                                    <div>
                                         <div class="author-name" style="line-height: 1.2;"><a href="{{ route('frontend.user', $feat->user->username) }}">{{ $feat->user->name }}</a></div>
                                         @if (!empty($feat->user->tagline))
                                             <div class="author-tagline-hero" style="font-size: 11px; color: rgba(255, 255, 255, 0.75); font-weight: normal; margin-top: 1px; margin-bottom: 2px;">{{ $feat->user->tagline }}</div>
                                         @endif
                                         <div class="meta-row">
                                            <span>{{ $feat->created_at->format('M d, Y') }}</span>
                                            <span class="meta-dot"></span>
                                            <span>{{ $feat->readTime() }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                    @endforeach
                </div>

                {{-- Controls bar --}}
                @if ($total > 1)
                <div class="hero-controls">
                    <div class="hero-dots" id="heroDots">
                        @foreach ($allFeatured as $idx => $feat)
                        <button class="hero-dot{{ $idx === 0 ? ' active' : '' }}" data-goto="{{ $idx }}" aria-label="Slide {{ $idx + 1 }}"></button>
                        @endforeach
                    </div>
                    <div class="hero-arrows">
                        <button class="hero-arrow-btn" id="heroPrev" aria-label="Previous">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                        </button>
                        <span class="hero-counter" id="heroCounter">1 / {{ $total }}</span>
                        <button class="hero-arrow-btn" id="heroNext" aria-label="Next">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                    </div>
                </div>
                @endif
            </div>

            @if ($totalActivePosts > 1)
            {{-- Right: Editor's Picks or Latest Stories --}}
            <div class="hero-trending-panel">
                <div class="hero-trending-header">
                    <h3>{{ $totalActivePosts >= 6 ? "Editor's Picks" : "Latest Stories" }}</h3>
                    <span class="category-style-1" style="font-size: 11px; padding: 2px 8px;">{{ $totalActivePosts >= 6 ? "Trending" : "New" }}</span>
                </div>
                <div class="trending-list">
                    @php
                        $displayPosts = $totalActivePosts >= 6 ? $trendingPosts : $fallbackStories;
                    @endphp
                    @forelse ($displayPosts->values() as $idx => $tPost)
                    <div class="article-card trending-item" data-size="md">
                        <div class="trending-num">0{{ $idx + 1 }}</div>
                        <div class="trending-details">
                            <a href="{{ route('frontend.post', $tPost->slug) }}">
                                <h4 class="article-card-title">{{ $tPost->title }}</h4>
                            </a>
                            <div class="trending-meta">
                                <span>In <a href="{{ route('frontend.category', $tPost->category->slug) }}" style="font-weight:600; color: var(--text-secondary);">{{ $tPost->category->title }}</a></span>
                                <span>•</span>
                                <span>{{ $tPost->readTime() }}</span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p style="font-size:13px; color:var(--text-muted); padding:12px 0;">No other posts to display.</p>
                    @endforelse
                </div>
            </div>
            @endif

        </div>
    </div>
</section>

{{-- Slider JS — inline so it runs regardless of @include context --}}
@if ($total > 1)
<script>
(function () {
    var wrapper = document.getElementById('heroSlider');
    if (!wrapper) return;

    var slides  = wrapper.querySelectorAll('.hero-featured-card');
    var dots    = wrapper.querySelectorAll('.hero-dot');
    var counter = document.getElementById('heroCounter');
    var total   = slides.length;
    var current = 0;
    var animating = false;

    function goTo(n) {
        if (animating) return;
        var next = ((n % total) + total) % total;
        if (next === current) return;

        animating = true;
        slides[current].classList.remove('active');
        if (dots[current]) dots[current].classList.remove('active');

        current = next;
        slides[current].classList.add('active');
        if (dots[current]) dots[current].classList.add('active');
        if (counter) counter.textContent = (current + 1) + ' / ' + total;

        // Reset animating flag after animation duration
        setTimeout(function () { animating = false; }, 500);
    }

    document.getElementById('heroPrev').addEventListener('click', function (e) { e.preventDefault(); goTo(current - 1); });
    document.getElementById('heroNext').addEventListener('click', function (e) { e.preventDefault(); goTo(current + 1); });

    dots.forEach(function (dot, i) {
        dot.addEventListener('click', function () { goTo(i); });
    });

    var timer = setInterval(function () { goTo(current + 1); }, 6000);
    wrapper.addEventListener('mouseenter', function () { clearInterval(timer); });
    wrapper.addEventListener('mouseleave', function () {
        timer = setInterval(function () { goTo(current + 1); }, 6000);
    });
})();
</script>
@endif

@endif
