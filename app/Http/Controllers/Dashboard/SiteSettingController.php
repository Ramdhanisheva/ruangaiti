<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

use App\Http\Controllers\Frontend\SitemapController;

class SiteSettingController extends Controller
{
    public function index() {
        $sitesettings = SiteSetting::first();
        return view("dashboard.setting.site", compact("sitesettings"));
    }

    public function update(Request $request) {
        $validated = $request->validate([
            "site_title" => ["required", "string", "min:2", "max:255"],
            "tagline" => ["required", "string", "min:2", "max:255"],
            "description" => ["required", "string", "min:2", "max:300"],
            "copyright_text" => ["required", "string", "min:2", "max:300"],
            "enable_registration" => ["nullable", "integer"],
            "logo_dark" => ["nullable", "image"],
            "logo_light" => ["nullable", "image"],
        ]);
        $sitesettings = SiteSetting::first();
        $sitesettings->site_title = $validated["site_title"];
        $sitesettings->tagline = $validated["tagline"];
        $sitesettings->description = $validated["description"];
        $sitesettings->copyright_text = $validated["copyright_text"];
        $sitesettings->enable_registration = Arr::has($validated, "enable_registration") ? "1" : "0";
        if (Arr::has($validated, "logo_dark")) {
            $image = $request->file("logo_dark");
            $imageName = "logo_dark_".Str::random(5).".".strtolower($image->getClientOriginalExtension());
            $image->move(public_path("uploads/logo"), $imageName);
            if (File::exists(public_path("uploads/logo/".$sitesettings->logo_dark))) {
                File::delete(public_path("uploads/logo/".$sitesettings->logo_dark));
            }
            $sitesettings->logo_dark = $imageName;
        }
        if (Arr::has($validated, "logo_light")) {
            $image = $request->file("logo_light");
            $imageName = "logo_light_".Str::random(5).".".strtolower($image->getClientOriginalExtension());
            $image->move(public_path("uploads/logo"), $imageName);
            if (File::exists(public_path("uploads/logo/".$sitesettings->logo_light))) {
                File::delete(public_path("uploads/logo/".$sitesettings->logo_light));
            }
            $sitesettings->logo_light = $imageName;
        }
        $sitesettings->save();
        return back()->with("success", "Site Settings updated!");
    }

    public function generateSitemap() {
        try {
            $sitemapController = new SitemapController();

            // 1. Generate Index
            $index = $sitemapController->index();
            $indexContent = str_replace('/index.php', '', $index->getContent());
            file_put_contents(public_path('sitemap.xml'), $indexContent);

            // 2. Generate Posts
            $posts = $sitemapController->posts();
            $postsContent = str_replace('/index.php', '', $posts->getContent());
            file_put_contents(public_path('sitemap-posts.xml'), $postsContent);

            // 3. Generate Pages
            $pages = $sitemapController->pages();
            $pagesContent = str_replace('/index.php', '', $pages->getContent());
            file_put_contents(public_path('sitemap-pages.xml'), $pagesContent);

            // 3b. Generate Roadmaps
            $roadmaps = $sitemapController->roadmaps();
            $roadmapsContent = str_replace('/index.php', '', $roadmaps->getContent());
            file_put_contents(public_path('sitemap-roadmaps.xml'), $roadmapsContent);

            // 4. Generate Taxonomies
            $taxonomies = $sitemapController->taxonomies();
            $taxonomiesContent = str_replace('/index.php', '', $taxonomies->getContent());
            file_put_contents(public_path('sitemap-taxonomies.xml'), $taxonomiesContent);

            // 5. Generate Authors
            $authors = $sitemapController->authors();
            $authorsContent = str_replace('/index.php', '', $authors->getContent());
            file_put_contents(public_path('sitemap-authors.xml'), $authorsContent);

            return back()->with("success", "Sitemap successfully updated!");
        } catch (\Exception $e) {
            return back()->withErrors("Failed to generate sitemap: " . $e->getMessage());
        }
    }
}
