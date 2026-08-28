<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;
use UnexpectedValueException;

/**
 * @phpstan-type VideoPayload array{
 *     youtube_id: string,
 *     title: string,
 *     description: string|null,
 *     thumbnail_url: string|null,
 *     duration: string|null,
 *     view_count: int,
 *     like_count: int,
 *     comment_count: int,
 *     published_at: string|null
 * }
 * @phpstan-type VideoStats array{view_count: int, like_count: int, comment_count: int}
 */
class YouTubeService
{
    protected string $apiKey;

    protected string $channelId;

    protected string $baseUrl = 'https://www.googleapis.com/youtube/v3';

    public function __construct()
    {
        $apiKey = config('services.youtube.api_key');
        $channelId = config('services.youtube.channel_id');

        $this->apiKey = is_string($apiKey) ? $apiKey : '';
        $this->channelId = is_string($channelId) ? $channelId : '';
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

            $response = self::request('https://www.googleapis.com/youtube/v3/channels', [
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
                'exception' => $exception::class,
            ]);

            return self::integer(Cache::get("{$cacheKey}.last_known", 0));
        }
    }

    /** @return list<VideoPayload> */
    public function getChannelVideos(int $maxResults = 50): array
    {
        if ($maxResults < 1) {
            return [];
        }

        $videos = [];
        $pageToken = null;

        do {
            $response = self::request("{$this->baseUrl}/search", array_filter([
                'key' => $this->apiKey,
                'channelId' => $this->channelId,
                'part' => 'snippet',
                'order' => 'date',
                'type' => 'video',
                'maxResults' => min($maxResults - count($videos), 50),
                'pageToken' => $pageToken,
            ]));

            $data = $response->json();
            $data = is_array($data) ? $data : [];
            $videoIds = [];

            foreach ($this->responseItems($response) as $item) {
                $videoId = data_get($item, 'id.videoId');

                if (is_string($videoId) && $videoId !== '') {
                    $videoIds[] = $videoId;
                }
            }

            if (! empty($videoIds)) {
                $details = $this->getVideoDetails($videoIds);
                $videos = array_merge($videos, $details);
            }

            $nextPageToken = $data['nextPageToken'] ?? null;
            $pageToken = is_string($nextPageToken) && $nextPageToken !== '' ? $nextPageToken : null;
        } while ($pageToken && count($videos) < $maxResults);

        return $videos;
    }

    /**
     * @param  list<string>  $videoIds
     * @return list<VideoPayload>
     */
    public function getVideoDetails(array $videoIds): array
    {
        $response = self::request("{$this->baseUrl}/videos", [
            'key' => $this->apiKey,
            'id' => implode(',', $videoIds),
            'part' => 'snippet,contentDetails,statistics',
        ]);

        $videos = [];

        foreach ($this->responseItems($response) as $item) {
            $videoId = data_get($item, 'id');
            $title = data_get($item, 'snippet.title');

            if (! is_string($videoId) || ! is_string($title)) {
                continue;
            }

            $videos[] = [
                'youtube_id' => $videoId,
                'title' => $title,
                'description' => $this->nullableString(data_get($item, 'snippet.description')),
                'thumbnail_url' => $this->nullableString(
                    data_get($item, 'snippet.thumbnails.high.url')
                        ?? data_get($item, 'snippet.thumbnails.medium.url')
                        ?? data_get($item, 'snippet.thumbnails.default.url'),
                ),
                'duration' => $this->nullableString(data_get($item, 'contentDetails.duration')),
                'view_count' => self::integer(data_get($item, 'statistics.viewCount', 0)),
                'like_count' => self::integer(data_get($item, 'statistics.likeCount', 0)),
                'comment_count' => self::integer(data_get($item, 'statistics.commentCount', 0)),
                'published_at' => $this->nullableString(data_get($item, 'snippet.publishedAt')),
            ];
        }

        return $videos;
    }

    /**
     * @param  list<string>  $videoIds
     * @return array<string, VideoStats>
     */
    public function getStatsForVideos(array $videoIds): array
    {
        $response = self::request("{$this->baseUrl}/videos", [
            'key' => $this->apiKey,
            'id' => implode(',', $videoIds),
            'part' => 'statistics',
        ]);

        $stats = [];

        foreach ($this->responseItems($response) as $item) {
            $videoId = data_get($item, 'id');

            if (! is_string($videoId)) {
                continue;
            }

            $stats[$videoId] = [
                'view_count' => self::integer(data_get($item, 'statistics.viewCount', 0)),
                'like_count' => self::integer(data_get($item, 'statistics.likeCount', 0)),
                'comment_count' => self::integer(data_get($item, 'statistics.commentCount', 0)),
            ];
        }

        return $stats;
    }

    /** @return list<array<string, mixed>> */
    private function responseItems(Response $response): array
    {
        $items = $response->json('items', []);

        if (! is_array($items)) {
            return [];
        }

        $validItems = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $validItem = [];

            foreach ($item as $key => $value) {
                if (! is_string($key)) {
                    continue 2;
                }

                $validItem[$key] = $value;
            }

            $validItems[] = $validItem;
        }

        return $validItems;
    }

    private function nullableString(mixed $value): ?string
    {
        return is_string($value) ? $value : null;
    }

    private static function integer(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }

    /** @param array<string, mixed> $query */
    private static function request(string $url, array $query): Response
    {
        try {
            return self::client()->get($url, $query);
        } catch (Throwable) {
            throw new RuntimeException('The YouTube request failed.');
        }
    }

    private static function client(): PendingRequest
    {
        return Http::connectTimeout(2)
            ->timeout(5)
            ->retry(2, 100)
            ->throw();
    }
}
