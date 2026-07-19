<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>

    <!-- Performance: DNS Prefetch & Preconnect for Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">

    @php
        $siteSettings = config('app.sitesettings')::first();
        $siteTitle = $siteSettings?->site_title ?? config('app.name', 'Blog');
        $siteDescription = $siteSettings?->description ?? '';
        $siteLogo = $siteSettings?->logo_light ? asset('uploads/logo/'.$siteSettings->logo_light) : '';
        $siteOgBanner = asset('assets/frontend/og_banner.jpg');
    @endphp

    <!-- SEO Meta Tags -->
    <meta name="google-site-verification" content="ZYvyRuK9ErF8B0y6i9o7EySupfb7FVI8VmTILe_76IM" />
    <meta name="google-site-verification" content="Ncyetsu0EniYjP3GSgtE80FxINzYOrEoI2V5sJBPnGg" />
    <meta name="description" content="@yield('meta_description', $siteDescription)">
    <meta name="keywords" content="@yield('meta_keywords', 'Ruang IT, RuangIT, Ruang AiTi, RuangAiTi, Ruang AI TI, ruang it, ruang aiti, ruangai, blog IT Indonesia, blog teknologi Indonesia')">
    <meta name="robots" content="@yield('meta_robots', 'index, follow')">
    <link rel="canonical" href="{{ request()->url() }}">
    <link rel="alternate" type="application/rss+xml" title="RSS Feed for {{ $siteTitle }}" href="{{ route('frontend.feed') }}" />

    <!-- Open Graph / WhatsApp / Facebook -->
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="{{ request()->url() }}">
    <meta property="og:title" content="@yield('title', $siteTitle)">
    <meta property="og:description" content="@yield('meta_description', $siteDescription)">
    <meta property="og:image" content="@yield('og_image', $siteOgBanner)">
    <meta property="og:image:secure_url" content="@yield('og_image', $siteOgBanner)">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="@yield('title', $siteTitle)">
    <meta property="og:site_name" content="{{ $siteTitle }}">
    <meta property="og:locale" content="{{ app()->getLocale() === 'id' ? 'id_ID' : (app()->getLocale() === 'en' ? 'en_US' : app()->getLocale()) }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ request()->url() }}">
    <meta name="twitter:title" content="@yield('title', $siteTitle)">
    <meta name="twitter:description" content="@yield('meta_description', $siteDescription)">
    <meta name="twitter:image" content="@yield('og_image', $siteOgBanner)">
    <meta name="twitter:image:alt" content="@yield('title', $siteTitle)">

    <!-- Favicon and App Icons -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="48x48" href="{{ asset('favicon-48x48.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="theme-color" content="#0f1934">

    <!-- Structured Data (JSON-LD) -->
    @yield('structured_data')

    @stack('head')

    <title>@yield("title")</title>
    
    <!-- Typography and Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{ asset("assets/frontend/css/bootstrap-grid.min.css") }}"/>
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300;0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;1,14..32,400&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300;0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;1,14..32,400&display=swap"></noscript>
    <link rel="stylesheet" href="{{ asset("assets/frontend/css/style.css") }}?v=3.8"/>
    
    <!-- Theme Initialization to avoid white flashes -->
    <script>
        const theme = localStorage.getItem('theme') || 'system';
        if (theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.setAttribute('data-theme', 'dark');
        } else {
            document.documentElement.setAttribute('data-theme', 'light');
        }
    </script>
</head>
<body>
    <!-- Preloader -->
    <div class="loader">
        <div class="loader-element"></div>
    </div>



    <!-- Main Header Navbar Component -->
    @if(!isset($isBlankTemplate) || !$isBlankTemplate)
        <x-frontend.header/>
    @endif

    <!-- Main Content Yield -->
    @yield("content")

    <!-- Footer Component -->
    @if(!isset($isBlankTemplate) || !$isBlankTemplate)
        <x-frontend.footer/>
    @endif

    @if(!isset($isBlankTemplate) || !$isBlankTemplate)
        <!-- Command Palette (Ctrl + K Modal) -->
        <div class="command-palette-overlay" id="command-palette" role="dialog" aria-modal="true" aria-label="Search command palette">
            <div class="command-palette-modal">
                <div class="command-palette-input-wrapper" role="search">
                    <x-icon name="search" width="22" height="22" style="color: var(--text-secondary)" />
                    <label for="command-palette-search-input" class="sr-only">Search articles</label>
                    <input type="text" id="command-palette-search-input" placeholder="Search articles..." autocomplete="off" aria-label="Search articles">
                    <span class="command-palette-shortcut-hint" aria-hidden="true">Ctrl + K</span>
                </div>
                <div class="command-palette-results" role="listbox" aria-label="Search results">
                    <!-- Dynamic Search Results -->
                </div>
            </div>
        </div>

        <!-- Mobile Navigation Drawer -->
        <div class="drawer-overlay"></div>
        <div class="mobile-drawer">
            <div class="mobile-drawer-header">
                <div class="logo">
                    <img src="{{ asset("uploads/logo/".(config('app.sitesettings')::first()?->logo_light ?? '')) }}" alt="{{ config('app.sitesettings')::first()?->site_title ?? 'RuangAiTi' }} Logo" class="logo-dark">
                    <img src="{{ asset("uploads/logo/".(config('app.sitesettings')::first()?->logo_dark ?? '')) }}" alt="{{ config('app.sitesettings')::first()?->site_title ?? 'RuangAiTi' }} Logo" class="logo-white">
                </div>
                <button class="mobile-drawer-close" aria-label="Close menu">
                    <x-icon name="x" width="20" height="20" />
                </button>
            </div>
            <div class="mobile-nav-links">
                @php
                    $headerMenu = json_decode(config('app.menus')::first()?->header_menu ?? '[]', true);
                @endphp
                @foreach($headerMenu as $item)
                    <a href="{{ $item['href'] }}">{{ $item['text'] }}</a>
                @endforeach
                @auth
                    <a href="{{ route('dashboard.home') }}">Dashboard</a>
                @endauth
            </div>
        </div>
    @endif

    <!-- Core Scripts -->
    <script src="{{ asset("assets/frontend/js/main.js") }}?v=3.7"></script>
    <script src="{{ asset("assets/frontend/js/tracker.js") }}?v=3.6"></script>
    <script src="{{ asset("assets/frontend/js/like.js") }}?v=3.6"></script>
    @yield("script")
</body>
</html>
