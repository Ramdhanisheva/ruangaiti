<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * V3 Analytics Tables
     * Creates: page_views, likes_feedback, search_logs, analytics_aggregates
     */
    public function up(): void
    {
        // ── 1. Page Views ──────────────────────────────────────────────────────
        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->string('visitor_id', 64)->index(); // UUID stored in LocalStorage
            $table->string('session_id', 64)->index();
            $table->string('path', 500);
            $table->string('viewable_type', 100)->nullable(); // App\Models\Post, App\Models\Roadmap, etc.
            $table->unsignedBigInteger('viewable_id')->nullable();
            $table->string('ip_hash', 64)->nullable();        // SHA-256 hashed IP
            $table->string('device', 20)->default('desktop'); // desktop | mobile | tablet
            $table->string('browser', 50)->nullable();
            $table->string('os', 50)->nullable();
            $table->string('referrer', 500)->nullable();
            $table->string('referrer_source', 50)->nullable(); // google | bing | direct | social | other
            $table->unsignedSmallInteger('read_time')->default(0); // seconds
            $table->timestamp('created_at')->useCurrent();

            $table->index(['viewable_type', 'viewable_id']);
            $table->index('created_at');
            $table->index('referrer_source');
        });

        // ── 2. Likes & Helpful Feedback ────────────────────────────────────────
        Schema::create('likes_feedback', function (Blueprint $table) {
            $table->id();
            $table->string('likeable_type', 100);
            $table->unsignedBigInteger('likeable_id');
            $table->enum('type', ['like', 'helpful_yes', 'helpful_no']);
            $table->string('visitor_id', 64)->index();
            $table->string('ip_hash', 64)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['likeable_type', 'likeable_id']);
            // Prevent spam: one action per visitor per content per day (enforced in Service)
        });

        // ── 3. Search Logs ─────────────────────────────────────────────────────
        Schema::create('search_logs', function (Blueprint $table) {
            $table->id();
            $table->string('query', 300);
            $table->string('search_type', 30)->default('global'); // global | article | roadmap
            $table->unsignedSmallInteger('results_count')->default(0);
            $table->unsignedTinyInteger('page')->default(1);
            $table->unsignedSmallInteger('duration_ms')->nullable();
            $table->string('visitor_id', 64)->nullable()->index();
            $table->string('ip_hash', 64)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('query');
            $table->index('created_at');
        });

        // ── 4. Analytics Aggregates ────────────────────────────────────────────
        // Pre-computed daily stats per entity to avoid scanning millions of rows.
        Schema::create('analytics_aggregates', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index();
            $table->string('entity_type', 100); // Post | Roadmap | Page | Category | Tag | global
            $table->unsignedBigInteger('entity_id')->default(0); // 0 = site-wide
            $table->unsignedBigInteger('views')->default(0);
            $table->unsignedBigInteger('unique_views')->default(0);
            $table->unsignedBigInteger('likes')->default(0);
            $table->unsignedBigInteger('helpful_yes')->default(0);
            $table->unsignedBigInteger('helpful_no')->default(0);
            $table->unsignedSmallInteger('avg_read_time')->default(0); // seconds
            $table->unsignedBigInteger('bookmarks')->default(0);
            $table->unsignedBigInteger('searches')->default(0);
            $table->timestamps();

            $table->unique(['date', 'entity_type', 'entity_id']);
            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_aggregates');
        Schema::dropIfExists('search_logs');
        Schema::dropIfExists('likes_feedback');
        Schema::dropIfExists('page_views');
    }
};
