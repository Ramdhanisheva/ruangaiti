<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index($slug) {
        $post = Post::with(["category", "user", "tags", "comments.user", "comments.replies.user", "roadmapModulePost.module.roadmap", "chapters"])->with("comments.replies", function($q) {
            $q->where("status", true);
        })->with("comments", function($q) {
            $q->where("status", true)->where("parent_id", null);
        })->withCount(["tags", "comments" => function($q) {
            $q->where("status", true);
        }])->where("status", true)->where("slug", $slug)->first();

        if ($post) {
            $post->views += 1;
            $post->save();
            $str = Str::class;

            // --- Chapters (Multi Page Article) Pagination ---
            $hasChapters = false;
            $currentPage = 1;
            $totalChapters = 0;
            $activeChapter = null;
            $chapters = collect();

            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('post_chapters')) {
                    $chapters = $post->chapters;
                    $dbChaptersCount = $chapters->count();
                    $hasChapters = $dbChaptersCount > 0;

                    if ($hasChapters) {
                        $totalChapters = 1 + $dbChaptersCount;
                        $currentPage = intval(request()->query('page', 1));
                        if ($currentPage < 1) $currentPage = 1;
                        if ($currentPage > $totalChapters) $currentPage = $totalChapters;
                        
                        if ($currentPage == 1) {
                            $activeChapter = null;
                        } else {
                            $activeChapter = $chapters->get($currentPage - 2);
                            // Override post content with the current page's chapter content
                            if ($activeChapter) {
                                $post->content = $activeChapter->content;
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Fail silently to prevent crashing the page if table is missing or migration is running
            }

            // Related posts: scored by category + shared tags + views
            $postTagIds = $post->tags->pluck('id')->toArray();

            // Base candidates: same category OR sharing at least one tag (wider pool)
            $candidateQuery = Post::with(['category', 'user', 'tags'])
                ->where('status', true)
                ->where('id', '!=', $post->id)
                ->where(function ($q) use ($post, $postTagIds) {
                    $q->where('category_id', $post->category_id);
                    if (!empty($postTagIds)) {
                        $q->orWhereHas('tags', function ($tq) use ($postTagIds) {
                            $tq->whereIn('tags.id', $postTagIds);
                        });
                    }
                })
                ->latest()
                ->limit(20)
                ->get();

            // Score each candidate
            $maxViews = $candidateQuery->max('views') ?: 1;
            $relatedPosts = $candidateQuery
                ->map(function ($p) use ($post, $postTagIds, $maxViews) {
                    $score = 0;
                    // Category match bonus
                    if ($p->category_id === $post->category_id) {
                        $score += 10;
                    }
                    // Shared tags bonus (5 per shared tag)
                    if (!empty($postTagIds)) {
                        $sharedTags = $p->tags->whereIn('id', $postTagIds)->count();
                        $score += $sharedTags * 5;
                    }
                    // Popularity bonus (0–5 normalized from views)
                    $score += round(($p->views / $maxViews) * 5, 2);
                    $p->_score = $score;
                    return $p;
                })
                ->sortByDesc('_score')
                ->take(3)
                ->values();

            // Fallback: fill remaining slots with latest posts
            if ($relatedPosts->count() < 3) {
                $excludeIds = $relatedPosts->pluck('id')->push($post->id)->toArray();
                $extras = Post::with(['category', 'user'])
                    ->where('status', true)
                    ->whereNotIn('id', $excludeIds)
                    ->latest()
                    ->limit(3 - $relatedPosts->count())
                    ->get();
                $relatedPosts = $relatedPosts->merge($extras);
            }

            // Previous post (older)
            $prevPost = Post::where("status", true)
                ->where("id", "<", $post->id)
                ->latest("id")
                ->first(["id", "title", "slug", "thumbnail", "content"]);

            // Next post (newer)
            $nextPost = Post::where("status", true)
                ->where("id", ">", $post->id)
                ->oldest("id")
                ->first(["id", "title", "slug", "thumbnail", "content"]);

            // --- Roadmap Context Integration ---
            $roadmap = null;
            $activeModule = null;
            $prevLesson = null;
            $nextLesson = null;
            $currentLessonNumber = 0;
            $totalLessons = 0;

            // Check if specific roadmap requested (indicating learning flow)
            if (request()->has('roadmap')) {
                $roadmap = \App\Models\Roadmap::published()->where('slug', request('roadmap'))->first();
            }

            if ($roadmap) {
                // Get all lessons in order
                $roadmapLessons = [];
                $modules = \App\Models\RoadmapModule::where('roadmap_id', $roadmap->id)
                    ->orderBy('sort_order', 'asc')
                    ->with(['posts' => function($q) {
                        $q->where('posts.status', true)->orderBy('roadmap_module_posts.sort_order', 'asc');
                    }])
                    ->get();

                foreach ($modules as $mod) {
                    if (!$activeModule && $mod->posts->contains($post->id)) {
                        $activeModule = $mod;
                    }
                    foreach ($mod->posts as $p) {
                        $p->module_title = $mod->title;
                        $roadmapLessons[] = $p;
                    }
                }

                $totalLessons = count($roadmapLessons);
                
                // Find position
                foreach ($roadmapLessons as $idx => $p) {
                    if ($p->id == $post->id) {
                        $currentLessonNumber = $idx + 1;
                        $prevLesson = $roadmapLessons[$idx - 1] ?? null;
                        if ($prevLesson) {
                            $prevLesson->lesson_number = $idx;
                        }
                        $nextLesson = $roadmapLessons[$idx + 1] ?? null;
                        if ($nextLesson) {
                            $nextLesson->lesson_number = $idx + 2;
                        }
                        break;
                    }
                }
            }

            return view("frontend.post.index", compact(
                "post", "str", "relatedPosts", "prevPost", "nextPost",
                "roadmap", "activeModule", "prevLesson", "nextLesson", "currentLessonNumber", "totalLessons",
                "hasChapters", "currentPage", "totalChapters", "activeChapter", "chapters"
            ));
        }
        return abort(404);
    }
}
