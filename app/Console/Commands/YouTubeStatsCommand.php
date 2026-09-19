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
        $videoCount = Video::query()->count();

        if ($videoCount === 0) {
            $this->info('No videos to update. Run youtube:sync first.');

            return self::SUCCESS;
        }

        $this->info("Updating stats for {$videoCount} videos...");

        $updated = 0;

        // YouTube API allows up to 50 IDs per request.
        foreach (Video::query()->lazyById(50)->chunk(50) as $videos) {
            $videoIds = [];

            foreach ($videos as $video) {
                $videoId = $video->getAttribute('youtube_id');

                if (is_string($videoId)) {
                    $videoIds[] = $videoId;
                }
            }

            if ($videoIds === []) {
                continue;
            }

            try {
                $stats = $youtube->getStatsForVideos($videoIds);
            } catch (\RuntimeException $e) {
                $this->error($e->getMessage());

                return self::FAILURE;
            }

            if ($stats === []) {
                continue;
            }

            $syncedAt = now();
            $updates = [];
            $videosByYouTubeId = $videos->keyBy('youtube_id');

            foreach ($stats as $youtubeId => $counts) {
                $video = $videosByYouTubeId->get($youtubeId);

                if (! $video instanceof Video) {
                    continue;
                }

                $updates[] = [
                    ...$video->getAttributes(),
                    ...$counts,
                    'synced_at' => $syncedAt,
                ];
            }

            if ($updates === []) {
                continue;
            }

            Video::query()->upsert(
                $updates,
                ['youtube_id'],
                ['view_count', 'like_count', 'comment_count', 'synced_at'],
            );
            $updated += count($updates);
        }

        $this->info("Updated stats for {$updated} videos.");

        return self::SUCCESS;
    }
}
