<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Database\Eloquent\Relations\Relation::morphMap([
            'post' => 'App\Models\Post',
            'page' => 'App\Models\Page',
        ]);

        Paginator::useBootstrapFour();

        if (config('app.env') === 'production' || env('FORCE_HTTPS', true)) {
            URL::forceScheme('https');
        }

        view()->composer(['components.frontend.footer', 'components.frontend.sidebar-category'], function ($view) {
            $categories = \App\Models\Category::where('status', true)
                ->whereHas('posts', function ($query) {
                    $query->where('status', true);
                })
                ->withCount(['posts' => function ($query) {
                    $query->where('status', true);
                }])
                ->orderBy('posts_count', 'desc')
                ->get();
            $view->with('categories', $categories);
        });
    }
}
