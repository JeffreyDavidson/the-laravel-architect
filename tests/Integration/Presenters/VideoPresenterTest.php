<?php

use App\Models\Video;
use App\Presenters\VideoPresenter;

covers(VideoPresenter::class);

it('links the video watch page and embedded player on YouTube', function () {
    $presenter = VideoPresenter::from(new Video(['youtube_id' => 'dQw4w9WgXcQ']));

    expect($presenter->youtubeUrl())->toBe('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
        ->and($presenter->embedUrl())
        ->toBe('https://www.youtube.com/embed/dQw4w9WgXcQ');
});
