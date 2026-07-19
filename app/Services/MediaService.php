<?php

namespace App\Services;

use App\Models\Media;
use App\Models\Post;
use App\Models\Roadmap;
use App\Models\Page;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;


class MediaService
{
    /**
     * Upload and store a new media file.
     */
    public function uploadFile(
        UploadedFile $file,
        ?string $alt = null,
        ?string $caption = null,
        ?string $title = null,
        ?string $description = null
    ): Media {
        $disk = 'uploads';
        $originalName = $file->getClientOriginalName();
        $filename = md5(time() . uniqid()) . '.' . $file->getClientOriginalExtension();
        $folder = 'media';

        // IMPORTANT: Extract all metadata from temp file BEFORE storeAs(),
        // because storeAs() moves/deletes the temp file making getRealPath() invalid.
        $width = null;
        $height = null;
        $dominantColor = null;
        $hash = null;

        $tempPath = $file->getRealPath();

        if ($tempPath && file_exists($tempPath)) {
            if (str_starts_with($file->getMimeType(), 'image/')) {
                $sizes = @getimagesize($tempPath);
                if ($sizes) {
                    $width = $sizes[0];
                    $height = $sizes[1];
                }
                $dominantColor = $this->extractDominantColor($tempPath);
            }
            $hash = @hash_file('sha256', $tempPath);
        }

        // Save file physically using Laravel standard Storage facade
        $path = $file->storeAs($folder, $filename, $disk);

        // If storeAs failed, try manual move as fallback
        if (!$path) {
            $targetDir = public_path('uploads/media');
            if (!is_dir($targetDir)) {
                @mkdir($targetDir, 0755, true);
            }
            $file->move($targetDir, $filename);
            $path = 'media/' . $filename;
        }

        return Media::create([
            'user_id'       => Auth::id() ?? 1,
            'disk'          => $disk,
            'path'          => $path,
            'file_name'     => $filename,
            'original_name' => $originalName,
            'extension'     => strtolower($file->getClientOriginalExtension()),
            'mime'          => $file->getMimeType(),
            'size'          => $file->getSize(),
            'width'         => $width,
            'height'        => $height,
            'alt'           => $alt,
            'caption'       => $caption,
            'title'         => $title,
            'description'   => $description,
            'dominant_color' => $dominantColor,
            'hash'          => $hash,
            'used_count'    => 0,
        ]);
    }

    /**
     * Upload multiple files at once. Returns array of created Media records.
     */
    public function uploadMultiple(array $files, ?string $alt = null, ?string $caption = null): array
    {
        $uploaded = [];
        foreach ($files as $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $uploaded[] = $this->uploadFile($file, $alt, $caption);
            }
        }
        return $uploaded;
    }

    /**
     * Replace file for existing media entry.
     * Preserves filename & path, updates size, mime, dimensions, dominant color, and hash.
     */
    public function replaceFile(Media $media, UploadedFile $file): bool
    {
        $disk = $media->disk;
        
        // Delete old file
        if (Storage::disk($disk)->exists($media->path)) {
            Storage::disk($disk)->delete($media->path);
        }

        // Store new file in the exact same path/filename
        Storage::disk($disk)->putFileAs(dirname($media->path), $file, $media->file_name);

        $width = null;
        $height = null;
        $dominantColor = null;

        if (str_starts_with($file->getMimeType(), 'image/')) {
            $sizes = @getimagesize($file->getRealPath());
            if ($sizes) {
                $width = $sizes[0];
                $height = $sizes[1];
            }
            $dominantColor = $this->extractDominantColor($file->getRealPath());
        }

        $hash = hash_file('sha256', $file->getRealPath());

        return $media->update([
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
            'width' => $width,
            'height' => $height,
            'dominant_color' => $dominantColor,
            'hash' => $hash,
        ]);
    }

    /**
     * Find references to this media file across Posts, Roadmaps, Pages, etc.
     */
    public function getUsageDetails(Media $media): array
    {
        $usages = [];
        $filename = $media->file_name;

        // Check posts thumbnail
        $postsThumb = Post::where('thumbnail', $filename)->get();
        foreach ($postsThumb as $post) {
            $usages[] = [
                'type' => 'Post Thumbnail',
                'title' => $post->title,
                'url' => route('frontend.post', $post->slug)
            ];
        }

        // Check posts content for embedded link
        $postsContent = Post::where('content', 'LIKE', '%' . $filename . '%')->get();
        foreach ($postsContent as $post) {
            $usages[] = [
                'type' => 'Post Body Content',
                'title' => $post->title,
                'url' => route('frontend.post', $post->slug)
            ];
        }

        // Check roadmaps cover
        $roadmapsCover = Roadmap::where('cover', 'LIKE', '%' . $filename . '%')->get();
        foreach ($roadmapsCover as $roadmap) {
            $usages[] = [
                'type' => 'Roadmap Cover',
                'title' => $roadmap->title,
                'url' => route('frontend.roadmap.show', $roadmap->slug)
            ];
        }

        // Check static pages
        $pagesContent = Page::where('content', 'LIKE', '%' . $filename . '%')->get();
        foreach ($pagesContent as $page) {
            $usages[] = [
                'type' => 'Page Content',
                'title' => $page->title,
                'url' => route('frontend.page', $page->slug)
            ];
        }

        // Check page sections if page sections exist (Phase 4)
        if (Schema::hasTable('page_section_items')) {
            $sectionItems = DB::table('page_section_items')
                ->where('image', 'LIKE', '%' . $filename . '%')
                ->orWhere('content', 'LIKE', '%' . $filename . '%')
                ->get();
            foreach ($sectionItems as $item) {
                $usages[] = [
                    'type' => 'Page Section Component',
                    'title' => $item->title ?? 'Section Item',
                    'url' => '#'
                ];
            }
        }

        return $usages;
    }

    /**
     * Recalculate usage counts for a media item.
     */
    public function updateUsageCount(Media $media): int
    {
        $usages = $this->getUsageDetails($media);
        $count = count($usages);
        $media->update(['used_count' => $count]);
        return $count;
    }

    /**
     * Extract dominant hexadecimal color from an image.
     */
    private function extractDominantColor(string $filePath): ?string
    {
        if (!extension_loaded('gd')) {
            return null;
        }

        $info = @getimagesize($filePath);
        if (!$info) {
            return null;
        }

        $mime = $info['mime'];
        switch ($mime) {
            case 'image/jpeg':
                $img = @imagecreatefromjpeg($filePath);
                break;
            case 'image/png':
                $img = @imagecreatefrompng($filePath);
                break;
            case 'image/gif':
                $img = @imagecreatefromgif($filePath);
                break;
            case 'image/webp':
                $img = @imagecreatefromwebp($filePath);
                break;
            default:
                return null;
        }

        if (!$img) {
            return null;
        }

        $tmp = imagecreatetruecolor(1, 1);
        imagecopyresampled($tmp, $img, 0, 0, 0, 0, 1, 1, imagesx($img), imagesy($img));
        $rgb = imagecolorat($tmp, 0, 0);

        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;

        imagedestroy($img);
        imagedestroy($tmp);

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
}
