<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->index(
                ['status', 'published_at', 'id'],
                'posts_publication_listing_index',
            );
            $table->index(
                ['category_id', 'status', 'published_at', 'id'],
                'posts_category_publication_listing_index',
            );
        });

        Schema::table('projects', function (Blueprint $table): void {
            $table->index(
                ['status', 'sort_order'],
                'projects_publication_listing_index',
            );
            $table->index(
                ['status', 'is_featured', 'sort_order'],
                'projects_featured_listing_index',
            );
        });

        Schema::table('videos', function (Blueprint $table): void {
            $table->index(
                ['published_at', 'id'],
                'videos_publication_listing_index',
            );
        });

        Schema::table('episodes', function (Blueprint $table): void {
            $table->index(
                ['podcast_id', 'status', 'published_at', 'id'],
                'episodes_publication_listing_index',
            );
        });
    }
};
