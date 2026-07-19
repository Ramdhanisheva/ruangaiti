<div class="page-hero-section">
    <div class="container-fluid">
        <div class="breadcrumb">
            <a href="{{ route('frontend.home') }}">Home</a>
            <x-icon name="chevron-right" width="12" height="12" />
            <span>Tags</span>
        </div>
        <h1 class="page-hero-title">#{{ $tag }}</h1>
        <p class="page-hero-subtitle">Showing all articles tagged with <strong>{{ $tag }}</strong></p>
    </div>
</div>
