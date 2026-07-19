<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\User;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaController extends Controller
{
    public function __construct(private readonly MediaService $mediaService)
    {
        //
    }

    /**
     * Unified Media Library — index with search, filter, sort, statistics.
     */
    public function index(Request $request)
    {
        $query = Media::query();

        // Role-based scope
        if (Auth::user()->role != 3) {
            $query->where('user_id', Auth::id());
        }

        // Search across multiple fields
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('file_name', 'LIKE', "%{$search}%")
                  ->orWhere('original_name', 'LIKE', "%{$search}%")
                  ->orWhere('alt', 'LIKE', "%{$search}%")
                  ->orWhere('caption', 'LIKE', "%{$search}%")
                  ->orWhere('title', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('extension', 'LIKE', "%{$search}%");
            });
        }

        // Type filter
        $type = $request->input('type', 'all');
        switch ($type) {
            case 'image':
                $query->whereIn('extension', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'ico', 'avif']);
                break;
            case 'svg':
                $query->where('extension', 'svg');
                break;
            case 'video':
                $query->whereIn('extension', ['mp4', 'webm', 'ogg', 'mov', 'avi']);
                break;
            case 'pdf':
                $query->where('extension', 'pdf');
                break;
            case 'document':
                $query->whereIn('extension', ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'odt']);
                break;
        }

        // Unused filter (admin only)
        if (Auth::user()->role == 3 && $request->boolean('unused')) {
            $query->where('used_count', 0);
        }

        // Virtual folder by extension
        if ($folder = $request->input('folder')) {
            $query->where('extension', $folder);
        }

        // Sorting
        $sort = $request->input('sort', 'newest');
        match ($sort) {
            'oldest'   => $query->orderBy('id', 'ASC'),
            'name'     => $query->orderBy('file_name', 'ASC'),
            'size_asc' => $query->orderBy('size', 'ASC'),
            'size_desc'=> $query->orderBy('size', 'DESC'),
            default    => $query->orderBy('id', 'DESC'), // newest
        };

        $media      = $query->paginate(24)->withQueryString();
        $extensions = Media::select('extension')->distinct()->pluck('extension')->filter()->sort()->values();
        $search     = $request->input('search');

        // Statistics (admin only — slightly expensive, cached per-request)
        $stats = null;
        if (Auth::user()->role == 3) {
            $allMedia = Media::query();
            $stats = [
                'total'    => $allMedia->count(),
                'images'   => (clone $allMedia)->whereIn('extension', ['jpg','jpeg','png','gif','webp','bmp','avif'])->count(),
                'svgs'     => (clone $allMedia)->where('extension', 'svg')->count(),
                'videos'   => (clone $allMedia)->whereIn('extension', ['mp4','webm','ogg','mov','avi'])->count(),
                'pdfs'     => (clone $allMedia)->where('extension', 'pdf')->count(),
                'documents'=> (clone $allMedia)->whereIn('extension', ['doc','docx','xls','xlsx','ppt','pptx','txt','csv'])->count(),
                'unused'   => (clone $allMedia)->where('used_count', 0)->count(),
                'storage'  => $this->formatBytes((clone $allMedia)->sum('size')),
            ];
        }

        return view('dashboard.media.index', compact('media', 'extensions', 'search', 'stats', 'type', 'sort'));
    }

    /**
     * Upload form — supports single and multiple file uploads.
     */
    public function create()
    {
        return view('dashboard.media.add');
    }

    /**
     * Store uploaded file(s).
     * Supports single file or multiple files (images[]).
     */
    public function store(Request $request)
    {
        // Multiple file upload mode
        if ($request->hasFile('images')) {
            $request->validate([
                'images'   => ['required', 'array', 'max:20'],
                'images.*' => ['file', 'max:10240', 'mimes:jpeg,jpg,png,gif,webp,svg,mp4,pdf,doc,docx,xls,xlsx'],
            ]);
            $uploaded = $this->mediaService->uploadMultiple(
                $request->file('images'),
                $request->input('alt'),
                $request->input('caption')
            );
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'count'   => count($uploaded),
                    'message' => count($uploaded) . ' file(s) uploaded successfully!',
                ]);
            }
            return redirect()->route('dashboard.media.index')
                ->with('success', count($uploaded) . ' file(s) uploaded successfully!');
        }

        // Single file upload (also handles AJAX drag-drop)
        $request->validate([
            'image'       => ['required', 'file', 'max:10240', 'mimes:jpeg,jpg,png,gif,webp,svg,mp4,pdf,doc,docx,xls,xlsx'],
            'alt'         => ['nullable', 'string', 'max:255'],
            'caption'     => ['nullable', 'string', 'max:500'],
            'title'       => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $media = $this->mediaService->uploadFile(
                $request->file('image'),
                $request->input('alt'),
                $request->input('caption'),
                $request->input('title'),
                $request->input('description')
            );
        } catch (\Throwable $e) {
            // Graceful legacy fallback
            $file      = $request->file('image');
            $imageName = md5(time() . rand(11111, 99999)) . '.' . strtolower($file->getClientOriginalExtension());
            $file->move(public_path('uploads/media'), $imageName);
            Media::create(['user_id' => Auth::id(), 'file_name' => $imageName]);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'File uploaded!']);
        }

        return redirect()->route('dashboard.media.index')->with('success', 'Media uploaded successfully!');
    }

    /**
     * Update SEO metadata — alt, caption, title, description.
     */
    public function update(Request $request, Media $media)
    {
        $request->validate([
            'alt'         => ['nullable', 'string', 'max:255'],
            'caption'     => ['nullable', 'string', 'max:500'],
            'title'       => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $media->update($request->only(['alt', 'caption', 'title', 'description']));

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Media details updated!']);
        }

        return back()->with('success', 'Media details updated!');
    }

    /**
     * Replace the physical file while preserving existing links.
     */
    public function replace(Request $request, Media $media)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:jpeg,jpg,png,gif,webp,svg'],
        ]);

        try {
            $this->mediaService->replaceFile($media, $request->file('file'));
            $message = 'Media replaced — existing links still work!';
        } catch (\Throwable $e) {
            return back()->withErrors('Replace failed: ' . $e->getMessage());
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    /**
     * Return JSON usage details for the modal.
     */
    public function usage(Media $media)
    {
        try {
            $usages = $this->mediaService->getUsageDetails($media);
            return response()->json($usages);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Serve file as download.
     */
    public function download(Media $media): StreamedResponse
    {
        $filename = $media->original_name ?? $media->file_name;

        // Storage disk path
        if ($media->disk && $media->path && Storage::disk($media->disk)->exists($media->path)) {
            return Storage::disk($media->disk)->download($media->path, $filename);
        }

        // Legacy public path
        $legacyPath = public_path('uploads/media/' . $media->file_name);
        if (File::exists($legacyPath)) {
            return response()->streamDownload(function () use ($legacyPath) {
                echo File::get($legacyPath);
            }, $filename);
        }

        abort(404, 'File not found.');
    }

    /**
     * Bulk delete selected media IDs.
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer', 'exists:media,id'],
        ]);

        $deleted = 0;
        $blocked = 0;

        foreach ($request->input('ids') as $id) {
            $media = Media::find($id);
            if (!$media) continue;

            // Only admin or owner
            if (Auth::user()->role != 3 && $media->user_id !== Auth::id()) {
                $blocked++;
                continue;
            }

            // Block if still in use
            try {
                $count = $this->mediaService->updateUsageCount($media);
                if ($count > 0) { $blocked++; continue; }
            } catch (\Throwable) {
                // ignore service errors for bulk op
            }

            $this->deleteMediaFile($media);
            $media->delete();
            $deleted++;
        }

        $msg = "{$deleted} file(s) deleted.";
        if ($blocked > 0) $msg .= " {$blocked} skipped (in-use or no permission).";

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $msg, 'deleted' => $deleted]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Delete a single media asset.
     */
    public function destroy($id)
    {
        $media = ($id instanceof Media) ? $id : Media::findOrFail($id);

        if (Auth::user()->role != 3 && !Gate::allows('update-media', $media)) {
            return back()->withErrors('You do not have permission to delete this file.');
        }

        try {
            $count = $this->mediaService->updateUsageCount($media);
            if ($count > 0) {
                return back()->withErrors("Cannot delete: this file is used in {$count} content item(s).");
            }
            if ($media->disk && $media->path && Storage::disk($media->disk)->exists($media->path)) {
                Storage::disk($media->disk)->delete($media->path);
            }
        } catch (\Throwable) {
            $this->deleteMediaFile($media);
        }

        $media->delete();
        return redirect()->route('dashboard.media.index')->with('success', 'Media deleted!');
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    private function deleteMediaFile(Media $media): void
    {
        $legacy = public_path('uploads/media/' . $media->file_name);
        if (File::exists($legacy)) File::delete($legacy);
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        if ($bytes === 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = (int) floor(log($bytes) / log(1024));
        return round($bytes / pow(1024, $i), $precision) . ' ' . $units[$i];
    }
}
