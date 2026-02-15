<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class YouTubeService
{
    protected string $apiKey;
    protected string $channelId;
    protected string $baseUrl = 'https://www.googleapis.com/youtube/v3';

    public function __construct()
    {
        $this->apiKey = config('services.youtube.api_key');
        $this->channelId = config('services.youtube.channel_id');
    }

    public function getChannelVideos(int $maxResults = 50): array
    {
        $videos = [];
        $pageToken = null;

        do {
            $response = Http::get("{$this->baseUrl}/search", array_filter([
                'key' => $this->apiKey,
                'channelId' => $this->channelId,
                'part' => 'snippet',
                'order' => 'date',
                'type' => 'video',
                'maxResults' => min($maxResults - count($videos), 50),
                'pageToken' => $pageToken,
            ]));

            if ($response->failed()) {
                throw new \RuntimeException('YouTube API error: ' . $response->body());
            }

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
        $response = Http::get("{$this->baseUrl}/videos", [
            'key' => $this->apiKey,
            'id' => implode(',', $videoIds),
            'part' => 'snippet,contentDetails,statistics',
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('YouTube API error: ' . $response->body());
        }

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
        $response = Http::get("{$this->baseUrl}/videos", [
            'key' => $this->apiKey,
            'id' => implode(',', $videoIds),
            'part' => 'statistics',
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('YouTube API error: ' . $response->body());
        }

        return collect($response->json('items', []))->mapWithKeys(function ($item) {
            return [$item['id'] => [
                'view_count' => (int) ($item['statistics']['viewCount'] ?? 0),
                'like_count' => (int) ($item['statistics']['likeCount'] ?? 0),
                'comment_count' => (int) ($item['statistics']['commentCount'] ?? 0),
            ]];
        })->toArray();
    }
}
