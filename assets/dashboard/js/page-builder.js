/**
 * Page Builder JS — V3 (RuangAiTi)
 * Handles: drag-sort sections, toggle item editors, add/remove items inline
 */

var PageBuilder = (function () {

    var _config = {};

    /* ---------- Sortable drag-drop ---------- */
    function initSortable(el, sortUrl, token) {
        var list = document.querySelector(el);
        if (!list) return;

        var drake = dragula([list], {
            moves: function (el, container, handle) {
                return handle.classList.contains('fa-grip-vertical') ||
                    handle.closest('.drag-handle') !== null;
            }
        });

        drake.on('drop', function () {
            var ids = [];
            list.querySelectorAll('.section-item[data-id]').forEach(function (item) {
                ids.push(item.getAttribute('data-id'));
            });

            fetch(sortUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ ids: ids })
            });
        });
    }

    /* ---------- Toggle item editor panels ---------- */
    function initToggleEditors() {
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.toggle-items-btn');
            if (!btn) return;

            var targetId = btn.getAttribute('data-target');
            var panel = document.querySelector(targetId);
            if (!panel) return;

            var isVisible = panel.style.display !== 'none';
            panel.style.display = isVisible ? 'none' : 'block';
            btn.innerHTML = isVisible
                ? '<i class="fas fa-edit"></i> Edit Items'
                : '<i class="fas fa-times"></i> Close Editor';
        });
    }

    /* ---------- Add item row ---------- */
    function initAddItem() {
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.add-item-btn');
            if (!btn) return;

            var containerId = btn.getAttribute('data-container');
            var container = document.getElementById(containerId);
            if (!container) return;

            var index = container.querySelectorAll('.section-item-row').length;

            var row = document.createElement('div');
            row.className = 'section-item-row border rounded p-2 mb-2 bg-light';
            row.innerHTML = '<div class="row">' +
                '<div class="col-md-6"><label class="small">Title</label>' +
                '<input type="text" name="items[' + index + '][title]" class="form-control form-control-sm" placeholder="Title"></div>' +
                '<div class="col-md-6"><label class="small">Subtitle</label>' +
                '<input type="text" name="items[' + index + '][subtitle]" class="form-control form-control-sm" placeholder="Subtitle"></div>' +
                '<div class="col-12 mt-1"><label class="small">Content</label>' +
                '<textarea name="items[' + index + '][content]" rows="2" class="form-control form-control-sm" placeholder="Content..."></textarea></div>' +
                '<div class="col-md-6 mt-1"><label class="small">Image URL</label>' +
                '<input type="text" name="items[' + index + '][image]" class="form-control form-control-sm" placeholder="https://..."></div>' +
                '<div class="col-md-6 mt-1"><label class="small">Link URL</label>' +
                '<input type="text" name="items[' + index + '][link]" class="form-control form-control-sm" placeholder="https://..."></div>' +
                '</div>' +
                '<button type="button" class="btn btn-xs btn-danger mt-1 remove-item-btn"><i class="fas fa-times"></i> Remove</button>';

            container.appendChild(row);
            reindexRows(container);
        });
    }

    /* ---------- Remove item row ---------- */
    function initRemoveItem() {
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.remove-item-btn');
            if (!btn) return;

            var row = btn.closest('.section-item-row');
            if (row) {
                var container = row.parentElement;
                row.remove();
                reindexRows(container);
            }
        });
    }

    /* ---------- Reindex item names after add/remove ---------- */
    function reindexRows(container) {
        if (!container) return;
        container.querySelectorAll('.section-item-row').forEach(function (row, index) {
            row.querySelectorAll('[name]').forEach(function (el) {
                el.name = el.name.replace(/items\[\d+\]/, 'items[' + index + ']');
            });
        });
    }

    /* ---------- Public API ---------- */
    return {
        init: function (config) {
            _config = config || {};

            // Dragula is loaded via CDN in master or via adminlte plugins
            if (typeof dragula !== 'undefined' && _config.sortableEl) {
                initSortable(_config.sortableEl, _config.sortUrl, _config.csrfToken);
            }

            initToggleEditors();
            initAddItem();
            initRemoveItem();
        }
    };

})();
