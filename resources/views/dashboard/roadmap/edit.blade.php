@extends('dashboard.master')
@section('title', 'Edit Roadmap - ' . config('app.sitesettings')::first()->site_title)

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Edit Roadmap</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('dashboard.roadmaps.index') }}">Roadmaps</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <form action="{{ route('dashboard.roadmaps.update', $roadmap->id) }}" method="POST" enctype="multipart/form-data">
                        @method('PUT')
                        @csrf
                        <div class="card card-warning">
                            <div class="card-header">
                                <h3 class="card-title">Edit Roadmap Information</h3>
                            </div>
                            <div class="card-body">
                                @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                    <h5><i class="icon fas fa-ban"></i> Validation Error!</h5>
                                    <ul class="m-0 pl-3">
                                        @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endif

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="title">Title <span class="text-danger">*</span></label>
                                            <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $roadmap->title) }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="slug">Slug <span class="text-danger">*</span></label>
                                            <input type="text" name="slug" id="slug" class="form-control" value="{{ old('slug', $roadmap->slug) }}" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="difficulty">Difficulty <span class="text-danger">*</span></label>
                                            <select name="difficulty" id="difficulty" class="form-control" required>
                                                <option value="Beginner" {{ old('difficulty', $roadmap->difficulty) == 'Beginner' ? 'selected' : '' }}>Beginner</option>
                                                <option value="Intermediate" {{ old('difficulty', $roadmap->difficulty) == 'Intermediate' ? 'selected' : '' }}>Intermediate</option>
                                                <option value="Advanced" {{ old('difficulty', $roadmap->difficulty) == 'Advanced' ? 'selected' : '' }}>Advanced</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="category_id">Category</label>
                                            <select name="category_id" id="category_id" class="form-control">
                                                <option value="">-- No Category --</option>
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category->id }}" {{ old('category_id', $roadmap->category_id) == $category->id ? 'selected' : '' }}>{{ $category->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="status">Status <span class="text-danger">*</span></label>
                                            <select name="status" id="status" class="form-control" required>
                                                <option value="Draft" {{ old('status', $roadmap->status) == 'Draft' ? 'selected' : '' }}>Draft</option>
                                                <option value="Published" {{ old('status', $roadmap->status) == 'Published' ? 'selected' : '' }}>Published</option>
                                                <option value="Archived" {{ old('status', $roadmap->status) == 'Archived' ? 'selected' : '' }}>Archived</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="icon">Icon Class</label>
                                            <x-dashboard.icon-picker name="icon" value="{{ $roadmap->icon }}" id="icon" />
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="cover">Cover Image</label>
                                            <div class="custom-file">
                                                <input type="file" name="cover" id="cover" class="custom-file-input" accept="image/*">
                                                <label class="custom-file-label" for="cover">Choose file</label>
                                            </div>
                                            @if($roadmap->cover)
                                                <div class="mt-2">
                                                    <img src="{{ asset('uploads/roadmap/' . $roadmap->cover) }}" alt="Cover" class="img-thumbnail" style="max-height: 100px;">
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="sort_order">Sort Order</label>
                                            <input type="number" name="sort_order" id="sort_order" class="form-control" value="{{ old('sort_order', $roadmap->sort_order) }}" required min="0">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea name="description" id="description" class="form-control" rows="4">{{ old('description', $roadmap->description) }}</textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="prerequisites">Prerequisites</label>
                                            <textarea name="prerequisites" id="prerequisites" class="form-control" rows="4">{{ old('prerequisites', $roadmap->prerequisites) }}</textarea>
                                            <small class="text-muted">Enter list items (one per line)</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="learning_outcomes">Learning Outcomes</label>
                                            <textarea name="learning_outcomes" id="learning_outcomes" class="form-control" rows="4">{{ old('learning_outcomes', $roadmap->learning_outcomes) }}</textarea>
                                            <small class="text-muted">Enter list items (one per line)</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-warning">Update Roadmap</button>
                                <a href="{{ route('dashboard.roadmaps.index') }}" class="btn btn-default">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        // Auto-slug generator
        $('#title').on('input', function() {
            let title = $(this).val();
            let slug = title.toLowerCase()
                .replace(/[^a-z0-9 -]/g, '') // remove invalid chars
                .replace(/\s+/g, '-')        // collapse whitespace and replace by -
                .replace(/-+/g, '-');        // collapse dashes
            $('#slug').val(slug);
        });

        // Display filename in file input box
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
        });
    });
</script>
@endsection
