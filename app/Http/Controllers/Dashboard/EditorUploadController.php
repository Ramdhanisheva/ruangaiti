<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EditorUploadController extends Controller
{
    public function __construct(private readonly MediaService $mediaService)
    {
    }

    /**
     * Upload an image from the editor (Summernote drag-drop, paste, upload).
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => ['required', 'file', 'image', 'max:10240'], // Max 10MB image
        ]);

        // Ensure upload directory exists
        $uploadDir = public_path('uploads/media');
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        try {
            $media = $this->mediaService->uploadFile(
                $request->file('image'),
                null,
                null,
                null,
                null
            );

            Log::info('Editor image upload succeeded via MediaService', [
                'media_id' => $media->id,
                'file_name' => $media->file_name,
                'disk' => $media->disk,
                'path' => $media->path,
                'public_url' => $media->public_url,
            ]);

            // Access the public URL using the accessor getPublicUrlAttribute
            return response()->json([
                'success' => true,
                'url' => $media->public_url,
                'media_id' => $media->id,
                'file_name' => $media->file_name
            ]);
        } catch (\Throwable $e) {
            Log::error('Editor image upload failed via MediaService: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            // Graceful fallback to legacy public path upload if MediaService fails
            try {
                $file = $request->file('image');
                $imageName = md5(time() . rand(11111, 99999)) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/media'), $imageName);
                
                $url = asset('uploads/media/' . $imageName);
                
                Log::info('Editor image upload succeeded via fallback move', [
                    'file_name' => $imageName,
                    'url' => $url,
                ]);

                return response()->json([
                    'success' => true,
                    'url' => $url,
                    'file_name' => $imageName
                ]);
            } catch (\Throwable $fallbackEx) {
                Log::error('Editor image upload fallback also failed: ' . $fallbackEx->getMessage());
                
                return response()->json([
                    'success' => false,
                    'message' => 'Upload failed: ' . $fallbackEx->getMessage()
                ], 500);
            }
        }
    }
}
