<?php
define('LARAVEL_START', microtime(true));

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

// Bind request to container to prevent UrlGenerator exception and set correct host
$request = Illuminate\Http\Request::capture();
$appUrl = 'https://ruangaiti.blog';
$parsedUrl = parse_url($appUrl);
if (isset($parsedUrl['host'])) {
    $request->headers->set('HOST', $parsedUrl['host']);
    $request->server->set('SERVER_NAME', $parsedUrl['host']);
    $request->server->set('HTTP_HOST', $parsedUrl['host']);
    if (isset($parsedUrl['scheme']) && $parsedUrl['scheme'] === 'https') {
        $request->server->set('HTTPS', 'on');
        $request->server->set('SERVER_PORT', 443);
    }
}
$app->instance('request', $request);

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Frontend\SitemapController;

try {
    $sitemapController = new SitemapController();

    // 1. Generate Index
    $index = $sitemapController->index();
    $indexContent = str_replace('/generate_static_sitemaps.php', '', $index->getContent());
    file_put_contents(__DIR__ . '/sitemap.xml', $indexContent);
    echo "Generated: sitemap.xml<br>";

    // 2. Generate Posts
    $posts = $sitemapController->posts();
    $postsContent = str_replace('/generate_static_sitemaps.php', '', $posts->getContent());
    file_put_contents(__DIR__ . '/sitemap-posts.xml', $postsContent);
    echo "Generated: sitemap-posts.xml<br>";

    // 3. Generate Pages
    $pages = $sitemapController->pages();
    $pagesContent = str_replace('/generate_static_sitemaps.php', '', $pages->getContent());
    file_put_contents(__DIR__ . '/sitemap-pages.xml', $pagesContent);
    echo "Generated: sitemap-pages.xml<br>";

    // 4. Generate Taxonomies
    $taxonomies = $sitemapController->taxonomies();
    $taxonomiesContent = str_replace('/generate_static_sitemaps.php', '', $taxonomies->getContent());
    file_put_contents(__DIR__ . '/sitemap-taxonomies.xml', $taxonomiesContent);
    echo "Generated: sitemap-taxonomies.xml<br>";

    // 4b. Generate Roadmaps
    $roadmaps = $sitemapController->roadmaps();
    $roadmapsContent = str_replace('/generate_static_sitemaps.php', '', $roadmaps->getContent());
    file_put_contents(__DIR__ . '/sitemap-roadmaps.xml', $roadmapsContent);
    echo "Generated: sitemap-roadmaps.xml<br>";

    // 5. Generate Authors
    $authors = $sitemapController->authors();
    $authorsContent = str_replace('/generate_static_sitemaps.php', '', $authors->getContent());
    file_put_contents(__DIR__ . '/sitemap-authors.xml', $authorsContent);
    echo "Generated: sitemap-authors.xml<br>";

    echo "<br><b>Success! All sitemaps generated as static XML files.</b>";
} catch (\Exception $e) {
    echo "Error generating sitemaps: " . $e->getMessage();
}
