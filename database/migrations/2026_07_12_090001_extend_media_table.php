<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Extend existing media table for V3 Media Library features.
     */
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->string('disk', 50)->default('public')->after('user_id');
            $table->string('path', 500)->nullable()->after('disk');
            $table->string('extension', 10)->nullable()->after('file_name');
            $table->string('mime', 100)->nullable()->after('extension');
            $table->unsignedBigInteger('size')->default(0)->after('mime');
            $table->unsignedInteger('width')->nullable()->after('size');
            $table->unsignedInteger('height')->nullable()->after('width');
            $table->string('alt', 300)->nullable()->after('height');
            $table->string('caption', 500)->nullable()->after('alt');
            $table->string('dominant_color', 7)->nullable()->after('caption'); // Hex color code
            $table->string('hash', 64)->nullable()->after('dominant_color');   // File content hash
            $table->unsignedInteger('used_count')->default(0)->after('hash');
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn([
                'disk',
                'path',
                'extension',
                'mime',
                'size',
                'width',
                'height',
                'alt',
                'caption',
                'dominant_color',
                'hash',
                'used_count'
            ]);
        });
    }
};
