@extends("dashboard.master")
@section("title", "Manage Analytics Data")

@section("style")
<link rel="stylesheet" href="{{ asset('assets/dashboard/css/analytics/analytics.css') }}"/>
<style>
    .manage-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    .manage-card {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        box-shadow: var(--card-shadow);
    }
    .manage-card-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .form-group label {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 0.5rem;
        display: block;
    }
    .form-control, select {
        width: 100%;
        padding: 0.6rem 0.85rem;
        font-size: 0.9rem;
        border-radius: var(--radius-md);
        border: 1px solid var(--card-border);
        background-color: var(--card-bg);
        color: var(--text-primary);
        outline: none;
        transition: all 0.2s ease;
    }
    .form-control:focus, select:focus {
        border-color: var(--accent-primary);
    }
    .btn-submit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.6rem 1.25rem;
        font-size: 0.9rem;
        font-weight: 600;
        border-radius: var(--radius-md);
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none !important;
    }
    .btn-submit--primary {
        background-color: var(--accent-primary);
        color: #fff;
    }
    .btn-submit--primary:hover {
        background-color: #2563eb;
    }
    .btn-submit--danger {
        background-color: #ef4444;
        color: #fff;
    }
    .btn-submit--danger:hover {
        background-color: #dc2626;
    }
    .btn-submit--secondary {
        background-color: #64748b;
        color: #fff;
    }
    .btn-submit--secondary:hover {
        background-color: #475569;
    }
    /* Search Table classes */
    .table-container {
        overflow-x: auto;
        margin-top: 1rem;
        border: 1px solid var(--card-border);
        border-radius: var(--radius-md);
    }
    .search-filter-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }
    .search-filter-input {
        max-width: 320px;
    }
</style>
@endsection

@section("content")
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="analytics-header">
                <div class="analytics-title">
                    <h1>Workspace Analytics</h1>
                    <p>Kelola, bersihkan, atau sesuaikan data kunjungan dan interaksi.</p>
                </div>
                <div>
                    <form action="{{ route('dashboard.analytics.manage.clear_cache') }}" method="POST" style="margin:0;">
                        @csrf
                        <button type="submit" class="btn-submit btn-submit--secondary py-2">
                            <i class="fas fa-sync-alt"></i> Bersihkan Cache Statistik
                        </button>
                    </form>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="analytics-tabs">
                <a href="{{ route('dashboard.analytics.overview') }}" class="analytics-tab-link">Overview</a>
                <a href="{{ route('dashboard.analytics.audience') }}" class="analytics-tab-link">Audience</a>
                <a href="{{ route('dashboard.analytics.content') }}" class="analytics-tab-link">Content & Search</a>
                <a href="{{ route('dashboard.analytics.manage') }}" class="analytics-tab-link active">Manage Data</a>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 8px; font-weight: 500;">
                    <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="manage-grid" style="grid-template-columns: 1fr; margin-bottom: 1.5rem;">
                <!-- Card 1: Hapus Views -->
                <div class="manage-card">
                    <h2 class="manage-card-title"><i class="fas fa-eye text-primary"></i> Kelola Data Views</h2>
                    <form action="{{ route('dashboard.analytics.manage.clear_views') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data views sesuai kriteria ini?')">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="view_mode">Pilihan Hapus</label>
                            <select id="view_mode" name="mode" onchange="toggleDateInput(this.value)">
                                <option value="all">Hapus Semua Data Views</option>
                                <option value="date">Hapus Hanya Pada Tanggal Tertentu</option>
                                <option value="before">Hapus Sebelum Tanggal Tertentu</option>
                            </select>
                        </div>
                        <div class="form-group mb-4" id="view_date_group" style="display: none;">
                            <label for="view_date">Pilih Tanggal</label>
                            <input type="date" id="view_date" name="date" class="form-control" max="{{ date('Y-m-d') }}"/>
                        </div>
                        <button type="submit" class="btn-submit btn-submit--danger">
                            <i class="fas fa-trash-alt"></i> Hapus Views
                        </button>
                    </form>
                </div>
            </div>

            <!-- Card 2: Atur Likes & Feedback Table -->
            <div class="manage-card mb-4">
                <h2 class="manage-card-title"><i class="fas fa-heart text-danger"></i> Atur Likes / Feedback Postingan</h2>
                
                <div class="mb-3" style="max-width: 450px;">
                    <label for="post-search" style="font-size: 0.85rem; font-weight:600; color: var(--text-secondary);">Cari Judul Postingan</label>
                    <input type="text" id="post-search" class="form-control" placeholder="Ketik judul postingan untuk memfilter..." onkeyup="filterPostsTable(this.value)"/>
                </div>

                <div class="table-container">
                    <table class="analytics-table">
                        <thead>
                            <tr>
                                <th>Judul Postingan</th>
                                <th style="width: 250px; text-align: center;">Suka (Like)</th>
                                <th style="width: 250px; text-align: center;">Bermanfaat (Yes)</th>
                                <th style="width: 250px; text-align: center;">Tidak Bermanfaat (No)</th>
                            </tr>
                        </thead>
                        <tbody id="posts-table-body">
                            @foreach($posts as $post)
                                <tr>
                                    <td class="post-title-cell">
                                        <strong>{{ $post->title }}</strong>
                                        @if($post->trashed())
                                            <span class="badge badge-danger ml-1" style="font-size: 10px;">Deleted</span>
                                        @endif
                                        <br/>
                                        <small class="text-muted">ID: {{ $post->id }} &middot; Slug: {{ $post->slug }}</small>
                                    </td>
                                    
                                    <!-- Suka -->
                                    <td style="text-align: center; vertical-align: middle;">
                                        <div class="mb-2 font-weight-bold" style="font-size: 1.1rem; color: var(--text-primary);">{{ $post->likes_count }}</div>
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <form action="{{ route('dashboard.analytics.manage.adjust_likes') }}" method="POST" style="margin:0;">
                                                @csrf
                                                <input type="hidden" name="post_id" value="{{ $post->id }}"/>
                                                <input type="hidden" name="type" value="like"/>
                                                <input type="hidden" name="action" value="add"/>
                                                <input type="hidden" name="quantity" value="10"/>
                                                <button type="submit" class="btn btn-xs btn-outline-success font-weight-bold" title="Tambah 10 Suka">+10</button>
                                            </form>
                                            <form action="{{ route('dashboard.analytics.manage.adjust_likes') }}" method="POST" style="margin:0;">
                                                @csrf
                                                <input type="hidden" name="post_id" value="{{ $post->id }}"/>
                                                <input type="hidden" name="type" value="like"/>
                                                <input type="hidden" name="action" value="remove"/>
                                                <input type="hidden" name="quantity" value="10"/>
                                                <button type="submit" class="btn btn-xs btn-outline-danger font-weight-bold" title="Kurangi 10 Suka">-10</button>
                                            </form>
                                            <form action="{{ route('dashboard.analytics.manage.adjust_likes') }}" method="POST" style="margin:0;" onsubmit="return confirm('Hapus semua data suka untuk postingan ini?')">
                                                @csrf
                                                <input type="hidden" name="post_id" value="{{ $post->id }}"/>
                                                <input type="hidden" name="type" value="like"/>
                                                <input type="hidden" name="action" value="reset"/>
                                                <button type="submit" class="btn btn-xs btn-danger font-weight-bold" title="Reset Suka">Reset</button>
                                            </form>
                                        </div>
                                    </td>

                                    <!-- Bermanfaat -->
                                    <td style="text-align: center; vertical-align: middle;">
                                        <div class="mb-2 font-weight-bold" style="font-size: 1.1rem; color: var(--text-primary);">{{ $post->helpful_yes_count }}</div>
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <form action="{{ route('dashboard.analytics.manage.adjust_likes') }}" method="POST" style="margin:0;">
                                                @csrf
                                                <input type="hidden" name="post_id" value="{{ $post->id }}"/>
                                                <input type="hidden" name="type" value="helpful_yes"/>
                                                <input type="hidden" name="action" value="add"/>
                                                <input type="hidden" name="quantity" value="10"/>
                                                <button type="submit" class="btn btn-xs btn-outline-success font-weight-bold" title="Tambah 10 Bermanfaat">+10</button>
                                            </form>
                                            <form action="{{ route('dashboard.analytics.manage.adjust_likes') }}" method="POST" style="margin:0;">
                                                @csrf
                                                <input type="hidden" name="post_id" value="{{ $post->id }}"/>
                                                <input type="hidden" name="type" value="helpful_yes"/>
                                                <input type="hidden" name="action" value="remove"/>
                                                <input type="hidden" name="quantity" value="10"/>
                                                <button type="submit" class="btn btn-xs btn-outline-danger font-weight-bold" title="Kurangi 10 Bermanfaat">-10</button>
                                            </form>
                                            <form action="{{ route('dashboard.analytics.manage.adjust_likes') }}" method="POST" style="margin:0;" onsubmit="return confirm('Hapus semua data bermanfaat untuk postingan ini?')">
                                                @csrf
                                                <input type="hidden" name="post_id" value="{{ $post->id }}"/>
                                                <input type="hidden" name="type" value="helpful_yes"/>
                                                <input type="hidden" name="action" value="reset"/>
                                                <button type="submit" class="btn btn-xs btn-danger font-weight-bold" title="Reset Bermanfaat">Reset</button>
                                            </form>
                                        </div>
                                    </td>

                                    <!-- Tidak Bermanfaat -->
                                    <td style="text-align: center; vertical-align: middle;">
                                        <div class="mb-2 font-weight-bold" style="font-size: 1.1rem; color: var(--text-primary);">{{ $post->helpful_no_count }}</div>
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <form action="{{ route('dashboard.analytics.manage.adjust_likes') }}" method="POST" style="margin:0;">
                                                @csrf
                                                <input type="hidden" name="post_id" value="{{ $post->id }}"/>
                                                <input type="hidden" name="type" value="helpful_no"/>
                                                <input type="hidden" name="action" value="add"/>
                                                <input type="hidden" name="quantity" value="10"/>
                                                <button type="submit" class="btn btn-xs btn-outline-success font-weight-bold" title="Tambah 10 Tidak Bermanfaat">+10</button>
                                            </form>
                                            <form action="{{ route('dashboard.analytics.manage.adjust_likes') }}" method="POST" style="margin:0;">
                                                @csrf
                                                <input type="hidden" name="post_id" value="{{ $post->id }}"/>
                                                <input type="hidden" name="type" value="helpful_no"/>
                                                <input type="hidden" name="action" value="remove"/>
                                                <input type="hidden" name="quantity" value="10"/>
                                                <button type="submit" class="btn btn-xs btn-outline-danger font-weight-bold" title="Kurangi 10 Tidak Bermanfaat">-10</button>
                                            </form>
                                            <form action="{{ route('dashboard.analytics.manage.adjust_likes') }}" method="POST" style="margin:0;" onsubmit="return confirm('Hapus semua data tidak bermanfaat untuk postingan ini?')">
                                                @csrf
                                                <input type="hidden" name="post_id" value="{{ $post->id }}"/>
                                                <input type="hidden" name="type" value="helpful_no"/>
                                                <input type="hidden" name="action" value="reset"/>
                                                <button type="submit" class="btn btn-xs btn-danger font-weight-bold" title="Reset Tidak Bermanfaat">Reset</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Card 3: Kelola Data Pencarian -->
            <div class="manage-card mb-4">
                <h2 class="manage-card-title"><i class="fas fa-search text-success"></i> Kelola Data Pencarian (Search Logs)</h2>
                
                <div class="search-filter-row">
                    <!-- Search Input Filter -->
                    <form method="GET" action="{{ route('dashboard.analytics.manage') }}" class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 450px;">
                        <input type="text" name="q" class="form-control search-filter-input" placeholder="Cari query pencarian..." value="{{ $searchQuery ?? '' }}"/>
                        <button type="submit" class="btn-submit btn-submit--secondary py-2"><i class="fas fa-filter"></i> Filter</button>
                        @if(!empty($searchQuery))
                            <a href="{{ route('dashboard.analytics.manage') }}" class="btn-submit btn-submit--secondary py-2" style="background:#dc2626;"><i class="fas fa-times"></i> Clear</a>
                        @endif
                    </form>

                    <!-- Batch Operations -->
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn-submit btn-submit--danger py-2" onclick="submitBatchDelete()">
                            <i class="fas fa-trash-alt"></i> Hapus Terpilih
                        </button>
                        <form action="{{ route('dashboard.analytics.manage.clear_searches') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus seluruh log pencarian?')" class="d-inline">
                            @csrf
                            <input type="hidden" name="action_type" value="all"/>
                            <button type="submit" class="btn-submit btn-submit--danger py-2">
                                <i class="fas fa-fire"></i> Kosongkan Semua Pencarian
                            </button>
                        </form>
                    </div>
                </div>

                <form id="batch-delete-form" action="{{ route('dashboard.analytics.manage.clear_searches') }}" method="POST" style="display: none;">
                    @csrf
                    <input type="hidden" name="action_type" value="selected"/>
                    <div id="batch-delete-inputs"></div>
                </form>

                <div class="table-container">
                    <table class="analytics-table">
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align: center;">
                                    <input type="checkbox" id="select-all" onclick="toggleSelectAll(this)"/>
                                </th>
                                <th>Query Pencarian</th>
                                <th>Jenis</th>
                                <th>Jumlah Hasil</th>
                                <th>IP Hash</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($searchLogs as $log)
                                <tr>
                                    <td style="text-align: center;">
                                        <input type="checkbox" class="search-checkbox" value="{{ $log->id }}"/>
                                    </td>
                                    <td><strong>{{ $log->query }}</strong></td>
                                    <td><span class="badge badge-secondary">{{ $log->search_type }}</span></td>
                                    <td>{{ $log->results_count }} hasil</td>
                                    <td><code style="font-size: 11px;">{{ substr($log->ip_hash, 0, 12) }}...</code></td>
                                    <td>{{ $log->created_at->format('d M Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="fas fa-info-circle mr-1"></i> Tidak ada data pencarian ditemukan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-3">
                    {{ $searchLogs->links() }}
                </div>
            </div>

        </div>
    </section>
</div>
@endsection

@section("script")
<script>
    function toggleDateInput(val) {
        const dateGroup = document.getElementById('view_date_group');
        const dateInput = document.getElementById('view_date');
        if (val === 'date' || val === 'before') {
            dateGroup.style.display = 'block';
            dateInput.required = true;
        } else {
            dateGroup.style.display = 'none';
            dateInput.required = false;
        }
    }

    function filterPostsTable(query) {
        const rows = document.querySelectorAll('#posts-table-body tr');
        const q = query.toLowerCase();
        rows.forEach(row => {
            const titleCell = row.querySelector('.post-title-cell');
            if (titleCell) {
                const titleText = titleCell.textContent.toLowerCase();
                if (titleText.includes(q)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    }

    function toggleSelectAll(master) {
        const checkboxes = document.querySelectorAll('.search-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = master.checked;
        });
    }

    function submitBatchDelete() {
        const selected = [];
        document.querySelectorAll('.search-checkbox:checked').forEach(cb => {
            selected.push(cb.value);
        });

        if (selected.length === 0) {
            alert('Silakan pilih minimal satu data pencarian untuk dihapus!');
            return;
        }

        if (confirm(`Apakah Anda yakin ingin menghapus ${selected.length} data pencarian terpilih?`)) {
            const form = document.getElementById('batch-delete-form');
            const container = document.getElementById('batch-delete-inputs');
            container.innerHTML = ''; // clear

            selected.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                container.appendChild(input);
            });

            form.submit();
        }
    }
</script>
@endsection
