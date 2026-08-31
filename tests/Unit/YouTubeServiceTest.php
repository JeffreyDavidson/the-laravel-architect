<?php

use App\Services\YouTubeService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JMac\Testing\Double;
use JMac\Testing\Matching\Argument;
use Psr\Log\LoggerInterface;

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

it('uses zero when the last known subscriber count is malformed', function () {
    Cache::put('youtube.subscriber_count.last_known', ['invalid'], now()->addDay());
    Http::fake(fn () => throw new RuntimeException('Unavailable'));

    expect(YouTubeService::subscriberCount())->toBe(0);
});

it('does not replace the last known count when YouTube returns malformed data', function () {
    Cache::put('youtube.subscriber_count.last_known', 9876, now()->addDay());
    $logger = Double::for(LoggerInterface::class);
    $logger->expects('warning')
        ->with('Unable to refresh the YouTube subscriber count.', Argument::type('array'));
    Log::swap($logger);
    Http::fake([
        'www.googleapis.com/youtube/v3/channels*' => Http::response(['items' => []]),
    ]);

    expect(YouTubeService::subscriberCount())->toBe(9876)
        ->and(Cache::get('youtube.subscriber_count'))->toBeNull()
        ->and(Cache::get('youtube.subscriber_count.last_known'))->toBe(9876);

});

it('does not expose the API key or request URL when subscriber refresh fails', function () {
    config()->set('services.youtube.api_key', 'secret-youtube-api-key');
    config()->set('services.youtube.channel_id', 'channel-id');
    Cache::put('youtube.subscriber_count.last_known', 9876, now()->addDay());
    $logger = Double::for(LoggerInterface::class);
    $logger->expects('warning')
        ->with(
            'Unable to refresh the YouTube subscriber count.',
            Argument::satisfies(function (array $context): bool {
                $loggedData = json_encode($context, JSON_THROW_ON_ERROR);

                return ! str_contains($loggedData, 'secret-youtube-api-key')
                    && ! str_contains($loggedData, 'www.googleapis.com');
            }),
        );
    Log::swap($logger);
    Http::fake(fn () => throw new RuntimeException(
        'Request failed for https://www.googleapis.com/youtube/v3/channels?key=secret-youtube-api-key',
    ));

    expect(YouTubeService::subscriberCount())->toBe(9876);

});

it('returns a credential-free failure when a video request fails', function () {
    config()->set('services.youtube.api_key', 'secret-youtube-api-key');
    Http::fake(fn () => throw new RuntimeException(
        'Request failed for https://www.googleapis.com/youtube/v3/videos?key=secret-youtube-api-key',
    ));

    expect(fn () => app(YouTubeService::class)->getVideoDetails(['video-1']))
        ->toThrow(RuntimeException::class, 'The YouTube request failed.');
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

it('maps video details into the application payload', function () {
    Http::fake([
        'www.googleapis.com/youtube/v3/videos*' => Http::response([
            'items' => [[
                'id' => 'video-1',
                'snippet' => [
                    'title' => 'Typed payloads',
                    'description' => 'A description',
                    'publishedAt' => '2026-08-18T12:00:00Z',
                    'thumbnails' => ['high' => ['url' => 'https://example.com/thumbnail.jpg']],
                ],
                'contentDetails' => ['duration' => 'PT5M'],
                'statistics' => [
                    'viewCount' => '120',
                    'likeCount' => '12',
                    'commentCount' => '3',
                ],
            ]],
        ]),
    ]);

    expect(app(YouTubeService::class)->getVideoDetails(['video-1']))->toBe([[
        'youtube_id' => 'video-1',
        'title' => 'Typed payloads',
        'description' => 'A description',
        'thumbnail_url' => 'https://example.com/thumbnail.jpg',
        'duration' => 'PT5M',
        'view_count' => 120,
        'like_count' => 12,
        'comment_count' => 3,
        'published_at' => '2026-08-18T12:00:00Z',
    ]]);
});

it('skips malformed video detail items', function () {
    Http::fake([
        'www.googleapis.com/youtube/v3/videos*' => Http::response([
            'items' => [null, 'invalid', ['id' => 'missing-title'], ['numeric-key']],
        ]),
    ]);

    expect(app(YouTubeService::class)->getVideoDetails(['video-1']))->toBe([]);
});

it('uses zero for malformed video statistics', function () {
    Http::fake([
        'www.googleapis.com/youtube/v3/videos*' => Http::response([
            'items' => [[
                'id' => 'video-1',
                'snippet' => ['title' => 'Malformed statistics'],
                'statistics' => [
                    'viewCount' => ['invalid'],
                    'likeCount' => new stdClass,
                    'commentCount' => null,
                ],
            ]],
        ]),
    ]);

    $videos = app(YouTubeService::class)->getVideoDetails(['video-1']);

    expect($videos)->toHaveCount(1)
        ->and($videos[0]['view_count'])->toBe(0)
        ->and($videos[0]['like_count'])->toBe(0)
        ->and($videos[0]['comment_count'])->toBe(0);
});

it('maps valid video statistics and skips malformed items', function () {
    Http::fake([
        'www.googleapis.com/youtube/v3/videos*' => Http::response([
            'items' => [
                ['id' => 'video-1', 'statistics' => [
                    'viewCount' => '120',
                    'likeCount' => ['invalid'],
                    'commentCount' => '3',
                ]],
                ['id' => ['invalid']],
                ['numeric-key'],
            ],
        ]),
    ]);

    expect(app(YouTubeService::class)->getStatsForVideos(['video-1']))->toBe([
        'video-1' => [
            'view_count' => 120,
            'like_count' => 0,
            'comment_count' => 3,
        ],
    ]);
});
