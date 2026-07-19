<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Roadmap;
use App\Models\RoadmapModule;
use App\Models\RoadmapModulePost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RoadmapSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Get or create a default category for the roadmaps
        $category = Category::where('status', true)->first();
        if (!$category) {
            $category = Category::create([
                'title' => 'Teknologi',
                'slug' => 'teknologi',
                'description' => 'Artikel seputar teknologi dan pemrograman.',
                'status' => true,
            ]);
        }

        // 2. Query existing posts to link to modules
        $posts = Post::where('status', true)->orderBy('id', 'asc')->get();
        $postCount = $posts->count();

        // 3. Create Cyber Security Roadmap
        $cyberRoadmap = Roadmap::create([
            'title' => 'Cyber Security Specialist',
            'slug' => 'cyber-security-specialist',
            'icon' => 'fas fa-shield-alt',
            'cover' => null,
            'description' => 'Jalur belajar terstruktur untuk menguasai dasar-dasar keamanan siber, mulai dari konsep jaringan, keamanan aplikasi web (OWASP Top 10), hingga latihan penetration testing praktis.',
            'difficulty' => 'Beginner',
            'category_id' => $category->id,
            'status' => 'Published',
            'sort_order' => 1,
            'prerequisites' => "Memahami dasar pengoperasian komputer\nTertarik dengan keamanan sistem dan jaringan\nMemiliki pemahaman dasar tentang HTML & CSS",
            'learning_outcomes' => "Memahami konsep CIA Triad dalam keamanan informasi\nMampu menganalisis protokol jaringan menggunakan Wireshark\nMengidentifikasi dan menguji kerentanan OWASP Top 10\nMampu menggunakan Burp Suite untuk analisis request HTTP",
        ]);

        // Create Modules for Cyber Security
        $module1 = RoadmapModule::create([
            'roadmap_id' => $cyberRoadmap->id,
            'title' => 'Jaringan & Protokol HTTP',
            'subtitle' => 'Dasar komunikasi data dan internet',
            'description' => 'Sebelum mempelajari cara meretas atau mengamankan sistem, kita wajib memahami bagaimana data dikirimkan melalui internet dan cara kerja protokol web.',
            'icon' => 'fas fa-network-wired',
            'color' => '#10b981',
            'sort_order' => 0,
        ]);

        $module2 = RoadmapModule::create([
            'roadmap_id' => $cyberRoadmap->id,
            'title' => 'Keamanan Web & Pentesting',
            'subtitle' => 'Analisis kerentanan aplikasi web',
            'description' => 'Mempelajari celah keamanan paling kritis pada website modern seperti SQL Injection, XSS, dan cara mengeksploitasinya secara etis.',
            'icon' => 'fas fa-globe',
            'color' => '#3b82f6',
            'sort_order' => 1,
        ]);

        $module3 = RoadmapModule::create([
            'roadmap_id' => $cyberRoadmap->id,
            'title' => 'Praktek Eksploitasi & CTF',
            'subtitle' => 'Latihan praktis lab keamanan siber',
            'description' => 'Asah keahlian analisis Anda dengan menyelesaikan tantangan keamanan praktis bergaya Capture The Flag.',
            'icon' => 'fas fa-trophy',
            'color' => '#ef4444',
            'sort_order' => 2,
        ]);

        // Link posts to Cyber Security Modules if we have posts
        if ($postCount > 0) {
            // Assign first 2 posts to Module 1
            for ($i = 0; $i < min(2, $postCount); $i++) {
                RoadmapModulePost::create([
                    'roadmap_module_id' => $module1->id,
                    'post_id' => $posts[$i]->id,
                    'sort_order' => $i,
                ]);
            }

            // Assign next 2 posts to Module 2
            if ($postCount > 2) {
                for ($i = 2; $i < min(4, $postCount); $i++) {
                    RoadmapModulePost::create([
                        'roadmap_module_id' => $module2->id,
                        'post_id' => $posts[$i]->id,
                        'sort_order' => $i - 2,
                    ]);
                }
            }

            // Assign next 1 post to Module 3
            if ($postCount > 4) {
                RoadmapModulePost::create([
                    'roadmap_module_id' => $module3->id,
                    'post_id' => $posts[4]->id,
                    'sort_order' => 0,
                ]);
            }
        }

        // 4. Create Linux Administrator Roadmap
        $linuxRoadmap = Roadmap::create([
            'title' => 'Linux System Administrator',
            'slug' => 'linux-system-administrator',
            'icon' => 'fab fa-linux',
            'cover' => null,
            'description' => 'Panduan lengkap mempelajari administrasi server Linux, manajemen user, konfigurasi web server, firewall, serta otomasi tugas menggunakan shell scripting.',
            'difficulty' => 'Intermediate',
            'category_id' => $category->id,
            'status' => 'Published',
            'sort_order' => 2,
            'prerequisites' => "Sudah mengenal sistem operasi secara umum\nMengetahui cara membuka aplikasi terminal / command line",
            'learning_outcomes' => "Menguasai perintah dasar navigasi file Linux\nMampu mengkonfigurasi hak akses file (chmod / chown)\nMampu membangun web server Apache / Nginx di Ubuntu Server\nMengotomatiskan backup file menggunakan shell script sederhana",
        ]);

        // Create Modules for Linux
        $linuxModule1 = RoadmapModule::create([
            'roadmap_id' => $linuxRoadmap->id,
            'title' => 'Navigasi Shell & Command Line',
            'subtitle' => 'Bekerja efektif di dalam terminal',
            'description' => 'Langkah pertama menguasai Linux adalah bersahabat dengan shell terminal dan memahami perintah dasar sistem file.',
            'icon' => 'fas fa-terminal',
            'color' => '#f59e0b',
            'sort_order' => 0,
        ]);

        $linuxModule2 = RoadmapModule::create([
            'roadmap_id' => $linuxRoadmap->id,
            'title' => 'Administrasi & Keamanan Server',
            'subtitle' => 'Konfigurasi web server dan pengamanan server',
            'description' => 'Bagaimana melakukan instalasi service, mengatur firewall, dan mengelola hak akses file server agar aman.',
            'icon' => 'fas fa-server',
            'color' => '#8b5cf6',
            'sort_order' => 1,
        ]);

        // Link posts to Linux Modules
        if ($postCount > 0) {
            // Assign first post to Linux Module 1
            RoadmapModulePost::create([
                'roadmap_module_id' => $linuxModule1->id,
                'post_id' => $posts[0]->id,
                'sort_order' => 0,
            ]);

            // Assign second post to Linux Module 2 if available
            if ($postCount > 1) {
                RoadmapModulePost::create([
                    'roadmap_module_id' => $linuxModule2->id,
                    'post_id' => $posts[1]->id,
                    'sort_order' => 0,
                ]);
            }
        }
    }
}
