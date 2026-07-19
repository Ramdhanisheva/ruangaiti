<?php

namespace App\Services;

use App\Models\AnalyticsAggregate;
use App\Models\LikesFeedback;
use App\Models\PageView;
use App\Models\SearchLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    // ──────────────────────────────────────────────────────────────────────────
    // TRACKING
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Record a page view.
     * Cooldown: one view per visitor per path per hour (prevents refresh spam).
     */
    public function trackView(array $data): bool
    {
        $cooldownKey = 'pv:' . $data['visitor_id'] . ':' . md5($data['path']);
        if (Cache::has($cooldownKey)) {
            return false;
        }
        Cache::put($cooldownKey, 1, now()->addHour());

        PageView::create([
            'visitor_id'      => $data['visitor_id'],
            'session_id'      => $data['session_id'],
            'path'            => $data['path'],
            'viewable_type'   => $data['viewable_type'] ?? null,
            'viewable_id'     => $data['viewable_id'] ?? null,
            'ip_hash'         => $this->hashIp($data['ip']),
            'device'          => $this->detectDevice($data['user_agent']),
            'browser'         => $this->detectBrowser($data['user_agent']),
            'os'              => $this->detectOs($data['user_agent']),
            'referrer'        => $data['referrer'] ?? null,
            'referrer_source' => $this->classifyReferrer($data['referrer'] ?? ''),
            'created_at'      => now(),
        ]);

        return true;
    }

    /**
     * Increment reading time for an active visitor (heartbeat ping).
     * Adds up to 15 seconds per ping to the latest matching view.
     */
    public function pingReadTime(string $visitorId, string $path, int $seconds = 15): void
    {
        PageView::where('visitor_id', $visitorId)
            ->where('path', $path)
            ->where('created_at', '>=', now()->subHours(4))
            ->orderByDesc('id')
            ->limit(1)
            ->increment('read_time', min($seconds, 30)); // cap at 30s per ping
    }

    /**
     * Record a like or helpful vote.
     * Cooldown: one vote per visitor per content item per day.
     */
    public function trackFeedback(array $data): array
    {
        $ipHash = $this->hashIp($data['ip']);
        $cooldownKey = 'fb:' . $ipHash . ':' . $data['type'] . ':' . $data['likeable_type'] . ':' . $data['likeable_id'];

        // Toggle: check if same vote exists from this IP hash (permanent, no startOfDay limit)
        $existing = LikesFeedback::where('ip_hash', $ipHash)
            ->where('likeable_type', $data['likeable_type'])
            ->where('likeable_id', $data['likeable_id'])
            ->where('type', $data['type'])
            ->first();

        if ($existing) {
            $existing->delete();
            Cache::forget($cooldownKey);
            return ['success' => true, 'action' => 'removed'];
        }

        // Only check cooldown for creating a new vote (prevents spamming new inserts)
        if (Cache::has($cooldownKey)) {
            return ['success' => false, 'message' => 'Already voted'];
        }

        LikesFeedback::create([
            'likeable_type' => $data['likeable_type'],
            'likeable_id'   => $data['likeable_id'],
            'type'          => $data['type'],
            'visitor_id'    => $data['visitor_id'] ?? 'ip-fallback',
            'ip_hash'       => $ipHash,
            'created_at'    => now(),
        ]);

        Cache::put($cooldownKey, 1, now()->addMinutes(10)); // 10 minutes cooldown for same vote creation
        return ['success' => true, 'action' => 'added'];
    }

    /**
     * Log a search query.
     */
    public function trackSearch(array $data): void
    {
        // Only log unique queries from this visitor in the last 5 minutes
        $recent = SearchLog::where('visitor_id', $data['visitor_id'])
            ->where('query', $data['query'])
            ->where('created_at', '>=', now()->subMinutes(5))
            ->exists();

        if (!$recent) {
            SearchLog::create([
                'query'         => mb_substr($data['query'], 0, 300),
                'search_type'   => $data['search_type'] ?? 'global',
                'results_count' => $data['results_count'] ?? 0,
                'page'          => $data['page'] ?? 1,
                'duration_ms'   => $data['duration_ms'] ?? null,
                'visitor_id'    => $data['visitor_id'],
                'ip_hash'       => $this->hashIp($data['ip']),
                'created_at'    => now(),
            ]);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // DASHBOARD REPORTS (reads from aggregates for speed)
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Overview KPI cards – cached for 10 minutes.
     */
    public function getOverviewStats(string $period = '30days'): array
    {
        return Cache::remember('analytics.overview.' . $period, 600, function () use ($period) {
            $from = $this->periodStart($period);

            $views         = PageView::where('created_at', '>=', $from)->count();
            $uniqueVisitors = PageView::where('created_at', '>=', $from)->distinct('visitor_id')->count('visitor_id');
            $avgReadTime   = (int) PageView::where('created_at', '>=', $from)->where('read_time', '>', 0)->avg('read_time');
            $searches      = SearchLog::where('created_at', '>=', $from)->count();
            $likes         = LikesFeedback::where('created_at', '>=', $from)->where('type', 'like')->count();
            $helpfulYes    = LikesFeedback::where('created_at', '>=', $from)->where('type', 'helpful_yes')->count();
            $helpfulNo     = LikesFeedback::where('created_at', '>=', $from)->where('type', 'helpful_no')->count();
            $helpfulTotal  = $helpfulYes + $helpfulNo;
            $helpfulRate   = $helpfulTotal > 0 ? round(($helpfulYes / $helpfulTotal) * 100, 1) : 0;

            return compact('views', 'uniqueVisitors', 'avgReadTime', 'searches', 'likes', 'helpfulRate', 'helpfulYes', 'helpfulNo');
        });
    }

    /**
     * Daily views chart data for the last N days.
     */
    public function getViewsChartData(int $days = 30): array
    {
        return Cache::remember('analytics.chart.views.' . $days, 600, function () use ($days) {
            $rows = PageView::select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as views'),
                    DB::raw('COUNT(DISTINCT visitor_id) as unique_views')
                )
                ->where('created_at', '>=', now()->subDays($days))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $labels = [];
            $views  = [];
            $unique = [];

            foreach ($rows as $row) {
                $labels[] = Carbon::parse($row->date)->format('d M');
                $views[]  = $row->views;
                $unique[] = $row->unique_views;
            }

            return compact('labels', 'views', 'unique');
        });
    }

    /**
     * Device breakdown for audience tab.
     */
    public function getDeviceStats(string $period = '30days'): array
    {
        return Cache::remember('analytics.devices.' . $period, 600, function () use ($period) {
            return PageView::select('device', DB::raw('COUNT(*) as count'))
                ->where('created_at', '>=', $this->periodStart($period))
                ->groupBy('device')
                ->pluck('count', 'device')
                ->toArray();
        });
    }

    /**
     * Browser breakdown for audience tab.
     */
    public function getBrowserStats(string $period = '30days'): array
    {
        return Cache::remember('analytics.browsers.' . $period, 600, function () use ($period) {
            return PageView::select('browser', DB::raw('COUNT(*) as count'))
                ->where('created_at', '>=', $this->periodStart($period))
                ->whereNotNull('browser')
                ->groupBy('browser')
                ->orderByDesc('count')
                ->limit(8)
                ->pluck('count', 'browser')
                ->toArray();
        });
    }

    /**
     * Traffic source / referrer breakdown.
     */
    public function getReferrerStats(string $period = '30days'): array
    {
        return Cache::remember('analytics.referrers.' . $period, 600, function () use ($period) {
            return PageView::select('referrer_source', DB::raw('COUNT(*) as count'))
                ->where('created_at', '>=', $this->periodStart($period))
                ->groupBy('referrer_source')
                ->orderByDesc('count')
                ->pluck('count', 'referrer_source')
                ->toArray();
        });
    }

    /**
     * OS breakdown for audience tab.
     */
    public function getOsStats(string $period = '30days'): array
    {
        return Cache::remember('analytics.os.' . $period, 600, function () use ($period) {
            return PageView::select('os', DB::raw('COUNT(*) as count'))
                ->where('created_at', '>=', $this->periodStart($period))
                ->whereNotNull('os')
                ->groupBy('os')
                ->orderByDesc('count')
                ->limit(8)
                ->pluck('count', 'os')
                ->toArray();
        });
    }

    /**
     * Top content by views.
     */
    public function getTopContent(string $type, int $limit = 10, string $period = '30days'): \Illuminate\Support\Collection
    {
        return Cache::remember("analytics.top.{$type}.{$period}", 600, function () use ($type, $limit, $period) {
            return PageView::select('viewable_id', DB::raw('COUNT(*) as views'))
                ->where('viewable_type', $type)
                ->where('created_at', '>=', $this->periodStart($period))
                ->groupBy('viewable_id')
                ->orderByDesc('views')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Top search keywords.
     */
    public function getTopSearches(int $limit = 20, string $period = '30days'): \Illuminate\Support\Collection
    {
        return Cache::remember('analytics.searches.' . $period, 600, function () use ($limit, $period) {
            return SearchLog::select('query', DB::raw('COUNT(*) as count'))
                ->where('created_at', '>=', $this->periodStart($period))
                ->groupBy('query')
                ->orderByDesc('count')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Zero-result searches.
     */
    public function getZeroResultSearches(int $limit = 20, string $period = '30days'): \Illuminate\Support\Collection
    {
        return Cache::remember('analytics.zero_searches.' . $period, 1200, function () use ($limit, $period) {
            return SearchLog::select('query', DB::raw('COUNT(*) as count'))
                ->where('results_count', 0)
                ->where('created_at', '>=', $this->periodStart($period))
                ->groupBy('query')
                ->orderByDesc('count')
                ->limit($limit)
                ->get();
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ──────────────────────────────────────────────────────────────────────────

    private function hashIp(?string $ip): ?string
    {
        return $ip ? hash('sha256', $ip . config('app.key')) : null;
    }

    private function periodStart(string $period): Carbon
    {
        return match ($period) {
            'today'   => now()->startOfDay(),
            '7days'   => now()->subDays(7),
            '30days'  => now()->subDays(30),
            '90days'  => now()->subDays(90),
            '12months'=> now()->subMonths(12),
            default   => now()->subDays(30),
        };
    }

    private function detectDevice(string $ua): string
    {
        $ua = strtolower($ua);
        if (str_contains($ua, 'mobile') || str_contains($ua, 'android') || str_contains($ua, 'iphone')) {
            return 'mobile';
        }
        if (str_contains($ua, 'tablet') || str_contains($ua, 'ipad')) {
            return 'tablet';
        }
        return 'desktop';
    }

    private function detectBrowser(string $ua): string
    {
        if (str_contains($ua, 'Edg/'))    return 'Edge';
        if (str_contains($ua, 'OPR/'))    return 'Opera';
        if (str_contains($ua, 'Chrome/')) return 'Chrome';
        if (str_contains($ua, 'Firefox/'))return 'Firefox';
        if (str_contains($ua, 'Safari/')) return 'Safari';
        return 'Other';
    }

    private function detectOs(string $ua): string
    {
        if (str_contains($ua, 'Windows'))      return 'Windows';
        if (str_contains($ua, 'Mac OS'))       return 'macOS';
        if (str_contains($ua, 'Linux'))        return 'Linux';
        if (str_contains($ua, 'Android'))      return 'Android';
        if (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')) return 'iOS';
        return 'Other';
    }

    private function classifyReferrer(?string $referrer): string
    {
        if (empty($referrer)) return 'direct';
        $r = strtolower($referrer);
        if (str_contains($r, 'google'))      return 'google';
        if (str_contains($r, 'bing'))        return 'bing';
        if (str_contains($r, 'duckduckgo')) return 'duckduckgo';
        if (str_contains($r, 'yahoo'))       return 'yahoo';
        if (str_contains($r, 'facebook') || str_contains($r, 'fb.com')) return 'facebook';
        if (str_contains($r, 'twitter') || str_contains($r, 'x.com'))   return 'twitter';
        if (str_contains($r, 'linkedin'))    return 'linkedin';
        if (str_contains($r, 'instagram'))   return 'instagram';
        if (str_contains($r, 'youtube'))     return 'youtube';
        return 'other';
    }
}
