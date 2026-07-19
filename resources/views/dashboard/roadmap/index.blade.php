@extends('dashboard.master')
@section('title', 'All Roadmaps - ' . config('app.sitesettings')::first()->site_title)

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">All Roadmaps</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard.home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">All Roadmaps</li>
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
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h3 class="card-title">All Roadmaps</h3>
                            <a href="{{ route('dashboard.roadmaps.create') }}" class="btn btn-primary btn-sm ml-auto">
                                <i class="fas fa-plus"></i> Add Roadmap
                            </a>
                        </div>
                        <div class="card-body">
                            @if (session('success'))
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h5><i class="icon fas fa-check"></i> Success!</h5>
                                <p class="m-0">{{ session('success') }}</p>
                            </div>
                            @endif
                            
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width: 50px;">#</th>
                                            <th class="text-center">Cover</th>
                                            <th class="text-center">Roadmap Name</th>
                                            <th class="text-center">Difficulty</th>
                                            <th class="text-center">Stats</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center" style="width: 320px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($roadmaps as $roadmap)
                                        <tr>
                                            <td class="text-center align-middle">{{ $loop->index + $roadmaps->firstItem() }}</td>
                                            <td class="text-center align-middle">
                                                @if($roadmap->cover)
                                                    <img width="80px" height="45px" style="object-fit: cover; border-radius: 4px;" src="{{ asset('uploads/roadmap/' . $roadmap->cover) }}" alt="{{ $roadmap->title }}"/>
                                                @else
                                                    <span class="badge bg-secondary">No Cover</span>
                                                @endif
                                            </td>
                                            <td class="align-middle">
                                                <div class="font-weight-bold">
                                                    @if($roadmap->icon)
                                                        <i class="{{ $roadmap->icon }} mr-1 text-primary"></i>
                                                    @endif
                                                    {{ $roadmap->title }}
                                                </div>
                                                <small class="text-muted">{{ $roadmap->slug }}</small>
                                            </td>
                                            <td class="text-center align-middle">
                                                @if($roadmap->difficulty == 'Beginner')
                                                    <span class="badge bg-success">Beginner</span>
                                                @elseif($roadmap->difficulty == 'Intermediate')
                                                    <span class="badge bg-warning">Intermediate</span>
                                                @else
                                                    <span class="badge bg-danger">Advanced</span>
                                                @endif
                                            </td>
                                            <td class="text-center align-middle">
                                                <div class="small">
                                                    <i class="fas fa-layer-group text-muted mr-1"></i> {{ $roadmap->modulesCount() }} Modules
                                                </div>
                                                <div class="small">
                                                    <i class="fas fa-file-alt text-muted mr-1"></i> {{ $roadmap->articlesCount() }} Articles
                                                </div>
                                                <div class="small text-muted">
                                                    <i class="fas fa-clock mr-1"></i> {{ $roadmap->estimatedMinutes() }}m
                                                </div>
                                            </td>
                                            <td class="text-center align-middle">
                                                @if($roadmap->status == 'Published')
                                                    <span class="badge badge-success">Published</span>
                                                @elseif($roadmap->status == 'Draft')
                                                    <span class="badge badge-warning">Draft</span>
                                                @else
                                                    <span class="badge badge-secondary">Archived</span>
                                                @endif
                                            </td>
                                            <td class="text-center align-middle">
                                                <div class="d-flex justify-content-center" style="gap: 6px;">
                                                    <a href="{{ route('dashboard.roadmaps.builder', $roadmap->id) }}" class="btn btn-success btn-sm">
                                                        <i class="fas fa-cubes"></i> Builder
                                                    </a>
                                                    <a href="{{ route('dashboard.roadmaps.edit', $roadmap->id) }}" class="btn btn-warning btn-sm">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <form action="{{ route('dashboard.roadmaps.destroy', $roadmap->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this roadmap?');">
                                                        @method('DELETE')
                                                        @csrf
                                                        <button type="submit" class="btn btn-danger btn-sm">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <div class="text-muted mb-2">Belum ada roadmap.</div>
                                                <a href="{{ route('dashboard.roadmaps.create') }}" class="btn btn-primary btn-sm">
                                                    Mulai buat roadmap pertama
                                                </a>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer clearfix">
                            <div class="float-right">
                                {{ $roadmaps->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
