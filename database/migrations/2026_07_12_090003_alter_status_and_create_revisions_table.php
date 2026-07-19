<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * V3: Add published_at to posts & roadmaps (V1/V2 status columns are NOT changed).
     * Add template + published_at to pages, create content_revisions and roadmap_relations tables.
     */
    public function up(): void
    {
        // Add published_at to posts (V1 — do NOT change status type, only addColumn)
        if (!Schema::hasColumn('posts', 'published_at')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->timestamp('published_at')->nullable()->after('status');
            });
        }

        // Add published_at to roadmaps (V2 — status is already string, only add column)
        if (!Schema::hasColumn('roadmaps', 'published_at')) {
            Schema::table('roadmaps', function (Blueprint $table) {
                $table->timestamp('published_at')->nullable()->after('status');
            });
        }

        // Add template + published_at to pages (V1 pages table)
        Schema::table('pages', function (Blueprint $table) {
            if (!Schema::hasColumn('pages', 'template')) {
                $table->string('template', 50)->default('default')->after('content');
            }
            if (!Schema::hasColumn('pages', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('status');
            }
        });

        // Create content_revisions table
        if (!Schema::hasTable('content_revisions')) {
            Schema::create('content_revisions', function (Blueprint $table) {
                $table->id();
                $table->string('revisable_type', 100);
                $table->unsignedBigInteger('revisable_id');
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->longText('content_data');
                $table->timestamp('created_at')->useCurrent();

                $table->index(['revisable_type', 'revisable_id']);
            });
        }

        // Create roadmap_relations table
        if (!Schema::hasTable('roadmap_relations')) {
            Schema::create('roadmap_relations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('roadmap_id')->constrained('roadmaps')->cascadeOnDelete();
                $table->foreignId('related_roadmap_id')->constrained('roadmaps')->cascadeOnDelete();
                $table->string('relation_type', 50)->default('Related');
                $table->timestamps();

                $table->unique(['roadmap_id', 'related_roadmap_id', 'relation_type'], 'roadmap_relation_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('roadmap_relations');
        Schema::dropIfExists('content_revisions');

        Schema::table('pages', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('pages', 'template'))      $cols[] = 'template';
            if (Schema::hasColumn('pages', 'published_at'))  $cols[] = 'published_at';
            if (!empty($cols)) $table->dropColumn($cols);
        });

        if (Schema::hasColumn('roadmaps', 'published_at')) {
            Schema::table('roadmaps', function (Blueprint $table) {
                $table->dropColumn('published_at');
            });
        }

        if (Schema::hasColumn('posts', 'published_at')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->dropColumn('published_at');
            });
        }
    }
};
