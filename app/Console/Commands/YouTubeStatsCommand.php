<?php

namespace App\Console\Commands;

use App\Models\Video;
use App\Services\YouTubeService;
use Illuminate\Console\Command;

class YouTubeStatsCommand extends Command
{
    protected $signature = 'youtube:stats';
    protected $description = 'Update view/like/comment counts for all synced videos';

    public function handle(YouTubeService $youtube): int
    {
        $videos = Video::all();

        if ($videos->isEmpty()) {
            $this->info('No videos to update. Run youtube:sync first.');
            return self::SUCCESS;
        }

        $this->info("Updating stats for {$videos->count()} videos...");

        // YouTube API allows up to 50 IDs per request
        $chunks = $videos->pluck('youtube_id')->chunk(50);

        $updated = 0;

        foreach ($chunks as $chunk) {
            try {
                $stats = $youtube->getStatsForVideos($chunk->toArray());
            } catch (\RuntimeException $e) {
                $this->error($e->getMessage());
                return self::FAILURE;
            }

            foreach ($stats as $youtubeId => $counts) {
                Video::where('youtube_id', $youtubeId)->update([
                    ...$counts,
                    'synced_at' => now(),
                ]);
                $updated++;
            }
        }

        $this->info("Updated stats for {$updated} videos.");

        return self::SUCCESS;
    }
}
