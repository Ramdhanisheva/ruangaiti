<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageView extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'visitor_id',
        'session_id',
        'path',
        'viewable_type',
        'viewable_id',
        'ip_hash',
        'device',
        'browser',
        'os',
        'referrer',
        'referrer_source',
        'read_time',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Polymorphic relation to the content entity being viewed.
     */
    public function viewable()
    {
        return $this->morphTo();
    }
}
