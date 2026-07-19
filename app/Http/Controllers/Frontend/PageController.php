<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index($slug) {
        $page = Page::where(function($q) {
            $q->where('status', true)->orWhere('status', 'Published');
        })->whereSlug($slug)->first();
        if ($page) {
            return view("frontend.page.index", compact("page"));
        }
        return abort(404);
    }
}
