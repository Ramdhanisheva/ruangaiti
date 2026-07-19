<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to add SEO fields to pages table.
     */
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            if (!Schema::hasColumn('pages', 'seo_title')) {
                $table->string('seo_title', 255)->nullable()->after('template');
            }
            if (!Schema::hasColumn('pages', 'seo_description')) {
                $table->text('seo_description')->nullable()->after('seo_title');
            }
            if (!Schema::hasColumn('pages', 'json_ld')) {
                $table->text('json_ld')->nullable()->after('seo_description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('pages', 'seo_title'))       $cols[] = 'seo_title';
            if (Schema::hasColumn('pages', 'seo_description')) $cols[] = 'seo_description';
            if (Schema::hasColumn('pages', 'json_ld'))         $cols[] = 'json_ld';
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
