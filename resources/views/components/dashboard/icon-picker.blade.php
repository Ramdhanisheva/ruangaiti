@props(['name' => 'icon', 'value' => '', 'id' => 'icon-picker-input'])

@php
    $selectedIcon = old($name, $value) ?: 'fas fa-star';
@endphp

<div class="icon-picker-wrapper" id="wrapper-{{ $id }}">
    <input type="hidden" name="{{ $name }}" id="{{ $id }}" value="{{ $selectedIcon }}">
    
    <div class="d-flex align-items-center">
        <div class="icon-picker-preview mr-3 text-center border rounded d-flex align-items-center justify-content-center" 
             style="width: 50px; height: 50px; background: #f8f9fa; font-size: 1.5rem; color: #495057;">
            <i class="{{ $selectedIcon }}" id="preview-icon-{{ $id }}"></i>
        </div>
        <div>
            <button type="button" class="btn btn-outline-primary btn-sm font-weight-bold" data-toggle="modal" data-target="#modal-{{ $id }}">
                <i class="fas fa-search mr-1"></i> Choose Icon…
            </button>
            <small class="text-muted d-block mt-1">Select visual indicator icon</small>
        </div>
    </div>

    {{-- Bootstrap Modal Icon Picker --}}
    <div class="modal fade icon-picker-modal" id="modal-{{ $id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-icons mr-1 text-primary"></i> Select Icon</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {{-- Search and filter toolbar --}}
                    <div class="row mb-3">
                        <div class="col-md-6 form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-search text-muted"></i></span>
                                </div>
                                <input type="text" class="form-control icon-search-input" placeholder="Search icon by name…">
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <select class="form-control icon-category-select">
                                <option value="all">All Icons</option>
                                <option value="recent">Recently Used</option>
                                <option value="favorites">Favorites</option>
                                <option value="tech">Technology & Coding</option>
                                <option value="education">Education & Learning</option>
                                <option value="security">Security & Encryption</option>
                                <option value="business">Business & Statistics</option>
                                <option value="media">Media & Visuals</option>
                            </select>
                        </div>
                    </div>

                    {{-- Icons grid --}}
                    <div class="border rounded p-3" style="max-height: 350px; overflow-y: auto; background: #fafafa;">
                        <div class="row text-center no-gutters icon-grid-container">
                            {{-- Clickable icons will be generated here by JS --}}
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <div>
                        <span class="text-muted">Selected: <strong class="text-primary selected-icon-name-label">{{ $selectedIcon }}</strong></span>
                    </div>
                    <div>
                        <button type="button" class="btn btn-outline-danger btn-sm font-weight-bold toggle-favorite-btn">
                            <i class="far fa-star mr-1"></i> Add to Favorites
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@once
@push('head')
<style>
    .icon-picker-grid-item {
        padding: 12px;
        font-size: 1.4rem;
        color: #495057;
        cursor: pointer;
        border-radius: 4px;
        transition: all 0.15s ease;
    }
    .icon-picker-grid-item:hover {
        background: #007bff;
        color: #fff !important;
        transform: scale(1.15);
    }
</style>
@endpush
@endonce

@push('js')
<script>
$(document).ready(function() {
    const pickerId = "{{ $id }}";
    const wrapper = $('#wrapper-' + pickerId);
    const modal = $('#modal-' + pickerId);
    const grid = modal.find('.icon-grid-container');
    const searchInp = modal.find('.icon-search-input');
    const categorySel = modal.find('.icon-category-select');
    const favBtn = modal.find('.toggle-favorite-btn');
    const label = modal.find('.selected-icon-name-label');

    // FontAwesome 5 predefined icons dictionary by category
    const iconList = [
        // Tech
        { class: 'fas fa-code', category: 'tech', keywords: 'code programming html tags dev' },
        { class: 'fas fa-laptop-code', category: 'tech', keywords: 'laptop developer write engineer' },
        { class: 'fas fa-server', category: 'tech', keywords: 'server cloud hosting backend databases data' },
        { class: 'fas fa-database', category: 'tech', keywords: 'database sql tables store server' },
        { class: 'fas fa-terminal', category: 'tech', keywords: 'terminal bash shell CLI execution prompt run' },
        { class: 'fas fa-microchip', category: 'tech', keywords: 'microchip hardware processor cpu iot' },
        { class: 'fas fa-network-wired', category: 'tech', keywords: 'network connection wire ethernet routing lan' },
        { class: 'fas fa-cloud', category: 'tech', keywords: 'cloud dynamic storage web network online' },
        
        // Education
        { class: 'fas fa-book', category: 'education', keywords: 'book reading study course modules learn guide' },
        { class: 'fas fa-graduation-cap', category: 'education', keywords: 'graduation cap student academy university pass' },
        { class: 'fas fa-school', category: 'education', keywords: 'school study institution education classes teacher' },
        { class: 'fas fa-lightbulb', category: 'education', keywords: 'lightbulb idea analytics insight thoughts create' },
        { class: 'fas fa-chalkboard-teacher', category: 'education', keywords: 'teacher presentation classes whiteboard learn' },
        { class: 'fas fa-award', category: 'education', keywords: 'award medal win score check modules certificate' },
        { class: 'fas fa-certificate', category: 'education', keywords: 'certificate pass finish validation official seal' },
        
        // Security
        { class: 'fas fa-shield-alt', category: 'security', keywords: 'shield protection encryption ssl admin guard' },
        { class: 'fas fa-lock', category: 'security', keywords: 'lock closed private key protection encryption auth' },
        { class: 'fas fa-key', category: 'security', keywords: 'key access login door secrets verify authentication' },
        { class: 'fas fa-user-shield', category: 'security', keywords: 'user guard moderator firewall credential safety' },
        { class: 'fas fa-fingerprint', category: 'security', keywords: 'fingerprint biometrics security private auth logs' },
        { class: 'fas fa-file-signature', category: 'security', keywords: 'file signature legal signed audit contract write' },
        
        // Business
        { class: 'fas fa-chart-line', category: 'business', keywords: 'chart analytics increase views content growth page' },
        { class: 'fas fa-chart-pie', category: 'business', keywords: 'chart pie distribution stats analytics data division' },
        { class: 'fas fa-briefcase', category: 'business', keywords: 'briefcase work job company portfolio documents bag' },
        { class: 'fas fa-folder', category: 'business', keywords: 'folder archive catalog list directory storage path' },
        { class: 'fas fa-cogs', category: 'business', keywords: 'cogs setup configs gears settings preferences tools' },
        { class: 'fas fa-route', category: 'business', keywords: 'route maps navigation road roadmaps path learn course' },
        { class: 'fas fa-compass', category: 'business', keywords: 'compass target navigation travel explore map directions' },

        // Media
        { class: 'fas fa-images', category: 'media', keywords: 'images gallery photo media library graphics banners' },
        { class: 'fas fa-video', category: 'media', keywords: 'video player clips films cameras streaming youtube' },
        { class: 'fas fa-music', category: 'media', keywords: 'music audio sounds player spotify songs track' },
        { class: 'fas fa-camera', category: 'media', keywords: 'camera snapshot lenses photos captures picture' },
        { class: 'fas fa-volume-up', category: 'media', keywords: 'volume speaker sound loud speak play podcast' }
    ];

    // LocalStorage helper keys
    const recentsKey = 'icon_picker_recent';
    const favsKey = 'icon_picker_favorites';

    function getRecents() {
        return JSON.parse(localStorage.getItem(recentsKey)) || [];
    }

    function addRecent(iconClass) {
        let recents = getRecents();
        recents = recents.filter(x => x !== iconClass);
        recents.unshift(iconClass);
        if (recents.length > 10) recents.pop();
        localStorage.setItem(recentsKey, JSON.stringify(recents));
    }

    function getFavorites() {
        return JSON.parse(localStorage.getItem(favsKey)) || [];
    }

    function toggleFavorite(iconClass) {
        let favs = getFavorites();
        if (favs.includes(iconClass)) {
            favs = favs.filter(x => x !== iconClass);
            favBtn.html('<i class="far fa-star mr-1"></i> Add to Favorites');
        } else {
            favs.push(iconClass);
            favBtn.html('<i class="fas fa-star mr-1"></i> Favorited');
        }
        localStorage.setItem(favsKey, JSON.stringify(favs));
        updateFavoriteButtonState(iconClass);
    }

    function updateFavoriteButtonState(iconClass) {
        let favs = getFavorites();
        if (favs.includes(iconClass)) {
            favBtn.removeClass('btn-outline-danger').addClass('btn-danger').html('<i class="fas fa-star mr-1"></i> Favorited');
        } else {
            favBtn.removeClass('btn-danger').addClass('btn-outline-danger').html('<i class="far fa-star mr-1"></i> Add to Favorites');
        }
    }

    // Render grid
    function renderGrid() {
        grid.empty();
        const search = searchInp.val().toLowerCase();
        const category = categorySel.val();
        let listToRender = [];

        if (category === 'recent') {
            listToRender = getRecents().map(x => ({ class: x, category: 'recent', keywords: '' }));
        } else if (category === 'favorites') {
            listToRender = getFavorites().map(x => ({ class: x, category: 'favorites', keywords: '' }));
        } else {
            listToRender = iconList.filter(item => {
                const matchCategory = (category === 'all' || item.category === category);
                const matchSearch = (!search || item.class.toLowerCase().includes(search) || item.keywords.includes(search));
                return matchCategory && matchSearch;
            });
        }

        if (listToRender.length === 0) {
            grid.append('<div class="col-12 py-4 text-muted">No icons match criteria.</div>');
            return;
        }

        listToRender.forEach(item => {
            const el = $('<div class="col-2 col-md-1 icon-picker-grid-item text-center" title="' + item.class + '"><i class="' + item.class + '"></i></div>');
            el.on('click', function() {
                // Select icon
                const selected = item.class;
                $('#' + pickerId).val(selected);
                $('#preview-icon-' + pickerId).attr('class', selected);
                label.text(selected);
                addRecent(selected);
                updateFavoriteButtonState(selected);
                modal.modal('hide');
            });
            grid.append(el);
        });
    }

    // Event listeners
    searchInp.on('input', renderGrid);
    categorySel.on('change', renderGrid);
    
    favBtn.on('click', function() {
        const curIcon = label.text();
        toggleFavorite(curIcon);
        if (categorySel.val() === 'favorites') {
            renderGrid();
        }
    });

    modal.on('show.bs.modal', function() {
        const val = $('#' + pickerId).val();
        label.text(val);
        updateFavoriteButtonState(val);
        renderGrid();
    });
});
</script>
@endpush
