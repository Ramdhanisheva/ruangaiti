<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    public const IS_VISITOR = 1;
    public const IS_AUTHOR = 2;
    public const IS_ADMIN = 3;
    
    protected $fillable = [
        'name',
        'username',
        'email',
        'profile',
        'about',
        'tagline',
        'role',
        'facebook',
        'twitter',
        'instagram',
        'linkedin',
        'youtube',
        'status',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'status' => 'boolean',
    ];

    protected function username(): Attribute {
        return Attribute::make(
            set: fn ($value) => Str::lower($value)
        );
    }

    public function posts() {
        return $this->hasMany(Post::class);
    }

    public function comments() {
        return $this->hasMany(Comment::class);
    }

    public function media() {
        return $this->hasMany(Media::class);
    }

    public function getStats(): array
    {
        $userId = $this->id;
        $publishedArticlesCount = $this->posts()->where('status', true)->count();

        $roadmapQuery = Roadmap::published()->whereHas('modules.posts', function ($query) use ($userId) {
            $query->where('user_id', $userId)->where('posts.status', true);
        });
        $publishedRoadmapsCount = $roadmapQuery->count();

        $postIds = $this->posts()->where('status', true)->pluck('id');
        $roadmapIds = $roadmapQuery->pluck('id');

        $totalViews = $this->posts()->where('status', true)->sum('views');
        if ($roadmapIds->isNotEmpty()) {
            $totalViews += PageView::whereIn('viewable_type', ['roadmap', 'App\\Models\\Roadmap'])
                ->whereIn('viewable_id', $roadmapIds)
                ->count();
        }

        $likesCount = LikesFeedback::whereIn('likeable_type', ['post', 'App\\Models\\Post'])
            ->whereIn('likeable_id', $postIds)
            ->where('type', 'like')
            ->count();
        if ($roadmapIds->isNotEmpty()) {
            $likesCount += LikesFeedback::whereIn('likeable_type', ['roadmap', 'App\\Models\\Roadmap'])
                ->whereIn('likeable_id', $roadmapIds)
                ->where('type', 'like')
                ->count();
        }

        $yesCount = LikesFeedback::whereIn('likeable_type', ['post', 'App\\Models\\Post'])
            ->whereIn('likeable_id', $postIds)
            ->where('type', 'helpful_yes')
            ->count();
        if ($roadmapIds->isNotEmpty()) {
            $yesCount += LikesFeedback::whereIn('likeable_type', ['roadmap', 'App\\Models\\Roadmap'])
                ->whereIn('likeable_id', $roadmapIds)
                ->where('type', 'helpful_yes')
                ->count();
        }

        $noCount = LikesFeedback::whereIn('likeable_type', ['post', 'App\\Models\\Post'])
            ->whereIn('likeable_id', $postIds)
            ->where('type', 'helpful_no')
            ->count();
        if ($roadmapIds->isNotEmpty()) {
            $noCount += LikesFeedback::whereIn('likeable_type', ['roadmap', 'App\\Models\\Roadmap'])
                ->whereIn('likeable_id', $roadmapIds)
                ->where('type', 'helpful_no')
                ->count();
        }

        $totalFeedback = $yesCount + $noCount;
        $helpfulRating = $totalFeedback > 0 ? round(($yesCount / $totalFeedback) * 100) : 100;

        // Last published content
        $latestPost = $this->posts()->where('status', true)->latest('created_at')->first();
        $latestRoadmap = Roadmap::published()->whereHas('modules.posts', function ($query) use ($userId) {
            $query->where('user_id', $userId)->where('posts.status', true);
        })->latest('created_at')->first();

        $lastContent = null;
        if ($latestPost && $latestRoadmap) {
            $lastContent = $latestPost->created_at > $latestRoadmap->created_at ? $latestPost : $latestRoadmap;
        } elseif ($latestPost) {
            $lastContent = $latestPost;
        } elseif ($latestRoadmap) {
            $lastContent = $latestRoadmap;
        }

        return [
            'articles_count' => $publishedArticlesCount,
            'roadmaps_count' => $publishedRoadmapsCount,
            'total_views' => $totalViews,
            'likes_count' => $likesCount,
            'helpful_rating' => $helpfulRating,
            'last_content' => $lastContent,
        ];
    }
}
