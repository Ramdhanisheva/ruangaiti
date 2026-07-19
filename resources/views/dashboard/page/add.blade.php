@extends('dashboard.master')
@section('title', 'Add Page')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Add Page</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route("dashboard.home") }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route("dashboard.pages.index") }}">All Pages</a></li>
                        <li class="breadcrumb-item active">Add Page</li>
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
                            <h3 class="card-title">Add Page</h3>
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
                            <form action="{{ route("dashboard.pages.store") }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-12 mx-auto">
                                        <div class="form-group">
                                            <label for="title">Title</label>
                                            <input type="text" class="form-control" id="title" name="title" placeholder="Enter title" value="{{ old('title') }}"/>
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
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="template">Template</label>
                                                    <select class="form-control" name="template" id="template">
                                                        <option value="default" {{ old('template') == 'default' ? 'selected' : '' }}>Default</option>
                                                        <option value="landing" {{ old('template') == 'landing' ? 'selected' : '' }}>Landing Page</option>
                                                        <option value="company" {{ old('template') == 'company' ? 'selected' : '' }}>Company / About</option>
                                                        <option value="blank" {{ old('template') == 'blank' ? 'selected' : '' }}>Blank Canvas</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="status">Status</label>
                                                    <select class="form-control" name="status" id="status">
                                                        <option value="Published" {{ old('status') == 'Published' ? 'selected' : '' }}>Publish</option>
                                                        <option value="Draft" {{ old('status', 'Draft') == 'Draft' ? 'selected' : '' }}>Draft</option>
                                                        <option value="Archived" {{ old('status') == 'Archived' ? 'selected' : '' }}>Archived</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- SEO Accordion --}}
                                        <div class="card card-secondary card-outline collapsed-card mt-3">
                                            <div class="card-header">
                                                <h3 class="card-title font-weight-bold" style="font-size: .95rem;"><i class="fas fa-search mr-1"></i> SEO & Schema Settings</h3>
                                                <div class="card-tools">
                                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label for="seo_title">SEO Meta Title</label>
                                                    <input type="text" name="seo_title" id="seo_title" class="form-control form-control-sm" value="{{ old('seo_title') }}" placeholder="Defaults to title if blank">
                                                </div>
                                                <div class="form-group">
                                                    <label for="seo_description">SEO Meta Description</label>
                                                    <textarea name="seo_description" id="seo_description" rows="2" class="form-control form-control-sm" placeholder="Summarize page content…">{{ old('seo_description') }}</textarea>
                                                </div>
                                                <div class="form-group mb-0">
                                                    <label for="json_ld">JSON-LD Schema</label>
                                                    <textarea name="json_ld" id="json_ld" rows="3" class="form-control form-control-sm" placeholder='{ "@context": "https://schema.org", ... }'>{{ old('json_ld') }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button class="btn btn-primary" type="submit">Create Page</button>
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
        $("#content").summernote({
            placeholder: 'Write content...',
            height: 200,
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
    });
</script>
@endsection
