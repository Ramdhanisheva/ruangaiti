<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Roadmap;
use Illuminate\Http\Request;

class RoadmapController extends Controller
{
    public function index(Request $request)
    {
        $query = Roadmap::published()->with(['modules.posts']);

        // Filter by category
        if ($request->has('category') && $request->category != '') {
            $query->where('category_id', $request->category);
        }

        // Filter by difficulty
        if ($request->has('difficulty') && $request->difficulty != '') {
            $query->where('difficulty', $request->difficulty);
        }

        // Search by keyword
        if ($request->has('q') && $request->q != '') {
            $q = $request->q;
            $query->where(function($subQuery) use ($q) {
                $subQuery->where('title', 'like', "%{$q}%")
                         ->orWhere('description', 'like', "%{$q}%");
            });
        }

        $roadmaps = $query->orderBy('sort_order', 'asc')->paginate(9)->withQueryString();
        $categories = Category::where('status', true)->orderBy('title', 'asc')->get();

        // SEO values
        $meta_title = "Roadmap Belajar IT Mandiri Secara Terstruktur | RuangAiTi";
        $meta_description = "Belajar IT secara terstruktur melalui kumpulan tutorial berkualitas di RuangAiTi. Pilih jalur belajar yang paling sesuai dari dasar hingga mahir.";
        $meta_url = route('frontend.roadmap');
        
        return view('frontend.roadmap.index', compact('roadmaps', 'categories', 'meta_title', 'meta_description', 'meta_url'));
    }

    public function show($slug)
    {
        $roadmap = Roadmap::published()
            ->where('slug', $slug)
            ->with(['modules.posts' => function($query) {
                $query->where('posts.status', true)->orderBy('roadmap_module_posts.sort_order', 'asc');
            }, 'category'])
            ->firstOrFail();

        // SEO details
        $meta_title = "Jalur Belajar " . $roadmap->title . " - Terstruktur & Gratis | RuangAiTi";
        $meta_description = $roadmap->description ?: "Ikuti panduan belajar terstruktur untuk menguasai " . $roadmap->title . " langkah-demi-langkah menggunakan artikel di RuangAiTi.";
        $meta_url = route('frontend.roadmap.show', $roadmap->slug);
        $meta_image = $roadmap->cover ? asset('uploads/roadmap/' . $roadmap->cover) : asset('assets/frontend/images/default-og.png');

        // Dynamic lists for Outcomes & Prerequisites
        $prerequisites = array_filter(array_map('trim', explode("\n", $roadmap->prerequisites)));
        $learning_outcomes = array_filter(array_map('trim', explode("\n", $roadmap->learning_outcomes)));

        return view('frontend.roadmap.show', compact('roadmap', 'prerequisites', 'learning_outcomes', 'meta_title', 'meta_description', 'meta_url', 'meta_image'));
    }
}
