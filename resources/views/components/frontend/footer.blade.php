<footer class="footer">
    <div class="container">
        <!-- Main Footer Grid -->
        <div class="footer-grid">

            <!-- Col 1: Brand + description -->
            <div class="footer-brand">
                <div class="logo" style="margin-bottom: 12px;">
                    <a href="{{ route("frontend.home") }}">
                        <img src="{{ asset("uploads/logo/".$sitesettings->logo_light) }}" alt="{{ $sitesettings->site_title }}" class="logo-dark" style="height: 30px;"/>
                        <img src="{{ asset("uploads/logo/".$sitesettings->logo_dark) }}" alt="{{ $sitesettings->site_title }}" class="logo-white" style="height: 30px;"/>
                    </a>
                </div>
                <p>{{ $sitesettings->description }}</p>
            </div>

            <!-- Col 2: Navigation menu -->
            <div class="footer-menu-column">
                <h6>Navigation</h6>
                <ul>
                    @if (count($menu) > 0)
                        @foreach ($menu as $item)
                        <li><a href="{{ $item["href"] }}">{{ $item["text"] }}</a></li>
                        @endforeach
                    @else
                        <li><a href="{{ route('frontend.home') }}">Home</a></li>
                    @endif
                </ul>
            </div>

            <!-- Col 3: Categories -->
            <div class="footer-menu-column">
                <h6>Categories</h6>
                <ul>
                    @forelse ($categories->take(5) as $cat)
                    <li>
                        <a href="{{ route('frontend.category', $cat->slug) }}" style="display:flex; justify-content:space-between; align-items:center; gap:8px;">
                            <span>{{ $cat->title }}</span>
                            <span style="font-size:11px; color:var(--text-muted); background:var(--bg-primary); padding:1px 7px; border-radius:999px;">{{ $cat->posts_count }}</span>
                        </a>
                    </li>
                    @empty
                    <li><a href="{{ route('frontend.home') }}">All Posts</a></li>
                    @endforelse
                </ul>
            </div>

            <!-- Col 4: Social Media from admin -->
            <div class="footer-menu-column">
                <h6>Follow Us</h6>
                @if ($socialmedia->count() > 0)
                <ul>
                    @foreach ($socialmedia as $media)
                    @php
                        $safeLink = $media->link;
                        if (str_contains(strtolower($safeLink), 'kontol') || !filter_var($safeLink, FILTER_VALIDATE_URL)) {
                            $safeLink = 'https://' . strtolower($media->platform ?? $media->title ?? 'facebook') . '.com';
                        }
                    @endphp
                    <li>
                        <a href="{{ $safeLink }}" target="_blank" rel="noopener" class="footer-social-link">
                            <span class="footer-social-icon">
                                <x-icon name="{{ strtolower($media->platform ?? $media->title) }}" width="15" height="15" />
                            </span>
                            <span>{{ $media->title }}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
                @else
                <p style="font-size:13px; color:var(--text-muted);">
                    Tambahkan akun sosial media melalui panel admin.
                </p>
                @endif
            </div>

        </div>

        <!-- Copyright Bar -->
        <div class="footer-copyright-bar">
            <p>{{ $sitesettings->copyright_text }}</p>
            <div class="footer-bottom-links">
                <a href="javascript:void(0)" onclick="document.getElementById('command-palette')?.classList.toggle('open')" style="margin-right:16px; font-size:13px; color:var(--text-muted);">Search (Ctrl+K)</a>
                <a href="#" onclick="window.scrollTo({top:0,behavior:'smooth'}); return false;" style="font-size:13px; color:var(--text-muted);">↑ Back to Top</a>
            </div>
        </div>
    </div>
</footer>
