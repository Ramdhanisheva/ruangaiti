@extends('dashboard.master')
@section('title', 'Upload Media')

@section('style')
<link rel="stylesheet" href="{{ asset('assets/dashboard/css/media/media-library.css') }}"/>
@endsection

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-cloud-upload-alt mr-2 text-primary"></i>Upload Media</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('dashboard.media.index') }}">Media</a></li>
                        <li class="breadcrumb-item active">Upload</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <h6 class="font-weight-bold"><i class="icon fas fa-ban mr-1"></i>Validation Error</h6>
                    @foreach ($errors->all() as $error)<p class="m-0">{{ $error }}</p>@endforeach
                </div>
            @endif
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <p class="m-0 font-weight-bold">{{ session('success') }}</p>
                </div>
            @endif

            <div class="row">
                {{-- Upload Panel --}}
                <div class="col-lg-7">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title font-weight-bold">Select Files</h3>
                            <div class="card-tools">
                                <span class="badge badge-secondary" id="selected-count">0 files selected</span>
                            </div>
                        </div>
                        <div class="card-body">
                            {{-- Drag & Drop Zone --}}
                            <div class="upload-dropzone" id="main-dropzone">
                                <input type="file" id="main-file-input" name="images[]" multiple
                                       accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx,.svg">
                                <div class="dropzone-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                <div class="dropzone-text">Drag & drop files here</div>
                                <div class="dropzone-hint">or click to browse files — JPEG, PNG, GIF, WEBP, SVG, PDF, DOC, XLS · max 10MB each</div>
                            </div>

                            {{-- File Queue with progress --}}
                            <div class="upload-queue mt-3" id="main-upload-queue"></div>

                            {{-- Upload button --}}
                            <div class="mt-3" id="upload-action" style="display:none">
                                <button class="btn btn-primary font-weight-bold" id="start-upload-btn">
                                    <i class="fas fa-upload mr-2"></i>Upload <span id="upload-btn-count"></span>
                                </button>
                                <button class="btn btn-outline-secondary ml-2" id="clear-queue-btn">
                                    <i class="fas fa-times mr-1"></i>Clear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Metadata Panel --}}
                <div class="col-lg-5">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title font-weight-bold">Default Metadata</h3>
                            <small class="text-muted d-block mt-1" style="font-size:.78rem">Applied to all uploaded files. Edit individually after upload.</small>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold" for="meta-alt">Alt Text <small class="text-muted">(SEO)</small></label>
                                <input type="text" class="form-control" id="meta-alt" placeholder="Describe the image content…">
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold" for="meta-caption">Caption</label>
                                <input type="text" class="form-control" id="meta-caption" placeholder="Caption or credit text…">
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold" for="meta-title">Title</label>
                                <input type="text" class="form-control" id="meta-title" placeholder="Descriptive title…">
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold" for="meta-description">Description</label>
                                <textarea class="form-control" id="meta-description" rows="3" placeholder="Longer description…"></textarea>
                            </div>
                            <hr>
                            <div class="d-flex align-items-center" style="gap: .5rem">
                                <a href="{{ route('dashboard.media.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left mr-1"></i>Back to Library
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Upload progress summary --}}
                    <div class="card" id="upload-summary-card" style="display:none">
                        <div class="card-header">
                            <h3 class="card-title font-weight-bold text-success">
                                <i class="fas fa-check-circle mr-1"></i>Upload Complete
                            </h3>
                        </div>
                        <div class="card-body" id="upload-summary-body">
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('dashboard.media.index') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-images mr-1"></i>View All Media
                            </a>
                            <button class="btn btn-outline-secondary btn-sm ml-2" onclick="resetUploader()">
                                <i class="fas fa-redo mr-1"></i>Upload More
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('script')
<script src="{{ asset('assets/dashboard/plugins/sweetalert2/sweetalert2.all.js') }}"></script>
<script>
(function () {
    'use strict';

    const dropzone   = document.getElementById('main-dropzone');
    const fileInput  = document.getElementById('main-file-input');
    const queue      = document.getElementById('main-upload-queue');
    const uploadAction = document.getElementById('upload-action');
    const startBtn   = document.getElementById('start-upload-btn');
    const clearBtn   = document.getElementById('clear-queue-btn');
    const countBadge = document.getElementById('selected-count');
    const uploadBtnCount = document.getElementById('upload-btn-count');

    const STORE_URL  = '{{ route("dashboard.media.store") }}';
    const CSRF       = '{{ csrf_token() }}';

    let selectedFiles = [];

    // ── Drag & drop events ──────────────────────────────────────────────────
    ['dragenter','dragover'].forEach(ev => dropzone.addEventListener(ev, e => {
        e.preventDefault(); dropzone.classList.add('dragover');
    }));
    ['dragleave','drop'].forEach(ev => dropzone.addEventListener(ev, e => {
        e.preventDefault(); dropzone.classList.remove('dragover');
    }));
    dropzone.addEventListener('drop', e => addFiles([...e.dataTransfer.files]));
    fileInput.addEventListener('change', () => addFiles([...fileInput.files]));

    function addFiles(files) {
        files.forEach(f => {
            if (!selectedFiles.find(x => x.name === f.name && x.size === f.size)) {
                selectedFiles.push(f);
            }
        });
        renderQueue();
        fileInput.value = '';
    }

    function renderQueue() {
        queue.innerHTML = '';
        selectedFiles.forEach((file, i) => {
            const item = document.createElement('div');
            item.className = 'upload-queue-item';
            item.id = 'qi-' + i;
            item.innerHTML = `
                <span class="item-name" title="${file.name}">${file.name}</span>
                <span style="font-size:.72rem;color:#6c757d">${formatBytes(file.size)}</span>
                <div class="item-progress">
                    <div class="progress"><div class="progress-bar bg-primary" id="pb-${i}" style="width:0%"></div></div>
                </div>
                <span class="item-status" id="ps-${i}">Pending</span>
                <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeFile(${i})" title="Remove">
                    <i class="fas fa-times"></i>
                </button>`;
            queue.appendChild(item);
        });
        const n = selectedFiles.length;
        countBadge.textContent = n + ' file' + (n !== 1 ? 's' : '') + ' selected';
        uploadBtnCount.textContent = '(' + n + ')';
        uploadAction.style.display = n > 0 ? '' : 'none';
    }

    window.removeFile = function(i) {
        selectedFiles.splice(i, 1);
        renderQueue();
    };

    clearBtn.addEventListener('click', () => { selectedFiles = []; renderQueue(); });

    // ── Upload ──────────────────────────────────────────────────────────────
    startBtn.addEventListener('click', async () => {
        if (selectedFiles.length === 0) return;
        startBtn.disabled = true;
        clearBtn.disabled = true;

        const alt         = document.getElementById('meta-alt').value;
        const caption     = document.getElementById('meta-caption').value;
        const title       = document.getElementById('meta-title').value;
        const description = document.getElementById('meta-description').value;

        let successCount = 0;
        let errorFiles   = [];

        for (let i = 0; i < selectedFiles.length; i++) {
            const file = selectedFiles[i];
            const fd = new FormData();
            fd.append('_token', CSRF);
            fd.append('image', file);
            if (alt)         fd.append('alt', alt);
            if (caption)     fd.append('caption', caption);
            if (title)       fd.append('title', title);
            if (description) fd.append('description', description);

            const pb = document.getElementById('pb-' + i);
            const ps = document.getElementById('ps-' + i);

            try {
                await uploadWithProgress(fd, pb, ps);
                successCount++;
            } catch (err) {
                errorFiles.push(file.name);
                if (ps) { ps.textContent = 'Error'; ps.className = 'item-status error'; }
                if (pb) { pb.className = 'progress-bar bg-danger'; pb.style.width = '100%'; }
            }
        }

        // Show summary
        const summaryCard = document.getElementById('upload-summary-card');
        const summaryBody = document.getElementById('upload-summary-body');
        summaryCard.style.display = '';
        summaryBody.innerHTML = `
            <p class="mb-1"><strong>${successCount}</strong> file(s) uploaded successfully.</p>
            ${errorFiles.length ? '<p class="text-danger mb-0">Failed: ' + errorFiles.join(', ') + '</p>' : ''}`;

        startBtn.disabled = false;
        clearBtn.disabled = false;
    });

    function uploadWithProgress(formData, progressBar, statusEl) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', STORE_URL);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.upload.onprogress = e => {
                if (e.lengthComputable && progressBar) {
                    progressBar.style.width = Math.round(e.loaded / e.total * 100) + '%';
                }
            };
            xhr.onload = () => {
                if (xhr.status >= 200 && xhr.status < 300) {
                    if (progressBar) { progressBar.style.width = '100%'; progressBar.className = 'progress-bar bg-success'; }
                    if (statusEl)   { statusEl.textContent = 'Done'; statusEl.className = 'item-status done'; }
                    resolve(xhr.response);
                } else {
                    reject(new Error('HTTP ' + xhr.status));
                }
            };
            xhr.onerror = () => reject(new Error('Network error'));
            xhr.send(formData);
        });
    }

    window.resetUploader = function() {
        selectedFiles = [];
        renderQueue();
        document.getElementById('upload-summary-card').style.display = 'none';
        document.getElementById('meta-alt').value        = '';
        document.getElementById('meta-caption').value   = '';
        document.getElementById('meta-title').value     = '';
        document.getElementById('meta-description').value = '';
    };

    function formatBytes(b) {
        if (!b) return '0 B';
        const u = ['B','KB','MB','GB'], i = Math.floor(Math.log(b)/Math.log(1024));
        return (b/Math.pow(1024,i)).toFixed(1) + ' ' + u[i];
    }

    // Quick dropzone on index (if present on other pages)
    const quickDropzone = document.getElementById('quick-dropzone');
    if (quickDropzone) {
        const qi = document.getElementById('quick-file-input');
        ['dragenter','dragover'].forEach(ev => quickDropzone.addEventListener(ev, e => {
            e.preventDefault(); quickDropzone.classList.add('dragover');
        }));
        ['dragleave','drop'].forEach(ev => quickDropzone.addEventListener(ev, e => {
            e.preventDefault(); quickDropzone.classList.remove('dragover');
        }));
        quickDropzone.addEventListener('drop', e => uploadQuick([...e.dataTransfer.files]));
        qi.addEventListener('change', () => uploadQuick([...qi.files]));

        function uploadQuick(files) {
            const uploadQueueEl = document.getElementById('upload-queue');
            files.forEach((file, idx) => {
                const item = document.createElement('div');
                item.className = 'upload-queue-item';
                item.innerHTML = `
                    <span class="item-name" title="${file.name}">${file.name}</span>
                    <div class="item-progress"><div class="progress"><div class="progress-bar bg-primary" id="qpb-${idx}" style="width:0%"></div></div></div>
                    <span class="item-status" id="qps-${idx}">Uploading…</span>`;
                uploadQueueEl.appendChild(item);

                const fd = new FormData();
                fd.append('_token', CSRF);
                fd.append('image', file);
                uploadWithProgress(fd, document.getElementById('qpb-'+idx), document.getElementById('qps-'+idx))
                    .then(() => setTimeout(() => item.remove(), 2500))
                    .catch(() => {});
            });
            qi.value = '';
        }
    }
})();
</script>
@endsection
