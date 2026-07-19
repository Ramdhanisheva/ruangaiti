@extends('dashboard.master')
@section('title', 'New Post')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">New Post</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route("dashboard.home") }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route("dashboard.posts.index") }}">All Posts</a></li>
                        <li class="breadcrumb-item active">New Post</li>
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
                            <h3 class="card-title">New Post</h3>
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
                            <form action="{{ route("dashboard.posts.store") }}" enctype="multipart/form-data" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="card card-primary card-outline" style="border-top: 3px solid #007bff; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-radius: 4px; background: #fff;">
                                            <div class="card-header">
                                                <h3 class="card-title font-weight-bold"><i class="fas fa-edit text-primary mr-1"></i> Post Details</h3>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label for="title">Title</label>
                                                    <input type="text" class="form-control" id="title" name="title" placeholder="Enter title" value="{{ old('title') }}"/>
                                                </div>
                                                <div class="form-group">
                                                    <label for="first_page_title">Judul Halaman Pertama <span class="text-muted">(Opsional, default: Pendahuluan)</span></label>
                                                    <input type="text" class="form-control" id="first_page_title" name="first_page_title" placeholder="Contoh: Pendahuluan, Ringkasan, Deskripsi..." value="{{ old('first_page_title') }}"/>
                                                </div>
                                                <div class="form-group">
                                                    <label for="slug">Slug</label>
                                                    <input type="text" class="form-control" id="slug" name="slug" placeholder="Enter slug" value="{{ old('slug') }}"/>
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
                                                    <textarea class="form-control" id="content" name="content" placeholder="Write content">{{ old('content') }}</textarea>
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
                                                        <i class="fas fa-paper-plane mr-1"></i> Publish
                                                    </button>
                                                </div>
                                                <div class="card-body">
                                                    <div class="form-group">
                                                        <label for="category">Category</label>
                                                        <select class="form-control" name="category" id="category" style="width: 100%;">
                                                            @foreach ($categories as $category)
                                                            <option value="{{ $category->id }}" {{ old("category") ? (old("category") == $category->id ? "selected" : "") : "" }}>{{ $category->title }}</option>
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
                                                        <img id="thumbnailpreview" class="img-fluid img-thumbnail mt-3 d-none"/>
                                                    </div>
                                                    <div class="align-items-center d-flex form-group justify-content-between">
                                                        <label for="featured">Featured</label>
                                                        <div class="icheck-success d-inline">
                                                            <input type="checkbox" name="featured" id="featured" value="1"/>
                                                            <label for="featured"></label>
                                                        </div>
                                                    </div>
                                                    <div class="align-items-center d-flex form-group justify-content-between">
                                                        <label for="comment">Enable Comment</label>
                                                        <div class="icheck-success d-inline">
                                                            <input type="checkbox" name="comment" id="comment" value="1" checked/>
                                                            <label for="comment"></label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="status">Status</label>
                                                        <select class="form-control" name="status" id="status">
                                                            @if (auth()->user()->role != 1)
                                                            <option value="1">Publish</option>
                                                            @endif
                                                            <option value="0">Draft</option>
                                                        </select>
                                                    </div>

                                                    <div class="card card-dark mt-4 mb-0">
                                                        <div class="card-header" style="padding: .5rem 1rem;">
                                                            <h3 class="card-title" style="font-size: 0.9rem;">Roadmap Integration</h3>
                                                        </div>
                                                        <div class="card-body" style="padding: 1rem;">
                                                            <div class="custom-control custom-checkbox mb-3">
                                                                <input class="custom-control-input" type="checkbox" id="in_roadmap" name="in_roadmap" value="1" {{ old('in_roadmap') ? 'checked' : '' }}>
                                                                <label for="in_roadmap" class="custom-control-label" style="font-size: 0.9rem;">Include in a Roadmap</label>
                                                            </div>
                                                            <div id="roadmap_fields" style="display: {{ old('in_roadmap') ? 'block' : 'none' }};">
                                                                <div class="form-group">
                                                                    <label for="roadmap_id" style="font-size: 0.85rem;">Select Roadmap</label>
                                                                    <select name="roadmap_id" id="roadmap_id" class="form-control" style="width: 100%;">
                                                                        <option value="">-- Choose Roadmap --</option>
                                                                        @foreach ($roadmaps as $roadmap)
                                                                            <option value="{{ $roadmap->id }}" {{ old('roadmap_id') == $roadmap->id ? 'selected' : '' }}>{{ $roadmap->title }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="roadmap_module_id" style="font-size: 0.85rem;">Select Module</label>
                                                                    <select name="roadmap_module_id" id="roadmap_module_id" class="form-control" style="width: 100%;">
                                                                        <option value="">-- Choose Module --</option>
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
                }
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
    });
</script>
<script src="{{ asset("assets/dashboard/js/editor-upgrade.js") }}?v=2.0"></script>
@endsection
