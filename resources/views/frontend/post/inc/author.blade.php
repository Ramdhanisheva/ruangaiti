<div class="author-bio-card-wrapper">
    <style>
    /* Bulletproof Embedded Styles for Author Card to bypass browser asset caching */
    .author-bio-card-wrapper {
        margin-top: 32px;
        width: 100%;
        position: relative;
    }
    .author-bio-card {
        position: relative;
        display: flex !important;
        flex-direction: row !important;
        gap: 20px !important;
        background-color: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-card);
        padding: 24px !important;
        box-shadow: var(--shadow-sm);
        min-height: 120px;
        align-items: flex-start !important;
    }
    .author-card-overlay-link {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 1;
        border-radius: var(--radius-card);
    }
    .author-card-avatar-side {
        flex-shrink: 0 !important;
        width: 72px !important;
        height: 72px !important;
    }
    .author-card-avatar-img {
        width: 72px !important;
        height: 72px !important;
        min-width: 72px !important;
        min-height: 72px !important;
        border-radius: 50% !important;
        object-fit: cover !important;
        border: 2px solid var(--border-color) !important;
        box-shadow: var(--shadow-sm) !important;
    }
    .author-card-content-side {
        flex-grow: 1 !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 8px !important;
        min-width: 0 !important;
    }
    .author-card-header-row {
        display: flex !important;
        flex-direction: row !important;
        justify-content: space-between !important;
        align-items: flex-start !important;
        gap: 12px !important;
    }
    .author-card-name {
        font-size: 18px;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
        line-height: 1.2;
        word-break: keep-all !important;
        overflow-wrap: break-word !important;
        hyphens: none !important;
        white-space: normal !important;
    }
    .author-card-role {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--color-primary);
        display: inline-block;
        margin-top: 2px;
    }
    .author-card-arrow {
        font-size: 16px;
        color: var(--text-muted);
        flex-shrink: 0;
        margin-top: 2px;
    }
    .author-card-bio {
        font-size: 14px;
        color: var(--text-secondary);
        line-height: 1.5;
    }
    .author-card-bio p {
        margin: 0;
    }
    .author-card-footer-row {
        display: flex !important;
        flex-direction: row !important;
        justify-content: space-between !important;
        align-items: center !important;
        gap: 16px !important;
        margin-top: 4px;
    }
    .author-card-stat {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-muted);
        display: inline-flex;
        align-items: center;
    }
    .author-card-socials {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        gap: 8px !important;
        position: relative;
        z-index: 2;
    }
    .author-social-btn {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background-color: var(--bg-secondary);
        border: 1px solid var(--border-color);
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }
    .author-social-btn:hover {
        background-color: var(--color-primary);
        border-color: var(--color-primary);
        color: #FFFFFF !important;
    }
    @media (max-width: 576px) {
        .author-bio-card {
            flex-direction: column !important;
            padding: 16px !important;
            gap: 16px !important;
        }
        .author-card-avatar-side {
            align-self: center !important;
        }
        .author-card-header-row {
            text-align: center !important;
            flex-direction: column !important;
            align-items: center !important;
            gap: 4px !important;
        }
        .author-card-arrow {
            display: none !important;
        }
        .author-card-bio {
            text-align: center !important;
        }
        .author-card-footer-row {
            flex-direction: column !important;
            gap: 12px !important;
            align-items: center !important;
            border-top: 1px solid var(--border-color);
            padding-top: 12px;
            margin-top: 4px;
        }
    }
    </style>

    <div class="author-bio-card">
        <!-- Stretched link for full card clickability without nested <a> tags (Android compatibility fix) -->
        <a href="{{ route('frontend.user', $post->user->username) }}" class="author-card-overlay-link" aria-label="Lihat profil {{ $post->user->name }}"></a>
        
        <div class="author-card-avatar-side">
            <img
                class="author-card-avatar-img"
                src="{{ asset('uploads/author/' . ($post->user->profile ?? 'default.webp')) }}"
                alt="{{ $post->user->name }}"
            >
        </div>
        
        <div class="author-card-content-side">
            <div class="author-card-header-row">
                <div>
                    <h4 class="author-card-name">{{ $post->user->name }}</h4>
                    <span class="author-card-role">{{ $post->user->tagline ?? 'Kontributor' }}</span>
                </div>
                <div class="author-card-arrow">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>
            
            <div class="author-card-bio">
                @if ($post->user->about)
                    <p>{{ $post->user->about }}</p>
                @else
                    <p>Writer, developer, and contributor at {{ config('app.name') }}.</p>
                @endif
            </div>

            <div class="author-card-footer-row">
                <span class="author-card-stat">
                    <i class="fas fa-file-alt mr-1"></i> {{ $post->user->posts()->where('status', true)->count() }} Artikel
                </span>
                
                {{-- Social Links --}}
                <div class="author-card-socials">
                    @if ($post->user->facebook)
                        <a href="{{ $post->user->facebook }}" target="_blank" class="author-social-btn" aria-label="Facebook">
                            <x-icon name="facebook" width="14" height="14" />
                        </a>
                    @endif
                    @if ($post->user->twitter)
                        <a href="{{ $post->user->twitter }}" target="_blank" class="author-social-btn" aria-label="Twitter">
                            <x-icon name="twitter" width="14" height="14" />
                        </a>
                    @endif
                    @if ($post->user->instagram)
                        <a href="{{ $post->user->instagram }}" target="_blank" class="author-social-btn" aria-label="Instagram">
                            <x-icon name="instagram" width="14" height="14" />
                        </a>
                    @endif
                    @if ($post->user->linkedin)
                        <a href="{{ $post->user->linkedin }}" target="_blank" class="author-social-btn" aria-label="LinkedIn">
                            <x-icon name="linkedin" width="14" height="14" />
                        </a>
                    @endif
                    @if ($post->user->youtube)
                        <a href="{{ $post->user->youtube }}" target="_blank" class="author-social-btn" aria-label="YouTube">
                            <x-icon name="youtube" width="14" height="14" />
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
