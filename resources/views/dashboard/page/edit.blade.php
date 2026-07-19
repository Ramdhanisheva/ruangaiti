@extends('dashboard.master')
@section('title', 'Edit Page — ' . $page->title)

@section('style')
<style>
    .nav-tabs-custom {
        box-shadow: 0 1px 3px rgba(0,0,0,.1);
        border-radius: .25rem;
        background: #fff;
    }
    .nav-tabs-custom > .nav-tabs {
        border-bottom: 1px solid #f4f4f4;
        margin: 0;
        border-top-right-radius: .25rem;
        border-top-left-radius: .25rem;
    }
    .nav-tabs-custom > .nav-tabs > li {
        border-top: 3px solid transparent;
        margin-bottom: -2px;
        margin-right: 5px;
    }
    .nav-tabs-custom > .nav-tabs > li > a {
        color: #444;
        border-radius: 0;
        padding: 10px 15px;
        display: block;
        font-weight: 500;
    }
    .nav-tabs-custom > .nav-tabs > li > a.active {
        border-top: 3px solid #3c8dbc;
        background-color: #fff;
        color: #3c8dbc;
        border-left: 1px solid #f4f4f4;
        border-right: 1px solid #f4f4f4;
        margin-top: -3px;
    }
    .section-card {
        border: 1px solid #e3e6f0;
        border-radius: .35rem;
        box-shadow: 0 .15rem 1.75rem 0 rgba(58,59,120,.05);
        margin-bottom: 1.5rem;
    }
    .section-card-header {
        background-color: #f8f9fc;
        border-bottom: 1px solid #e3e6f0;
        padding: .75rem 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: move;
    }
    .section-item-row {
        background: #fdfdfd;
        border: 1px dashed #e3e6f0;
        padding: 15px;
        margin-bottom: 15px;
        border-radius: 5px;
        position: relative;
    }
    .remove-item-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        color: #e74a3b;
        cursor: pointer;
    }
</style>
@endsection

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Edit Page: {{ $page->title }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route("dashboard.home") }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route("dashboard.pages.index") }}">All Pages</a></li>
                        <li class="breadcrumb-item active">Edit Page</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
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

            <div class="row">
                <div class="col-12">
                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs" id="custom-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="editor-tab-link" data-toggle="tab" href="#editor-tab" role="tab" aria-controls="editor-tab" aria-selected="true"><i class="fas fa-edit mr-1"></i> Page Editor</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="sections-tab-link" data-toggle="tab" href="#sections-tab" role="tab" aria-controls="sections-tab" aria-selected="false"><i class="fas fa-cubes mr-1"></i> Page Sections Builder</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="seo-tab-link" data-toggle="tab" href="#seo-tab" role="tab" aria-controls="seo-tab" aria-selected="false"><i class="fas fa-search mr-1"></i> SEO & Templates</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="revisions-tab-link" data-toggle="tab" href="#revisions-tab" role="tab" aria-controls="revisions-tab" aria-selected="false"><i class="fas fa-history mr-1"></i> Revisions History</a>
                            </li>
                        </ul>

                        <div class="tab-content p-4">
                            <!-- TAB 1: Editor -->
                            <div class="tab-pane fade show active" id="editor-tab" role="tabpanel" aria-labelledby="editor-tab-link">
                                <form action="{{ route("dashboard.pages.update", $page->id) }}" method="POST" id="edit-page-form">
                                    @csrf
                                    @method("PUT")

                                    <div class="form-group">
                                        <label for="title">Title</label>
                                        <input type="text" class="form-control" id="title" name="title" placeholder="Enter title" value="{{ $page->title }}" required/>
                                    </div>
                                    <div class="form-group">
                                        <label for="slug">Slug</label>
                                        <input type="text" class="form-control" id="slug" name="slug" placeholder="Enter slug" value="{{ $page->slug }}" required/>
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
                                        <textarea class="form-control" id="content" name="content" placeholder="Write content">{{ $page->content }}</textarea>
                                    </div>
                                    <button class="btn btn-primary" type="submit"><i class="fas fa-save mr-1"></i> Save Changes</button>
                                </form>
                            </div>

                            <!-- TAB 2: Section Layout Builder -->
                            <div class="tab-pane fade" id="sections-tab" role="tabpanel" aria-labelledby="sections-tab-link">
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <h4 class="font-weight-bold text-dark m-0"><i class="fas fa-th-list mr-1"></i> Layout Sections</h4>
                                    <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#addSectionModal">
                                        <i class="fas fa-plus mr-1"></i> Add Layout Section
                                    </button>
                                </div>

                                <div id="sections-sortable-list">
                                    @forelse($page->sections as $section)
                                    <div class="card section-card" data-id="{{ $section->id }}">
                                        <div class="section-card-header">
                                            <div>
                                                <i class="fas fa-grip-lines mr-2 text-muted drag-handle"></i>
                                                <span class="badge badge-info mr-2">{{ strtoupper($section->type) }}</span>
                                                <span class="text-muted">Layout: {{ ucfirst($section->layout_style) }}</span>
                                            </div>
                                            <div class="d-flex align-items-center" style="gap: .5rem">
                                                <button class="btn btn-xs btn-outline-secondary" data-toggle="collapse" data-target="#section-body-{{ $section->id }}">
                                                    <i class="fas fa-edit mr-1"></i> Edit Items
                                                </button>
                                                <form action="{{ route('dashboard.pages.duplicate-section', $section->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-xs btn-outline-primary" title="Duplicate Section">
                                                        <i class="fas fa-copy"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('dashboard.pages.delete-section', $section->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this layout section?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-outline-danger" title="Delete Section">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="collapse" id="section-body-{{ $section->id }}">
                                            <div class="card-body">
                                                <form action="{{ route('dashboard.pages.save-section-items', $section->id) }}" method="POST">
                                                    @csrf
                                                    <div class="section-items-container" id="items-container-{{ $section->id }}">
                                                        @foreach($section->items as $idx => $item)
                                                        <div class="section-item-row">
                                                            <span class="remove-item-btn" onclick="this.parentElement.remove()"><i class="fas fa-times-circle"></i></span>
                                                            <div class="row">
                                                                <div class="col-md-6 form-group">
                                                                    <label>Title</label>
                                                                    <input type="text" name="items[{{ $idx }}][title]" class="form-control form-control-sm" value="{{ $item->title }}">
                                                                </div>
                                                                <div class="col-md-6 form-group">
                                                                    <label>Subtitle</label>
                                                                    <input type="text" name="items[{{ $idx }}][subtitle]" class="form-control form-control-sm" value="{{ $item->subtitle }}">
                                                                </div>
                                                                <div class="col-md-12 form-group">
                                                                    <label>Content</label>
                                                                    <textarea name="items[{{ $idx }}][content]" class="form-control form-control-sm" rows="2">{{ $item->content }}</textarea>
                                                                </div>
                                                                <div class="col-md-6 form-group">
                                                                    <label>Image URL/Path</label>
                                                                    <input type="text" name="items[{{ $idx }}][image]" class="form-control form-control-sm" value="{{ $item->image }}">
                                                                </div>
                                                                <div class="col-md-6 form-group">
                                                                    <label>Action Link</label>
                                                                    <input type="text" name="items[{{ $idx }}][link]" class="form-control form-control-sm" value="{{ $item->link }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                    <button type="button" class="btn btn-xs btn-outline-info mb-3" onclick="addSectionRow({{ $section->id }})">
                                                        <i class="fas fa-plus mr-1"></i> Add Row Item
                                                    </button>
                                                    <hr>
                                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save mr-1"></i> Save Section Content</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="text-center py-5 text-muted">
                                        <i class="fas fa-cubes fa-2x mb-2"></i>
                                        <p>No layout sections defined yet. Page renders default raw content.</p>
                                    </div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- TAB 3: SEO Settings & Templates -->
                            <div class="tab-pane fade" id="seo-tab" role="tabpanel" aria-labelledby="seo-tab-link">
                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label for="seo-template">Template</label>
                                            <select class="form-control" name="template" id="seo-template" form="edit-page-form">
                                                <option value="default" {{ $page->template == 'default' ? 'selected' : '' }}>Default</option>
                                                <option value="landing" {{ $page->template == 'landing' ? 'selected' : '' }}>Landing Page</option>
                                                <option value="company" {{ $page->template == 'company' ? 'selected' : '' }}>Company / About</option>
                                                <option value="blank" {{ $page->template == 'blank' ? 'selected' : '' }}>Blank Canvas</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label for="seo-status">Status</label>
                                            <select class="form-control" name="status" id="seo-status" form="edit-page-form">
                                                <option value="Published" {{ $page->status == 'Published' ? 'selected' : '' }}>Publish</option>
                                                <option value="Draft" {{ $page->status == 'Draft' ? 'selected' : '' }}>Draft</option>
                                                <option value="Archived" {{ $page->status == 'Archived' ? 'selected' : '' }}>Archived</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="seo_title">SEO Meta Title</label>
                                        <input type="text" name="seo_title" id="seo_title" class="form-control" value="{{ $page->seo_title }}" placeholder="Defaults to page title if empty" form="edit-page-form">
                                    </div>
                                    <div class="form-group">
                                        <label for="seo_description">SEO Meta Description</label>
                                        <textarea name="seo_description" id="seo_description" rows="3" class="form-control" placeholder="Summarize content for search result snippet…" form="edit-page-form">{{ $page->seo_description }}</textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="json_ld">JSON-LD Structured Schema</label>
                                        <textarea name="json_ld" id="json_ld" rows="4" class="form-control" placeholder='{ "@context": "https://schema.org", ... }' form="edit-page-form">{{ $page->json_ld }}</textarea>
                                    </div>

                                    <button class="btn btn-primary" type="submit" form="edit-page-form"><i class="fas fa-save mr-1"></i> Save Changes</button>
                            </div>

                            <!-- TAB 4: Revisions History -->
                            <div class="tab-pane fade" id="revisions-tab" role="tabpanel" aria-labelledby="revisions-tab-link">
                                <h5 class="font-weight-bold mb-3"><i class="fas fa-history mr-1"></i> Revision History (Keep last 5 edits)</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Timestamp</th>
                                                <th>Title Reference</th>
                                                <th>Content Size</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($revisions as $rev)
                                                @php $data = json_decode($rev->content_data, true); @endphp
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::parse($rev->created_at)->diffForHumans() }} ({{ $rev->created_at }})</td>
                                                    <td>{{ $data['title'] ?? '—' }}</td>
                                                    <td>{{ strlen($data['content'] ?? '') }} bytes</td>
                                                    <td>
                                                        <form action="{{ route('dashboard.pages.restore-revision', $page->id) }}" method="POST" onsubmit="return confirm('Restore page content to this revision?')">
                                                            @csrf
                                                            <input type="hidden" name="revision_id" value="{{ $rev->id }}">
                                                            <button type="submit" class="btn btn-xs btn-warning font-weight-bold">
                                                                <i class="fas fa-undo mr-1"></i>Restore
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted">No revisions logged yet.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

{{-- Add Layout Section Modal --}}
<div class="modal fade" id="addSectionModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('dashboard.pages.add-section', $page->id) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-plus mr-1"></i>Add Layout Section</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Section Type</label>
                        <select name="type" class="form-control" required>
                            <option value="hero">Hero / Banner</option>
                            <option value="features">Features / Cards</option>
                            <option value="cta">CTA (Call-To-Action)</option>
                            <option value="faq">FAQ Section</option>
                            <option value="timeline">Timeline Section</option>
                            <option value="accordion">Accordion Section</option>
                            <option value="tabs">Tabs Section</option>
                            <option value="html">Custom HTML Block</option>
                            <option value="css">Custom CSS Block</option>
                            <option value="markdown">Markdown Block</option>
                            <option value="team">Team Members</option>
                            <option value="testimonials">Testimonials</option>
                            <option value="text">Text Block</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Layout Style</label>
                        <select name="layout_style" class="form-control" required>
                            <option value="full-width">Full Width</option>
                            <option value="container">Container</option>
                            <option value="split">Split Layout</option>
                            <option value="minimal">Minimalist Grid</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Add Section</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section("script")
<script src="{{ asset("assets/dashboard/plugins/sweetalert2/sweetalert2.all.js") }}"></script>
<script src="{{ asset("assets/dashboard/plugins/speakingurl/speakingurl.min.js") }}"></script>
<script src="{{ asset("assets/dashboard/plugins/slugify/slugify.min.js") }}"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script>
    $(document).ready(function() {
        $('#title').on("input", () => {
            $('#slug').val($.slugify($('#title').val()));
        });
        $("#content").summernote({
            placeholder: 'Write content...',
            height: 350,
        });

        // Initialize sortable layout sections list
        $("#sections-sortable-list").sortable({
            handle: ".drag-handle",
            update: function(event, ui) {
                const sectionIds = [];
                $("#sections-sortable-list > .section-card").each(function() {
                    sectionIds.push($(this).data("id"));
                });

                $.ajax({
                    url: "{{ route('dashboard.pages.sort-sections') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        ids: sectionIds
                    },
                    success: function(response) {
                        if (response.success) {
                            console.log("Sections reordered successfully");
                        }
                    }
                });
            }
        });

        // Document import AJAX handler
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
                url: "{{ route('dashboard.pages.import-content') }}",
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

        // Tab Persistence & Activation via URL hash
        const hash = window.location.hash;
        if (hash) {
            $('.nav-tabs a[href="' + hash + '"]').tab('show');
        }

        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            window.location.hash = e.target.hash;
        });
    });

    function addSectionRow(sectionId) {
        const container = document.getElementById("items-container-" + sectionId);
        const idx = container.children.length;
        const row = document.createElement("div");
        row.className = "section-item-row";
        row.innerHTML = `
            <span class="remove-item-btn" onclick="this.parentElement.remove()"><i class="fas fa-times-circle"></i></span>
            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Title</label>
                    <input type="text" name="items[${idx}][title]" class="form-control form-control-sm">
                </div>
                <div class="col-md-6 form-group">
                    <label>Subtitle</label>
                    <input type="text" name="items[${idx}][subtitle]" class="form-control form-control-sm">
                </div>
                <div class="col-md-12 form-group">
                    <label>Content</label>
                    <textarea name="items[${idx}][content]" class="form-control form-control-sm" rows="2"></textarea>
                </div>
                <div class="col-md-6 form-group">
                    <label>Image URL/Path</label>
                    <input type="text" name="items[${idx}][image]" class="form-control form-control-sm">
                </div>
                <div class="col-md-6 form-group">
                    <label>Action Link</label>
                    <input type="text" name="items[${idx}][link]" class="form-control form-control-sm">
                </div>
            </div>
        `;
        container.appendChild(row);
    }
</script>
@endsection
