<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create relational page builder tables.
     */
    public function up(): void
    {
        // 1. Page Sections
        Schema::create('page_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
            $table->string('type', 50);          // hero, timeline, faq, values, mission, cta, markdown, etc.
            $table->string('layout_style', 50)->default('default'); // centered, split, minimal, full_width
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status', 50)->default('Published'); // Published | Draft
            $table->timestamps();

            $table->index(['page_id', 'sort_order']);
        });

        // 2. Page Section Items
        Schema::create('page_section_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_section_id')->constrained('page_sections')->cascadeOnDelete();
            $table->string('title', 300)->nullable();
            $table->string('subtitle', 300)->nullable();
            $table->text('content')->nullable();
            $table->string('image', 500)->nullable(); // Media file name/path reference
            $table->string('link', 500)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('meta_data')->nullable(); // Extra variables
            $table->timestamps();

            $table->index(['page_section_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_section_items');
        Schema::dropIfExists('page_sections');
    }
};
