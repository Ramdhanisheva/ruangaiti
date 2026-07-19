<div class="widget">
    <div class="widget-title">
        <h5>Tags</h5>
    </div>
    <div class="tags-cloud">
        @forelse ($tags as $tag)
            <a href="{{ route("frontend.tag", $str::slug($tag->name)) }}" class="tag-badge">{{ $tag->name }}</a>
        @empty
            <div style="color:var(--text-secondary); font-size:14px;">No tag found!</div>
        @endforelse
    </div>
</div>
