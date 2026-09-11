<?php

use App\Data\YouTubeVideoData;

it('maps typed video data into explicit persistence attributes', function () {
    $data = new YouTubeVideoData(
        youtubeId: 'video-1',
        title: 'A video',
        description: null,
        thumbnailUrl: null,
        duration: null,
        viewCount: 0,
        likeCount: 0,
        commentCount: 0,
        publishedAt: null,
    );

    $attributes = $data
        ->toArray();

    expect($attributes)->toBe([
        'youtube_id' => 'video-1',
        'title' => 'A video',
        'description' => null,
        'thumbnail_url' => null,
        'duration' => null,
        'view_count' => 0,
        'like_count' => 0,
        'comment_count' => 0,
        'published_at' => null,
    ]);
});
