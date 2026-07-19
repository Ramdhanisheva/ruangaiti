<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostChapter extends Model
{
    use HasFactory;

    protected $table = 'post_chapters';

    protected $fillable = [
        'post_id',
        'title',
        'content',
        'order',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}
