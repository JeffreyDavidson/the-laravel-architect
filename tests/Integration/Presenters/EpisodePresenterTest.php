<?php

use App\Models\Episode;
use App\Presenters\EpisodePresenter;

it('formats an episode duration stored in seconds', function (?int $seconds, string $expected) {
    $episode = new Episode(['duration_seconds' => $seconds]);

    expect(EpisodePresenter::from($episode)->duration())
        ->toBe($expected);
})->with([
    'not set' => [null, ''],
    'zero' => [0, ''],
    'under a minute rounds up to one minute' => [20, '1 min'],
    'rounds down to the nearest minute' => [89, '1 min'],
    'rounds up to the nearest minute' => [90, '2 min'],
    'whole minutes' => [1500, '25 min'],
    'just under an hour rounds to the hour' => [3599, '1h 0m'],
    'hours and minutes' => [3900, '1h 5m'],
    'two hours' => [7260, '2h 1m'],
]);

it('turns only a Transistor share URL into its embed URL', function (?string $url, ?string $embedUrl) {
    $episode = new Episode(['transistor_url' => $url]);

    expect(EpisodePresenter::from($episode)->transistorEmbedUrl())
        ->toBe($embedUrl);
})->with([
    'share URL' => ['https://share.transistor.fm/s/428dcd6b', 'https://share.transistor.fm/e/428dcd6b'],
    'share URL with trailing slash' => ['https://share.transistor.fm/s/428dcd6b/', 'https://share.transistor.fm/e/428dcd6b'],
    'missing' => [null, null],
    'other host' => ['https://example.com/s/428dcd6b', null],
]);

it('finds the YouTube video ID in watch, embed and short links', function (?string $url, bool $hasYouTube, ?string $videoId) {
    $presenter = EpisodePresenter::from(new Episode(['youtube_url' => $url]));

    expect($presenter->hasYouTube())->toBe($hasYouTube)
        ->and($presenter->youtubeVideoId())
        ->toBe($videoId);
})->with([
    'watch link' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ', true, 'dQw4w9WgXcQ'],
    'embed link' => ['https://www.youtube.com/embed/dQw4w9WgXcQ', true, 'dQw4w9WgXcQ'],
    'short link' => ['https://youtu.be/abc_DEF-123', true, 'abc_DEF-123'],
    'channel link has no video' => ['https://www.youtube.com/@thelaravelarchitect', true, null],
    'other video site' => ['https://vimeo.com/123', false, null],
    'no link' => [null, false, null],
]);
