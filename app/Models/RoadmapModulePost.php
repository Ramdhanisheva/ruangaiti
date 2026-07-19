<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoadmapModulePost extends Model
{
    use HasFactory;

    protected $table = 'roadmap_module_posts';

    protected $fillable = [
        'roadmap_module_id',
        'post_id',
        'sort_order',
    ];

    public function module()
    {
        return $this->belongsTo(RoadmapModule::class, 'roadmap_module_id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}
