<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsAggregate extends Model
{
    protected $fillable = [
        'date',
        'entity_type',
        'entity_id',
        'views',
        'unique_views',
        'likes',
        'helpful_yes',
        'helpful_no',
        'avg_read_time',
        'bookmarks',
        'searches',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
