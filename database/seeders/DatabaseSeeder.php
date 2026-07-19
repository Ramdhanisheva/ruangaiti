<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Post;
use App\Models\Comment;
use App\Models\SiteSetting;
use App\Models\Menu;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Users
        $admin = User::create([
            "name" => "Sarah Connor",
            "username" => "admin",
            "email" => "admin@admin.com",
            "role" => 3,
            "profile" => "default.webp",
            "about" => "Staff Software Engineer, designer, and tech enthusiast. Writer at Oredoo. Formerly at Stripe and Linear.",
            "password" => "admin",
            "facebook" => "https://facebook.com",
            "twitter" => "https://twitter.com",
            "instagram" => "https://instagram.com",
            "linkedin" => "https://linkedin.com",
            "status" => true,
        ]);

        $author = User::create([
            "name" => "Alex Mercer",
            "username" => "alexmercer",
            "email" => "alex@example.com",
            "role" => 2,
            "profile" => "default.webp",
            "about" => "Security Researcher and Cryptographer. Loves analyzing network traffic and auditing codebases.",
            "password" => "password",
            "twitter" => "https://twitter.com",
            "status" => true,
        ]);

        // 2. Create Categories
        $categoriesData = [
            [
                "title" => "Technology",
                "slug" => "technology",
                "description" => "Explorations into the latest tech trends, AI advancements, edge networks, and hardware developments.",
                "image" => "cat_tech.webp",
                "status" => true,
            ],
            [
                "title" => "Programming",
                "slug" => "programming",
                "description" => "In-depth code walkthroughs, design patterns, framework reviews, and software architecture articles.",
                "image" => "cat_prog.webp",
                "status" => true,
            ],
            [
                "title" => "Cyber Security",
                "slug" => "cyber-security",
                "description" => "Essential guides on defensive security, threat modeling, network forensics, and web application patching.",
                "image" => "cat_sec.webp",
                "status" => true,
            ],
            [
                "title" => "Design System",
                "slug" => "design-system",
                "description" => "Modern UI/UX practices, CSS layouts, design tokens, micro-interactions, and accessibility standards.",
                "image" => "cat_design.webp",
                "status" => true,
            ],
        ];

        $categories = [];
        foreach ($categoriesData as $c) {
            $categories[] = Category::create($c);
        }

        // 3. Create Tags
        $tagsData = ["Laravel", "PHP", "CSS", "Vite", "CyberSecurity", "XSS", "UI/UX", "Accessibility", "Performance"];
        $tags = [];
        foreach ($tagsData as $t) {
            $tags[] = Tag::create(["name" => $t]);
        }

        // 4. Create Site Settings & Menus
        SiteSetting::create([
            "site_title" => "RuangAiTi",
            "tagline" => "Blog IT dan Teknologi Indonesia",
            "description" => "Platform media teknologi informasi terdepan di Indonesia yang membahas pemrograman, sistem keamanan cyber, cloud, dan tren teknologi terkini.",
            "logo_dark" => "logo_dark.png",
            "logo_light" => "logo_light.png",
            "copyright_text" => "© 2026 RuangAiTi. All Rights Reserved.",
            "enable_registration" => "1",
        ]);

        Menu::create([
            "header_menu" => json_encode([
                ["href" => "/", "icon" => "", "text" => "Home", "tooltip" => "", "children" => []],
                ["href" => "/category/programming", "icon" => "", "text" => "Programming", "tooltip" => "", "children" => []],
                ["href" => "/category/cyber-security", "icon" => "", "text" => "Security", "tooltip" => "", "children" => []],
                ["href" => "/category/technology", "icon" => "", "text" => "Technology", "tooltip" => "", "children" => []],
            ]),
            "footer_menu" => json_encode([
                ["href" => "/", "icon" => "", "text" => "Home", "tooltip" => "", "children" => []],
                ["href" => "/category/programming", "icon" => "", "text" => "Programming", "tooltip" => "", "children" => []],
                ["href" => "/category/cyber-security", "icon" => "", "text" => "Security", "tooltip" => "", "children" => []],
                ["href" => "/category/technology", "icon" => "", "text" => "Technology", "tooltip" => "", "children" => []],
            ]),
        ]);

        // Helper to download mock image or create color-block fallback
        $downloadImage = function ($filename, $searchQuery) {
            $destDir = public_path("uploads/post");
            if (!File::isDirectory($destDir)) {
                File::makeDirectory($destDir, 0777, true, true);
            }
            $destPath = $destDir . "/" . $filename;

            // Try loading from web, fallback to local canvas if offline
            try {
                $url = "https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=800&q=80"; // Tech default
                if (str_contains($searchQuery, 'code')) {
                    $url = "https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=800&q=80";
                } elseif (str_contains($searchQuery, 'sec')) {
                    $url = "https://images.unsplash.com/photo-1550751827-4bd374c3f58b?auto=format&fit=crop&w=800&q=80";
                } elseif (str_contains($searchQuery, 'design')) {
                    $url = "https://images.unsplash.com/photo-1507238691740-187a5b1d37b8?auto=format&fit=crop&w=800&q=80";
                }
                
                $ctx = stream_context_create([
                    "http" => ["timeout" => 5]
                ]);
                $imgData = @file_get_contents($url, false, $ctx);
                if ($imgData) {
                    File::put($destPath, $imgData);
                    return;
                }
            } catch (\Exception $e) {
                // Ignore download failure, create GD placeholder
            }

            // GD fallback
            if (extension_loaded('gd')) {
                $im = imagecreatetruecolor(800, 500);
                $bgColors = [
                    [15, 23, 42],   // slate 900
                    [9, 9, 11],     // zinc 950
                    [30, 41, 59],   // slate 800
                    [24, 24, 27],   // zinc 900
                ];
                $bg = $bgColors[rand(0, 3)];
                $color = imagecolorallocate($im, $bg[0], $bg[1], $bg[2]);
                imagefill($im, 0, 0, $color);
                
                $text_color = imagecolorallocate($im, 255, 255, 255);
                imagestring($im, 5, 200, 240, "Antigravity Blog Mockup - " . strtoupper($searchQuery), $text_color);
                
                imagewebp($im, $destPath);
                imagedestroy($im);
            } else {
                // If GD not loaded, write empty file
                File::put($destPath, '');
            }
        };

        // Create Category images if they don't exist
        $destCatDir = public_path("uploads/category");
        if (!File::isDirectory($destCatDir)) {
            File::makeDirectory($destCatDir, 0777, true, true);
        }
        foreach (['cat_tech.webp', 'cat_prog.webp', 'cat_sec.webp', 'cat_design.webp'] as $idx => $catName) {
            $catPath = $destCatDir . "/" . $catName;
            if (extension_loaded('gd')) {
                $im = imagecreatetruecolor(400, 300);
                $bg = imagecolorallocate($im, 79, 70, 229); // Indigo
                imagefill($im, 0, 0, $bg);
                imagewebp($im, $catPath);
                imagedestroy($im);
            } else {
                File::put($catPath, '');
            }
        }

        // 5. Create Posts with high-quality rich content (for Table of Contents)
        $postsData = [
            [
                "user_id" => $admin->id,
                "category_id" => $categories[1]->id, // Programming
                "title" => "Mastering Laravel in 2026: Modern Patterns & Performance",
                "slug" => "mastering-laravel-in-2026-modern-patterns-performance",
                "is_featured" => true,
                "enable_comment" => true,
                "status" => true,
                "thumbnail" => "post1.webp",
                "content" => "
<h2>Introduction to Laravel in 2026</h2>
<p>Laravel has evolved significantly, integrating built-in support for Vite bundling, advanced database features, and streamlined routing configurations. Building robust software requires understanding these capabilities.</p>

<h2>Advanced Database Optimizations</h2>
<p>Modern applications deal with millions of rows of data. To keep performance high, you must implement proper database indices and query strategies.</p>
<h3>Implementing Prepared Statements</h3>
<p>Ensure query performance remains fast and safe from potential security threats by strictly utilizing Laravel's Eloquent ORM or prepared PDO bindings. Here's a clean coding snippet demonstrating modern query logic:</p>
<pre><code class=\"language-php\">// app/Http/Controllers/PostController.php
public function show(\$id) {
    return Post::with(['category', 'user'])
        ->where('status', true)
        ->findOrFail(\$id);
}</code></pre>

<h2>Vite Compilation & Asset Pipelines</h2>
<p>Optimizing asset packaging ensures fast initial page rendering. Let's analyze how to leverage the default asset pipelines to load style modifications cleanly without bundling unnecessary overhead.</p>

<h2>Conclusion</h2>
<p>Laravel remains a powerhouse framework. By combining refined architectures with robust UI layers, you can build software that stands out in visual fidelity and loading speed.</p>
                ",
            ],
            [
                "user_id" => $admin->id,
                "category_id" => $categories[0]->id, // Technology
                "title" => "The Rise of Edge Computing and Serverless Architecture",
                "slug" => "the-rise-of-edge-computing-and-serverless-architecture",
                "is_featured" => true,
                "enable_comment" => true,
                "status" => true,
                "thumbnail" => "post2.webp",
                "content" => "
<h2>What is Edge Computing?</h2>
<p>Edge computing brings computation and data storage closer to the sources of data. This reduces latency and improves site speed. Users from around the globe experience fast loading speeds.</p>

<h2>How Serverless Fits In</h2>
<p>With serverless technology, developer focus moves entirely to writing logic without dealing with infrastructure provisioning. This increases developer velocity and decreases running costs.</p>

<h2>Conclusion</h2>
<p>Edge and serverless will define web architecture in 2026. Leveraging lightweight systems and fast CDNs is critical to achieving high Lighthouse performance scores.</p>
                ",
            ],
            [
                "user_id" => $author->id,
                "category_id" => $categories[2]->id, // Security
                "title" => "Defending Web Applications Against Cross-Site Scripting (XSS)",
                "slug" => "defending-web-applications-against-cross-site-scripting-xss",
                "is_featured" => true,
                "enable_comment" => true,
                "status" => true,
                "thumbnail" => "post3.webp",
                "content" => "
<h2>Understanding XSS in Modern Frontends</h2>
<p>Cross-Site Scripting (XSS) is one of the most prominent web vulnerabilities. Attackers inject malicious scripts into trusted websites, putting user sessions and cookies at risk.</p>

<h2>Core Prevention Strategies</h2>
<p>Protecting applications requires multi-layered defensive frameworks. You cannot rely on a single guard.</p>
<h3>Escaping Output & Sanitizing Input</h3>
<p>Always double escape user-provided fields in PHP and Blade using standard triple braces <code>{{{ \$input }}}</code> or PHP's htmlspecialchars. In JavaScript, use <code>textContent</code> instead of <code>innerHTML</code> to prevent arbitrary script executions.</p>

<h2>Setting CSP Headers</h2>
<p>A Content Security Policy (CSP) restricts the sources of scripts and resources allowed to load on your domain. Here is a configuration snippet:</p>
<pre><code class=\"language-javascript\">// Content-Security-Policy header format
Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline';</code></pre>

<h2>Summary</h2>
<p>A secure web application is the baseline of professional systems. Conduct regular audits and implement strong sanitization rules.</p>
                ",
            ],
            [
                "user_id" => $admin->id,
                "category_id" => $categories[3]->id, // Design
                "title" => "Designing Premium Interfaces: Spacing, Typography, and Whitespace",
                "slug" => "designing-premium-interfaces-spacing-typography-whitespace",
                "is_featured" => false,
                "enable_comment" => true,
                "status" => true,
                "thumbnail" => "post4.webp",
                "content" => "
<h2>The Power of Whitespace</h2>
<p>Whitespace is not empty space; it is a design element that gives visual balance. Startup layouts like Linear and Vercel use extensive padding to structure contents clearly.</p>

<h2>Implementing the 8px Spacing Grid</h2>
<p>An 8px grid system ensures consistency across layout margins, card padding, and button sizes. Always use increments of 8px (8, 16, 24, 32, 40, etc.) for margins.</p>
<pre><code class=\"language-css\">.card {
  padding: var(--space-3); /* 24px */
  margin-bottom: var(--space-4); /* 32px */
  border-radius: var(--radius-card); /* 24px */
}</code></pre>

<h2>Choosing Typography Hierarchy</h2>
<p>Establish clear visual weight. Use 64px for heroic displays, 40px for major headings, and 17px for comfortable, long-form reading body copy.</p>
                ",
            ],
            [
                "user_id" => $author->id,
                "category_id" => $categories[2]->id, // Security
                "title" => "Introduction to Network Forensics & Audits",
                "slug" => "introduction-to-network-forensics-audits",
                "is_featured" => false,
                "enable_comment" => true,
                "status" => true,
                "thumbnail" => "post5.webp",
                "content" => "
<h2>Why Network Forensics Matters</h2>
<p>Security breaches are inevitable. When they happen, forensics teams analyze logs, traffic patterns, and memory dumps to trace malicious entries and patch holes.</p>

<h2>Essential Audits</h2>
<p>Perform monthly penetration tests and network traffic captures. Investigate open ports and secure data transport lines using modern HTTPS encryptions.</p>
                ",
            ],
        ];

        foreach ($postsData as $index => $pData) {
            $pName = $pData["thumbnail"];
            $downloadImage($pName, $pData["slug"]);
            
            $post = Post::create($pData);
            
            // Attach random tags
            $post->tags()->attach([$tags[0]->id, $tags[1]->id, $tags[2]->id]);

            // Add mock comments for post 1
            if ($index === 0) {
                $c1 = Comment::create([
                    "message" => "This article is absolutely phenomenal! The code examples are very clear and helpful. Thank you for putting this together.",
                    "post_id" => $post->id,
                    "user_id" => $author->id,
                    "status" => true,
                ]);

                Comment::create([
                    "message" => "Great point about database indexations! What are your thoughts on cache strategy with Laravel Redis?",
                    "post_id" => $post->id,
                    "parent_id" => $c1->id,
                    "user_id" => $author->id,
                    "status" => true,
                ]);

                Comment::create([
                    "message" => "Thanks Alex! I'll cover Redis and model caching in my next article. Stay tuned!",
                    "post_id" => $post->id,
                    "parent_id" => $c1->id,
                    "user_id" => $admin->id,
                    "status" => true,
                ]);

                Comment::create([
                    "message" => "Clean layout and super helpful tutorials. Thanks Connor!",
                    "post_id" => $post->id,
                    "name" => "Visitor Guest",
                    "email" => "visitor@guest.com",
                    "status" => true,
                ]);
            }
        }
    }
}
