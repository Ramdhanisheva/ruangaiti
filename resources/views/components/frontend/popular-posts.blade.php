<div class="widget">
    <style>
    /* ── Popular Posts Tabs ─────────────────────────── */
    .popular-tabs-header {
        display: flex;
        gap: 2px;
        margin-bottom: 14px;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 0;
        overflow-x: auto;
        white-space: nowrap;
        scrollbar-width: none;
    }
    .popular-tabs-header::-webkit-scrollbar { display: none; }

    .popular-tab-btn {
        border: none;
        background: none;
        font-size: 11.5px;
        font-weight: 600;
        letter-spacing: 0.02em;
        color: var(--text-secondary);
        cursor: pointer;
        padding: 8px 12px;
        border-radius: 0;
        border-bottom: 2px solid transparent;
        margin-bottom: -1px;
        transition: color 0.18s ease, border-color 0.18s ease;
        text-transform: uppercase;
    }
    .popular-tab-btn:hover {
        color: var(--text-primary);
        border-bottom-color: var(--border-color);
    }
    .popular-tab-btn.active {
        color: var(--color-primary);
        border-bottom-color: var(--color-primary);
        font-weight: 700;
    }

    /* ── Tab Panes ──────────────────────────────────── */
    .popular-tab-pane {
        display: none;
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .popular-tab-pane.active {
        display: block;
        animation: fadeInPopular 0.2s ease-out;
    }
    @keyframes fadeInPopular {
        from { opacity: 0; transform: translateY(3px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ── Post Item ──────────────────────────────────── */
    .pop-post-item {
        display: flex;
        align-items: flex-start;
        gap: 11px;
        padding: 10px 0;
        border-bottom: 1px solid var(--border-color);
        text-decoration: none;
    }
    .pop-post-item:last-child { border-bottom: none; }
    .pop-post-thumb {
        width: 62px;
        height: 50px;
        object-fit: cover;
        border-radius: 6px;
        flex-shrink: 0;
        background: var(--bg-tertiary);
    }
    .pop-post-body { flex: 1; min-width: 0; }
    .pop-post-title {
        font-size: 12.5px;
        font-weight: 600;
        line-height: 1.45;
        color: var(--text-primary);
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        margin-bottom: 4px;
        transition: color 0.15s ease;
    }
    .pop-post-item:hover .pop-post-title { color: var(--color-primary); }
    .pop-post-meta {
        font-size: 10.5px;
        color: var(--text-muted);
    }
    .pop-empty {
        font-size: 12px;
        color: var(--text-muted);
        text-align: center;
        padding: 16px 0;
    }
    </style>

    <div class="widget-title">
        <h5>Popular Posts</h5>
    </div>

    @if($showTabs)
        {{-- Tab headers (shown only when enough data) --}}
        <div class="popular-tabs-header">
            <button class="popular-tab-btn active" data-tab="trending">Trending</button>
            <button class="popular-tab-btn" data-tab="viewed">Viewed</button>
            <button class="popular-tab-btn" data-tab="liked">Liked</button>
            <button class="popular-tab-btn" data-tab="rated">Rated</button>
        </div>

        {{-- Tab panes --}}
        <div class="popular-tabs-content">
            @foreach(['trending' => $trending, 'viewed' => $viewed, 'liked' => $liked, 'rated' => $rated] as $tabName => $list)
                <ul class="popular-tab-pane {{ $tabName === 'trending' ? 'active' : '' }}" id="pane-{{ $tabName }}">
                    @forelse ($list as $item)
                        <li>
                            <a href="{{ route('frontend.post', $item->slug) }}" class="pop-post-item">
                                <img
                                    src="{{ asset('uploads/post/'.$item->thumbnail) }}"
                                    alt="{{ $item->title }}"
                                    class="pop-post-thumb"
                                    loading="lazy"
                                />
                                <div class="pop-post-body">
                                    <div class="pop-post-title">{{ $item->title }}</div>
                                    <span class="pop-post-meta">{{ $item->created_at->diffForHumans() }}</span>
                                </div>
                            </a>
                        </li>
                    @empty
                        <li><div class="pop-empty">No articles recorded yet.</div></li>
                    @endforelse
                </ul>
            @endforeach
        </div>

        <script>
        (function() {
            const widget = document.currentScript.closest('.widget');
            const btns   = widget.querySelectorAll('.popular-tab-btn');
            const panes  = widget.querySelectorAll('.popular-tab-pane');

            btns.forEach(btn => {
                btn.addEventListener('click', () => {
                    btns.forEach(b  => b.classList.remove('active'));
                    panes.forEach(p => p.classList.remove('active'));
                    btn.classList.add('active');
                    const pane = widget.querySelector('#pane-' + btn.dataset.tab);
                    if (pane) pane.classList.add('active');
                });
            });
        })();
        </script>
    @else
        {{-- Single list mode when too few posts to differentiate tabs --}}
        <ul class="popular-tab-pane active" style="display: block;">
            @forelse ($trending as $item)
                <li>
                    <a href="{{ route('frontend.post', $item->slug) }}" class="pop-post-item">
                        <img
                            src="{{ asset('uploads/post/'.$item->thumbnail) }}"
                            alt="{{ $item->title }}"
                            class="pop-post-thumb"
                            loading="lazy"
                        />
                        <div class="pop-post-body">
                            <div class="pop-post-title">{{ $item->title }}</div>
                            <span class="pop-post-meta">{{ $item->created_at->diffForHumans() }}</span>
                        </div>
                    </a>
                </li>
            @empty
                <li><div class="pop-empty">No articles recorded yet.</div></li>
            @endforelse
        </ul>
    @endif
</div>
