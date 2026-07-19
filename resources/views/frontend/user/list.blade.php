@extends("frontend.master")

@section("title", "Daftar Anggota - " . config('app.sitesettings')::first()?->site_title)
@section("meta_description", 'Temui tim penulis dan kontributor konten terbaik di ' . config('app.sitesettings')::first()?->site_title)

@section("content")
<div class="page-hero-section">
    <div class="container-fluid">
        <div class="breadcrumb">
            <a href="{{ route('frontend.home') }}">Home</a>
            <x-icon name="chevron-right" width="12" height="12" />
            <span>Members</span>
        </div>
        <h1 class="page-hero-title">Tim & Penulis</h1>
        <p class="page-hero-subtitle">Para kontributor artikel dan tutorial teknologi di <strong>{{ config('app.sitesettings')::first()?->site_title ?? config('app.name') }}</strong></p>
    </div>
</div>

<section class="home-content-section" style="padding: var(--space-4) 0 var(--space-5);">
    <div class="container">
        <div class="members-grid">
            @forelse($members as $member)
                <div class="member-card">
                    <div class="member-card-banner"></div>
                    <div class="member-card-avatar-wrapper">
                        @if($member->profile)
                            <img src="{{ asset('uploads/author/' . $member->profile) }}" alt="{{ $member->name }}">
                        @else
                            <img src="{{ asset('uploads/author/default.webp') }}" alt="{{ $member->name }}">
                        @endif
                    </div>
                    <div class="member-card-body">
                        <div class="member-role-badge">
                            @if($member->role == \App\Models\User::IS_ADMIN)
                                <span class="role-admin">Admin</span>
                            @else
                                <span class="role-author">Author</span>
                            @endif
                        </div>
                        <h3 class="member-name">
                            <a href="{{ route('frontend.user', $member->username) }}">{{ $member->name }}</a>
                        </h3>
                        @if($member->tagline)
                            <p class="member-tagline">{{ $member->tagline }}</p>
                        @endif
                        
                        <p class="member-bio">
                            {{ $member->about ? \Illuminate\Support\Str::limit($member->about, 100) : 'Pakar teknologi dan kontributor aktif yang membagikan ilmu bermanfaat di '.( config('app.sitesettings')::first()?->site_title ?? config('app.name') ).'.' }}
                        </p>

                        <div class="member-stats">
                            <x-icon name="file-text" width="16" height="16" />
                            <span>{{ $member->posts_count }} Artikel Diterbitkan</span>
                        </div>

                        {{-- Social Links --}}
                        <div class="member-socials">
                            @if($member->facebook)
                                <a href="{{ $member->facebook }}" target="_blank" aria-label="Facebook">
                                    <x-icon name="facebook" width="18" height="18" />
                                </a>
                            @endif
                            @if($member->twitter)
                                <a href="{{ $member->twitter }}" target="_blank" aria-label="Twitter">
                                    <x-icon name="twitter" width="18" height="18" />
                                </a>
                            @endif
                            @if($member->instagram)
                                <a href="{{ $member->instagram }}" target="_blank" aria-label="Instagram">
                                    <x-icon name="instagram" width="18" height="18" />
                                </a>
                            @endif
                            @if($member->linkedin)
                                <a href="{{ $member->linkedin }}" target="_blank" aria-label="LinkedIn">
                                    <x-icon name="linkedin" width="18" height="18" />
                                </a>
                            @endif
                            @if($member->youtube)
                                <a href="{{ $member->youtube }}" target="_blank" aria-label="YouTube">
                                    <x-icon name="youtube" width="18" height="18" />
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="member-card-footer">
                        <a href="{{ route('frontend.user', $member->username) }}" class="btn-view-profile">
                            Lihat Profil & Artikel
                            <x-icon name="chevron-right" width="14" height="14" />
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-lg-12">
                    <x-frontend.empty-state
                        icon="file-text"
                        title="Tidak Ada Anggota"
                        message="Belum ada anggota tim atau penulis yang terdaftar."
                        cta-text="Kembali ke Beranda"
                        :cta-url="route('frontend.home')"
                    />
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="pagination" style="margin-top: var(--space-4);">
            <div class="pagination-area">
                {{ $members->links('vendor.pagination.custom') }}
            </div>
        </div>
    </div>
</section>
@endsection
