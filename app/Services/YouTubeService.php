<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;
use UnexpectedValueException;

class YouTubeService
{
    protected string $apiKey;

    protected string $channelId;

    protected string $baseUrl = 'https://www.googleapis.com/youtube/v3';

    public function __construct()
    {
        $this->apiKey = (string) config('services.youtube.api_key');
        $this->channelId = (string) config('services.youtube.channel_id');
    }

    public static function subscriberCount(): int
    {
        $cacheKey = 'youtube.subscriber_count';

        $cachedCount = Cache::get($cacheKey);

        if (is_numeric($cachedCount)) {
            return (int) $cachedCount;
        }

        try {
            $apiKey = config('services.youtube.api_key');
            $channelId = config('services.youtube.channel_id');

            $response = self::client()->get('https://www.googleapis.com/youtube/v3/channels', [
                'key' => $apiKey,
                'id' => $channelId,
                'part' => 'statistics',
            ]);

            $subscriberCount = $response->json('items.0.statistics.subscriberCount');

            if (! is_numeric($subscriberCount)) {
                throw new UnexpectedValueException('YouTube returned no subscriber count.');
            }

            $count = (int) $subscriberCount;

            Cache::put($cacheKey, $count, now()->addHours(6));
            Cache::put("{$cacheKey}.last_known", $count, now()->addDays(30));

            return $count;
        } catch (Throwable $exception) {
            Log::warning('Unable to refresh the YouTube subscriber count.', [
                'exception' => $exception,
            ]);

            return (int) Cache::get("{$cacheKey}.last_known", 0);
        }
    }

    public function getChannelVideos(int $maxResults = 50): array
    {
        if ($maxResults < 1) {
            return [];
        }

        $videos = [];
        $pageToken = null;

        do {
            $response = self::client()->get("{$this->baseUrl}/search", array_filter([
                'key' => $this->apiKey,
                'channelId' => $this->channelId,
                'part' => 'snippet',
                'order' => 'date',
                'type' => 'video',
                'maxResults' => min($maxResults - count($videos), 50),
                'pageToken' => $pageToken,
            ]));

            $data = $response->json();
            $videoIds = collect($data['items'] ?? [])->pluck('id.videoId')->filter()->toArray();

            if (! empty($videoIds)) {
                $details = $this->getVideoDetails($videoIds);
                $videos = array_merge($videos, $details);
            }

            $pageToken = $data['nextPageToken'] ?? null;
        } while ($pageToken && count($videos) < $maxResults);

        return $videos;
    }

    public function getVideoDetails(array $videoIds): array
    {
        $response = self::client()->get("{$this->baseUrl}/videos", [
            'key' => $this->apiKey,
            'id' => implode(',', $videoIds),
            'part' => 'snippet,contentDetails,statistics',
        ]);

        return collect($response->json('items', []))->map(function ($item) {
            return [
                'youtube_id' => $item['id'],
                'title' => $item['snippet']['title'],
                'description' => $item['snippet']['description'] ?? null,
                'thumbnail_url' => $item['snippet']['thumbnails']['high']['url']
                    ?? $item['snippet']['thumbnails']['medium']['url']
                    ?? $item['snippet']['thumbnails']['default']['url']
                    ?? null,
                'duration' => $item['contentDetails']['duration'] ?? null,
                'view_count' => (int) ($item['statistics']['viewCount'] ?? 0),
                'like_count' => (int) ($item['statistics']['likeCount'] ?? 0),
                'comment_count' => (int) ($item['statistics']['commentCount'] ?? 0),
                'published_at' => $item['snippet']['publishedAt'] ?? null,
            ];
        })->toArray();
    }

    public function getStatsForVideos(array $videoIds): array
    {
        $response = self::client()->get("{$this->baseUrl}/videos", [
            'key' => $this->apiKey,
            'id' => implode(',', $videoIds),
            'part' => 'statistics',
        ]);

        return collect($response->json('items', []))->mapWithKeys(function ($item) {
            return [$item['id'] => [
                'view_count' => (int) ($item['statistics']['viewCount'] ?? 0),
                'like_count' => (int) ($item['statistics']['likeCount'] ?? 0),
                'comment_count' => (int) ($item['statistics']['commentCount'] ?? 0),
            ]];
        })->toArray();
    }

    private static function client(): PendingRequest
    {
        return Http::connectTimeout(2)
            ->timeout(5)
            ->retry(2, 100)
            ->throw();
    }
}
