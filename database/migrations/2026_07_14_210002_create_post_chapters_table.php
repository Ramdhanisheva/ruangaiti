<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->string('title');
            $table->longText('content');
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_chapters');
    }
};
