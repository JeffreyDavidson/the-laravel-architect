<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('podcasts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('long_description')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('color')->default('#6366f1');
            $table->string('apple_url')->nullable();
            $table->string('spotify_url')->nullable();
            $table->string('rss_url')->nullable();
            $table->string('youtube_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Add podcast_id to episodes
        Schema::table('episodes', function (Blueprint $table) {
            $table->foreignId('podcast_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('episodes', function (Blueprint $table) {
            $table->dropForeign(['podcast_id']);
            $table->dropColumn('podcast_id');
        });
        Schema::dropIfExists('podcasts');
    }
};
