<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\Roadmap;
use App\Models\RoadmapModule;
use App\Models\RoadmapModulePost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoadmapController extends Controller
{
    public function index()
    {
        $roadmaps = Roadmap::with('category')->orderBy('sort_order', 'asc')->paginate(10);
        return view('dashboard.roadmap.index', compact('roadmaps'));
    }

    public function create()
    {
        $categories = Category::where('status', true)->get();
        return view('dashboard.roadmap.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:roadmaps,slug',
            'difficulty' => 'required|string|in:Beginner,Intermediate,Advanced',
            'status' => 'required|string|in:Draft,Published,Archived',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data = $request->except('cover');
        $data['slug'] = Str::slug($request->slug);

        if ($request->hasFile('cover')) {
            $imageName = time() . '_' . uniqid() . '.' . strtolower($request->cover->getClientOriginalExtension());
            $request->cover->move(public_path('uploads/roadmap'), $imageName);
            $data['cover'] = $imageName;
        }

        Roadmap::create($data);

        return redirect()->route('dashboard.roadmaps.index')->with('success', 'Roadmap created successfully!');
    }

    public function edit($id)
    {
        $roadmap = Roadmap::findOrFail($id);
        $categories = Category::where('status', true)->get();
        return view('dashboard.roadmap.edit', compact('roadmap', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $roadmap = Roadmap::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:roadmaps,slug,' . $id,
            'difficulty' => 'required|string|in:Beginner,Intermediate,Advanced',
            'status' => 'required|string|in:Draft,Published,Archived',
            'cover' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data = $request->except('cover');
        $data['slug'] = Str::slug($request->slug);

        if ($request->hasFile('cover')) {
            // Delete old cover
            if ($roadmap->cover && file_exists(public_path('uploads/roadmap/' . $roadmap->cover))) {
                @unlink(public_path('uploads/roadmap/' . $roadmap->cover));
            }

            $imageName = time() . '_' . uniqid() . '.' . strtolower($request->cover->getClientOriginalExtension());
            $request->cover->move(public_path('uploads/roadmap'), $imageName);
            $data['cover'] = $imageName;
        }

        $roadmap->update($data);

        return redirect()->route('dashboard.roadmaps.index')->with('success', 'Roadmap updated successfully!');
    }

    public function destroy($id)
    {
        $roadmap = Roadmap::findOrFail($id);

        // Delete cover file
        if ($roadmap->cover && file_exists(public_path('uploads/roadmap/' . $roadmap->cover))) {
            @unlink(public_path('uploads/roadmap/' . $roadmap->cover));
        }

        $roadmap->delete();

        return redirect()->route('dashboard.roadmaps.index')->with('success', 'Roadmap deleted successfully!');
    }

    // Single-page Visual Roadmap Builder workspace
    public function builder($id)
    {
        $roadmap = Roadmap::with(['modules.posts' => function($query) {
            $query->orderBy('roadmap_module_posts.sort_order', 'asc');
        }])->findOrFail($id);

        return view('dashboard.roadmap.builder', compact('roadmap'));
    }

    // AJAX Module Operations
    public function addModule(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
        ]);

        $maxOrder = RoadmapModule::where('roadmap_id', $id)->max('sort_order') ?? -1;

        $module = RoadmapModule::create([
            'roadmap_id' => $id,
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'description' => $request->description,
            'icon' => $request->icon ?: 'fas fa-book',
            'color' => $request->color ?: '#2563eb',
            'sort_order' => $maxOrder + 1,
        ]);

        return response()->json([
            'success' => true,
            'module' => $module
        ]);
    }

    public function renameModule(Request $request, $id, $moduleId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
        ]);

        $module = RoadmapModule::where('id', $moduleId)->where('roadmap_id', $id)->firstOrFail();
        $module->update($request->only(['title', 'subtitle', 'description', 'icon', 'color']));

        return response()->json(['success' => true, 'module' => $module]);
    }

    public function deleteModule($id, $moduleId)
    {
        $module = RoadmapModule::where('id', $moduleId)->where('roadmap_id', $id)->firstOrFail();
        $module->delete();

        return response()->json(['success' => true]);
    }

    public function sortModules(Request $request, $id)
    {
        $moduleIds = $request->get('modules', []);
        foreach ($moduleIds as $index => $moduleId) {
            RoadmapModule::where('id', $moduleId)
                ->where('roadmap_id', $id)
                ->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    // AJAX Article/Post Operations
    public function addPost(Request $request, $id, $moduleId)
    {
        $request->validate([
            'post_ids' => 'required|array',
            'post_ids.*' => 'required|exists:posts,id'
        ]);

        $module = RoadmapModule::where('id', $moduleId)->where('roadmap_id', $id)->firstOrFail();
        $moduleIds = RoadmapModule::where('roadmap_id', $id)->pluck('id')->toArray();
        $postIds = $request->post_ids;

        foreach ($postIds as $postId) {
            // Remove from other modules of the same roadmap first (one post can only be in one module at a time)
            RoadmapModulePost::whereIn('roadmap_module_id', $moduleIds)
                ->where('post_id', $postId)
                ->delete();

            $maxOrder = RoadmapModulePost::where('roadmap_module_id', $moduleId)->max('sort_order') ?? -1;

            RoadmapModulePost::create([
                'roadmap_module_id' => $moduleId,
                'post_id' => $postId,
                'sort_order' => $maxOrder + 1
            ]);
        }

        // Fetch freshly added posts with complete details for visual rendering
        $posts = $module->posts()->whereIn('posts.id', $postIds)->get()->map(function($post) {
            return [
                'id' => $post->id,
                'title' => $post->title,
                'read_time' => $post->readTime(),
                'difficulty' => $post->is_featured ? 'Featured' : 'Standard',
            ];
        });

        return response()->json([
            'success' => true,
            'posts' => $posts
        ]);
    }

    public function removePost($id, $moduleId, $postId)
    {
        $module = RoadmapModule::where('id', $moduleId)->where('roadmap_id', $id)->firstOrFail();
        
        RoadmapModulePost::where('roadmap_module_id', $moduleId)
            ->where('post_id', $postId)
            ->delete();

        return response()->json(['success' => true]);
    }

    public function sortPosts(Request $request, $id)
    {
        $structure = $request->get('structure', []);
        $moduleIds = RoadmapModule::where('roadmap_id', $id)->pluck('id')->toArray();

        foreach ($structure as $moduleData) {
            $moduleId = $moduleData['module_id'];
            if (!in_array($moduleId, $moduleIds)) continue;

            $postIds = $moduleData['posts'] ?? [];

            foreach ($postIds as $index => $postId) {
                // Ensure post is only associated with the current target module
                RoadmapModulePost::whereIn('roadmap_module_id', $moduleIds)
                    ->where('post_id', $postId)
                    ->where('roadmap_module_id', '!=', $moduleId)
                    ->delete();

                RoadmapModulePost::updateOrCreate(
                    ['roadmap_module_id' => $moduleId, 'post_id' => $postId],
                    ['sort_order' => $index]
                );
            }
        }

        return response()->json(['success' => true]);
    }

    // Command-Palette AJAX Search Endpoint
    public function searchPosts(Request $request)
    {
        $q = $request->get('q');
        
        $posts = Post::where('status', true)
            ->where(function($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                      ->orWhere('slug', 'like', "%{$q}%")
                      ->orWhereHas('category', function($catQuery) use ($q) {
                          $catQuery->where('title', 'like', "%{$q}%");
                      })
                      ->orWhereHas('tags', function($tagQuery) use ($q) {
                          $tagQuery->where('name', 'like', "%{$q}%");
                      });
            })
            ->with(['category', 'user'])
            ->latest()
            ->take(15)
            ->get();

        return response()->json($posts->map(function($post) {
            return [
                'id' => $post->id,
                'title' => $post->title,
                'thumbnail' => $post->thumbnail ? asset('uploads/post/' . $post->thumbnail) : asset('assets/frontend/images/default.webp'),
                'category' => $post->category ? $post->category->title : 'Uncategorized',
                'read_time' => $post->readTime(),
                'author' => $post->user ? $post->user->name : 'Admin',
                'published_date' => $post->created_at->format('d M Y')
            ];
        }));
    }
}
