<?php

use App\Models\Podcast;

it('renders nothing for a podcast without cover artwork', function () {
    $podcast = new Podcast([
        'name' => 'Architecture Sessions',
        'slug' => 'architecture-sessions',
    ]);

    $html = (string) $this->blade(
        '<x-podcast-cover :podcast="$podcast" sizes="224px" width="224" height="224" />',
        ['podcast' => $podcast],
    );

    expect(trim($html))->toBeEmpty();
});

it('renders an uploaded podcast cover', function () {
    $podcast = new Podcast([
        'name' => 'Architecture Sessions',
        'slug' => 'architecture-sessions',
        'cover_image_path' => 'podcasts/cover.png',
    ]);

    $html = (string) $this->blade(
        '<x-podcast-cover :podcast="$podcast" sizes="224px" width="224" height="224" />',
        ['podcast' => $podcast],
    );

    expect($html)->toContain('src="'.$podcast->cover_image_url.'"');
});
