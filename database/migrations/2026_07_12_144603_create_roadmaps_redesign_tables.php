<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roadmaps', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->string('cover')->nullable();
            $table->text('description')->nullable();
            $table->string('difficulty'); // e.g. Beginner, Intermediate, Advanced
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('status')->default('Draft'); // Draft, Published, Archived
            $table->integer('sort_order')->default(0);
            $table->text('prerequisites')->nullable();
            $table->text('learning_outcomes')->nullable();
            $table->timestamps();
        });

        Schema::create('roadmap_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('roadmap_id')->constrained('roadmaps')->cascadeOnDelete();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->default('#2563eb');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('roadmap_module_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('roadmap_module_id')->constrained('roadmap_modules')->cascadeOnDelete();
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roadmap_module_posts');
        Schema::dropIfExists('roadmap_modules');
        Schema::dropIfExists('roadmaps');
    }
};
