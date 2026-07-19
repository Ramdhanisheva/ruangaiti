<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostChapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Mews\Purifier\Facades\Purifier;

class PostChapterController extends Controller
{
    public function store(Request $request, Post $post)
    {
        if (!Gate::allows('update-post', $post)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        $maxOrder = $post->chapters()->max('order') ?? -1;

        $chapter = PostChapter::create([
            'post_id' => $post->id,
            'title' => $validated['title'] ?? null,
            'content' => Purifier::clean($validated['content']),
            'order' => $maxOrder + 1,
        ]);

        return response()->json([
            'success' => true,
            'chapter' => $chapter
        ]);
    }

    public function update(Request $request, Post $post, $chapterId)
    {
        if (!Gate::allows('update-post', $post)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $chapter = PostChapter::where('post_id', $post->id)->findOrFail($chapterId);

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        $chapter->update([
            'title' => $validated['title'] ?? null,
            'content' => Purifier::clean($validated['content']),
        ]);

        return response()->json([
            'success' => true,
            'chapter' => $chapter
        ]);
    }

    public function destroy(Post $post, $chapterId)
    {
        if (!Gate::allows('update-post', $post)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $chapter = PostChapter::where('post_id', $post->id)->findOrFail($chapterId);
        $chapter->delete();

        // Re-index orders to prevent gaps
        $chapters = $post->chapters()->get();
        foreach ($chapters as $index => $c) {
            $c->update(['order' => $index]);
        }

        return response()->json([
            'success' => true
        ]);
    }

    public function reorder(Request $request, Post $post)
    {
        if (!Gate::allows('update-post', $post)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'exists:post_chapters,id'],
        ]);

        foreach ($validated['ids'] as $index => $id) {
            PostChapter::where('post_id', $post->id)
                ->where('id', $id)
                ->update(['order' => $index]);
        }

        return response()->json([
            'success' => true
        ]);
    }
}
