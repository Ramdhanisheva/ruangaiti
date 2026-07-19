@extends("dashboard.master")
@section("title", "Analytics Audience")

@section("style")
<link rel="stylesheet" href="{{ asset('assets/dashboard/css/analytics/analytics.css') }}"/>
<style>
    .audience-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
        gap: var(--spacing-md);
        margin-bottom: 2rem;
    }
    .audience-chart-wrapper {
        position: relative;
        width: 100%;
        height: 250px;
    }
</style>
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
                    <form method="GET" action="{{ route('dashboard.analytics.audience') }}" id="period-form">
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
                <a href="{{ route('dashboard.analytics.audience', ['period' => $period]) }}" class="analytics-tab-link active">Audience</a>
                <a href="{{ route('dashboard.analytics.content', ['period' => $period]) }}" class="analytics-tab-link">Content & Search</a>
                <a href="{{ route('dashboard.analytics.manage') }}" class="analytics-tab-link">Manage Data</a>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="audience-grid">
                <!-- Traffic Source Chart -->
                <div class="chart-card">
                    <div class="chart-card-header">
                        <h2 class="chart-card-title">Traffic Channels</h2>
                    </div>
                    <div class="audience-chart-wrapper">
                        <canvas id="referrerChart"></canvas>
                    </div>
                </div>

                <!-- Devices Chart -->
                <div class="chart-card">
                    <div class="chart-card-header">
                        <h2 class="chart-card-title">Visitor Devices</h2>
                    </div>
                    <div class="audience-chart-wrapper">
                        <canvas id="deviceChart"></canvas>
                    </div>
                </div>

                <!-- Browsers Chart -->
                <div class="chart-card">
                    <div class="chart-card-header">
                        <h2 class="chart-card-title">Top Browsers</h2>
                    </div>
                    <div class="audience-chart-wrapper">
                        <canvas id="browserChart"></canvas>
                    </div>
                </div>

                <!-- OS Chart -->
                <div class="chart-card">
                    <div class="chart-card-header">
                        <h2 class="chart-card-title">Operating Systems</h2>
                    </div>
                    <div class="audience-chart-wrapper">
                        <canvas id="osChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section("script")
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const textColor = document.documentElement.getAttribute('data-theme') === 'dark' ? '#94a3b8' : '#64748b';
        const gridColor = document.documentElement.getAttribute('data-theme') === 'dark' ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.05)';

        // 1. Referrer / Traffic Sources Chart
        const refCtx = document.getElementById('referrerChart').getContext('2d');
        const referrers = @json($referrers);
        const refLabels = Object.keys(referrers).map(k => k.charAt(0).toUpperCase() + k.slice(1));
        const refValues = Object.values(referrers);

        new Chart(refCtx, {
            type: 'bar',
            data: {
                labels: refLabels.length ? refLabels : ['Direct'],
                datasets: [{
                    label: 'Sessions',
                    data: refValues.length ? refValues : [0],
                    backgroundColor: '#3b82f6',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                scales: {
                    xAxes: [{ gridLines: { color: 'transparent' }, ticks: { fontColor: textColor } }],
                    yAxes: [{ gridLines: { color: gridColor }, ticks: { fontColor: textColor, beginAtZero: true } }]
                }
            }
        });

        // 2. Devices Chart
        const devCtx = document.getElementById('deviceChart').getContext('2d');
        const devices = @json($devices);
        const devLabels = Object.keys(devices).map(k => k.charAt(0).toUpperCase() + k.slice(1));
        const devValues = Object.values(devices);

        new Chart(devCtx, {
            type: 'doughnut',
            data: {
                labels: devLabels.length ? devLabels : ['Desktop'],
                datasets: [{
                    data: devValues.length ? devValues : [0],
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { position: 'right', labels: { fontColor: textColor } }
            }
        });

        // 3. Browsers Chart
        const brCtx = document.getElementById('browserChart').getContext('2d');
        const browsers = @json($browsers);
        new Chart(brCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(browsers).length ? Object.keys(browsers) : ['Other'],
                datasets: [{
                    data: Object.values(browsers).length ? Object.values(browsers) : [0],
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#374151'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { position: 'right', labels: { fontColor: textColor } }
            }
        });

        // 4. OS Chart
        const osCtx = document.getElementById('osChart').getContext('2d');
        const osData = @json($os);
        new Chart(osCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(osData).length ? Object.keys(osData) : ['Other'],
                datasets: [{
                    data: Object.values(osData).length ? Object.values(osData) : [0],
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#6b7280'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { position: 'right', labels: { fontColor: textColor } }
            }
        });
    });
</script>
@endsection
