<?php

use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('featured_image_path')->nullable()->after('content');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->string('featured_image_path')->nullable()->after('content');
        });

        Schema::table('podcasts', function (Blueprint $table) {
            $table->string('cover_image_path')->nullable()->after('long_description');
        });

        Schema::table('episodes', function (Blueprint $table) {
            $table->string('featured_image_path')->nullable()->after('show_notes');
            $table->string('audio_path')->nullable()->after('audio_url');
        });

        if (! Schema::hasTable('media')) {
            return;
        }

        $mappings = [
            [Post::class, 'featured_image', 'posts', 'featured_image_path'],
            [Project::class, 'featured_image', 'projects', 'featured_image_path'],
            [Podcast::class, 'cover_image', 'podcasts', 'cover_image_path'],
            [Episode::class, 'featured_image', 'episodes', 'featured_image_path'],
            [Episode::class, 'audio', 'episodes', 'audio_path'],
        ];

        foreach ($mappings as [$modelType, $collection, $table, $column]) {
            DB::table('media')
                ->where('model_type', $modelType)
                ->where('collection_name', $collection)
                ->orderBy('order_column')
                ->orderBy('id')
                ->get(['id', 'model_id', 'file_name'])
                ->each(function (object $media) use ($table, $column): void {
                    DB::table($table)
                        ->where('id', $media->model_id)
                        ->update([$column => "{$media->id}/{$media->file_name}"]);
                });
        }

        Schema::drop('media');
    }
};
