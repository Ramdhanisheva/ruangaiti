<div class="widget">
    <div class="widget-title">
        <h5>Categories</h5>
    </div>
    @if ($categories->count() > 0)
    <ul class="widget-cat-list">
        @foreach ($categories as $category)
        <li>
            <a href="{{ route("frontend.category", $category->slug) }}">
                @if($category->icon)
                    <i class="{{ $category->icon }} mr-2 text-muted"></i>
                @endif
                {{ $category->title }}
                <span>{{ $category->posts_count }}</span>
            </a>
        </li>
        @endforeach
    </ul>
    @else
    <div style="color:var(--text-secondary); font-size:14px;">No category found!</div>
    @endif
</div>
