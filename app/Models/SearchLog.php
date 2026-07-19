<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'query',
        'search_type',
        'results_count',
        'page',
        'duration_ms',
        'visitor_id',
        'ip_hash',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
