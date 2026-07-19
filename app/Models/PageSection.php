<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageSection extends Model
{
    protected $fillable = [
        'page_id',
        'type',
        'layout_style',
        'sort_order',
        'status',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function items()
    {
        return $this->hasMany(PageSectionItem::class, 'page_section_id')->orderBy('sort_order');
    }
}
