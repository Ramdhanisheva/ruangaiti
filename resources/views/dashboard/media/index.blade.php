@extends('dashboard.master')
@section('title', 'Media')

@section('style')
<link rel="stylesheet" href="{{ asset('assets/dashboard/css/media/media-library.css') }}"/>
@endsection

@section('content')
<div class="content-wrapper">
    {{-- Header --}}
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold"><i class="fas fa-images mr-2 text-primary"></i>Media</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Media</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            {{-- Flash messages --}}
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <h6 class="font-weight-bold"><i class="icon fas fa-ban mr-1"></i>Error!</h6>
                    @foreach ($errors->all() as $error)<p class="m-0">{{ $error }}</p>@endforeach
                </div>
            @endif
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <h6 class="font-weight-bold"><i class="icon fas fa-check mr-1"></i>Success!</h6>
                    <p class="m-0">{{ session('success') }}</p>
                </div>
            @endif

            {{-- Statistics Bar (admin only) --}}
            @if($stats)
            <div class="media-stats-bar">
                <div class="media-stat-card">
                    <div class="stat-icon bg-primary"><i class="fas fa-layer-group"></i></div>
                    <div><div class="stat-value">{{ number_format($stats['total']) }}</div><div class="stat-label">Total Files</div></div>
                </div>
                <div class="media-stat-card">
                    <div class="stat-icon bg-success"><i class="fas fa-image"></i></div>
                    <div><div class="stat-value">{{ number_format($stats['images'] + $stats['svgs']) }}</div><div class="stat-label">Images</div></div>
                </div>
                <div class="media-stat-card">
                    <div class="stat-icon bg-info"><i class="fas fa-film"></i></div>
                    <div><div class="stat-value">{{ number_format($stats['videos']) }}</div><div class="stat-label">Videos</div></div>
                </div>
                <div class="media-stat-card">
                    <div class="stat-icon bg-warning"><i class="fas fa-file-alt"></i></div>
                    <div><div class="stat-value">{{ number_format($stats['documents'] + $stats['pdfs']) }}</div><div class="stat-label">Documents</div></div>
                </div>
                <div class="media-stat-card">
                    <div class="stat-icon bg-secondary"><i class="fas fa-hdd"></i></div>
                    <div><div class="stat-value">{{ $stats['storage'] }}</div><div class="stat-label">Storage Used</div></div>
                </div>
                <div class="media-stat-card">
                    <div class="stat-icon bg-danger"><i class="fas fa-unlink"></i></div>
                    <div><div class="stat-value">{{ number_format($stats['unused']) }}</div><div class="stat-label">Unused</div></div>
                </div>
            </div>
            @endif

            <div class="row">
                {{-- Sidebar: Upload + Folders --}}
                <div class="col-lg-3 col-md-4">



                    {{-- Type Filters --}}
                    <div class="card mb-4">
                        <div class="card-header"><h3 class="card-title font-weight-bold">File Types</h3></div>
                        <div class="card-body p-2">
                            <ul class="media-folder-list">
                                <li class="media-folder-item {{ $type === 'all' ? 'active' : '' }}">
                                    <a href="{{ route('dashboard.media.index', array_merge(request()->except(['type','page']), ['type' => 'all'])) }}">
                                        <i class="fas fa-folder-open"></i> All Files
                                    </a>
                                </li>
                                <li class="media-folder-item {{ $type === 'image' ? 'active' : '' }}">
                                    <a href="{{ route('dashboard.media.index', array_merge(request()->except(['type','page']), ['type' => 'image'])) }}">
                                        <i class="fas fa-image"></i> Images
                                    </a>
                                </li>
                                <li class="media-folder-item {{ $type === 'svg' ? 'active' : '' }}">
                                    <a href="{{ route('dashboard.media.index', array_merge(request()->except(['type','page']), ['type' => 'svg'])) }}">
                                        <i class="fas fa-bezier-curve"></i> SVG
                                    </a>
                                </li>
                                <li class="media-folder-item {{ $type === 'video' ? 'active' : '' }}">
                                    <a href="{{ route('dashboard.media.index', array_merge(request()->except(['type','page']), ['type' => 'video'])) }}">
                                        <i class="fas fa-film"></i> Video
                                    </a>
                                </li>
                                <li class="media-folder-item {{ $type === 'pdf' ? 'active' : '' }}">
                                    <a href="{{ route('dashboard.media.index', array_merge(request()->except(['type','page']), ['type' => 'pdf'])) }}">
                                        <i class="fas fa-file-pdf"></i> PDF
                                    </a>
                                </li>
                                <li class="media-folder-item {{ $type === 'document' ? 'active' : '' }}">
                                    <a href="{{ route('dashboard.media.index', array_merge(request()->except(['type','page']), ['type' => 'document'])) }}">
                                        <i class="fas fa-file-word"></i> Documents
                                    </a>
                                </li>
                            </ul>
                            @if($extensions->count())
                            <hr class="my-2">
                            <div class="px-1 mb-1" style="font-size:.72rem;color:#6c757d;font-weight:700;text-transform:uppercase;letter-spacing:.05em">By Extension</div>
                            <ul class="media-folder-list">
                                @foreach($extensions as $ext)
                                <li class="media-folder-item {{ request('folder') === $ext ? 'active' : '' }}">
                                    <a href="{{ route('dashboard.media.index', array_merge(request()->all(), ['folder' => $ext])) }}">
                                        <i class="fas fa-folder"></i>.{{ strtoupper($ext) }}
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                            @endif
                        </div>
                    </div>

                </div>{{-- /sidebar --}}

                {{-- Main Content --}}
                <div class="col-lg-9 col-md-8">

                    {{-- Toolbar --}}
                    <div class="media-toolbar">
                        {{-- Search --}}
                        <form method="GET" action="{{ route('dashboard.media.index') }}" class="d-flex" style="flex:1;gap:.4rem;min-width:200px">
                            @foreach(request()->except(['search','page']) as $key => $val)
                                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                            @endforeach
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search files, alt, caption…" value="{{ $search }}">
                            <button class="btn btn-sm btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                        </form>

                        {{-- Sort --}}
                        <form method="GET" action="{{ route('dashboard.media.index') }}" id="sort-form">
                            @foreach(request()->except(['sort','page']) as $key => $val)
                                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                            @endforeach
                            <select name="sort" class="form-control form-control-sm" onchange="document.getElementById('sort-form').submit()" style="min-width:130px">
                                <option value="newest"    {{ $sort === 'newest'    ? 'selected' : '' }}>Newest first</option>
                                <option value="oldest"    {{ $sort === 'oldest'    ? 'selected' : '' }}>Oldest first</option>
                                <option value="name"      {{ $sort === 'name'      ? 'selected' : '' }}>Name A–Z</option>
                                <option value="size_desc" {{ $sort === 'size_desc' ? 'selected' : '' }}>Largest first</option>
                                <option value="size_asc"  {{ $sort === 'size_asc'  ? 'selected' : '' }}>Smallest first</option>
                            </select>
                        </form>

                        {{-- Unused filter (admin) --}}
                        @if(auth()->user()->role == 3)
                        <form method="GET" action="{{ route('dashboard.media.index') }}" id="unused-form">
                            @foreach(request()->except(['unused','page']) as $key => $val)
                                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                            @endforeach
                            <div class="custom-control custom-checkbox d-flex align-items-center" style="white-space:nowrap">
                                <input type="checkbox" name="unused" value="1" class="custom-control-input" id="unused-cb"
                                    {{ request('unused') ? 'checked' : '' }} onchange="document.getElementById('unused-form').submit()">
                                <label class="custom-control-label font-weight-bold" for="unused-cb" style="font-size:.8rem">Unused only</label>
                            </div>
                        </form>
                        @endif

                        {{-- Bulk toggle --}}
                        <button class="btn btn-sm btn-outline-secondary" id="bulk-toggle-btn" title="Select files for bulk action">
                            <i class="fas fa-check-square mr-1"></i>Select
                        </button>

                        {{-- Clear filters --}}
                        @if(request()->anyFilled(['search','type','folder','unused','sort']))
                            <a href="{{ route('dashboard.media.index') }}" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-times-circle mr-1"></i>Clear
                            </a>
                        @endif

                        <span class="media-count">{{ $media->total() }} file(s)</span>
                    </div>

                    {{-- Grid --}}
                    <div class="card">
                        <div class="card-body p-4">
                            <div class="media-library-grid" id="media-grid">
                                @forelse($media as $item)
                                    @php
                                        $fileType = $item->file_type;
                                        $isImage  = in_array($fileType, ['image', 'svg']);
                                        $fileUrl  = $item->public_url;
                                    @endphp
                                    <div class="media-item-card"
                                         data-id="{{ $item->id }}"
                                         data-filename="{{ $item->file_name }}"
                                         data-original="{{ $item->original_name ?? $item->file_name }}"
                                         data-path="{{ $item->path ?? 'uploads/media/' . $item->file_name }}"
                                         data-url="{{ $fileUrl }}"
                                         data-alt="{{ $item->alt }}"
                                         data-caption="{{ $item->caption }}"
                                         data-title="{{ $item->title }}"
                                         data-description="{{ $item->description }}"
                                         data-size="{{ $item->file_size_human }}"
                                         data-dimensions="{{ $item->width ? $item->width . 'x' . $item->height : '' }}"
                                         data-color="{{ $item->dominant_color }}"
                                         data-type="{{ $fileType }}"
                                         data-is-image="{{ $isImage ? 'true' : 'false' }}">

                                        {{-- Bulk checkbox --}}
                                        <div class="media-select-overlay">
                                            <input type="checkbox" class="media-checkbox" data-id="{{ $item->id }}"
                                                   onclick="event.stopPropagation()">
                                        </div>

                                        <div class="media-preview-wrapper"
                                             style="{{ $item->dominant_color ? 'background-color:' . $item->dominant_color . ';' : '' }}">
                                            @if($isImage)
                                                <img src="{{ $fileUrl }}" alt="{{ $item->alt ?? $item->file_name }}" loading="lazy">
                                            @elseif($fileType === 'video')
                                                <div class="media-icon-placeholder video"><i class="fas fa-play-circle"></i></div>
                                            @elseif($fileType === 'pdf')
                                                <div class="media-icon-placeholder pdf"><i class="fas fa-file-pdf"></i></div>
                                            @elseif($fileType === 'document')
                                                <div class="media-icon-placeholder doc"><i class="fas fa-file-word"></i></div>
                                            @else
                                                <div class="media-icon-placeholder"><i class="fas fa-file-alt"></i></div>
                                            @endif
                                        </div>

                                        <div class="media-info-bar">
                                            <div class="media-filename" title="{{ $item->original_name ?? $item->file_name }}">
                                                {{ $item->original_name ?? $item->file_name }}
                                            </div>
                                            <div class="media-meta-info">
                                                <span>.{{ strtoupper($item->extension) }}</span>
                                                <span class="badge {{ ($item->used_count ?? 0) > 0 ? 'badge-warning' : 'badge-success' }}" style="font-size:.65rem">
                                                    {{ $item->used_count ?? 0 }} uses
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div style="grid-column:1/-1;text-align:center;padding:3rem;color:#6c757d">
                                        <i class="fas fa-images fa-3x mb-3 d-block text-secondary"></i>
                                        <p class="font-weight-bold m-0">No files found.</p>
                                        <a href="{{ route('dashboard.media.create') }}" class="btn btn-primary btn-sm mt-2">
                                            <i class="fas fa-upload mr-1"></i>Upload Now
                                        </a>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                        <div class="card-footer clearfix bg-white border-top-0">
                            <div class="float-right">{{ $media->links() }}</div>
                        </div>
                    </div>

                </div>{{-- /main --}}
            </div>{{-- /row --}}
        </div>
    </section>
</div>

{{-- Bulk Action Floating Bar --}}
<div class="bulk-action-bar" id="bulk-action-bar">
    <span class="bulk-count" id="bulk-count">0 selected</span>
    <button class="btn btn-sm btn-outline-light" id="bulk-select-all"><i class="fas fa-check-double mr-1"></i>All</button>
    <button class="btn btn-sm btn-outline-light" id="bulk-deselect"><i class="fas fa-times mr-1"></i>None</button>
    <button class="btn btn-sm btn-danger" id="bulk-delete-btn"><i class="fas fa-trash-alt mr-1"></i>Delete Selected</button>
    <button class="btn btn-sm btn-outline-light" id="bulk-cancel-btn"><i class="fas fa-ban mr-1"></i>Cancel</button>
    <form id="bulk-delete-form" method="POST" action="{{ route('dashboard.media.bulk-destroy') }}" class="d-none">
        @csrf
        <div id="bulk-ids-container"></div>
    </form>
</div>

{{-- Detail Modal --}}
<div class="modal fade media-details-modal" id="mediaDetailsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header border-bottom-0 pb-0">
                <div>
                    <h5 class="modal-title font-weight-bold" id="detail-filename">Asset Details</h5>
                    <small class="text-muted" id="detail-original-name"></small>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
            </div>
            <div class="modal-body pt-3">
                <div class="media-detail-grid">
                    {{-- Left: Preview + Replace --}}
                    <div>
                        <div class="media-detail-preview" id="detail-preview-container"></div>

                        <div class="d-flex mt-2 mb-3" style="gap: .5rem">
                            <button class="btn btn-sm btn-outline-primary copy-link-btn font-weight-bold" id="detail-copy-btn" data-url="">
                                <i class="fas fa-copy mr-1"></i>Copy URL
                            </button>
                            <a href="#" class="btn btn-sm btn-outline-secondary font-weight-bold" id="detail-download-btn" target="_blank">
                                <i class="fas fa-download mr-1"></i>Download
                            </a>
                        </div>

                        {{-- File info table --}}
                        <table class="table table-sm table-borderless font-size-sm mb-3">
                            <tr><td class="text-muted font-weight-bold py-1" style="width:100px">Type:</td><td class="py-1" id="detail-type">—</td></tr>
                            <tr><td class="text-muted font-weight-bold py-1">Size:</td><td class="py-1" id="detail-size">—</td></tr>
                            <tr><td class="text-muted font-weight-bold py-1">Dimensions:</td><td class="py-1" id="detail-dimensions">—</td></tr>
                            <tr id="color-row"><td class="text-muted font-weight-bold py-1">Color:</td>
                                <td class="py-1"><span class="dominant-color-swatch" id="detail-color-swatch"></span><span id="detail-color-label">—</span></td>
                            </tr>
                        </table>

                        {{-- Replace --}}
                        <div class="card mb-0">
                            <div class="card-header py-2 bg-light border-0">
                                <h6 class="m-0 font-weight-bold text-secondary" style="font-size:.8rem">
                                    <i class="fas fa-exchange-alt mr-1"></i>Replace File (URL stays the same)
                                </h6>
                            </div>
                            <div class="card-body py-2">
                                <form id="replace-file-form" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="input-group input-group-sm">
                                        <div class="custom-file">
                                            <input type="file" name="file" class="custom-file-input" id="replace-upload" required>
                                            <label class="custom-file-label" for="replace-upload">Choose replacement…</label>
                                        </div>
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-primary font-weight-bold" type="submit">Replace</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Right: SEO metadata + Usage --}}
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="font-weight-bold text-muted" style="font-size:.85rem">SEO Metadata</span>
                            <span class="usage-badge unused" id="detail-usage-badge">Unused</span>
                        </div>

                        <form id="update-meta-form" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="form-group mb-2">
                                <label for="detail-title" class="font-weight-bold" style="font-size:.8rem">Title</label>
                                <input type="text" name="title" class="form-control form-control-sm" id="detail-title" placeholder="Media title…">
                            </div>
                            <div class="form-group mb-2">
                                <label for="detail-alt" class="font-weight-bold" style="font-size:.8rem">Alt Text</label>
                                <input type="text" name="alt" class="form-control form-control-sm" id="detail-alt" placeholder="Describe image content…">
                            </div>
                            <div class="form-group mb-2">
                                <label for="detail-caption" class="font-weight-bold" style="font-size:.8rem">Caption</label>
                                <input type="text" name="caption" class="form-control form-control-sm" id="detail-caption" placeholder="Caption / credit…">
                            </div>
                            <div class="form-group mb-3">
                                <label for="detail-description" class="font-weight-bold" style="font-size:.8rem">Description</label>
                                <textarea name="description" class="form-control form-control-sm" id="detail-description" rows="2" placeholder="Longer description…"></textarea>
                            </div>
                            <button type="submit" class="btn btn-success btn-sm btn-block font-weight-bold mb-3">
                                <i class="fas fa-save mr-1"></i>Save Metadata
                            </button>
                        </form>

                        {{-- Usage --}}
                        <div class="font-weight-bold text-muted mb-2" style="font-size:.8rem">
                            <i class="fas fa-link mr-1"></i>Used In
                        </div>
                        <ul class="list-unstyled" id="detail-usages-list"
                            style="max-height:130px;overflow-y:auto;font-size:.82rem;padding-left:0"></ul>

                        {{-- Delete --}}
                        <div class="pt-3 border-top mt-3">
                            <form id="delete-media-form" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm font-weight-bold" id="delete-media-btn">
                                    <i class="fas fa-trash-alt mr-1"></i>Delete File
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="{{ asset('assets/dashboard/plugins/sweetalert2/sweetalert2.all.js') }}"></script>
<script src="{{ asset('assets/dashboard/js/media-library.js') }}"></script>
<script>
    // Custom file input label update
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('custom-file-input')) {
            const label = e.target.nextElementSibling;
            if (label) label.textContent = e.target.files.length > 1
                ? e.target.files.length + ' files selected'
                : (e.target.files[0]?.name ?? 'Choose file…');
        }
    });
</script>
@endsection
