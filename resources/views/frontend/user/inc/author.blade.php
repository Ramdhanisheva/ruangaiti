<section class="user-profile-header">
    <div class="container" style="max-width: 800px; text-align: center;">
        <div style="margin-bottom: var(--space-3); display: flex; justify-content: center;">
            <img src="{{ asset("uploads/author/".($user->profile ?? "default.webp")) }}" alt="{{ $user->name }}" style="width: 96px; height: 96px; border-radius: 50%; object-fit: cover; border: 2px solid var(--border-color); box-shadow: var(--shadow-sm);"/>
        </div>
        <h1 style="font-size: clamp(24px, 6vw, 32px); font-weight: 700; margin-bottom: 4px;">{{ $user->name }}</h1>
        @if(!empty($user->tagline))
            <div class="author-profile-tagline" style="font-size: 13px; font-weight: 600; color: var(--color-primary); margin-bottom: 12px; letter-spacing: 0.05em; text-transform: uppercase;">{{ $user->tagline }}</div>
        @endif
        <p style="color: var(--text-secondary); max-width: 600px; margin: 0 auto 16px; font-size: 15px; line-height: 1.6;">
            @if ($user->about)
                {{ $user->about }}
            @else
                Writer, developer, and contributor at {{ config('app.name') }}.
            @endif
        </p>
        
        {{-- Social Links --}}
        <div style="display: flex; justify-content: center; gap: 10px;">
            @if ($user->facebook)
                <a href="{{ $user->facebook }}" target="_blank" class="action-btn" aria-label="Facebook"><x-icon name="facebook" width="16" height="16" /></a>
            @endif
            @if ($user->twitter)
                <a href="{{ $user->twitter }}" target="_blank" class="action-btn" aria-label="Twitter"><x-icon name="twitter" width="16" height="16" /></a>
            @endif
            @if ($user->instagram)
                <a href="{{ $user->instagram }}" target="_blank" class="action-btn" aria-label="Instagram"><x-icon name="instagram" width="16" height="16" /></a>
            @endif
            @if ($user->linkedin)
                <a href="{{ $user->linkedin }}" target="_blank" class="action-btn" aria-label="LinkedIn"><x-icon name="linkedin" width="16" height="16" /></a>
            @endif
            @if ($user->youtube)
                <a href="{{ $user->youtube }}" target="_blank" class="action-btn" aria-label="YouTube"><x-icon name="youtube" width="16" height="16" /></a>
            @endif
        </div>

        @php
            $stats = $user->getStats();
        @endphp

        {{-- Author Stats Card --}}
        <div class="author-stats-container" style="max-width: 480px; width: calc(100% - 32px); box-sizing: border-box; margin: var(--space-4) auto 0; padding: 20px; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-card); box-shadow: var(--shadow-sm); text-align: left;">
            <h3 style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary); margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px; text-align: left;">Published Content</h3>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 14px;">
                    <span style="color: var(--text-secondary); font-weight: 500;">Articles</span>
                    <strong style="color: var(--text-primary); font-size: 15px;">{{ number_format($stats['articles_count']) }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 14px;">
                    <span style="color: var(--text-secondary); font-weight: 500;">Roadmaps</span>
                    <strong style="color: var(--text-primary); font-size: 15px;">{{ number_format($stats['roadmaps_count']) }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 14px;">
                    <span style="color: var(--text-secondary); font-weight: 500;">Total Views</span>
                    <strong style="color: var(--text-primary); font-size: 15px;">{{ number_format($stats['total_views']) }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 14px;">
                    <span style="color: var(--text-secondary); font-weight: 500;">Likes</span>
                    <strong style="color: var(--text-primary); font-size: 15px;">{{ number_format($stats['likes_count']) }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 14px;">
                    <span style="color: var(--text-secondary); font-weight: 500;">Helpful</span>
                    <strong style="color: var(--text-primary); font-size: 15px;">{{ $stats['helpful_rating'] }}%</strong>
                </div>
                @if ($stats['last_content'])
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; width: 100%; min-width: 0; font-size: 13px; margin-top: 8px; padding-top: 12px; border-top: 1px solid var(--border-color);">
                    <span style="color: var(--text-secondary); font-weight: 500; white-space: nowrap; flex-shrink: 0;">Last Published</span>
                    <a href="{{ $stats['last_content'] instanceof \App\Models\Roadmap ? route('frontend.roadmap.show', $stats['last_content']->slug) : route('frontend.post', $stats['last_content']->slug) }}" style="color: var(--color-primary); font-weight: 600; text-decoration: none; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; text-align: right; flex-grow: 1; min-width: 0;" title="{{ $stats['last_content']->title }}">
                        {{ $stats['last_content']->title }}
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>
