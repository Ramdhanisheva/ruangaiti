<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use App\Models\Page;
use App\Models\Roadmap;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function roadmaps()
    {
        $roadmaps = Roadmap::published()->get();
        $content = view('frontend.sitemap.roadmaps', compact('roadmaps'))->render();
        return response('<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $content, 200)->header('Content-Type', 'text/xml');
    }
    public function index()
    {
        $content = view('frontend.sitemap.index')->render();
        return response('<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $content, 200)->header('Content-Type', 'text/xml');
    }

    public function posts()
    {
        $posts = Post::where('status', true)->latest()->get();
        $content = view('frontend.sitemap.posts', compact('posts'))->render();
        return response('<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $content, 200)->header('Content-Type', 'text/xml');
    }

    public function pages()
    {
        $pages = Page::where('status', true)->get();
        $content = view('frontend.sitemap.pages', compact('pages'))->render();
        return response('<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $content, 200)->header('Content-Type', 'text/xml');
    }

    public function taxonomies()
    {
        $categories = Category::where('status', true)->get();
        $tags = Tag::all();
        $content = view('frontend.sitemap.taxonomies', compact('categories', 'tags'))->render();
        return response('<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $content, 200)->header('Content-Type', 'text/xml');
    }

    public function authors()
    {
        $authors = User::where('status', true)->whereIn('role', [User::IS_AUTHOR, User::IS_ADMIN])->get();
        $content = view('frontend.sitemap.authors', compact('authors'))->render();
        return response('<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $content, 200)->header('Content-Type', 'text/xml');
    }

    public function robots()
    {
        $appUrl = url('/');
        $robotsContent = <<<TEXT
User-agent: *
Disallow:

Sitemap: {$appUrl}/sitemap.xml
TEXT;
        return response($robotsContent, 200)->header('Content-Type', 'text/plain');
    }

    public function feed()
    {
        $posts = Post::where('status', true)->latest()->limit(20)->get();
        $content = view('frontend.feed', compact('posts'))->render();
        return response($content, 200)->header('Content-Type', 'application/rss+xml; charset=utf-8');
    }
}
