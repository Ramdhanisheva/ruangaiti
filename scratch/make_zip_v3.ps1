# RuangAiTi V3 — cPanel Deploy Package Builder
# Run this script from the project root directory
# Usage: powershell -ExecutionPolicy Bypass -File scratch/make_zip_v3.ps1

$Root        = Split-Path $PSScriptRoot -Parent
$ZipName     = "ruangaiti-v3-deploy.zip"
$ZipPath     = Join-Path $Root $ZipName

if (Test-Path $ZipPath) { Remove-Item $ZipPath -Force }

Write-Host "==> Building V3 deploy package: $ZipName" -ForegroundColor Cyan

# All V3 files to include (relative to project root)
$files = @(
    # ── Migrations ──────────────────────────────────────
    "database/migrations/2026_07_12_090000_create_v3_analytics_tables.php",
    "database/migrations/2026_07_12_090001_extend_media_table.php",
    "database/migrations/2026_07_12_090002_create_page_builder_tables.php",
    "database/migrations/2026_07_12_090003_alter_status_and_create_revisions_table.php",
    "database/migrations/2026_07_12_144603_create_roadmaps_redesign_tables.php",
    "database/migrations/2026_07_12_150000_add_seo_fields_to_pages_table.php",
    "database/migrations/2026_07_12_200000_extend_media_seo_fields.php",
    "database/migrations/2026_07_12_210000_add_icon_to_categories_table.php",
    "database/migrations/2026_07_12_210001_alter_pages_status_to_string.php",

    # ── Seeders ─────────────────────────────────────────
    "database/seeders/V3TestSeeder.php",

    # ── Models ──────────────────────────────────────────
    "app/Models/Page.php",
    "app/Models/PageSection.php",
    "app/Models/PageSectionItem.php",
    "app/Models/Media.php",
    "app/Models/PageView.php",
    "app/Models/LikesFeedback.php",
    "app/Models/SearchLog.php",
    "app/Models/AnalyticsAggregate.php",
    "app/Models/Roadmap.php",
    "app/Models/RoadmapModule.php",
    "app/Models/RoadmapModulePost.php",
    "app/Models/Post.php",
    "app/Models/Category.php",

    # ── Services ────────────────────────────────────────
    "app/Services/AnalyticsService.php",
    "app/Services/MediaService.php",
    "app/Services/PageBuilderService.php",
    "app/Services/ContentImportService.php",

    # ── Controllers ─────────────────────────────────────
    "app/Http/Controllers/Dashboard/AnalyticsController.php",
    "app/Http/Controllers/Dashboard/MediaController.php",
    "app/Http/Controllers/Dashboard/PageController.php",
    "app/Http/Controllers/Dashboard/CategoryController.php",
    "app/Http/Controllers/Analytics/TrackingController.php",

    # ── Requests (if exist) ─────────────────────────────
    "app/Http/Requests/TrackPageViewRequest.php",
    "app/Http/Requests/TrackFeedbackRequest.php",

    # ── View Components ─────────────────────────────────
    "app/View/Components/Frontend/PopularPosts.php",
    "resources/views/components/dashboard/icon-picker.blade.php",

    # ── Component Views ─────────────────────────────────
    "resources/views/components/frontend/popular-posts.blade.php",

    # ── Routes ──────────────────────────────────────────
    "routes/web.php",

    # ── Views: Analytics ────────────────────────────────
    "resources/views/dashboard/analytics/overview.blade.php",
    "resources/views/dashboard/analytics/audience.blade.php",
    "resources/views/dashboard/analytics/content.blade.php",

    # ── Views: Media Library ─────────────────────────────────────
    "resources/views/dashboard/media/index.blade.php",
    "resources/views/dashboard/media/add.blade.php",

    # ── Views: Pages ────────────────────────────────────
    "resources/views/dashboard/page/index.blade.php",
    "resources/views/dashboard/page/add.blade.php",
    "resources/views/dashboard/page/edit.blade.php",

    # ── Views: Shared Layout ────────────────────────────
    "resources/views/dashboard/inc/sidebar.blade.php",
    "resources/views/dashboard/roadmap/index.blade.php",
    "resources/views/dashboard/roadmap/create.blade.php",
    "resources/views/dashboard/roadmap/edit.blade.php",
    "resources/views/dashboard/roadmap/builder.blade.php",
    "resources/views/dashboard/category/add.blade.php",
    "resources/views/dashboard/category/edit.blade.php",

    # ── Frontend JS ─────────────────────────────────────
    "assets/frontend/js/tracker.js",
    "assets/frontend/js/like.js",

    # ── Frontend CSS ────────────────────────────────────
    "assets/frontend/css/style.css",
    "assets/frontend/css/roadmap.css",
    "assets/frontend/css/roadmap-detail.css",
    "assets/frontend/css/roadmap-timeline.css",
    "assets/frontend/css/roadmap-responsive.css",

    # ── Frontend Views ──────────────────────────────────
    "resources/views/frontend/post/index.blade.php",
    "resources/views/frontend/category/index.blade.php",
    "resources/views/frontend/tag/index.blade.php",
    "resources/views/frontend/search/index.blade.php",
    "resources/views/frontend/roadmap/show.blade.php",
    "resources/views/frontend/user/list.blade.php",
    "resources/views/frontend/page/index.blade.php",

    # ── Deploy Helper ────────────────────────────────────
    "public/deploy-v3.php",

    # ── Frontend Controllers ─────────────────────────────
    "app/Http/Controllers/Frontend/PostController.php",
    "app/Http/Controllers/Frontend/TagController.php",
    "app/Http/Controllers/Frontend/PageController.php",

    # ── Dashboard CSS ───────────────────────────────────
    "assets/dashboard/css/analytics/analytics.css",
    "assets/dashboard/css/media/media-library.css",
    "assets/dashboard/css/pages/page-builder.css",

    # ── Dashboard JS ────────────────────────────────────
    "assets/dashboard/js/analytics.js",
    "assets/dashboard/js/media-library.js",
    "assets/dashboard/js/page-builder.js",

    # ── Project Rules ─────────────────────────────────────
    ".agents/AGENTS.md",

    # ── V3 Post Importer, Icon Picker Stacks & Morph Maps ──
    "app/Providers/AppServiceProvider.php",
    "app/Http/Controllers/Dashboard/PostController.php",
    "resources/views/dashboard/post/add.blade.php",
    "resources/views/dashboard/post/edit.blade.php",
    "resources/views/dashboard/master.blade.php",

    # ── Category Frontend Icon Rendering ──
    "resources/views/frontend/home/inc/category.blade.php",
    "resources/views/components/frontend/sidebar-category.blade.php",
    "resources/views/frontend/master.blade.php",

    # ── SVG Icon Resolver & Heart SVG Icon ──
    "app/View/Components/Icon.php",
    "assets/frontend/icons/heart.svg",

    # ── Author Stats Enhancement ──
    "app/Models/User.php",
    "resources/views/frontend/user/inc/author.blade.php",

    # ── Author Card Fix (embedded CSS bypass) + CSS cache-bust ──
    "resources/views/frontend/post/inc/author.blade.php",

    # ── Setting Profile View ──
    "resources/views/dashboard/setting/profile.blade.php",

    # ── Multi Page Chapters Feature ──
    "database/migrations/2026_07_14_210002_create_post_chapters_table.php",
    "database/migrations/2026_07_14_210003_make_post_chapters_title_nullable.php",
    "app/Models/PostChapter.php",
    "app/Http/Controllers/Dashboard/PostChapterController.php"
)

$errors   = @()
$included = @()

Add-Type -AssemblyName System.IO.Compression.FileSystem
$zip = [System.IO.Compression.ZipFile]::Open($ZipPath, 'Create')

foreach ($relPath in $files) {
    $fullPath = Join-Path $Root $relPath
    $normalizedRel = $relPath -replace '/', '\'

    if (Test-Path $fullPath) {
        [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile(
            $zip, $fullPath, ($relPath -replace '\\', '/'), [System.IO.Compression.CompressionLevel]::Optimal
        ) | Out-Null
        $included += $relPath
        Write-Host "  [OK] $relPath" -ForegroundColor Green
    } else {
        $errors += $relPath
        Write-Host "  [SKIP] $relPath (not found)" -ForegroundColor Yellow
    }
}

$zip.Dispose()

Write-Host ""
Write-Host "============================================" -ForegroundColor Cyan
Write-Host "  Package: $ZipPath"
Write-Host "  Included: $($included.Count) files"

if ($errors.Count -gt 0) {
    Write-Host "  Skipped:  $($errors.Count) files (not found)" -ForegroundColor Yellow
    $errors | ForEach-Object { Write-Host "    - $_" -ForegroundColor Yellow }
}

Write-Host ""
Write-Host "==> DEPLOY INSTRUCTIONS FOR cPANEL:" -ForegroundColor Magenta
Write-Host "  1. Upload '$ZipName' to your cPanel File Manager (project root)"
Write-Host "  2. Extract it in-place (it preserves full directory structure)"
Write-Host "  3. Open cPanel Terminal or SSH, then run:"
Write-Host "       php artisan migrate --force"
Write-Host "  4. Run: php artisan config:clear && php artisan view:clear && php artisan cache:clear"
Write-Host "  5. Access /dashboard/analytics/overview to verify Analytics"
Write-Host "  6. Access /dashboard/media to verify unified Media Library"
Write-Host "  7. Access /dashboard/pages-builder to verify Page Builder"
Write-Host ""
Write-Host "==> DONE!" -ForegroundColor Green
