<?php

use App\Services\YouTubeService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

it('does not replace the last known count when YouTube returns malformed data', function () {
    Cache::put('youtube.subscriber_count.last_known', 9876, now()->addDay());
    Log::spy();
    Http::fake([
        'www.googleapis.com/youtube/v3/channels*' => Http::response(['items' => []]),
    ]);

    expect(YouTubeService::subscriberCount())->toBe(9876)
        ->and(Cache::get('youtube.subscriber_count'))->toBeNull()
        ->and(Cache::get('youtube.subscriber_count.last_known'))->toBe(9876);

    Log::shouldHaveReceived('warning')
        ->once()
        ->withArgs(fn (string $message): bool => $message === 'Unable to refresh the YouTube subscriber count.');
});

it('accepts and caches a legitimate zero subscriber count', function () {
    Http::fake([
        'www.googleapis.com/youtube/v3/channels*' => Http::response([
            'items' => [['statistics' => ['subscriberCount' => 0]]],
        ]),
    ]);

    expect(YouTubeService::subscriberCount())->toBe(0)
        ->and(Cache::get('youtube.subscriber_count'))->toBe(0);
});

it('does not call YouTube when no channel videos are requested', function () {
    Http::fake();

    expect(app(YouTubeService::class)->getChannelVideos(0))->toBe([]);

    Http::assertNothingSent();
});
