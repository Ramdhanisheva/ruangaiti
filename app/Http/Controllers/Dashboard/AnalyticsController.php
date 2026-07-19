<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use App\Models\Post;
use App\Models\PageView;
use App\Models\SearchLog;
use App\Models\LikesFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;

class AnalyticsController extends Controller
{
    public function __construct(private readonly AnalyticsService $analytics)
    {
        $this->middleware('admin');
    }

    /**
     * Analytics Overview.
     */
    public function overview(Request $request)
    {
        $period = $request->input('period', '30days');
        $stats = $this->analytics->getOverviewStats($period);
        $chartData = $this->analytics->getViewsChartData(30);

        return view('dashboard.analytics.overview', compact('stats', 'chartData', 'period'));
    }

    /**
     * Audience Analytics.
     */
    public function audience(Request $request)
    {
        $period = $request->input('period', '30days');
        $devices = $this->analytics->getDeviceStats($period);
        $browsers = $this->analytics->getBrowserStats($period);
        $referrers = $this->analytics->getReferrerStats($period);
        $os = $this->analytics->getOsStats($period);

        return view('dashboard.analytics.audience', compact('devices', 'browsers', 'referrers', 'os', 'period'));
    }

    /**
     * Content & Search Analytics.
     */
    public function content(Request $request)
    {
        $period = $request->input('period', '30days');
        $topArticles = $this->analytics->getTopContent('App\\Models\\Post', 10, $period);
        $topRoadmaps = $this->analytics->getTopContent('App\\Models\\Roadmap', 10, $period);
        $topSearches = $this->analytics->getTopSearches(20, $period);
        $zeroSearches = $this->analytics->getZeroResultSearches(20, $period);

        return view('dashboard.analytics.content', compact('topArticles', 'topRoadmaps', 'topSearches', 'zeroSearches', 'period'));
    }

    /**
     * Manage Analytics Data Dashboard UI.
     */
    public function manage(Request $request)
    {
        $posts = Post::withTrashed()->orderBy('title', 'asc')->get();
        
        foreach ($posts as $post) {
            $post->likes_count = LikesFeedback::whereIn('likeable_type', ['post', 'App\\Models\\Post'])
                ->where('likeable_id', $post->id)
                ->where('type', 'like')
                ->count();
            $post->helpful_yes_count = LikesFeedback::whereIn('likeable_type', ['post', 'App\\Models\\Post'])
                ->where('likeable_id', $post->id)
                ->where('type', 'helpful_yes')
                ->count();
            $post->helpful_no_count = LikesFeedback::whereIn('likeable_type', ['post', 'App\\Models\\Post'])
                ->where('likeable_id', $post->id)
                ->where('type', 'helpful_no')
                ->count();
        }

        $searchQuery = $request->input('q');
        $searchLogsQuery = SearchLog::orderBy('created_at', 'desc');
        if (!empty($searchQuery)) {
            $searchLogsQuery->where('query', 'like', '%' . $searchQuery . '%');
        }
        $searchLogs = $searchLogsQuery->paginate(50)->withQueryString();

        return view('dashboard.analytics.manage', compact('posts', 'searchLogs', 'searchQuery'));
    }

    /**
     * Clear Page Views.
     */
    public function clearViews(Request $request)
    {
        $request->validate([
            'mode' => 'required|in:all,date,before',
            'date' => 'required_if:mode,date,before|nullable|date',
        ]);

        $mode = $request->mode;
        $query = PageView::query();

        if ($mode === 'all') {
            $query->delete();
            $message = 'Semua data views berhasil dihapus.';
        } elseif ($mode === 'date') {
            $date = Carbon::parse($request->date);
            $query->whereDate('created_at', $date)->delete();
            $message = 'Data views pada tanggal ' . $date->format('d M Y') . ' berhasil dihapus.';
        } elseif ($mode === 'before') {
            $date = Carbon::parse($request->date);
            $query->where('created_at', '<', $date->startOfDay())->delete();
            $message = 'Data views sebelum tanggal ' . $date->format('d M Y') . ' berhasil dihapus.';
        }

        $this->clearAnalyticsCache();

        return back()->with('success', $message);
    }

    /**
     * Clear Search Logs.
     */
    public function clearSearches(Request $request)
    {
        $request->validate([
            'action_type' => 'required|in:all,selected',
            'ids' => 'required_if:action_type,selected|array',
            'ids.*' => 'exists:search_logs,id',
        ]);

        if ($request->action_type === 'all') {
            SearchLog::query()->delete();
            $message = 'Semua data pencarian berhasil dihapus.';
        } else {
            SearchLog::whereIn('id', $request->ids)->delete();
            $message = count($request->ids) . ' data pencarian terpilih berhasil dihapus.';
        }

        $this->clearAnalyticsCache();

        return back()->with('success', $message);
    }

    /**
     * Adjust Likes/Feedback.
     */
    public function adjustLikes(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id',
            'type' => 'required|in:like,helpful_yes,helpful_no',
            'action' => 'required|in:add,remove,reset',
            'quantity' => 'required_unless:action,reset|nullable|integer|min:1|max:1000',
        ]);

        $postId = $request->post_id;
        $type = $request->type;
        $action = $request->action;
        $qty = $request->quantity;

        if ($action === 'add') {
            for ($i = 0; $i < $qty; $i++) {
                LikesFeedback::create([
                    'likeable_type' => 'post',
                    'likeable_id' => $postId,
                    'type' => $type,
                    'visitor_id' => 'mock-' . Str::random(10),
                    'ip_hash' => hash('sha256', 'mock-ip-' . rand(1, 100000)),
                    'created_at' => now()->subHours(rand(0, 72)),
                ]);
            }
            $message = "Berhasil menambahkan {$qty} data feedback ({$type}) pada postingan.";
        } elseif ($action === 'remove') {
            $deleted = LikesFeedback::whereIn('likeable_type', ['post', 'App\\Models\\Post'])
                ->where('likeable_id', $postId)
                ->where('type', $type)
                ->limit($qty)
                ->delete();
            $message = "Berhasil menghapus {$deleted} data feedback ({$type}) dari postingan.";
        } else { // reset
            $deleted = LikesFeedback::whereIn('likeable_type', ['post', 'App\\Models\\Post'])
                ->where('likeable_id', $postId)
                ->where('type', $type)
                ->delete();
            $message = "Berhasil mereset (menghapus semua) data feedback ({$type}) dari postingan. Total terhapus: {$deleted} data.";
        }

        $this->clearAnalyticsCache();

        return back()->with('success', $message);
    }

    public function clearCacheManual()
    {
        $this->clearAnalyticsCache();
        return back()->with('success', 'Cache grafik, statistik, dan pencarian berhasil dibersihkan.');
    }

    private function clearAnalyticsCache()
    {
        $periods = ['today', '7days', '30days', '90days', '12months'];
        
        \Illuminate\Support\Facades\Cache::forget('analytics.chart.views.30');
        
        foreach ($periods as $p) {
            \Illuminate\Support\Facades\Cache::forget('analytics.overview.' . $p);
            \Illuminate\Support\Facades\Cache::forget('analytics.devices.' . $p);
            \Illuminate\Support\Facades\Cache::forget('analytics.browsers.' . $p);
            \Illuminate\Support\Facades\Cache::forget('analytics.referrers.' . $p);
            \Illuminate\Support\Facades\Cache::forget('analytics.os.' . $p);
            \Illuminate\Support\Facades\Cache::forget('analytics.top.App\\Models\\Post.' . $p);
            \Illuminate\Support\Facades\Cache::forget('analytics.top.App\\Models\\Roadmap.' . $p);
            \Illuminate\Support\Facades\Cache::forget('analytics.searches.' . $p);
            \Illuminate\Support\Facades\Cache::forget('analytics.zero_searches.' . $p);
        }
    }
}
