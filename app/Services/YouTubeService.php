<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class YouTubeService
{
    public static function subscriberCount(): int
    {
        return Cache::remember('youtube_subscriber_count', now()->addHours(4), function () {
            try {
                $response = Http::get('https://www.googleapis.com/youtube/v3/channels', [
                    'part' => 'statistics',
                    'id' => config('services.youtube.channel_id'),
                    'key' => config('services.youtube.api_key'),
                ]);

                if ($response->successful()) {
                    return (int) data_get($response->json(), 'items.0.statistics.subscriberCount', 0);
                }
            } catch (\Exception $e) {
                report($e);
            }

            return 0;
        });
    }
}
