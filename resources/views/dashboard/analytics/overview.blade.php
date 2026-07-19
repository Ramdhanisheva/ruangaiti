@extends("dashboard.master")
@section("title", "Analytics Overview")

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
                    <form method="GET" action="{{ route('dashboard.analytics.overview') }}" id="period-form">
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
                <a href="{{ route('dashboard.analytics.overview', ['period' => $period]) }}" class="analytics-tab-link active">Overview</a>
                <a href="{{ route('dashboard.analytics.audience', ['period' => $period]) }}" class="analytics-tab-link">Audience</a>
                <a href="{{ route('dashboard.analytics.content', ['period' => $period]) }}" class="analytics-tab-link">Content & Search</a>
                <a href="{{ route('dashboard.analytics.manage') }}" class="analytics-tab-link">Manage Data</a>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- KPI Cards -->
            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-label">Total Views</div>
                    <div class="kpi-value">{{ number_format($stats['views']) }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Unique Visitors</div>
                    <div class="kpi-value">{{ number_format($stats['uniqueVisitors']) }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Avg. Reading Time</div>
                    <div class="kpi-value">
                        @if($stats['avgReadTime'] >= 60)
                            {{ floor($stats['avgReadTime'] / 60) }}m {{ $stats['avgReadTime'] % 60 }}s
                        @else
                            {{ $stats['avgReadTime'] }}s
                        @endif
                    </div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Total Searches</div>
                    <div class="kpi-value">{{ number_format($stats['searches']) }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Helpful Rate</div>
                    <div class="kpi-value">{{ $stats['helpfulRate'] }}%</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Likes</div>
                    <div class="kpi-value">{{ number_format($stats['likes']) }}</div>
                </div>
            </div>

            <!-- Views Chart -->
            <div class="chart-card">
                <div class="chart-card-header">
                    <h2 class="chart-card-title">Visitor Traffic Overview</h2>
                </div>
                <div class="chart-wrapper">
                    <canvas id="viewsChart"></canvas>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section("script")
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('viewsChart').getContext('2d');
        const chartData = @json($chartData);

        const gridColor = document.documentElement.getAttribute('data-theme') === 'dark' ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.05)';
        const textColor = document.documentElement.getAttribute('data-theme') === 'dark' ? '#94a3b8' : '#64748b';

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [
                    {
                        label: 'Total Views',
                        data: chartData.views,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.05)',
                        borderWidth: 2,
                        pointRadius: 3,
                        pointBackgroundColor: '#3b82f6',
                        fill: true,
                        tension: 0.15
                    },
                    {
                        label: 'Unique Visitors',
                        data: chartData.unique,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.05)',
                        borderWidth: 2,
                        pointRadius: 3,
                        pointBackgroundColor: '#10b981',
                        fill: true,
                        tension: 0.15
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'top',
                    labels: {
                        fontColor: textColor,
                        fontFamily: 'Inter, sans-serif',
                        fontWeight: 500
                    }
                },
                scales: {
                    yAxes: [{
                        gridLines: {
                            color: gridColor,
                            zeroLineColor: gridColor
                        },
                        ticks: {
                            fontColor: textColor,
                            fontFamily: 'Inter, sans-serif',
                            beginAtZero: true
                        }
                    }],
                    xAxes: [{
                        gridLines: {
                            color: 'transparent',
                            zeroLineColor: gridColor
                        },
                        ticks: {
                            fontColor: textColor,
                            fontFamily: 'Inter, sans-serif'
                        }
                    }]
                },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                    titleFontFamily: 'Inter, sans-serif',
                    bodyFontFamily: 'Inter, sans-serif',
                    cornerRadius: 8
                }
            }
        });
    });
</script>
@endsection
