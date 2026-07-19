@extends("dashboard.master")
@section("title", "Content & Search Analytics")

@section("style")
<link rel="stylesheet" href="{{ asset('assets/dashboard/css/analytics/analytics.css') }}"/>
@endsection

@section("content")
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="analytics-header">
                <div class="analytics-title">
                    <h1>Workspace Analytics</h1>
                    <p>Track visitor traffic, search activities, and reading engagement.</p>
                </div>
                <div>
                    <form method="GET" action="{{ route('dashboard.analytics.content') }}" id="period-form">
                        <select name="period" class="period-select" onchange="document.getElementById('period-form').submit()">
                            <option value="today" {{ $period === 'today' ? 'selected' : '' }}>Today</option>
                            <option value="7days" {{ $period === '7days' ? 'selected' : '' }}>Last 7 Days</option>
                            <option value="30days" {{ $period === '30days' ? 'selected' : '' }}>Last 30 Days</option>
                            <option value="90days" {{ $period === '90days' ? 'selected' : '' }}>Last 90 Days</option>
                            <option value="12months" {{ $period === '12months' ? 'selected' : '' }}>Last 12 Months</option>
                        </select>
                    </form>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="analytics-tabs">
                <a href="{{ route('dashboard.analytics.overview', ['period' => $period]) }}" class="analytics-tab-link">Overview</a>
                <a href="{{ route('dashboard.analytics.audience', ['period' => $period]) }}" class="analytics-tab-link">Audience</a>
                <a href="{{ route('dashboard.analytics.content', ['period' => $period]) }}" class="analytics-tab-link active">Content & Search</a>
                <a href="{{ route('dashboard.analytics.manage') }}" class="analytics-tab-link">Manage Data</a>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            @php
                // Pre-fetch related posts and roadmaps in batch to avoid N+1 query loops
                $postIds = $topArticles->pluck('viewable_id')->filter();
                $posts = \App\Models\Post::whereIn('id', $postIds)->get()->keyBy('id');

                $roadmapIds = $topRoadmaps->pluck('viewable_id')->filter();
                $roadmaps = \App\Models\Roadmap::whereIn('id', $roadmapIds)->get()->keyBy('id');
            @endphp

            <div class="content-grid">
                <!-- Top Articles -->
                <div class="table-card">
                    <h2 class="table-card-title">Top Articles</h2>
                    <table class="analytics-table">
                        <thead>
                            <tr>
                                <th class="rank-col">#</th>
                                <th>Article Title</th>
                                <th class="stat-col">Views</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topArticles as $index => $item)
                                @php
                                    $post = $posts->get($item->viewable_id);
                                @endphp
                                <tr>
                                    <td class="rank-col">{{ $index + 1 }}</td>
                                    <td>
                                        @if($post)
                                            <a href="{{ route('frontend.post', $post->slug) }}" target="_blank" class="text-truncate-custom" title="{{ $post->title }}">
                                                {{ $post->title }}
                                            </a>
                                        @else
                                            <span class="text-muted">Deleted Post (ID: {{ $item->viewable_id }})</span>
                                        @endif
                                    </td>
                                    <td class="stat-col">{{ number_format($item->views) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">No content view data recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Top Roadmaps -->
                <div class="table-card">
                    <h2 class="table-card-title">Top Roadmaps</h2>
                    <table class="analytics-table">
                        <thead>
                            <tr>
                                <th class="rank-col">#</th>
                                <th>Roadmap Title</th>
                                <th class="stat-col">Views</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topRoadmaps as $index => $item)
                                @php
                                    $roadmap = $roadmaps->get($item->viewable_id);
                                @endphp
                                <tr>
                                    <td class="rank-col">{{ $index + 1 }}</td>
                                    <td>
                                        @if($roadmap)
                                            <a href="{{ route('frontend.roadmap.show', $roadmap->slug) }}" target="_blank" class="text-truncate-custom" title="{{ $roadmap->title }}">
                                                {{ $roadmap->title }}
                                            </a>
                                        @else
                                            <span class="text-muted">Deleted Roadmap (ID: {{ $item->viewable_id }})</span>
                                        @endif
                                    </td>
                                    <td class="stat-col">{{ number_format($item->views) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">No roadmap view data recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Top Searches -->
                <div class="table-card">
                    <h2 class="table-card-title">Top Searches</h2>
                    <table class="analytics-table">
                        <thead>
                            <tr>
                                <th class="rank-col">#</th>
                                <th>Search Query</th>
                                <th class="stat-col">Volume</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topSearches as $index => $item)
                                <tr>
                                    <td class="rank-col">{{ $index + 1 }}</td>
                                    <td>
                                        <span class="text-truncate-custom" title="{{ $item->query }}">{{ $item->query }}</span>
                                    </td>
                                    <td class="stat-col">{{ number_format($item->count) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">No search query logs found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Zero-Result Searches -->
                <div class="table-card">
                    <h2 class="table-card-title">Zero-Result Searches</h2>
                    <table class="analytics-table">
                        <thead>
                            <tr>
                                <th class="rank-col">#</th>
                                <th>Unmatched Query</th>
                                <th class="stat-col">Volume</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($zeroSearches as $index => $item)
                                <tr>
                                    <td class="rank-col">{{ $index + 1 }}</td>
                                    <td>
                                        <span class="text-danger text-truncate-custom" title="{{ $item->query }}">{{ $item->query }}</span>
                                    </td>
                                    <td class="stat-col">{{ number_format($item->count) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">No empty search queries logged.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
