<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\SignupController;
use App\Http\Controllers\Dashboard\CategoryController as DashboardCategoryController;
use App\Http\Controllers\Dashboard\CommentController as DashboardCommentController;
use App\Http\Controllers\Dashboard\HomeController as DashboardHomeController;
use App\Http\Controllers\Dashboard\MediaController;
use App\Http\Controllers\Dashboard\MenuController;
use App\Http\Controllers\Dashboard\PageController as DashboardPageController;

use App\Http\Controllers\Dashboard\PostController as DashboardPostController;
use App\Http\Controllers\Dashboard\ProfileController;
use App\Http\Controllers\Dashboard\RoadmapController as DashboardRoadmapController;
use App\Http\Controllers\Dashboard\SiteSettingController;
use App\Http\Controllers\Dashboard\SocialMediaController;
use App\Http\Controllers\Dashboard\TagController as DashboardTagController;
use App\Http\Controllers\Dashboard\UserController as DashboardUserController;
use App\Http\Controllers\Dashboard\AnalyticsController as DashboardAnalyticsController;
use App\Http\Controllers\Frontend\CategoryController;
use App\Http\Controllers\Frontend\CommentController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\Frontend\PostController;
use App\Http\Controllers\Frontend\RoadmapController;
use App\Http\Controllers\Frontend\SearchController;
use App\Http\Controllers\Frontend\SitemapController;
use App\Http\Controllers\Frontend\TagController;
use App\Http\Controllers\Frontend\UserController;
use Illuminate\Support\Facades\Route;

Route::name("frontend.")->group(function() {
    Route::get("/sitemap.xml", [SitemapController::class, "index"])->name("sitemap");
    Route::get("/sitemap-posts.xml", [SitemapController::class, "posts"])->name("sitemap.posts");
    Route::get("/sitemap-pages.xml", [SitemapController::class, "pages"])->name("sitemap.pages");
    Route::get("/sitemap-roadmaps.xml", [SitemapController::class, "roadmaps"])->name("sitemap.roadmaps");
    Route::get("/sitemap-taxonomies.xml", [SitemapController::class, "taxonomies"])->name("sitemap.taxonomies");
    Route::get("/sitemap-authors.xml", [SitemapController::class, "authors"])->name("sitemap.authors");
    Route::get("/robots.txt", [SitemapController::class, "robots"])->name("robots");
    Route::get("/feed", [SitemapController::class, "feed"])->name("feed");

    Route::get("/", [HomeController::class, "index"])->name("home");
    Route::get("/search", [SearchController::class, "index"])->name("search");
    Route::get("/post/{slug}", [PostController::class, "index"])->name("post");
    Route::post("/comment/{id}", [CommentController::class, "index"])->name("comment");
    Route::post("/comment-reply", [CommentController::class, "reply"])->name("comment.reply");
    Route::get("/category/{slug}", [CategoryController::class, "index"])->name("category");
    Route::get("/user/{username}", [UserController::class, "index"])->name("user");
    Route::get("/members", [UserController::class, "members"])->name("members");
    Route::get("/tag/{name}", [TagController::class, "index"])->name("tag");
    Route::get("/page/{slug}", [PageController::class, "index"])->name("page");
    Route::get("/roadmap", [RoadmapController::class, "index"])->name("roadmap");
    Route::get("/roadmap/{slug}", [RoadmapController::class, "show"])->name("roadmap.show");
    Route::post("/newsletter/subscribe", [\App\Http\Controllers\Frontend\NewsletterController::class, "subscribe"])->name("newsletter.subscribe");
});

// internal tracking routes protected by web middleware (CSRF + throttling)
Route::prefix('/internal-analytics')->middleware(['web', 'throttle:60,1'])->group(function() {
    Route::post('/track', [\App\Http\Controllers\Analytics\TrackingController::class, 'track'])->name('analytics.track');
    Route::post('/ping', [\App\Http\Controllers\Analytics\TrackingController::class, 'ping'])->name('analytics.ping');
    Route::post('/feedback', [\App\Http\Controllers\Analytics\TrackingController::class, 'feedback'])->name('analytics.feedback');
    Route::post('/search', [\App\Http\Controllers\Analytics\TrackingController::class, 'search'])->name('analytics.search');
});

// cPanel Web deploy helper (runs migrations, seeders, clear caches via browser)
Route::get('/deploy-v3', function() {
    if (request('key') !== 'ruangaiti2026') {
        abort(403, 'Unauthorized. Please specify valid key.');
    }
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $output = "Migration completed.\n";

        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'V3TestSeeder']);
        $output .= "V3TestSeeder completed.\n";

        \Illuminate\Support\Facades\Artisan::call('config:clear');
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        \Illuminate\Support\Facades\Artisan::call('route:clear');
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        $output .= "All Laravel caches cleared successfully!\n";

        return response($output, 200)->header('Content-Type', 'text/plain');
    } catch (\Exception $e) {
        return response("Deploy Error:\n" . $e->getMessage(), 500)->header('Content-Type', 'text/plain');
    }
});

// Clear cache helper (public, no key required)
Route::get('/clear-cache', function() {
    try {
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        \Illuminate\Support\Facades\Artisan::call('route:clear');
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        return response("All Laravel caches cleared successfully!", 200)->header('Content-Type', 'text/plain');
    } catch (\Exception $e) {
        return response("Error clearing cache: " . $e->getMessage(), 500)->header('Content-Type', 'text/plain');
    }
});

// Diagnostics & Log Reader (Key Protected)
Route::get('/debug-logs', function() {
    if (request('key') !== 'ruangaiti2026') {
        abort(403, 'Unauthorized');
    }
    
    $out = "=== DIAGNOSTICS ===\n";
    $out .= "Public Path: " . public_path() . "\n";
    $out .= "Base Path: " . base_path() . "\n\n";

    $mediaDir = public_path('uploads/media');
    $out .= "=== uploads/media folder ===\n";
    if (file_exists($mediaDir) && is_dir($mediaDir)) {
        $files = scandir($mediaDir);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $out .= "  - $file (" . round(filesize($mediaDir . '/' . $file) / 1024, 1) . " KB)\n";
            }
        }
    } else {
        $out .= "  Folder does not exist or is not a directory.\n";
    }

    $postDir = public_path('uploads/post');
    $out .= "\n=== uploads/post folder ===\n";
    if (file_exists($postDir) && is_dir($postDir)) {
        $files = scandir($postDir);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $out .= "  - $file (" . round(filesize($postDir . '/' . $file) / 1024, 1) . " KB)\n";
            }
        }
    } else {
        $out .= "  Folder does not exist or is not a directory.\n";
    }

    $logFile = storage_path('logs/laravel.log');
    $out .= "\n=== LAST 100 LINES OF LARAVEL.LOG ===\n\n";
    if (!file_exists($logFile)) {
        $out .= "laravel.log not found.\n";
    } else {
        // Read last 100 lines
        $lines = [];
        $handle = @fopen($logFile, 'r');
        if ($handle) {
            $cursor = -1;
            @fseek($handle, $cursor, SEEK_END);
            $char = @fgetc($handle);

            while ($char === "\n" || $char === "\r") {
                $cursor--;
                @fseek($handle, $cursor, SEEK_END);
                $char = @fgetc($handle);
            }

            $lineCount = 0;
            $maxLines = 100;
            $buffer = '';

            while ($cursor > -1000000 && $lineCount < $maxLines) {
                @fseek($handle, $cursor, SEEK_END);
                $char = @fgetc($handle);

                if ($char === "\n" || $char === "\r") {
                    if ($buffer !== '') {
                        $lines[] = strrev($buffer);
                        $buffer = '';
                        $lineCount++;
                    }
                } else {
                    $buffer .= $char;
                }
                $cursor--;
            }
            
            if ($buffer !== '') {
                $lines[] = strrev($buffer);
            }
            @fclose($handle);
        }
        $lines = array_reverse($lines);
        $out .= implode("\n", $lines);
    }

    // Database post content inspection
    $postId = request('post_id', 12);
    $post = \App\Models\Post::find($postId);
    if ($post) {
        $out .= "\n\n=== POST ID $postId DATABASE CONTENT ===\n";
        $out .= "Title: " . $post->title . "\n";
        $out .= "Raw HTML Content:\n" . htmlspecialchars($post->content) . "\n";
    } else {
        $out .= "\n\n=== POST ID $postId DATABASE CONTENT ===\n";
        $out .= "Post ID $postId not found in database.\n";
    }

    // Media table inspection
    $out .= "\n\n=== LAST 10 MEDIA TABLE RECORDS ===\n";
    try {
        $medias = \App\Models\Media::orderBy('id', 'desc')->take(10)->get();
        if ($medias->count() > 0) {
            foreach ($medias as $m) {
                $out .= "ID: {$m->id} | Name: {$m->file_name} | Orig: {$m->original_name} | Disk: {$m->disk} | Path: {$m->path} | URL: {$m->public_url} | Created: {$m->created_at}\n";
            }
        } else {
            $out .= "No records found in media table.\n";
        }
    } catch (\Exception $ex) {
        $out .= "Error querying media table: " . $ex->getMessage() . "\n";
    }
    // User table inspection
    $out .= "\n\n=== USER ACCOUNTS ===\n";
    try {
        $users = \App\Models\User::all();
        foreach ($users as $u) {
            $out .= "ID: {$u->id} | Name: {$u->name} | Email: {$u->email} | Role: {$u->role}\n";
        }
    } catch (\Exception $ex) {
        $out .= "Error querying users table: " . $ex->getMessage() . "\n";
    }

    // Clear Purifier cache programmatically
    if (request('clear_purifier') == '1') {
        $purifierCacheDir = storage_path('app/purifier');
        if (file_exists($purifierCacheDir) && is_dir($purifierCacheDir)) {
            $files = glob($purifierCacheDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
            $out .= "\n=== HTMLPURIFIER CACHE CLEARED ===\n";
        }
    }

    // HTMLPurifier Cache Audit
    $purifierCacheDir = storage_path('app/purifier');
    $out .= "\n=== HTMLPurifier cache folder ===\n";
    if (file_exists($purifierCacheDir) && is_dir($purifierCacheDir)) {
        $files = scandir($purifierCacheDir);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $out .= "  - $file (" . round(filesize($purifierCacheDir . '/' . $file) / 1024, 1) . " KB)\n";
            }
        }
    } else {
        $out .= "  Folder does not exist or is not a directory.\n";
    }

    return response($out, 200)->header('Content-Type', 'text/plain');
});

// Storage symlink / junction helper
Route::get('/create-symlink', function() {
    try {
        $uploadsPublic = storage_path('app/public/uploads');
        if (!file_exists($uploadsPublic)) {
            mkdir($uploadsPublic, 0755, true);
        }
        
        $linkPath = base_path('storage/uploads');
        if (!file_exists($linkPath)) {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $target = str_replace('/', '\\', $uploadsPublic);
                $link = str_replace('/', '\\', $linkPath);
                exec("mklink /J \"{$link}\" \"{$target}\"", $output, $resultCode);
                if ($resultCode !== 0) {
                    throw new \Exception("Windows junction failed: " . implode("\n", $output));
                }
            } else {
                if (!symlink($uploadsPublic, $linkPath)) {
                    throw new \Exception("Linux symlink creation failed.");
                }
            }
            return response("Symlink/Junction created successfully!", 200)->header('Content-Type', 'text/plain');
        }
        return response("Symlink/Junction already exists.", 200)->header('Content-Type', 'text/plain');
    } catch (\Exception $e) {
        return response("Error creating symlink/junction: " . $e->getMessage(), 500)->header('Content-Type', 'text/plain');
    }
});

Route::name("auth.")->group(function() {
    Route::get("/signup", [SignupController::class, "index"])->name("signup");
    Route::post("/signup", [SignupController::class, "signup"])->middleware("throttle:30,1")->name("signup.submit");
    Route::get("/login", [LoginController::class, "index"])->name("login");
    Route::post("/login", [LoginController::class, "login"])->middleware("throttle:30,1")->name("login.submit");
    Route::post("/logout", [LogoutController::class, "index"])->name("logout");
});

Route::name("dashboard.")->prefix("/dashboard")->middleware(["auth"])->group(function() {
    // dashboard home
    Route::get("/", [DashboardHomeController::class, "index"])->name("home");

    // editor image upload
    Route::post("/editor/upload-image", [\App\Http\Controllers\Dashboard\EditorUploadController::class, "uploadImage"])->name("editor.upload-image");

    // posts
    Route::prefix("/posts")->name("posts.")->controller(DashboardPostController::class)->group(function() {
        Route::get("/{id}/status", "status")->name("status");
        Route::get("/{id}/featured", "featured")->name("featured");
        Route::get("/{id}/comment", "comment")->name("comment");
        Route::get("/trashed", "trashed")->name("trashed");
        Route::get("/{id}/restore", "restore")->name("restore");
        Route::delete("/{id}/delete", "delete")->name("delete");
        Route::post("/import-content", "importContent")->name("import-content");
    });
    Route::resource("/posts", DashboardPostController::class)->except(["show"]);

    // post chapters
    Route::prefix("/posts/{post}/chapters")->name("posts.chapters.")->controller(\App\Http\Controllers\Dashboard\PostChapterController::class)->group(function() {
        Route::post("/", "store")->name("store");
        Route::put("/{chapter}", "update")->name("update");
        Route::delete("/{chapter}", "destroy")->name("destroy");
        Route::post("/reorder", "reorder")->name("reorder");
    });

    // media — unified module (upload, search, filter, sort, replace, usage, bulk, download)
    Route::prefix("/media")->name("media.")->controller(MediaController::class)->group(function() {
        Route::post("/{media}/replace", "replace")->name("replace");
        Route::get("/{media}/usage",    "usage")->name("usage");
        Route::get("/{media}/download", "download")->name("download");
        Route::post("/bulk-destroy",    "bulkDestroy")->name("bulk-destroy");
    });
    Route::resource("/media", MediaController::class)->except(["show", "edit"]);

    // comments
    Route::prefix("/comments")->name("comments.")->controller(DashboardCommentController::class)->group(function() {
        Route::get("/{id}/status", "status")->name("status");
        Route::get("/trashed", "trashed")->name("trashed");
        Route::get("/{id}/restore", "restore")->name("restore");
        Route::delete("/{id}/delete", "delete")->name("delete");
    });
    Route::resource("/comments", DashboardCommentController::class)->only(["index", "show", "destroy"]);

    // categories
    Route::prefix("/categories")->name("categories.")->controller(DashboardCategoryController::class)->middleware(["admin"])->group(function() {
        Route::get("/{id}/status", "status")->name("status");
        Route::get("/trashed", "trashed")->name("trashed");
        Route::get("/{id}/restore", "restore")->name("restore");
        Route::delete("/{id}/delete", "delete")->name("delete");
    });
    Route::resource("/categories", DashboardCategoryController::class)->middleware(["admin"]);

    //tags
    Route::prefix("/tags")->name("tags.")->controller(DashboardTagController::class)->middleware(["admin"])->group(function() {
        Route::get("/index", "index")->name("index");
        Route::delete("/{id}/destroy", "destroy")->name("destroy");
    });

    // users
    Route::prefix("/users")->name("users.")->controller(DashboardUserController::class)->middleware(["admin"])->group(function() {
        Route::get("/{id}/status", "status")->name("status");
    });
    Route::resource("/users", DashboardUserController::class)->middleware(["admin"]);

    // pages
    Route::prefix("/pages")->name("pages.")->controller(DashboardPageController::class)->middleware(["admin"])->group(function() {
        Route::get("/{id}/status", "status")->name("status");
        Route::get("/trashed", "trashed")->name("trashed");
        Route::get("/{id}/restore", "restore")->name("restore");
        Route::delete("/{id}/delete", "delete")->name("delete");

        // V3 Section Builder & Revisions integrations (ajax endpoints)
        Route::post("/duplicate/{page}", "duplicatePage")->name("duplicate-page");
        Route::post("/sort-sections", "sortSections")->name("sort-sections");
        Route::post("/{page}/add-section", "addSection")->name("add-section");
        Route::post("/section/{section}/duplicate", "duplicateSection")->name("duplicate-section");
        Route::delete("/section/{section}/delete", "deleteSection")->name("delete-section");
        Route::post("/section/{section}/save-items", "saveSectionItems")->name("save-section-items");
        Route::post("/{page}/restore-revision", "restoreRevision")->name("restore-revision");
        Route::post("/import-content", "importContent")->name("import-content");
    });
    Route::resource("/pages", DashboardPageController::class)->except(["show"])->middleware(["admin"]);

    // roadmaps
    Route::prefix("/roadmaps")->name("roadmaps.")->controller(DashboardRoadmapController::class)->middleware(["admin"])->group(function() {
        Route::get("/builder/{id}", "builder")->name("builder");
        Route::post("/builder/{id}/module/add", "addModule")->name("builder.module.add");
        Route::post("/builder/{id}/module/{moduleId}/rename", "renameModule")->name("builder.module.rename");
        Route::delete("/builder/{id}/module/{moduleId}/delete", "deleteModule")->name("builder.module.delete");
        Route::post("/builder/{id}/sort-modules", "sortModules")->name("builder.sort_modules");
        Route::post("/builder/{id}/module/{moduleId}/add-post", "addPost")->name("builder.module.add_post");
        Route::delete("/builder/{id}/module/{moduleId}/remove-post/{postId}", "removePost")->name("builder.module.remove_post");
        Route::post("/builder/{id}/sort-posts", "sortPosts")->name("builder.sort_posts");
        Route::get("/search-posts", "searchPosts")->name("search_posts");
    });
    Route::resource("/roadmaps", DashboardRoadmapController::class)->middleware(["admin"]);

    // analytics workspace
    Route::prefix("/analytics")->name("analytics.")->middleware(["admin"])->controller(DashboardAnalyticsController::class)->group(function() {
        Route::get("/overview", "overview")->name("overview");
        Route::get("/audience", "audience")->name("audience");
        Route::get("/content", "content")->name("content");
        Route::get("/manage", "manage")->name("manage");
        Route::post("/manage/clear-views", "clearViews")->name("manage.clear_views");
        Route::post("/manage/clear-searches", "clearSearches")->name("manage.clear_searches");
        Route::post("/manage/adjust-likes", "adjustLikes")->name("manage.adjust_likes");
        Route::post("/manage/clear-cache", "clearCacheManual")->name("manage.clear_cache");
    });

    // settings
    Route::prefix("/settings")->name("settings.")->middleware(["admin"])->group(function() {
        // site settings
        Route::get("/site-settings", [SiteSettingController::class, "index"])->name("site");
        Route::post("/site-settings", [SiteSettingController::class, "update"])->name("site.update");
        Route::post("/generate-sitemap", [SiteSettingController::class, "generateSitemap"])->name("sitemap.generate");
        // profile update
        Route::get("/profile", [ProfileController::class, "index"])->withoutMiddleware(["admin"])->name("profile");
        Route::post("/profile", [ProfileController::class, "update"])->withoutMiddleware(["admin"])->name("profile.update");
        // password change
        Route::get("/change-password", [ProfileController::class, "password"])->withoutMiddleware(["admin"])->name("password");
        Route::post("/change-password", [ProfileController::class, "passwordUpdate"])->withoutMiddleware(["admin"])->name("password.update");
        // social media
        Route::get("/social-media", [SocialMediaController::class, "index"])->name("social.media");
        Route::post("/social-media", [SocialMediaController::class, "add"])->name("social.media.add");
        Route::get("/social-media/{id}/status", [SocialMediaController::class, "status"])->name("social.media.status");
        Route::delete("/social-media/{id}/delete", [SocialMediaController::class, "delete"])->name("social.media.delete");
        // site menu
        Route::get("/menus/header", [MenuController::class, "header"])->name("menus.header");
        Route::post("/menus/header", [MenuController::class, "headerUpdate"])->name("menus.header.update");
        Route::get("/menus/footer", [MenuController::class, "footer"])->name("menus.footer");
        Route::post("/menus/footer", [MenuController::class, "footerUpdate"])->name("menus.footer.update");
    });
});

// Storage streaming fallback route (when symlinks are disabled)
Route::get('storage/{path}', function($path) {
    $filePath = storage_path('app/public/' . $path);
    if (!file_exists($filePath)) {
        abort(404);
    }
    
    $file = file_get_contents($filePath);
    $type = mime_content_type($filePath);
    
    return response($file, 200)->header('Content-Type', $type);
})->where('path', '.*');

// Uploads streaming fallback route (for robust cPanel image delivery)
Route::get('uploads/{path}', function($path) {
    $filePath = public_path('uploads/' . $path);
    if (!file_exists($filePath)) {
        abort(404);
    }
    
    $file = file_get_contents($filePath);
    $type = mime_content_type($filePath);
    
    return response($file, 200)->header('Content-Type', $type);
})->where('path', '.*');
