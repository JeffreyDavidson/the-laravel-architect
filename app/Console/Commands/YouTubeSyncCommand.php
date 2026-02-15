<?php

namespace App\Console\Commands;

use App\Models\Video;
use App\Services\YouTubeService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class YouTubeSyncCommand extends Command
{
    protected $signature = 'youtube:sync {--limit=50 : Maximum videos to fetch}';
    protected $description = 'Sync videos from YouTube channel';

    public function handle(YouTubeService $youtube): int
    {
        $this->info('Fetching videos from YouTube...');

        try {
            $videos = $youtube->getChannelVideos((int) $this->option('limit'));
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        $synced = 0;
        $updated = 0;

        foreach ($videos as $videoData) {
            $video = Video::where('youtube_id', $videoData['youtube_id'])->first();

            if ($video) {
                $video->update([
                    'title' => $videoData['title'],
                    'description' => $videoData['description'],
                    'thumbnail_url' => $videoData['thumbnail_url'],
                    'duration' => $videoData['duration'],
                    'view_count' => $videoData['view_count'],
                    'like_count' => $videoData['like_count'],
                    'comment_count' => $videoData['comment_count'],
                    'synced_at' => now(),
                ]);
                $updated++;
            } else {
                Video::create([
                    ...$videoData,
                    'slug' => Str::slug($videoData['title']),
                    'published_at' => $videoData['published_at'],
                    'synced_at' => now(),
                ]);
                $synced++;
            }
        }

        $this->info("Done! {$synced} new, {$updated} updated.");

        return self::SUCCESS;
    }
}
