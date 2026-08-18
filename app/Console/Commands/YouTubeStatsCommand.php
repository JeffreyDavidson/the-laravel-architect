<?php

namespace App\Console\Commands;

use App\Models\Video;
use App\Services\YouTubeService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('youtube:stats')]
#[Description('Update view/like/comment counts for all synced videos')]
class YouTubeStatsCommand extends Command
{
    public function handle(YouTubeService $youtube): int
    {
        $videos = Video::all();

        if ($videos->isEmpty()) {
            $this->info('No videos to update. Run youtube:sync first.');

            return self::SUCCESS;
        }

        $this->info("Updating stats for {$videos->count()} videos...");

        $videoIds = $videos->pluck('youtube_id')
            ->filter(is_string(...))
            ->values()
            ->all();

        $updated = 0;

        // YouTube API allows up to 50 IDs per request
        foreach (array_chunk($videoIds, 50) as $chunk) {
            try {
                $stats = $youtube->getStatsForVideos($chunk);
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
