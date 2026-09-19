<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Video;

final class YouTubeVideoStatsSynchronizer
{
    /**
     * @return array{videoCount: int, updated: int}
     */
    public function synchronize(YouTubeService $youtube): array
    {
        $videoCount = Video::query()->count();

        if ($videoCount === 0) {
            return ['videoCount' => 0, 'updated' => 0];
        }

        $updated = 0;

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

            $stats = $youtube->getStatsForVideos($videoIds);

            if ($stats === []) {
                continue;
            }

            $syncedAt = now();
            $videosByYouTubeId = $videos->keyBy('youtube_id');
            $updates = [];

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

        return [
            'videoCount' => $videoCount,
            'updated' => $updated,
        ];
    }
}
