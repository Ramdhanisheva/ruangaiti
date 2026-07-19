<header class="header fixed-top">
    <div class="container-fluid">
        <div class="header-area">
            <!-- Brand Logo -->
            <div class="logo">
                <a href="{{ route("frontend.home") }}">
                    <img src="{{ asset("uploads/logo/".$sitesettings->logo_light) }}" alt="{{ $sitesettings->site_title }}" class="logo-dark"/>
                    <img src="{{ asset("uploads/logo/".$sitesettings->logo_dark) }}" alt="{{ $sitesettings->site_title }}" class="logo-white"/>
                </a>
            </div>

            <!-- Desktop Navigation Menu -->
            <div class="header-navbar">
                <nav class="navbar">
                    @if (count($menu) > 0)
                    <ul class="navbar-nav">
                        @foreach ($menu as $item)
                        <li class="nav-item">
                            <a class="nav-link{{ request()->url() == $item["href"] ? " active" : "" }}" href="{{ $item["href"] }}">{{ $item["text"] }}</a>
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </nav>
            </div>

            <!-- Navbar Actions -->
            <div class="header-right">
                <!-- Search Button (Launches Command Palette) -->
                <button type="button" class="search-icon-btn" aria-label="Search">
                    <x-icon name="search" width="18" height="18" />
                </button>

                <!-- Theme Toggle Button -->
                <button type="button" class="theme-toggle-btn" aria-label="Toggle Theme">
                    <x-icon name="sun" class="icon-light" width="18" height="18" />
                    <x-icon name="moon" class="icon-dark" width="18" height="18" />
                </button>

                <!-- CTA Button -->
                @auth
                <div class="botton-sub">
                    <a href="{{ route("dashboard.home") }}" class="btn-primary">Dashboard</a>
                </div>
                @endauth

                <!-- Mobile Hamburger Toggler -->
                <button class="navbar-toggler" type="button" aria-label="Toggle navigation">
                    <x-icon name="menu" width="22" height="22" />
                </button>
            </div>
        </div>
    </div>
</header>
