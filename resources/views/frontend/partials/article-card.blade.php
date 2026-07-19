{{-- Reusable article card row — same on ALL listing pages --}}
<div class="article-card-row">
    <div class="card-img-wrapper">
        <a href="{{ route('frontend.post', $post->slug) }}">
            <img src="{{ asset('uploads/post/' . $post->thumbnail) }}"
                 alt="{{ $post->title }}"
                 loading="lazy">
        </a>
    </div>
    <div class="card-content-wrapper">
        <div class="card-title">
            <h3><a href="{{ route('frontend.post', $post->slug) }}">{{ $post->title }}</a></h3>
        </div>
        <ul class="entry-meta">
            <li class="entry-cat">
                <a href="{{ route('frontend.category', $post->category->slug) }}" class="category-style-1" style="--cat-color: var(--cat-{{ \Illuminate\Support\Str::slug($post->category->slug) }});">
                    {{ $post->category->title }}
                </a>
            </li>
            <li class="post-date">
                <span class="line"></span>{{ $post->created_at->format('F d, Y') }}
            </li>
        </ul>
        <div class="card-excerpt">
            {{ $post->excerpt(120) }}
        </div>
        <div class="post-btn">
            <a href="{{ route('frontend.post', $post->slug) }}" class="btn-read-more">
                Continue Reading
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
        </div>
    </div>
</div>
