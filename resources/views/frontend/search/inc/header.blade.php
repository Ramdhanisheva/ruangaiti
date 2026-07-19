<div class="page-hero-section">
    <div class="container-fluid">
        <div class="breadcrumb">
            <a href="{{ route('frontend.home') }}">Home</a>
            <x-icon name="chevron-right" width="12" height="12" />
            <span>Search</span>
        </div>
        <h1 class="page-hero-title">Search Results</h1>
        <p class="page-hero-subtitle">Showing articles matching "<strong>{{ $query }}</strong>"</p>
    </div>
</div>
