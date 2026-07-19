@extends('dashboard.master')
@section('title', 'Edit Post')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Edit Post</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route("dashboard.home") }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route("dashboard.posts.index") }}">All Posts</a></li>
                        <li class="breadcrumb-item active">Edit Post</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Edit Post</h3>
                        </div>
                        <div class="card-body">
                            @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h5><i class="icon fas fa-ban"></i> Error!</h5>
                                @foreach ($errors->all() as $error)
                                <p class="m-0">{{ $error }}</p>
                                @endforeach
                            </div>
                            @endif
                            @if (session("success"))
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h5><i class="icon fas fa-check"></i> Success!</h5>
                                <p class="m-0">{{ session("success") }}</p>
                            </div>
                            @endif
                            <form action="{{ route("dashboard.posts.update", $post->id) }}" enctype="multipart/form-data" method="POST">
                                @csrf
                                @method("PUT")
                                <div class="row">
                                    <div class="col-md-8 mx-auto">
                                        <div class="card card-primary card-outline card-outline-tabs" style="border-top: 3px solid #007bff; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-radius: 4px; background: #fff;">
                                            <div class="card-header p-0 border-bottom-0">
                                                <ul class="nav nav-tabs" id="post-edit-tabs" role="tablist">
                                                    <li class="nav-item">
                                                        <a class="nav-link active font-weight-bold" id="post-details-tab" data-toggle="pill" href="#post-details" role="tab" aria-controls="post-details" aria-selected="true">
                                                            <i class="fas fa-edit mr-1 text-primary"></i> Post Details
                                                        </a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link font-weight-bold" id="post-chapters-tab" data-toggle="pill" href="#post-chapters" role="tab" aria-controls="post-chapters" aria-selected="false">
                                                            <i class="fas fa-book mr-1 text-success"></i> Halaman Tambahan ({{ $post->chapters()->count() }})
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="card-body">
                                                <div class="tab-content" id="post-edit-tabs-content">
                                                    <!-- Tab 1: Details -->
                                                    <div class="tab-pane fade show active" id="post-details" role="tabpanel" aria-labelledby="post-details-tab">
                                                        <div class="form-group">
                                                            <label for="title">Title</label>
                                                            <input type="text" class="form-control" id="title" name="title" placeholder="Enter title" value="{{ $post->title }}"/>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="first_page_title">Judul Halaman Pertama <span class="text-muted">(Opsional, default: Pendahuluan)</span></label>
                                                            <input type="text" class="form-control" id="first_page_title" name="first_page_title" placeholder="Contoh: Pendahuluan, Ringkasan, Deskripsi..." value="{{ $post->first_page_title ?? '' }}"/>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="slug">Slug</label>
                                                            <input type="text" class="form-control" id="slug" name="slug" placeholder="Enter slug" value="{{ $post->slug }}"/>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                                <label for="content" class="m-0">Content</label>
                                                                <div class="btn-group">
                                                                    <button type="button" class="btn btn-xs btn-outline-secondary dropdown-toggle font-weight-bold" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                        <i class="fas fa-file-import mr-1"></i> Import Document
                                                                    </button>
                                                                    <div class="dropdown-menu dropdown-menu-right p-3" style="min-width: 280px; box-shadow: 0 4px 12px rgba(0,0,0,.15); border-radius: 8px;">
                                                                        <h6 class="dropdown-header px-0 font-weight-bold text-dark mb-2">Choose File to Import</h6>
                                                                        <input type="file" id="import-document-input" class="form-control-file mb-2" accept=".md,.markdown,.docx,.pdf,.html,.htm" style="font-size: .85rem;">
                                                                        <small class="text-muted d-block mb-3">Supports Markdown, Word (.docx), PDF, HTML files (Max 10MB)</small>
                                                                        <button type="button" class="btn btn-primary btn-xs btn-block font-weight-bold" id="import-document-btn">
                                                                            <span id="import-btn-text">Parse & Inject Content</span>
                                                                            <span id="import-btn-spinner" class="d-none"><i class="fas fa-spinner fa-spin"></i> Parsing...</span>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <textarea class="form-control" id="content" name="content" placeholder="Write content">{{ $post->content }}</textarea>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Tab 2: Chapters -->
                                                    <div class="tab-pane fade" id="post-chapters" role="tabpanel" aria-labelledby="post-chapters-tab">
                                                        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                                            <h5 class="m-0 font-weight-bold text-dark"><i class="fas fa-layer-group text-primary mr-1"></i> Urutan Halaman Tambahan</h5>
                                                            <button type="button" class="btn btn-sm btn-success" id="btn-add-chapter">
                                                                <i class="fas fa-plus-circle mr-1"></i> Tambah Halaman
                                                            </button>
                                                        </div>

                                                        <div class="alert alert-info py-2" id="chapter-reorder-alert" style="display:none; font-size: 0.85rem;">
                                                            <i class="fas fa-sync-alt fa-spin mr-1"></i> Menyimpan urutan halaman...
                                                        </div>

                                                        <ul class="list-group ui-sortable" id="chapters-sortable-list" style="cursor: move; min-height: 100px;">
                                                            @forelse($post->chapters as $index => $chapter)
                                                                <li class="list-group-item d-flex align-items-center justify-content-between p-3 mb-2" data-id="{{ $chapter->id }}" style="border-left: 4px solid #007bff; border-radius: 4px; background: #f8f9fa; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                                                                    <div class="d-flex align-items-center">
                                                                        <span class="drag-handle mr-3 text-muted" style="cursor: grab;"><i class="fas fa-bars fa-lg"></i></span>
                                                                        <div>
                                                                            <span class="badge badge-primary px-2 py-1 mr-2" style="font-size: 0.85rem;">Halaman {{ $index + 2 }}</span>
                                                                            <span class="chapter-title-display font-weight-bold text-dark">{{ $chapter->title ?: '(Halaman Tanpa Judul)' }}</span>
                                                                        </div>
                                                                    </div>
                                                                    <div>
                                                                        <button type="button" class="btn btn-xs btn-info btn-edit-chapter mr-1" data-id="{{ $chapter->id }}" data-title="{{ $chapter->title }}">
                                                                            <i class="fas fa-edit"></i> Edit
                                                                        </button>
                                                                        <button type="button" class="btn btn-xs btn-danger btn-delete-chapter" data-id="{{ $chapter->id }}">
                                                                            <i class="fas fa-trash-alt"></i> Hapus
                                                                        </button>
                                                                    </div>
                                                                    <div class="chapter-content-hidden d-none">{!! $chapter->content !!}</div>
                                                                </li>
                                                            @empty
                                                                <div class="text-center py-5 text-muted" id="chapters-empty-state" style="background: #fdfdfd; border: 2px dashed #e9ecef; border-radius: 6px;">
                                                                    <i class="fas fa-book-open fa-3x mb-3 text-secondary" style="opacity: 0.5;"></i>
                                                                    <h6 class="font-weight-bold mb-1">Belum ada halaman tambahan</h6>
                                                                    <p class="m-0" style="font-size: 0.85rem;">Artikel ini saat ini berstatus single-page. Klik "Tambah Halaman" untuk membaginya menjadi beberapa halaman.</p>
                                                                </div>
                                                            @endforelse
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="sticky-top" style="top: 20px; z-index: 1020;">
                                            <div class="card card-primary card-outline" style="border-top: 3px solid #007bff; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-radius: 4px; background: #fff;">
                                                <div class="card-header d-flex align-items-center justify-content-between p-2 px-3">
                                                    <h3 class="card-title font-weight-bold m-0" style="font-size: 0.95rem;"><i class="fas fa-paper-plane text-primary mr-1"></i> Publish</h3>
                                                    <button class="btn btn-sm btn-primary font-weight-bold px-3" type="submit">
                                                        <i class="fas fa-save mr-1"></i> Update
                                                    </button>
                                                </div>
                                                <div class="card-body">
                                                    <div class="form-group">
                                                        <label for="category">Category</label>
                                                        <select class="form-control" name="category" id="category" style="width: 100%;">
                                                            @foreach ($categories as $category)
                                                            <option value="{{ $category->id }}" {{ $post->category_id == $category->id ? "selected" : "" }}>{{ $category->title }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="tags">Tags</label>
                                                        <div class="select2-purple">
                                                            <select multiple="multiple" data-placeholder="Select tag" data-dropdown-css-class="select2-purple" class="form-control" name="tags[]" id="tags" style="width: 100%;">
                                                                @foreach ($tags as $tag)
                                                                <option value="{{ $tag->name }}">{{ $tag->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="thumbnail">Thumbnail</label>
                                                        <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/*"/>
                                                        <img id="thumbnailpreview" class="img-fluid img-thumbnail mt-3" src="{{ asset("uploads/post/".$post->thumbnail) }}"/>
                                                    </div>
                                                    <div class="align-items-center d-flex form-group justify-content-between">
                                                        <label for="featured">Featured</label>
                                                        <div class="icheck-success d-inline">
                                                            <input type="checkbox" name="featured" id="featured" value="1" {{ $post->is_featured ? "checked" : "" }}/>
                                                            <label for="featured"></label>
                                                        </div>
                                                    </div>
                                                    <div class="align-items-center d-flex form-group justify-content-between">
                                                        <label for="comment">Enable Comment</label>
                                                        <div class="icheck-success d-inline">
                                                            <input type="checkbox" name="comment" id="comment" value="1" {{ $post->enable_comment ? "checked" : "" }}/>
                                                            <label for="comment"></label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="status">Status</label>
                                                        <select class="form-control" name="status" id="status">
                                                            @if (auth()->user()->role != 1)
                                                            <option value="1" {{ $post->status ? "selected" : "" }}>Publish</option>
                                                            @endif
                                                            <option value="0" {{ !$post->status ? "selected" : "" }}>Draft</option>
                                                        </select>
                                                    </div>

                                                    <div class="card card-dark mt-4 mb-0">
                                                        <div class="card-header" style="padding: .5rem 1rem;">
                                                            <h3 class="card-title" style="font-size: 0.9rem;">Roadmap Integration</h3>
                                                        </div>
                                                        <div class="card-body" style="padding: 1rem;">
                                                            @if($post->roadmapModulePost)
                                                            <!-- If already assigned, show READ ONLY metadata -->
                                                            <div class="alert alert-info py-2 px-3 mb-2" style="font-size: 0.85rem; border-radius: 4px;">
                                                                <strong>Active Roadmap:</strong><br>
                                                                {{ $post->roadmapModulePost->module->roadmap->title }}<br>
                                                                <strong>Module:</strong> {{ $post->roadmapModulePost->module->title }}
                                                            </div>
                                                            <a href="{{ route('dashboard.roadmaps.builder', $post->roadmapModulePost->module->roadmap_id) }}" class="btn btn-block btn-outline-info btn-xs mb-3">
                                                                <i class="fas fa-cubes"></i> Manage in Builder →
                                                            </a>
                                                            @endif

                                                            <div class="custom-control custom-checkbox mb-3">
                                                                <input class="custom-control-input" type="checkbox" id="in_roadmap" name="in_roadmap" value="1" {{ old('in_roadmap', $post->roadmapModulePost ? 1 : 0) ? 'checked' : '' }}>
                                                                <label for="in_roadmap" class="custom-control-label" style="font-size: 0.9rem;">Include in a Roadmap</label>
                                                            </div>
                                                            <div id="roadmap_fields" style="display: {{ old('in_roadmap', $post->roadmapModulePost ? 1 : 0) ? 'block' : 'none' }};">
                                                                <div class="form-group">
                                                                    <label for="roadmap_id" style="font-size: 0.85rem;">Select Roadmap</label>
                                                                    <select name="roadmap_id" id="roadmap_id" class="form-control" style="width: 100%;">
                                                                        <option value="">-- Choose Roadmap --</option>
                                                                        @foreach ($roadmaps as $roadmap)
                                                                            <option value="{{ $roadmap->id }}" {{ old('roadmap_id', $post->roadmapModulePost ? $post->roadmapModulePost->module->roadmap_id : '') == $roadmap->id ? 'selected' : '' }}>{{ $roadmap->title }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="roadmap_module_id" style="font-size: 0.85rem;">Select Module</label>
                                                                    <select name="roadmap_module_id" id="roadmap_module_id" class="form-control" style="width: 100%;">
                                                                        <option value="">-- Choose Module --</option>
                                                                        @if($post->roadmapModulePost)
                                                                            @foreach($post->roadmapModulePost->module->roadmap->modules as $mod)
                                                                                <option value="{{ $mod->id }}" {{ $post->roadmapModulePost->roadmap_module_id == $mod->id ? 'selected' : '' }}>{{ $mod->title }}</option>
                                                                            @endforeach
                                                                        @endif
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-footer bg-light border-top d-flex justify-content-between p-3">
                                                    <a href="{{ route('dashboard.posts.index') }}" class="btn btn-default font-weight-bold"><i class="fas fa-arrow-left mr-1"></i> Back</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Chapter Form Modal -->
<div class="modal fade" id="chapter-modal" tabindex="-1" role="dialog" aria-labelledby="chapter-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius: 8px; overflow: hidden;">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="chapter-modal-title">Tambah Halaman</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="chapter-form">
                <div class="modal-body">
                    <input type="hidden" id="chapter-id">
                    <div class="form-group">
                        <label for="chapter-title">Judul Halaman (Opsional)</label>
                        <input type="text" class="form-control" id="chapter-title" name="title" placeholder="Masukkan judul halaman (opsional)">
                    </div>
                    <div class="form-group">
                        <label for="chapter-content">Konten Halaman</label>
                        <textarea class="form-control" id="chapter-content" name="content" placeholder="Tulis konten halaman..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btn-save-chapter">Simpan Halaman</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section("style")
<link rel="stylesheet" href="{{ asset("assets/dashboard/plugins/select2/css/select2.min.css") }}"/>
<link rel="stylesheet" href="{{ asset("assets/dashboard/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css") }}"/>
<link rel="stylesheet" href="{{ asset("assets/dashboard/css/editor-upgrade.css") }}?v=2.0"/>
@endsection

@section("script")
<script src="{{ asset("assets/dashboard/plugins/sweetalert2/sweetalert2.all.js") }}"></script>
<script src="{{ asset("assets/dashboard/plugins/select2/js/select2.full.min.js") }}"></script>
<script src="{{ asset("assets/dashboard/plugins/speakingurl/speakingurl.min.js") }}"></script>
<script src="{{ asset("assets/dashboard/plugins/slugify/slugify.min.js") }}"></script>
<script>
    $(document).ready(function() {
        $('#title').on("input", () => {
            $('#slug').val($.slugify($('#title').val()));
        });
        $('#category').select2({
            theme: 'bootstrap4'
        });

        $('#tags').select2({
            tags: true,
        });
        @if ($post->tags_count > 0)
        var tags = [];
        @foreach ($post->tags as $tag)
        tags.push('{{ $tag->name }}');
        @endforeach
        $('#tags').val(tags).trigger('change');
        @endif
        // Custom Summernote Buttons
        var MediaPickerButton = function (context) {
            var ui = $.summernote.ui;
            var button = ui.button({
                contents: '<i class="fas fa-folder-open mr-1"></i> Media Library',
                tooltip: 'Insert image from Media Manager',
                click: function () {
                    window.open('/dashboard/media?mode=picker', 'Media Library', 'width=1000,height=650,status=no,toolbar=no,menubar=no,location=no');
                }
            });
            return button.render();
        };

        /**
         * Smart paste handler: preserves headings, lists, tables, code from Word/GPT/Claude.
         * Strips ugly Word/Google Docs inline styles while keeping semantic structure.
         */
        function smartPasteHandler(e) {
            e.preventDefault();
            var clipboardData = e.originalEvent.clipboardData || window.clipboardData;
            var html = clipboardData.getData('text/html');
            var text = clipboardData.getData('text/plain');

            if (html && html.trim().length > 0) {
                // Create a temporary DOM to clean the HTML
                var tmp = document.createElement('div');
                tmp.innerHTML = html;

                // Remove completely unwanted tags (keep content)
                var badTags = ['script', 'style', 'meta', 'link', 'head', 'o\\:p', 'xml', 'w\\:sdt', 'w\\:sdtContent'];
                badTags.forEach(function(tag) {
                    tmp.querySelectorAll(tag).forEach(function(el) { el.remove(); });
                });

                // Convert Word/Google heading paragraphs to proper H tags
                tmp.querySelectorAll('p').forEach(function(p) {
                    var cls = (p.className || '').toLowerCase();
                    var style = (p.getAttribute('style') || '').toLowerCase();
                    if (cls.includes('heading1') || style.includes('font-size:2') || style.includes('font-size: 2')) {
                        var h = document.createElement('h2'); h.innerHTML = p.innerHTML; p.replaceWith(h);
                    } else if (cls.includes('heading2') || cls.includes('heading 2')) {
                        var h = document.createElement('h3'); h.innerHTML = p.innerHTML; p.replaceWith(h);
                    } else if (cls.includes('heading3') || cls.includes('heading 3')) {
                        var h = document.createElement('h4'); h.innerHTML = p.innerHTML; p.replaceWith(h);
                    }
                });

                // Preserve inline code <code> inside paragraphs
                // Keep: h1-h6, p, ul, ol, li, table, thead, tbody, tr, td, th, blockquote, pre, code, strong, em, b, i, u, s, a, br, img, hr
                var allowedTags = new Set(['H1','H2','H3','H4','H5','H6','P','UL','OL','LI','TABLE','THEAD','TBODY','TR','TD','TH','BLOCKQUOTE','PRE','CODE','STRONG','EM','B','I','U','S','A','BR','IMG','HR','DIV','SPAN']);

                // Strip all inline styles, class, id EXCEPT on specific elements
                tmp.querySelectorAll('*').forEach(function(el) {
                    if (!allowedTags.has(el.tagName)) {
                        // Replace tag with its children
                        var parent = el.parentNode;
                        while (el.firstChild) parent.insertBefore(el.firstChild, el);
                        parent.removeChild(el);
                        return;
                    }
                    // Keep href for links, src for images - strip everything else
                    var keep = {};
                    if (el.tagName === 'A' && el.getAttribute('href')) keep.href = el.getAttribute('href');
                    if (el.tagName === 'IMG' && el.getAttribute('src')) keep.src = el.getAttribute('src');
                    if (el.tagName === 'CODE') {
                        // Keep language class if present
                        var langClass = Array.from(el.classList).find(function(c) { return c.startsWith('language-'); });
                        if (langClass) keep.class = langClass;
                    }
                    // Remove all attributes then restore kept ones
                    while (el.attributes.length > 0) el.removeAttribute(el.attributes[0].name);
                    Object.keys(keep).forEach(function(attr) { el.setAttribute(attr, keep[attr]); });
                });

                // Get cleaned html
                var cleaned = tmp.innerHTML
                    .replace(/<p>\s*<\/p>/gi, '') // Remove empty paragraphs
                    .replace(/&nbsp;/gi, ' ')       // Replace non-breaking spaces
                    .trim();

                if (cleaned.length > 0) {
                    $(this).summernote('pasteHTML', cleaned);
                    return;
                }
            }

            // Fallback: plain text, convert newlines to <br> and paragraphs
            if (text && text.trim().length > 0) {
                var lines = text.split('\n');
                var pasteHtml = lines.map(function(line) {
                    return line.trim() ? '<p>' + line.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;') + '</p>' : '';
                }).filter(Boolean).join('');
                $(this).summernote('pasteHTML', pasteHtml);
            }
        }

        var CodeBlockButton = function (context) {
            var ui = $.summernote.ui;
            var button = ui.buttonGroup([
                ui.button({
                    className: 'dropdown-toggle',
                    contents: '<i class="fas fa-code mr-1"></i> Code Block <span class="caret"></span>',
                    tooltip: 'Insert code block',
                    data: { toggle: 'dropdown' }
                }),
                ui.dropdown({
                    className: 'dropdown-menu-right',
                    items: [
                        'Bash', 'Python', 'PHP', 'JavaScript', 'TypeScript', 'HTML', 'CSS', 'JSON', 'YAML', 'SQL', 'Dockerfile', 'Nginx', 'Apache', 'C', 'C++', 'Java', 'Go', 'Rust', 'Markdown'
                    ],
                    click: function (event) {
                        var lang = $(event.target).text();
                        var cleanLang = lang.toLowerCase().replace('++', 'cpp');
                        context.invoke('editor.insertNode', $(
                            '<pre><code class="language-' + cleanLang + '">// write ' + lang + ' code here\n</code></pre>'
                        )[0]);
                    }
                })
            ]);
            return button.render();
        };

        var CalloutButton = function (context) {
            var ui = $.summernote.ui;
            var button = ui.buttonGroup([
                ui.button({
                    className: 'dropdown-toggle',
                    contents: '<i class="fas fa-info-circle mr-1"></i> Info Box <span class="caret"></span>',
                    tooltip: 'Insert callout/alert box',
                    data: { toggle: 'dropdown' }
                }),
                ui.dropdown({
                    className: 'dropdown-menu-right',
                    items: [
                        'Info (Blue)', 'Warning (Yellow)', 'Success (Green)', 'Danger (Red)'
                    ],
                    click: function (event) {
                        var text = $(event.target).text();
                        var type = 'info';
                        var label = 'Info';
                        if (text.includes('Warning')) { type = 'warning'; label = 'Warning'; }
                        else if (text.includes('Success')) { type = 'success'; label = 'Success'; }
                        else if (text.includes('Danger')) { type = 'danger'; label = 'Danger'; }
                        
                        context.invoke('editor.insertNode', $(
                            '<div class="callout callout-' + type + '"><div class="callout-title"><strong>' + label + '</strong></div><p>Alert message here...</p></div>'
                        )[0]);
                    }
                })
            ]);
            return button.render();
        };

        var SpoilerButton = function (context) {
            var ui = $.summernote.ui;
            var button = ui.button({
                contents: '<i class="fas fa-eye-slash mr-1"></i> Spoiler',
                tooltip: 'Insert collapsible details/spoiler block',
                click: function () {
                    context.invoke('editor.insertNode', $(
                        '<details class="editor-details"><summary>Show Solution</summary><p>Spoiler content here...</p></details>'
                    )[0]);
                }
            });
            return button.render();
        };

        var FocusModeButton = function (context) {
            var ui = $.summernote.ui;
            var button = ui.button({
                contents: '<i class="fas fa-expand-arrows-alt mr-1"></i> Focus Mode',
                tooltip: 'Toggle distraction-free focus writing mode',
                click: function () {
                    window.toggleFocusMode();
                }
            });
            return button.render();
        };

        $("#content").summernote({
            placeholder: 'Write content...',
            height: 500,
            toolbar: [
                ['style', ['style']],
                ['fontname', ['fontname']],
                ['font', ['bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript', 'clear']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph', 'height']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video', 'hr']],
                ['custom', ['mediapicker', 'codeblock', 'callout', 'spoiler', 'focusmode']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            fontNames: ['Inter', 'Arial', 'Arial Black', 'Comic Sans MS', 'Courier New', 'Georgia', 'Helvetica', 'Impact', 'Lucida Grande', 'Tahoma', 'Times New Roman', 'Verdana'],
            fontNamesIgnoreCheck: ['Inter'],
            buttons: {
                mediapicker: MediaPickerButton,
                codeblock: CodeBlockButton,
                callout: CalloutButton,
                spoiler: SpoilerButton,
                focusmode: FocusModeButton
            },
            callbacks: {
                onImageUpload: function(files) {
                    window.uploadEditorImages(files, this);
                },
                onImagePaste: function(files) {
                    window.uploadEditorImages(files, this);
                },
                onPaste: smartPasteHandler
            }
        });
        function readURL(input) {
            if (input.files && input.files[0] && input.files[0].type.includes("image")) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $('#thumbnailpreview').removeClass("d-none");
                    $('#thumbnailpreview').attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                $("#thumbnail").val('');
                $('#thumbnailpreview').addClass("d-none");
                Swal.fire({
                    icon: "error",
                    text: "Select a valid image!"
                });
            }
        }
        $("#thumbnail").change(function(){
            readURL(this);
        });

        // Roadmap Integration dropdown helper
        let roadmapsData = {!! json_encode($roadmaps) !!};
        
        $('#in_roadmap').change(function() {
            if ($(this).is(':checked')) {
                $('#roadmap_fields').slideDown(200);
            } else {
                $('#roadmap_fields').slideUp(200);
            }
        });

        $('#roadmap_id').change(function() {
            let roadmapId = $(this).val();
            let moduleSelect = $('#roadmap_module_id');
            moduleSelect.empty().append('<option value="">-- Choose Module --</option>');
            
            if (roadmapId) {
                let roadmap = roadmapsData.find(r => r.id == roadmapId);
                if (roadmap && roadmap.modules) {
                    roadmap.modules.forEach(function(module) {
                        moduleSelect.append(`<option value="${module.id}">${module.title}</option>`);
                    });
                }
            }
        });

        // Document import AJAX handler for Posts
        $('#import-document-btn').on('click', function(e) {
            e.stopPropagation(); // prevent closing dropdown
            const fileInput = document.getElementById('import-document-input');
            if (fileInput.files.length === 0) {
                alert('Please select a file to import first.');
                return;
            }

            const formData = new FormData();
            formData.append('file', fileInput.files[0]);
            formData.append('_token', '{{ csrf_token() }}');

            // Show spinner
            $('#import-btn-text').addClass('d-none');
            $('#import-btn-spinner').removeClass('d-none');
            $('#import-document-btn').prop('disabled', true);

            $.ajax({
                url: "{{ route('dashboard.posts.import-content') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.html) {
                        // Inject parsed HTML content to Summernote instance
                        $('#content').summernote('code', response.html);
                        // Trigger sweetalert success toast
                        Swal.fire({
                            icon: 'success',
                            title: 'Content imported successfully!',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                        // Close dropdown manually
                        $('.dropdown-menu').removeClass('show');
                    } else if (response.error) {
                        alert(response.error);
                    }
                },
                error: function(xhr) {
                    const err = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Import failed.';
                    alert(err);
                },
                complete: function() {
                    // Hide spinner
                    $('#import-btn-text').removeClass('d-none');
                    $('#import-btn-spinner').addClass('d-none');
                    $('#import-document-btn').prop('disabled', false);
                }
            });
        });

        // --- Multi Page Chapters AJAX Logic ---
        $('#chapter-content').summernote({
            placeholder: 'Write chapter content...',
            height: 350,
            toolbar: [
                ['style', ['style']],
                ['fontname', ['fontname']],
                ['font', ['bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript', 'clear']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph', 'height']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video', 'hr']],
                ['custom', ['mediapicker', 'codeblock', 'callout', 'spoiler']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            fontNames: ['Inter', 'Arial', 'Arial Black', 'Comic Sans MS', 'Courier New', 'Georgia', 'Helvetica', 'Impact', 'Lucida Grande', 'Tahoma', 'Times New Roman', 'Verdana'],
            fontNamesIgnoreCheck: ['Inter'],
            buttons: {
                mediapicker: MediaPickerButton,
                codeblock: CodeBlockButton,
                callout: CalloutButton,
                spoiler: SpoilerButton
            },
            callbacks: {
                onImageUpload: function(files) {
                    window.uploadEditorImages(files, this);
                },
                onImagePaste: function(files) {
                    window.uploadEditorImages(files, this);
                },
                onPaste: smartPasteHandler
            }
        });

        $("#chapters-sortable-list").sortable({
            handle: '.drag-handle',
            update: function(event, ui) {
                let ids = [];
                $("#chapters-sortable-list > li").each(function() {
                    ids.push($(this).data('id'));
                });
                
                $('#chapter-reorder-alert').slideDown(150);
                
                $.ajax({
                    url: "{{ route('dashboard.posts.chapters.reorder', $post->id) }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        ids: ids
                    },
                    success: function(response) {
                        if (response.success) {
                            // Update page numbers in badges
                            $("#chapters-sortable-list > li").each(function(index) {
                                $(this).find('.badge').text('Halaman ' + (index + 2));
                            });
                            Swal.fire({
                                icon: 'success',
                                title: 'Urutan halaman berhasil diperbarui!',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 2000
                            });
                        }
                    },
                    error: function() {
                        alert('Failed to reorder chapters.');
                    },
                    complete: function() {
                        $('#chapter-reorder-alert').slideUp(150);
                    }
                });
            }
        });

        $('#btn-add-chapter').on('click', function() {
            $('#chapter-id').val('');
            $('#chapter-title').val('');
            $('#chapter-content').summernote('code', '');
            $('#chapter-modal-title').text('Tambah Halaman');
            $('#chapter-modal').modal('show');
        });

        $(document).on('click', '.btn-edit-chapter', function() {
            let id = $(this).data('id');
            let title = $(this).data('title');
            let content = $(this).closest('li').find('.chapter-content-hidden').html();
            
            $('#chapter-id').val(id);
            $('#chapter-title').val(title);
            $('#chapter-content').summernote('code', content);
            $('#chapter-modal-title').text('Edit Halaman');
            $('#chapter-modal').modal('show');
        });

        $('#chapter-form').on('submit', function(e) {
            e.preventDefault();
            let id = $('#chapter-id').val();
            let title = $('#chapter-title').val();
            let content = $('#chapter-content').summernote('code');
            
            let url = id 
                ? "{{ route('dashboard.posts.chapters.update', [$post->id, ':id']) }}".replace(':id', id)
                : "{{ route('dashboard.posts.chapters.store', $post->id) }}";
            let type = id ? 'PUT' : 'POST';
            
            $('#btn-save-chapter').prop('disabled', true).text('Menyimpan...');
            
            $.ajax({
                url: url,
                type: type,
                data: {
                    _token: "{{ csrf_token() }}",
                    title: title,
                    content: content
                },
                success: function(response) {
                    if (response.success) {
                        $('#chapter-modal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: id ? 'Halaman diperbarui!' : 'Halaman ditambahkan!',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000
                        });
                        location.reload();
                    }
                },
                error: function(xhr) {
                    alert('Error: ' + (xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal menyimpan halaman'));
                },
                complete: function() {
                    $('#btn-save-chapter').prop('disabled', false).text('Simpan Halaman');
                }
            });
        });

        $(document).on('click', '.btn-delete-chapter', function() {
            let id = $(this).data('id');
            
            Swal.fire({
                title: 'Hapus Halaman?',
                text: "Apakah Anda yakin ingin menghapus halaman tambahan ini? Tindakan ini tidak dapat dibatalkan.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    let url = "{{ route('dashboard.posts.chapters.destroy', [$post->id, ':id']) }}".replace(':id', id);
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Halaman berhasil dihapus!',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 2000
                                });
                                location.reload();
                            }
                        },
                        error: function() {
                            alert('Gagal menghapus halaman.');
                        }
                    });
                }
            });
        });

        // --- Persist Active Tab State ---
        $('a[data-toggle="tab"], a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
            localStorage.setItem('activePostEditTab', $(e.target).attr('href'));
        });
        
        let activeTab = localStorage.getItem('activePostEditTab');
        if (activeTab) {
            $('.nav-tabs a[href="' + activeTab + '"]').tab('show');
        }
    });
</script>
<script src="{{ asset("assets/dashboard/js/editor-upgrade.js") }}?v=2.0"></script>
@endsection
