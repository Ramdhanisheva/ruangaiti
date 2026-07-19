@extends('frontend.master')

@section('title', $meta_title)
@section('meta_description', $meta_description)
@section('canonical', $meta_url)

@push('head')
<link rel="stylesheet" href="{{ asset('assets/frontend/css/roadmap.css') }}">
<link rel="stylesheet" href="{{ asset('assets/frontend/css/roadmap-card.css') }}">
<link rel="stylesheet" href="{{ asset('assets/frontend/css/components/badges.css') }}">
<link rel="stylesheet" href="{{ asset('assets/frontend/css/components/buttons.css') }}">
<link rel="stylesheet" href="{{ asset('assets/frontend/css/roadmap-responsive.css') }}">
@endpush

@section('content')
<main class="roadmap-container">
    <div class="container">
        <!-- Hero Section -->
        <div class="roadmap-header">
            <h1>Roadmap Belajar IT</h1>
            <p>Belajar IT secara terstruktur melalui kumpulan artikel yang disusun menjadi jalur belajar yang mudah diikuti.</p>
        </div>

        <!-- Filters Section -->
        <form action="{{ route('frontend.roadmap') }}" method="GET" class="roadmap-filter-section">
            <div class="roadmap-search-box">
                <i class="fas fa-search"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari jalur belajar..." aria-label="Cari jalur belajar">
            </div>
            
            <div class="roadmap-filters-wrap">
                <select name="difficulty" class="roadmap-filter-select" onchange="this.form.submit()" aria-label="Filter Kesulitan">
                    <option value="">Semua Kesulitan</option>
                    <option value="Beginner" {{ request('difficulty') == 'Beginner' ? 'selected' : '' }}>Beginner</option>
                    <option value="Intermediate" {{ request('difficulty') == 'Intermediate' ? 'selected' : '' }}>Intermediate</option>
                    <option value="Advanced" {{ request('difficulty') == 'Advanced' ? 'selected' : '' }}>Advanced</option>
                </select>

                <select name="category" class="roadmap-filter-select" onchange="this.form.submit()" aria-label="Filter Kategori">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->title }}</option>
                    @endforeach
                </select>

                @if(request('q') || request('difficulty') || request('category'))
                    <a href="{{ route('frontend.roadmap') }}" class="btn-roadmap-outline" style="min-height: 45px; padding: 0 16px;">Reset</a>
                @endif
            </div>
        </form>

        <!-- Grid Roadmaps -->
        @if($roadmaps->count() > 0)
            <div class="roadmap-grid">
                @foreach($roadmaps as $roadmap)
                    @php
                        $totalMinutes = $roadmap->estimatedMinutes();
                        $hours = floor($totalMinutes / 60);
                        $mins = $totalMinutes % 60;
                        $timeString = $hours > 0 ? "±{$hours} Jam" : "{$mins} Menit";
                    @endphp
                     <article class="public-roadmap-card card-roadmap-glow">
                        <div class="roadmap-card-img-wrapper">
                            @if($roadmap->cover)
                                <img src="{{ asset('uploads/roadmap/' . $roadmap->cover) }}" alt="{{ $roadmap->title }}" loading="lazy">
                            @else
                                <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);"></div>
                            @endif
                        </div>

                        <div class="roadmap-card-body">
                            <div class="roadmap-card-category">{{ $roadmap->category ? $roadmap->category->title : 'Jalur Belajar' }}</div>
                            <h2 class="roadmap-card-title">
                                <i class="{{ $roadmap->icon ?: 'fas fa-route' }}" style="color: #3b82f6; margin-right: 6px; font-size: 1.1rem;"></i>
                                {{ $roadmap->title }}
                            </h2>
                            <p class="roadmap-card-description">{{ $roadmap->description }}</p>

                            <div class="roadmap-card-stats">
                                <span class="badge-roadmap badge-roadmap-{{ strtolower($roadmap->difficulty) }}">{{ $roadmap->difficulty }}</span>
                                <span><i class="fas fa-layer-group"></i> {{ $roadmap->modulesCount() }} Modul</span>
                                <span><i class="fas fa-file-alt"></i> {{ $roadmap->articlesCount() }} Artikel</span>
                                <span><i class="fas fa-clock"></i> {{ $timeString }}</span>
                            </div>
                        </div>

                        <div class="roadmap-card-footer">
                            <a href="{{ route('frontend.roadmap.show', $roadmap->slug) }}" class="btn-roadmap-primary w-full">Mulai Belajar</a>
                        </div>
                    </article>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-5">
                {{ $roadmaps->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-5" style="background: rgba(30,41,59,0.2); border: 1px solid rgba(255,255,255,0.05); border-radius: 12px;">
                <i class="fas fa-route fa-3x text-muted mb-3"></i>
                <h3 class="text-white mb-2">Belum ada roadmap.</h3>
                <p class="text-muted mb-0">Jalur belajar yang Anda cari belum tersedia. Silakan hubungi kami untuk saran topik baru.</p>
            </div>
        @endif
    </div>
</main>
@endsection
