<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Alter status column to VARCHAR(30)
        DB::statement("ALTER TABLE pages MODIFY COLUMN status VARCHAR(30) NOT NULL DEFAULT 'Draft'");

        // Map old boolean values: '1' or 'true' -> 'Published', '0' or 'false' -> 'Draft'
        DB::table('pages')->where('status', '1')->update(['status' => 'Published']);
        DB::table('pages')->where('status', '0')->update(['status' => 'Draft']);
    }

    public function down(): void
    {
        // Map back to boolean-like values: 'Published' -> '1', others -> '0'
        DB::table('pages')->where('status', 'Published')->update(['status' => '1']);
        DB::table('pages')->where('status', '!=', '1')->update(['status' => '0']);

        // Revert column to tinyint(1)
        DB::statement("ALTER TABLE pages MODIFY COLUMN status TINYINT(1) NOT NULL DEFAULT 0");
    }
};
