<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Video;
use Illuminate\Support\Str;

final readonly class YouTubeVideoSynchronizer
{
    public function __construct(private YouTubeService $youtube) {}

    /** @return array{created: int, updated: int} */
    public function synchronize(int $limit): array
    {
        $videos = $this->youtube->getChannelVideos($limit);
        $created = 0;
        $updated = 0;

        foreach ($videos as $videoData) {
            $video = Video::query()->where('youtube_id', $videoData->youtubeId)->first();

            if ($video instanceof Video) {
                $video->update([
                    'title' => $videoData->title,
                    'description' => $videoData->description,
                    'thumbnail_url' => $videoData->thumbnailUrl,
                    'duration' => $videoData->duration,
                    'view_count' => $videoData->viewCount,
                    'like_count' => $videoData->likeCount,
                    'comment_count' => $videoData->commentCount,
                    'synced_at' => now(),
                ]);
                $updated++;

                continue;
            }

            Video::query()->create([
                ...$videoData->toArray(),
                'slug' => $this->uniqueSlug($videoData->title, $videoData->youtubeId),
                'synced_at' => now(),
            ]);
            $created++;
        }

        return [
            'created' => $created,
            'updated' => $updated,
        ];
    }

    private function uniqueSlug(string $title, string $youtubeId): string
    {
        $youtubeIdSlug = Str::slug($youtubeId);

        if ($youtubeIdSlug === '') {
            $youtubeIdSlug = substr(hash('sha256', $youtubeId), 0, 12);
        }

        $youtubeIdSlug = Str::substr($youtubeIdSlug, 0, 48);
        $baseSlug = Str::slug($title);

        if ($baseSlug === '') {
            $baseSlug = "video-{$youtubeIdSlug}";
        }

        $baseSlug = rtrim(Str::substr($baseSlug, 0, 255), '-');

        if (! Video::query()->where('slug', $baseSlug)->exists()) {
            return $baseSlug;
        }

        $suffix = "-{$youtubeIdSlug}";
        $slug = rtrim(Str::substr($baseSlug, 0, 255 - strlen($suffix)), '-').$suffix;
        $attempt = 2;

        while (Video::query()->where('slug', $slug)->exists()) {
            $numberedSuffix = "{$suffix}-{$attempt}";
            $slug = rtrim(Str::substr($baseSlug, 0, 255 - strlen($numberedSuffix)), '-').$numberedSuffix;
            $attempt++;
        }

        return $slug;
    }
}
