<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "pages";

    protected $fillable = [
        "title",
        "slug",
        "content",
        "template",
        "status",
        "published_at",
        "seo_title",
        "seo_description",
        "json_ld",
    ];

    protected $casts = [
        "published_at" => "datetime",
    ];

    public function sections()
    {
        return $this->hasMany(PageSection::class)->orderBy('sort_order');
    }
}
