<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagController extends Controller
{
    public function index($name) {
        $tagModel = Tag::where('name', 'like', Str::lower(Str::headline($name)))
            ->orWhere('name', 'like', $name)
            ->first();
        if ($tagModel) {
            $posts = $tagModel->posts()->paginate(10);
            $tag = Str::lower(Str::headline($name));
            return view("frontend.tag.index", compact("posts", "tag", "tagModel"));
        }
        return redirect()->route("frontend.home");
    }
}
