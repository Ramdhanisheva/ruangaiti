<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $table = "media";

    protected $fillable = [
        "user_id",
        "disk",
        "path",
        "file_name",
        "original_name",
        "extension",
        "mime",
        "size",
        "width",
        "height",
        "alt",
        "caption",
        "title",
        "description",
        "dominant_color",
        "hash",
        "used_count",
    ];

    /** Relationship: uploader */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Human-readable file size accessor.
     * e.g. 1024 → "1 KB", 1048576 → "1 MB"
     */
    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->size ?? 0;
        if ($bytes === 0) return '0 B';
        $k = 1024;
        $sizes = ['B', 'KB', 'MB', 'GB'];
        $i = (int) floor(log($bytes) / log($k));
        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }

    /**
     * Categorise file into a human-readable type group.
     * Returns: image | svg | video | pdf | document | other
     */
    public function getFileTypeAttribute(): string
    {
        $ext = strtolower($this->extension ?? '');
        if ($ext === 'svg') return 'svg';
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'ico', 'avif'])) return 'image';
        if (in_array($ext, ['mp4', 'webm', 'ogg', 'mov', 'avi'])) return 'video';
        if ($ext === 'pdf') return 'pdf';
        if (in_array($ext, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'odt'])) return 'document';
        return 'other';
    }

    /**
     * Return the public URL of this media file.
     * Supports both legacy uploads/media/ path and Storage disk paths.
     */
    public function getPublicUrlAttribute(): string
    {
        if ($this->disk === 'public' && $this->path) {
            return asset('storage/' . $this->path);
        }
        return asset('uploads/media/' . $this->file_name);
    }
}
