<?php

namespace App\Console\Commands;

use App\Models\Video;
use App\Services\YouTubeService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('youtube:sync {--limit=50 : Maximum videos to fetch}')]
#[Description('Sync videos from YouTube channel')]
class YouTubeSyncCommand extends Command
{
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
            $video = Video::query()->where('youtube_id', $videoData->youtubeId)->first();

            if ($video) {
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
            } else {
                Video::query()->create([
                    ...$videoData->toArray(),
                    'slug' => $this->uniqueSlug($videoData->title, $videoData->youtubeId),
                    'synced_at' => now(),
                ]);
                $synced++;
            }
        }

        $this->info("Done! {$synced} new, {$updated} updated.");

        return self::SUCCESS;
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
