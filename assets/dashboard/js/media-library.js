/**
 * RuangAiTi Media Module — Unified JS
 * Handles: detail modal, bulk select, bulk delete, copy URL, quick upload dropzone.
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', () => {
        initDetailCards();
        initBulkMode();
        initCopyButtons();
        initQuickDropzone();
        initPickerMode();
    });

    // ── Picker Mode ───────────────────────────────────────────────────────────
    function initPickerMode() {
        const urlParams = new URLSearchParams(window.location.search);
        const isPicker = urlParams.get('mode') === 'picker';
        if (isPicker) {
            document.body.classList.add('media-picker-mode');
            
            // Hide statistics bar
            const stats = document.querySelector('.media-stats-bar');
            if (stats) stats.style.display = 'none';
            
            const bulkBtn = document.getElementById('bulk-toggle-btn');
            if (bulkBtn) bulkBtn.style.display = 'none';
            
            // Add custom insert button inside the details modal
            const detailCopyBtn = document.getElementById('detail-copy-btn');
            if (detailCopyBtn && !document.getElementById('detail-insert-btn')) {
                const insertBtn = document.createElement('button');
                insertBtn.id = 'detail-insert-btn';
                insertBtn.className = 'btn btn-sm btn-success font-weight-bold ml-2';
                insertBtn.innerHTML = '<i class="fas fa-check-circle mr-1"></i>Insert into Editor';
                insertBtn.addEventListener('click', function() {
                    const url = detailCopyBtn.getAttribute('data-url');
                    const alt = document.getElementById('detail-alt').value;
                    const caption = document.getElementById('detail-caption').value;
                    const title = document.getElementById('detail-title').value;
                    
                    if (window.opener) {
                        window.opener.postMessage({
                            type: 'insert-image',
                            url: url,
                            alt: alt,
                            caption: caption,
                            title: title
                        }, '*');
                        window.close();
                    } else {
                        toast('error', 'No parent window found to insert image.');
                    }
                });
                detailCopyBtn.parentNode.insertBefore(insertBtn, detailCopyBtn.nextSibling);
            }
            
            // Change page title if exists
            const h1 = document.querySelector('.content-header h1');
            if (h1) {
                h1.innerHTML = '<i class="fas fa-images mr-2 text-primary"></i>Pilih Media untuk Editor';
            }
        }
    }

    // ── Detail Modal ──────────────────────────────────────────────────────────
    function initDetailCards() {
        document.querySelectorAll('.media-item-card').forEach(card => {
            card.addEventListener('click', function (e) {
                // Don't open modal if clicking the bulk checkbox
                if (e.target.closest('.media-select-overlay')) return;
                // Don't open modal if in bulk mode (toggle selection instead)
                if (document.getElementById('media-grid')?.classList.contains('bulk-mode')) {
                    toggleCardSelect(this);
                    return;
                }
                openMediaDetails(this);
            });
        });
    }

    function openMediaDetails(card) {
        const id          = card.dataset.id;
        const filename    = card.dataset.filename;
        const original    = card.dataset.original || filename;
        const path        = card.dataset.path;
        const url         = card.dataset.url;
        const alt         = card.dataset.alt   || '';
        const caption     = card.dataset.caption || '';
        const title       = card.dataset.title || '';
        const description = card.dataset.description || '';
        const size        = card.dataset.size;
        const dimensions  = card.dataset.dimensions || 'N/A';
        const color       = card.dataset.color || '';
        const isImage     = card.dataset.isImage === 'true';
        const fileType    = card.dataset.type || 'other';

        const modal = document.getElementById('mediaDetailsModal');
        if (!modal) return;

        modal.querySelector('#detail-filename').textContent    = original;
        modal.querySelector('#detail-original-name').textContent = filename;
        modal.querySelector('#detail-type').textContent       = fileType.toUpperCase();
        modal.querySelector('#detail-size').textContent       = size || '—';
        modal.querySelector('#detail-dimensions').textContent = dimensions;

        // Color swatch
        const swatch = modal.querySelector('#detail-color-swatch');
        const clabel = modal.querySelector('#detail-color-label');
        if (color) {
            swatch.style.backgroundColor = color;
            swatch.style.display = 'inline-block';
            clabel.textContent   = color;
            modal.querySelector('#color-row').style.display = '';
        } else {
            modal.querySelector('#color-row').style.display = 'none';
        }

        // Preview
        const preview = modal.querySelector('#detail-preview-container');
        preview.innerHTML = '';
        if (isImage) {
            const img = document.createElement('img');
            img.src = url;
            img.alt = alt;
            preview.appendChild(img);
        } else if (fileType === 'video') {
            const vid = document.createElement('video');
            vid.src = url; vid.controls = true; vid.style.cssText = 'width:100%;height:100%';
            preview.appendChild(vid);
        } else {
            const ph = document.createElement('div');
            ph.className = 'media-icon-placeholder ' + fileType;
            const iconMap = { pdf: 'fa-file-pdf', document: 'fa-file-word', svg: 'fa-bezier-curve' };
            ph.innerHTML = `<i class="fas ${iconMap[fileType] || 'fa-file-alt'}" style="font-size:3rem"></i>`;
            preview.appendChild(ph);
        }

        // Buttons
        modal.querySelector('#detail-copy-btn').setAttribute('data-url', url);
        const dlBtn = modal.querySelector('#detail-download-btn');
        if (dlBtn) dlBtn.href = '/dashboard/media/' + id + '/download';

        // SEO form
        const updateForm = modal.querySelector('#update-meta-form');
        if (updateForm) {
            updateForm.action = '/dashboard/media/' + id;
            updateForm.querySelector('[name="alt"]').value         = alt;
            updateForm.querySelector('[name="caption"]').value     = caption;
            updateForm.querySelector('[name="title"]').value       = title;
            updateForm.querySelector('[name="description"]').value = description;
        }

        // Replace form
        const replaceForm = modal.querySelector('#replace-file-form');
        if (replaceForm) replaceForm.action = '/dashboard/media/' + id + '/replace';

        // Delete form
        const deleteForm = modal.querySelector('#delete-media-form');
        if (deleteForm) deleteForm.action = '/dashboard/media/' + id;

        // Usage list
        const usagesEl = modal.querySelector('#detail-usages-list');
        usagesEl.innerHTML = '<li class="text-muted"><i class="fas fa-spinner fa-spin mr-1"></i>Checking…</li>';

        $(modal).modal('show');

        fetch('/dashboard/media/' + id + '/usage')
            .then(r => r.json())
            .then(usages => {
                usagesEl.innerHTML = '';
                const badge = modal.querySelector('#detail-usage-badge');
                if (usages.length === 0) {
                    badge.className     = 'usage-badge unused';
                    badge.textContent   = 'Unused';
                    usagesEl.innerHTML  = '<li class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i>Not used anywhere — safe to delete.</li>';
                    modal.querySelector('#delete-media-btn').removeAttribute('disabled');
                } else {
                    badge.className   = 'usage-badge used';
                    badge.textContent = usages.length + ' usage' + (usages.length > 1 ? 's' : '');
                    modal.querySelector('#delete-media-btn').setAttribute('disabled', 'true');
                    usages.forEach(u => {
                        const li = document.createElement('li');
                        li.className = 'mb-1';
                        li.innerHTML = `<span class="badge badge-secondary mr-1">${u.type}</span> `
                            + (u.url !== '#' ? `<a href="${u.url}" target="_blank">${u.title}</a>` : u.title);
                        usagesEl.appendChild(li);
                    });
                }
            })
            .catch(() => {
                usagesEl.innerHTML = '<li class="text-muted"><i class="fas fa-question-circle mr-1"></i>Could not verify usage.</li>';
                modal.querySelector('#delete-media-btn').removeAttribute('disabled');
            });
    }

    // ── Bulk Selection ────────────────────────────────────────────────────────
    function initBulkMode() {
        const toggleBtn = document.getElementById('bulk-toggle-btn');
        const cancelBtn = document.getElementById('bulk-cancel-btn');
        const selectAll = document.getElementById('bulk-select-all');
        const deselect  = document.getElementById('bulk-deselect');
        const deleteBtn = document.getElementById('bulk-delete-btn');
        const actionBar = document.getElementById('bulk-action-bar');
        const grid      = document.getElementById('media-grid');
        if (!toggleBtn || !grid) return;

        function enterBulkMode() {
            grid.classList.add('bulk-mode');
            toggleBtn.classList.add('active');
            updateBulkBar();
        }
        function exitBulkMode() {
            grid.classList.remove('bulk-mode');
            toggleBtn.classList.remove('active');
            document.querySelectorAll('.media-checkbox').forEach(cb => cb.checked = false);
            document.querySelectorAll('.media-item-card').forEach(c => c.classList.remove('selected'));
            if (actionBar) { actionBar.classList.remove('visible'); }
        }

        toggleBtn.addEventListener('click', () => {
            grid.classList.contains('bulk-mode') ? exitBulkMode() : enterBulkMode();
        });
        if (cancelBtn) cancelBtn.addEventListener('click', exitBulkMode);

        if (selectAll) selectAll.addEventListener('click', () => {
            document.querySelectorAll('.media-checkbox').forEach(cb => {
                cb.checked = true;
                cb.closest('.media-item-card')?.classList.add('selected');
            });
            updateBulkBar();
        });
        if (deselect) deselect.addEventListener('click', () => {
            document.querySelectorAll('.media-checkbox').forEach(cb => {
                cb.checked = false;
                cb.closest('.media-item-card')?.classList.remove('selected');
            });
            updateBulkBar();
        });

        document.addEventListener('change', e => {
            if (e.target.classList.contains('media-checkbox')) {
                const card = e.target.closest('.media-item-card');
                if (card) card.classList.toggle('selected', e.target.checked);
                updateBulkBar();
            }
        });

        if (deleteBtn) deleteBtn.addEventListener('click', () => {
            const ids = getSelectedIds();
            if (ids.length === 0) return;
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Delete ' + ids.length + ' file(s)?',
                    text: 'Files currently in use will NOT be deleted.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Yes, delete',
                }).then(r => { if (r.isConfirmed) submitBulkDelete(ids); });
            } else {
                if (confirm('Delete ' + ids.length + ' file(s)?')) submitBulkDelete(ids);
            }
        });

        function submitBulkDelete(ids) {
            const form      = document.getElementById('bulk-delete-form');
            const container = document.getElementById('bulk-ids-container');
            container.innerHTML = '';
            ids.forEach(id => {
                const inp = document.createElement('input');
                inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = id;
                container.appendChild(inp);
            });
            form.submit();
        }

        function updateBulkBar() {
            const ids = getSelectedIds();
            const countEl = document.getElementById('bulk-count');
            if (countEl) countEl.textContent = ids.length + ' selected';
            if (actionBar) actionBar.classList.toggle('visible', grid.classList.contains('bulk-mode'));
        }
    }

    function toggleCardSelect(card) {
        const cb = card.querySelector('.media-checkbox');
        if (cb) { cb.checked = !cb.checked; card.classList.toggle('selected', cb.checked); }
        const event = new Event('change', { bubbles: true });
        cb?.dispatchEvent(event);
    }

    function getSelectedIds() {
        return [...document.querySelectorAll('.media-checkbox:checked')].map(cb => cb.dataset.id);
    }

    // ── Copy URL ──────────────────────────────────────────────────────────────
    function initCopyButtons() {
        document.addEventListener('click', e => {
            const btn = e.target.closest('.copy-link-btn');
            if (!btn) return;
            e.stopPropagation();
            const url = btn.getAttribute('data-url');
            navigator.clipboard.writeText(url).then(() => {
                toast('success', 'URL copied to clipboard!');
            }).catch(() => {
                toast('error', 'Copy failed — please copy manually: ' + url);
            });
        });
    }

    // ── Quick Dropzone (sidebar on index) ─────────────────────────────────────
    function initQuickDropzone() {
        const zone = document.getElementById('quick-dropzone');
        const inp  = document.getElementById('quick-file-input');
        const queueEl = document.getElementById('upload-queue');
        if (!zone || !inp || !queueEl) return;

        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content
            || document.querySelector('input[name="_token"]')?.value || '';

        ['dragenter','dragover'].forEach(ev => zone.addEventListener(ev, e => {
            e.preventDefault(); zone.classList.add('dragover');
        }));
        ['dragleave','drop'].forEach(ev => zone.addEventListener(ev, e => {
            e.preventDefault(); zone.classList.remove('dragover');
        }));
        zone.addEventListener('drop', e => uploadFiles([...e.dataTransfer.files]));
        inp.addEventListener('change', () => { uploadFiles([...inp.files]); inp.value = ''; });

        function uploadFiles(files) {
            files.forEach((file, idx) => {
                const key  = 'qq-' + Date.now() + idx;
                const item = document.createElement('div');
                item.className = 'upload-queue-item'; item.id = key;
                item.innerHTML = `
                    <span class="item-name" title="${escHtml(file.name)}">${escHtml(file.name)}</span>
                    <div class="item-progress"><div class="progress"><div class="progress-bar bg-primary" id="pb-${key}" style="width:0%"></div></div></div>
                    <span class="item-status" id="ps-${key}">…</span>`;
                queueEl.appendChild(item);

                const fd = new FormData();
                fd.append('_token', CSRF || '');
                fd.append('image', file);

                const xhr = new XMLHttpRequest();
                xhr.open('POST', '/dashboard/media');
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.upload.onprogress = e => {
                    if (e.lengthComputable) {
                        document.getElementById('pb-' + key).style.width = Math.round(e.loaded/e.total*100) + '%';
                    }
                };
                xhr.onload = () => {
                    const pb = document.getElementById('pb-' + key);
                    const ps = document.getElementById('ps-' + key);
                    if (xhr.status >= 200 && xhr.status < 300) {
                        if (pb) { pb.style.width = '100%'; pb.className = 'progress-bar bg-success'; }
                        if (ps) { ps.textContent = 'Done'; ps.className = 'item-status done'; }
                        setTimeout(() => { item.remove(); location.reload(); }, 1800);
                    } else {
                        if (pb) { pb.className = 'progress-bar bg-danger'; pb.style.width = '100%'; }
                        if (ps) { ps.textContent = 'Error'; ps.className = 'item-status error'; }
                    }
                };
                xhr.onerror = () => {
                    const ps = document.getElementById('ps-' + key);
                    if (ps) { ps.textContent = 'Failed'; ps.className = 'item-status error'; }
                };
                xhr.send(fd);
            });
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    function toast(icon, title) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ toast:true, position:'top-end', icon, title, showConfirmButton:false, timer:2000 });
        }
    }

    function escHtml(str) {
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

})();
