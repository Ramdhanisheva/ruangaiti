<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LikesFeedback extends Model
{
    public $timestamps = false;

    protected $table = 'likes_feedback';

    protected $fillable = [
        'likeable_type',
        'likeable_id',
        'type',
        'visitor_id',
        'ip_hash',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function likeable()
    {
        return $this->morphTo();
    }
}
