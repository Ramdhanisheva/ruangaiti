<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\Roadmap;
use App\Models\RoadmapModule;
use App\Models\RoadmapModulePost;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Mews\Purifier\Facades\Purifier;
use App\Services\ContentImportService;

class PostController extends Controller
{
    public function __construct(private readonly ContentImportService $contentImportService) {}
    public function index() {
        if (Auth::user()->role == 3) {
            $posts = Post::with(["category", "tags", "user"])->withCount(["comments"])->orderBy("id", "DESC")->paginate(20);
        } else {
            $posts = Post::with(["category", "tags", "user"])->withCount(["comments"])->orderBy("id", "DESC")->where("user_id", Auth::id())->paginate(20);
        }
        return view("dashboard.post.index", compact("posts"));
    }

    public function create() {
        $categories = Category::where("status", true)->orderBy("title", "ASC")->get();
        $tags = Tag::orderBy("name", "ASC")->get();
        $roadmaps = Roadmap::with('modules')->orderBy('title', 'asc')->get();
        return view("dashboard.post.add", compact("categories", "tags", "roadmaps"));
    }

    public function store(Request $request) {
        $validated = $request->validate([
            "title" => ["required", "string"],
            "first_page_title" => ["nullable", "string", "max:150"],
            "slug" => ["required", "string", "unique:posts,slug"],
            "content" => ["required", "string"],
            "category" => ["required", "exists:categories,id"],
            "tags" => ["nullable", "array"],
            "featured" => ["nullable", Rule::in(["0", "1"])],
            "comment" => ["nullable", Rule::in(["0", "1"])],
            "status" => ["required", Rule::in(["0", "1"])],
            "thumbnail" => ["required", "image"],
        ]);
        $image = $request->file("thumbnail");
        $imageName = md5(time().rand(11111, 99999)).".".strtolower($image->getClientOriginalExtension());
        $image->move(public_path("uploads/post"), $imageName);
        $post = Post::create([
            "user_id" => Auth::user()->id,
            "title" => $validated["title"],
            "first_page_title" => $validated["first_page_title"] ?? null,
            "slug" => Str::slug($validated["slug"]),
            "category_id" => $validated["category"],
            "content" => Purifier::clean($validated["content"]),
            "thumbnail" => $imageName,
            "is_featured" => Arr::has($validated, "featured"),
            "enable_comment" => Arr::has($validated, "comment"),
            "status" => Auth::user()->role == 1 ? "0" : $validated["status"],
        ]);
        if (Arr::has($validated, "tags")) {
            foreach ($validated["tags"] as $tag) {
                $tag = Tag::firstOrCreate(["name" => Str::lower($tag)]);
                $post->tags()->attach([$tag->id]);
            }
        }

        // Roadmap Integration Redesign Save Flow
        if ($request->has('in_roadmap') && $request->roadmap_module_id) {
            $maxOrder = RoadmapModulePost::where('roadmap_module_id', $request->roadmap_module_id)->max('sort_order') ?? -1;
            RoadmapModulePost::create([
                'roadmap_module_id' => $request->roadmap_module_id,
                'post_id' => $post->id,
                'sort_order' => $maxOrder + 1
            ]);
        }

        return redirect()->route("dashboard.posts.index")->with("success", "Post created!");
    }

    public function edit($id) {
        $post = Post::with(["tags", "roadmapModulePost.module"])->withCount(["tags"])->find($id);
        if ($post && Gate::allows("update-post", $post)) {
            $categories = Category::where("status", true)->orderBy("title", "ASC")->get();
            $tags = Tag::orderBy("name", "ASC")->get();
            $roadmaps = Roadmap::with('modules')->orderBy('title', 'asc')->get();
            return view("dashboard.post.edit", compact("post", "categories", "tags", "roadmaps"));
        }
        return back()->withErrors("Post not exists!");
    }

    public function update(Request $request, $id) {
        $post = Post::find($id);
        if ($post && Gate::allows("update-post", $post)) {
            $validated = $request->validate([
                "title" => ["required", "string"],
                "first_page_title" => ["nullable", "string", "max:150"],
                "slug" => ["required", "string", Rule::unique("posts", "slug")->ignore($post->id)],
                "content" => ["required", "string"],
                "category" => ["required", "exists:categories,id"],
                "tags" => ["nullable", "array"],
                "featured" => ["nullable", Rule::in(["0", "1"])],
                "comment" => ["nullable", Rule::in(["0", "1"])],
                "status" => ["required", Rule::in(["0", "1"])],
                "thumbnail" => ["nullable", "image"],
            ]);
            $post->title = $validated["title"];
            $post->first_page_title = $validated["first_page_title"] ?? null;
            $post->slug = Str::slug($validated["slug"]);
            $post->category_id = $validated["category"];
            $post->content = Purifier::clean($validated["content"]);
            $post->is_featured = Arr::has($validated, "featured");
            $post->enable_comment = Arr::has($validated, "comment");
            $post->status = Auth::user()->role == 1 ? "0" : $validated["status"];
            if ($request->hasFile("thumbnail")) {
                $image = $request->file("thumbnail");
                $imageName = md5(time().rand(11111, 99999)).".".strtolower($image->getClientOriginalExtension());
                $image->move(public_path("uploads/post"), $imageName);
                if (File::exists(public_path("uploads/post/".$post->thumbnail))) {
                    File::delete(public_path("uploads/post/".$post->thumbnail));
                }
                $post->thumbnail = $imageName;
            }
            $post->save();

            // Roadmap Integration Redesign Save/Sync Flow
            if ($request->has('in_roadmap') && $request->roadmap_module_id) {
                $currentPivot = RoadmapModulePost::where('post_id', $post->id)->first();
                if ($currentPivot) {
                    if ($currentPivot->roadmap_module_id != $request->roadmap_module_id) {
                        $currentPivot->delete();
                        $maxOrder = RoadmapModulePost::where('roadmap_module_id', $request->roadmap_module_id)->max('sort_order') ?? -1;
                        RoadmapModulePost::create([
                            'roadmap_module_id' => $request->roadmap_module_id,
                            'post_id' => $post->id,
                            'sort_order' => $maxOrder + 1
                        ]);
                    }
                } else {
                    $maxOrder = RoadmapModulePost::where('roadmap_module_id', $request->roadmap_module_id)->max('sort_order') ?? -1;
                    RoadmapModulePost::create([
                        'roadmap_module_id' => $request->roadmap_module_id,
                        'post_id' => $post->id,
                        'sort_order' => $maxOrder + 1
                    ]);
                }
            } else {
                // If unchecked, delete the association
                RoadmapModulePost::where('post_id', $post->id)->delete();
            }

            if (Arr::has($validated, "tags")) {
                $tagArr = [];
                foreach ($validated["tags"] as $tag) {
                    $tag = Tag::firstOrCreate(["name" => Str::lower($tag)]);
                    $tagArr[] = $tag->id;
                }
                $post->tags()->sync($tagArr);
            } else {
                $post->tags()->sync([]);
            }
            return redirect()->route("dashboard.posts.index")->with("success", "Post updated!");
        }
        return back()->withErrors("Post not exists!");
    }

    public function destroy($id) {
        $post = Post::find($id);
        if ($post && Gate::allows("update-post", $post)) {
            $post->delete();
            return back()->with("success", "Post deleted!");
        }
        return back()->withErrors("Post not exists!");
    }

    public function status($id) {
        $post = Post::find($id);
        if ($post && Gate::allows("update-post", $post)) {
            if (Auth::user()->role == 1) {
                return back()->withErrors("You can't update status!");
            }
            $post->status = $post->status ? "0" : "1";
            $post->save();
            $alert = $post->status ? "Post published!" : "Post drafted!";
            return back()->with("success", $alert);
        }
        return back()->withErrors("Post not exists!");
    }

    public function featured($id) {
        $post = Post::find($id);
        if ($post) {
            $post->is_featured = $post->is_featured ? "0" : "1";
            $post->save();
            $alert = $post->is_featured ? "Post added to featured!" : "Post removed from featured!";
            return back()->with("success", $alert);
        }
        return back()->withErrors("Post not exists!");
    }

    public function comment($id) {
        $post = Post::find($id);
        if ($post && Gate::allows("update-post", $post)) {
            $post->enable_comment = $post->enable_comment ? "0" : "1";
            $post->save();
            $alert = $post->enable_comment ? "Post comment enabled!" : "Post comment disabled!";
            return back()->with("success", $alert);
        }
        return back()->withErrors("Post not exists!");
    }


    public function trashed() {
        if (Auth::user()->role == 3) {
            $posts = Post::onlyTrashed()->with(["category" => function($q) {
                $q->withTrashed();
            }, "tags", "user"])->withCount(["comments" => function($q) {
                $q->withTrashed();
            }])->orderBy("id", "DESC")->paginate(20);
        } else {
            $posts = Post::onlyTrashed()->with(["category" => function($q) {
                $q->withTrashed();
            }, "tags", "user"])->withCount(["comments" => function($q) {
                $q->withTrashed();
            }])->orderBy("id", "DESC")->where("user_id", Auth::id())->paginate(20);
        }
        return view("dashboard.post.trashed", compact("posts"));
    }

    public function restore($id) {
        $post = Post::onlyTrashed()->find($id);
        if ($post && Gate::allows("update-post", $post)) {
            if ($post->category()->withTrashed()->first()->deleted_at) {
                return back()->withErrors("Restore the category first!");
            }
            $post->restore();
            return back()->with("success", "Post restored!");
        }
        return back()->withErrors("Post not exists!");
    }

    public function delete($id) {
        $post = Post::onlyTrashed()->find($id);
        if ($post && Gate::allows("update-post", $post)) {
            if (File::exists(public_path("uploads/post/".$post->thumbnail))) {
                File::delete(public_path("uploads/post/".$post->thumbnail));
            }
            $post->tags()->sync([]);
            $post->comments()->forceDelete();
            $post->forceDelete();
            return back()->with("success", "Post deleted!");
        }
        return back()->withErrors("Post not exists!");
    }

    public function importContent(Request $request) {
        $request->validate([
            'file' => ['required', 'file', 'max:10240']
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        $path = $file->getRealPath();

        try {
            $html = '';
            if ($ext === 'md' || $ext === 'markdown') {
                $html = $this->contentImportService->importMarkdown(file_get_contents($path));
            } elseif ($ext === 'docx') {
                $html = $this->contentImportService->importDocx($path);
            } elseif ($ext === 'pdf') {
                $html = $this->contentImportService->importPdf($path);
            } elseif ($ext === 'html' || $ext === 'htm') {
                $html = $this->contentImportService->importHtml(file_get_contents($path));
            } else {
                return response()->json(['error' => 'Unsupported file format.'], 422);
            }

            // Clean parsed HTML using Purifier before injection
            $html = Purifier::clean($html);

            return response()->json(['html' => $html]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to parse file: ' . $e->getMessage()], 500);
        }
    }
}
