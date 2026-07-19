<?php

namespace App\View\Components\Frontend;

use App\Models\Post;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\View\Component;

class PopularPosts extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Fetch posts by ranked IDs, preserving the order from the analytics query.
     */
    private function getPostsByRankedIds($ids)
    {
        if ($ids->isEmpty()) {
            return collect();
        }

        $posts = Post::where("status", true)->whereIn('id', $ids)->get();

        // Preserve the ranked order from analytics query
        $idOrder = $ids->flip();
        return $posts->sortBy(function ($post) use ($idOrder) {
            return $idOrder->get($post->id, PHP_INT_MAX);
        })->values();
    }

    public function render(): View|Closure|string
    {
        $totalPublished = Post::where("status", true)->count();

        if ($totalPublished < 1) {
            return '';
        }

        // Determine whether to show tabs or a single combined list
        $showTabs = $totalPublished > 4;

        // 1. Trending (last 7 days page views)
        $trendingIds = \App\Models\PageView::where('viewable_type', 'App\\Models\\Post')
            ->where('created_at', '>=', now()->subDays(7))
            ->select('viewable_id', DB::raw('COUNT(*) as count'))
            ->groupBy('viewable_id')
            ->orderByDesc('count')
            ->limit(5)
            ->pluck('viewable_id');
        $trending = $this->getPostsByRankedIds($trendingIds);
        if ($trending->isEmpty()) {
            // Fallback: random posts for variety
            $trending = Post::where("status", true)->inRandomOrder()->limit(5)->get();
        }

        // 2. Most Viewed (All time page views)
        $viewedIds = \App\Models\PageView::where('viewable_type', 'App\\Models\\Post')
            ->select('viewable_id', DB::raw('COUNT(*) as count'))
            ->groupBy('viewable_id')
            ->orderByDesc('count')
            ->limit(5)
            ->pluck('viewable_id');
        $viewed = $this->getPostsByRankedIds($viewedIds);
        if ($viewed->isEmpty()) {
            // Fallback: use the 'views' column on posts table
            $viewed = Post::where("status", true)->orderBy("views", "DESC")->limit(5)->get();
        }

        // 3. Most Liked (Likes count)
        $likedIds = \App\Models\LikesFeedback::whereIn('likeable_type', ['post', 'App\\Models\\Post'])
            ->where('type', 'like')
            ->select('likeable_id', DB::raw('COUNT(*) as count'))
            ->groupBy('likeable_id')
            ->orderByDesc('count')
            ->limit(5)
            ->pluck('likeable_id');
        $liked = $this->getPostsByRankedIds($likedIds);
        if ($liked->isEmpty()) {
            // Fallback: newest posts by created_at
            $liked = Post::where("status", true)->orderBy("created_at", "DESC")->limit(5)->get();
        }

        // 4. Highest Rated (Helpful yes feedback)
        $ratedIds = \App\Models\LikesFeedback::whereIn('likeable_type', ['post', 'App\\Models\\Post'])
            ->where('type', 'helpful_yes')
            ->select('likeable_id', DB::raw('COUNT(*) as count'))
            ->groupBy('likeable_id')
            ->orderByDesc('count')
            ->limit(5)
            ->pluck('likeable_id');
        $rated = $this->getPostsByRankedIds($ratedIds);
        if ($rated->isEmpty()) {
            // Fallback: oldest posts by created_at (different from liked fallback)
            $rated = Post::where("status", true)->orderBy("created_at", "ASC")->limit(5)->get();
        }

        return view('components.frontend.popular-posts', compact("trending", "viewed", "liked", "rated", "showTabs"));
    }
}
