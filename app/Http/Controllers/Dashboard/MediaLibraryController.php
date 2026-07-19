<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaLibraryController extends Controller
{
    public function __construct(private readonly MediaService $mediaService)
    {
        $this->middleware('admin');
    }

    /**
     * Display V3 Media Library panel.
     */
    public function index(Request $request)
    {
        $query = Media::query();

        // Search by filename or alt tag
        if ($search = $request->input('search')) {
            $query->where('file_name', 'LIKE', '%' . $search . '%')
                  ->orWhere('alt', 'LIKE', '%' . $search . '%');
        }

        // Filter by unused files
        if ($request->boolean('unused')) {
            $query->where('used_count', 0);
        }

        // Folder filter (simulated using folder extensions or prefix, if prefix folder matches)
        if ($folder = $request->input('folder')) {
            $query->where('extension', $folder);
        }

        $media = $query->orderBy('id', 'DESC')->paginate(24);

        // Get extension list for virtual folder navigation
        $extensions = Media::select('extension')->distinct()->pluck('extension')->filter();

        return view('dashboard.media.index', compact('media', 'extensions', 'search'));
    }

    /**
     * Upload a new asset.
     */
    public function store(Request $request)
    {
        $request->validate([
            'image' => ['required', 'file', 'max:10240', 'mimes:jpeg,png,gif,webp,svg'],
            'alt' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:500'],
        ]);

        $file = $request->file('image');
        $media = $this->mediaService->uploadFile($file, $request->input('alt'), $request->input('caption'));

        return redirect()->route('dashboard.media-library.index')->with('success', 'Media asset uploaded successfully!');
    }

    /**
     * Update media tags (Alt and Caption).
     */
    public function update(Request $request, Media $media)
    {
        $request->validate([
            'alt' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:500'],
        ]);

        $media->update($request->only(['alt', 'caption']));

        return back()->with('success', 'Media details updated!');
    }

    /**
     * Replace file for this media record.
     */
    public function replace(Request $request, Media $media)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:jpeg,png,gif,webp,svg'],
        ]);

        $file = $request->file('file');
        $this->mediaService->replaceFile($media, $file);

        return back()->with('success', 'Media file replaced successfully while maintaining existing links!');
    }

    /**
     * Usage details in JSON format.
     */
    public function usage(Media $media)
    {
        $usages = $this->mediaService->getUsageDetails($media);
        return response()->json($usages);
    }

    /**
     * Delete media record.
     */
    public function destroy(Media $media)
    {
        // Refresh usages count
        $usagesCount = $this->mediaService->updateUsageCount($media);

        if ($usagesCount > 0) {
            return back()->withErrors('Cannot delete media asset because it is currently used by ' . $usagesCount . ' content items.');
        }

        // Delete physical file from storage
        if (Storage::disk($media->disk)->exists($media->path)) {
            Storage::disk($media->disk)->delete($media->path);
        }

        $media->delete();

        return redirect()->route('dashboard.media-library.index')->with('success', 'Media asset deleted successfully.');
    }
}
