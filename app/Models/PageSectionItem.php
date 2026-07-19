<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageSectionItem extends Model
{
    protected $fillable = [
        'page_section_id',
        'title',
        'subtitle',
        'content',
        'image',
        'link',
        'sort_order',
        'meta_data',
    ];

    protected $casts = [
        'meta_data' => 'array',
    ];

    public function section()
    {
        return $this->belongsTo(PageSection::class, 'page_section_id');
    }
}
