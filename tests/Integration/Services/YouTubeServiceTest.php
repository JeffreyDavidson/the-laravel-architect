<?php

use App\Data\YouTubeVideoData;
use App\Services\YouTubeService;
use Illuminate\Support\Facades\Http;

it('returns a credential-free failure when a video request fails', function () {
    config()->set('services.youtube.api_key', 'secret-youtube-api-key');
    Http::fake(fn () => throw new RuntimeException(
        'Request failed for https://www.googleapis.com/youtube/v3/videos?key=secret-youtube-api-key',
    ));

    expect(fn () => app(YouTubeService::class)->getVideoDetails(['video-1']))
        ->toThrow(RuntimeException::class, 'The YouTube request failed.');
});

it('does not call YouTube when no channel videos are requested', function () {
    Http::fake();

    expect(app(YouTubeService::class)->getChannelVideos(0))->toBeEmpty();

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

    expect(app(YouTubeService::class)->getVideoDetails(['video-1']))->toEqual([new YouTubeVideoData(
        youtubeId: 'video-1',
        title: 'Typed payloads',
        description: 'A description',
        thumbnailUrl: 'https://example.com/thumbnail.jpg',
        duration: 'PT5M',
        viewCount: 120,
        likeCount: 12,
        commentCount: 3,
        publishedAt: '2026-08-18T12:00:00Z',
    )]);
});

it('skips malformed video detail items', function () {
    Http::fake([
        'www.googleapis.com/youtube/v3/videos*' => Http::response([
            'items' => [null, 'invalid', ['id' => 'missing-title'], ['numeric-key']],
        ]),
    ]);

    expect(app(YouTubeService::class)->getVideoDetails(['video-1']))->toBeEmpty();
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
        ->and($videos[0]->viewCount)
        ->toBe(0)
        ->and($videos[0]->likeCount)
        ->toBe(0)
        ->and($videos[0]->commentCount)
        ->toBe(0);
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
