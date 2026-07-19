$root = 'C:\Users\ramdh\Documents\blog\blog'
$zip  = 'C:\Users\ramdh\Documents\blog\blog\ruangaiti-hotfix-ui.zip'

if (Test-Path $zip) { Remove-Item $zip -Force }

Add-Type -AssemblyName System.IO.Compression.FileSystem
$z = [System.IO.Compression.ZipFile]::Open($zip, 'Create')

$files = @(
    # Fix DB not found error (homepage 500)
    'app/View/Components/Frontend/PopularPosts.php',

    # Popular Posts - UI redesign (no emoji, clean tabs)
    'resources/views/components/frontend/popular-posts.blade.php',

    # Engagement section CSS upgrade + corruption fix
    'assets/frontend/css/style.css',

    # Post navigation symmetric cards
    'resources/views/frontend/post/index.blade.php',
    'resources/views/frontend/page/index.blade.php',

    # Public Roadmap details & responsiveness
    'resources/views/frontend/roadmap/show.blade.php',
    'assets/frontend/css/roadmap.css',
    'assets/frontend/css/roadmap-detail.css',
    'assets/frontend/css/roadmap-timeline.css',
    'assets/frontend/css/roadmap-responsive.css',

    # Dashboard Bootstrap 4 gap fixes & UI polish
    'resources/views/dashboard/media/index.blade.php',
    'resources/views/dashboard/media/add.blade.php',
    'resources/views/dashboard/page/index.blade.php',
    'resources/views/dashboard/page/add.blade.php',
    'resources/views/dashboard/page/edit.blade.php',
    'resources/views/dashboard/roadmap/index.blade.php',
    'resources/views/dashboard/roadmap/create.blade.php',
    'resources/views/dashboard/roadmap/edit.blade.php',
    'resources/views/dashboard/roadmap/builder.blade.php',
    'resources/views/dashboard/category/add.blade.php',
    'resources/views/dashboard/category/edit.blade.php',
    'resources/views/dashboard/inc/sidebar.blade.php',
    'resources/views/components/dashboard/icon-picker.blade.php',
    'assets/dashboard/css/media/media-library.css',
    'routes/web.php',
    
    # Controllers & Services
    'app/Http/Controllers/Dashboard/PageController.php',
    'app/Http/Controllers/Dashboard/CategoryController.php',
    'app/Http/Controllers/Frontend/PageController.php',
    'app/Services/AnalyticsService.php',
    'app/Services/ContentImportService.php',
    'assets/frontend/js/like.js',

    # Migrations
    'database/migrations/2026_07_12_210000_add_icon_to_categories_table.php',
    'database/migrations/2026_07_12_210001_alter_pages_status_to_string.php',

    # Seeder fixed columns
    'database/seeders/V3TestSeeder.php',

    # Deploy helper
    'public/deploy-v3.php',

    # V3 Post Importer, Icon Picker Stacks & Morph Maps
    'app/Providers/AppServiceProvider.php',
    'app/Http/Controllers/Dashboard/PostController.php',
    'resources/views/dashboard/post/add.blade.php',
    'resources/views/dashboard/post/edit.blade.php',
    'resources/views/dashboard/master.blade.php',

    # Category Frontend Icon Rendering
    'resources/views/frontend/home/inc/category.blade.php',
    'resources/views/components/frontend/sidebar-category.blade.php',
    'resources/views/frontend/master.blade.php',

    # SVG Icon Resolver & Heart SVG Icon
    'app/View/Components/Icon.php',
    'assets/frontend/icons/heart.svg',

    # Author Stats Enhancement
    'app/Models/User.php',
    'resources/views/frontend/user/inc/author.blade.php',

    # Setting Profile View
    'resources/views/dashboard/setting/profile.blade.php',

    # Multi Page Chapters Feature
    'database/migrations/2026_07_14_210002_create_post_chapters_table.php',
    'database/migrations/2026_07_14_210003_make_post_chapters_title_nullable.php',
    'app/Models/PostChapter.php',
    'app/Models/Post.php',
    'app/Http/Controllers/Dashboard/PostChapterController.php'
)

foreach ($f in $files) {
    $full = Join-Path $root $f
    [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($z, $full, $f, [System.IO.Compression.CompressionLevel]::Optimal) | Out-Null
    Write-Host "[OK] $f"
}

$z.Dispose()
Write-Host ""
Write-Host "DONE => $zip" -ForegroundColor Green
Write-Host "Extract ke root ruangaiti.blog/ lalu akses /deploy-v3.php?key=ruangaiti2026" -ForegroundColor Cyan
