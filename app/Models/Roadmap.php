<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Roadmap extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'icon',
        'cover',
        'description',
        'difficulty',
        'category_id',
        'status',
        'sort_order',
        'prerequisites',
        'learning_outcomes',
    ];

    public function modules()
    {
        return $this->hasMany(RoadmapModule::class)->orderBy('sort_order', 'asc');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Dynamic calculations
    public function modulesCount()
    {
        return $this->modules()->count();
    }

    public function articlesCount()
    {
        $count = 0;
        foreach ($this->modules as $module) {
            $count += $module->posts()->count();
        }
        return $count;
    }

    public function estimatedMinutes()
    {
        $minutes = 0;
        $modules = $this->modules()->with('posts')->get();
        foreach ($modules as $module) {
            foreach ($module->posts as $post) {
                $minutes += $post->readTimeMinutes();
            }
        }
        return $minutes;
    }

    public function lastUpdated()
    {
        $latestPostUpdate = null;
        foreach ($this->modules as $module) {
            $latest = $module->posts()->max('posts.updated_at');
            if ($latest && (!$latestPostUpdate || $latest > $latestPostUpdate)) {
                $latestPostUpdate = $latest;
            }
        }
        $roadmapUpdate = $this->updated_at ? $this->updated_at->toDateTimeString() : null;
        
        if ($latestPostUpdate && $roadmapUpdate) {
            return $latestPostUpdate > $roadmapUpdate ? \Carbon\Carbon::parse($latestPostUpdate) : $this->updated_at;
        }
        
        return $this->updated_at ?: \Carbon\Carbon::now();
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'Published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'Draft');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'Archived');
    }
}
