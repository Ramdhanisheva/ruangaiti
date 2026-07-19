@extends('frontend.master')

@section('title', $meta_title)
@section('meta_description', $meta_description)
@section('og_image', $meta_image)
@section('canonical', $meta_url)

@push('head')
<link rel="stylesheet" href="{{ asset('assets/frontend/css/roadmap.css') }}">
<link rel="stylesheet" href="{{ asset('assets/frontend/css/roadmap-detail.css') }}">
<link rel="stylesheet" href="{{ asset('assets/frontend/css/roadmap-timeline.css') }}">
<link rel="stylesheet" href="{{ asset('assets/frontend/css/components/badges.css') }}">
<link rel="stylesheet" href="{{ asset('assets/frontend/css/components/buttons.css') }}">
<link rel="stylesheet" href="{{ asset('assets/frontend/css/components/breadcrumb.css') }}">
<link rel="stylesheet" href="{{ asset('assets/frontend/css/roadmap-responsive.css') }}">
<style>
    html {
        scroll-behavior: smooth;
    }
</style>
<meta name="page-entity-type" content="App\Models\Roadmap">
<meta name="page-entity-id" content="{{ $roadmap->id }}">
@endpush

@section('structured_data')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "CollectionPage",
      "@id": "{{ $meta_url }}#webpage",
      "url": "{{ $meta_url }}",
      "name": "{{ $meta_title }}",
      "description": "{{ $meta_description }}",
      "isPartOf": {
        "@type": "WebSite",
        "@id": "{{ url('/') }}#website",
        "url": "{{ url('/') }}",
        "name": "{{ config('app.sitesettings')::first()->site_title ?? 'RuangAiTi' }}"
      }
    },
    {
      "@type": "BreadcrumbList",
      "@id": "{{ $meta_url }}#breadcrumb",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "name": "Home",
          "item": "{{ route('frontend.home') }}"
        },
        {
          "@type": "ListItem",
          "position": 2,
          "name": "Roadmap",
          "item": "{{ route('frontend.roadmap') }}"
        },
        {
          "@type": "ListItem",
          "position": 3,
          "name": "{{ $roadmap->title }}"
        }
      ]
    },
    {
      "@type": "EducationalOccupationalProgram",
      "name": "{{ $roadmap->title }}",
      "description": "{{ $roadmap->description }}",
      "educationalCredentialAwarded": "Certificate of Completion",
      "provider": {
        "@type": "Organization",
        "name": "{{ config('app.sitesettings')::first()?->site_title ?? config('app.name') }}",
        "url": "{{ url('/') }}"
      },
      "programPrerequisites": [
        @foreach($prerequisites as $prereq)
          "{{ $prereq }}"{{ !$loop->last ? ',' : '' }}
        @endforeach
      ],
      "occupationalCategory": "{{ $roadmap->category ? $roadmap->category->title : 'IT & Technology' }}"
    }
  ]
}
</script>
@endsection

@section('content')
<main class="roadmap-container">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ul class="roadmap-breadcrumb">
                <li><a href="{{ route('frontend.home') }}">Home</a></li>
                <li><a href="{{ route('frontend.roadmap') }}">Roadmap</a></li>
                <li>{{ $roadmap->title }}</li>
            </ul>
        </nav>

        <!-- Hero Section -->
        <header class="roadmap-detail-hero-vertical">
            <div class="roadmap-detail-hero-content">
                @php
                    $totalMinutes = $roadmap->estimatedMinutes();
                    $hours = floor($totalMinutes / 60);
                    $mins = $totalMinutes % 60;
                    $timeString = $hours > 0 ? "±{$hours} Jam " . ($mins > 0 ? "{$mins} Menit" : "") : "{$mins} Menit";
                @endphp

                <div class="roadmap-detail-meta-row">
                    <span class="badge-roadmap badge-roadmap-{{ strtolower($roadmap->difficulty) }}">{{ $roadmap->difficulty }}</span>
                    <span class="roadmap-date-update">
                        <i class="far fa-calendar-alt mr-1"></i> Diperbarui {{ \Carbon\Carbon::parse($roadmap->lastUpdated())->translatedFormat('d F Y') }}
                    </span>
                </div>

                <h1>{{ $roadmap->title }}</h1>
                <p class="roadmap-detail-description">{{ $roadmap->description }}</p>

                <div class="roadmap-detail-stats-row">
                    <span>{{ $roadmap->modulesCount() }} Modules</span>
                    <span class="stats-separator">•</span>
                    <span>{{ $roadmap->articlesCount() }} Lessons</span>
                    <span class="stats-separator">•</span>
                    <span>{{ $timeString }}</span>
                </div>

                @if($roadmap->modules->count() > 0)
                    <div class="roadmap-hero-action-btn">
                        <a href="#module-{{ $roadmap->modules->first()->id }}" class="btn-roadmap-primary">
                            Mulai Belajar
                        </a>
                    </div>
                @endif
            </div>

            @if($roadmap->cover)
                <div class="roadmap-detail-hero-cover-banner">
                    <img src="{{ asset('uploads/roadmap/' . $roadmap->cover) }}" alt="{{ $roadmap->title }}">
                </div>
            @endif
        </header>

        <div class="roadmap-detail-layout">
            <!-- Sidebar Area: Outline (Top stacked on mobile) -->
            <aside class="roadmap-sidebar-outline-card">
                <div class="roadmap-course-sidebar">
                    <div class="roadmap-sidebar-header">
                        📚 Course Outline
                    </div>

                    @if($roadmap->modules->count() > 0)
                    <div class="roadmap-sidebar-divider"></div>
                    <div class="roadmap-sidebar-section-content">
                        <nav class="roadmap-nav-sidebar">
                            @foreach($roadmap->modules as $idx => $module)
                                <a href="#module-{{ $module->id }}" class="roadmap-nav-item">
                                    <span class="roadmap-nav-number">{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                    <span class="roadmap-nav-title">{{ $module->title }}</span>
                                </a>
                            @endforeach
                        </nav>
                    </div>
                    @endif
                </div>
            </aside>


            <!-- Main Content Area (Right Column on desktop) -->
            <div class="roadmap-main-column">
                <!-- Modules List -->
                @forelse($roadmap->modules as $index => $module)
                    <section id="module-{{ $module->id }}" class="roadmap-detail-module-card">
                        <div class="roadmap-detail-module-header">
                            <div class="roadmap-detail-module-header-title">
                                <span class="module-number">MODULE {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                <h3>{{ $module->title }}</h3>
                                @if($module->subtitle)
                                    <p class="module-subtitle">{{ $module->subtitle }}</p>
                                @endif
                            </div>
                            <span class="badge badge-dark" style="background: rgba(255,255,255,0.05); color: #cbd5e1; border: 1px solid rgba(255,255,255,0.05);">
                                {{ $module->posts->count() }} Lessons
                            </span>
                        </div>

                        <div class="roadmap-detail-module-body">
                            @if($module->description)
                                <p class="roadmap-detail-module-description">{{ $module->description }}</p>
                            @endif

                            <div class="roadmap-lessons-list">
                                @forelse($module->posts as $lessonIndex => $post)
                                    <a href="{{ route('frontend.post', $post->slug) }}?roadmap={{ $roadmap->slug }}" class="roadmap-lesson-row">
                                        <div class="roadmap-lesson-row-left">
                                            @if($post->thumbnail && file_exists(public_path('uploads/post/' . $post->thumbnail)))
                                                <img src="{{ asset('uploads/post/' . $post->thumbnail) }}" class="lesson-row-thumbnail" alt="{{ $post->title }}">
                                            @else
                                                <div class="lesson-row-icon-fallback">
                                                    <i class="far fa-file-alt"></i>
                                                </div>
                                            @endif
                                            <span class="roadmap-lesson-title">{{ $post->title }}</span>
                                        </div>
                                        <div class="roadmap-lesson-row-right">
                                            <span class="roadmap-lesson-time">Estimasi {{ $post->readTime() }}</span>
                                            <i class="fas fa-chevron-right fa-xs"></i>
                                        </div>
                                    </a>
                                @empty
                                    <!-- Inner Empty State -->
                                    <div class="text-center py-4 text-muted">
                                        Belum ada artikel dalam modul ini.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </section>
                @empty
                    <!-- Empty State for Modules -->
                    <div class="text-center py-5" style="background: rgba(30,41,59,0.2); border: 1px solid rgba(255,255,255,0.05); border-radius: 12px;">
                        <i class="fas fa-layer-group fa-3x text-muted mb-3"></i>
                        <h4 class="text-white mb-2">Belum ada modul.</h4>
                        <p class="text-muted mb-0">Roadmap ini sedang dalam proses penyusunan modul kurikulum.</p>
                    </div>
                @endforelse

                <!-- Prerequisites and Outcomes (Moved to bottom of main column) -->
                @if(count($prerequisites) > 0 || count($learning_outcomes) > 0)
                <div class="roadmap-detail-bottom-card mt-5">
                    <div class="roadmap-course-sidebar">
                        <div class="roadmap-sidebar-header mb-4">
                            Requirements & Outcomes
                        </div>
                        
                        <div class="row">
                            <!-- Section: Prerequisites -->
                            @if(count($prerequisites) > 0)
                            <div class="col-md-6 mb-4 mb-md-0">
                                <h3 class="roadmap-section-title mb-3" style="font-size: 1.05rem; font-weight: 600; color: var(--rm-text);">Prerequisites</h3>
                                <ul class="roadmap-overview-list pl-0" style="list-style: none;">
                                    @foreach($prerequisites as $prereq)
                                        <li class="d-flex align-items-start mb-2" style="font-size: 0.95rem; color: var(--rm-text); opacity: 0.85;">
                                            <span class="roadmap-sidebar-dot mr-2 mt-2" style="background: #3b82f6; width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0;"></span> 
                                            <span>{{ $prereq }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            <!-- Section: Outcomes -->
                            @if(count($learning_outcomes) > 0)
                            <div class="col-md-6">
                                <h3 class="roadmap-section-title mb-3" style="font-size: 1.05rem; font-weight: 600; color: var(--rm-text);">What you'll learn</h3>
                                <ul class="roadmap-overview-list pl-0" style="list-style: none;">
                                    @foreach($learning_outcomes as $outcome)
                                        <li class="d-flex align-items-start mb-2" style="font-size: 0.95rem; color: var(--rm-text); opacity: 0.85;">
                                            <span class="roadmap-sidebar-dot mr-2 mt-2" style="background: #10b981; width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0;"></span> 
                                            <span>{{ $outcome }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</main>
@endsection
