@extends('dashboard.master')
@section('title', 'Roadmap Builder - ' . $roadmap->title)

@section('style')
<link rel="stylesheet" href="{{ asset('assets/dashboard/css/roadmap-builder.css') }}"/>
<link rel="stylesheet" href="{{ asset('assets/dashboard/css/sortable.css') }}"/>
<link rel="stylesheet" href="{{ asset('assets/dashboard/css/roadmap-responsive.css') }}"/>
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="{{ $roadmap->icon ?: 'fas fa-route' }} mr-2 text-primary"></i>
                        {{ $roadmap->title }}
                        <span class="badge badge-info ml-2" style="font-size: 0.8rem;">{{ $roadmap->difficulty }}</span>
                    </h1>
                </div>
                <div class="col-sm-6 text-sm-right mt-2 mt-sm-0">
                    <span id="save-indicator" class="save-status-indicator mr-3">
                        <i class="fas fa-check-circle"></i> Saved
                    </span>
                    <button type="button" class="btn btn-primary" onclick="openModuleModal()">
                        <i class="fas fa-plus mr-1"></i> Add Module
                    </button>
                    <a href="{{ route('dashboard.roadmaps.index') }}" class="btn btn-default">
                        Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Empty state for Roadmap -->
            <div id="roadmap-empty-state" class="text-center py-5 {{ $roadmap->modules->count() > 0 ? 'd-none' : '' }}" style="background: rgba(0,0,0,0.02); border-radius: 8px; border: 2px dashed rgba(0,0,0,0.05);">
                <i class="fas fa-layer-group fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">Belum ada modul.</h4>
                <p class="text-muted mb-4">Tambahkan modul pertama untuk mulai menyusun kurikulum belajar.</p>
                <button type="button" class="btn btn-primary btn-sm" onclick="openModuleModal()">
                    <i class="fas fa-plus mr-1"></i> Tambah Modul
                </button>
            </div>

            <!-- Modules Draggable Container -->
            <div id="modules-container" class="sortable-modules">
                @foreach ($roadmap->modules as $module)
                <div class="card module-card" data-module-id="{{ $module->id }}" style="border-left-color: {{ $module->color ?: '#2563eb' }};">
                    <div class="module-header">
                        <span class="module-drag-handle"><i class="fas fa-grip-vertical"></i></span>
                        <span class="module-collapse-btn mr-2" onclick="toggleModuleBody(this)"><i class="fas fa-chevron-down"></i></span>
                        
                        <div class="module-title-section">
                            <h4 class="module-title-text">
                                <i class="{{ $module->icon ?: 'fas fa-book' }} mr-2" style="color: {{ $module->color ?: '#2563eb' }};"></i>
                                {{ $module->title }}
                            </h4>
                            <div class="module-subtitle-text module-subtitle">{{ $module->subtitle }}</div>
                        </div>

                        <div class="module-actions">
                            <span class="badge badge-light mr-3 article-count-badge">{{ $module->posts->count() }} Articles</span>
                            <button type="button" class="btn btn-default btn-xs mr-1" onclick="editModule({{ json_encode($module) }})" title="Edit Module">
                                <i class="fas fa-cog"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-xs" onclick="deleteModule({{ $module->id }})" title="Delete Module">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="module-body">
                        @if($module->description)
                        <p class="module-description-text module-description">{{ $module->description }}</p>
                        @else
                        <p class="module-description-text module-description d-none"></p>
                        @endif

                        <!-- Articles Draggable Container -->
                        <div class="articles-list sortable-articles" data-module-id="{{ $module->id }}">
                            @forelse ($module->posts as $post)
                            <div class="article-item" data-post-id="{{ $post->id }}">
                                <span class="article-drag-handle"><i class="fas fa-grip-vertical"></i></span>
                                <i class="far fa-file-alt text-primary"></i>
                                <div class="article-title-section">
                                    <div class="article-title font-weight-bold">{{ $post->title }}</div>
                                    <small class="text-muted">{{ $post->category ? $post->category->title : 'Uncategorized' }}</small>
                                </div>
                                <div class="article-meta">
                                    <span class="badge bg-secondary small">{{ $post->readTime() }}</span>
                                    <span class="article-remove-btn" onclick="removePost(this, {{ $module->id }}, {{ $post->id }})" title="Remove from module">
                                        <i class="fas fa-times-circle"></i>
                                    </span>
                                </div>
                            </div>
                            @empty
                            <!-- Inner empty state -->
                            <div class="empty-placeholder text-center py-3 text-muted">
                                <small>Belum ada artikel. Klik "Add Article" untuk menghubungkan artikel dari database.</small>
                            </div>
                            @endforelse
                        </div>

                        <button type="button" class="btn btn-outline-primary btn-sm btn-add-article-trigger" onclick="openSearchPalette({{ $module->id }})">
                            <i class="fas fa-plus mr-1"></i> Add Article
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
</div>

<!-- Add/Edit Module Modal -->
<div class="modal fade" id="moduleModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="moduleModalTitle">Add Module</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="moduleForm">
                <input type="hidden" name="module_id" id="modal_module_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="modal_title">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="modal_title" class="form-control" required placeholder="e.g. Fundamental">
                    </div>
                    <div class="form-group">
                        <label for="modal_subtitle">Subtitle</label>
                        <input type="text" name="subtitle" id="modal_subtitle" class="form-control" placeholder="e.g. Pengenalan konsep dasar cyber security">
                    </div>
                    <div class="form-group">
                        <label for="modal_icon">Icon Class</label>
                        <x-dashboard.icon-picker name="icon" value="fas fa-book" id="modal_icon" />
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="modal_color">Theme Color</label>
                                <input type="color" name="color" id="modal_color" class="form-control" value="#2563eb" style="height: 38px; padding: 2px;">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="modal_description">Description</label>
                        <textarea name="description" id="modal_description" class="form-control" rows="3" placeholder="Brief outline of the module topics"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="btn-save-module">Save Module</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Command-Palette Article Selector Modal -->
<div class="modal fade command-palette-modal" id="searchPaletteModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="command-search-wrapper">
                <i class="fas fa-search command-search-icon"></i>
                <input type="text" id="command-search" class="command-search-input" placeholder="Type to search articles by Title, Category, Tag or Slug..." autocomplete="off">
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="font-size: 1.5rem; margin-top: -2px;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <div class="command-results-list" id="command-results">
                <!-- Search results will be populated here -->
                <div class="text-center py-4 text-muted">
                    Start typing to search existing articles...
                </div>
            </div>

            <div class="command-palette-footer">
                <div>
                    <span>Press <kbd>Esc</kbd> to close.</span>
                </div>
                <div class="text-right">
                    <button type="button" class="btn btn-primary btn-xs px-3" id="btn-submit-articles">Insert Selected</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    let activeModuleId = null;
    let selectedPostIds = [];

    // Set up CSRF token for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).ready(function() {
        // Initialize Sortable on Modules
        $('#modules-container').sortable({
            handle: '.module-drag-handle',
            placeholder: 'module-card-placeholder',
            opacity: 0.8,
            update: function(event, ui) {
                showSaveIndicator('saving');
                let modulesOrder = $('#modules-container .module-card').map(function() {
                    return $(this).data('module-id');
                }).get();

                $.post("{{ route('dashboard.roadmaps.builder.sort_modules', $roadmap->id) }}", {
                    modules: modulesOrder
                })
                .done(function() {
                    showSaveIndicator('saved');
                })
                .fail(function() {
                    alert('Failed to save modules sort order.');
                    showSaveIndicator('error');
                });
            }
        });

        // Initialize Sortable on Articles
        initSortableArticles();

        // Handle Module Form Submit
        $('#moduleForm').submit(function(e) {
            e.preventDefault();
            let moduleId = $('#modal_module_id').val();
            let url = moduleId 
                ? "{{ route('dashboard.roadmaps.builder.module.rename', [$roadmap->id, ':id']) }}".replace(':id', moduleId)
                : "{{ route('dashboard.roadmaps.builder.module.add', $roadmap->id) }}";

            showSaveIndicator('saving');
            $('#btn-save-module').prop('disabled', true);

            $.post(url, $(this).serialize())
            .done(function(res) {
                $('#moduleModal').modal('hide');
                showSaveIndicator('saved');
                
                // If it was edit, update DOM elements
                if (moduleId) {
                    let card = $(`.module-card[data-module-id="${moduleId}"]`);
                    card.css('border-left-color', res.module.color);
                    card.find('.module-title-text').html(`<i class="${res.module.icon} mr-2" style="color: ${res.module.color};"></i> ${res.module.title}`);
                    card.find('.module-subtitle-text').text(res.module.subtitle || '');
                    
                    if (res.module.description) {
                        card.find('.module-description-text').removeClass('d-none').text(res.module.description);
                    } else {
                        card.find('.module-description-text').addClass('d-none').text('');
                    }
                } else {
                    // Refresh page or append new card (refreshing builder keeps jQuery UI states clean!)
                    window.location.reload();
                }
            })
            .fail(function(xhr) {
                alert('Error saving module information.');
                showSaveIndicator('error');
            })
            .always(function() {
                $('#btn-save-module').prop('disabled', false);
            });
        });

        // Live article search keyup binding
        let searchTimeout = null;
        $('#command-search').on('input', function() {
            clearTimeout(searchTimeout);
            let q = $(this).val();
            if (q.trim().length === 0) {
                $('#command-results').html('<div class="text-center py-4 text-muted">Start typing to search existing articles...</div>');
                return;
            }

            searchTimeout = setTimeout(function() {
                $('#command-results').html('<div class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin mr-1"></i> Searching...</div>');
                
                $.get("{{ route('dashboard.roadmaps.search_posts') }}", { q: q })
                .done(function(posts) {
                    if (posts.length === 0) {
                        $('#command-results').html('<div class="text-center py-4 text-muted">No articles found matching that query.</div>');
                        return;
                    }

                    let html = '';
                    posts.forEach(function(post) {
                        let isChecked = selectedPostIds.includes(post.id);
                        html += `
                            <div class="command-result-item ${isChecked ? 'selected' : ''}" data-post-id="${post.id}">
                                <img src="${post.thumbnail}" class="command-result-thumb" alt="">
                                <div class="command-result-info">
                                    <div class="command-result-title">${post.title}</div>
                                    <div class="command-result-meta">
                                        <span><i class="far fa-folder mr-1"></i> ${post.category}</span>
                                        <span><i class="far fa-clock mr-1"></i> ${post.read_time}</span>
                                        <span><i class="far fa-calendar mr-1"></i> ${post.published_date}</span>
                                    </div>
                                </div>
                                <div class="command-result-checkbox">
                                    <i class="${isChecked ? 'fas fa-check-circle' : 'far fa-circle'}"></i>
                                </div>
                            </div>
                        `;
                    });
                    $('#command-results').html(html);
                });
            }, 300);
        });

        // Select post toggle inside results list
        $(document).on('click', '.command-result-item', function() {
            let postId = $(this).data('post-id');
            let idx = selectedPostIds.indexOf(postId);
            
            if (idx === -1) {
                selectedPostIds.push(postId);
                $(this).addClass('selected');
                $(this).find('.command-result-checkbox i').removeClass('far fa-circle').addClass('fas fa-check-circle');
            } else {
                selectedPostIds.splice(idx, 1);
                $(this).removeClass('selected');
                $(this).find('.command-result-checkbox i').removeClass('fas fa-check-circle').addClass('far fa-circle');
            }
        });

        // Insert selected articles into module
        $('#btn-submit-articles').click(function() {
            if (selectedPostIds.length === 0) {
                alert('Please select at least one article.');
                return;
            }

            showSaveIndicator('saving');
            $(this).prop('disabled', true);
            let url = "{{ route('dashboard.roadmaps.builder.module.add_post', [$roadmap->id, ':moduleId']) }}".replace(':moduleId', activeModuleId);

            $.post(url, { post_ids: selectedPostIds })
            .done(function(res) {
                $('#searchPaletteModal').modal('hide');
                showSaveIndicator('saved');
                
                // Refresh to reload articles with proper order instantly
                window.location.reload();
            })
            .fail(function() {
                alert('Error adding articles to module.');
                showSaveIndicator('error');
            })
            .always(function() {
                $('#btn-submit-articles').prop('disabled', false);
            });
        });
    });

    // Initialize Sortable on Articles
    function initSortableArticles() {
        $('.sortable-articles').sortable({
            connectWith: '.sortable-articles',
            placeholder: 'ui-state-placeholder',
            opacity: 0.8,
            update: function(event, ui) {
                showSaveIndicator('saving');
                
                // Re-calculate counts and restructure arrays
                let structure = [];
                $('.module-card').each(function() {
                    let mId = $(this).data('module-id');
                    let pIds = $(this).find('.article-item').map(function() {
                        return $(this).data('post-id');
                    }).get();
                    
                    structure.push({ module_id: mId, posts: pIds });
                    
                    // Update inner badge count
                    $(this).find('.article-count-badge').text(pIds.length + ' Articles');
                    
                    // Toggle empty placeholder visibility
                    let emptyPlaceholder = $(this).find('.empty-placeholder');
                    if (pIds.length > 0) {
                        emptyPlaceholder.remove();
                    } else if (emptyPlaceholder.length === 0) {
                        $(this).find('.sortable-articles').append(`
                            <div class="empty-placeholder text-center py-3 text-muted">
                                <small>Belum ada artikel. Klik "Add Article" untuk menghubungkan artikel dari database.</small>
                            </div>
                        `);
                    }
                });

                $.post("{{ route('dashboard.roadmaps.builder.sort_posts', $roadmap->id) }}", {
                    structure: structure
                })
                .done(function() {
                    showSaveIndicator('saved');
                })
                .fail(function() {
                    alert('Failed to save articles sorting.');
                    showSaveIndicator('error');
                });
            }
        });
    }

    // Toggle Collapsing Module Card Bodies
    function toggleModuleBody(btn) {
        let card = $(btn).closest('.module-card');
        let body = card.find('.module-body');
        let icon = $(btn).find('i');
        
        body.slideToggle(200, function() {
            if (body.is(':visible')) {
                icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
                $(btn).removeClass('collapsed');
            } else {
                icon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
                $(btn).addClass('collapsed');
            }
        });
    }

    // Module modal helpers
    function openModuleModal() {
        $('#modal_module_id').val('');
        $('#moduleForm')[0].reset();
        $('#modal_color').val('#2563eb');
        $('#preview-icon-modal_icon').attr('class', 'fas fa-book');
        $('#moduleModalTitle').text('Add Module');
        $('#moduleModal').modal('show');
    }

    function editModule(module) {
        $('#modal_module_id').val(module.id);
        $('#modal_title').val(module.title);
        $('#modal_subtitle').val(module.subtitle || '');
        $('#modal_icon').val(module.icon || 'fas fa-book');
        $('#preview-icon-modal_icon').attr('class', module.icon || 'fas fa-book');
        $('#modal_color').val(module.color || '#2563eb');
        $('#modal_description').val(module.description || '');
        $('#moduleModalTitle').text('Rename/Edit Module');
        $('#moduleModal').modal('show');
    }

    function deleteModule(moduleId) {
        if (!confirm('Are you sure you want to delete this module? All post associations inside this module will be removed.')) return;
        
        showSaveIndicator('saving');
        let url = "{{ route('dashboard.roadmaps.builder.module.delete', [$roadmap->id, ':id']) }}".replace(':id', moduleId);
        
        $.ajax({
            url: url,
            type: 'DELETE',
            success: function() {
                $(`.module-card[data-module-id="${moduleId}"]`).slideUp(300, function() {
                    $(this).remove();
                    if ($('.module-card').length === 0) {
                        $('#roadmap-empty-state').removeClass('d-none');
                    }
                });
                showSaveIndicator('saved');
            },
            error: function() {
                alert('Failed to delete module.');
                showSaveIndicator('error');
            }
        });
    }

    // Article action helpers
    function openSearchPalette(moduleId) {
        activeModuleId = moduleId;
        selectedPostIds = [];
        $('#command-search').val('');
        $('#command-results').html('<div class="text-center py-4 text-muted">Start typing to search existing articles...</div>');
        $('#searchPaletteModal').modal('show');
        setTimeout(function() {
            $('#command-search').focus();
        }, 400);
    }

    function removePost(btn, moduleId, postId) {
        if (!confirm('Remove this article from the learning module?')) return;
        
        showSaveIndicator('saving');
        let item = $(btn).closest('.article-item');
        let url = "{{ route('dashboard.roadmaps.builder.module.remove_post', [$roadmap->id, ':moduleId', ':postId']) }}"
            .replace(':moduleId', moduleId)
            .replace(':postId', postId);

        $.ajax({
            url: url,
            type: 'DELETE',
            success: function() {
                item.slideUp(200, function() {
                    let parent = $(this).parent();
                    $(this).remove();
                    
                    // Update badges
                    let card = $(`.module-card[data-module-id="${moduleId}"]`);
                    let count = card.find('.article-item').length;
                    card.find('.article-count-badge').text(count + ' Articles');
                    
                    if (count === 0 && card.find('.empty-placeholder').length === 0) {
                        parent.append(`
                            <div class="empty-placeholder text-center py-3 text-muted">
                                <small>Belum ada artikel. Klik "Add Article" untuk menghubungkan artikel dari database.</small>
                            </div>
                        `);
                    }
                });
                showSaveIndicator('saved');
            },
            error: function() {
                alert('Failed to remove article.');
                showSaveIndicator('error');
            }
        });
    }

    // Save status notification indicator
    function showSaveIndicator(status) {
        let indicator = $('#save-indicator');
        indicator.removeClass('visible saving saved error text-danger');
        
        if (status === 'saving') {
            indicator.addClass('visible saving').html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');
        } else if (status === 'saved') {
            indicator.addClass('visible saved').html('<i class="fas fa-check-circle mr-1"></i> Saved');
            setTimeout(function() {
                indicator.removeClass('visible');
            }, 2000);
        } else if (status === 'error') {
            indicator.addClass('visible text-danger').html('<i class="fas fa-exclamation-triangle mr-1"></i> Error');
        }
    }
</script>
@endsection
