<?php

namespace Database\Seeders;

use App\Models\AnalyticsAggregate;
use App\Models\LikesFeedback;
use App\Models\Media;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\PageSectionItem;
use App\Models\PageView;
use App\Models\Post;
use App\Models\SearchLog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class V3TestSeeder extends Seeder
{
    /**
     * Seed dummy data untuk fitur V3 (Analytics, Media Library, Page Builder).
     * Aman dijalankan di production karena tidak menghapus data existing.
     */
    public function run(): void
    {
        $this->command->info('==> Seeding V3 test data...');

        $admin = User::where('role', 3)->first();
        if (!$admin) {
            $this->command->warn('No admin user found. Create one first.');
            return;
        }

        // ─────────────────────────────────────────────
        // 1. ANALYTICS — page_views
        // Kolom sesuai migration: visitor_id, session_id, path, viewable_type,
        //   viewable_id, ip_hash, device, browser, os, referrer, referrer_source, read_time
        // ─────────────────────────────────────────────
        if (Schema::hasTable('page_views')) {
            $this->command->info('  Seeding page_views...');

            $posts = Post::where('status', true)->take(5)->get();
            $paths = array_merge(
                ['/'],
                $posts->map(fn($p) => '/post/' . $p->slug)->toArray(),
                ['/roadmap', '/category/programming', '/search']
            );

            $referrers = [
                null, 'https://google.com', 'https://facebook.com',
                'https://twitter.com', null, null,
            ];
            $referrerSources = ['google', 'social', 'social', 'direct', 'direct', 'other'];
            $devices  = ['desktop', 'desktop', 'desktop', 'mobile', 'tablet'];
            $browsers = ['Chrome', 'Firefox', 'Safari', 'Chrome', 'Edge'];
            $oses     = ['Windows', 'MacOS', 'Linux', 'Android', 'iOS'];

            for ($i = 0; $i < 200; $i++) {
                $daysAgo   = rand(0, 29);
                $refIdx    = array_rand($referrers);
                $visitorId = 'vis_' . substr(md5(rand(1, 50)), 0, 12);

                PageView::create([
                    'visitor_id'      => $visitorId,
                    'session_id'      => 'sess_' . substr(md5(rand(1, 30)), 0, 12),
                    'path'            => $paths[array_rand($paths)],
                    'viewable_type'   => $posts->isNotEmpty() ? 'App\\Models\\Post' : null,
                    'viewable_id'     => $posts->isNotEmpty() ? $posts->random()->id : null,
                    'ip_hash'         => hash('sha256', '192.168.' . rand(1, 255) . '.' . rand(1, 255)),
                    'device'          => $devices[array_rand($devices)],
                    'browser'         => $browsers[array_rand($browsers)],
                    'os'              => $oses[array_rand($oses)],
                    'referrer'        => $referrers[$refIdx],
                    'referrer_source' => $referrerSources[$refIdx],
                    'read_time'       => rand(0, 300),
                    'created_at'      => Carbon::now()->subDays($daysAgo)->subHours(rand(0, 23)),
                ]);
            }

            $this->command->info('  [OK] 200 page views seeded.');
        } else {
            $this->command->warn('  [SKIP] page_views table not found — run migrations first.');
        }

        // ─────────────────────────────────────────────
        // 2. ANALYTICS — likes_feedback
        // Kolom: likeable_type, likeable_id, type, visitor_id, ip_hash
        // ─────────────────────────────────────────────
        if (Schema::hasTable('likes_feedback')) {
            $this->command->info('  Seeding likes_feedback...');

            $posts = Post::where('status', true)->take(5)->get();
            $types = ['like', 'like', 'like', 'helpful_yes', 'helpful_no'];

            foreach ($posts as $post) {
                $likeCount = rand(5, 30);
                for ($i = 0; $i < $likeCount; $i++) {
                    LikesFeedback::create([
                        'likeable_type' => 'post',
                        'likeable_id'   => $post->id,
                        'type'          => $types[array_rand($types)],
                        'visitor_id'    => 'vis_' . substr(md5(rand(1, 999)), 0, 12),
                        'ip_hash'       => hash('sha256', '10.0.' . rand(1, 255) . '.' . rand(1, 255)),
                        'created_at'    => Carbon::now()->subDays(rand(0, 29)),
                    ]);
                }
            }

            $this->command->info('  [OK] Likes/feedback seeded.');
        } else {
            $this->command->warn('  [SKIP] likes_feedback table not found.');
        }

        // ─────────────────────────────────────────────
        // 3. ANALYTICS — search_logs
        // Kolom: query, search_type, results_count, page, visitor_id, ip_hash
        // ─────────────────────────────────────────────
        if (Schema::hasTable('search_logs')) {
            $this->command->info('  Seeding search_logs...');

            $keywords = [
                'laravel', 'php tutorial', 'cara belajar linux',
                'cyber security pemula', 'xss attack', 'bootstrap css',
                'cara deploy cpanel', 'roadmap programmer', 'tips coding',
                'web security 2026',
            ];

            foreach ($keywords as $kw) {
                $count = rand(2, 15);
                for ($i = 0; $i < $count; $i++) {
                    SearchLog::create([
                        'query'         => $kw,
                        'search_type'   => 'global',
                        'results_count' => rand(0, 10),
                        'page'          => 1,
                        'visitor_id'    => 'vis_' . substr(md5(rand(1, 999)), 0, 12),
                        'ip_hash'       => hash('sha256', '10.0.' . rand(1, 255) . '.' . rand(1, 255)),
                        'created_at'    => Carbon::now()->subDays(rand(0, 29)),
                    ]);
                }
            }

            $this->command->info('  [OK] Search logs seeded.');
        } else {
            $this->command->warn('  [SKIP] search_logs table not found.');
        }

        // ─────────────────────────────────────────────
        // 4. ANALYTICS — analytics_aggregates
        // Kolom: date, entity_type, entity_id, views, unique_views, likes,
        //        helpful_yes, helpful_no, avg_read_time, bookmarks, searches
        // ─────────────────────────────────────────────
        if (Schema::hasTable('analytics_aggregates')) {
            $this->command->info('  Seeding analytics_aggregates...');

            for ($day = 0; $day < 30; $day++) {
                $date = Carbon::now()->subDays($day)->toDateString();

                AnalyticsAggregate::updateOrCreate(
                    ['date' => $date, 'entity_type' => 'global', 'entity_id' => 0],
                    [
                        'views'         => rand(50, 500),
                        'unique_views'  => rand(20, 200),
                        'likes'         => rand(5, 80),
                        'helpful_yes'   => rand(3, 50),
                        'helpful_no'    => rand(1, 20),
                        'avg_read_time' => rand(30, 300),
                        'searches'      => rand(10, 100),
                    ]
                );
            }

            $this->command->info('  [OK] 30-day aggregates seeded.');
        } else {
            $this->command->warn('  [SKIP] analytics_aggregates table not found.');
        }

        // ─────────────────────────────────────────────
        // 5. MEDIA — update metadata jika kolom ada
        // ─────────────────────────────────────────────
        if (Schema::hasTable('media') && Schema::hasColumn('media', 'alt')) {
            $this->command->info('  Updating media with V3 metadata...');

            Media::all()->each(function ($m) {
                $updates = [];
                if (empty($m->alt))         $updates['alt']         = 'Media item ' . $m->id;
                if (empty($m->caption))     $updates['caption']     = 'Uploaded via media library';
                if (!empty($updates))       $m->update($updates);
            });

            $this->command->info('  [OK] Media metadata updated.');
        } else {
            $this->command->warn('  [SKIP] media columns not found — run migrations first.');
        }

        // ─────────────────────────────────────────────
        // 6. PAGE BUILDER — sample pages + sections
        // ─────────────────────────────────────────────
        if (Schema::hasTable('page_sections')) {
            $this->command->info('  Seeding Page Builder demo pages...');

            $siteTitle = DB::table('site_settings')->value('site_title') ?? config('app.name');

            // Demo page 1: Landing Page
            $landing = Page::firstOrCreate(
                ['slug' => 'demo-landing-page'],
                [
                    'title'        => 'Demo Landing Page',
                    'content'      => '',
                    'template'     => 'landing',
                    'status'       => 'Draft',
                    'published_at' => null,
                ]
            );

            $hero = PageSection::firstOrCreate(
                ['page_id' => $landing->id, 'type' => 'hero', 'sort_order' => 0],
                ['layout_style' => 'full-width', 'status' => 'Published']
            );
            PageSectionItem::firstOrCreate(
                ['page_section_id' => $hero->id, 'sort_order' => 0],
                [
                    'title'    => 'Selamat Datang di ' . $siteTitle,
                    'subtitle' => 'Platform belajar IT terstruktur untuk semua level',
                    'content'  => 'Mulai belajar dari dasar hingga mahir dengan panduan yang tersusun rapi.',
                    'image'    => null,
                    'link'     => '/roadmap',
                ]
            );

            $features = PageSection::firstOrCreate(
                ['page_id' => $landing->id, 'type' => 'features', 'sort_order' => 1],
                ['layout_style' => 'grid-3', 'status' => 'Published']
            );

            $featureItems = [
                ['title' => 'Artikel Berkualitas',  'subtitle' => '100+ artikel tech',         'content' => 'Ditulis oleh praktisi industri.',      'link' => '/'],
                ['title' => 'Learning Roadmap',     'subtitle' => 'Jalur belajar terstruktur', 'content' => 'Dari pemula hingga mahir step by step.','link' => '/roadmap'],
                ['title' => 'Komunitas Aktif',      'subtitle' => 'Belajar bersama',           'content' => 'Forum diskusi dan komentar aktif.',     'link' => '/members'],
            ];
            foreach ($featureItems as $fi => $fItem) {
                PageSectionItem::firstOrCreate(
                    ['page_section_id' => $features->id, 'sort_order' => $fi],
                    array_merge($fItem, ['image' => null])
                );
            }

            // Demo page 2: About
            $about = Page::firstOrCreate(
                ['slug' => 'about'],
                [
                    'title'        => 'About ' . $siteTitle,
                    'content'      => '<p>' . $siteTitle . ' adalah platform blog IT & teknologi untuk pelajar Indonesia.</p>',
                    'template'     => 'company',
                    'status'       => 'Draft',
                    'published_at' => null,
                ]
            );

            $this->command->info('  [OK] 2 demo pages seeded.');
        } else {
            $this->command->warn('  [SKIP] page_sections table not found — run migrations first.');
        }

        $this->command->info('');
        $this->command->info('==> V3 Test Seeder DONE!');
        $this->command->info('   Verifikasi:');
        $this->command->info('   - /dashboard/analytics/overview');
        $this->command->info('   - /dashboard/media');
        $this->command->info('   - /dashboard/pages-builder');
    }
}
