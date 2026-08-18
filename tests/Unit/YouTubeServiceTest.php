<?php

use App\Services\YouTubeService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Cache::flush();
});

it('caches the subscriber count', function () {
    Http::fake([
        'www.googleapis.com/youtube/v3/channels*' => Http::response([
            'items' => [['statistics' => ['subscriberCount' => 4321]]],
        ]),
    ]);

    expect(YouTubeService::subscriberCount())->toBe(4321)
        ->and(YouTubeService::subscriberCount())->toBe(4321);

    Http::assertSentCount(1);
});

it('uses the last known subscriber count when YouTube is unavailable', function () {
    Cache::put('youtube.subscriber_count.last_known', 9876, now()->addDay());
    Http::fake(fn () => throw new RuntimeException('Unavailable'));

    expect(YouTubeService::subscriberCount())->toBe(9876);
});
