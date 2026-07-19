<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Extend media table with SEO fields (title, description, original_name).
     * Safe to run on existing data — all columns are nullable with defaults.
     */
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            if (!Schema::hasColumn('media', 'original_name')) {
                $table->string('original_name', 255)->nullable()->after('file_name');
            }
            if (!Schema::hasColumn('media', 'title')) {
                $table->string('title', 255)->nullable()->after('alt');
            }
            if (!Schema::hasColumn('media', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $columns = ['original_name', 'title', 'description'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('media', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
