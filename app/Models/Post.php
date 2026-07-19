<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "posts";
    protected $fillable = [
        "user_id",
        "title",
        "first_page_title",
        "slug",
        "category_id",
        "content",
        "thumbnail",
        "views",
        "is_featured",
        "enable_comment",
        "status",
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'enable_comment' => 'boolean',
        'status' => 'boolean',
    ];

    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function readTime() {
        $minutesToRead = ceil(Str::wordCount(strip_tags($this->content ?? '')) / 200);
        if ($minutesToRead < 1) {
            $minutesToRead = 1;
        }
        return $minutesToRead . " min read";
    }

    public function readTimeMinutes() {
        $minutesToRead = ceil(Str::wordCount(strip_tags($this->content ?? '')) / 200);
        return $minutesToRead < 1 ? 1 : $minutesToRead;
    }

    public function excerpt($limit = 120) {
        if (preg_match('/<p>(.*?)<\/p>/is', $this->content, $matches)) {
            $paragraph = $matches[1];
        } else {
            $paragraph = $this->content;
        }
        
        $text = preg_replace('/<[^>]*>/', ' ', $paragraph);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        return Str::limit($text, $limit);
    }

    public function tags() {
        return $this->belongsToMany(Tag::class);
    }

    public function comments() {
        return $this->hasMany(Comment::class)->orderBy("created_at", "ASC");
    }

    public function roadmapModulePost() {
        return $this->hasOne(RoadmapModulePost::class, 'post_id');
    }

    public function chapters() {
        return $this->hasMany(PostChapter::class, 'post_id')->orderBy('order', 'asc');
    }
}
