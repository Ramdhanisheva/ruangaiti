<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoadmapModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'roadmap_id',
        'title',
        'subtitle',
        'description',
        'icon',
        'color',
        'sort_order',
    ];

    public function roadmap()
    {
        return $this->belongsTo(Roadmap::class);
    }

    public function posts()
    {
        return $this->belongsToMany(Post::class, 'roadmap_module_posts', 'roadmap_module_id', 'post_id')
            ->withPivot('id', 'sort_order')
            ->withTimestamps()
            ->orderBy('roadmap_module_posts.sort_order', 'asc');
    }

    public function roadmapModulePosts()
    {
        return $this->hasMany(RoadmapModulePost::class, 'roadmap_module_id');
    }
}
