<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request) {
        if ($request->q) {
            $query = $request->q;
            
            // Handle AJAX search requests for the Ctrl+K Command Palette
            if ($request->ajax() || $request->has("ajax")) {
                $posts = Post::with("category")
                    ->where("status", true)
                    ->where("title", "LIKE", "%{$query}%")
                    ->orderBy("id", "DESC")
                    ->limit(10)
                    ->get();
                
                return response()->json($posts->map(function($post) {
                    return [
                        "title" => $post->title,
                        "slug" => $post->slug,
                        "category_title" => $post->category->title ?? "Uncategorized"
                    ];
                }));
            }

            $posts = Post::with("category")->whereStatus(true)->where("title", "LIKE", "%{$query}%")->orWhere("title", "LIKE", "%{$query}%")->orderBy("id", "DESC")->paginate(10);
            return view("frontend.search.index", compact("posts", "query"));
        }
        return redirect()->route("frontend.home");
    }
}
