<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index($slug) {
        $lookupSlugs = [$slug];
        if ($slug === 'cyber-security' || $slug === 'cyber-securityy') {
            $lookupSlugs = ['cyber-security', 'cyber-securityy'];
        }

        $category = Category::whereIn("slug", $lookupSlugs)->where("status", true)->first();
        if ($category) {
            $str = Str::class;
            $categoryIds = Category::whereIn("slug", $lookupSlugs)->pluck('id');
            $posts = \App\Models\Post::whereIn('category_id', $categoryIds)
                ->with(["category", "user"])
                ->where("status", true)
                ->orderBy("id", "DESC")
                ->paginate(10);
            return view("frontend.category.index", compact("category", "posts", "str"));
        }
        return abort(404);
    }
}
